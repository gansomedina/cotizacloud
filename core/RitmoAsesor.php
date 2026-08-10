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
    private const CITAS_BASE_MIN = 2.0; // citas/sem que prueban ritmo (gate de ámbar Y rojo)

    public static function empresa(int $empresa_id): array
    {
        // Memo por request: el termómetro llama expediente() por asesor y cada uno
        // pediría toda la empresa → sin esto sería O(N²).
        static $memo = [];
        if (isset($memo[$empresa_id])) return $memo[$empresa_id];

        try {
            if ((int)DB::val("SELECT mesa_activa FROM empresas WHERE id=?", [$empresa_id]) < 1) return $memo[$empresa_id] = [];
        } catch (Throwable $e) { return $memo[$empresa_id] = []; }

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
        } catch (Throwable $e) { return $memo[$empresa_id] = []; }
        if (!$asesores) return $memo[$empresa_id] = [];

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
        return $memo[$empresa_id] = $filas;
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
        [$venc_cnt, $venc_hoy] = self::_reloj($empresa_id, $uid);
        [$citas7, $citas_base, $citas_baja, $citas_base_wk] = self::_citas($empresa_id, $uid);

        $conv_rate  = $trabajo > 0 ? (int)round($cierres / $trabajo * 100) : 0;
        $r_sincita  = $desc >= self::DUMP_MIN ? $sincita / $desc : 0.0;
        $r_rapido   = $desc >= self::DUMP_MIN ? $rapido / $desc : 0.0;
        $r_noc      = $contactados >= self::CONTACTO_MIN ? $no_conecta / $contactados : 0.0;

        $pct = fn(int $n, int $d): int => $d > 0 ? (int)round($n / $d * 100) : 0;

        // ── PILAR 1: Conversión (resultado directo, sin comparación) ──
        //   Todo sobre el MISMO denominador: cotizaciones trabajadas. Muestra %.
        //   cerró algo → verde ; trabajó y cerró 0 → rojo (0% es lo peor) ;
        //   muestra chica para juzgar → gris (neutral, NO verde/elogio).
        if ($cierres > 0) {
            $conv_estado = 'verde';
            $conv_txt    = "cerró {$cierres} de {$trabajo} ({$conv_rate}%)";
        } elseif ($trabajo >= self::CERO_MIN) {
            $conv_estado = 'rojo';
            $conv_txt    = "cerró 0 de {$trabajo} (0%)";
        } else {
            $conv_estado = 'gris';
            $conv_txt    = $trabajo > 0 ? "cerró 0 de {$trabajo} — muestra chica" : "sin actividad";
        }

        // ── PILAR 2: Descartadas (% de lo trabajado · sin cita · muy rápido) ──
        //   El "vs cierres" ya se ve en Conversión arriba (mismo denominador) →
        //   aquí no se repite "cerró 0". sin-cita/rápido en % de los descartes.
        $dumping = ($desc >= self::DUMP_MIN && $desc > $cierres);
        if ($dumping && ($r_sincita >= 0.6 || $r_rapido >= 0.6))       $desc_estado = 'rojo';
        elseif ($desc >= self::DUMP_MIN && ($r_sincita >= 0.35 || $r_rapido >= 0.3 || $desc > 2 * max($cierres, 1))) $desc_estado = 'amarillo';
        elseif ($desc === 0)                                          $desc_estado = 'gris';
        else                                                          $desc_estado = 'verde';
        $desc_txt = self::_desc_txt($desc_estado, $desc, $sincita, $rapido, $trabajo, $pct);

        // ── PILAR 3: Citas (su ritmo de agendar) — texto FACTUAL, verde = sin alarma ──
        // "en 7 días", no "esta semana": la ventana es RODANTE (NOW() - 7 DAY).
        // CERO ES ROJO (regla CEO): con ritmo probado, no agendar NI UNA en 7
        // días no es "una caída a la mitad", es el embudo parado — mismo criterio
        // que Seguimiento ("una vencida es una vencida"). El ámbar queda para la
        // caída parcial, y siempre se dice contra qué se compara (su promedio).
        $citas_ref = $citas_base >= 1 ? " (~{$citas_base}/sem)" : "";
        $cita_pal  = $citas7 === 1 ? 'cita' : 'citas';
        $citas_estado = self::_citas_semaforo($citas7, $citas_base_wk, $citas_baja);
        if ($citas_estado === 'gris')          $citas_txt = "casi no agenda";
        elseif ($citas_estado === 'rojo')      $citas_txt = "0 citas en 7 días · venía en ~{$citas_base}/sem";
        elseif ($citas_estado === 'amarillo')  $citas_txt = "{$citas7} {$cita_pal} en 7 días · venía en ~{$citas_base}/sem";
        else                                   $citas_txt = "{$citas7} {$cita_pal} en 7 días{$citas_ref}";

        // ── PILAR 4: Seguimiento — el RELOJ de la Mesa (mismo que ve el asesor).
        //   Una vencida es una vencida (sin histórico): tiene vencidas → rojo.
        //   Solo "vence hoy" (esperó al último) → amarillo. Ninguna cerca → verde.
        if ($venc_cnt > 0) {
            $venc_estado = 'rojo';
            $venc_txt    = "{$venc_cnt} vencidas" . ($venc_hoy > 0 ? " · {$venc_hoy} vencen hoy" : "");
        } elseif ($venc_hoy > 0) {
            $venc_estado = 'amarillo';
            $venc_txt    = "{$venc_hoy} vencen hoy (esperó al último)";
        } else {
            $venc_estado = 'verde';
            $venc_txt    = "al día (0 vencidas)";
        }

        // ── PILAR 5: Contacto — de los que intentó contactar, cuántos NO LE
        //   CONTESTARON (nunca llegó a "hablamos"). MISMO marco para todos (el
        //   verde solo significa que el % es bajo, no cambia el texto).
        if ($contactados < self::CONTACTO_MIN) {
            $cont_estado = 'gris';
            $cont_txt    = "pocos contactos aún";
        } else {
            $cont_txt = "{$no_conecta} de {$contactados} no le contestaron (" . $pct($no_conecta, $contactados) . "%)";
            if ($r_noc >= 0.6)      $cont_estado = 'rojo';
            elseif ($r_noc >= 0.35) $cont_estado = 'amarillo';
            else                    $cont_estado = 'verde';
        }

        // Semáforo del asesor = el PEOR de sus 5 pilares (gris/verde = sin alarma).
        $labels  = ['Conversión', 'Descartadas', 'Citas', 'Seguimiento', 'Contacto'];
        $estados = [$conv_estado, $desc_estado, $citas_estado, $venc_estado, $cont_estado];
        $ord = ['gris' => 0, 'verde' => 0, 'amarillo' => 1, 'rojo' => 2];
        $sem = 'verde';
        foreach ($estados as $st) if (($ord[$st] ?? 0) > $ord[$sem]) $sem = $st;
        $problemas = count(array_filter($estados, fn($s) => $s === 'amarillo' || $s === 'rojo'));
        $flag = ($sem === 'rojo');
        // Badge: "no sigue el proceso" + el/los pilar(es) en rojo (accionable).
        $rojos = [];
        foreach ($estados as $i => $st) if ($st === 'rojo') $rojos[] = $labels[$i];
        $flag_pilares = implode(', ', $rojos);

        $motivo = self::_motivo($conv_estado, $desc_estado, $cont_estado, $venc_estado, $citas_baja, $citas_estado);

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem, 'flag' => $flag, 'problemas' => $problemas,
            'flag_pilares' => $flag_pilares,
            'conv_estado' => $conv_estado, 'conv_txt' => $conv_txt,
            'desc_estado' => $desc_estado, 'desc_txt' => $desc_txt,
            'citas_estado' => $citas_estado, 'citas_txt' => $citas_txt,
            'venc_estado' => $venc_estado, 'venc_txt' => $venc_txt,
            'cont_estado' => $cont_estado, 'cont_txt' => $cont_txt,
            // Crudos de los pilares: quien construya un texto encima (tips,
            // reporte) usa ESTOS y no puede contradecir al pilar. Sin queries
            // extra — ya están calculados arriba.
            'n_trabajo' => $trabajo, 'n_cierres' => $cierres,
            'n_desc' => $desc, 'n_sincita' => $sincita, 'n_rapido' => $rapido,
            'n_contactados' => $contactados, 'n_noc' => $no_conecta,
            'n_venc' => $venc_cnt, 'n_venc_hoy' => $venc_hoy,
            'n_citas7' => $citas7, 'n_citas_base' => $citas_base,
            'motivo' => $motivo,
        ];
    }

    private static function _desc_txt(string $estado, int $desc, int $sincita, int $rapido, int $trabajo, callable $pct): string
    {
        if ($estado === 'gris') return "0 descartes";
        // El desglose (# sin cita, # muy rápido) se muestra SIEMPRE que haya
        //   descartes — también en verde: sin cita es el dato clave, no se oculta.
        $sub = [];
        if ($sincita > 0) $sub[] = "{$sincita} sin cita";
        if ($rapido > 0)  $sub[] = "{$rapido} muy rápido";
        return "descartó {$desc} de {$trabajo} (" . $pct($desc, $trabajo) . "%)"
             . ($sub ? " · " . implode(' · ', $sub) : "");
    }

    /**
     * Semáforo del pilar Citas. Puro (sin BD) a propósito: la tabla de verdad se
     * verifica en tools/test_citas_semaforo.php.
     *   gris     = nunca agenda (no hay ritmo que juzgar)
     *   rojo     = tenía ritmo probado y NO agendó NI UNA en 7 días (embudo parado)
     *   amarillo = cayó a menos de la mitad de su propio ritmo, pero agendó algo
     *   verde    = sin alarma
     * El rojo es un SUBCONJUNTO del ámbar de antes (mismo gate de ritmo probado:
     * base >= 2/sem) — no aparecen alertas nuevas, solo sube la severidad del cero.
     */
    private static function _citas_semaforo(int $c7, float $base_wk, bool $baja): string
    {
        if ($base_wk < 1.0 && $c7 < 1)          return 'gris';
        if ($c7 === 0 && $base_wk >= self::CITAS_BASE_MIN) return 'rojo';
        return $baja ? 'amarillo' : 'verde';
    }

    private static function _motivo(string $conv_estado, string $desc_estado, string $cont_estado, string $venc_estado, bool $citas_baja, string $citas_estado = ''): string
    {
        if ($desc_estado === 'rojo') return "Descarta mal — sin llegar a cita y muy rápido. Que trabaje el lead antes de tirarlo.";
        if ($venc_estado === 'rojo') return "Trae seguimientos VENCIDOS — inexcusable. Que se ponga al día hoy mismo.";
        if ($cont_estado === 'rojo') return "A la mayoría no le contestan — revisa cómo, cuándo y por qué medio les marca.";
        if ($conv_estado === 'rojo') return "Trabajó varias y no ha cerrado nada — ¿qué lo está frenando?";
        if ($citas_estado === 'rojo') return "No agendó NI UNA cita en 7 días y venía con ritmo — su embudo se paró.";
        if ($desc_estado === 'amarillo') return "Cuida sus descartes — que llegue a cita antes de tirar.";
        if ($cont_estado === 'amarillo') return "A varios no le contestan — ajusta su forma de contactar (horario/medio/insistencia).";
        if ($venc_estado === 'amarillo') return "Trae seguimientos al límite (vencen hoy) — que no los deje caer.";
        if ($citas_baja) return "Bajó su ritmo de citas en los últimos 7 días — su embudo se está secando.";
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

    /**
     * Reloj de seguimiento — MISMO que ve el asesor en su Mesa. Read-only
     * (Mesa::armar en modo solo_lectura no escribe mesa_vencidos). Devuelve
     * [vencidas, vence_hoy] del estado ACTUAL del reloj, sin histórico.
     */
    private static function _reloj(int $empresa_id, int $uid): array
    {
        try {
            $r = Mesa::armar($empresa_id, $uid, true)['resumen'] ?? [];
            return [(int)($r['vencidas'] ?? 0), (int)($r['vence_hoy'] ?? 0)];
        } catch (Throwable $e) { return [0, 0]; }
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
        } catch (Throwable $e) { return [0, 0, false, 0.0]; }
        $base_wk = $c28 / 4.0;
        $baja = ($base_wk >= self::CITAS_BASE_MIN) && ($c7 < $base_wk * 0.5);
        // base_wk CRUDO además del redondeado: el semáforo compara contra el
        // float (1.75 no es 2), el texto muestra el entero.
        return [$c7, (int)round($base_wk), $baja, $base_wk];
    }
}
