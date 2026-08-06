<?php
// ============================================================
//  RitmoReporte — Reporte del Director de Ventas por asesor
//  ANCLADO A LA TARJETA (RitmoAsesor): el veredicto, los 5 pilares y
//  sus números son EXACTAMENTE los de la tarjeta de Ritmo. Encima
//  agrega casos concretos, Radar, descuentos, cartera y el consejo.
//  Nunca puede contradecir a la tarjeta (misma fuente, misma ventana).
//
//  Fuentes: RitmoAsesor (pilares) · usuario_score (score) ·
//  Mesa::armar (vencidas) · Mesa::reporte (se-fueron) ·
//  bucket_transitions + radar_feedback (Radar) · ventas (descuentos).
//  Solo lectura. Fact-lint: cada cifra sale del sistema.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoReporte
{
    public static function generar(int $empresa_id, int $asesor_id, string $desde = '', string $hasta = ''): array
    {
        $nombre = (string) DB::val("SELECT nombre FROM usuarios WHERE id=? AND empresa_id=?", [$asesor_id, $empresa_id]);
        if ($nombre === '') $nombre = "Asesor #{$asesor_id}";

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

        $d = [
            'nombre' => $nombre, 'win' => $win, 'card' => $card,
            'score'  => self::_score($asesor_id, $empresa_id),
            'desc'   => self::_descartes($empresa_id, $asesor_id, $win, $rapido_dias),
            'vent'   => self::_ventas($empresa_id, $asesor_id, $win),
            'mesa'   => self::_mesa($empresa_id, $asesor_id),
            'radar'  => self::_radar($empresa_id, $asesor_id, $win),
            'cartera'=> self::_cartera($empresa_id, $asesor_id, $win),
        ];
        $d['secciones'] = self::_componer($d);
        $d['html'] = self::render($d);
        return $d;
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
        $o = ['n'=>0,'sincita'=>0,'rapido'=>0,'casos'=>[]];
        $nc = "(NOT EXISTS (SELECT 1 FROM mesa_estados mc WHERE mc.cotizacion_id=c.id AND mc.area='compromiso' AND mc.estado='nos_citamos'))";
        try {
            $rows = DB::query(
                "SELECT d.numero, d.total, d.cliente, MAX(d.sin_cita) AS sin_cita, MAX(d.rapido) AS rapido
                 FROM (
                    SELECT c.id AS cid, c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente,
                           $nc AS sin_cita, (DATEDIFF(m.created_at, c.created_at) <= $rapido_dias) AS rapido
                      FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                      LEFT JOIN clientes cl ON cl.id=c.cliente_id
                     WHERE m.empresa_id=? AND m.area='postura' AND m.estado='descartada'
                       AND COALESCE(c.vendedor_id,c.usuario_id)=?
                       AND m.created_at >= NOW() - INTERVAL $win DAY AND c.created_at >= NOW() - INTERVAL $win DAY
                    UNION
                    SELECT c.id AS cid, c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente,
                           $nc AS sin_cita, (DATEDIFF(rf.updated_at, c.created_at) <= $rapido_dias) AS rapido
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
                if (count($o['casos']) < 4)
                    $o['casos'][] = ['numero'=>$x['numero'],'cliente'=>$x['cliente'],'total'=>(float)$x['total'],'sin_cita'=>(int)$x['sin_cita'],'rapido'=>(int)$x['rapido']];
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
        $hot = "('probable_cierre','onfire','inminente','validando_precio','prediccion_alta','lectura_comprometida')";
        $r = ['calientes'=>0,'descarto_cal'=>0,'sin_feedback'=>0,'casos'=>[]];
        try {
            $r['calientes'] = (int)DB::val(
                "SELECT COUNT(DISTINCT bt.cotizacion_id) FROM bucket_transitions bt JOIN cotizaciones c ON c.id=bt.cotizacion_id
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=? AND bt.bucket_nuevo IN $hot
                    AND bt.created_at >= NOW() - INTERVAL $win DAY AND c.suspendida=0",
                [$uid, $eid]);
        } catch (Throwable $e) {}
        try {
            // Tiró leads con señal de compra: calientes que ADEMÁS descartó (👎 o postura).
            $r['descarto_cal'] = (int)DB::val(
                "SELECT COUNT(DISTINCT c.id) FROM cotizaciones c
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=?
                    AND EXISTS (SELECT 1 FROM bucket_transitions bt WHERE bt.cotizacion_id=c.id AND bt.bucket_nuevo IN $hot AND bt.created_at >= NOW() - INTERVAL $win DAY)
                    AND ( EXISTS (SELECT 1 FROM radar_feedback rf WHERE rf.cotizacion_id=c.id AND rf.usuario_id=COALESCE(c.vendedor_id,c.usuario_id) AND rf.tipo='sin_interes')
                       OR EXISTS (SELECT 1 FROM mesa_estados mp WHERE mp.cotizacion_id=c.id AND mp.area='postura' AND mp.estado='descartada') )",
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

    private static function _cartera(int $eid, int $uid, int $win): array
    {
        $c = ['se_fueron'=>0,'monto'=>0.0];
        try {
            $rep = Mesa::reporte($eid, $win); $a = $rep['asesores'][$uid] ?? null;
            if ($a) { $c['se_fueron']=(int)($a['se_fueron']??0); $c['monto']=(float)($a['monto_se_fueron']??0); }
        } catch (Throwable $e) {}
        return $c;
    }

    // ═══════════ Composición (anclada a la tarjeta) ═══════════
    private static function _componer(array $d): array
    {
        $card = $d['card']; $de = $d['desc']; $ve = $d['vent']; $m = $d['mesa']; $rd = $d['radar']; $ca = $d['cartera'];

        // Embudo = los 5 pilares EXACTOS de la tarjeta (estado + texto).
        $emb = [];
        if ($card) {
            $emb[] = ['estado'=>$card['conv_estado'],  'label'=>'Cierre',      'txt'=>$card['conv_txt']];
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
        if ($ca['se_fueron'] > 0) $res[] = "Además, {$ca['se_fueron']} cotizaciones pasaron el ciclo sin ningún toque suyo (" . self::_money($ca['monto']) . ").";
        if (!$res) $res[] = ($card ? "Va al corriente — sin pilares en rojo." : "Sin datos de Ritmo para este asesor.");

        // ── Embudo (para render con color) — se pasa aparte ──

        // ── Calidad de cierre (descuentos) ──
        $cal = [];
        if ($ve['cierres'] === 0) $cal[] = "Aún no cierra en la ventana — sin ventas no hay calidad que medir.";
        else {
            if ($ve['con_dto'] > $ve['sin_dto']) $cal[] = "De {$ve['cierres']} cierres, {$ve['con_dto']} con descuento. Está comprando la venta con precio, no con valor.";
            elseif ($ve['con_dto'] > 0) $cal[] = "{$ve['con_dto']} de {$ve['cierres']} con descuento — vigílalo, que no se haga costumbre.";
            else $cal[] = "Cerró {$ve['cierres']} sin dar descuentos — vende por valor. Muy bien.";
            if (($d['score']['ticket'] ?? 0) > 0) $cal[] = "Ticket promedio " . self::_money($d['score']['ticket']) . ".";
        }

        // ── Radar ──
        $rad = [];
        if ($rd['calientes'] === 0) $rad[] = "Sin señales calientes del Radar en la ventana.";
        else {
            $rad[] = "{$rd['calientes']} clientes se pusieron calientes en la ventana.";
            if ($rd['descarto_cal'] > 0) $rad[] = "Descartó {$rd['descarto_cal']} que el Radar tenía caliente — tiró leads con señal de compra.";
            if ($rd['sin_feedback'] > 0) {
                $rad[] = "{$rd['sin_feedback']} calientes sin revisar (ni las tocó):";
                foreach ($rd['casos'] as $c) $rad[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']);
            }
            if ($rd['descarto_cal'] === 0 && $rd['sin_feedback'] === 0) $rad[] = "Trabajó todas sus calientes — bien.";
        }

        // ── Casos concretos de los focos ──
        $casos = [];
        if ($m['vencidas'] > 0 && $m['casos']) {
            $casos[] = "Vencidas más urgentes:";
            foreach ($m['casos'] as $c) $casos[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']) . " · {$c['dias']}d vencida";
        }
        if (($card && ($card['desc_estado'] === 'rojo' || $card['desc_estado'] === 'amarillo')) && $de['casos']) {
            $casos[] = "Descartadas de mayor monto:";
            foreach ($de['casos'] as $c) {
                $tag = [];
                if ($c['sin_cita']) $tag[] = 'sin cita';
                if ($c['rapido'])   $tag[] = 'muy rápido';
                $casos[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']) . ($tag ? ' · ' . implode(', ', $tag) : '');
            }
        }

        // ── Cartera (solo el HECHO: pasaron el ciclo sin ningún toque suyo) ──
        $car = [];
        if ($ca['se_fueron'] > 0) $car[] = "{$ca['se_fueron']} cotizaciones pasaron el ciclo sin ningún toque suyo (ni captura, ni feedback, ni edición): " . self::_money($ca['monto']) . ".";

        // ── Consejo del Director: el motivo de la tarjeta + refuerzos ──
        $cons = [];
        if ($card && !empty($card['motivo'])) $cons[] = $card['motivo'];
        if ($rd['descarto_cal'] > 0) $cons[] = "Peor aún: {$rd['descarto_cal']} de las que tiró estaban calientes en el Radar — mandó a la basura leads con señal de compra. Eso es lo primero que hay que frenar.";
        if ($ve['cierres'] > 0 && $ve['con_dto'] > $ve['sin_dto']) $cons[] = "Y cuida el margen: cierra regalando descuento ({$ve['con_dto']} de {$ve['cierres']}). Enséñale a defender el precio.";
        if ($rd['sin_feedback'] >= 2) $cons[] = "Tiene {$rd['sin_feedback']} calientes sin revisar en el Radar — son sus ventas más fáciles, siéntate con él a trabajarlas hoy.";
        if ($ca['se_fueron'] >= 5) $cons[] = "Hay {$ca['se_fueron']} cotizaciones que pasaron el ciclo sin ningún toque suyo (" . self::_money($ca['monto']) . ") — revísenlas juntos.";
        if (!$cons) $cons[] = "Va sólido. Súbele la vara: más volumen o mejor ticket, y que no baje el ritmo de citas.";

        // ── Guion 1:1 ──
        $g = [];
        if ($m['vencidas'] > 0 && $m['casos']) { $c0=$m['casos'][0]; $g[]="\"Ábreme la #{$c0['numero']} de {$c0['cliente']} — lleva {$c0['dias']} días vencida. ¿Qué pasó?\""; }
        if ($card && $card['desc_estado'] === 'rojo') $g[]="\"¿Por qué descartas antes de agendar? {$de['sincita']} de tus {$de['n']} nunca llegaron a cita.\"";
        if ($card && $card['conv_estado'] === 'rojo') $g[]="\"Trabajaste varias y no has cerrado — muéstrame dónde se te caen.\"";
        if ($ve['cierres'] > 0 && $ve['con_dto'] > $ve['sin_dto']) $g[]="\"{$ve['con_dto']} de tus {$ve['cierres']} ventas fueron con descuento — ¿por qué necesitaste bajar el precio?\"";
        if ($rd['sin_feedback'] >= 2) $g[]="\"Tienes {$rd['sin_feedback']} calientes sin marcar — revísalas conmigo ahorita.\"";
        if ($card && $card['cont_estado'] !== 'verde' && $card['cont_estado'] !== 'gris') $g[]="\"" . $card['cont_txt'] . " — ¿a qué hora y por qué medio les marcas?\"";
        if (!$g) $g[]="\"Vas bien — ¿qué necesitas de mí para cerrar más rápido?\"";

        // ── Meta ──
        $meta = [];
        if ($m['vencidas'] > 0) $meta[]="Poner al día las {$m['vencidas']} vencidas antes del viernes.";
        if ($card && $card['desc_estado'] === 'rojo') $meta[]="No descartar ninguna sin al menos 1 intento de cita.";
        if ($card && $card['conv_estado'] === 'rojo') $meta[]="Cerrar al menos 1 venta esta semana.";
        if ($rd['sin_feedback'] >= 2) $meta[]="Marcar (👍/👎) las {$rd['sin_feedback']} calientes del Radar.";
        if ($ve['cierres'] > 0 && $ve['con_dto'] > $ve['sin_dto']) $meta[]="Cerrar la próxima venta sin descuento.";
        if (!$meta) $meta[]="Sostener el ritmo: mesa al día y seguir cerrando.";

        return ['resumen'=>$res,'embudo'=>$emb,'calidad'=>$cal,'radar'=>$rad,'casos'=>$casos,'cartera'=>$car,'consejo'=>$cons,'guion'=>$g,'meta'=>$meta];
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
        $h .= $sec('Calidad de cierre', $s['calidad']);
        $h .= $sec('Radar', $s['radar']);
        $h .= $sec('Cartera en riesgo', $s['cartera']);
        $h .= $sec('Consejo del Director', $s['consejo'], 'rr-consejo');
        $h .= $sec('Guion para el 1:1', $s['guion']);
        $h .= $sec('Meta de la semana', $s['meta']);
        $h .= '<div class="rr-foot">Datos reales de la Mesa, el Radar y las ventas. Cada cifra sale del sistema — nada inventado.</div></div>';
        return $h;
    }
}
