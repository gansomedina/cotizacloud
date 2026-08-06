<?php
// ============================================================
//  RitmoAsesor — Rendimiento de asesores desde la Mesa
//  SOLO LECTURA. No toca la Mesa ni ActividadScore.
//
//  Se mide contra las REGLAS DE LA MESA (no contra la empresa ni la
//  historia — eso se diluye o no sirve en un SaaS de 1/2/N asesores).
//  El tool muestra HECHOS; el gerente diagnostica el porqué.
//
//  3 PILARES (siempre visibles):
//    CONVERSIÓN — cerró vs trabajó (cierres/cotizaciones). Un valor directo.
//    PROCESO    — ¿sigue Cotización→Cita→Seguimiento? Roto contra las reglas:
//                   · descarta muy rápido   → vs la VENTANA DE CIERRE (ciclo real)
//                   · descarta sin cita     → se saltó la ETAPA del embudo
//                   · no logró contactar    → no arrancó el proceso
//    RITMO      — ⏰ vencidas → vs el CRONÓMETRO de la Mesa · 📅 citas bajando
//
//  Guarda: solo empresas con Mesa activa.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    private const DIAS_BASE     = 28;
    private const W_RAPIDO      = 50;  // peso de "descarta antes del ciclo"
    private const W_SINCITA     = 40;  // peso de "descarta sin llegar a cita"
    private const W_NOCONTACTO  = 40;  // peso de "no logró contactar"
    private const PROC_ROJO     = 40;  // proceso por debajo → rojo
    private const PROC_VERDE    = 70;  // proceso arriba → sigue el proceso
    private const DUMP_MIN      = 3;   // mínimo de descartes para juzgar
    private const CONTACTO_MIN  = 4;   // mínimo de contactados para juzgar
    private const CERO_MIN      = 8;   // trabajó esto y cerró 0 → alarma

    public static function empresa(int $empresa_id): array
    {
        try {
            if ((int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$empresa_id]) < 1) return [];
        } catch (Throwable $e) { return []; }

        // Ciclo REAL de la empresa (regla de la ventana de cierre).
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
            return $x['proceso'] <=> $y['proceso']; // peor proceso arriba
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

        // ── CONVERSIÓN: cierres / cotizaciones trabajadas (valor directo) ──
        $conv_rate = $trabajo > 0 ? (int)round($cierres / $trabajo * 100) : 0;

        // ── PROCESO: contra las reglas de la Mesa (proporciones propias) ──
        $proceso = 100;
        if ($desc >= self::DUMP_MIN) {
            $proceso -= (int)round($rapido  / $desc * self::W_RAPIDO);   // antes del ciclo
            $proceso -= (int)round($sincita / $desc * self::W_SINCITA);  // sin llegar a cita
        }
        if ($contactados >= self::CONTACTO_MIN) {
            $proceso -= (int)round($no_conecta / $contactados * self::W_NOCONTACTO); // no arrancó
        }
        $proceso = self::_clamp($proceso, 0, 100);

        // ── RITMO: contra el cronómetro de la Mesa ──
        [$venc_now, $venc_sube, $venc_base, $venc_week] = self::_vencidas($empresa_id, $uid);
        [$citas7, $citas_base, $citas_baja] = self::_citas($empresa_id, $uid);

        $cero  = ($cierres === 0 && $trabajo >= self::CERO_MIN); // trabajó harto y cerró 0
        $flag  = ($proceso < self::PROC_ROJO);
        $alerta_ritmo = $venc_sube || $citas_baja;

        if ($flag || ($cero && $proceso < self::PROC_VERDE)) {
            $sem = 'rojo';
        } elseif ($proceso < self::PROC_VERDE || $alerta_ritmo || $cero) {
            $sem = 'amarillo';
        } else {
            $sem = 'verde';
        }

        // ── Textos por pilar (una sola vez; las vistas solo los pintan) ──
        $conv_txt = "cerró {$cierres} de {$trabajo}" . ($trabajo > 0 ? " ({$conv_rate}%)" : "");
        $proc_txt = self::_proc_txt($proceso, $desc, $sincita, $rapido, $no_conecta);
        $ritmo_txt = self::_ritmo_txt($venc_sube, $venc_week, $venc_base, $citas_baja, $citas7, $citas_base);
        $motivo = self::_motivo($sem, $flag, $cero, $proceso, $venc_sube, $citas_baja);

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem,
            'proceso' => $proceso, 'flag' => $flag, 'conv_rate' => $conv_rate,
            'cierres7' => $cierres, 'trabajo7' => $trabajo,
            'desc7' => $desc, 'sincita7' => $sincita, 'rapido7' => $rapido, 'nocontesta7' => $no_conecta,
            'venc_sube' => $venc_sube, 'citas_baja' => $citas_baja,
            'conv_txt' => $conv_txt, 'proc_txt' => $proc_txt, 'ritmo_txt' => $ritmo_txt,
            'motivo' => $motivo,
        ];
    }

    // Texto del pilar Proceso (hechos, no acusa).
    private static function _proc_txt(int $proceso, int $desc, int $sincita, int $rapido, int $no_conecta): string
    {
        if ($proceso >= self::PROC_VERDE) return "✓ sigue el proceso";
        $d = [];
        if ($desc > 0) {
            $sub = [];
            if ($sincita > 0) $sub[] = "{$sincita} sin cita";
            if ($rapido > 0)  $sub[] = "{$rapido} muy rápido";
            $d[] = "descartó {$desc}" . ($sub ? " (" . implode(', ', $sub) . ")" : "");
        }
        if ($no_conecta > 0) $d[] = "no logró contactar a {$no_conecta}";
        return $d ? implode(' · ', $d) : "revisar";
    }

    // Texto del pilar Ritmo (contra el cronómetro).
    private static function _ritmo_txt(bool $venc_sube, int $venc_week, int $venc_base, bool $citas_baja, int $citas7, int $citas_base): string
    {
        if (!$venc_sube && !$citas_baja) return "✓ al día";
        $r = [];
        if ($venc_sube)  $r[] = "⏰ se le vencen seguimientos ({$venc_week} esta semana vs ~{$venc_base})";
        if ($citas_baja) $r[] = "📅 bajó sus citas ({$citas7} vs ~{$citas_base})";
        return implode(' · ', $r);
    }

    private static function _motivo(string $sem, bool $flag, bool $cero, int $proceso, bool $venc_sube, bool $citas_baja): string
    {
        if ($flag) return "No sigue el proceso — que llegue a cita antes de tirar y trabaje sus leads.";
        if ($cero && $sem === 'rojo') return "Trabajó pero no cerró nada y descuida el proceso — revísalo.";
        if ($cero) return "Trabajó bastante pero no ha cerrado — ¿qué lo está frenando?";
        if ($proceso < self::PROC_VERDE) return "Cuida el proceso — que consiga cita antes de descartar.";
        if ($venc_sube) return "Se le está acumulando el seguimiento esta semana — presiónalo.";
        if ($citas_baja) return "Bajó su ritmo de citas — su embudo se está secando.";
        return "Va bien — cierra lo que trabaja y sigue el proceso.";
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

    private static function _clamp(int $v, int $lo, int $hi): int { return max($lo, min($hi, $v)); }
}
