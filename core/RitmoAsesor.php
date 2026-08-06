<?php
// ============================================================
//  RitmoAsesor — Rendimiento de asesores desde la Mesa
//  SOLO LECTURA. Se mide contra las REGLAS DE LA MESA (no contra
//  empresa ni historia). Muestra HECHOS; el gerente diagnostica.
//
//  5 PILARES, cada uno con SUS parámetros (nada resumido a uno):
//    CONVERSIÓN  — cerró vs cotizaciones
//    DESCARTADAS — vs cierres (volumen) · sin cita · muy rápido (ciclo)
//    CITAS       — su ritmo de agendar (bajó vs su normal)
//    SEGUIMIENTO — vencidas (el cronómetro de la Mesa)
//    CONTACTO    — no logra contactar (leads que lo buscaron)
//
//  Guarda: solo empresas con Mesa activa.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    private const DIAS_BASE     = 28;
    private const DUMP_MIN      = 3;   // mínimo de descartes para juzgar
    private const CONTACTO_MIN  = 4;   // mínimo de contactados para juzgar
    private const CERO_MIN      = 8;   // trabajó esto y cerró 0 → alarma

    public static function empresa(int $empresa_id): array
    {
        try {
            if ((int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$empresa_id]) < 1) return [];
        } catch (Throwable $e) { return []; }

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

        $filas = [];
        foreach ($asesores as $a) {
            $f = self::_asesor($empresa_id, (int)$a['id'], (string)$a['nombre'], $win, $rapido_dias);
            if ($f !== null) $filas[] = $f;
        }

        $peso = ['rojo' => 0, 'amarillo' => 1, 'verde' => 2];
        usort($filas, function ($x, $y) use ($peso) {
            $px = $peso[$x['semaforo']] ?? 3; $py = $peso[$y['semaforo']] ?? 3;
            if ($px !== $py) return $px <=> $py;
            return $y['problemas'] <=> $x['problemas']; // más pilares en problema arriba
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

    private static function _asesor(int $empresa_id, int $uid, string $nombre, int $win, int $rapido_dias): ?array
    {
        $cierres = 0; $trabajo = 0; $desc = 0; $sincita = 0; $rapido = 0; $contactados = 0; $no_conecta = 0;
        try { $cierres = self::_cierres($empresa_id, $uid, $win); } catch (Throwable $e) {}
        try { $trabajo = self::_trabajo($empresa_id, $uid, $win); } catch (Throwable $e) {}
        try { [$desc, $sincita, $rapido] = self::_descartes($empresa_id, $uid, $win, $win, $rapido_dias); } catch (Throwable $e) {}
        try { [$contactados, $no_conecta] = self::_contacto($empresa_id, $uid, $win); } catch (Throwable $e) {}
        [$venc_now, $venc_sube, $venc_base, $venc_week] = self::_vencidas($empresa_id, $uid);
        [$citas7, $citas_base, $citas_baja] = self::_citas($empresa_id, $uid);

        $conv_rate  = $trabajo > 0 ? (int)round($cierres / $trabajo * 100) : 0;
        $cero       = ($cierres === 0 && $trabajo >= self::CERO_MIN);
        $r_sincita  = $desc >= self::DUMP_MIN ? $sincita / $desc : 0.0;
        $r_rapido   = $desc >= self::DUMP_MIN ? $rapido / $desc : 0.0;
        $r_noc      = $contactados >= self::CONTACTO_MIN ? $no_conecta / $contactados : 0.0;

        // ── PILAR 1: Conversión ──
        $conv_estado = $cierres > 0 ? 'verde' : ($cero ? 'amarillo' : 'verde');
        $conv_txt    = "cerró {$cierres} de {$trabajo}" . ($trabajo > 0 ? " ({$conv_rate}%)" : "");

        // ── PILAR 2: Descartadas (volumen vs cierres · sin cita · muy rápido) ──
        $dumping = ($desc >= self::DUMP_MIN && $desc > $cierres);
        if ($dumping && ($r_sincita >= 0.6 || $r_rapido >= 0.6))       $desc_estado = 'rojo';
        elseif ($desc >= self::DUMP_MIN && ($r_sincita >= 0.35 || $r_rapido >= 0.3 || $desc > 2 * max($cierres, 1))) $desc_estado = 'amarillo';
        else                                                          $desc_estado = 'verde';
        $desc_txt = self::_desc_txt($desc_estado, $desc, $sincita, $rapido, $cierres);

        // ── PILAR 3: Citas (su ritmo de agendar) ──
        $citas_estado = $citas_baja ? 'amarillo' : 'verde';
        $citas_txt    = $citas_baja ? "bajó su ritmo ({$citas7} esta semana vs ~{$citas_base})" : "✓ al ritmo";

        // ── PILAR 4: Seguimiento (vencidas, el cronómetro) ──
        $venc_estado = $venc_sube ? 'amarillo' : 'verde';
        $venc_txt    = $venc_sube ? "se le vencen seguimientos ({$venc_week} esta semana vs ~{$venc_base})" : "✓ al día";

        // ── PILAR 5: Contacto (no logra contactar) ──
        if ($r_noc >= 0.6)       $cont_estado = 'rojo';
        elseif ($r_noc >= 0.35)  $cont_estado = 'amarillo';
        else                     $cont_estado = 'verde';
        $cont_txt = $no_conecta > 0 ? "no logró contactar a {$no_conecta} de {$contactados}" : "✓ contacta";

        // Semáforo del asesor = el PEOR de sus 5 pilares.
        $estados = [$conv_estado, $desc_estado, $citas_estado, $venc_estado, $cont_estado];
        $ord = ['verde' => 0, 'amarillo' => 1, 'rojo' => 2];
        $sem = 'verde';
        foreach ($estados as $st) if ($ord[$st] > $ord[$sem]) $sem = $st;
        $problemas = count(array_filter($estados, fn($s) => $s !== 'verde'));
        $flag = ($sem === 'rojo');

        $motivo = self::_motivo($desc_estado, $cont_estado, $venc_sube, $citas_baja, $cero);

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem, 'flag' => $flag, 'problemas' => $problemas,
            'conv_estado' => $conv_estado, 'conv_txt' => $conv_txt,
            'desc_estado' => $desc_estado, 'desc_txt' => $desc_txt,
            'citas_estado' => $citas_estado, 'citas_txt' => $citas_txt,
            'venc_estado' => $venc_estado, 'venc_txt' => $venc_txt,
            'cont_estado' => $cont_estado, 'cont_txt' => $cont_txt,
            'motivo' => $motivo,
        ];
    }

    private static function _desc_txt(string $estado, int $desc, int $sincita, int $rapido, int $cierres): string
    {
        if ($estado === 'verde') return $desc > 0 ? "descarta sano ({$desc}, cerró {$cierres})" : "✓ sin descartes";
        $sub = [];
        if ($sincita > 0) $sub[] = "{$sincita} sin cita";
        if ($rapido > 0)  $sub[] = "{$rapido} muy rápido";
        return "descartó {$desc}" . ($sub ? " (" . implode(', ', $sub) . ")" : "") . " · cerró {$cierres}";
    }

    private static function _motivo(string $desc_estado, string $cont_estado, bool $venc_sube, bool $citas_baja, bool $cero): string
    {
        if ($desc_estado === 'rojo') return "Descarta mal — sin llegar a cita y muy rápido. Que trabaje el lead antes de tirarlo.";
        if ($cont_estado === 'rojo') return "No logra contactar a sus leads (que lo buscaron) — revisa cómo y cuándo les habla.";
        if ($desc_estado === 'amarillo') return "Cuida sus descartes — que llegue a cita antes de tirar.";
        if ($cont_estado === 'amarillo') return "Le cuesta contactar — algo hace mal en el arranque.";
        if ($venc_sube) return "Se le está acumulando el seguimiento esta semana — presiónalo.";
        if ($citas_baja) return "Bajó su ritmo de citas esta semana — su embudo se está secando.";
        if ($cero) return "Trabajó bastante pero no ha cerrado — ¿qué lo está frenando?";
        return "Va bien — cierra, descarta sano y da seguimiento.";
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
}
