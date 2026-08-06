<?php
// ============================================================
//  RitmoAsesor — Rendimiento de asesores desde la Mesa
//  SOLO LECTURA. No toca la Mesa ni ActividadScore.
//
//  Diseño final (acordado con el CEO). Distingue:
//   · CADENCIA de revisar = semanal (tú actúas cada 7 días)
//   · VENTANA de medir     = según lo que necesita cada señal:
//
//    VEREDICTO (sobre el CICLO REAL de la empresa, 2×p75 — un rate
//    necesita muestra; 7 días era espejismo):
//      CONVERSIÓN — cerró vs trabajó, vs su empresa
//      LIMPIO     — gaming: descartes vs cierres (volumen) + descartes
//                   SIN cita (calidad). Solo descartes EN JUEGO. NO se
//                   acusa de gaming a un asesor nuevo (cartera heredada
//                   = limpieza, caso Manuel/Israel).
//    ALERTAS DE RITMO (7 días vs su propio nivel — cachan el cambio ya):
//      ⏰ Vencidas subiendo · 📅 Citas bajando
//
//  Guarda: solo empresas con Mesa activa. SIN gracia al score.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    private const DIAS_RITMO = 7;    // alertas: cambio de la semana
    private const DIAS_BASE  = 28;   // baseline propio del ritmo (4 semanas)
    private const CONV_BAJO  = 50;
    private const SCORE_ROJO = 35;
    private const SCORE_VERDE= 58;
    private const DUMP_MIN   = 3;    // mínimo de descartes en-juego para juzgar gaming

    public static function empresa(int $empresa_id): array
    {
        try {
            if ((int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$empresa_id]) < 1) return [];
        } catch (Throwable $e) { return []; }

        // Ciclo REAL de la empresa → ventana del veredicto (2×p75).
        $p75 = 10;
        try {
            if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
            $c = Radar::ciclo_venta($empresa_id);
            if (!empty($c['auto']) && !empty($c['p75'])) $p75 = max(3, (int)$c['p75']);
        } catch (Throwable $e) {}
        $win = 2 * $p75; // ventana del veredicto (conversión + gaming) y "en juego"

        try {
            $asesores = DB::query(
                "SELECT DISTINCT u.id, u.nombre
                   FROM usuarios u
                   JOIN cotizaciones c ON COALESCE(c.vendedor_id, c.usuario_id) = u.id
                  WHERE u.empresa_id = ? AND u.activo = 1 AND u.rol <> 'superadmin'
                    AND c.empresa_id = ? AND c.created_at >= NOW() - INTERVAL 120 DAY
                  ORDER BY u.nombre ASC",
                [$empresa_id, $empresa_id]
            );
        } catch (Throwable $e) { return []; }
        if (!$asesores) return [];

        // Benchmark de la empresa sobre la MISMA ventana del veredicto.
        $b = self::_bench($empresa_id, $win);

        $filas = [];
        foreach ($asesores as $a) {
            $f = self::_asesor($empresa_id, (int)$a['id'], (string)$a['nombre'], $win, $b);
            if ($f !== null) $filas[] = $f;
        }

        $peso = ['rojo' => 0, 'amarillo' => 1, 'verde' => 2];
        usort($filas, function ($x, $y) use ($peso) {
            $px = $peso[$x['semaforo']] ?? 3; $py = $peso[$y['semaforo']] ?? 3;
            if ($px !== $py) return $px <=> $py;
            return $x['score'] <=> $y['score'];
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

    // Benchmark de la empresa sobre la ventana del veredicto ($win).
    private static function _bench(int $empresa_id, int $win): array
    {
        $cierres = 0; $trabajo = 0; $desc = 0;
        try { $cierres = self::_cierres($empresa_id, null, $win); } catch (Throwable $e) {}
        try { $trabajo = self::_trabajo($empresa_id, null, $win); } catch (Throwable $e) {}
        try { [$desc,] = self::_descartes($empresa_id, null, $win, $win); } catch (Throwable $e) {}
        return [
            'conv' => $trabajo > 0 ? $cierres / $trabajo : 0.0,
            'dump' => ($desc + $cierres) > 0 ? $desc / ($desc + $cierres) : 0.0,
        ];
    }

    private static function _asesor(int $empresa_id, int $uid, string $nombre, int $win, array $b): ?array
    {
        // Veredicto (conversión + gaming) sobre la ventana del CICLO ($win).
        $cierres = 0; $trabajo = 0; $desc = 0; $sincita = 0;
        try { $cierres = self::_cierres($empresa_id, $uid, $win); } catch (Throwable $e) {}
        try { $trabajo = self::_trabajo($empresa_id, $uid, $win); } catch (Throwable $e) {}
        try { [$desc, $sincita] = self::_descartes($empresa_id, $uid, $win, $win); } catch (Throwable $e) {}

        // Antigüedad: el gaming necesita que la cartera sea SUYA. Si entró hace
        // menos que la ventana del ciclo ($win = 2×p75), lo que descarta es
        // herencia (limpieza), NO trampa → no se le acusa de gaming.
        $antiguedad = 999;
        try { $antiguedad = (int) DB::val("SELECT DATEDIFF(NOW(), created_at) FROM usuarios WHERE id=?", [$uid]); } catch (Throwable $e) {}

        // ── CONVERSIÓN (cerró vs trabajó, vs su empresa) ──
        if ($trabajo === 0) {
            $conv = 0;
        } else {
            $his = $cierres / $trabajo;
            $ratio = $b['conv'] > 0 ? $his / $b['conv'] : 1.0;
            $conv = self::_clamp((int)round($ratio * 50), 0, 100);
        }

        // ── LIMPIO (gaming) — dos tells sobre descartes EN JUEGO ──
        $dump_ratio    = ($desc + $cierres) > 0 ? $desc / ($desc + $cierres) : 0.0;
        $sincita_ratio = $desc > 0 ? $sincita / $desc : 0.0;
        if ($desc < self::DUMP_MIN) {
            $limpio = 100;
        } else {
            $exceso = max(0.0, $dump_ratio - $b['dump']);
            $limpio = self::_clamp((int)round(100 - $exceso * 120 - $sincita_ratio * 40), 0, 100);
        }
        $gaming = ($antiguedad >= $win)                          // la cartera ya es suya
               && ($desc >= self::DUMP_MIN) && ($dump_ratio > $b['dump'])
               && ($sincita_ratio >= 0.5) && ($conv < self::CONV_BAJO);

        // ── ALERTAS DE RITMO (7 días) ──
        [$venc_now, $venc_sube, $venc_base, $venc_week] = self::_vencidas($empresa_id, $uid);
        [$citas7, $citas_base, $citas_baja] = self::_citas($empresa_id, $uid);

        $score = (int)round(0.6 * $conv + 0.4 * $limpio);
        if ($gaming) $score = min($score, 30);

        $alerta_ritmo = $venc_sube || $citas_baja;
        if ($gaming || $score < self::SCORE_ROJO) {
            $sem = 'rojo';
        } elseif ($conv < self::CONV_BAJO || $alerta_ritmo || $score < self::SCORE_VERDE) {
            $sem = 'amarillo';
        } else {
            $sem = 'verde';
        }

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem, 'score' => $score,
            'conv' => $conv, 'limpio' => $limpio, 'gaming' => $gaming, 'nuevo' => ($antiguedad < $win),
            'cierres7' => $cierres, 'trabajo7' => $trabajo,
            'desc7' => $desc, 'sincita7' => $sincita,
            'venc_now' => $venc_now, 'venc_week' => $venc_week, 'venc_base' => $venc_base, 'venc_sube' => $venc_sube,
            'citas7' => $citas7, 'citas_base' => $citas_base, 'citas_baja' => $citas_baja,
            'motivo' => self::_motivo($sem, $gaming, ($antiguedad < $win), $conv, $cierres, $trabajo, $desc, $sincita, $venc_sube, $venc_week, $venc_base, $citas_baja, $citas7, $citas_base),
        ];
    }

    private static function _cierres(int $empresa_id, ?int $uid, int $dias): int
    {
        $w = $uid !== null ? "AND COALESCE(v.vendedor_id, v.usuario_id, c.vendedor_id, c.usuario_id) = ?" : "";
        $p = [$empresa_id]; if ($uid !== null) $p[] = $uid;
        return (int) DB::val(
            "SELECT COUNT(*) FROM ventas v
               LEFT JOIN cotizaciones c ON c.id = v.cotizacion_id
              WHERE v.empresa_id = ? AND v.estado <> 'cancelada' AND v.pagado > 0 AND v.total > 0
                AND v.created_at >= NOW() - INTERVAL $dias DAY $w", $p
        );
    }

    // "Trabajó" = cotizaciones que MOVIÓ en la Mesa (cualquier captura) en la ventana.
    private static function _trabajo(int $empresa_id, ?int $uid, int $dias): int
    {
        $w = $uid !== null ? "AND COALESCE(c.vendedor_id, c.usuario_id) = ?" : "";
        $p = [$empresa_id]; if ($uid !== null) $p[] = $uid;
        return (int) DB::val(
            "SELECT COUNT(DISTINCT m.cotizacion_id)
               FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
              WHERE m.empresa_id = ? AND m.created_at >= NOW() - INTERVAL $dias DAY $w", $p
        );
    }

    // Descartes EN JUEGO (cot creada dentro de la ventana) + cuántos SIN cita.
    private static function _descartes(int $empresa_id, ?int $uid, int $dias, int $en_juego_dias): array
    {
        $w = $uid !== null ? "AND COALESCE(c.vendedor_id, c.usuario_id) = ?" : "";
        $p = [$empresa_id]; if ($uid !== null) $p[] = $uid;
        $p2 = [$empresa_id]; if ($uid !== null) $p2[] = $uid;
        $row = DB::row(
            "SELECT COUNT(DISTINCT d.cid) AS n,
                    COUNT(DISTINCT CASE WHEN NOT EXISTS (
                        SELECT 1 FROM mesa_estados mc
                         WHERE mc.cotizacion_id = d.cid AND mc.area = 'compromiso' AND mc.estado = 'nos_citamos'
                    ) THEN d.cid END) AS sin_cita
             FROM (
                SELECT m.cotizacion_id AS cid
                  FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
                 WHERE m.empresa_id = ? AND m.area = 'postura' AND m.estado = 'descartada'
                   AND m.created_at >= NOW() - INTERVAL $dias DAY
                   AND c.created_at >= NOW() - INTERVAL $en_juego_dias DAY $w
                UNION
                SELECT rf.cotizacion_id AS cid
                  FROM radar_feedback rf JOIN cotizaciones c ON c.id = rf.cotizacion_id
                 WHERE rf.empresa_id = ? AND rf.tipo = 'sin_interes'
                   AND rf.updated_at >= NOW() - INTERVAL $dias DAY
                   AND c.created_at >= NOW() - INTERVAL $en_juego_dias DAY $w
             ) d",
            array_merge($p, $p2)
        );
        return [(int)($row['n'] ?? 0), (int)($row['sin_cita'] ?? 0)];
    }

    // Vencidas: hoy + ¿subiendo? Devuelve [hoy, sube, base_semanal, esta_semana].
    private static function _vencidas(int $empresa_id, int $uid): array
    {
        $v7 = 0; $vprior = 0; $vnow = 0;
        try {
            $row = DB::row(
                "SELECT
                    COUNT(DISTINCT CASE WHEN fecha >= CURDATE() THEN cotizacion_id END) AS vnow,
                    COUNT(DISTINCT CASE WHEN fecha >= CURDATE() - INTERVAL 6 DAY THEN cotizacion_id END) AS v7,
                    COUNT(DISTINCT CASE WHEN fecha <  CURDATE() - INTERVAL 6 DAY THEN cotizacion_id END) AS vprior
                 FROM mesa_vencidos
                 WHERE empresa_id = ? AND usuario_id = ? AND fecha >= CURDATE() - INTERVAL 27 DAY",
                [$empresa_id, $uid]
            );
            $vnow = (int)($row['vnow'] ?? 0); $v7 = (int)($row['v7'] ?? 0); $vprior = (int)($row['vprior'] ?? 0);
        } catch (Throwable $e) {}
        $base_wk = $vprior / 3.0;
        $sube = ($vprior > 0) && ($v7 >= 3) && ($v7 > $base_wk * 1.5);
        return [$vnow, $sube, (int)round($base_wk), $v7];
    }

    // Citas: hechas en 7d vs su propio promedio semanal (28d/4). Conservador.
    private static function _citas(int $empresa_id, int $uid): array
    {
        try {
            $row = DB::row(
                "SELECT SUM(CASE WHEN m.created_at >= NOW() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS c7, COUNT(*) AS c28
                   FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
                  WHERE m.empresa_id = ? AND m.area = 'compromiso' AND m.estado = 'nos_citamos'
                    AND COALESCE(c.vendedor_id, c.usuario_id) = ?
                    AND m.created_at >= NOW() - INTERVAL " . self::DIAS_BASE . " DAY",
                [$empresa_id, $uid]
            );
            $c7 = (int)($row['c7'] ?? 0); $c28 = (int)($row['c28'] ?? 0);
        } catch (Throwable $e) { return [0, 0, false]; }
        $base_wk = $c28 / 4.0;
        $baja = ($base_wk >= 2.0) && ($c7 < $base_wk * 0.5);
        return [$c7, (int)round($base_wk), $baja];
    }

    private static function _motivo(string $sem, bool $gaming, bool $nuevo, int $conv, int $cierres, int $trabajo, int $desc, int $sincita, bool $venc_sube, int $venc_week, int $venc_base, bool $citas_baja, int $citas7, int $citas_base): string
    {
        if ($gaming) return "Descarta sin trabajar ({$desc} descartes, {$sincita} sin cita) y cierra poco — está limpiando la mesa, no vendiendo.";
        if ($trabajo === 0) return "No movió ninguna cotización — ¿por qué está parado?";
        if ($conv < self::CONV_BAJO && $sem !== 'verde') {
            $extra = $nuevo ? " (nuevo — cartera heredada, dale tiempo)" : "";
            return "Convierte poco: cerró {$cierres} de {$trabajo} que trabajó — apóyalo a cerrar{$extra}.";
        }
        if ($venc_sube) return "Se le vencieron {$venc_week} seguimientos esta semana vs ~{$venc_base} normal — dejó de mantenerse al día.";
        if ($citas_baja) return "Bajó su ritmo de citas ({$citas7} esta semana vs ~{$citas_base} normal) — su embudo se está secando.";
        return "Va bien — cierra lo que trabaja y juega limpio.";
    }

    private static function _clamp(int $v, int $lo, int $hi): int { return max($lo, min($hi, $v)); }
}
