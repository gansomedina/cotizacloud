<?php
// ============================================================
//  RitmoReporte — Reporte del Director de Ventas por asesor
//  Cruza TODA la data real (sin LLM, fact-lint, cero PII afuera):
//    · Ritmo / actividad del rango   (cierres, descuentos, citas, descartes, contacto)
//    · Score + debug                 usuario_score
//    · Mesa (reloj vivo)             Mesa::armar (read-only) — vencidas
//    · Cartera                       Mesa::reporte — se le fueron
//    · Radar + historial             bucket_transitions + radar_feedback
//
//  Principio: SOLO se juzga lo que YA NO TIENE REMEDIO. Un lead de
//  hoy/ayer no es falla — se aplica una ventana de reacción (madurez).
//  Terminal (vencida, descartada, se-fue) es definitivo por sí mismo;
//  lo aún-fixable (no contestó, caliente sin marcar, no cerró) solo
//  cuenta si ya pasó el tiempo para actuar.
//
//  Salida: Resumen · Embudo · Calidad de cierre · Radar · Cartera ·
//  Consejo del Director · Guion 1:1 · Meta. Solo lectura.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoReporte
{
    public static function generar(int $empresa_id, int $asesor_id, string $desde, string $hasta): array
    {
        $nombre = (string) DB::val("SELECT nombre FROM usuarios WHERE id=? AND empresa_id=?", [$asesor_id, $empresa_id]);
        if ($nombre === '') $nombre = "Asesor #{$asesor_id}";

        $mediana = 5; $p75 = 10;
        try {
            if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
            $c = Radar::ciclo_venta($empresa_id);
            if (!empty($c['auto'])) {
                if (!empty($c['mediana'])) $mediana = max(1, (int)$c['mediana']);
                if (!empty($c['p75']))     $p75     = max(3, (int)$c['p75']);
            }
        } catch (Throwable $e) {}
        $rapido_dias = max(1, (int)floor($mediana / 2));
        $madurez     = max(3, $rapido_dias);   // días para reaccionar → más viejo = ya no tiene remedio
        $cycle       = max(5, $p75);           // ya debió cerrar

        $d = [
            'nombre'  => $nombre, 'desde' => $desde, 'hasta' => $hasta,
            'madurez' => $madurez, 'cycle' => $cycle, 'rapido_dias' => $rapido_dias,
            'score'   => self::_score($asesor_id, $empresa_id),
            'act'     => self::_actividad($empresa_id, $asesor_id, $desde, $hasta, $rapido_dias, $madurez, $cycle),
            'mesa'    => self::_mesa($empresa_id, $asesor_id),
            'radar'   => self::_radar($empresa_id, $asesor_id, $desde, $hasta, $madurez),
            'cartera' => self::_cartera($empresa_id, $asesor_id, $desde, $hasta),
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
            'bonus_cierre' => (int)($r['bonus_cierre'] ?? 0),
        ];
    }

    private static function _actividad(int $eid, int $uid, string $desde, string $hasta, int $rapido_dias, int $madurez, int $cycle): array
    {
        $ini = $desde . ' 00:00:00'; $fin = $hasta . ' 23:59:59';
        $a = ['cierres'=>0,'monto_cierres'=>0.0,'con_dto'=>0,'sin_dto'=>0,
              'trabajo'=>0,'trabajo_maduro'=>0,'citas'=>0,
              'desc'=>0,'sincita'=>0,'rapido'=>0,'desc_casos'=>[],
              'contactados'=>0,'no_conecta'=>0];

        try {
            $r = DB::row(
                "SELECT COUNT(*) AS n, COALESCE(SUM(v.total),0) AS monto,
                        SUM(CASE WHEN COALESCE(c.cupon_pct,0)>0 OR COALESCE(c.descuento_auto_pct,0)>0 THEN 1 ELSE 0 END) AS con_dto,
                        SUM(CASE WHEN COALESCE(c.cupon_pct,0)=0 AND COALESCE(c.descuento_auto_pct,0)=0 THEN 1 ELSE 0 END) AS sin_dto
                   FROM ventas v LEFT JOIN cotizaciones c ON c.id=v.cotizacion_id
                  WHERE v.empresa_id=? AND v.estado<>'cancelada' AND v.pagado>0 AND v.total>0
                    AND COALESCE(v.vendedor_id,v.usuario_id,c.vendedor_id,c.usuario_id)=? AND v.created_at BETWEEN ? AND ?",
                [$eid, $uid, $ini, $fin]);
            $a['cierres']=(int)($r['n']??0); $a['monto_cierres']=(float)($r['monto']??0);
            $a['con_dto']=(int)($r['con_dto']??0); $a['sin_dto']=(int)($r['sin_dto']??0);
        } catch (Throwable $e) {}

        try {
            $a['trabajo'] = (int)DB::val(
                "SELECT COUNT(DISTINCT m.cotizacion_id) FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                  WHERE m.empresa_id=? AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?",
                [$eid, $uid, $ini, $fin]);
            // Maduras: las que YA pasaron el ciclo — debieron cerrar. Juzgar "0 cierres" solo con estas.
            $a['trabajo_maduro'] = (int)DB::val(
                "SELECT COUNT(DISTINCT m.cotizacion_id) FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                  WHERE m.empresa_id=? AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?
                    AND c.created_at <= DATE_SUB(NOW(), INTERVAL $cycle DAY)",
                [$eid, $uid, $ini, $fin]);
        } catch (Throwable $e) {}

        try {
            $a['citas'] = (int)DB::val(
                "SELECT COUNT(DISTINCT m.cotizacion_id) FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                  WHERE m.empresa_id=? AND m.area='compromiso' AND m.estado='nos_citamos'
                    AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?",
                [$eid, $uid, $ini, $fin]);
        } catch (Throwable $e) {}

        // Descartes = terminal (definitivo). Sin filtro de madurez.
        try {
            $rows = DB::query(
                "SELECT c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente,
                        DATEDIFF(m.created_at, c.created_at) AS dias,
                        (NOT EXISTS (SELECT 1 FROM mesa_estados mc WHERE mc.cotizacion_id=c.id AND mc.area='compromiso' AND mc.estado='nos_citamos')) AS sin_cita
                   FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                   LEFT JOIN clientes cl ON cl.id=c.cliente_id
                  WHERE m.empresa_id=? AND m.area='postura' AND m.estado='descartada'
                    AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?
                  ORDER BY c.total DESC",
                [$eid, $uid, $ini, $fin]);
            $a['desc'] = count($rows);
            foreach ($rows as $x) {
                if ((int)$x['sin_cita']) $a['sincita']++;
                if ((int)$x['dias'] <= $rapido_dias) $a['rapido']++;
                if (count($a['desc_casos']) < 4)
                    $a['desc_casos'][] = ['numero'=>$x['numero'],'cliente'=>$x['cliente'],'total'=>(float)$x['total'],'dias'=>(int)$x['dias'],'sin_cita'=>(int)$x['sin_cita']];
            }
        } catch (Throwable $e) {}

        // Contacto — "no le contestaron" SOLO si el último intento ya maduró (>= madurez días).
        try {
            $r = DB::row(
                "SELECT COUNT(*) AS contactados,
                        SUM(CASE WHEN t.sin_hablamos=1 AND t.ult <= DATE_SUB(NOW(), INTERVAL $madurez DAY) THEN 1 ELSE 0 END) AS no_conecta
                   FROM (
                     SELECT m.cotizacion_id, MAX(m.created_at) AS ult,
                            (NOT EXISTS (SELECT 1 FROM mesa_estados h WHERE h.cotizacion_id=m.cotizacion_id AND h.area='contacto' AND h.estado='hablamos')) AS sin_hablamos
                       FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                      WHERE m.empresa_id=? AND m.area='contacto' AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?
                      GROUP BY m.cotizacion_id
                   ) t",
                [$eid, $uid, $ini, $fin]);
            $a['contactados']=(int)($r['contactados']??0); $a['no_conecta']=(int)($r['no_conecta']??0);
        } catch (Throwable $e) {}

        return $a;
    }

    private static function _mesa(int $eid, int $uid): array
    {
        $m = ['vencidas'=>0,'vence_hoy'=>0,'venc_casos'=>[]];
        try {
            $r = Mesa::armar($eid, $uid, true); $res = $r['resumen'] ?? [];
            $m['vencidas']=(int)($res['vencidas']??0); $m['vence_hoy']=(int)($res['vence_hoy']??0);
            $casos = [];
            foreach (($r['rows'] ?? []) as $row) {
                if (($row['seguimiento']['estado'] ?? '') !== 'vencida') continue;
                $casos[] = ['numero'=>$row['numero']??'','cliente'=>$row['cliente']??'—','total'=>(float)($row['total']??0),'dias'=>(int)($row['seguimiento']['dias']??0)];
            }
            usort($casos, fn($x,$y) => ($y['dias']*max($y['total'],1)) <=> ($x['dias']*max($x['total'],1)));
            $m['venc_casos'] = array_slice($casos, 0, 4);
        } catch (Throwable $e) {}
        return $m;
    }

    private static function _radar(int $eid, int $uid, string $desde, string $hasta, int $madurez): array
    {
        $ini = $desde . ' 00:00:00'; $fin = $hasta . ' 23:59:59';
        $hot = "('probable_cierre','onfire','inminente','validando_precio','prediccion_alta','lectura_comprometida')";
        $r = ['calientes'=>0,'marco'=>0,'sin_feedback'=>0,'casos'=>[]];
        try {
            $r['calientes'] = (int)DB::val(
                "SELECT COUNT(DISTINCT bt.cotizacion_id) FROM bucket_transitions bt JOIN cotizaciones c ON c.id=bt.cotizacion_id
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=? AND bt.bucket_nuevo IN $hot
                    AND bt.created_at BETWEEN ? AND ? AND c.suspendida=0",
                [$uid, $eid, $ini, $fin]);
        } catch (Throwable $e) {}
        try {
            $r['marco'] = (int)DB::val(
                "SELECT COUNT(DISTINCT rf.cotizacion_id) FROM radar_feedback rf
                  WHERE rf.usuario_id=? AND rf.empresa_id=? AND rf.updated_at BETWEEN ? AND ?",
                [$uid, $eid, $ini, $fin]);
        } catch (Throwable $e) {}
        // Sin marcar = caliente + sin feedback + el episodio caliente YA maduró (ya no tiene remedio no verla).
        try {
            $rows = DB::query(
                "SELECT DISTINCT c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente
                   FROM bucket_transitions bt JOIN cotizaciones c ON c.id=bt.cotizacion_id
                   LEFT JOIN clientes cl ON cl.id=c.cliente_id
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=? AND bt.bucket_nuevo IN $hot
                    AND bt.created_at BETWEEN ? AND ? AND bt.created_at <= DATE_SUB(NOW(), INTERVAL $madurez DAY)
                    AND c.estado IN ('enviada','vista') AND c.suspendida=0
                    AND NOT EXISTS (SELECT 1 FROM radar_feedback rf WHERE rf.cotizacion_id=c.id AND rf.usuario_id=COALESCE(c.vendedor_id,c.usuario_id))
                  ORDER BY c.total DESC",
                [$uid, $eid, $ini, $fin]);
            $r['sin_feedback'] = count($rows);
            $r['casos'] = array_slice(array_map(fn($x)=>['numero'=>$x['numero'],'cliente'=>$x['cliente'],'total'=>(float)$x['total']], $rows), 0, 4);
        } catch (Throwable $e) {}
        return $r;
    }

    private static function _cartera(int $eid, int $uid, string $desde, string $hasta): array
    {
        $c = ['se_fueron'=>0,'monto_se_fueron'=>0.0];
        try {
            $dias = max(1, (int)round((strtotime($hasta) - strtotime($desde)) / 86400) + 1);
            $rep = Mesa::reporte($eid, $dias); $a = $rep['asesores'][$uid] ?? null;
            if ($a) { $c['se_fueron']=(int)($a['se_fueron']??0); $c['monto_se_fueron']=(float)($a['monto_se_fueron']??0); }
        } catch (Throwable $e) {}
        return $c;
    }

    // ═══════════ Composición (fact-lint, madurez, tono Director) ═══════════
    private static function _componer(array $d): array
    {
        $a=$d['act']; $m=$d['mesa']; $rd=$d['radar']; $ca=$d['cartera'];
        $rate = $a['trabajo']>0 ? (int)round($a['cierres']/$a['trabajo']*100) : 0;
        $noc  = $a['contactados']>0 ? (int)round($a['no_conecta']/$a['contactados']*100) : 0;

        // Focos que YA NO tienen remedio (definidos / maduros)
        $desc_foco  = ($a['desc']>=3 && ($a['sincita']>=(int)ceil($a['desc']*0.5) || $a['rapido']>=3));
        $cont_foco  = ($a['contactados']>=4 && $noc>=35 && $a['no_conecta']>=2);
        $conv_foco  = ($a['cierres']===0 && $a['trabajo_maduro']>=5);
        $venc_foco  = ($m['vencidas']>0);
        $radar_foco = ($rd['sin_feedback']>=2);
        $dto_foco   = ($a['cierres']>0 && $a['con_dto']>$a['sin_dto']);

        // ── Resumen: SOLO cosas ya definidas de los 15 días ──
        $res = [];
        if ($conv_foco) $res[] = "De {$a['trabajo_maduro']} cotizaciones que ya pasaron el ciclo, no cerró ninguna.";
        elseif ($a['cierres']>0) $res[] = "Cerró {$a['cierres']} venta" . ($a['cierres']>1?'s':'') . " (" . self::_money($a['monto_cierres']) . ").";
        if ($venc_foco) $res[] = "Dejó vencer {$m['vencidas']} seguimiento" . ($m['vencidas']>1?'s':'') . " en su mesa.";
        if ($desc_foco) $res[] = "Descartó {$a['desc']} y {$a['sincita']} sin llegar a cita.";
        if ($cont_foco) $res[] = "A {$a['no_conecta']} de {$a['contactados']} nunca les logró contacto ({$noc}%).";
        if ($radar_foco) $res[] = "Ignoró {$rd['sin_feedback']} clientes calientes del Radar.";
        if ($dto_foco) $res[] = "De sus cierres, {$a['con_dto']} fueron con descuento.";
        if (!$res) $res[] = "Sin fallas definitivas en el rango — va al corriente.";

        // ── Embudo completo (siempre los 5 pasos) ──
        $emb = [];
        // Contacto
        if ($a['contactados'] < 4) $emb[] = "Contacto — pocos intentos aún para juzgar.";
        elseif ($noc >= 50)        $emb[] = "Contacto — a {$a['no_conecta']} de {$a['contactados']} no les contesta ({$noc}%). Grave: pierde la mitad en el arranque.";
        elseif ($noc >= 35)        $emb[] = "Contacto — a {$a['no_conecta']} de {$a['contactados']} no les contesta ({$noc}%). Cuídalo: 1 de cada 3 se cae al inicio.";
        else                       $emb[] = "Contacto — le responden " . ($a['contactados']-$a['no_conecta']) . " de {$a['contactados']}. Bien.";
        // Citas
        $emb[] = $a['citas']===0 ? "Citas — no agendó ninguna. Seco: su embudo se está vaciando."
                                 : "Citas — agendó {$a['citas']}. Sabe abrir.";
        // Seguimiento
        $emb[] = $venc_foco ? "Seguimiento — {$m['vencidas']} vencidas" . ($m['vence_hoy']>0?" · {$m['vence_hoy']} vencen hoy":"") . ". Cada día vencido enfría al cliente."
                            : "Seguimiento — al día, 0 vencidas. Bien.";
        // Descartadas
        if ($desc_foco) $emb[] = "Descartadas — descartó {$a['desc']} y {$a['sincita']} sin llegar a cita. Está tirando leads sin trabajarlos.";
        elseif ($a['desc']>0) $emb[] = "Descartadas — descartó {$a['desc']} (descarte sano).";
        else $emb[] = "Descartadas — no descartó nada.";
        // Cierre
        if ($a['cierres']>0) $emb[] = "Cierre — cerró {$a['cierres']} de {$a['trabajo']} ({$rate}%).";
        elseif ($conv_foco)  $emb[] = "Cierre — 0 ventas, y {$a['trabajo_maduro']} ya pasaron el ciclo sin cerrar. Aquí está el problema.";
        else                 $emb[] = "Cierre — 0 aún, pero la mayoría son recientes: dales tiempo.";

        // ── Calidad de cierre (descuentos) ──
        $cal = [];
        if ($a['cierres']===0) $cal[] = "Aún no cierra — sin ventas no hay calidad que medir.";
        else {
            if ($dto_foco) $cal[] = "De {$a['cierres']} cierres, {$a['con_dto']} con descuento. Está comprando la venta con precio, no con valor.";
            elseif ($a['con_dto']>0) $cal[] = "{$a['con_dto']} de {$a['cierres']} con descuento — vigílalo, que no se haga costumbre.";
            else $cal[] = "Cerró sin dar descuentos — vende por valor. Muy bien.";
            if (($d['score']['ticket'] ?? 0) > 0) $cal[] = "Ticket promedio " . self::_money($d['score']['ticket']) . ".";
        }

        // ── Radar ──
        $rad = [];
        if ($rd['calientes']===0) $rad[] = "Sin señales calientes del Radar en el periodo.";
        else {
            $rad[] = "{$rd['calientes']} clientes se pusieron calientes; marcó {$rd['marco']}, ignoró {$rd['sin_feedback']}.";
            if ($radar_foco) {
                $rad[] = "Ahí están sus ventas más fáciles y no las está viendo:";
                foreach ($rd['casos'] as $c) $rad[] = "  #{$c['numero']} {$c['cliente']} · " . self::_money($c['total']);
            } elseif ($rd['sin_feedback']===0) $rad[] = "Les da seguimiento a todas — bien.";
        }

        // ── Cartera en riesgo (número + importe, SIN lista) ──
        $car = [];
        if ($ca['se_fueron']>0) $car[] = "Se le fueron {$ca['se_fueron']} cotizaciones sin atención (" . self::_money($ca['monto_se_fueron']) . "). Revisa con él cuáles rescatar.";

        // ── Consejo del Director (directo, un solo golpe al foco dominante) ──
        $cons = [];
        if ($venc_foco && $m['vencidas'] >= 3)
            $cons[] = "Tiene {$m['vencidas']} seguimientos caídos — cada día vencido pierde temperatura. Que los cierre HOY antes que nada.";
        elseif ($conv_foco && $a['citas']>0)
            $cons[] = "El cuello NO es abrir, es CERRAR: {$a['citas']} citas y 0 ventas con {$a['trabajo_maduro']} cotizaciones ya maduras. Métete a su próxima cita o pídele la objeción real. Es técnica de cierre, no volumen.";
        elseif ($conv_foco && $cont_foco)
            $cons[] = "No llega ni a la cita: a {$a['no_conecta']} no les contesta. Arregla primero el contacto —horario y medio—, sin cita no hay venta.";
        elseif ($desc_foco)
            $cons[] = "Está tirando leads sin trabajarlos ({$a['sincita']} sin cita). Regla clara: no descartar sin al menos 1 intento de cita.";
        elseif ($dto_foco)
            $cons[] = "Cierra pero regalando: {$a['con_dto']} de {$a['cierres']} con descuento. Enséñale a defender el precio con valor o te come el margen.";
        elseif ($radar_foco)
            $cons[] = "Ignora el Radar: {$rd['sin_feedback']} calientes sin marcar. Siéntate con él y trabajen esas hoy — son las más fáciles.";
        elseif ($venc_foco)
            $cons[] = "Trae {$m['vencidas']} vencida" . ($m['vencidas']>1?'s':'') . " — pónganlas al día antes de que se enfríen.";
        else
            $cons[] = "Va sólido. Súbele la vara: más volumen o mejor ticket, y que no baje el ritmo de citas.";

        // ── Guion 1:1 (solo focos reales) ──
        $g = [];
        if ($venc_foco && $m['venc_casos']) { $c0=$m['venc_casos'][0]; $g[] = "\"Ábreme la #{$c0['numero']} de {$c0['cliente']} — lleva {$c0['dias']} días vencida. ¿Qué pasó?\""; }
        if ($conv_foco && $a['citas']>0) $g[] = "\"Tienes {$a['citas']} citas y 0 cierres — ¿qué está pasando en la cita?\"";
        if ($desc_foco) $g[] = "\"¿Por qué descartas antes de agendar? {$a['sincita']} de tus {$a['desc']} nunca llegaron a cita.\"";
        if ($dto_foco) $g[] = "\"{$a['con_dto']} de tus {$a['cierres']} ventas fueron con descuento — ¿por qué necesitaste bajar el precio?\"";
        if ($radar_foco) $g[] = "\"Tienes {$rd['sin_feedback']} calientes sin marcar — revísalas conmigo ahorita.\"";
        if ($cont_foco) $g[] = "\"A {$a['no_conecta']} clientes no les contestas — ¿a qué hora y por qué medio les marcas?\"";
        if (!$g) $g[] = "\"Vas bien — ¿qué necesitas de mí para cerrar más rápido?\"";

        // ── Meta de la semana ──
        $meta = [];
        if ($venc_foco) $meta[] = "Poner al día las {$m['vencidas']} vencidas antes del viernes.";
        if ($conv_foco && $a['citas']>0) $meta[] = "Convertir 1 de sus {$a['citas']} citas en venta.";
        if ($desc_foco) $meta[] = "No descartar ninguna sin al menos 1 intento de cita.";
        if ($radar_foco) $meta[] = "Marcar (👍/👎) las {$rd['sin_feedback']} calientes del Radar.";
        if ($dto_foco) $meta[] = "Cerrar la próxima venta sin descuento.";
        if (!$meta) $meta[] = "Sostener el ritmo: mesa al día y seguir cerrando.";

        return ['resumen'=>$res,'embudo'=>$emb,'calidad'=>$cal,'radar'=>$rad,'cartera'=>$car,'consejo'=>$cons,'guion'=>$g,'meta'=>$meta];
    }

    private static function _money(float $n): string { return '$' . number_format($n, 0, '.', ','); }

    // ═══════════ Render HTML (usa las vars de tema del dashboard) ═══════════
    public static function render(array $d): string
    {
        $s = $d['secciones']; $sc = $d['score'];
        $rango = date('d M', strtotime($d['desde'])) . ' – ' . date('d M', strtotime($d['hasta']));
        $h  = '<div class="rr">';
        $h .= '<div class="rr-hd"><div><div class="rr-k">Reporte de asesor · ' . e($rango) . '</div>'
            . '<div class="rr-name">' . e($d['nombre']) . '</div></div>';
        if ($sc) $h .= '<div class="rr-score"><div class="rr-sn">' . (int)$sc['score'] . '</div><div class="rr-sl">' . e(ucfirst($sc['nivel'])) . '</div></div>';
        $h .= '</div>';
        if ($sc) {
            $h .= '<div class="rr-dims">';
            foreach ($sc['dim'] as $lab => $v) $h .= '<span class="rr-dim"><b>' . e($lab) . '</b> ' . (int)round($v*100) . '%</span>';
            $h .= '</div><div class="rr-note">Score en ventana estándar de 15 días. El reporte solo juzga lo que ya no tiene remedio (leads maduros) — lo de hoy/ayer no cuenta como falla.</div>';
        }

        $sec = function (string $titulo, array $items, string $cls = '') {
            if (!$items) return '';
            $o = '<div class="rr-sec ' . $cls . '"><div class="rr-st">' . e($titulo) . '</div><ul class="rr-list">';
            foreach ($items as $it) {
                $sub = str_starts_with($it, '  ');
                $o .= '<li' . ($sub ? ' class="rr-sub"' : '') . '>' . e(trim($it)) . '</li>';
            }
            return $o . '</ul></div>';
        };

        $h .= $sec('Resumen', $s['resumen']);
        $h .= $sec('El embudo', $s['embudo']);
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
