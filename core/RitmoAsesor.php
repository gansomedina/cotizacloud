<?php
// ============================================================
//  RitmoAsesor — Rendimiento de asesores desde la Mesa
//  SOLO LECTURA. No toca la Mesa ni ActividadScore.
//
//  Diseño final (acordado con el CEO), TODO semanal + auto-ajustable
//  sobre el CICLO REAL de cada empresa (Radar::ciclo_venta), cero
//  valores fijos de negocio:
//
//    CONVERSIÓN  — cerró vs trabajó esta semana (7d), vs su empresa
//    LIMPIO      — gaming de descartes, DOS tells:
//                    · descartes vs cierres (volumen)
//                    · descartes SIN cita   (calidad)
//                  Solo cuenta descartar cotizaciones AÚN EN JUEGO
//                  (creadas dentro de 2×p75) → limpiar cartera vieja
//                  heredada NO es gaming (caso Manuel/Israel).
//    RITMO       — ⏰ vencidas subiendo · 📅 citas bajando (7d vs su nivel)
//
//  Guarda: solo empresas con Mesa activa. SIN gracia para nuevos.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    private const DIAS       = 7;    // ventana semanal (el ciclo real ≤ p75 = 7-10d)
    private const DIAS_BENCH = 30;   // benchmark de la empresa (estable)
    private const DIAS_BASE  = 28;   // baseline propio del ritmo (4 semanas)
    // Umbrales del semáforo — RELATIVOS al promedio (50 = promedio de su empresa).
    private const CONV_BAJO  = 50;
    private const SCORE_ROJO = 35;
    private const SCORE_VERDE= 58;
    private const DUMP_MIN   = 3;    // mínimo de descartes en-juego para juzgar gaming

    public static function empresa(int $empresa_id): array
    {
        try {
            if ((int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$empresa_id]) < 1) return [];
        } catch (Throwable $e) { return []; }

        // Ciclo REAL de la empresa (para la ventana de "en juego" del gaming).
        $p75 = 10;
        try {
            if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
            $c = Radar::ciclo_venta($empresa_id);
            if (!empty($c['auto']) && !empty($c['p75'])) $p75 = max(3, (int)$c['p75']);
        } catch (Throwable $e) {}
        $en_juego_dias = 2 * $p75; // creada dentro de 2×p75 = todavía tenía chance

        // Asesores de la empresa (con cartera). Excluye superadmin.
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

        // Benchmark de la empresa (30d): conversión y ratio de descarte.
        $b = self::_bench($empresa_id, $en_juego_dias);

        $filas = [];
        foreach ($asesores as $a) {
            $f = self::_asesor($empresa_id, (int)$a['id'], (string)$a['nombre'], $en_juego_dias, $b);
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

    // Benchmark de la empresa (30d): para auto-ajustar conversión y gaming.
    private static function _bench(int $empresa_id, int $en_juego_dias): array
    {
        $cierres = 0; $trabajo = 0; $desc = 0;
        try { $cierres = self::_cierres($empresa_id, null, self::DIAS_BENCH); } catch (Throwable $e) {}
        try { $trabajo = self::_trabajo($empresa_id, null, self::DIAS_BENCH); } catch (Throwable $e) {}
        try { [$desc,] = self::_descartes($empresa_id, null, self::DIAS_BENCH, $en_juego_dias); } catch (Throwable $e) {}
        return [
            'conv' => $trabajo > 0 ? $cierres / $trabajo : 0.0,
            'dump' => ($desc + $cierres) > 0 ? $desc / ($desc + $cierres) : 0.0,
        ];
    }

    private static function _asesor(int $empresa_id, int $uid, string $nombre, int $en_juego_dias, array $b): ?array
    {
        $cierres7 = 0; $trabajo7 = 0; $desc7 = 0; $sincita7 = 0;
        try { $cierres7 = self::_cierres($empresa_id, $uid, self::DIAS); } catch (Throwable $e) {}
        try { $trabajo7 = self::_trabajo($empresa_id, $uid, self::DIAS); } catch (Throwable $e) {}
        try { [$desc7, $sincita7] = self::_descartes($empresa_id, $uid, self::DIAS, $en_juego_dias); } catch (Throwable $e) {}

        // ── CONVERSIÓN (cerró vs trabajó, vs su empresa) ──
        if ($trabajo7 === 0) {
            $conv = 0;                                   // no movió nada esta semana
        } else {
            $his = $cierres7 / $trabajo7;
            $ratio = $b['conv'] > 0 ? $his / $b['conv'] : 1.0;
            $conv = self::_clamp((int)round($ratio * 50), 0, 100);
        }

        // ── LIMPIO (gaming) — dos tells, sobre descartes EN JUEGO ──
        $dump_ratio = ($desc7 + $cierres7) > 0 ? $desc7 / ($desc7 + $cierres7) : 0.0; // volumen
        $sincita_ratio = $desc7 > 0 ? $sincita7 / $desc7 : 0.0;                       // calidad
        if ($desc7 < self::DUMP_MIN) {
            $limpio = 100;                               // no descarta lo suficiente para juzgar
        } else {
            $exceso = max(0.0, $dump_ratio - $b['dump']); // descarta MÁS que su empresa
            $limpio = self::_clamp((int)round(100 - $exceso * 120 - $sincita_ratio * 40), 0, 100);
        }
        // Gaming: descarta en-juego MÁS que su empresa, la mayoría SIN cita, Y
        // cierra por debajo del promedio. El que cierra bien (Kevin) NO cae.
        $gaming = ($desc7 >= self::DUMP_MIN) && ($dump_ratio > $b['dump'])
               && ($sincita_ratio >= 0.5) && ($conv < self::CONV_BAJO);

        // ── RITMO ──
        [$venc_now, $venc_sube, $venc_prev] = self::_vencidas($empresa_id, $uid);
        [$citas7, $citas_base, $citas_baja]  = self::_citas($empresa_id, $uid);

        // ── Score (Conversión + Limpio; gaming lo aplasta) ──
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
            'conv' => $conv, 'limpio' => $limpio, 'gaming' => $gaming,
            'cierres7' => $cierres7, 'trabajo7' => $trabajo7,
            'desc7' => $desc7, 'sincita7' => $sincita7,
            'venc_now' => $venc_now, 'venc_prev' => $venc_prev, 'venc_sube' => $venc_sube,
            'citas7' => $citas7, 'citas_base' => $citas_base, 'citas_baja' => $citas_baja,
            'motivo' => self::_motivo($nombre, $sem, $gaming, $conv, $cierres7, $trabajo7, $desc7, $sincita7, $venc_sube, $venc_prev, $venc_now, $citas_baja, $citas7, $citas_base),
        ];
    }

    // Cierres (pagado>0) en la ventana. $uid null = toda la empresa.
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

    // Descartes EN JUEGO (cot creada dentro de 2×p75) + cuántos SIN cita.
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

    // Vencidas: hoy + ¿subiendo vs su propio nivel? (mesa_vencidos).
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
        // Sube vs su propio nivel: exige historial (vprior>0), salto claro y piso 3.
        $sube = ($vprior > 0) && ($v7 >= 3) && ($v7 > $base_wk * 1.5);
        return [$vnow, $sube, (int)round($base_wk)];
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
        $baja = ($base_wk >= 2.0) && ($c7 < $base_wk * 0.5); // tenía ritmo y cayó a la mitad
        return [$c7, (int)round($base_wk), $baja];
    }

    private static function _motivo(string $nombre, string $sem, bool $gaming, int $conv, int $cierres7, int $trabajo7, int $desc7, int $sincita7, bool $venc_sube, int $venc_prev, int $venc_now, bool $citas_baja, int $citas7, int $citas_base): string
    {
        if ($gaming) return "Descarta sin trabajar ({$desc7} descartes, {$sincita7} sin cita) y cierra poco — está limpiando la mesa, no vendiendo.";
        if ($trabajo7 === 0) return "No movió ninguna cotización esta semana — ¿por qué está parado?";
        if ($conv < self::CONV_BAJO && $sem !== 'verde') return "Convierte poco: cerró {$cierres7} de {$trabajo7} que trabajó — apóyalo a cerrar.";
        if ($venc_sube) return "Se le empiezan a vencer seguimientos ({$venc_prev}→{$venc_now}/sem) — dejó de mantenerse al día.";
        if ($citas_baja) return "Bajó su ritmo de citas ({$citas7} esta semana vs ~{$citas_base} normal) — su embudo se está secando.";
        return "Va bien — cierra lo que trabaja y juega limpio.";
    }

    private static function _clamp(int $v, int $lo, int $hi): int { return max($lo, min($hi, $v)); }
}
