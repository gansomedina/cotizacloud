<?php
// ============================================================
//  CotizaApp — core/Mesa.php  v1 (beta admin)
//  Mesa de Trabajo: cola operativa del asesor, armada sola por
//  3 motores existentes — Radar (calor), ciclo_venta (ventana),
//  monto (tamaño). v1 = solo lectura, visible solo para admin
//  (evaluación del CEO antes de soltarla a asesores).
//  Diseño: docs/mesa_trabajo_diseno.md
// ============================================================

defined('COTIZAAPP') or die;

class Mesa
{
    // Buckets calientes — mismo set que gatea el feedback del Radar
    public const HOT = [
        'probable_cierre','onfire','inminente','validando_precio',
        'prediccion_alta','lectura_comprometida','multi_persona','alto_importe',
    ];

    private const CAP_MESA     = 25;
    private const CAP_MILAGROS = 3;

    private static array $cache = [];

    /**
     * Arma la mesa de un vendedor.
     * Retorna ['rows'=>[], 'limpieza'=>['n'=>..,'monto'=>..,'linea_dias'=>..],
     *          'ciclo'=>[...], 'resumen'=>[...]]
     */
    public static function armar(int $empresa_id, int $vendedor_id): array
    {
        $ck = "$empresa_id:$vendedor_id";
        if (isset(self::$cache[$ck])) return self::$cache[$ck];

        // Radar vive en modules/, fuera del autoloader de core/
        if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';

        $ciclo = Radar::ciclo_venta($empresa_id);
        $p75   = ($ciclo['auto'] && !empty($ciclo['p75'])) ? max(1, (int)$ciclo['p75']) : 30;

        // Cierre más tardío de la historia de la empresa (línea de evidencia)
        $max_hist = (int)DB::val(
            "SELECT COALESCE(MAX(DATEDIFF(v.created_at, c.created_at)), 0)
             FROM ventas v JOIN cotizaciones c ON c.id = v.cotizacion_id
             WHERE v.empresa_id = ? AND v.estado != 'cancelada'
               AND DATEDIFF(v.created_at, c.created_at) >= 0",
            [$empresa_id]
        );
        $linea_limpieza = max($max_hist, 2 * $p75); // nunca sugerir bajo 2×p75

        // Universo: activas del vendedor (mismos criterios que score/Radar)
        $cots = DB::query(
            "SELECT c.id, c.numero, c.titulo, c.total, c.estado, c.visitas,
                    c.radar_bucket, c.radar_bucket_at, c.ultima_vista_at, c.created_at, c.radar_senales,
                    cl.nombre AS cliente, cl.telefono AS cli_tel,
                    DATEDIFF(NOW(), c.created_at) AS edad,
                    DATEDIFF(NOW(), COALESCE(c.ultima_vista_at, c.created_at)) AS dias_sin_vista
             FROM cotizaciones c
             LEFT JOIN clientes cl ON cl.id = c.cliente_id
             WHERE c.empresa_id = ? AND COALESCE(c.vendedor_id, c.usuario_id) = ?
               AND c.estado IN ('enviada','vista') AND c.suspendida = 0 AND c.total > 0
               AND c.accion_at IS NULL
               AND NOT EXISTS (
                   SELECT 1 FROM ventas v
                   WHERE v.cotizacion_id = c.id AND v.estado != 'cancelada'
               )",
            [$empresa_id, $vendedor_id]
        );

        if (!$cots) {
            return self::$cache[$ck] = [
                'rows' => [], 'limpieza' => ['n' => 0, 'monto' => 0.0, 'linea_dias' => $linea_limpieza],
                'ciclo' => $ciclo, 'resumen' => ['n' => 0, 'monto' => 0.0, 'sin_postura' => 0, 'mas_viejo_dias' => 0],
            ];
        }

        $ids = array_map(fn($c) => (int)$c['id'], $cots);
        $in  = implode(',', $ids);

        // Feedback Radar del ASESOR de esta mesa (una marca por cotización)
        $fb = [];
        foreach (DB::query(
            "SELECT cotizacion_id, tipo, updated_at FROM radar_feedback
             WHERE empresa_id = ? AND usuario_id = ? AND cotizacion_id IN ($in)",
            [$empresa_id, $vendedor_id]
        ) as $r) {
            $fb[(int)$r['cotizacion_id']] = $r;
        }

        // Última acción del asesor sobre la cotización (editada/reenviada)
        $acc = [];
        foreach (DB::query(
            "SELECT cotizacion_id, MAX(created_at) AS ult
             FROM cotizacion_log
             WHERE cotizacion_id IN ($in) AND usuario_id IS NOT NULL
               AND COALESCE(accion, evento) IN ('editada','enviada')
             GROUP BY cotizacion_id", []
        ) as $r) {
            $acc[(int)$r['cotizacion_id']] = $r['ult'];
        }

        // Estados declarados en la mesa (última declaración por área)
        $me = []; $nc = [];
        try {
            foreach (DB::query(
                "SELECT m.cotizacion_id, m.area, m.estado, m.razon, m.created_at
                 FROM mesa_estados m
                 JOIN (SELECT cotizacion_id, area, MAX(id) AS mid FROM mesa_estados
                       WHERE empresa_id = ? AND cotizacion_id IN ($in)
                       GROUP BY cotizacion_id, area) t ON t.mid = m.id",
                [$empresa_id]
            ) as $r) {
                $me[(int)$r['cotizacion_id']][$r['area']] = [
                    'estado' => $r['estado'], 'at' => $r['created_at'], 'razon' => $r['razon'],
                ];
            }
            // Historia de contacto → intentos de no_contesta desde el último "hablamos"
            foreach (DB::query(
                "SELECT cotizacion_id, estado FROM mesa_estados
                 WHERE empresa_id = ? AND cotizacion_id IN ($in) AND area = 'contacto'
                 ORDER BY id",
                [$empresa_id]
            ) as $r) {
                $cid = (int)$r['cotizacion_id'];
                if ($r['estado'] === 'hablamos') $nc[$cid] = 0;
                else $nc[$cid] = ($nc[$cid] ?? 0) + 1;
            }
        } catch (Throwable $e) {} // tabla aún no migrada

        // Actividad reciente del cliente (respeta Escudo + filtro ghost)
        $act_c = [];
        foreach (DB::query(
            "SELECT cotizacion_id,
                    SUM(created_at >= NOW() - INTERVAL 1 DAY) AS v24,
                    SUM(created_at >= NOW() - INTERVAL 7 DAY) AS v7,
                    COUNT(DISTINCT CASE WHEN created_at >= NOW() - INTERVAL 7 DAY THEN ip END) AS ips7
             FROM quote_sessions
             WHERE cotizacion_id IN ($in) AND es_interno = 0
               AND NOT (COALESCE(visible_ms,0) < 200 AND COALESCE(scroll_max,0) < 35)
             GROUP BY cotizacion_id", []
        ) as $r) {
            $act_c[(int)$r['cotizacion_id']] = $r;
        }

        // Ticket promedio de la empresa (cache estático)
        static $tickets = [];
        if (!array_key_exists($empresa_id, $tickets)) {
            $t = DB::row(
                "SELECT AVG(total) AS avg_t, COUNT(*) AS n FROM ventas
                 WHERE empresa_id = ? AND estado != 'cancelada' AND total > 0
                   AND created_at >= NOW() - INTERVAL 180 DAY", [$empresa_id]
            );
            if ((int)($t['n'] ?? 0) < 5) {
                $t = DB::row(
                    "SELECT AVG(total) AS avg_t, COUNT(*) AS n FROM ventas
                     WHERE empresa_id = ? AND estado != 'cancelada' AND total > 0", [$empresa_id]
                );
            }
            $tickets[$empresa_id] = ((int)($t['n'] ?? 0) >= 5) ? (float)$t['avg_t'] : null;
        }
        $ticket_empresa = $tickets[$empresa_id];

        // Arquetipo del asesor (termómetro) — modula el CÓMO de los tips
        $arquetipo = 'muestra_chica';
        try {
            $us = DB::row(
                "SELECT * FROM usuario_score WHERE usuario_id = ? AND empresa_id = ?",
                [$vendedor_id, $empresa_id]
            );
            if ($us) $arquetipo = DiagnosticoTips::arquetipo($us);
        } catch (Throwable $e) {}

        // Resurrección: descartadas cuyo cliente volvió a calentarse DESPUÉS del descarte
        $revividas = [];
        $descartadas_ids = [];
        foreach ($fb as $cid => $f) {
            if ($f['tipo'] === 'sin_interes') $descartadas_ids[] = $cid;
        }
        if ($descartadas_ids) {
            $din = implode(',', array_map('intval', $descartadas_ids));
            $hot_in = "'" . implode("','", self::HOT) . "'";
            foreach (DB::query(
                "SELECT bt.cotizacion_id, MAX(bt.created_at) AS ult_hot
                 FROM bucket_transitions bt
                 WHERE bt.cotizacion_id IN ($din) AND bt.bucket_nuevo IN ($hot_in)
                 GROUP BY bt.cotizacion_id", []
            ) as $r) {
                $cid = (int)$r['cotizacion_id'];
                $t_hot = strtotime($r['ult_hot']);
                if ($t_hot > strtotime($fb[$cid]['updated_at']) && $t_hot >= time() - 7 * 86400) {
                    $revividas[$cid] = true;
                }
            }
        }

        // ── Clasificar por CATEGORÍA DE FALTA ────────────────
        // La mesa NO repite el Radar: solo muestra donde falta o se
        // contradice la ACCIÓN/JUICIO del asesor. Lo que va bien, no sale.
        $rows = [];
        $limpieza_n = 0; $limpieza_monto = 0.0;
        $now = time();

        foreach ($cots as $c) {
            $cid    = (int)$c['id'];
            $edad   = (int)$c['edad'];
            $bucket = $c['radar_bucket'];
            $es_hot = $bucket !== null && in_array($bucket, self::HOT, true);
            $hot_reciente = $es_hot && $c['radar_bucket_at']
                && (strtotime($c['radar_bucket_at']) >= $now - 7 * 86400);
            $postura    = $fb[$cid]['tipo'] ?? null;
            $descartada = ($postura === 'sin_interes');
            $dormida    = ((int)$c['visitas'] > 0 && (int)$c['dias_sin_vista'] >= 7);
            $fuera      = $edad > 2 * $p75;

            // Descartada HOY: se queda visible un día en su propia sección
            $descartada_hoy = $descartada && !empty($fb[$cid]['updated_at'])
                && strtotime($fb[$cid]['updated_at']) >= strtotime('today');
            // Descartada de días anteriores sin revivir → fuera (el Radar la vigila)
            if ($descartada && !isset($revividas[$cid]) && !$descartada_hoy) {
                if ($edad > $linea_limpieza && !$hot_reciente) { $limpieza_n++; $limpieza_monto += (float)$c['total']; }
                continue;
            }
            // Fuera de toda ventana, sin calor reciente, sin revivir → limpieza
            if ($fuera && !$hot_reciente && !isset($revividas[$cid])) {
                if ($edad > $linea_limpieza) { $limpieza_n++; $limpieza_monto += (float)$c['total']; }
                continue;
            }

            // Sin señal del cliente (nunca abrió) y sin calor → no es trabajo
            // de mesa (la tarjeta "Sin abrir" del dashboard ya la cubre)
            if (!$es_hot && (int)$c['visitas'] === 0) continue;

            // Categoría: mesa (capturas) + like del Radar (columna "Marcaste")
            if ($descartada && !isset($revividas[$cid])) {
                $cat = 'descartada_hoy';     // visible solo hoy; mañana sale de la mesa
            } elseif (isset($revividas[$cid])) {
                $cat = 'revivida';           // descartada y el cliente volvió vivo AHORA
            } elseif ($fuera && $hot_reciente) {
                $cat = 'milagro';            // fuera de ciclo pero viéndola AHORA
            } elseif ($postura === 'con_interes' && empty($me[$cid]['postura']) && ($dormida || $bucket === 'enfriandose')) {
                $cat = 'interes_muriendo';   // tu 👍 dice interés y el cliente se apaga
            } elseif ($postura === 'con_interes' && empty($me[$cid]['postura']) && $edad > $p75) {
                $cat = 'ultimo_tramo';       // 👍 pero saliendo de tu ventana
            } elseif (empty($me[$cid]) && $postura === null) {
                $cat = 'sin_postura';        // nada capturado ni marcado aún
            } else {
                $cat = 'trabajo';            // capturada → a trabajarla
            }

            $rows[] = [
                'id' => $cid, 'numero' => $c['numero'], 'titulo' => $c['titulo'],
                'cliente' => $c['cliente'] ?: '—', 'telefono' => $c['cli_tel'],
                'total' => (float)$c['total'], 'edad' => $edad, 'cat' => $cat,
                'bucket' => $bucket, 'es_hot' => $es_hot,
                'visitas' => (int)$c['visitas'], 'dias_sin_vista' => (int)$c['dias_sin_vista'],
                'postura' => $postura, 'ultima_accion' => $acc[$cid] ?? null,
                'dormida' => $dormida,
                'tier' => ($edad <= $p75 ? 1 : 2),
                'decl' => $me[$cid] ?? [],
                'atendida_hoy' => (function () use ($me, $cid) {
                    foreach (($me[$cid] ?? []) as $d) {
                        if (strtotime($d['at']) >= strtotime('today')) return true;
                    }
                    return false;
                })(),
                // Días desde la última declaración en la mesa (null = nunca)
                'ult_decl_dias' => (function () use ($me, $cid) {
                    $max = 0;
                    foreach (($me[$cid] ?? []) as $d) {
                        $t = strtotime($d['at']);
                        if ($t > $max) $max = $t;
                    }
                    return $max ? (int)floor((time() - $max) / 86400) : null;
                })(),
                'revivida' => ($cat === 'revivida'),
                'milagro'  => ($cat === 'milagro'),
                'fuera_ventana' => ($edad > $p75),
                'sugerencia' => MesaSugerencias::sugerir([
                    'total' => (float)$c['total'], 'edad' => $edad, 'cat' => $cat,
                    'bucket' => $bucket, 'es_hot' => $es_hot && $hot_reciente,
                    'pc_source' => (function () use ($c) {
                        $s = $c['radar_senales'] ? json_decode($c['radar_senales'], true) : null;
                        return $s['pc_source'] ?? null;
                    })(),
                    'visitas' => (int)$c['visitas'], 'dias_sin_vista' => (int)$c['dias_sin_vista'],
                    'ultima_vista_at' => $c['ultima_vista_at'],
                    'revivida' => ($cat === 'revivida'), 'milagro' => ($cat === 'milagro'),
                    'contacto' => $me[$cid]['contacto'] ?? null,
                    'compromiso' => $me[$cid]['compromiso'] ?? null,
                    'postura_decl' => $me[$cid]['postura'] ?? null,
                    'razon_descarte' => $me[$cid]['postura']['razon'] ?? null,
                    'intentos_nc' => $nc[$cid] ?? 0,
                    'vistas_24h' => (int)($act_c[$cid]['v24'] ?? 0),
                    'vistas_7d'  => (int)($act_c[$cid]['v7'] ?? 0),
                    'ips_7d'     => (int)($act_c[$cid]['ips7'] ?? 0),
                    'accion_post_cambios' => (function () use ($me, $acc, $cid) {
                        $pc = $me[$cid]['postura'] ?? null;
                        if (!$pc || $pc['estado'] !== 'pidio_cambios') return false;
                        $ua = $acc[$cid] ?? null;
                        return $ua && strtotime($ua) > strtotime($pc['at']);
                    })(),
                    'p75' => $p75, 'mediana' => (int)($ciclo['mediana'] ?? $p75),
                    'ticket_empresa' => $ticket_empresa,
                    'arquetipo' => $arquetipo,
                ]),
            ];
        }

        // Orden: revividas/milagros arriba → en ventana → cerrándose;
        // dentro: calor del Radar primero, luego monto DESC
        $prio_bucket = array_flip(self::HOT);
        usort($rows, function ($a, $b) use ($prio_bucket) {
            $ga = in_array($a['cat'], ['revivida','milagro'], true) ? 0 : 1;
            $gb = in_array($b['cat'], ['revivida','milagro'], true) ? 0 : 1;
            if ($ga !== $gb) return $ga <=> $gb;
            if ($ga === 1 && $a['tier'] !== $b['tier']) return $a['tier'] <=> $b['tier'];
            $ha = $prio_bucket[$a['bucket']] ?? 99;
            $hb = $prio_bucket[$b['bucket']] ?? 99;
            if ($ha !== $hb) return $ha <=> $hb;
            return $b['total'] <=> $a['total'];
        });

        // Caps: milagros/revividas 6, mesa 25
        $t3 = 0; $capped = [];
        foreach ($rows as $r) {
            if (in_array($r['cat'], ['revivida','milagro'], true)) {
                $t3++;
                if ($t3 > 6) continue;
            }
            if (count($capped) >= self::CAP_MESA) break;
            $capped[] = $r;
        }
        $rows = $capped;

        $sin_postura = 0; $monto = 0.0; $mas_viejo = 0; $atendidas = 0; $descartadas = 0;
        foreach ($rows as $r) {
            if ($r['cat'] === 'descartada_hoy') { $descartadas++; continue; }
            if ($r['atendida_hoy']) { $atendidas++; continue; }
            $monto += $r['total'];
            if ($r['cat'] === 'sin_postura') {
                $sin_postura++;
                if ($r['edad'] > $mas_viejo) $mas_viejo = $r['edad'];
            }
        }

        return self::$cache[$ck] = [
            'rows'     => $rows,
            'p75'      => $p75,
            'limpieza' => ['n' => $limpieza_n, 'monto' => $limpieza_monto, 'linea_dias' => $linea_limpieza],
            'ciclo'    => $ciclo,
            'resumen'  => ['n' => count($rows) - $atendidas - $descartadas, 'monto' => $monto,
                           'atendidas' => $atendidas, 'descartadas' => $descartadas,
                           'sin_postura' => $sin_postura, 'mas_viejo_dias' => $mas_viejo],
        ];
    }

    /** Resumen agregado por vendedor — para el leaderboard del admin. */
    public static function resumen(int $empresa_id, int $vendedor_id): array
    {
        return self::armar($empresa_id, $vendedor_id)['resumen'];
    }
}
