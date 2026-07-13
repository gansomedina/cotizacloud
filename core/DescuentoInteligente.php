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
//    fin_vida = 2×p75         · dead = max(p90, 3×p75)        · techo = dead + 3×p75
//    R1 = [fin_vida, dead)    · R2 = [dead, techo]            · >techo = fósil, no dispara
//
//  Rendimiento: las anclas (p75/p90) escanean ventas → se cachean por
//  empresa (TTL 24h) en desc_int_config. El slug solo hace lecturas
//  indexadas de ESTA cotización y ESTE cliente (ver evaluar()).
// ============================================================
defined('COTIZAAPP') or die;

class DescuentoInteligente
{
    // ── Multiplicadores de zona (sobre p75). Ajustables aquí. ──
    const MULT_R1_INICIO = 2;    // inicio recuperación = fin de vida comercial (2×p75)
    const MULT_DEAD      = 3;    // inicio zona muerta (piso, además de p90)
    const MULT_TECHO     = 3;    // ancho de R2 sobre dead

    const MIN_VENTAS      = 5;    // sin muestra suficiente, la feature no corre
    const GENERICO_COTS   = 10;   // cliente con >N cotizaciones vivas = cajón genérico
    const VIGENCIA_HORAS  = 24;
    const ANCLAS_TTL_HORAS = 24;

    // Buckets que BLOQUEAN el descuento (la cotización sigue viva)
    const HOT = ['probable_cierre', 'onfire', 'inminente', 'prediccion_alta',
                 'lectura_comprometida', 'multi_persona', 'alto_importe',
                 'validando_precio', 'pidio_cambios'];

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

        // No apilar con descuento manual del asesor ni cupón
        if (!empty($cot['descuento_auto_activo']) || !empty($cot['cupon_id'])) return null;

        // Zona por edad
        $edad = (int)floor((time() - strtotime($cot['created_at'])) / 86400);
        if ($edad >= $anc['dia_fin_vida'] && $edad < $anc['dia_dead'] && (int)$cfg['r1_activa']) {
            $regla = 1; $pct = (float)$cfg['r1_pct']; $window = max(1, (int)ceil($anc['p75'] / 2));
        } elseif ($edad >= $anc['dia_dead'] && $edad <= $anc['dia_techo'] && (int)$cfg['r2_activa']) {
            $regla = 2; $pct = (float)$cfg['r2_pct']; $window = max(1, (int)$anc['p75']);
        } else {
            return null; // aún viva, fósil, o regla apagada
        }
        if ($pct <= 0) return null;

        // Exclusión B: bucket vivo → sigue en juego, no descontar
        $bucket = $cot['radar_bucket'] ?? null;
        if ($bucket !== null && in_array($bucket, self::HOT, true)) return null;

        // El cliente NUNCA la vio → no hay a quién recuperar
        $vio = (int)DB::val(
            "SELECT EXISTS(SELECT 1 FROM quote_sessions qs
                WHERE qs.cotizacion_id = ? AND qs.es_interno = 0
                  AND NOT (COALESCE(qs.visible_ms,0) < 200 AND COALESCE(qs.scroll_max,0) < 35))",
            [(int)$cot['id']]);
        if (!$vio) return null;

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
    //    Devuelve la fila de activación (nueva o la existente si hubo carrera),
    //    o null si el cliente ya tiene una en otra cotización (UNIQUE cliente).
    public static function activar(array $cot, array $ev, ?string $visitor_id = null): ?array
    {
        $eid   = (int)$cot['empresa_id'];
        $total = (float)$cot['total'];
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
            // UNIQUE cotización (carrera, doble-clic) → devolver la vigente.
            // UNIQUE cliente (ya tiene en otra cot) → vigente(this) = null.
            return self::vigente((int)$cot['id']);
        }
        return self::vigente((int)$cot['id']);
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
            DB::execute("UPDATE desc_int_activaciones SET estado='vencido' WHERE id=?", [$a['id']]);
            return null;
        }
        return $a;
    }
}
