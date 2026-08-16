<?php
// ============================================================
//  RitmoReporte — Reporte del Director de Ventas por asesor
//  ANCLADO A LA TARJETA (RitmoAsesor): el veredicto, los 5 pilares y
//  sus números son EXACTAMENTE los de la tarjeta de Ritmo. Encima
//  agrega casos concretos, Radar, descuentos y el consejo. TODO dentro de
//  la ventana — nada acumulado/fuera del rango.
//  Nunca puede contradecir a la tarjeta (misma fuente, misma ventana).
//
//  Fuentes: RitmoAsesor (pilares) · usuario_score (score) ·
//  Mesa::armar (vencidas) ·
//  bucket_transitions + radar_feedback (Radar) · ventas (descuentos).
//  Solo lectura. Fact-lint: cada cifra sale del sistema.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoReporte
{
    /**
     * Expediente del asesor (datos, sin render): pilares de la tarjeta + score +
     * descartes + ventas + mesa + radar. Lo consume el reporte Y el termómetro,
     * para que ambos detecten la MISMA debilidad #1. Memoizado por (empresa,asesor).
     */
    public static function expediente(int $empresa_id, int $asesor_id): array
    {
        static $memo = [];
        $mk = "$empresa_id:$asesor_id";
        if (isset($memo[$mk])) return $memo[$mk];

        // Ventana = la MISMA de la tarjeta (2×p75 del ciclo real de la empresa).
        $p75 = 10; $mediana = 5;
        try {
            if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
            $c = Radar::ciclo_venta($empresa_id);
            if (!empty($c['auto'])) {
                if (!empty($c['p75']))     $p75 = max(3, (int)$c['p75']);
                if (!empty($c['mediana'])) $mediana = max(1, (int)$c['mediana']);
            }
        } catch (Throwable $e) {}
        $win = 2 * $p75;
        $rapido_dias = max(1, (int)floor($mediana / 2));

        // ── Pilares: EXACTOS de la tarjeta ──
        $card = null;
        try {
            foreach (RitmoAsesor::empresa($empresa_id) as $r)
                if ((int)$r['usuario_id'] === $asesor_id) { $card = $r; break; }
        } catch (Throwable $e) {}

        // El nombre sale de la tarjeta; solo se consulta si no hubo tarjeta.
        $nombre = (string)($card['nombre'] ?? '');
        if ($nombre === '') {
            try { $nombre = (string) DB::val("SELECT nombre FROM usuarios WHERE id=? AND empresa_id=?", [$asesor_id, $empresa_id]); }
            catch (Throwable $e) { $nombre = ''; }
        }
        if ($nombre === '') $nombre = "Asesor #{$asesor_id}";

        return $memo[$mk] = [
            'nombre' => $nombre, 'win' => $win, 'card' => $card,
            'score'  => self::_score($asesor_id, $empresa_id),
            'bench_ticket' => self::_bench_ticket($empresa_id),
            'desc'   => self::_descartes($empresa_id, $asesor_id, $win, $rapido_dias),
            'vent'   => self::_ventas($empresa_id, $asesor_id, $win),
            'mesa'   => self::_mesa($empresa_id, $asesor_id),
            'radar'  => self::_radar($empresa_id, $asesor_id, $win),
        ];
    }

    /**
     * Reporte completo. NO recibe rango: la ventana la manda el ciclo real de la
     * empresa (2×p75), igual que la tarjeta. Quien lo llame lee $rep['win'].
     */
    public static function generar(int $empresa_id, int $asesor_id): array
    {
        $d = self::expediente($empresa_id, $asesor_id);

        // Tip de coaching rotado (no repite técnica hasta agotar la debilidad).
        $vistos = [];
        try {
            // id DESC desempata: dos reportes en el MISMO segundo tienen igual
            // created_at y el orden decidiría mal cuál es "la más reciente".
            foreach (DB::query("SELECT handle FROM ritmo_tips WHERE empresa_id=? AND asesor_id=? ORDER BY created_at DESC, id DESC LIMIT 40",
                     [$empresa_id, $asesor_id]) as $r) $vistos[] = $r['handle'];
        } catch (Throwable $e) {}
        try { $d['tip'] = RitmoTip::elegir($d, $vistos); } catch (Throwable $e) { $d['tip'] = null; }

        $d['secciones'] = self::_componer($d);
        $d['html'] = self::render($d);
        return $d;
    }

    /**
     * Ticket promedio del EQUIPO (referencia para "cierra chico"). Exige al menos
     * 2 asesores con ticket — con uno solo se compararía contra sí mismo. 0 = no
     * hay con quién comparar (el tip de ticket no se dispara).
     */
    private static function _bench_ticket(int $eid): float
    {
        static $c = [];
        if (isset($c[$eid])) return $c[$eid];
        $v = 0.0;
        try {
            // Mismo filtro canónico que RitmoAsesor/ActividadScore: sin superadmin
            // ni usuarios dados de baja (si no, inflan o ensucian el promedio).
            $r = DB::row("SELECT COUNT(*) AS n, AVG(us.ticket_promedio) AS t
                            FROM usuario_score us JOIN usuarios u ON u.id = us.usuario_id
                           WHERE us.empresa_id=? AND us.ticket_promedio>0
                             AND u.activo=1 AND COALESCE(u.rol,'') <> 'superadmin'", [$eid]);
            if ((int)($r['n'] ?? 0) >= 2) $v = (float)($r['t'] ?? 0);
        } catch (Throwable $e) {}
        return $c[$eid] = $v;
    }

    private static function _score(int $uid, int $eid): ?array
    {
        try { $r = DB::row("SELECT * FROM usuario_score WHERE usuario_id=? AND empresa_id=?", [$uid, $eid]); }
        catch (Throwable $e) { $r = null; }
        if (!$r) return null;
        return [
            'score' => (int)($r['score'] ?? 0), 'nivel' => (string)($r['nivel'] ?? ''),
            'dim' => [
                'Activación'   => (float)($r['s_activacion'] ?? 0),
                'Engagement'   => (float)($r['s_engagement'] ?? 0),
                'Seguimiento'  => (float)($r['s_seguimiento'] ?? 0),
                'Radar Health' => (float)($r['s_radar_health'] ?? 0),
                'Conversión'   => (float)($r['s_conversion'] ?? 0),
            ],
            'ticket' => (float)($r['ticket_promedio'] ?? 0),
        ];
    }

    // Descartes de la ventana — MISMO UNION que la tarjeta (mesa postura
    // 'descartada' + 👎 del Radar 'sin_interes') para que n/sincita/rapido y los
    // casos coincidan EXACTO con el pilar Descartadas de RitmoAsesor.
    private static function _descartes(int $eid, int $uid, int $win, int $rapido_dias): array
    {
        $o = ['n'=>0,'sincita'=>0,'rapido'=>0,'hot'=>0,'hot_noprecio'=>0,'precio'=>0,'precio_hot'=>0,'aire'=>0,'casos'=>[]];
        $nc = "(NOT EXISTS (SELECT 1 FROM mesa_estados mc WHERE mc.cotizacion_id=c.id AND mc.area='compromiso' AND mc.estado='nos_citamos'))";
        // ¿esta cotización estuvo caliente en la ventana? → para "descartó calientes",
        //   garantizado subconjunto de los descartes (mismo set, misma ventana).
        $hotset = Mesa::hot_sql();   // fuente única (Mesa::HOT)
        $hot = "EXISTS (SELECT 1 FROM bucket_transitions bt WHERE bt.cotizacion_id=c.id AND bt.bucket_nuevo IN $hotset AND bt.created_at >= NOW() - INTERVAL $win DAY)";
        // Cruce por cotización: la razón declarada al descartar + la última postura previa.
        $rz = "(SELECT mp.razon FROM mesa_estados mp WHERE mp.cotizacion_id=c.id AND mp.area='postura' AND mp.estado='descartada' ORDER BY mp.id DESC LIMIT 1)";
        $pp = "(SELECT mp2.estado FROM mesa_estados mp2 WHERE mp2.cotizacion_id=c.id AND mp2.area='postura' AND mp2.estado<>'descartada' ORDER BY mp2.id DESC LIMIT 1)";
        try {
            $rows = DB::query(
                "SELECT d.numero, d.total, d.cliente, MAX(d.sin_cita) AS sin_cita, MAX(d.rapido) AS rapido, MAX(d.hot) AS hot,
                        MAX(d.razon) AS razon, MAX(d.postura) AS postura, MAX(d.visitas) AS visitas, MAX(d.dias_vista) AS dias_vista
                 FROM (
                    SELECT c.id AS cid, c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente,
                           $nc AS sin_cita, (DATEDIFF(m.created_at, c.created_at) <= $rapido_dias) AS rapido, $hot AS hot, $rz AS razon, $pp AS postura,
                           c.visitas AS visitas, DATEDIFF(NOW(), c.ultima_vista_at) AS dias_vista
                      FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                      LEFT JOIN clientes cl ON cl.id=c.cliente_id
                     WHERE m.empresa_id=? AND m.area='postura' AND m.estado='descartada'
                       AND COALESCE(c.vendedor_id,c.usuario_id)=?
                       AND m.created_at >= NOW() - INTERVAL $win DAY AND c.created_at >= NOW() - INTERVAL $win DAY
                       -- DESCARTADA VIGENTE (misma regla que Mesa::armar y
                       -- Mesa::reporte): la ÚLTIMA postura debe seguir siendo
                       -- 'descartada' y no puede haber un 👍 posterior que la
                       -- anule. mesa_estados es insert-only: sin esto, un
                       -- descarte CORREGIDO —o una descartada que revivió y se
                       -- VENDIÓ— seguía contando como descarte y pintaba al
                       -- asesor rojo mientras la Mesa la celebraba como
                       -- recuperada.
                       AND m.id = (SELECT MAX(mv.id) FROM mesa_estados mv
                                    WHERE mv.cotizacion_id = m.cotizacion_id AND mv.area = 'postura')
                       AND NOT EXISTS (SELECT 1 FROM mesa_estados mfv
                                        WHERE mfv.cotizacion_id = m.cotizacion_id
                                          AND mfv.area = 'feedback' AND mfv.estado = 'con_interes'
                                          AND mfv.id > m.id)
                       AND NOT EXISTS (SELECT 1 FROM radar_feedback rfv
                                        WHERE rfv.cotizacion_id = m.cotizacion_id
                                          AND rfv.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                          AND rfv.tipo = 'con_interes' AND rfv.updated_at > m.created_at)
                    UNION
                    SELECT c.id AS cid, c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente,
                           $nc AS sin_cita, (DATEDIFF(rf.updated_at, c.created_at) <= $rapido_dias) AS rapido, $hot AS hot, $rz AS razon, $pp AS postura,
                           c.visitas AS visitas, DATEDIFF(NOW(), c.ultima_vista_at) AS dias_vista
                      FROM radar_feedback rf JOIN cotizaciones c ON c.id=rf.cotizacion_id
                      LEFT JOIN clientes cl ON cl.id=c.cliente_id
                     WHERE rf.empresa_id=? AND rf.tipo='sin_interes'
                       AND COALESCE(c.vendedor_id,c.usuario_id)=?
                       AND rf.updated_at >= NOW() - INTERVAL $win DAY AND c.created_at >= NOW() - INTERVAL $win DAY
                 ) d
                 GROUP BY d.cid, d.numero, d.total, d.cliente
                 ORDER BY d.total DESC",
                [$eid, $uid, $eid, $uid]);
            $o['n'] = count($rows);
            foreach ($rows as $x) {
                if ((int)$x['sin_cita']) $o['sincita']++;
                if ((int)$x['rapido'])   $o['rapido']++;
                if ((int)$x['hot'])      $o['hot']++;
                $es_precio = ($x['razon'] === 'precio' || $x['postura'] === 'objecion_precio');
                if ($es_precio) { $o['precio']++; if ((int)$x['hot']) $o['precio_hot']++; }
                elseif ((int)$x['hot']) $o['hot_noprecio']++;
                // "En el aire" / "para después" = objeción que nunca se destapó.
                if (!$es_precio && (($x['razon'] ?? '') === 'despues'
                    || in_array($x['postura'] ?? '', ['en_el_aire','decidiendo'], true))) $o['aire']++;
                if (count($o['casos']) < 10)
                    $o['casos'][] = ['numero'=>$x['numero'],'cliente'=>$x['cliente'],'total'=>(float)$x['total'],
                        'sin_cita'=>(int)$x['sin_cita'],'rapido'=>(int)$x['rapido'],'hot'=>(int)$x['hot'],
                        'razon'=>$x['razon'],'postura'=>$x['postura'],'es_precio'=>$es_precio ? 1 : 0,
                        'visitas'=>(int)$x['visitas'], 'dias_vista'=>($x['dias_vista'] === null ? null : (int)$x['dias_vista'])];
            }
        } catch (Throwable $e) {}
        return $o;
    }

    private static function _ventas(int $eid, int $uid, int $win): array
    {
        $o = ['cierres'=>0,'monto'=>0.0,'con_dto'=>0,'sin_dto'=>0];
        try {
            $r = DB::row(
                "SELECT COUNT(*) AS n, COALESCE(SUM(v.total),0) AS monto,
                        SUM(CASE WHEN COALESCE(c.cupon_pct,0)>0 OR COALESCE(c.descuento_auto_pct,0)>0 THEN 1 ELSE 0 END) AS con_dto,
                        SUM(CASE WHEN COALESCE(c.cupon_pct,0)=0 AND COALESCE(c.descuento_auto_pct,0)=0 THEN 1 ELSE 0 END) AS sin_dto
                   FROM ventas v LEFT JOIN cotizaciones c ON c.id=v.cotizacion_id
                  WHERE v.empresa_id=? AND v.estado<>'cancelada' AND v.pagado>0 AND v.total>0
                    AND COALESCE(v.vendedor_id,v.usuario_id,c.vendedor_id,c.usuario_id)=?
                    AND v.created_at >= NOW() - INTERVAL $win DAY",
                [$eid, $uid]);
            $o['cierres']=(int)($r['n']??0); $o['monto']=(float)($r['monto']??0);
            $o['con_dto']=(int)($r['con_dto']??0); $o['sin_dto']=(int)($r['sin_dto']??0);
        } catch (Throwable $e) {}
        return $o;
    }

    private static function _mesa(int $eid, int $uid): array
    {
        $m = ['vencidas'=>0,'vence_hoy'=>0,'casos'=>[]];
        try {
            $r = Mesa::armar($eid, $uid, true); $res = $r['resumen'] ?? [];
            $m['vencidas']=(int)($res['vencidas']??0); $m['vence_hoy']=(int)($res['vence_hoy']??0);
            $casos = [];
            foreach (($r['rows'] ?? []) as $row) {
                if (($row['seguimiento']['estado'] ?? '') !== 'vencida') continue;
                $casos[] = ['numero'=>$row['numero']??'','cliente'=>$row['cliente']??'—','total'=>(float)($row['total']??0),'dias'=>(int)($row['seguimiento']['dias']??0)];
            }
            usort($casos, fn($x,$y) => ($y['dias']*max($y['total'],1)) <=> ($x['dias']*max($x['total'],1)));
            $m['casos'] = array_slice($casos, 0, 4);
        } catch (Throwable $e) {}
        return $m;
    }

    private static function _radar(int $eid, int $uid, int $win): array
    {
        $hot = Mesa::hot_sql();      // fuente única (Mesa::HOT)
        $r = ['calientes'=>0,'sin_feedback'=>0,'casos'=>[]];
        try {
            $r['calientes'] = (int)DB::val(
                "SELECT COUNT(DISTINCT bt.cotizacion_id) FROM bucket_transitions bt JOIN cotizaciones c ON c.id=bt.cotizacion_id
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=? AND bt.bucket_nuevo IN $hot
                    AND bt.created_at >= NOW() - INTERVAL $win DAY AND c.suspendida=0",
                [$uid, $eid]);
        } catch (Throwable $e) {}
        try {
            $rows = DB::query(
                "SELECT DISTINCT c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente
                   FROM bucket_transitions bt JOIN cotizaciones c ON c.id=bt.cotizacion_id
                   LEFT JOIN clientes cl ON cl.id=c.cliente_id
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=? AND bt.bucket_nuevo IN $hot
                    AND bt.created_at >= NOW() - INTERVAL $win DAY
                    AND c.estado IN ('enviada','vista') AND c.suspendida=0
                    AND NOT EXISTS (SELECT 1 FROM radar_feedback rf WHERE rf.cotizacion_id=c.id AND rf.usuario_id=COALESCE(c.vendedor_id,c.usuario_id))
                  ORDER BY c.total DESC",
                [$uid, $eid]);
            $r['sin_feedback'] = count($rows);
            $r['casos'] = array_slice(array_map(fn($x)=>['numero'=>$x['numero'],'cliente'=>$x['cliente'],'total'=>(float)$x['total']], $rows), 0, 4);
        } catch (Throwable $e) {}
        return $r;
    }

    // ═══════════ Composición (anclada a la tarjeta) ═══════════
    private static function _componer(array $d): array
    {
        $card = $d['card']; $de = $d['desc']; $ve = $d['vent']; $m = $d['mesa']; $rd = $d['radar'];

        // Embudo = los 5 pilares EXACTOS de la tarjeta (estado + texto).
        $emb = [];
        if ($card) {
            $emb[] = ['estado'=>$card['conv_estado'],  'label'=>'Conversión',  'txt'=>$card['conv_txt']];
            $emb[] = ['estado'=>$card['desc_estado'],  'label'=>'Descartadas', 'txt'=>$card['desc_txt']];
            $emb[] = ['estado'=>$card['citas_estado'], 'label'=>'Citas',       'txt'=>$card['citas_txt']];
            $emb[] = ['estado'=>$card['venc_estado'],  'label'=>'Seguimiento', 'txt'=>$card['venc_txt']];
            $emb[] = ['estado'=>$card['cont_estado'],  'label'=>'Contacto',    'txt'=>$card['cont_txt']];
        }

        // Pilares en problema (mismos de la tarjeta)
        $rojos = $card ? array_filter($emb, fn($p) => $p['estado'] === 'rojo') : [];
        $ambar = $card ? array_filter($emb, fn($p) => $p['estado'] === 'amarillo') : [];

        // ── Resumen: el veredicto de la tarjeta, tal cual ──
        $res = [];
        if ($card && !empty($card['flag'])) $res[] = "No sigue el proceso — falla en " . $card['flag_pilares'] . ".";
        if ($card) foreach (array_merge($rojos, $ambar) as $p) $res[] = ucfirst($p['label']) . ": " . $p['txt'] . ".";
        if (!$res) $res[] = ($card ? "Va al corriente — sin pilares en rojo." : "Sin datos de Ritmo para este asesor.");

        // ── Embudo (para render con color) — se pasa aparte ──

        // ── Calidad de cierre (descuentos) ──
        $cal = [];
        if ($ve['cierres'] === 0) $cal[] = "Aún no cierra en la ventana — sin ventas no hay calidad que medir.";
        else {
            if ($ve['con_dto'] > $ve['sin_dto']) $cal[] = "De {$ve['cierres']} cierres, {$ve['con_dto']} con descuento. Está comprando la venta con precio, no con valor.";
            elseif ($ve['con_dto'] > 0) $cal[] = "{$ve['con_dto']} de {$ve['cierres']} con descuento — vigílalo, que no se haga costumbre.";
            else $cal[] = "Cerró {$ve['cierres']} sin dar descuentos — vende por valor. Muy bien.";
            // Ticket de ESTOS cierres (la ventana), no usuario_score.ticket_promedio
            // — esa columna guarda el promedio de la EMPRESA, igual para todos los
            // asesores, y aquí se leía como si fuera personal.
            if ($ve['cierres'] > 0 && $ve['monto'] > 0)
                $cal[] = "Ticket promedio de esos cierres " . self::_money($ve['monto'] / $ve['cierres']) . ".";
        }

        // ── Radar ──
        $rad = [];
        if ($rd['calientes'] === 0) $rad[] = "Sin señales calientes del Radar en la ventana.";
        else {
            $rad[] = "{$rd['calientes']} clientes se pusieron calientes en la ventana.";
            if ($de['hot_noprecio'] > 0) $rad[] = "Descartó {$de['hot_noprecio']} de esos {$rd['calientes']} calientes — tiró " . ($de['hot_noprecio'] === 1 ? "un lead" : "leads") . " con señal de compra.";
            if ($rd['sin_feedback'] > 0) {
                $rad[] = $rd['sin_feedback'] === 1
                    ? "1 caliente sin revisar (ni la tocó):"
                    : "{$rd['sin_feedback']} calientes sin revisar (ni las tocó):";
                foreach ($rd['casos'] as $c) $rad[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']);
            }
            if ($de['hot_noprecio'] === 0 && $rd['sin_feedback'] === 0) $rad[] = "Trabajó todas sus calientes — bien.";
        }

        // ── Casos concretos de los focos ──
        $casos = [];
        if ($m['vencidas'] > 0 && $m['casos']) {
            $casos[] = "Vencidas más urgentes:";
            foreach ($m['casos'] as $c) $casos[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']) . " · {$c['dias']}d vencida";
        }
        $RZ = ['precio'=>'objeción de precio','competencia'=>'se fue con competencia','despues'=>'lo dejó para después',
               'no_responde'=>'no responde','no_comprador'=>'no era comprador','otro'=>'otro motivo'];
        $PP = ['objecion_precio'=>'objeción de precio','pidio_cambios'=>'pidió cambios','en_el_aire'=>'quedó en el aire',
               'decidiendo'=>'estaba decidiendo','sin_compromiso'=>'sin compromiso'];
        $tags = function ($c) {
            $t = [];
            if ($c['hot'])      $t[] = 'estaba caliente';
            if ($c['sin_cita']) $t[] = 'sin cita';
            if ($c['rapido'])   $t[] = 'muy rápido';
            return $t ? ' · ' . implode(', ', $t) : '';
        };
        // Contexto del Radar: cuántas veces lo vio el cliente y hace cuánto.
        $vistas = function ($c) {
            $v = (int)($c['visitas'] ?? 0);
            if ($v <= 0) return '';
            $dv = $c['dias_vista'] ?? null;
            $cuando = ($dv === null) ? '' : ($dv <= 0 ? ', última hoy' : ", última hace {$dv}d");
            return ' · ' . $v . ($v === 1 ? ' vista' : ' vistas') . $cuando;
        };
        $desc_rojo = $card && ($card['desc_estado'] === 'rojo' || $card['desc_estado'] === 'amarillo');

        // Casos para revisar: descartes SIN objeción de precio (esos van aparte).
        if ($desc_rojo) {
            $noprecio = array_values(array_filter($de['casos'], fn($c) => !$c['es_precio']));
            if ($noprecio) {
                $casos[] = "Descartadas de mayor monto:";
                foreach (array_slice($noprecio, 0, 4) as $c) {
                    if (!empty($c['razon']) && isset($RZ[$c['razon']]))         $why = $RZ[$c['razon']];
                    elseif (!empty($c['postura']) && isset($PP[$c['postura']]))  $why = $PP[$c['postura']];
                    elseif (empty($c['razon']))                                  $why = 'descartada en el Radar (👎)';
                    else                                                         $why = $c['razon'];
                    $casos[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']) . " · " . $why . $vistas($c) . $tags($c);
                }
            }
        }

        // ── Objeción por precio (sección aparte: habilidad de comunicar valor) ──
        $prc = [];
        if ($de['precio'] > 0) {
            $prc[] = "{$de['precio']} de sus {$de['n']} descartes se cayeron por objeción de precio"
                   . ($de['precio_hot'] > 0 ? " ({$de['precio_hot']} estaban calientes)" : "") . ".";
            $solop = array_values(array_filter($de['casos'], fn($c) => $c['es_precio']));
            foreach (array_slice($solop, 0, 4) as $c)
                $prc[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']) . $vistas($c) . $tags($c);
            $prc[] = "Perder por precio no es lo mismo que no saber defenderlo — conviene revisar si fue precio real o comunicación de valor.";
        }

        // ── Consejo del Director: recomendaciones IMPERSONALES (el reporte puede
        //   leerlo el propio asesor — sin "con él / conmigo", sin tono de guion). ──
        $cons = [];
        if ($card && !empty($card['motivo'])) $cons[] = $card['motivo'];
        // Tono duro de "tiró leads calientes" solo si es dumper real (Descartadas rojo).
        if ($card && $card['desc_estado'] === 'rojo' && $de['hot_noprecio'] > 0)
            $cons[] = "Peor aún: {$de['hot_noprecio']} de las que descartó estaban calientes en el Radar — leads con señal de compra que no deben irse a la basura. Es lo primero que hay que frenar.";
        // Caso concreto de lead caliente descartado (recomendación, no pregunta).
        $hc = null; foreach ($de['casos'] as $c) if ($c['hot'] && !$c['es_precio']) { $hc = $c; break; }
        if ($hc) $cons[] = "{$hc['cliente']} (#{$hc['numero']}) estaba caliente en el Radar al descartarse — los calientes conviene retomarlos mientras el cliente sigue viendo la cotización.";
        if ($de['precio'] > 0) $cons[] = "{$de['precio']} se cayeron por objeción de precio" . ($de['precio_hot'] > 0 ? " ({$de['precio_hot']} calientes)" : "") . ". Conviene revisar si fue precio real o comunicación de valor — no darlo por hecho.";
        if ($ve['cierres'] > 0 && $ve['con_dto'] > $ve['sin_dto']) $cons[] = "Cierra regalando descuento ({$ve['con_dto']} de {$ve['cierres']}) — conviene cuidar el margen defendiendo el precio.";
        if ($rd['sin_feedback'] >= 1) $cons[] = ($rd['sin_feedback'] === 1 ? "1 caliente del Radar sin marcar" : "{$rd['sin_feedback']} calientes del Radar sin marcar") . " — son de las ventas más fáciles, conviene atenderlas.";
        if ($card && $card['citas_estado'] === 'rojo') $cons[] = "Ni una cita en 7 días: {$card['citas_txt']}. Con el embudo parado, lo que se deja de sembrar hoy falta en el cierre del mes.";
        elseif ($card && $card['citas_estado'] === 'amarillo') $cons[] = "Bajó el ritmo de citas: {$card['citas_txt']} — conviene recuperarlo.";
        if (!$cons) $cons[] = "Va sólido. Para subir: más volumen o mejor ticket, sin bajar el ritmo de citas.";

        // ── Meta de la semana (acciones, impersonales) ──
        $meta = [];
        if ($m['vencidas'] > 0) $meta[]="Poner al día las {$m['vencidas']} vencidas antes del viernes.";
        if ($card && $card['desc_estado'] === 'rojo') $meta[]="No descartar ninguna sin al menos 1 intento de cita.";
        if ($card && $card['conv_estado'] === 'rojo') $meta[]="Cerrar al menos 1 venta esta semana.";
        if ($de['precio'] > 0) $meta[]="Trabajar una respuesta a la objeción de precio para la próxima cotización cara.";
        if ($rd['sin_feedback'] >= 1) $meta[]="Marcar (👍/👎) las calientes del Radar sin revisar.";
        if ($ve['cierres'] > 0 && $ve['con_dto'] > $ve['sin_dto']) $meta[]="Cerrar la próxima venta sin descuento.";
        if ($card && in_array($card['citas_estado'] ?? '', ['rojo','amarillo'], true))
            $meta[] = ($card['citas_estado'] === 'rojo')
                ? "Agendar al menos 1 cita esta semana — el embudo no puede quedar en cero."
                : "Recuperar el ritmo de citas de la semana.";
        if (!$meta) $meta[]="Sostener el ritmo: mesa al día y seguir cerrando.";

        return ['resumen'=>$res,'embudo'=>$emb,'calidad'=>$cal,'radar'=>$rad,'casos'=>$casos,'precio'=>$prc,'consejo'=>$cons,'meta'=>$meta];
    }

    private static function _money(float $n): string { return '$' . number_format($n, 0, '.', ','); }

    // ═══════════ Render HTML ═══════════
    public static function render(array $d): string
    {
        $s = $d['secciones']; $sc = $d['score'];
        $col = ['rojo'=>'#dc2626','amarillo'=>'#c07d16','verde'=>'#1f9d57','gris'=>'#9aa1aa'];
        $h  = '<div class="rr">';
        $h .= '<div class="rr-hd"><div><div class="rr-k">Reporte de asesor · ventana ' . (int)$d['win'] . ' días</div>'
            . '<div class="rr-name">' . e($d['nombre']) . '</div></div>';
        if ($sc) $h .= '<div class="rr-score"><div class="rr-sn">' . (int)$sc['score'] . '</div><div class="rr-sl">' . e(ucfirst($sc['nivel'])) . '</div></div>';
        $h .= '</div>';
        if ($sc) {
            $h .= '<div class="rr-dims">';
            foreach ($sc['dim'] as $lab => $v) $h .= '<span class="rr-dim"><b>' . e($lab) . '</b> ' . (int)round($v*100) . '%</span>';
            $h .= '</div><div class="rr-note">Los pilares y el veredicto son los MISMOS de la tarjeta de Ritmo (misma ventana). El score es la ventana estándar de 15 días.</div>';
        }

        // 💡 Tip de la semana (técnica de ventas rotada, personalizada)
        if (!empty($d['tip']['texto'])) {
            $h .= '<div class="rr-tip"><div class="rr-tip-h">💡 Tip de la semana</div>'
                . '<div class="rr-tip-b">' . e($d['tip']['texto']) . '</div></div>';
        }

        $sec = function (string $t, array $items, string $cls = '') {
            if (!$items) return '';
            $o = '<div class="rr-sec ' . $cls . '"><div class="rr-st">' . e($t) . '</div><ul class="rr-list">';
            foreach ($items as $it) {
                $sub = is_string($it) && str_starts_with($it, '  ');
                $o .= '<li' . ($sub ? ' class="rr-sub"' : '') . '>' . e(is_string($it) ? trim($it) : '') . '</li>';
            }
            return $o . '</ul></div>';
        };

        // Embudo con color por pilar (igual que la tarjeta)
        $emb = '';
        if (!empty($s['embudo'])) {
            $emb = '<div class="rr-sec"><div class="rr-st">El embudo</div>';
            foreach ($s['embudo'] as $p) {
                $c = $col[$p['estado']] ?? '#9aa1aa';
                $emb .= '<div class="rr-pil"><span class="rr-dot" style="background:' . $c . '"></span>'
                     . '<b>' . e($p['label']) . '</b> <span style="color:' . ($p['estado']==='verde'||$p['estado']==='gris'?'var(--t2)':$c) . '">' . e($p['txt']) . '</span></div>';
            }
            $emb .= '</div>';
        }

        $h .= $sec('Resumen', $s['resumen']);
        $h .= $emb;
        $h .= $sec('Casos para revisar', $s['casos']);
        $h .= $sec('Objeción por precio', $s['precio']);
        $h .= $sec('Calidad de cierre', $s['calidad']);
        $h .= $sec('Radar', $s['radar']);
        $h .= $sec('Consejo del Director', $s['consejo'], 'rr-consejo');
        $h .= $sec('Meta de la semana', $s['meta']);
        $h .= '<div class="rr-foot">Generado el ' . date('d/M/Y H:i') . ' · ventana de ' . (int)$d['win']
            . ' días (' . date('d/M', strtotime('-' . (int)$d['win'] . ' days')) . ' a ' . date('d/M') . ')'
            . '<br>Datos reales de la Mesa, el Radar y las ventas. Cada cifra sale del sistema — nada inventado.</div></div>';
        return $h;
    }
}
