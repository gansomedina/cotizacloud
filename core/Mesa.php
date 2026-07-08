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
               AND c.estado IN ('enviada','vista') AND c.suspendida = 0 AND c.total > 0",
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
                if (strtotime($r['ult_hot']) > strtotime($fb[$cid]['updated_at'])) {
                    $revividas[$cid] = true;
                }
            }
        }

        // ── Clasificar ────────────────────────────────────────
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
            $postura = $fb[$cid]['tipo'] ?? null;
            $descartada = ($postura === 'sin_interes');

            // Descartada sin revivir → fuera de la mesa (el Radar la vigila)
            if ($descartada && !isset($revividas[$cid])) {
                if ($edad > $linea_limpieza && !$es_hot) { $limpieza_n++; $limpieza_monto += (float)$c['total']; }
                continue;
            }

            // Tier
            if ($edad <= $p75)            $tier = 1;
            elseif ($edad <= 2 * $p75)    $tier = 2;
            elseif ($hot_reciente || isset($revividas[$cid])) $tier = 3; // milagro / revivida
            else {
                // Fuera de toda ventana y sin calor → candidata a limpieza
                $limpieza_n++; $limpieza_monto += (float)$c['total'];
                continue;
            }

            $rows[] = [
                'id'         => $cid,
                'numero'     => $c['numero'],
                'titulo'     => $c['titulo'],
                'cliente'    => $c['cliente'] ?: '—',
                'telefono'   => $c['cli_tel'],
                'total'      => (float)$c['total'],
                'edad'       => $edad,
                'tier'       => $tier,
                'bucket'     => $bucket,
                'es_hot'     => $es_hot,
                'visitas'    => (int)$c['visitas'],
                'dias_sin_vista' => (int)$c['dias_sin_vista'],
                'postura'    => $postura,
                'ultima_accion' => $acc[$cid] ?? null,
                'revivida'   => isset($revividas[$cid]),
                'dormida'    => ((int)$c['visitas'] > 0 && (int)$c['dias_sin_vista'] >= 7),
                'sugerencia' => self::sugerencia($bucket, $postura, $tier, $edad, $p75, isset($revividas[$cid])),
            ];
        }

        // ── Orden: milagros → sin postura → tier → calor → monto ──
        $prio_bucket = array_flip(self::HOT); // menor índice = más caliente
        usort($rows, function ($a, $b) use ($prio_bucket) {
            if ($a['tier'] === 3 xor $b['tier'] === 3) return $a['tier'] === 3 ? -1 : 1;
            $ap = $a['postura'] === null ? 0 : 1;
            $bp = $b['postura'] === null ? 0 : 1;
            if ($ap !== $bp) return $ap <=> $bp;
            if ($a['tier'] !== $b['tier']) return $a['tier'] <=> $b['tier'];
            $ah = $prio_bucket[$a['bucket']] ?? 99;
            $bh = $prio_bucket[$b['bucket']] ?? 99;
            if ($ah !== $bh) return $ah <=> $bh;
            return $b['total'] <=> $a['total'];
        });

        // Caps
        $milagros = 0;
        $rows = array_values(array_filter($rows, function ($r) use (&$milagros) {
            if ($r['tier'] === 3) { $milagros++; return $milagros <= self::CAP_MILAGROS; }
            return true;
        }));
        $rows = array_slice($rows, 0, self::CAP_MESA);

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

    // ── Sugerencia v1: bucket × postura × ventana (sin arquetipo aún) ──
    private static function sugerencia(?string $bucket, ?string $postura, int $tier, int $edad, int $p75, bool $revivida): string
    {
        if ($revivida) return 'La descartaste y el cliente volvió a calentarse — los milagros no se repiten: un mensaje directo, hoy.';
        if ($tier === 3) return 'Fuera de tu ciclo normal y el cliente la está viendo AHORA — es ahora o se va.';

        $ventana = $edad <= $p75
            ? "día {$edad} de un ciclo de ~{$p75}"
            : "día {$edad} — saliendo de tu ventana de cierre (~{$p75}d)";

        return match (true) {
            $bucket === 'validando_precio' || $bucket === 'probable_cierre' && $postura === null
                => "Se está clavando en el precio ({$ventana}). No lo defiendas — llega con la estructura de pago resuelta.",
            $bucket === 'multi_persona'
                => "Varias personas la están evaluando ({$ventana}). Dale municiones al tuyo: garantía y proceso por escrito.",
            $bucket === 'onfire' || $bucket === 'inminente'
                => "Señal fuerte y reciente ({$ventana}). Es tu contacto del día — directo al siguiente paso.",
            $postura === 'con_interes' && $edad > $p75
                => "Tú dijiste que va en serio y ya está en {$ventana}. Último tramo útil — toque con motivo concreto.",
            $bucket === null && $tier === 2
                => "Sin señal del cliente y {$ventana}. Reenvíale el link con una línea nueva, no con 'sigo pendiente'.",
            default
                => "En juego ({$ventana}). Un toque que termine en algo concreto — cita, ajuste o fecha.",
        };
    }

    /** Resumen agregado por vendedor — para el leaderboard del admin. */
    public static function resumen(int $empresa_id, int $vendedor_id): array
    {
        return self::armar($empresa_id, $vendedor_id)['resumen'];
    }
}
