<?php
// ============================================================
//  RitmoAsesor — Rendimiento de asesores desde la Mesa
//  SOLO LECTURA. No toca la Mesa ni ActividadScore.
//
//  El tool muestra el COMPORTAMIENTO; el gerente diagnostica el porqué.
//  NUNCA acusa de "trampa" (no puede saber la intención).
//
//  VEREDICTO (sobre el CICLO REAL de la empresa, 2×p75):
//    CONVERSIÓN — cerró vs trabajó, vs su empresa
//    PROCESO    — ¿sigue Cotización→Cita→Seguimiento? 3 tells factuales:
//                   · descarta sin llegar a cita
//                   · descarta muy rápido (antes del ciclo)
//                   · muchos "no contestó" (no logra contactar leads que
//                     ellos MISMOS pidieron → algo hace mal)
//                 Solo descartes EN JUEGO (creados dentro de 2×p75).
//  ALERTAS DE RITMO (7 días vs su propio nivel):
//    ⏰ Vencidas subiendo · 📅 Citas bajando
//
//  Guarda: solo empresas con Mesa activa.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    private const DIAS_BASE   = 28;
    private const CONV_BAJO   = 50;
    private const SCORE_ROJO  = 35;
    private const SCORE_VERDE = 58;
    private const PROC_BAJO   = 60;   // proceso por debajo → amarillo
    private const PROC_MALO   = 50;   // proceso claramente malo → parte del flag
    private const DUMP_MIN    = 3;    // mínimo de descartes para juzgar el proceso
    private const CONTACTO_MIN= 4;    // mínimo de contactados para juzgar "no contestó"

    public static function empresa(int $empresa_id): array
    {
        try {
            if ((int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$empresa_id]) < 1) return [];
        } catch (Throwable $e) { return []; }

        // Ciclo REAL → ventana del veredicto (2×p75) y umbral de "muy rápido".
        $p75 = 10; $mediana = 5;
        try {
            if (!class_exists('Radar')) require_once MODULES_PATH . '/radar/Radar.php';
            $c = Radar::ciclo_venta($empresa_id);
            if (!empty($c['auto'])) {
                if (!empty($c['p75']))     $p75 = max(3, (int)$c['p75']);
                if (!empty($c['mediana'])) $mediana = max(1, (int)$c['mediana']);
            }
        } catch (Throwable $e) {}
        $win = 2 * $p75;
        $rapido_dias = max(1, (int)floor($mediana / 2)); // descartar antes de esto = muy rápido

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

        $b = self::_bench($empresa_id, $win, $rapido_dias);

        $filas = [];
        foreach ($asesores as $a) {
            $f = self::_asesor($empresa_id, (int)$a['id'], (string)$a['nombre'], $win, $rapido_dias, $b);
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

    // Benchmark de la empresa (misma ventana): conversión + normas de proceso.
    private static function _bench(int $empresa_id, int $win, int $rapido_dias): array
    {
        $cierres = 0; $trabajo = 0; $dn = 0; $dsc = 0; $cont = 0; $noc = 0;
        try { $cierres = self::_cierres($empresa_id, null, $win); } catch (Throwable $e) {}
        try { $trabajo = self::_trabajo($empresa_id, null, $win); } catch (Throwable $e) {}
        try { [$dn, $dsc,] = self::_descartes($empresa_id, null, $win, $win, $rapido_dias); } catch (Throwable $e) {}
        try { [$cont, $noc] = self::_contacto($empresa_id, null, $win); } catch (Throwable $e) {}
        return [
            'conv'    => $trabajo > 0 ? $cierres / $trabajo : 0.0,
            'sincita' => $dn > 0 ? $dsc / $dn : 0.0,
            'noc'     => $cont > 0 ? $noc / $cont : 0.0,
        ];
    }

    private static function _asesor(int $empresa_id, int $uid, string $nombre, int $win, int $rapido_dias, array $b): ?array
    {
        $cierres = 0; $trabajo = 0; $desc = 0; $sincita = 0; $rapido = 0; $contactados = 0; $no_conecta = 0;
        try { $cierres = self::_cierres($empresa_id, $uid, $win); } catch (Throwable $e) {}
        try { $trabajo = self::_trabajo($empresa_id, $uid, $win); } catch (Throwable $e) {}
        try { [$desc, $sincita, $rapido] = self::_descartes($empresa_id, $uid, $win, $win, $rapido_dias); } catch (Throwable $e) {}
        try { [$contactados, $no_conecta] = self::_contacto($empresa_id, $uid, $win); } catch (Throwable $e) {}

        // ── CONVERSIÓN ──
        if ($trabajo === 0) {
            $conv = 0;
        } else {
            $his = $cierres / $trabajo;
            $ratio = $b['conv'] > 0 ? $his / $b['conv'] : 1.0;
            $conv = self::_clamp((int)round($ratio * 50), 0, 100);
        }

        // ── PROCESO (¿sigue el embudo?) — 3 tells factuales, vs su empresa ──
        $proceso = 100;
        if ($desc >= self::DUMP_MIN) {
            $r_sincita = $sincita / $desc;
            $r_rapido  = $rapido / $desc;
            $proceso -= (int)round(max(0.0, $r_sincita - $b['sincita']) * 100); // descarta sin cita > norma
            $proceso -= (int)round($r_rapido * 60);                             // descarta muy rápido
        }
        if ($contactados >= self::CONTACTO_MIN) {
            $r_noc = $no_conecta / $contactados;
            $proceso -= (int)round(max(0.0, $r_noc - $b['noc']) * 80);          // no contacta > norma
        }
        $proceso = self::_clamp($proceso, 0, 100);
        // Flag "revisar": cierra poco Y no sigue el proceso. Es un HECHO, no acusa.
        $flag = ($conv < self::CONV_BAJO) && ($proceso < self::PROC_MALO);

        // ── ALERTAS DE RITMO (7 días) ──
        [$venc_now, $venc_sube, $venc_base, $venc_week] = self::_vencidas($empresa_id, $uid);
        [$citas7, $citas_base, $citas_baja] = self::_citas($empresa_id, $uid);

        $score = (int)round(0.6 * $conv + 0.4 * $proceso);
        if ($flag) $score = min($score, 30);

        if ($flag || $score < self::SCORE_ROJO) {
            $sem = 'rojo';
        } elseif ($conv < self::CONV_BAJO || $proceso < self::PROC_BAJO || $venc_sube || $citas_baja || $score < self::SCORE_VERDE) {
            $sem = 'amarillo';
        } else {
            $sem = 'verde';
        }

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem, 'score' => $score,
            'conv' => $conv, 'proceso' => $proceso, 'flag' => $flag,
            'cierres7' => $cierres, 'trabajo7' => $trabajo,
            'desc7' => $desc, 'sincita7' => $sincita, 'rapido7' => $rapido, 'nocontesta7' => $no_conecta,
            'venc_now' => $venc_now, 'venc_week' => $venc_week, 'venc_base' => $venc_base, 'venc_sube' => $venc_sube,
            'citas7' => $citas7, 'citas_base' => $citas_base, 'citas_baja' => $citas_baja,
            'motivo' => self::_motivo($sem, $flag, $conv, $cierres, $trabajo, $proceso, $desc, $sincita, $rapido, $no_conecta, $venc_sube, $venc_week, $venc_base, $citas_baja, $citas7, $citas_base),
        ];
    }

    // Descripción factual del proceso (los tells presentes). NUNCA "trampa".
    private static function _proceso_txt(int $desc, int $sincita, int $rapido, int $no_conecta): string
    {
        $p = [];
        if ($desc > 0) {
            $det = [];
            if ($sincita > 0) $det[] = "{$sincita} sin cita";
            if ($rapido > 0)  $det[] = "{$rapido} muy rápido";
            $p[] = "descartó {$desc}" . ($det ? " (" . implode(', ', $det) . ")" : "");
        }
        if ($no_conecta > 0) $p[] = "no contactó a {$no_conecta}";
        return implode(' · ', $p);
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

    // Descartes EN JUEGO: total, sin cita, y "muy rápido" (descartado < rapido_dias de creada).
    private static function _descartes(int $empresa_id, ?int $uid, int $dias, int $en_juego_dias, int $rapido_dias): array
    {
        $w = $uid !== null ? "AND COALESCE(c.vendedor_id, c.usuario_id) = ?" : "";
        $p = [$empresa_id]; if ($uid !== null) $p[] = $uid;
        $p2 = [$empresa_id]; if ($uid !== null) $p2[] = $uid;
        $row = DB::row(
            "SELECT COUNT(DISTINCT d.cid) AS n,
                    COUNT(DISTINCT CASE WHEN d.sin_cita THEN d.cid END) AS sin_cita,
                    COUNT(DISTINCT CASE WHEN d.rapido   THEN d.cid END) AS rapido
             FROM (
                SELECT m.cotizacion_id AS cid,
                       (NOT EXISTS (SELECT 1 FROM mesa_estados mc
                          WHERE mc.cotizacion_id = m.cotizacion_id AND mc.area='compromiso' AND mc.estado='nos_citamos')) AS sin_cita,
                       (DATEDIFF(m.created_at, c.created_at) <= $rapido_dias) AS rapido
                  FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
                 WHERE m.empresa_id = ? AND m.area='postura' AND m.estado='descartada'
                   AND m.created_at >= NOW() - INTERVAL $dias DAY
                   AND c.created_at >= NOW() - INTERVAL $en_juego_dias DAY $w
                UNION
                SELECT rf.cotizacion_id AS cid,
                       (NOT EXISTS (SELECT 1 FROM mesa_estados mc
                          WHERE mc.cotizacion_id = rf.cotizacion_id AND mc.area='compromiso' AND mc.estado='nos_citamos')) AS sin_cita,
                       (DATEDIFF(rf.updated_at, c.created_at) <= $rapido_dias) AS rapido
                  FROM radar_feedback rf JOIN cotizaciones c ON c.id = rf.cotizacion_id
                 WHERE rf.empresa_id = ? AND rf.tipo='sin_interes'
                   AND rf.updated_at >= NOW() - INTERVAL $dias DAY
                   AND c.created_at >= NOW() - INTERVAL $en_juego_dias DAY $w
             ) d",
            array_merge($p, $p2)
        );
        return [(int)($row['n'] ?? 0), (int)($row['sin_cita'] ?? 0), (int)($row['rapido'] ?? 0)];
    }

    // Contacto: cotizaciones que INTENTÓ contactar, y de ésas cuántas NUNCA logró
    // (no_contesta sin un solo hablamos) = "no contestó" alto.
    private static function _contacto(int $empresa_id, ?int $uid, int $dias): array
    {
        $w = $uid !== null ? "AND COALESCE(c.vendedor_id, c.usuario_id) = ?" : "";
        $p = [$empresa_id]; if ($uid !== null) $p[] = $uid;
        $row = DB::row(
            "SELECT COUNT(DISTINCT m.cotizacion_id) AS contactados,
                    COUNT(DISTINCT CASE WHEN NOT EXISTS (
                        SELECT 1 FROM mesa_estados h
                         WHERE h.cotizacion_id = m.cotizacion_id AND h.area='contacto' AND h.estado='hablamos'
                    ) THEN m.cotizacion_id END) AS no_conecta
               FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
              WHERE m.empresa_id = ? AND m.area='contacto'
                AND m.created_at >= NOW() - INTERVAL $dias DAY $w", $p
        );
        return [(int)($row['contactados'] ?? 0), (int)($row['no_conecta'] ?? 0)];
    }

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

    private static function _motivo(string $sem, bool $flag, int $conv, int $cierres, int $trabajo, int $proceso, int $desc, int $sincita, int $rapido, int $no_conecta, bool $venc_sube, int $venc_week, int $venc_base, bool $citas_baja, int $citas7, int $citas_base): string
    {
        // El MENSAJE es solo el veredicto + qué hacer. Los NÚMEROS van una sola
        // vez, en la línea de datos de la tarjeta (no se repiten aquí).
        if ($flag) return "No sigue el proceso — que trabaje sus leads y llegue a cita antes de tirar.";
        if ($trabajo === 0) return "No movió ninguna cotización esta semana — ¿por qué está parado?";
        if ($proceso < self::PROC_BAJO && $sem !== 'verde') return "Cuida el proceso — que consiga cita antes de descartar.";
        if ($conv < self::CONV_BAJO && $sem !== 'verde') return "Convierte poco de lo que trabaja — apóyalo a cerrar.";
        if ($venc_sube) return "Se le vencieron {$venc_week} seguimientos esta semana vs ~{$venc_base} normal — dejó de mantenerse al día.";
        if ($citas_baja) return "Bajó su ritmo de citas ({$citas7} vs ~{$citas_base} normal) — su embudo se está secando.";
        return "Va bien — cierra lo que trabaja y sigue el proceso.";
    }

    private static function _clamp(int $v, int $lo, int $hi): int { return max($lo, min($hi, $v)); }
}
