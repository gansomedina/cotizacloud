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

            // Sin señal del cliente (nunca abrió) y sin calor → no es trabajo
            // de mesa (la tarjeta "Sin abrir" del dashboard ya la cubre)
            if (!$es_hot && (int)$c['visitas'] === 0) continue;

            // Categoría: faltas/contradicciones primero; lo demás = trabajo del día
            if (isset($revividas[$cid])) {
                $cat = 'revivida';           // tu 👎 dice muerto; el cliente, vivo AHORA
            } elseif ($fuera && $hot_reciente) {
                $cat = 'milagro';            // fuera de ciclo pero viéndola AHORA
            } elseif ($postura === 'con_interes' && ($dormida || $bucket === 'enfriandose')) {
                $cat = 'interes_muriendo';   // dijiste que va en serio y se apaga
            } elseif ($postura === null && ((int)$c['visitas'] > 0 || $es_hot)) {
                $cat = 'sin_postura';        // el cliente ya se movió y tú no lo has juzgado
            } elseif ($postura === 'con_interes' && $edad > $p75) {
                $cat = 'ultimo_tramo';       // en serio, pero saliendo de tu ventana
            } else {
                $cat = 'trabajo';            // buena y en ventana → a cerrarla
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
                'revivida' => ($cat === 'revivida'),
                'milagro'  => ($cat === 'milagro'),
                'fuera_ventana' => ($edad > $p75),
                'sugerencia' => self::sugerencia($cat, $bucket, $postura, $edad, $p75),
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
            'p75'      => $p75,
            'limpieza' => ['n' => $limpieza_n, 'monto' => $limpieza_monto, 'linea_dias' => $linea_limpieza],
            'ciclo'    => $ciclo,
            'resumen'  => ['n' => count($rows), 'monto' => $monto,
                           'sin_postura' => $sin_postura, 'mas_viejo_dias' => $mas_viejo],
        ];
    }

    // ── Sugerencia por fila (bucket × categoría-de-falta × ventana) ──
    private static function sugerencia(string $cat, ?string $bucket, ?string $postura, int $edad, int $p75): string
    {
        if ($cat === 'revivida')
            return 'La descartaste y el cliente volvió a calentarse esta semana — los milagros no se repiten: un mensaje directo hoy.';
        if ($cat === 'milagro')
            return 'Fuera de tu ciclo normal y la está viendo AHORA — es ahora o se va.';
        if ($cat === 'interes_muriendo')
            return 'Tú dijiste que va en serio y el cliente se está apagando — rescátala hoy con un motivo concreto, o corrige tu postura.';
        if ($cat === 'ultimo_tramo')
            return "Va en serio pero ya está en día {$edad}, saliendo de tu ventana (~{$p75}d) — último tramo útil.";
        if ($cat === 'trabajo') {
            if ($postura === 'con_interes' && $es_hot_txt = true) {
                return match ($bucket) {
                    'validando_precio' => "Va en serio y está validando el precio (día {$edad} de ~{$p75}) — llega con la estructura de pago, no defiendas el número.",
                    'multi_persona'    => "Va en serio y varias personas la evalúan — dale municiones: garantía y proceso por escrito.",
                    'onfire', 'inminente', 'probable_cierre'
                                       => "Va en serio y el Radar la marca fuerte (día {$edad} de ~{$p75}) — ciérrala esta semana.",
                    default            => "Va en serio y está en tu ventana (día {$edad} de ~{$p75}) — un toque que termine en algo concreto.",
                };
            }
            return "En ventana (día {$edad} de ~{$p75}) — trabájala hoy: cita, ajuste o fecha.";
        }
        // sin_postura → según lo que muestra el Radar
        return match ($bucket) {
            'validando_precio', 'probable_cierre'
                => 'Se está clavando en el precio y no lo has calificado — ¿cómo lo ves? No lo defiendas, llega con la estructura de pago.',
            'multi_persona'
                => 'Varias personas la evalúan y falta tu juicio — dale municiones al tuyo: garantía y proceso por escrito.',
            'onfire', 'inminente'
                => 'Señal fuerte y reciente sin tu juicio — es tu contacto del día.',
            default
                => 'El cliente ya se movió y no la has calificado — ¿cómo lo ves? (👍/👎)',
        };
    }

    /** Resumen agregado por vendedor — para el leaderboard del admin. */
    public static function resumen(int $empresa_id, int $vendedor_id): array
    {
        return self::armar($empresa_id, $vendedor_id)['resumen'];
    }
}
