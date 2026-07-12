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
    private const CAP_MILAGROS = 6;

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
        $p75   = ($ciclo['auto'] && isset($ciclo['p75']) && $ciclo['p75'] !== null) ? max(1, (int)$ciclo['p75']) : 30;

        // Cierre más tardío de la historia de la empresa (línea de evidencia)
        // — cacheado: con la mesa por-asesor, armar() corre N veces por carga
        static $mh_cache = [];
        if (!array_key_exists($empresa_id, $mh_cache)) {
            $mh_cache[$empresa_id] = (int)DB::val(
                "SELECT COALESCE(MAX(DATEDIFF(v.created_at, c.created_at)), 0)
                 FROM ventas v JOIN cotizaciones c ON c.id = v.cotizacion_id
                 WHERE v.empresa_id = ? AND v.estado != 'cancelada'
                   AND DATEDIFF(v.created_at, c.created_at) >= 0",
                [$empresa_id]
            );
        }
        $max_hist = $mh_cache[$empresa_id];
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
                'rows' => [], 'p75' => $p75,
                'limpieza' => ['n' => 0, 'monto' => 0.0, 'linea_dias' => $linea_limpieza],
                'ciclo' => $ciclo, 'resumen' => ['n' => 0, 'monto' => 0.0, 'sin_postura' => 0, 'universo' => 0,
                                                 'mas_viejo_dias' => 0, 'atendidas' => 0, 'descartadas' => 0],
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
                   AND created_at >= NOW() - INTERVAL 30 DAY
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

        // Resurrección: descartadas cuyo cliente volvió DESPUÉS del descarte.
        // Regla ÚNICA basada en VISTA REAL (se calcula dentro del loop con
        // ultima_vista_at). Las transiciones de bucket NO bastan: un recálculo
        // del Radar puede escribir una transición caliente→caliente sin
        // actividad del cliente (flapping) y fabricar una ⚡ falsa — caso
        // COT-2026-0246 verificado en producción (12 jul 2026).
        $revividas = [];

        // "Hoy" del RELOJ DE LA BD (no de PHP): si los timezone difieren,
        // strtotime('today') haría desaparecer el descarte de hoy al instante
        static $hoy_cache = null;
        $hoy_db = $hoy_cache ??= (string)DB::val("SELECT CURDATE()");

        // ── Clasificar por CATEGORÍA DE FALTA ────────────────
        // La mesa NO repite el Radar: solo muestra donde falta o se
        // contradice la ACCIÓN/JUICIO del asesor. Lo que va bien, no sale.
        $rows = [];
        $limpieza_n = 0; $limpieza_monto = 0.0;
        $now = time();

        foreach ($cots as $c) {
            $cid    = (int)$c['id'];
            $sen    = $c['radar_senales'] ? (json_decode($c['radar_senales'], true) ?: []) : [];
            $edad   = (int)$c['edad'];
            $bucket = $c['radar_bucket'];
            $es_hot = $bucket !== null && in_array($bucket, self::HOT, true);
            // bucket_at solo se actualiza cuando el bucket CAMBIA: calor sostenido
            // (cliente activo, bucket sin cambiar de nombre) también cuenta como reciente
            $hot_reciente = $es_hot && (
                ($c['radar_bucket_at'] && strtotime($c['radar_bucket_at']) >= $now - 7 * 86400)
                || (int)($act_c[$cid]['v7'] ?? 0) > 0
            );
            $postura    = $fb[$cid]['tipo'] ?? null;
            // Descartada = 👎 del dueño O postura vigente 'descartada' (misma
            // definición doble que el reporte — reasignadas/legacy incluidas)
            $post_desc  = (($me[$cid]['postura']['estado'] ?? '') === 'descartada');
            // Un juicio POSITIVO posterior anula la postura descartada: el tap
            // de 👍/compromiso corrige el feedback pero nunca escribe postura —
            // sin este guard la cotización corregida se trataba como muerta
            // (desaparecía de la mesa e inflaba Recuperado)
            if ($post_desc) {
                $post_t = strtotime($me[$cid]['postura']['at']);
                $fbh = $me[$cid]['feedback'] ?? null;
                if ($fbh && $fbh['estado'] === 'con_interes' && strtotime($fbh['at']) > $post_t) $post_desc = false;
                if ($postura === 'con_interes'
                    && strtotime($fb[$cid]['updated_at'] ?? '') > $post_t) $post_desc = false;
            }
            $descartada = ($postura === 'sin_interes') || $post_desc;
            $desc_at    = max(
                $postura === 'sin_interes' ? strtotime($fb[$cid]['updated_at'] ?? '') : 0,
                $post_desc ? strtotime($me[$cid]['postura']['at']) : 0
            );
            $dormida    = ((int)$c['visitas'] > 0 && (int)$c['dias_sin_vista'] >= 7);
            $fuera      = $edad > 2 * $p75;

            // Revivida = el cliente ABRIÓ después del descarte, dentro de los
            // últimos 7 días. Ancla = el ÚLTIMO juicio del dueño ($desc_at es
            // max de las dos fuentes): re-confirmar el 👎 apaga la ⚡.
            if ($descartada && $desc_at
                && $c['ultima_vista_at']
                && strtotime($c['ultima_vista_at']) > $desc_at
                && strtotime($c['ultima_vista_at']) >= $now - 7 * 86400) {
                $revividas[$cid] = true;
            }

            // Descartada HOY (día del reloj de la BD): visible un día en su sección
            $descartada_hoy = $descartada && $desc_at && date('Y-m-d', $desc_at) === $hoy_db;
            // Descartada de días anteriores sin revivir → fuera (el Radar la vigila)
            if ($descartada && !isset($revividas[$cid]) && !$descartada_hoy) {
                if ($edad > $linea_limpieza && !$hot_reciente) { $limpieza_n++; $limpieza_monto += (float)$c['total']; }
                continue;
            }
            // Fuera de toda ventana, sin calor reciente, sin revivir → limpieza
            // (descartada_hoy se respeta: prometimos visible un día)
            if ($fuera && !$hot_reciente && !isset($revividas[$cid]) && !$descartada_hoy) {
                if ($edad > $linea_limpieza) { $limpieza_n++; $limpieza_monto += (float)$c['total']; }
                continue;
            }

            // Sin señal del cliente (nunca abrió) y sin calor → no es trabajo
            // de mesa (la tarjeta "Sin abrir" del dashboard ya la cubre).
            // Las revividas se respetan (ghost cleanup puede dejar visitas=0)
            if (!$es_hot && (int)$c['visitas'] === 0 && !$descartada_hoy && !isset($revividas[$cid])) continue;

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
            } elseif (empty(array_diff_key($me[$cid] ?? [], ['feedback' => 1])) && $postura === null) {
                $cat = 'sin_postura';        // nada capturado ni marcado aún
                                             // (filas 'feedback' de dueños anteriores no cuentan como captura)
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
                'atendida_hoy' => (function () use ($me, $cid, $hoy_db) {
                    foreach (($me[$cid] ?? []) as $a => $d) {
                        if ($a === 'feedback') continue; // un like solo no es atención
                        if (substr($d['at'], 0, 10) === $hoy_db) return true;
                    }
                    return false;
                })(),
                // Días desde la última declaración en la mesa (null = nunca)
                'ult_decl_dias' => (function () use ($me, $cid) {
                    $max = 0;
                    foreach (($me[$cid] ?? []) as $a => $d) {
                        if ($a === 'feedback') continue;
                        $t = strtotime($d['at']);
                        if ($t > $max) $max = $t;
                    }
                    return $max ? (int)floor((time() - $max) / 86400) : null;
                })(),
                'revivida' => ($cat === 'revivida'),
                'milagro'  => ($cat === 'milagro'),
                'fuera_ventana' => ($edad > $p75),
                'sugerencia' => MesaSugerencias::sugerir([
                    'cot_id' => $cid,
                    'total' => (float)$c['total'], 'edad' => $edad, 'cat' => $cat,
                    'bucket' => $bucket, 'es_hot' => $es_hot && $hot_reciente,
                    'pc_source' => $sen['pc_source'] ?? null,
                    'momentum'  => $sen['momentum'] ?? null,
                    'fit_pct'   => (int)($sen['fit_pct'] ?? 0),
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
                    'accion_post_cambios' => ($apc_at = (function () use ($me, $acc, $cid) {
                        $pc = $me[$cid]['postura'] ?? null;
                        if (!$pc || $pc['estado'] !== 'pidio_cambios') return null;
                        $ua = $acc[$cid] ?? null;
                        // Retorna el TIMESTAMP del edit — "ya vio la versión
                        // nueva" exige vista POSTERIOR al edit, no a la postura
                        return ($ua && strtotime($ua) > strtotime($pc['at'])) ? $ua : null;
                    })()) !== null,
                    'accion_post_cambios_at' => $apc_at,
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
            // Dentro del grupo urgente, la revivida manda sobre el milagro
            // (es la promesa "si la reabre, vuelve sola" — no debe caerse del cap)
            if ($ga === 0 && $a['cat'] !== $b['cat']) return $a['cat'] === 'revivida' ? -1 : 1;
            if ($ga === 1 && $a['tier'] !== $b['tier']) return $a['tier'] <=> $b['tier'];
            $ha = $prio_bucket[$a['bucket']] ?? 99;
            $hb = $prio_bucket[$b['bucket']] ?? 99;
            if ($ha !== $hb) return $ha <=> $hb;
            return ($b['total'] <=> $a['total']) ?: ($a['id'] <=> $b['id']);
        });

        // Caps: milagros/revividas 6, mesa 25
        $universo = count($rows);
        $t3 = 0; $capped = [];
        foreach ($rows as $r) {
            if (in_array($r['cat'], ['revivida','milagro'], true)) {
                $t3++;
                if ($t3 > self::CAP_MILAGROS) continue;
            }
            // Promesa "visible un día": descartada_hoy no se corta por el cap
            if (count($capped) >= self::CAP_MESA && $r['cat'] !== 'descartada_hoy') continue;
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
                           'atendidas' => $atendidas, 'descartadas' => $descartadas, 'universo' => $universo,
                           'sin_postura' => $sin_postura, 'mas_viejo_dias' => $mas_viejo],
        ];
    }

    /** Resumen agregado por vendedor — para el leaderboard del admin. */
    public static function resumen(int $empresa_id, int $vendedor_id): array
    {
        return self::armar($empresa_id, $vendedor_id)['resumen'];
    }

    /**
     * FUENTE ÚNICA de cobertura de señales 🔥 — la consumen el reporte del
     * equipo, el score (25% del Seguimiento) y el widget del asesor. Si el
     * número que el asesor ve difiere del que lo examina, es un bug.
     *
     * Episodio = transición a bucket hot (set Mesa::HOT) sin otra transición
     * hot en los 3 días previos (rebotes no son señales nuevas). Solo se
     * juzgan episodios con la ventana CERRADA (3+ días). Atendida = captura
     * de mesa, 👍👎 del dueño o venta dentro de los 3 días — o cierre de la
     * cotización después de la señal (venta/respuesta del cliente = el
     * desenlace llegó; los taps se bloquean en cerradas).
     *
     * @return array $vendedor_id=null → [uid => ['pedidas','atendidas','fallas']]
     *               $vendedor_id=int  → ['pedidas','atendidas','fallas']
     */
    public static function cobertura_senales(int $empresa_id, ?int $vendedor_id = null, int $dias = 30): array
    {
        $dias   = max(1, (int)$dias);
        $hot_in = "'" . implode("','", self::HOT) . "'";
        $uf     = $vendedor_id !== null
            ? 'AND COALESCE(c2.vendedor_id, c2.usuario_id) = ' . (int)$vendedor_id : '';
        $out = [];
        try {
            foreach (DB::query(
                "SELECT uid, COUNT(*) AS pedidas,
                        SUM(NOT (ate_mesa OR ate_fb OR ate_venta OR ate_cierre)) AS fallas
                 FROM (
                    SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid,
                           EXISTS (SELECT 1 FROM mesa_estados m
                                   WHERE m.cotizacion_id = s.cotizacion_id
                                     AND m.created_at >= s.senal_at
                                     AND m.created_at <= s.senal_at + INTERVAL 3 DAY) AS ate_mesa,
                           EXISTS (SELECT 1 FROM radar_feedback rf
                                   WHERE rf.cotizacion_id = s.cotizacion_id
                                     AND rf.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                     AND rf.updated_at >= s.senal_at
                                     AND rf.updated_at <= s.senal_at + INTERVAL 3 DAY) AS ate_fb,
                           EXISTS (SELECT 1 FROM ventas v
                                   WHERE v.cotizacion_id = s.cotizacion_id AND v.estado != 'cancelada'
                                     AND v.created_at >= s.senal_at) AS ate_venta,
                           (c.accion_at IS NOT NULL AND c.accion_at >= s.senal_at) AS ate_cierre
                    FROM (SELECT bt.cotizacion_id, bt.created_at AS senal_at
                          FROM bucket_transitions bt
                          JOIN cotizaciones c2 ON c2.id = bt.cotizacion_id
                          WHERE c2.empresa_id = ? AND bt.bucket_nuevo IN ($hot_in)
                            AND c2.suspendida = 0 AND c2.total > 0 $uf
                            AND bt.created_at >= NOW() - INTERVAL $dias DAY
                            AND bt.created_at <= NOW() - INTERVAL 3 DAY
                            AND NOT EXISTS (SELECT 1 FROM bucket_transitions bt2
                                            WHERE bt2.cotizacion_id = bt.cotizacion_id
                                              AND bt2.bucket_nuevo IN ($hot_in)
                                              AND (bt2.created_at < bt.created_at
                                                   OR (bt2.created_at = bt.created_at AND bt2.id < bt.id))
                                              AND bt2.created_at >= bt.created_at - INTERVAL 3 DAY)) s
                    JOIN cotizaciones c ON c.id = s.cotizacion_id
                 ) sen
                 GROUP BY uid", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid'];
                $out[$u] = [
                    'pedidas'   => (int)$r['pedidas'],
                    'atendidas' => (int)$r['pedidas'] - (int)$r['fallas'],
                    'fallas'    => (int)$r['fallas'],
                ];
            }
        } catch (Throwable $e) {
            // FAIL-NEUTRAL, no fail-open: un error SQL no debe regalar el 25%
            // del score. El flag 'error' hace que ActividadScore salte el blend.
            error_log('[Mesa cobertura] ' . $e->getMessage());
            if ($vendedor_id !== null) {
                return ['pedidas' => 0, 'atendidas' => 0, 'fallas' => 0, 'error' => true];
            }
            return [];
        }
        if ($vendedor_id !== null) {
            return $out[$vendedor_id] ?? ['pedidas' => 0, 'atendidas' => 0, 'fallas' => 0];
        }
        return $out;
    }

    /**
     * Desglose de señales del asesor para SU widget de cobertura: cada
     * episodio con folio, fecha y estado (atendida / vencida / por vencer).
     * Incluye episodios con ventana ABIERTA (aún no se juzgan) para que el
     * "vence mañana" sea accionable. Mismas reglas que cobertura_senales().
     */
    public static function cobertura_detalle(int $empresa_id, int $vendedor_id, int $dias = 30): array
    {
        $dias   = max(1, (int)$dias);
        $hot_in = "'" . implode("','", self::HOT) . "'";
        try {
            return DB::query(
                "SELECT s.cotizacion_id, c.numero, c.titulo, s.senal_at,
                        (s.senal_at <= NOW() - INTERVAL 3 DAY) AS cerrada,
                        (EXISTS (SELECT 1 FROM mesa_estados m
                                 WHERE m.cotizacion_id = s.cotizacion_id
                                   AND m.created_at >= s.senal_at
                                   AND m.created_at <= s.senal_at + INTERVAL 3 DAY)
                      OR EXISTS (SELECT 1 FROM radar_feedback rf
                                 WHERE rf.cotizacion_id = s.cotizacion_id
                                   AND rf.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                   AND rf.updated_at >= s.senal_at
                                   AND rf.updated_at <= s.senal_at + INTERVAL 3 DAY)
                      OR EXISTS (SELECT 1 FROM ventas v
                                 WHERE v.cotizacion_id = s.cotizacion_id AND v.estado != 'cancelada'
                                   AND v.created_at >= s.senal_at)
                      OR (c.accion_at IS NOT NULL AND c.accion_at >= s.senal_at)) AS atendida
                 FROM (SELECT bt.cotizacion_id, bt.created_at AS senal_at
                       FROM bucket_transitions bt
                       JOIN cotizaciones c2 ON c2.id = bt.cotizacion_id
                       WHERE c2.empresa_id = ? AND bt.bucket_nuevo IN ($hot_in)
                         AND c2.suspendida = 0 AND c2.total > 0
                         AND COALESCE(c2.vendedor_id, c2.usuario_id) = ?
                         AND bt.created_at >= NOW() - INTERVAL $dias DAY
                         AND NOT EXISTS (SELECT 1 FROM bucket_transitions bt2
                                         WHERE bt2.cotizacion_id = bt.cotizacion_id
                                           AND bt2.bucket_nuevo IN ($hot_in)
                                           AND (bt2.created_at < bt.created_at
                                                OR (bt2.created_at = bt.created_at AND bt2.id < bt.id))
                                           AND bt2.created_at >= bt.created_at - INTERVAL 3 DAY)) s
                 JOIN cotizaciones c ON c.id = s.cotizacion_id
                 ORDER BY s.senal_at DESC", [$empresa_id, $vendedor_id]
            );
        } catch (Throwable $e) { return []; }
    }

    /**
     * Reporte del equipo — métricas por asesor de lo capturado en la mesa
     * Y de lo que NO ha hecho (para eso evalúa el dueño).
     *
     * Atribución: por el asesor DUEÑO de la cotización (vendedor_id/usuario_id),
     * no por quién dio el tap — hoy tapea el admin pero el crédito va al asesor.
     *
     * CARTERA (foto de hoy):
     * - activas:        cotizaciones vivas asignadas (mismo universo que la mesa)
     * - sin_calificar:  activas sin NINGÚN juicio (ni "¿Cómo lo ves?" ni 👍👎)
     * - sin_trabajar:   activas sin UNA SOLA captura en la mesa + monto
     * - se_fueron:      pasaron su ventana (p75) ABANDONADAS: sin ninguna
     *                   atención (captura, 👍👎, edición/reenvío) en los
     *                   últimos p75/2 días (mín. 3). Justa: mide atención,
     *                   nunca el resultado de la venta; descartarla con 👎
     *                   cuenta como decisión tomada y la excluye
     * - hot_desatendidas: episodios 🔥 del período sin acción en los 3 días
     *                   siguientes a la señal. Por episodio (rebotes entre
     *                   buckets calientes no cuentan doble) y con ventana:
     *                   atender hoy no perdona la señal ignorada hace semanas
     *
     * TRABAJO (últimos N días) — PRINCIPIO ÚNICO (no desviarse al editar):
     * el estado VIGENTE manda y el PARADERO decide dónde se juzga cada
     * cotización: viva → compromiso/cumplidos; vendida/aceptada → sigue
     * contando ahí (éxito del acuerdo); DESCARTADA → sale completa de las
     * métricas de acuerdo (ni numerador ni denominador) y se juzga en
     * revividos/recuperado. Los toques cuentan siempre (esfuerzo real).
     *
     * - contacto:    conteo de toques declarados → % le contesta
     * - compromiso:  de las cotizaciones con plática, en cuántas quedó en algo
     *                → % genera compromiso. POR COTIZACIÓN, no por tap:
     *                re-declarar la misma pill no infla el número
     * - cumplidos:   de los "Quedamos en algo" con 5+ días de maduración,
     *                en cuántos el cliente SE MOVIÓ en los 5 días siguientes
     *                (abrió la cotización o compró) — hecho observable, no juicio
     * - postura:     distribución de "¿Cómo lo ves?" (última por cotización)
     * - revividos:   de sus 👎, cuántos el cliente volvió a calentar después
     * - recuperado:  su rebanada del contador (descartada antes, vendida después)
     */
    public static function reporte(int $empresa_id, int $dias = 30): array
    {
        $dias = max(1, (int)$dias);
        $out  = [];
        $base = [
            'nombre' => '', 'hablamos' => 0, 'no_contesta' => 0,
            'hablamos_cots' => 0,
            'con_compromiso' => 0, 'no_quiso' => 0, 'sin_compromiso' => 0,
            'compromiso_cots' => [],
            'comp_maduros' => 0, 'comp_cumplidos' => 0, 'comp_en_curso' => 0,
            'postura' => [], 'descartes' => 0, 'revividos' => 0,
            'rec_n' => 0, 'rec_monto' => 0.0,
            'activas' => 0, 'sin_calificar' => 0,
            'sin_trabajar' => 0, 'monto_sin_trabajar' => 0.0,
            'se_fueron' => 0, 'monto_se_fueron' => 0.0,
            'hot_total' => 0, 'hot_desatendidas' => 0,
        ];

        try {
            // Ventana real de la empresa (misma que usa la mesa)
            if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
            $ciclo = Radar::ciclo_venta($empresa_id);
            $p75   = ($ciclo['auto'] && isset($ciclo['p75']) && $ciclo['p75'] !== null) ? max(1, (int)$ciclo['p75']) : 30;

            // 0) CARTERA — la foto de hoy: qué tiene cada asesor y qué NO ha hecho.
            //    "sin_trabajar" = ni una captura en la mesa NI un 👍👎 en el Radar.
            //    "se_fueron" = pasó la ventana Y lleva $k+ días sin NINGUNA
            //    atención (captura, feedback, edición/reenvío). Mide atención,
            //    no ventas. La descartada (👎 vigente) queda fuera: descartar
            //    ES la decisión correcta para una muerta — el Radar la vigila.
            $k = max(3, (int)ceil($p75 / 2)); // cadencia justa, proporcional al ciclo
            foreach (DB::query(
                "SELECT uid, COUNT(*) AS activas,
                        SUM(sin_calificar) AS sin_calificar,
                        SUM(sin_trabajar)  AS sin_trabajar,
                        SUM(CASE WHEN sin_trabajar THEN total ELSE 0 END) AS monto_sin_trabajar,
                        SUM(fuera AND abandonada AND NOT descartada) AS se_fueron,
                        SUM(CASE WHEN fuera AND abandonada AND NOT descartada THEN total ELSE 0 END) AS monto_se_fueron
                 FROM (
                    SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid, c.total,
                           (DATEDIFF(NOW(), c.created_at) > $p75) AS fuera,
                           (NOT EXISTS (SELECT 1 FROM mesa_estados m
                                        WHERE m.cotizacion_id = c.id
                                          AND m.area IN ('contacto','compromiso','postura'))
                            AND NOT EXISTS (SELECT 1 FROM radar_feedback rf
                                            WHERE rf.cotizacion_id = c.id
                                              AND rf.usuario_id = COALESCE(c.vendedor_id, c.usuario_id))) AS sin_trabajar,
                           (NOT EXISTS (SELECT 1 FROM mesa_estados m2
                                        WHERE m2.cotizacion_id = c.id AND m2.area = 'postura')
                            AND NOT EXISTS (SELECT 1 FROM radar_feedback rf2
                                            WHERE rf2.cotizacion_id = c.id
                                              AND rf2.usuario_id = COALESCE(c.vendedor_id, c.usuario_id))) AS sin_calificar,
                           (NOT EXISTS (SELECT 1 FROM mesa_estados m3
                                        WHERE m3.cotizacion_id = c.id
                                          AND m3.created_at >= NOW() - INTERVAL $k DAY)
                            AND NOT EXISTS (SELECT 1 FROM radar_feedback rf3
                                            WHERE rf3.cotizacion_id = c.id
                                              AND rf3.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                              AND rf3.updated_at >= NOW() - INTERVAL $k DAY)
                            AND NOT EXISTS (SELECT 1 FROM cotizacion_log cl
                                            WHERE cl.cotizacion_id = c.id AND cl.usuario_id IS NOT NULL
                                              AND COALESCE(cl.accion, cl.evento) IN ('editada','enviada')
                                              AND cl.created_at >= NOW() - INTERVAL $k DAY)) AS abandonada,
                           (EXISTS (SELECT 1 FROM radar_feedback rf4
                                    WHERE rf4.cotizacion_id = c.id
                                      AND rf4.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                      AND rf4.tipo = 'sin_interes')
                            OR ((SELECT mp0.estado FROM mesa_estados mp0
                                WHERE mp0.cotizacion_id = c.id AND mp0.area = 'postura'
                                ORDER BY mp0.id DESC LIMIT 1) <=> 'descartada'
                                AND NOT EXISTS (SELECT 1 FROM mesa_estados mfp0
                                     WHERE mfp0.cotizacion_id = c.id AND mfp0.area = 'feedback'
                                       AND mfp0.estado = 'con_interes'
                                       AND mfp0.id > (SELECT MAX(mp02.id) FROM mesa_estados mp02
                                                      WHERE mp02.cotizacion_id = c.id AND mp02.area = 'postura')))) AS descartada
                    FROM cotizaciones c
                    WHERE c.empresa_id = ? AND c.estado IN ('enviada','vista')
                      AND c.suspendida = 0 AND c.total > 0 AND c.accion_at IS NULL
                      AND NOT EXISTS (SELECT 1 FROM ventas v
                                      WHERE v.cotizacion_id = c.id AND v.estado != 'cancelada')
                 ) cart
                 GROUP BY uid", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['activas']            = (int)$r['activas'];
                $out[$u]['sin_calificar']      = (int)$r['sin_calificar'];
                $out[$u]['sin_trabajar']       = (int)$r['sin_trabajar'];
                $out[$u]['monto_sin_trabajar'] = (float)$r['monto_sin_trabajar'];
                $out[$u]['se_fueron']          = (int)$r['se_fueron'];
                $out[$u]['monto_se_fueron']    = (float)$r['monto_se_fueron'];
            }

            // 0b) Señales calientes DESATENDIDAS — FUENTE ÚNICA (helper
            //     cobertura_senales: mismas reglas para reporte, score y widget)
            foreach (self::cobertura_senales($empresa_id, null, $dias) as $u => $cs) {
                if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['hot_total']        = $cs['pedidas'];
                $out[$u]['hot_desatendidas'] = $cs['fallas'];
            }

            // 1) Contacto: cada declaración es un toque registrado
            foreach (DB::query(
                "SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid,
                        SUM(m.estado = 'hablamos')    AS hablamos,
                        SUM(m.estado = 'no_contesta') AS no_contesta
                 FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
                 WHERE m.empresa_id = ? AND m.area = 'contacto'
                   AND m.created_at >= NOW() - INTERVAL $dias DAY
                 GROUP BY uid", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['hablamos']    = (int)$r['hablamos'];
                $out[$u]['no_contesta'] = (int)$r['no_contesta'];
            }

            // 2) Desenlaces de plática — POR COTIZACIÓN y por estado VIGENTE
            //    (un compromiso luego cambiado a "Nada" cuenta como "Nada",
            //    igual que en la mesa). Denominador: cotizaciones con
            //    conversación declarada en el período — plática O desenlace
            //    (un compromiso implica plática aunque el "hablamos" haya
            //    quedado fuera del período) — el numerador siempre cabe.
            foreach (DB::query(
                "SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid,
                        COUNT(DISTINCT m.cotizacion_id) AS hablamos_cots
                 FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
                 WHERE m.empresa_id = ? AND m.created_at >= NOW() - INTERVAL $dias DAY
                   AND ((m.area = 'contacto' AND m.estado = 'hablamos') OR m.area = 'compromiso')
                   AND NOT ((SELECT mp.estado FROM mesa_estados mp
                             WHERE mp.cotizacion_id = m.cotizacion_id AND mp.area = 'postura'
                             ORDER BY mp.id DESC LIMIT 1) <=> 'descartada'
                            AND NOT EXISTS (SELECT 1 FROM mesa_estados mfp
                                 WHERE mfp.cotizacion_id = m.cotizacion_id AND mfp.area = 'feedback'
                                   AND mfp.estado = 'con_interes'
                                   AND mfp.id > (SELECT MAX(mp2.id) FROM mesa_estados mp2
                                                 WHERE mp2.cotizacion_id = m.cotizacion_id AND mp2.area = 'postura')))
                   AND NOT EXISTS (SELECT 1 FROM radar_feedback rf
                                   WHERE rf.cotizacion_id = m.cotizacion_id
                                     AND rf.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                     AND rf.tipo = 'sin_interes')
                 GROUP BY uid", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['hablamos_cots'] = (int)$r['hablamos_cots'];
            }
            //    Filas por cotización (no agregado) para poder DESGLOSAR el
            //    numerador en la UI: folio + paradero — un acuerdo puede haber
            //    salido de la mesa (vendida/aceptada/descartada) y aun así
            //    contar en el período; el dueño debe poder rastrearlo.
            //    El acuerdo se toma del estado VIGENTE del área SIN límite de
            //    período (la leyenda promete "el acuerdo VIGENTE"): un acuerdo
            //    longevo con plática nueva cuenta a favor, no en contra. La
            //    membresía sí es del período: solo cotizaciones con conversación
            //    declarada en él (mismo criterio que el denominador).
            foreach (DB::query(
                "SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid,
                        m.estado, c.numero, c.estado AS cot_estado, c.suspendida,
                        EXISTS (SELECT 1 FROM ventas v
                                WHERE v.cotizacion_id = c.id AND v.estado != 'cancelada') AS vendida
                 FROM mesa_estados m
                 JOIN (SELECT cotizacion_id, MAX(id) AS mid FROM mesa_estados
                       WHERE empresa_id = ? AND area = 'compromiso'
                       GROUP BY cotizacion_id) td ON td.mid = m.id
                 JOIN cotizaciones c ON c.id = m.cotizacion_id
                 WHERE EXISTS (SELECT 1 FROM mesa_estados mx
                               WHERE mx.cotizacion_id = m.cotizacion_id
                                 AND mx.created_at >= NOW() - INTERVAL $dias DAY
                                 AND ((mx.area = 'contacto' AND mx.estado = 'hablamos') OR mx.area = 'compromiso'))
                   AND NOT ((SELECT mp.estado FROM mesa_estados mp
                             WHERE mp.cotizacion_id = m.cotizacion_id AND mp.area = 'postura'
                             ORDER BY mp.id DESC LIMIT 1) <=> 'descartada'
                            AND NOT EXISTS (SELECT 1 FROM mesa_estados mfp
                                 WHERE mfp.cotizacion_id = m.cotizacion_id AND mfp.area = 'feedback'
                                   AND mfp.estado = 'con_interes'
                                   AND mfp.id > (SELECT MAX(mp2.id) FROM mesa_estados mp2
                                                 WHERE mp2.cotizacion_id = m.cotizacion_id AND mp2.area = 'postura')))
                   AND NOT EXISTS (SELECT 1 FROM radar_feedback rf2
                                   WHERE rf2.cotizacion_id = m.cotizacion_id
                                     AND rf2.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                     AND rf2.tipo = 'sin_interes')", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                if ($r['estado'] === 'compromiso') {
                    $out[$u]['con_compromiso']++;
                    $out[$u]['compromiso_cots'][] = [
                        'numero' => $r['numero'],
                        'donde'  => ((int)$r['vendida'] ? 'vendida'
                                  : ($r['cot_estado'] === 'aceptada' ? 'aceptada'
                                  : ($r['cot_estado'] === 'rechazada' ? 'rechazada'
                                  : ((int)$r['suspendida'] ? 'suspendida' : 'activa')))),
                    ];
                } elseif ($r['estado'] === 'propuse_no_quiso') {
                    $out[$u]['no_quiso']++;
                } elseif ($r['estado'] === 'sin_compromiso') {
                    $out[$u]['sin_compromiso']++;
                }
            }

            // 3) Compromisos cumplidos: el cliente se movió en los 5 días
            //    siguientes al "Quedamos en algo" (abrió la cotización con
            //    engagement real, o hubo venta). POR COTIZACIÓN: solo su
            //    "Quedamos en algo" VIGENTE del período: si después se cambió a
            //    "Nada"/"No quiso", el acuerdo ya no existe y NO cuenta como
            //    en curso — el reporte debe cuadrar con lo que la mesa muestra.
            //    DESCARTADAS fuera: al descartar (👎 o postura descartada), el
            //    acuerdo sale del examen — la cotización se juzga en descartes
            //    (revividos/recuperado), no aquí. Ni "en curso" falso ni
            //    "no cumplido" injusto por haberla matado a tiempo.
            //    Maduro = 5+ días; los frescos van como "en curso", no en contra.
            //    RELOJ DE RACHA: la fecha del examen es la PRIMERA declaración
            //    de la racha vigente del mismo estado — re-tapear "Quedamos en
            //    algo" no reinicia los 5 días (si no, re-confirmar cada 4 días
            //    borraría los reprobados para siempre). Solo un CAMBIO real de
            //    desenlace arranca un acuerdo nuevo.
            foreach (DB::query(
                "SELECT uid,
                        SUM(eff <= NOW() - INTERVAL 5 DAY) AS maduros,
                        SUM(eff >  NOW() - INTERVAL 5 DAY) AS en_curso,
                        SUM(eff <= NOW() - INTERVAL 5 DAY AND (
                            EXISTS (SELECT 1 FROM ventas v
                                    WHERE v.cotizacion_id = ex.cotizacion_id AND v.estado != 'cancelada'
                                      AND v.created_at >= eff
                                      AND v.created_at <= eff + INTERVAL 5 DAY)
                         OR EXISTS (SELECT 1 FROM quote_sessions qs
                                    WHERE qs.cotizacion_id = ex.cotizacion_id AND qs.es_interno = 0
                                      AND NOT (COALESCE(qs.visible_ms,0) < 200 AND COALESCE(qs.scroll_max,0) < 35)
                                      AND qs.created_at > eff
                                      AND qs.created_at <= eff + INTERVAL 5 DAY)
                        )) AS cumplidos
                 FROM (
                 SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid, m.cotizacion_id,
                        (SELECT MIN(m3.created_at) FROM mesa_estados m3
                         WHERE m3.cotizacion_id = m.cotizacion_id AND m3.area = 'compromiso'
                           AND m3.estado = 'compromiso'
                           AND m3.id > COALESCE(
                               (SELECT MAX(m4.id) FROM mesa_estados m4
                                WHERE m4.cotizacion_id = m.cotizacion_id AND m4.area = 'compromiso'
                                  AND m4.estado != 'compromiso'), 0)) AS eff
                 FROM mesa_estados m
                 JOIN (SELECT cotizacion_id, MAX(id) AS mid FROM mesa_estados
                       WHERE empresa_id = ? AND area = 'compromiso'
                         AND created_at >= NOW() - INTERVAL $dias DAY
                       GROUP BY cotizacion_id) tc ON tc.mid = m.id
                 JOIN cotizaciones c ON c.id = m.cotizacion_id
                 WHERE m.estado = 'compromiso'
                   AND NOT ((SELECT mp.estado FROM mesa_estados mp
                             WHERE mp.cotizacion_id = m.cotizacion_id AND mp.area = 'postura'
                             ORDER BY mp.id DESC LIMIT 1) <=> 'descartada'
                            AND NOT EXISTS (SELECT 1 FROM mesa_estados mfp
                                 WHERE mfp.cotizacion_id = m.cotizacion_id AND mfp.area = 'feedback'
                                   AND mfp.estado = 'con_interes'
                                   AND mfp.id > (SELECT MAX(mp2.id) FROM mesa_estados mp2
                                                 WHERE mp2.cotizacion_id = m.cotizacion_id AND mp2.area = 'postura')))
                   AND NOT EXISTS (SELECT 1 FROM radar_feedback rf
                                   WHERE rf.cotizacion_id = m.cotizacion_id
                                     AND rf.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                     AND rf.tipo = 'sin_interes')
                 ) ex
                 GROUP BY uid", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['comp_maduros']   = (int)$r['maduros'];
                $out[$u]['comp_cumplidos'] = (int)$r['cumplidos'];
                $out[$u]['comp_en_curso']  = (int)$r['en_curso'];
            }

            // 4) ¿Cómo lo ves? — última declaración por cotización en el período
            foreach (DB::query(
                "SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid, m.estado, COUNT(*) AS n
                 FROM mesa_estados m
                 JOIN (SELECT cotizacion_id, MAX(id) AS mid FROM mesa_estados
                       WHERE empresa_id = ? AND area = 'postura'
                         AND created_at >= NOW() - INTERVAL $dias DAY
                       GROUP BY cotizacion_id) t ON t.mid = m.id
                 JOIN cotizaciones c ON c.id = m.cotizacion_id
                 GROUP BY uid, m.estado", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['postura'][$r['estado']] = (int)$r['n'];
            }

            // 5) Descartes DEL PERÍODO (del dueño) que el cliente recalentó
            //    después. El descarte se FECHA por el inicio de su episodio
            //    (primer 'sin_interes' posterior al último 'con_interes';
            //    fallback: primera postura 'descartada'; fallback: updated_at)
            //    — re-confirmar un 👎 viejo NO lo mete al período como si fuera
            //    descarte nuevo, y no borra el revivido (updated_at se bumpea).
            //    Las VENDIDAS fuera (principio único): descartada-y-vendida se
            //    juzga en Recuperado — aquí duplicaría el denominador.
            //    "Revivió" = VISTA REAL del cliente posterior al descarte (misma
            //    regla que armar) — las transiciones de bucket solas son
            //    recálculos del Radar, no actividad del cliente (flapping).
            foreach (DB::query(
                "SELECT uid, COUNT(*) AS descartes,
                        SUM(EXISTS (SELECT 1 FROM quote_sessions qs
                                    WHERE qs.cotizacion_id = x.cid AND qs.es_interno = 0
                                      AND NOT (COALESCE(qs.visible_ms,0) < 200 AND COALESCE(qs.scroll_max,0) < 35)
                                      AND qs.created_at > x.anc)) AS revividos
                 FROM (
                    SELECT COALESCE(c.vendedor_id, c.usuario_id) AS uid, rf.cotizacion_id AS cid,
                           COALESCE(
                               (SELECT MIN(mf.created_at) FROM mesa_estados mf
                                WHERE mf.cotizacion_id = rf.cotizacion_id
                                  AND mf.area = 'feedback' AND mf.estado = 'sin_interes'
                                  AND mf.created_at > COALESCE(
                                      (SELECT MAX(mc.created_at) FROM mesa_estados mc
                                       WHERE mc.cotizacion_id = rf.cotizacion_id
                                         AND mc.area = 'feedback' AND mc.estado = 'con_interes'),
                                      '2000-01-01')),
                               (SELECT MIN(mp3.created_at) FROM mesa_estados mp3
                                WHERE mp3.cotizacion_id = rf.cotizacion_id
                                  AND mp3.area = 'postura' AND mp3.estado = 'descartada'),
                               rf.updated_at) AS anc
                    FROM radar_feedback rf JOIN cotizaciones c ON c.id = rf.cotizacion_id
                    WHERE rf.empresa_id = ? AND rf.tipo = 'sin_interes'
                      AND rf.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                      AND NOT EXISTS (SELECT 1 FROM ventas v
                                      WHERE v.cotizacion_id = rf.cotizacion_id
                                        AND v.estado != 'cancelada')
                 ) x
                 WHERE x.anc >= NOW() - INTERVAL $dias DAY
                 GROUP BY uid", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['descartes'] = (int)$r['descartes'];
                $out[$u]['revividos'] = (int)$r['revividos'];
            }

            // 6) Recuperado por asesor (mismas condiciones que recuperado())
            foreach (DB::query(
                "SELECT uid, SUM(fd) AS rec_n, SUM(CASE WHEN fd THEN total ELSE 0 END) AS rec_monto
                 FROM (
                    SELECT COALESCE(c2.vendedor_id, c2.usuario_id) AS uid, v.total,
                           ((SELECT mf.estado FROM mesa_estados mf
                             WHERE mf.cotizacion_id = v.cotizacion_id AND mf.empresa_id = v.empresa_id
                               AND mf.area = 'feedback' AND mf.created_at < v.created_at
                             ORDER BY mf.id DESC LIMIT 1) <=> 'sin_interes'
                            OR ((SELECT mp.estado FROM mesa_estados mp
                             WHERE mp.cotizacion_id = v.cotizacion_id AND mp.empresa_id = v.empresa_id
                               AND mp.area = 'postura' AND mp.created_at < v.created_at
                             ORDER BY mp.id DESC LIMIT 1) <=> 'descartada'
                             AND NOT ((SELECT mf2.estado FROM mesa_estados mf2
                               WHERE mf2.cotizacion_id = v.cotizacion_id AND mf2.empresa_id = v.empresa_id
                                 AND mf2.area = 'feedback' AND mf2.created_at < v.created_at
                               ORDER BY mf2.id DESC LIMIT 1) <=> 'con_interes'))
                            OR EXISTS (SELECT 1 FROM radar_feedback rf
                                    WHERE rf.cotizacion_id = v.cotizacion_id AND rf.empresa_id = v.empresa_id
                                      AND rf.usuario_id = COALESCE(c2.vendedor_id, c2.usuario_id)
                                      AND rf.tipo = 'sin_interes' AND rf.updated_at < v.created_at
                            )) AS fd
                    FROM ventas v JOIN cotizaciones c2 ON c2.id = v.cotizacion_id
                    WHERE v.empresa_id = ? AND v.estado != 'cancelada'
                      AND v.cotizacion_id IS NOT NULL AND v.total > 0
                      AND v.created_at >= NOW() - INTERVAL $dias DAY
                 ) x
                 GROUP BY uid", [$empresa_id]
            ) as $r) {
                $u = (int)$r['uid']; if (!$u) continue;
                $out[$u] ??= $base;
                $out[$u]['rec_n']     = (int)$r['rec_n'];
                $out[$u]['rec_monto'] = (float)$r['rec_monto'];
            }
        } catch (Throwable $e) {
            return ['dias' => $dias, 'asesores' => []]; // mesa_estados aún no migrada
        }

        if ($out) {
            // Solo usuarios de ESTA empresa — un uid de otra empresa (ej. el
            // superadmin que creó cotizaciones aquí) no debe mostrar su nombre
            // ni aparecer como fila de asesor del reporte
            $uin = implode(',', array_map('intval', array_keys($out)));
            $validos = [];
            foreach (DB::query(
                "SELECT id, nombre, activo FROM usuarios
                 WHERE id IN ($uin) AND empresa_id = ?", [$empresa_id]
            ) as $r) {
                $validos[(int)$r['id']] = true;
                if (isset($out[(int)$r['id']])) {
                    $out[(int)$r['id']]['nombre'] = $r['nombre'] . ((int)($r['activo'] ?? 1) ? '' : ' (inactivo)');
                }
            }
            $out = array_intersect_key($out, $validos);
            uasort($out, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));
        }
        return ['dias' => $dias, 'asesores' => $out];
    }

    /**
     * Contador de recuperado — la prueba en pesos de la Mesa (empresa-wide).
     *
     * "Recuperado": ventas de los últimos N días cuya cotización YA estaba
     * descartada ANTES de la venta (👎 del Radar o "Descartar" en la mesa) —
     * dinero que sin la vigilancia de revividas se habría dado por muerto.
     *
     * "Trabajada": ventas cuya cotización tuvo al menos una declaración real
     * en la mesa (contacto/compromiso/postura) antes de cerrar. Excluye las
     * ya contadas como recuperadas para no sumar el mismo peso dos veces.
     *
     * Se evalúa el estado VIGENTE al momento de la venta: si el asesor
     * corrigió el 👎 a 👍 (o la postura descartada a otra) ANTES de cerrar,
     * la venta NO cuenta como recuperada — por ninguna de las 3 vías.
     */
    public static function recuperado(int $empresa_id, int $dias = 30, ?int $vendedor_id = null): array
    {
        $dias  = max(1, (int)$dias);
        $vacio = ['rec_n' => 0, 'rec_monto' => 0.0, 'trab_n' => 0, 'trab_monto' => 0.0, 'dias' => $dias];
        try {
            $rows = DB::query(
                "SELECT v.total,
                        ((SELECT mf.estado FROM mesa_estados mf
                          WHERE mf.cotizacion_id = v.cotizacion_id AND mf.empresa_id = v.empresa_id
                            AND mf.area = 'feedback' AND mf.created_at < v.created_at
                          ORDER BY mf.id DESC LIMIT 1) <=> 'sin_interes'
                         OR ((SELECT mp.estado FROM mesa_estados mp
                          WHERE mp.cotizacion_id = v.cotizacion_id AND mp.empresa_id = v.empresa_id
                            AND mp.area = 'postura' AND mp.created_at < v.created_at
                          ORDER BY mp.id DESC LIMIT 1) <=> 'descartada'
                          AND NOT ((SELECT mf2.estado FROM mesa_estados mf2
                            WHERE mf2.cotizacion_id = v.cotizacion_id AND mf2.empresa_id = v.empresa_id
                              AND mf2.area = 'feedback' AND mf2.created_at < v.created_at
                            ORDER BY mf2.id DESC LIMIT 1) <=> 'con_interes'))
                         OR EXISTS (
                            SELECT 1 FROM radar_feedback rf
                            WHERE rf.cotizacion_id = v.cotizacion_id AND rf.empresa_id = v.empresa_id
                              AND rf.usuario_id = COALESCE(c2.vendedor_id, c2.usuario_id)
                              AND rf.tipo = 'sin_interes' AND rf.updated_at < v.created_at
                        )) AS fue_descartada,
                        EXISTS (
                            SELECT 1 FROM mesa_estados m2
                            WHERE m2.cotizacion_id = v.cotizacion_id AND m2.empresa_id = v.empresa_id
                              AND m2.area IN ('contacto','compromiso','postura')
                              AND m2.created_at < v.created_at
                        ) AS fue_trabajada
                 FROM ventas v JOIN cotizaciones c2 ON c2.id = v.cotizacion_id
                 WHERE v.empresa_id = ? AND v.estado != 'cancelada'
                   AND v.cotizacion_id IS NOT NULL AND v.total > 0
                   AND v.created_at >= NOW() - INTERVAL $dias DAY"
                 . ($vendedor_id !== null ? ' AND COALESCE(c2.vendedor_id, c2.usuario_id) = ' . (int)$vendedor_id : ''),
                [$empresa_id]
            );
        } catch (Throwable $e) {
            return $vacio; // mesa_estados aún no migrada
        }

        $out = $vacio;
        foreach ($rows as $r) {
            if ((int)$r['fue_descartada'] === 1) {
                $out['rec_n']++;
                $out['rec_monto'] += (float)$r['total'];
            } elseif ((int)$r['fue_trabajada'] === 1) {
                $out['trab_n']++;
                $out['trab_monto'] += (float)$r['total'];
            }
        }
        return $out;
    }
}
