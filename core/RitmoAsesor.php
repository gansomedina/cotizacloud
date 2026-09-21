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
    private const DUMP_VOLUMEN  = 0.25; // tirar >= 1/4 de la cartera para el ROJO
    private const HIST_MIN       = 8;   // cohorte histórica mínima para juzgar
    private const CITAS_BASE_MIN = 2.0; // citas/sem que prueban ritmo (gate SOLO del ámbar; el rojo del cero no tiene gate)

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
        // Denominador: cotizaciones del período que el cliente abrió.
        $vistas = 0;
        try { $vistas = self::_abiertas($empresa_id, $uid, $win); } catch (Throwable $e) {}
        // VARA: la tasa histórica de la empresa que YA calcula el termómetro
        // (ActividadScore::close_rate_historico). Estaba escrita, documentada
        // como "para que la tarjeta use EXACTAMENTE la misma y no invente la
        // suya"... y sin un solo llamador. Así el asesor se mide contra la misma
        // vara en las dos pantallas. Se midió contra la cohorte que se usaba
        // antes: 19% vs 18% en Hermosillo y 22% vs 22% en Nogales — el cambio de
        // vara no mueve nada, es el numerador el que estaba mal.
        $hist_r = 0.0; $hist_n = 0;
        try {
            $cr = ActividadScore::close_rate_historico($empresa_id);
            $hist_r = (float)($cr['rate'] ?? 0);
            $hist_n = (int)($cr['muestra'] ?? 0);
        } catch (Throwable $e) {}
        $hist_ok = $hist_n >= self::HIST_MIN && $hist_r > 0;
        [$venc_cnt, $venc_hoy] = self::_reloj($empresa_id, $uid);
        [$citas7, $citas_base, $citas_baja, $citas_base_wk] = self::_citas($empresa_id, $uid);

        $r_sincita  = $desc >= self::DUMP_MIN ? $sincita / $desc : 0.0;
        $r_rapido   = $desc >= self::DUMP_MIN ? $rapido / $desc : 0.0;
        $r_noc      = $contactados >= self::CONTACTO_MIN ? $no_conecta / $contactados : 0.0;

        $pct = fn(int $n, int $d): int => $d > 0 ? (int)round($n / $d * 100) : 0;

        // ── PILAR 1: Conversión — la definición de la CASA ──
        //   Cierres del período ÷ cotizaciones del período. Es la misma cuenta
        //   que hacen los otros cinco lugares que miden lo mismo (el termómetro
        //   en ActividadScore:578, el dashboard, reportes y el ejecutivo);
        //   la tarjeta era la única que se había salido.
        //
        //   QUÉ ESTABA ROTO (21-sep): imprimía el numerador de un lado y el
        //   porcentaje del otro. Con datos reales — Abigail "cerró 7 ... (11%)"
        //   donde el 11% lo producía un 2; Kevin "cerró 2 ... (7%)" donde el 7%
        //   lo producía un 1. El número impreso YA era el correcto; lo que salía
        //   de otra cuenta, más chica, era el porcentaje. Subcalificaba a los
        //   tres asesores y a uno le ponía un rojo que no le tocaba.
        //
        //   TOPE 100%: el numerador cuenta por accion_at y el denominador por
        //   created_at, así que cerrar backlog puede dar más cierres que
        //   cotizaciones nuevas. El motor ya vive con eso y lo topa igual
        //   (ActividadScore:578). Por eso el texto NO dice "X de Y": "cerró 3 de
        //   2" es imposible de escribir, "cerró 3 · abrió 2" no.
        $conv_rate_r = $vistas > 0 ? min($cierres / $vistas, 1.0) : 0.0;
        $conv_rate   = (int)round($conv_rate_r * 100);
        $hist_pct    = (int)round($hist_r * 100);
        $vs          = $hist_ok ? " · la empresa {$hist_pct}%" : "";
        $cuenta      = "cerró {$cierres} · abrió {$vistas}";

        if ($vistas < self::CERO_MIN) {
            // Muestra chica: no se juzga a nadie con 3 cotizaciones.
            $conv_estado = 'gris';
            if ($vistas > 0)        $conv_txt = "{$cuenta} — muestra chica";
            elseif ($cierres > 0)   $conv_txt = "cerró {$cierres} · sin cotizaciones nuevas";
            else                    $conv_txt = "sin actividad";
        } elseif ($hist_ok) {
            // REGLA CEO (11-ago): el color lo decide la comparación contra la
            // tasa HISTÓRICA de su propia empresa, no "cerró algo = verde".
            // Antes, 1 de 24 (4%) salía VERDE con el motivo "va bien".
            //   >= histórico          → verde
            //   >= mitad del histórico→ amarillo
            //   <  mitad              → rojo
            if     ($conv_rate_r >= $hist_r)       $conv_estado = 'verde';
            elseif ($conv_rate_r >= $hist_r * 0.5) $conv_estado = 'amarillo';
            else                                   $conv_estado = 'rojo';
            $conv_txt = "{$cuenta} · {$conv_rate}%{$vs}";
        } else {
            // Empresa sin historial suficiente: se conserva la regla vieja
            // (binaria) para no inventar un veredicto sin vara.
            $conv_estado = $cierres > 0 ? 'verde' : 'rojo';
            $conv_txt    = "{$cuenta} · {$conv_rate}%";
        }

        // ── PILAR 2: Descartadas (% de lo trabajado · sin cita · muy rápido) ──
        //   OJO: el denominador es TRABAJADAS (taps de Mesa en la ventana), que
        //   NO es el de Conversión (cotizaciones nacidas en la ventana). Por eso
        //   el texto lo dice con todas sus letras — sin el sustantivo, el lector
        //   asumía que era el mismo número de la línea de arriba.
        //
        //   EL ROJO EXIGE LAS DOS COSAS (regla CEO): mala proporción Y volumen.
        //   Antes solo miraba la proporción interna de los descartes, así que
        //   Abigail (4 descartes, 3 sin cita = 75%) recibía el MISMO rojo y el
        //   MISMO consejo que Manuel (24 descartes, 22 sin cita = 92%) — aunque
        //   ella tira el 13% de su cartera y él el 63%. Con el badge diciendo lo
        //   mismo para los dos, dejaba de servir para saber a quién sentar.
        $r_volumen = $trabajo > 0 ? $desc / $trabajo : 0.0;
        $dumping = ($desc >= self::DUMP_MIN && $desc > $cierres
                    && $r_volumen >= self::DUMP_VOLUMEN);
        if ($dumping && ($r_sincita >= 0.6 || $r_rapido >= 0.6))       $desc_estado = 'rojo';
        elseif ($desc >= self::DUMP_MIN && ($r_sincita >= 0.35 || $r_rapido >= 0.3 || $desc > 2 * max($cierres, 1))) $desc_estado = 'amarillo';
        elseif ($desc === 0)                                          $desc_estado = 'gris';
        else                                                          $desc_estado = 'verde';
        $desc_txt = self::_desc_txt($desc_estado, $desc, $sincita, $rapido, $trabajo, $pct);

        // ── PILAR 3: Citas (su ritmo de agendar) — texto FACTUAL, verde = sin alarma ──
        // "en 7 días", no "esta semana": la ventana es RODANTE (NOW() - 7 DAY).
        // CERO ES ROJO SIEMPRE (regla CEO): no agendar NI UNA en 7 días es el
        // embudo parado, sin importar su historia — mismo criterio que Seguimiento
        // ("una vencida es una vencida"). El ámbar queda para la caída parcial.
        $citas_ref = $citas_base >= 1 ? " (~{$citas_base}/sem)" : "";
        $cita_pal  = $citas7 === 1 ? 'cita' : 'citas';
        $citas_28  = (int)round($citas_base_wk * 4);   // total de la base (28 días)
        $citas_estado = self::_citas_semaforo($citas7, $citas_base_wk, $citas_baja);
        if ($citas_estado === 'rojo') {
            // El cero manda, pero el contexto se dice con la verdad de cada caso:
            // el que venía con ritmo, el que agendaba poco y el que nunca agenda.
            if ($citas_28 === 0)        $citas_txt = "0 citas en 7 días · ninguna en 28 días";
            elseif ($citas_base >= 1)   $citas_txt = "0 citas en 7 días · venía en ~{$citas_base}/sem";
            else                        $citas_txt = "0 citas en 7 días · solo {$citas_28} en 28 días";
        }
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
            'n_trabajo' => $trabajo, 'n_cierres' => $cierres, 'n_vistas' => $vistas,
            'n_conv_pct' => $conv_rate, 'n_hist_pct' => $hist_pct,
            'n_desc' => $desc, 'n_sincita' => $sincita, 'n_rapido' => $rapido,
            'n_contactados' => $contactados, 'n_noc' => $no_conecta,
            'n_venc' => $venc_cnt, 'n_venc_hoy' => $venc_hoy,
            'n_citas7' => $citas7, 'n_citas_base' => $citas_base, 'n_citas28' => $citas_28,
            'motivo' => $motivo,
        ];
    }

    private static function _desc_txt(string $estado, int $desc, int $sincita, int $rapido, int $trabajo, callable $pct): string
    {
        if ($estado === 'gris') return "0 descartes";
        // El desglose (# sin cita, # muy rápido) se muestra SIEMPRE que haya
        //   descartes — también en verde: sin cita es el dato clave, no se oculta.
        // El % es sobre los DESCARTES (no sobre trabajadas): es exactamente el
        // número que decide el color (>=60% sin cita o muy rápido → rojo).
        // Sin él, el lector veía "21 sin cita" y no podía saber si eso era
        // mucho o poco sobre 23 descartes.
        $sub = [];
        if ($sincita > 0) $sub[] = "{$sincita} sin cita (" . $pct($sincita, $desc) . "%)";
        if ($rapido > 0)  $sub[] = "{$rapido} muy rápido (" . $pct($rapido, $desc) . "%)";
        return "descartó {$desc} de {$trabajo} trabajadas (" . $pct($desc, $trabajo) . "%)"
             . ($sub ? " · " . implode(' · ', $sub) : "");
    }

    /**
     * Semáforo del pilar Citas. Puro (sin BD) a propósito: la tabla de verdad se
     * verifica en tools/test_citas_semaforo.php.
     *   rojo     = NO agendó NI UNA en 7 días. SIEMPRE, sin importar su historia
     *              (decisión CEO): cero es cero. Sin citas no hay embudo, y da
     *              igual si antes agendaba mucho, poco o nunca.
     *   amarillo = agendó algo, pero cayó a menos de la mitad de su propio ritmo
     *   verde    = sin alarma
     * NO hay 'gris' en este pilar: el "casi no agenda" de antes también es cero.
     */
    private static function _citas_semaforo(int $c7, float $base_wk, bool $baja): string
    {
        if ($c7 === 0) return 'rojo';
        return $baja ? 'amarillo' : 'verde';
    }

    private static function _motivo(string $conv_estado, string $desc_estado, string $cont_estado, string $venc_estado, bool $citas_baja, string $citas_estado = ''): string
    {
        if ($desc_estado === 'rojo') return "Descarta mal — sin llegar a cita y muy rápido. Que trabaje el lead antes de tirarlo.";
        if ($venc_estado === 'rojo') return "Trae seguimientos VENCIDOS — inexcusable. Que se ponga al día hoy mismo.";
        if ($cont_estado === 'rojo') return "A la mayoría no le contestan — revisa cómo, cuándo y por qué medio les marca.";
        if ($conv_estado === 'rojo') return "Trabajó varias y no ha cerrado nada — ¿qué lo está frenando?";
        if ($citas_estado === 'rojo') return "No agendó NI UNA cita en 7 días — sin citas no hay embudo.";
        if ($desc_estado === 'amarillo') return "Cuida sus descartes — que llegue a cita antes de tirar.";
        if ($cont_estado === 'amarillo') return "A varios no le contestan — ajusta su forma de contactar (horario/medio/insistencia).";
        if ($venc_estado === 'amarillo') return "Trae seguimientos al límite (vencen hoy) — que no los deje caer.";
        if ($citas_baja) return "Bajó su ritmo de citas en los últimos 7 días — su embudo se está secando.";
        return "Va bien — cierra, descarta sano y da seguimiento.";
    }

    /**
     * CIERRES del período — la receta de la CASA, copiada de ActividadScore:347.
     * Cotizaciones que el cliente ACEPTÓ dentro de la ventana (accion_at), con
     * anticipo cobrado, descontando las que cerró el Descuento Inteligente (esas
     * son de la empresa, no del asesor).
     *
     * "Venta del período es venta del período, sin importar cuándo nació la
     * cotización" (CEO, 21-sep). Antes contaba filas de `ventas` por
     * `ventas.created_at`: en los datos reales daba el MISMO número (7, 3 y 2
     * para los tres asesores de OnTime), pero eran dos relojes distintos para el
     * mismo hecho, y solo uno de los dos coincidía con el score que califica al
     * mismo asesor. Los filtros van idénticos al motor a propósito — el punto de
     * todo esto es que no haya dos definiciones de "cierre".
     */
    private static function _cierres(int $empresa_id, ?int $uid, int $dias): int
    {
        $w = $uid !== null ? "AND COALESCE(c.vendedor_id, c.usuario_id) = ?" : "";
        $p = [$empresa_id]; if ($uid !== null) $p[] = $uid;
        return (int) DB::val(
            "SELECT COUNT(*) FROM cotizaciones c
              WHERE c.empresa_id = ?
                AND c.estado IN ('aceptada','convertida','aceptada_cliente')
                AND c.accion_at >= NOW() - INTERVAL $dias DAY
                AND EXISTS (SELECT 1 FROM ventas v WHERE v.cotizacion_id = c.id
                            AND v.pagado > 0 AND v.estado <> 'cancelada')
                AND NOT EXISTS (SELECT 1 FROM desc_int_activaciones di
                                WHERE di.cotizacion_id = c.id AND di.estado = 'utilizado')
                $w", $p
        );
    }

    /**
     * ABIERTAS del período — el denominador de la CASA (ActividadScore:304, lo
     * que el motor llama `cot_vistas`): cotizaciones nacidas en la ventana que
     * el cliente abrió o que ya tienen acción.
     *
     * NO lleva filtro de madurez. Se midió: excluir las de menos de la mediana
     * del ciclo mueve la tasa entre 0 y 4 puntos y NO cambia el color de ningún
     * asesor — no vale mantener una segunda receta por eso.
     */
    private static function _abiertas(int $empresa_id, ?int $uid, int $dias): int
    {
        $w = $uid !== null ? "AND COALESCE(c.vendedor_id, c.usuario_id) = ?" : "";
        $p = [$empresa_id]; if ($uid !== null) $p[] = $uid;
        return (int) DB::val(
            "SELECT COUNT(*) FROM cotizaciones c
              WHERE c.empresa_id = ? AND c.total > 0 AND c.suspendida = 0
                AND (c.estado IN ('vista','aceptada','convertida','aceptada_cliente') OR c.visitas > 0)
                AND c.created_at >= NOW() - INTERVAL $dias DAY $w", $p
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
                   -- DESCARTADA VIGENTE (misma regla que Mesa::armar y
                   -- Mesa::reporte): la ÚLTIMA postura debe seguir siendo
                   -- 'descartada' y no puede haber un 👍 posterior que la
                   -- anule. mesa_estados es insert-only: sin esto, un
                   -- descarte CORREGIDO —o una descartada que revivió y se
                   -- VENDIÓ— seguía contando como descarte y pintaba al
                   -- asesor rojo mientras la Mesa la celebraba como
                   -- recuperada.
                   AND m.id = (SELECT MAX(mv.id) FROM mesa_estados mv
                                WHERE mv.cotizacion_id = m.cotizacion_id AND mv.area = 'postura')
                   AND NOT EXISTS (SELECT 1 FROM mesa_estados mfv
                                    WHERE mfv.cotizacion_id = m.cotizacion_id
                                      AND mfv.area = 'feedback' AND mfv.estado = 'con_interes'
                                      AND mfv.id > m.id)
                   AND NOT EXISTS (SELECT 1 FROM radar_feedback rfv
                                    WHERE rfv.cotizacion_id = m.cotizacion_id
                                      AND rfv.usuario_id = COALESCE(c.vendedor_id, c.usuario_id)
                                      AND rfv.tipo = 'con_interes' AND rfv.updated_at > m.created_at)

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
