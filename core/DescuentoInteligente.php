<?php
// ============================================================
//  core/DescuentoInteligente.php — MOTOR (Fase 1)
//
//  Feature INDEPENDIENTE. NO toca descuento_auto, cupones ni comisión.
//  Revive cotizaciones fuera de su vida comercial estadística con un
//  descuento automático, SOLO cuando el Radar confirma que ya no están
//  vivas (bucket frío + sin actividad reciente de cliente/asesor).
//
//  Dos reglas configurables e independientes por empresa:
//    R1 (recuperación): cotización pasó su vida comercial pero aún no muere.
//    R2 (muerto):       cotización en la zona donde históricamente ya no se
//                       cierra. Descuento mayor (último intento).
//
//  Zonas (multiplicadores sobre p75 = ventana de la empresa):
//    R1_inicio = 1.5×p75      · dead = max(p90, 2.5×p75)      · techo = dead + 3×p75
//    R1 = [R1_inicio, dead)   · R2 = [dead, techo]            · >techo = fósil, no dispara
//    (R1 arranca antes porque las exclusiones B —trabajo del asesor + apertura
//     del cliente— protegen la canibalización; validado con datos históricos)
//
//  Rendimiento: las anclas (p75/p90) escanean ventas → se cachean por
//  empresa (TTL 24h) en desc_int_config. El slug solo hace lecturas
//  indexadas de ESTA cotización y ESTE cliente (ver evaluar()).
// ============================================================
defined('COTIZAAPP') or die;

class DescuentoInteligente
{
    // ── Multiplicadores de zona (sobre p75). Ajustables aquí. ──
    // El DI arranca DESPUÉS de la ventana de mesa (2×p75). Mientras la mesa la
    // tiene en juego (0–2×p75) es milagro/trabajo del asesor, NO DI. Con p75=10:
    //   mesa 0–20 · R1 (recuperación) 21–30 · R2 (muerto) 31–55 · fósil 56+.
    const MULT_R1_INICIO = 2.0;  // R1 empieza JUSTO después de la mesa (2×p75), con > estricto → día 21
    const MULT_DEAD      = 3.0;  // R2 empieza en 3×p75 (piso, además de p90) → día 31
    const MULT_TECHO     = 2.5;  // ancho de R2 sobre dead: dead + 2.5×p75 → día 55

    const MIN_VENTAS      = 5;    // sin muestra suficiente, la feature no corre
    const GENERICO_COTS   = 10;   // cliente con >N cotizaciones vivas = cajón genérico
    const VIGENCIA_HORAS  = 24;
    const ANCLAS_TTL_HORAS = 24;

    // Buckets FRÍOS del Radar donde SÍ se permite DI: la cotización ya no está
    // viva. Cualquier OTRO bucket no-null = viva (caliente, decisión activa o
    // re-enganche) → bloquea. Es un allowlist (no blocklist) a prueba de futuro:
    // si el Radar inventa un bucket nuevo, NO habilita DI por accidente.
    //   frío   = enfriandose · hesitacion · no_abierta
    //   vivo   = todo lo demás del Radar (Radar.php $PRIORIDAD), incl.
    //            decision_activa, revivio, re_enganche_caliente, re_enganche,
    //            revision_profunda, vistas_multiples, sobre_analisis, regreso,
    //            comparando + toda la franja caliente.
    const COLD = ['enfriandose', 'hesitacion', 'no_abierta'];

    // ── 1) Anclas por empresa — cacheadas en desc_int_config (TTL 24h) ──
    //    Devuelve ['n_ventas','p75','p90','dia_fin_vida','dia_dead','dia_techo']
    //    o null si no hay muestra suficiente (feature apagada para esa empresa).
    public static function anclas(int $empresa_id): ?array
    {
        try {
            $row = DB::row(
                "SELECT n_ventas, p75, p90, dia_fin_vida, dia_dead, dia_techo, anclas_at
                 FROM desc_int_config WHERE empresa_id = ?", [$empresa_id]);
        } catch (\Throwable $e) { return null; } // tabla sin migrar

        if ($row && $row['anclas_at'] && $row['p75'] !== null &&
            strtotime($row['anclas_at']) >= time() - self::ANCLAS_TTL_HORAS * 3600) {
            return [
                'n_ventas'     => (int)$row['n_ventas'],
                'p75'          => (int)$row['p75'],
                'p90'          => (int)$row['p90'],
                'dia_fin_vida' => (int)$row['dia_fin_vida'],
                'dia_dead'     => (int)$row['dia_dead'],
                'dia_techo'    => (int)$row['dia_techo'],
            ];
        }

        // Recalcular de ventas (mismo criterio que Radar::ciclo_venta)
        $dias = [];
        foreach (DB::query(
            "SELECT GREATEST(DATEDIFF(v.created_at, c.created_at), 0) AS d
             FROM ventas v JOIN cotizaciones c ON c.id = v.cotizacion_id
             WHERE v.empresa_id = ? AND v.estado <> 'cancelada'
               AND DATEDIFF(v.created_at, c.created_at) >= 0
             ORDER BY d", [$empresa_id]) as $r) {
            $dias[] = (int)$r['d'];
        }
        $n = count($dias);
        if ($n < self::MIN_VENTAS) {
            self::_guardar_anclas($empresa_id, $n, null, null, null, null, null);
            return null;
        }

        // Percentiles discretos (idénticos a ciclo_venta: floor sobre el ordenado)
        $p75 = $dias[(int)floor($n * 0.75)];
        $p90 = $dias[(int)floor($n * 0.90)];
        $fin   = max(1, (int)round(self::MULT_R1_INICIO * $p75));
        $dead  = max($p90, (int)round(self::MULT_DEAD * $p75));
        $techo = $dead + (int)round(self::MULT_TECHO * $p75);
        // Garantía de orden (defensa ante p75=0/1): dead > fin, techo > dead
        $dead  = max($dead, $fin + 1);
        $techo = max($techo, $dead + 1);

        self::_guardar_anclas($empresa_id, $n, $p75, $p90, $fin, $dead, $techo);
        return ['n_ventas' => $n, 'p75' => $p75, 'p90' => $p90,
                'dia_fin_vida' => $fin, 'dia_dead' => $dead, 'dia_techo' => $techo];
    }

    private static function _guardar_anclas(int $eid, int $n, ?int $p75, ?int $p90,
                                            ?int $fin, ?int $dead, ?int $techo): void
    {
        try {
            DB::execute(
                "INSERT INTO desc_int_config
                   (empresa_id, n_ventas, p75, p90, dia_fin_vida, dia_dead, dia_techo, anclas_at)
                 VALUES (?,?,?,?,?,?,?, NOW())
                 ON DUPLICATE KEY UPDATE
                   n_ventas=VALUES(n_ventas), p75=VALUES(p75), p90=VALUES(p90),
                   dia_fin_vida=VALUES(dia_fin_vida), dia_dead=VALUES(dia_dead),
                   dia_techo=VALUES(dia_techo), anclas_at=NOW()",
                [$eid, $n, $p75, $p90, $fin, $dead, $techo]);
        } catch (\Throwable $e) { /* tabla sin migrar */ }
    }

    // ── 2) Config del admin (toggles + %) ──
    public static function config(int $empresa_id): ?array
    {
        try {
            return DB::row(
                "SELECT r1_activa, r1_pct, r2_activa, r2_pct
                 FROM desc_int_config WHERE empresa_id = ?", [$empresa_id]);
        } catch (\Throwable $e) { return null; }
    }

    // ── 3) Evaluar candidatura + exclusiones → regla aplicable o null ──
    //    $cot requiere: id, empresa_id, cliente_id, total, created_at,
    //    radar_bucket, descuento_auto_activo, cupon_id.
    //    IMPORTANTE: llamar ANTES de registrar la visita actual — el window
    //    de "actividad reciente" mira el estado PREVIO a esta apertura.
    public static function evaluar(array $cot): ?array
    {
        $eid = (int)$cot['empresa_id'];
        $cfg = self::config($eid);
        if (!$cfg || (!(int)$cfg['r1_activa'] && !(int)$cfg['r2_activa'])) return null;

        $anc = self::anclas($eid);
        if (!$anc) return null; // sin muestra suficiente

        // Cliente válido: no NULL, no genérico (cajón de mostrador)
        $cli = (int)($cot['cliente_id'] ?? 0);
        if ($cli <= 0) return null;
        $cots_cli = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE cliente_id = ? AND empresa_id = ? AND estado IN ('enviada','vista')",
            [$cli, $eid]);
        if ($cots_cli > self::GENERICO_COTS) return null;

        // Cliente que YA compró (tiene una venta no cancelada) NO es un lead muerto
        // a recuperar — ya es cliente. No se le regala descuento. (Para exigir pago
        // real en vez de solo aceptación, agregar AND v.pagado > 0.)
        $ya_cliente = (int)DB::val(
            "SELECT EXISTS(SELECT 1 FROM ventas v
                WHERE v.cliente_id = ? AND v.empresa_id = ? AND v.estado <> 'cancelada')",
            [$cli, $eid]);
        if ($ya_cliente) return null;

        // No apilar. El descuento manual del asesor bloquea SOLO si sigue VIVO
        // (Option B: un manual vencido sin usarse es independiente — el
        // inteligente puede intentar de nuevo). Cupón asignado siempre bloquea.
        $manual_vivo = !empty($cot['descuento_auto_activo'])
            && (empty($cot['descuento_auto_expira'])
                || strtotime($cot['descuento_auto_expira']) >= time());
        if ($manual_vivo || !empty($cot['cupon_id'])) return null;

        // Zona por edad
        $edad = (int)floor((time() - strtotime($cot['created_at'])) / 86400);
        // R1 = (dia_fin_vida, dia_dead]  ·  R2 = (dia_dead, dia_techo]. El > estricto
        // en el borde inferior hace que R1 empiece el día DESPUÉS de la mesa (día 21).
        if ($edad > $anc['dia_fin_vida'] && $edad <= $anc['dia_dead'] && (int)$cfg['r1_activa']) {
            $regla = 1; $pct = (float)$cfg['r1_pct']; $window = max(1, (int)ceil($anc['p75'] / 2));
        } elseif ($edad > $anc['dia_dead'] && $edad <= $anc['dia_techo'] && (int)$cfg['r2_activa']) {
            $regla = 2; $pct = (float)$cfg['r2_pct']; $window = max(1, (int)$anc['p75']);
        } else {
            return null; // aún viva, fósil, o regla apagada
        }
        if ($pct <= 0) return null;

        // Exclusión B: solo se descuenta si el Radar la dejó FRÍA. Cualquier
        // bucket no-null que no sea frío = sigue viva (caliente o re-enganche).
        $bucket = $cot['radar_bucket'] ?? null;
        if ($bucket !== null && !in_array($bucket, self::COLD, true)) return null;

        // El cliente NUNCA la vio → no hay a quién recuperar
        $vio = (int)DB::val(
            "SELECT EXISTS(SELECT 1 FROM quote_sessions qs
                WHERE qs.cotizacion_id = ? AND qs.es_interno = 0
                  AND NOT (COALESCE(qs.visible_ms,0) < 200 AND COALESCE(qs.scroll_max,0) < 35))",
            [(int)$cot['id']]);
        if (!$vio) return null;

        // DORMANCIA DEL CLIENTE (reset por interés): si te vio hace poco, hay
        // interés → es un MILAGRO (lo trabaja el asesor), NO un DI. El DI solo
        // dispara si el cliente estuvo callado MÁS que tu ventana de mesa (2×p75)
        // desde su última vista. Cada vista real resetea el reloj sola: evaluar()
        // corre ANTES de sellar la visita actual (public/cotizacion.php:370), así
        // que ultima_vista_at = la vista PREVIA. Sin esto, el DI se rendía (regalaba
        // %) mientras la mesa aún la tenía en juego (R1 arranca en 1.5×p75).
        $uv   = $cot['ultima_vista_at'] ?? null;
        $dorm = $uv ? (int)floor((time() - strtotime($uv)) / 86400) : 0;
        if ($dorm <= 2 * (int)$anc['p75']) return null; // te vio dentro de tu ventana → milagro, no DI

        // Exclusión B: actividad reciente en el window (cliente O asesor) → viva
        $reciente = (int)DB::val(
            "SELECT
               EXISTS(SELECT 1 FROM quote_sessions qs
                 WHERE qs.cotizacion_id = ? AND qs.es_interno = 0
                   AND NOT (COALESCE(qs.visible_ms,0) < 200 AND COALESCE(qs.scroll_max,0) < 35)
                   AND qs.created_at >= NOW() - INTERVAL ? DAY)
               OR EXISTS(SELECT 1 FROM cotizacion_log a
                 WHERE a.cotizacion_id = ? AND a.usuario_id IS NOT NULL
                   AND COALESCE(a.accion, a.evento) IN ('editada','enviada')
                   AND a.created_at >= NOW() - INTERVAL ? DAY)
               OR EXISTS(SELECT 1 FROM radar_feedback rf
                 WHERE rf.cotizacion_id = ? AND rf.updated_at >= NOW() - INTERVAL ? DAY)",
            [(int)$cot['id'], $window, (int)$cot['id'], $window, (int)$cot['id'], $window]);
        if ($reciente) return null;

        return [
            'regla'        => $regla,
            'pct'          => $pct,
            'edad'         => $edad,
            'dia_fin_vida' => (int)$anc['dia_fin_vida'],
            'dia_dead'     => (int)$anc['dia_dead'],
        ];
    }

    // ── 4) Activar — transaccional, los UNIQUE evitan doble/duplicado ──
    //    $precio_original = precio SIN extras (la base sobre la que aplica el
    //    descuento). El slug lo calcula (total sin extras). Devuelve la fila de
    //    activación (nueva o la existente si hubo carrera), o null si el cliente
    //    ya tiene una en otra cotización (UNIQUE cliente).
    public static function activar(array $cot, array $ev, float $precio_original, ?string $visitor_id = null): ?array
    {
        $eid   = (int)$cot['empresa_id'];
        $total = round($precio_original, 2);
        $monto = round($total * $ev['pct'] / 100, 2);
        $nuevo = round($total - $monto, 2);
        $expira = date('Y-m-d H:i:s', time() + self::VIGENCIA_HORAS * 3600);
        try {
            DB::execute(
                "INSERT INTO desc_int_activaciones
                   (empresa_id, cotizacion_id, cliente_id, regla, pct, precio_original,
                    monto_desc, nuevo_total, edad_dias, dia_fin_vida, dia_dead,
                    bucket_snapshot, estado, fecha_apertura, expira_at, visitor_id)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'activo', NOW(), ?, ?)",
                [$eid, (int)$cot['id'], (int)$cot['cliente_id'], $ev['regla'], $ev['pct'],
                 $total, $monto, $nuevo, $ev['edad'], $ev['dia_fin_vida'], $ev['dia_dead'],
                 $cot['radar_bucket'] ?? null, $expira, $visitor_id]);
        } catch (\Throwable $e) {
            // 23000 = violación UNIQUE (esperada): cotización ya activada
            // (carrera/doble-clic) → vigente(this) = la existente; o el cliente
            // ya tiene una viva → vigente(this) = null. Cualquier otro error es
            // un bug real (deadlock, tipo, FK): dejar rastro antes de fallar-seguro.
            $code = (string)$e->getCode();
            if ($code !== '23000' && stripos($e->getMessage(), 'duplicate') === false) {
                error_log('[DI activar] error inesperado: ' . $e->getMessage());
            }
            return self::vigente((int)$cot['id']);
        }
        // INSERT exitoso = activación RECIÉN creada. Marca `_nueva` para que el
        // caller dispare la notificación solo la 1ª vez (no en cada reapertura).
        $row = self::vigente((int)$cot['id']);
        if ($row) $row['_nueva'] = true;
        return $row;
    }

    // ── 5) Activación vigente — lazy expiry, SIN cron ──
    //    'utilizado' se devuelve siempre (descuento ya aplicado a la venta).
    //    'activo' vencido → se marca 'vencido' y devuelve null.
    public static function vigente(int $cotizacion_id): ?array
    {
        try {
            $a = DB::row(
                "SELECT * FROM desc_int_activaciones
                 WHERE cotizacion_id = ? AND estado IN ('activo','utilizado')
                 ORDER BY id DESC LIMIT 1", [$cotizacion_id]);
        } catch (\Throwable $e) { return null; }
        if (!$a) return null;
        if ($a['estado'] === 'activo' && strtotime($a['expira_at']) < time()) {
            // WHERE estado='activo' evita pisar un 'utilizado' que un accept
            // simultáneo acabe de escribir (degradaría un descuento ya usado).
            DB::execute("UPDATE desc_int_activaciones SET estado='vencido' WHERE id=? AND estado='activo'", [$a['id']]);
            // Re-leer por si la carrera lo marcó 'utilizado' antes que nosotros.
            $a2 = DB::row("SELECT * FROM desc_int_activaciones WHERE id=?", [$a['id']]);
            return ($a2 && $a2['estado'] === 'utilizado') ? $a2 : null;
        }
        return $a;
    }
}
