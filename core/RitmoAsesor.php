<?php
// ============================================================
//  RitmoAsesor — Rendimiento de asesores desde la Mesa
//  SOLO LECTURA. No toca la Mesa ni ActividadScore.
//
//  Método (gerente de ventas), TODO auto-ajustable, cero valores fijos:
//
//  VEREDICTO (cómo va):
//    1. Cierra  — sus ventas vs el histórico de cierre de SU empresa
//    2. Limpio  — descartes vs ventas (gaming) vs la norma de SU empresa
//
//  RITMO (leading — quién se está soltando, presiona YA):
//    3. Vencidas subiendo — límite de seguimiento de la Mesa (normal = bajo;
//       sube vs SU propio nivel = se soltó)
//    4. Citas bajando — su ritmo de agendar cae vs SU propio promedio
//
//  Guarda: solo empresas con Mesa activa. SIN gracia para nuevos (ventana
//  corta → se llena rápido). Diseño acordado con el CEO.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    private const DIAS_VEREDICTO = 30;   // cierre/gaming: resultado sobre ~un ciclo
    private const DIAS_RITMO     = 7;     // vencidas/citas de la semana
    private const DIAS_BASE      = 28;    // baseline propio del ritmo (4 semanas)
    // Umbrales del semáforo — RELATIVOS al promedio (50 = promedio de su empresa),
    // no magic numbers absolutos. Calibrables sin tocar la lógica.
    private const CIERRA_BAJO    = 50;    // por debajo del promedio de su empresa
    private const SCORE_ROJO     = 35;    // muy por debajo del promedio
    private const SCORE_VERDE    = 58;    // claramente arriba del promedio

    public static function empresa(int $empresa_id): array
    {
        // Guarda: solo con Mesa activa (sin Mesa no hay reloj ni datos).
        try {
            if ((int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$empresa_id]) < 1) return [];
        } catch (Throwable $e) { return []; }

        // Reporte del equipo (ventas, descartes, activas) — fuente única de la Mesa.
        try {
            $rep = Mesa::reporte($empresa_id, self::DIAS_VEREDICTO)['asesores'] ?? [];
        } catch (Throwable $e) { error_log('[RitmoAsesor reporte] ' . $e->getMessage()); return []; }
        if (!$rep) return [];

        // Benchmark de cierre HISTÓRICO de la empresa (antes de la ventana): así
        // un asesor solo NO se compara consigo mismo (sigmoid(x,x)=0.5). Auto-ajuste.
        $emp_close_hist = self::_close_hist($empresa_id);

        // Benchmark de la empresa (agregado del período).
        $tot_v = 0; $tot_d = 0; $tot_a = 0;
        foreach ($rep as $r) { $tot_v += (int)($r['ventas_n'] ?? 0); $tot_d += (int)($r['descartes'] ?? 0); $tot_a += (int)($r['activas'] ?? 0); }
        $emp_dump = ($tot_v + $tot_d) > 0 ? $tot_d / ($tot_v + $tot_d) : 0.0;
        // Cierra = ventas ABSOLUTAS vs el promedio del equipo (premia cerrar, NO
        // castiga tener pipeline grande — el bug de ventas/activas). Con 1 solo
        // asesor no hay pares → se compara contra su propio esperado histórico.
        $n_adv = count($rep);
        $team_avg_ventas = $n_adv >= 2 ? $tot_v / $n_adv : null;

        $filas = [];
        foreach ($rep as $uid => $r) {
            $f = self::_asesor($empresa_id, (int)$uid, $r, $team_avg_ventas, $emp_close_hist, $emp_dump);
            if ($f !== null) $filas[] = $f;
        }

        $peso = ['rojo' => 0, 'amarillo' => 1, 'verde' => 2];
        usort($filas, function ($x, $y) use ($peso) {
            $px = $peso[$x['semaforo']] ?? 3; $py = $peso[$y['semaforo']] ?? 3;
            if ($px !== $py) return $px <=> $py;
            return $x['score'] <=> $y['score']; // peor score arriba dentro de la banda
        });
        return $filas;
    }

    public static function todas(): array
    {
        try {
            $emps = DB::query("SELECT id, nombre FROM empresas WHERE mesa_activa >= 1 AND slug <> '_system' ORDER BY nombre ASC");
        } catch (Throwable $e) { return []; }
        $out = [];
        foreach ($emps as $e) {
            $filas = self::empresa((int)$e['id']);
            if (!$filas) continue;
            $out[] = ['empresa_id' => (int)$e['id'], 'empresa' => $e['nombre'], 'asesores' => $filas];
        }
        return $out;
    }

    // ── Un asesor ──
    private static function _asesor(int $empresa_id, int $uid, array $r, ?float $team_avg_ventas, float $emp_close_hist, float $emp_dump): ?array
    {
        $nombre   = (string)($r['nombre'] ?? '');
        $ventas   = (int)($r['ventas_n'] ?? 0);
        $descartes= (int)($r['descartes'] ?? 0);
        $activas  = (int)($r['activas'] ?? 0);

        // ── 1. CIERRA (ventas ABSOLUTAS vs sus pares; solo → vs su esperado
        //    histórico). NO ventas/activas: castigaba el pipeline grande. ──
        if ($team_avg_ventas !== null && $team_avg_ventas > 0) {
            $bench_v = $team_avg_ventas;                          // promedio del equipo
        } else {
            $bench_v = $emp_close_hist > 0 ? max(1.0, $activas * $emp_close_hist) : max(1.0, (float)$ventas);
        }
        $cierra = self::_clamp((int)round($ventas / max($bench_v, 0.5) * 50), 0, 100);

        // ── 2. LIMPIO (gaming: descartes vs ventas, vs norma de la empresa) ──
        $his_dump = ($ventas + $descartes) > 0 ? $descartes / ($ventas + $descartes) : 0.0;
        $exceso   = max(0.0, $his_dump - $emp_dump);            // cuánto descarta por encima de la norma
        $limpio   = self::_clamp((int)round(100 - $exceso * 200), 0, 100);
        // Gaming = descarta MÁS que su empresa Y cierra por debajo del promedio.
        // (El que descarta mucho pero cierra bien —Kevin— NO es gaming.)
        $gaming   = ($his_dump > $emp_dump + 0.05) && ($cierra < self::CIERRA_BAJO) && ($descartes >= 3);

        // ── 3. RITMO: vencidas (límite de seguimiento) ──
        [$venc_now, $venc_sube, $venc_prev] = self::_vencidas($empresa_id, $uid);

        // ── 4. RITMO: citas bajando (vs su propio promedio) ──
        [$citas7, $citas_base, $citas_baja] = self::_citas($empresa_id, $uid);

        // ── Score (veredicto). Limpio de candado: gaming lo aplasta. ──
        $score = (int)round(0.6 * $cierra + 0.4 * $limpio);
        if ($gaming) $score = min($score, 30);

        // ── Semáforo — relativo (50 = promedio de su empresa), + ritmo ──
        $alerta_ritmo = $venc_sube || $citas_baja;
        if ($gaming || $score < self::SCORE_ROJO) {
            $sem = 'rojo';
        } elseif ($cierra < self::CIERRA_BAJO || $alerta_ritmo || $score < self::SCORE_VERDE) {
            $sem = 'amarillo';
        } else {
            $sem = 'verde';
        }

        $motivo = self::_motivo($nombre, $sem, $gaming, $cierra, $venc_sube, $venc_now, $venc_prev, $citas_baja, $citas7, $citas_base, $descartes, $ventas);

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem, 'score' => $score,
            'cierra' => $cierra, 'limpio' => $limpio, 'gaming' => $gaming,
            'ventas' => $ventas, 'descartes' => $descartes, 'activas' => $activas,
            'venc_now' => $venc_now, 'venc_prev' => $venc_prev, 'venc_sube' => $venc_sube,
            'citas7' => $citas7, 'citas_base' => $citas_base, 'citas_baja' => $citas_baja,
            'motivo' => $motivo,
        ];
    }

    // Cierre histórico de la empresa (antes de la ventana): benchmark auto-ajustable.
    private static function _close_hist(int $empresa_id): float
    {
        try {
            $row = DB::row(
                "SELECT
                    (SELECT COUNT(*) FROM ventas v
                      WHERE v.empresa_id = ? AND v.pagado > 0 AND v.estado <> 'cancelada'
                        AND v.created_at <  NOW() - INTERVAL " . self::DIAS_VEREDICTO . " DAY
                        AND v.created_at >= NOW() - INTERVAL 180 DAY) AS vh,
                    (SELECT COUNT(*) FROM cotizaciones c
                      WHERE c.empresa_id = ? AND c.estado <> 'borrador'
                        AND c.created_at <  NOW() - INTERVAL " . self::DIAS_VEREDICTO . " DAY
                        AND c.created_at >= NOW() - INTERVAL 180 DAY) AS ch",
                [$empresa_id, $empresa_id]
            );
            $ch = (int)($row['ch'] ?? 0);
            return $ch > 0 ? ((int)($row['vh'] ?? 0)) / $ch : 0.0;
        } catch (Throwable $e) { return 0.0; }
    }

    // Vencidas (límite de seguimiento): foto de hoy + ¿subiendo vs su propio nivel?
    private static function _vencidas(int $empresa_id, int $uid): array
    {
        $now = 0;
        try { $now = (int)(Mesa::armar($empresa_id, $uid)['resumen']['vencidas'] ?? 0); } catch (Throwable $e) {}
        $v7 = 0; $vprior = 0;
        try {
            $row = DB::row(
                "SELECT
                    COUNT(DISTINCT CASE WHEN fecha >= CURDATE() - INTERVAL 6 DAY THEN cotizacion_id END) AS v7,
                    COUNT(DISTINCT CASE WHEN fecha <  CURDATE() - INTERVAL 6 DAY THEN cotizacion_id END) AS vprior
                 FROM mesa_vencidos
                 WHERE empresa_id = ? AND usuario_id = ? AND fecha >= CURDATE() - INTERVAL 27 DAY",
                [$empresa_id, $uid]
            );
            $v7 = (int)($row['v7'] ?? 0); $vprior = (int)($row['vprior'] ?? 0);
        } catch (Throwable $e) {}
        $base_wk = $vprior / 3.0;                 // promedio semanal de las 3 semanas previas
        $sube    = ($v7 > $base_wk) && ($v7 >= 2); // sube vs su propio nivel (piso 2 para no marcar 0→1)
        return [$now > 0 ? $now : $v7, $sube, (int)round($base_wk)];
    }

    // Citas: hechas en 7d vs su propio promedio semanal (28d/4).
    private static function _citas(int $empresa_id, int $uid): array
    {
        try {
            $row = DB::row(
                "SELECT
                    SUM(CASE WHEN m.created_at >= NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS c7,
                    COUNT(*) AS c28
                 FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
                 WHERE m.empresa_id = ? AND m.area = 'compromiso' AND m.estado = 'nos_citamos'
                   AND COALESCE(c.vendedor_id, c.usuario_id) = ?
                   AND m.created_at >= NOW() - INTERVAL " . self::DIAS_BASE . " DAY",
                [$empresa_id, $uid]
            );
            $c7 = (int)($row['c7'] ?? 0); $c28 = (int)($row['c28'] ?? 0);
        } catch (Throwable $e) { return [0, 0, false]; }
        $base_wk = $c28 / 4.0;
        // Solo alarma si tenía ritmo de agendar (base>=1) Y esta semana cayó.
        $baja = ($base_wk >= 1.0) && ($c7 < $base_wk);
        return [$c7, (int)round($base_wk), $baja];
    }

    private static function _motivo(string $nombre, string $sem, bool $gaming, int $cierra, bool $venc_sube, int $venc_now, int $venc_prev, bool $citas_baja, int $citas7, int $citas_base, int $descartes, int $ventas): string
    {
        $p = trim(explode(' ', trim($nombre))[0] ?? $nombre);
        if ($gaming) return "Descarta más de lo que cierra ({$descartes} descartes vs {$ventas} ventas) — revisa qué está tirando.";
        if ($cierra < self::CIERRA_BAJO && $sem !== 'verde') return "Cierra por debajo del promedio de la empresa — apóyalo a cerrar.";
        if ($venc_sube) return "Se le empiezan a vencer seguimientos ({$venc_prev}→{$venc_now}/sem) — dejó de mantenerse al día.";
        if ($citas_baja) return "Bajó su ritmo de citas ({$citas7} esta semana vs ~{$citas_base} normal) — su embudo se está secando.";
        return "Va bien — cierra y juega limpio.";
    }

    private static function _clamp(int $v, int $lo, int $hi): int { return max($lo, min($hi, $v)); }
}
