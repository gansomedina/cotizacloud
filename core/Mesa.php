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
    private const HOT = [
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
                    c.radar_bucket, c.radar_bucket_at, c.ultima_vista_at, c.created_at,
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

        // Postura actual (radar_feedback = proyección del juicio; v1 la usa como postura)
        $fb = [];
        foreach (DB::query(
            "SELECT cotizacion_id, tipo, updated_at FROM radar_feedback
             WHERE empresa_id = ? AND cotizacion_id IN ($in)",
            [$empresa_id]
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

            // Descartada sin revivir RECIENTE → fuera (el Radar la vigila)
            if ($descartada && !isset($revividas[$cid])) {
                if ($edad > $linea_limpieza && !$hot_reciente) { $limpieza_n++; $limpieza_monto += (float)$c['total']; }
                continue;
            }
            // Fuera de toda ventana, sin calor reciente, sin revivir → limpieza
            if ($fuera && !$hot_reciente && !isset($revividas[$cid])) {
                if ($edad > $linea_limpieza) { $limpieza_n++; $limpieza_monto += (float)$c['total']; }
                continue;
            }

            // ¿Cuál es LA FALTA? (si no hay falta, no sale en la mesa)
            $cat = null;
            if (isset($revividas[$cid])) {
                $cat = 'revivida';           // tu 👎 dice muerto; el cliente, vivo AHORA
            } elseif ($fuera && $hot_reciente) {
                $cat = 'milagro';            // fuera de ciclo pero viéndola AHORA
            } elseif ($postura === 'con_interes' && ($dormida || $bucket === 'enfriandose')) {
                $cat = 'interes_muriendo';   // dijiste que va en serio y se apaga
            } elseif ($postura === 'con_interes' && $edad > $p75) {
                $cat = 'ultimo_tramo';       // en serio, pero saliendo de tu ventana
            } elseif ($postura === null && $edad >= 1 && ((int)$c['visitas'] > 0 || $es_hot)) {
                $cat = 'sin_postura';        // el cliente ya se movió y tú no lo has juzgado
            }
            if ($cat === null) continue;     // va bien o es fresca → territorio del Radar

            $rows[] = [
                'id' => $cid, 'numero' => $c['numero'], 'titulo' => $c['titulo'],
                'cliente' => $c['cliente'] ?: '—', 'telefono' => $c['cli_tel'],
                'total' => (float)$c['total'], 'edad' => $edad, 'cat' => $cat,
                'bucket' => $bucket, 'es_hot' => $es_hot,
                'visitas' => (int)$c['visitas'], 'dias_sin_vista' => (int)$c['dias_sin_vista'],
                'postura' => $postura, 'ultima_accion' => $acc[$cid] ?? null,
                'dormida' => $dormida,
            ];
        }

        // Orden: revividas/milagros → interés muriéndose → sin postura → último tramo; monto DESC dentro
        $cat_prio = ['revivida' => 0, 'milagro' => 1, 'interes_muriendo' => 2, 'sin_postura' => 3, 'ultimo_tramo' => 4];
        usort($rows, function ($a, $b) use ($cat_prio) {
            $d = $cat_prio[$a['cat']] <=> $cat_prio[$b['cat']];
            return $d !== 0 ? $d : ($b['total'] <=> $a['total']);
        });

        // Caps: 4 por categoría, 12 total (una mesa, no una lista)
        $por_cat = []; $capped = [];
        foreach ($rows as $r) {
            $por_cat[$r['cat']] = ($por_cat[$r['cat']] ?? 0) + 1;
            if ($por_cat[$r['cat']] <= 4 && count($capped) < 12) $capped[] = $r;
        }
        $rows = $capped;

        $sin_postura = 0; $monto = 0.0; $mas_viejo = 0;
        foreach ($rows as $r) {
            $monto += $r['total'];
            if ($r['postura'] === null) {
                $sin_postura++;
                if ($r['edad'] > $mas_viejo) $mas_viejo = $r['edad'];
            }
        }

        return self::$cache[$ck] = [
            'rows'     => $rows,
            'limpieza' => ['n' => $limpieza_n, 'monto' => $limpieza_monto, 'linea_dias' => $linea_limpieza],
            'ciclo'    => $ciclo,
            'resumen'  => ['n' => count($rows), 'monto' => $monto,
                           'sin_postura' => $sin_postura, 'mas_viejo_dias' => $mas_viejo],
        ];
    }

    /** Resumen agregado por vendedor — para el leaderboard del admin. */
    public static function resumen(int $empresa_id, int $vendedor_id): array
    {
        return self::armar($empresa_id, $vendedor_id)['resumen'];
    }
}
