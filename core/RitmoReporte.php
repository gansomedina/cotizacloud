<?php
// ============================================================
//  RitmoReporte — Reporte del Director de Ventas por asesor
//  Cruza TODA la data real (sin LLM, sin PII afuera, fact-lint):
//    · Ritmo (5 pilares)         RitmoAsesor
//    · Score + debug             usuario_score
//    · Mesa (pipeline vivo)      Mesa::armar (read-only)
//    · Mesa (cartera)            Mesa::reporte
//    · Radar + historial         bucket_transitions + radar_feedback
//    · Ventas                    ventas
//  Compone 5 secciones con casos CONCRETOS (folio, cliente, monto,
//  días). Cada frase sale de un dato real — no se inventa nada.
//  Solo lectura. La renta la controla el endpoint (Business + 1/sem).
// ============================================================
defined('COTIZAAPP') or die;

class RitmoReporte
{
    /**
     * @return array ['nombre','rango','score','ritmo','html', ...]
     */
    public static function generar(int $empresa_id, int $asesor_id, string $desde, string $hasta): array
    {
        $nombre = (string) DB::val("SELECT nombre FROM usuarios WHERE id=? AND empresa_id=?", [$asesor_id, $empresa_id]);
        if ($nombre === '') $nombre = "Asesor #{$asesor_id}";

        // Ciclo real de la empresa (para "muy rápido")
        $mediana = 5;
        try {
            if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
            $c = Radar::ciclo_venta($empresa_id);
            if (!empty($c['auto']) && !empty($c['mediana'])) $mediana = max(1, (int)$c['mediana']);
        } catch (Throwable $e) {}
        $rapido_dias = max(1, (int)floor($mediana / 2));

        $d = [
            'nombre'  => $nombre,
            'desde'   => $desde,
            'hasta'   => $hasta,
            'score'   => self::_score($asesor_id, $empresa_id),
            'act'     => self::_actividad($empresa_id, $asesor_id, $desde, $hasta, $rapido_dias),
            'mesa'    => self::_mesa($empresa_id, $asesor_id),
            'radar'   => self::_radar($empresa_id, $asesor_id, $desde, $hasta),
            'cartera' => self::_cartera($empresa_id, $asesor_id, $desde, $hasta),
            'rapido_dias' => $rapido_dias,
        ];
        $d['secciones'] = self::_componer($d);
        $d['html'] = self::render($d);
        return $d;
    }

    // ── Score + debug (ventana estándar 15d de usuario_score) ──
    private static function _score(int $uid, int $eid): ?array
    {
        try {
            $r = DB::row("SELECT * FROM usuario_score WHERE usuario_id=? AND empresa_id=?", [$uid, $eid]);
        } catch (Throwable $e) { $r = null; }
        if (!$r) return null;
        return [
            'score'  => (int)($r['score'] ?? 0),
            'nivel'  => (string)($r['nivel'] ?? ''),
            'dim'    => [
                'Activación'   => (float)($r['s_activacion'] ?? 0),
                'Engagement'   => (float)($r['s_engagement'] ?? 0),
                'Seguimiento'  => (float)($r['s_seguimiento'] ?? 0),
                'Radar Health' => (float)($r['s_radar_health'] ?? 0),
                'Conversión'   => (float)($r['s_conversion'] ?? 0),
            ],
            'tasa_cierre'    => (float)($r['tasa_cierre'] ?? 0),
            'ventas_periodo' => (int)($r['ventas_periodo'] ?? 0),
            'bench_ventas'   => (float)($r['bench_ventas'] ?? 0),
            'ticket'         => (float)($r['ticket_promedio'] ?? 0),
            'bonus_cierre'   => (int)($r['bonus_cierre'] ?? 0),
        ];
    }

    // ── Actividad del rango (range-bound, como el Ritmo) ──
    private static function _actividad(int $eid, int $uid, string $desde, string $hasta, int $rapido_dias): array
    {
        $a = ['cierres'=>0,'monto_cierres'=>0.0,'trabajo'=>0,'citas'=>0,
              'desc'=>0,'sincita'=>0,'rapido'=>0,'desc_casos'=>[],
              'contactados'=>0,'no_conecta'=>0];

        try {
            $r = DB::row(
                "SELECT COUNT(*) AS n, COALESCE(SUM(v.total),0) AS monto
                   FROM ventas v LEFT JOIN cotizaciones c ON c.id=v.cotizacion_id
                  WHERE v.empresa_id=? AND v.estado<>'cancelada' AND v.pagado>0 AND v.total>0
                    AND COALESCE(v.vendedor_id,v.usuario_id,c.vendedor_id,c.usuario_id)=?
                    AND v.created_at BETWEEN ? AND ?",
                [$eid, $uid, $desde . ' 00:00:00', $hasta . ' 23:59:59']
            );
            $a['cierres'] = (int)($r['n'] ?? 0); $a['monto_cierres'] = (float)($r['monto'] ?? 0);
        } catch (Throwable $e) {}

        try {
            $a['trabajo'] = (int)DB::val(
                "SELECT COUNT(DISTINCT m.cotizacion_id) FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                  WHERE m.empresa_id=? AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?",
                [$eid, $uid, $desde . ' 00:00:00', $hasta . ' 23:59:59']
            );
        } catch (Throwable $e) {}

        try {
            $a['citas'] = (int)DB::val(
                "SELECT COUNT(DISTINCT m.cotizacion_id) FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                  WHERE m.empresa_id=? AND m.area='compromiso' AND m.estado='nos_citamos'
                    AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?",
                [$eid, $uid, $desde . ' 00:00:00', $hasta . ' 23:59:59']
            );
        } catch (Throwable $e) {}

        // Descartes + casos concretos (folio, cliente, monto, días a descarte)
        try {
            $rows = DB::query(
                "SELECT c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente,
                        DATEDIFF(m.created_at, c.created_at) AS dias,
                        (NOT EXISTS (SELECT 1 FROM mesa_estados mc
                           WHERE mc.cotizacion_id=c.id AND mc.area='compromiso' AND mc.estado='nos_citamos')) AS sin_cita
                   FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                   LEFT JOIN clientes cl ON cl.id=c.cliente_id
                  WHERE m.empresa_id=? AND m.area='postura' AND m.estado='descartada'
                    AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?
                  ORDER BY c.total DESC",
                [$eid, $uid, $desde . ' 00:00:00', $hasta . ' 23:59:59']
            );
            $a['desc'] = count($rows);
            foreach ($rows as $x) {
                if ((int)$x['sin_cita']) $a['sincita']++;
                if ((int)$x['dias'] <= $rapido_dias) $a['rapido']++;
                if (count($a['desc_casos']) < 4)
                    $a['desc_casos'][] = ['numero'=>$x['numero'],'cliente'=>$x['cliente'],
                        'total'=>(float)$x['total'],'dias'=>(int)$x['dias'],'sin_cita'=>(int)$x['sin_cita']];
            }
        } catch (Throwable $e) {}

        try {
            $r = DB::row(
                "SELECT COUNT(DISTINCT m.cotizacion_id) AS contactados,
                        COUNT(DISTINCT CASE WHEN NOT EXISTS (
                          SELECT 1 FROM mesa_estados h WHERE h.cotizacion_id=m.cotizacion_id
                            AND h.area='contacto' AND h.estado='hablamos') THEN m.cotizacion_id END) AS no_conecta
                   FROM mesa_estados m JOIN cotizaciones c ON c.id=m.cotizacion_id
                  WHERE m.empresa_id=? AND m.area='contacto'
                    AND COALESCE(c.vendedor_id,c.usuario_id)=? AND m.created_at BETWEEN ? AND ?",
                [$eid, $uid, $desde . ' 00:00:00', $hasta . ' 23:59:59']
            );
            $a['contactados'] = (int)($r['contactados'] ?? 0); $a['no_conecta'] = (int)($r['no_conecta'] ?? 0);
        } catch (Throwable $e) {}

        return $a;
    }

    // ── Pipeline vivo (Mesa::armar read-only): vencidas + vence hoy + casos ──
    private static function _mesa(int $eid, int $uid): array
    {
        $m = ['vencidas'=>0,'vence_hoy'=>0,'venc_casos'=>[]];
        try {
            $r = Mesa::armar($eid, $uid, true);
            $res = $r['resumen'] ?? [];
            $m['vencidas'] = (int)($res['vencidas'] ?? 0);
            $m['vence_hoy'] = (int)($res['vence_hoy'] ?? 0);
            $casos = [];
            foreach (($r['rows'] ?? []) as $row) {
                if (($row['seguimiento']['estado'] ?? '') !== 'vencida') continue;
                $casos[] = [
                    'numero'  => $row['numero'] ?? '',
                    'cliente' => $row['cliente'] ?? '—',
                    'total'   => (float)($row['total'] ?? 0),
                    'dias'    => (int)($row['seguimiento']['dias'] ?? 0),
                ];
            }
            // más urgentes primero: días × monto
            usort($casos, fn($x, $y) => ($y['dias'] * max($y['total'],1)) <=> ($x['dias'] * max($x['total'],1)));
            $m['venc_casos'] = array_slice($casos, 0, 4);
        } catch (Throwable $e) {}
        return $m;
    }

    // ── Radar + historial: calientes sin feedback + cobertura ──
    private static function _radar(int $eid, int $uid, string $desde, string $hasta): array
    {
        $hot = "('probable_cierre','onfire','inminente','validando_precio','prediccion_alta','lectura_comprometida')";
        $r = ['calientes'=>0,'sin_feedback'=>0,'casos'=>[]];
        try {
            $r['calientes'] = (int)DB::val(
                "SELECT COUNT(DISTINCT bt.cotizacion_id) FROM bucket_transitions bt
                   JOIN cotizaciones c ON c.id=bt.cotizacion_id
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=?
                    AND bt.bucket_nuevo IN $hot AND bt.created_at BETWEEN ? AND ? AND c.suspendida=0",
                [$uid, $eid, $desde . ' 00:00:00', $hasta . ' 23:59:59']
            );
        } catch (Throwable $e) {}
        try {
            $rows = DB::query(
                "SELECT DISTINCT c.numero, c.total, COALESCE(cl.nombre,'—') AS cliente
                   FROM bucket_transitions bt JOIN cotizaciones c ON c.id=bt.cotizacion_id
                   LEFT JOIN clientes cl ON cl.id=c.cliente_id
                  WHERE COALESCE(c.vendedor_id,c.usuario_id)=? AND c.empresa_id=?
                    AND bt.bucket_nuevo IN $hot AND bt.created_at BETWEEN ? AND ?
                    AND c.estado IN ('enviada','vista') AND c.suspendida=0
                    AND NOT EXISTS (SELECT 1 FROM radar_feedback rf
                       WHERE rf.cotizacion_id=c.id AND rf.usuario_id=COALESCE(c.vendedor_id,c.usuario_id))
                  ORDER BY c.total DESC",
                [$uid, $eid, $desde . ' 00:00:00', $hasta . ' 23:59:59']
            );
            $r['sin_feedback'] = count($rows);
            $r['casos'] = array_slice(array_map(fn($x) => [
                'numero'=>$x['numero'],'cliente'=>$x['cliente'],'total'=>(float)$x['total']], $rows), 0, 4);
        } catch (Throwable $e) {}
        return $r;
    }

    // ── Cartera (Mesa::reporte): se le fueron ──
    private static function _cartera(int $eid, int $uid, string $desde, string $hasta): array
    {
        $c = ['se_fueron'=>0,'monto_se_fueron'=>0.0,'sf_folios'=>[]];
        try {
            $dias = max(1, (int)round((strtotime($hasta) - strtotime($desde)) / 86400) + 1);
            $rep = Mesa::reporte($eid, $dias);
            $a = $rep['asesores'][$uid] ?? null;
            if ($a) {
                $c['se_fueron'] = (int)($a['se_fueron'] ?? 0);
                $c['monto_se_fueron'] = (float)($a['monto_se_fueron'] ?? 0);
                $c['sf_folios'] = array_slice((array)($a['se_fueron_cots'] ?? []), 0, 5);
            }
        } catch (Throwable $e) {}
        return $c;
    }

    // ═══════════ Composición de secciones (fact-lint) ═══════════
    private static function _componer(array $d): array
    {
        $a = $d['act']; $m = $d['mesa']; $rd = $d['radar']; $ca = $d['cartera'];
        $rate = $a['trabajo'] > 0 ? (int)round($a['cierres'] / $a['trabajo'] * 100) : 0;
        $noc  = $a['contactados'] > 0 ? (int)round($a['no_conecta'] / $a['contactados'] * 100) : 0;

        // ── Resumen ──
        $res = [];
        if ($a['trabajo'] > 0) {
            $res[] = $a['cierres'] > 0
                ? "Trabajó {$a['trabajo']} cotizaciones y cerró {$a['cierres']} ({$rate}%)."
                : "Trabajó {$a['trabajo']} cotizaciones y no cerró ninguna (0%).";
        }
        if ($m['vencidas'] > 0) $res[] = "Trae {$m['vencidas']} seguimiento" . ($m['vencidas']>1?'s':'') . " vencido" . ($m['vencidas']>1?'s':'') . " en su mesa.";
        if ($a['desc'] > 0 && $a['sincita'] > 0) $res[] = "Descartó {$a['desc']} y {$a['sincita']} sin llegar a cita.";
        if ($rd['sin_feedback'] > 0) $res[] = "Tiene {$rd['sin_feedback']} caliente" . ($rd['sin_feedback']>1?'s':'') . " del Radar sin marcar.";
        if (!$res) $res[] = "Sin actividad relevante en el rango.";

        // ── Fortalezas ──
        $fort = [];
        if ($a['cierres'] > 0) $fort[] = "Cerró {$a['cierres']} venta" . ($a['cierres']>1?'s':'') . " (" . self::_money($a['monto_cierres']) . ") en el rango.";
        if (($d['score']['bonus_cierre'] ?? 0) > 0) $fort[] = "Cierra por encima del histórico de la empresa (bono de cierre activo).";
        if ($a['citas'] > 0) $fort[] = "Agendó {$a['citas']} cita" . ($a['citas']>1?'s':'') . " — sabe abrir.";
        if ($a['contactados'] >= 4 && $noc < 35) $fort[] = "Buen contacto: le respondieron " . ($a['contactados']-$a['no_conecta']) . " de {$a['contactados']}.";
        if ($m['vencidas'] === 0 && $m['vence_hoy'] === 0 && $a['trabajo'] > 0) $fort[] = "Mesa al día: 0 seguimientos vencidos.";
        if (!$fort) $fort[] = "Sin fortalezas claras que destacar en el rango — foco en lo de abajo.";

        // ── Focos rojos con casos ──
        $focos = [];
        if ($m['vencidas'] > 0) {
            $casos = array_map(fn($x) => "#{$x['numero']} {$x['cliente']} · " . self::_money($x['total']) . " · {$x['dias']}d vencida", $m['venc_casos']);
            $focos[] = ['t' => "Seguimiento — {$m['vencidas']} vencidas" . ($m['vence_hoy']>0 ? " · {$m['vence_hoy']} vencen hoy" : ""), 'casos' => $casos];
        }
        if ($a['desc'] > 0 && ($a['sincita'] >= max(3, (int)ceil($a['desc']*0.5)) || $a['rapido'] >= 3)) {
            $casos = [];
            foreach ($a['desc_casos'] as $x) {
                $tag = [];
                if ($x['sin_cita']) $tag[] = 'sin cita';
                if ($x['dias'] <= $d['rapido_dias']) $tag[] = "a {$x['dias']}d";
                $casos[] = "#{$x['numero']} {$x['cliente']} · " . self::_money($x['total']) . ($tag ? ' · ' . implode(', ', $tag) : '');
            }
            $focos[] = ['t' => "Descartadas — {$a['desc']} ({$a['sincita']} sin cita, {$a['rapido']} muy rápido)", 'casos' => $casos];
        }
        if ($a['cierres'] === 0 && $a['trabajo'] >= 8) {
            $focos[] = ['t' => "Cierre — 0 de {$a['trabajo']} trabajadas (0%)", 'casos' => []];
        }
        if ($rd['sin_feedback'] > 0) {
            $casos = array_map(fn($x) => "#{$x['numero']} {$x['cliente']} · " . self::_money($x['total']), $rd['casos']);
            $focos[] = ['t' => "Radar — {$rd['sin_feedback']} caliente" . ($rd['sin_feedback']>1?'s':'') . " sin marcar (👍/👎)", 'casos' => $casos];
        }
        if ($a['contactados'] >= 4 && $noc >= 35) {
            $focos[] = ['t' => "Contacto — {$a['no_conecta']} de {$a['contactados']} no le contestaron ({$noc}%)", 'casos' => []];
        }
        if ($ca['se_fueron'] > 0) {
            $folios = array_map(fn($f) => "#{$f}", $ca['sf_folios']);
            $focos[] = ['t' => "Se le fueron — {$ca['se_fueron']} sin atención (" . self::_money($ca['monto_se_fueron']) . ")", 'casos' => $folios];
        }

        // ── Guion para el 1:1 ──
        $guion = [];
        if ($m['vencidas'] > 0 && $m['venc_casos']) {
            $c0 = $m['venc_casos'][0];
            $guion[] = "\"Ábreme la #{$c0['numero']} de {$c0['cliente']} — lleva {$c0['dias']} días vencida. ¿Qué pasó?\"";
        }
        if ($a['desc'] > 0 && $a['sincita'] > 0) $guion[] = "\"¿Por qué descartas antes de agendar? {$a['sincita']} de tus {$a['desc']} descartes nunca llegaron a cita.\"";
        if ($a['cierres'] === 0 && $a['citas'] > 0) $guion[] = "\"Tienes {$a['citas']} citas y 0 cierres — ¿qué pasa en la cita?\"";
        if ($rd['sin_feedback'] > 0) $guion[] = "\"Tienes {$rd['sin_feedback']} calientes sin marcar en el Radar — revísalas conmigo ahorita.\"";
        if ($noc >= 35 && $a['contactados'] >= 4) $guion[] = "\"A {$a['no_conecta']} clientes no les contestas — ¿a qué hora y por qué medio les marcas?\"";
        if (!$guion) $guion[] = "\"Vas bien — ¿qué necesitas de mí para cerrar más rápido?\"";

        // ── Meta de la semana ──
        $meta = [];
        if ($m['vencidas'] > 0) $meta[] = "Poner al día las {$m['vencidas']} vencidas antes del viernes.";
        if ($a['desc'] > 0 && $a['sincita'] > 0) $meta[] = "No descartar ninguna cotización sin al menos 1 intento de cita.";
        if ($a['cierres'] === 0 && $a['citas'] > 0) $meta[] = "Convertir 1 de sus " . $a['citas'] . " citas en venta.";
        if ($rd['sin_feedback'] > 0) $meta[] = "Marcar (👍/👎) las {$rd['sin_feedback']} calientes del Radar.";
        if (!$meta) $meta[] = "Sostener el ritmo: mesa al día y seguir cerrando.";

        return ['resumen'=>$res, 'fortalezas'=>$fort, 'focos'=>$focos, 'guion'=>$guion, 'meta'=>$meta];
    }

    private static function _money(float $n): string
    {
        return '$' . number_format($n, 0, '.', ',');
    }

    // ═══════════ Render HTML (usa las vars de tema del dashboard) ═══════════
    public static function render(array $d): string
    {
        $s = $d['secciones']; $sc = $d['score'];
        $rango = date('d M', strtotime($d['desde'])) . ' – ' . date('d M', strtotime($d['hasta']));
        $h  = '<div class="rr">';
        $h .= '<div class="rr-hd"><div><div class="rr-k">Reporte de asesor · ' . e($rango) . '</div>'
            . '<div class="rr-name">' . e($d['nombre']) . '</div></div>';
        if ($sc) {
            $h .= '<div class="rr-score"><div class="rr-sn">' . (int)$sc['score'] . '</div>'
                . '<div class="rr-sl">' . e(ucfirst($sc['nivel'])) . '</div></div>';
        }
        $h .= '</div>';

        if ($sc) {
            $h .= '<div class="rr-dims">';
            foreach ($sc['dim'] as $lab => $v) {
                $pct = (int)round($v * 100);
                $h .= '<span class="rr-dim"><b>' . e($lab) . '</b> ' . $pct . '%</span>';
            }
            $h .= '</div>';
            $h .= '<div class="rr-note">Score en ventana estándar de 15 días. Los casos de abajo son del rango elegido y del pipeline actual.</div>';
        }

        $sec = function (string $titulo, array $items, bool $foco = false) {
            if (!$items) return '';
            $o = '<div class="rr-sec"><div class="rr-st">' . e($titulo) . '</div>';
            if ($foco) {
                foreach ($items as $f) {
                    $o .= '<div class="rr-foco"><div class="rr-ft">' . e($f['t']) . '</div>';
                    if (!empty($f['casos'])) {
                        $o .= '<ul class="rr-casos">';
                        foreach ($f['casos'] as $c) $o .= '<li>' . e($c) . '</li>';
                        $o .= '</ul>';
                    }
                    $o .= '</div>';
                }
            } else {
                $o .= '<ul class="rr-list">';
                foreach ($items as $it) $o .= '<li>' . e($it) . '</li>';
                $o .= '</ul>';
            }
            return $o . '</div>';
        };

        $h .= $sec('Resumen', $s['resumen']);
        $h .= $sec('Fortalezas', $s['fortalezas']);
        $h .= $sec('Focos rojos', $s['focos'], true);
        $h .= $sec('Guion para el 1:1', $s['guion']);
        $h .= $sec('Meta de la semana', $s['meta']);
        $h .= '<div class="rr-foot">Generado con datos reales de la Mesa, el Radar y el score. Sin invenciones — cada cifra sale del sistema.</div>';
        $h .= '</div>';
        return $h;
    }
}
