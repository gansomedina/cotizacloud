<?php
// ============================================================
//  core/DiagnosticoTips.php
//  Motor de "tips" del termómetro en voz de gerente comercial.
//
//  Filosofía: NO listar stats. Diagnosticar la FUGA #1 del embudo,
//  decir a QUIÉN atender con datos reales, dar UNA acción, cerrar con
//  consecuencia según el tier de score.
//
//  Consistencia: todo se compone de PIEZAS vetadas (opener/weapon/
//  action/close) + helper de plurales. Nunca strings sueltos por
//  combinación.
//
//  Variedad: multiplicativa por EJES independientes:
//    esqueleto (forma) × ángulo × pools × datos vivos × rotación diaria.
//  La rotación usa (usuario_id + día del año) para que un lector diario
//  no cruce el espacio en meses.
//
//  PURO: no toca BD. Recibe el array de score ($s) y el contexto ($ctx),
//  igual que ActividadScore::diagnostico(). Testeable en aislamiento.
// ============================================================

defined('COTIZAAPP') or die;

final class DiagnosticoTips
{
    // ── Helpers de composición ──────────────────────────────

    /** Plural correcto: _pl(1,'venta','ventas') = "1 venta"; _pl(2,...) = "2 ventas" */
    private static function _pl(int $n, string $sing, string $plur): string
    {
        return $n . ' ' . ($n === 1 ? $sing : $plur);
    }

    /** Elige de un pool por semilla (rota estable por usuario+día). */
    private static function _pick(array $pool, int $seed): string
    {
        if (empty($pool)) return '';
        return $pool[$seed % count($pool)];
    }

    /** Primera letra mayúscula (multibyte). */
    private static function _cap(string $s): string
    {
        return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
    }

    // ── Entrada ─────────────────────────────────────────────

    public static function build(array $s, ?array $ctx = null): string
    {
        $m = self::_metricas($s, $ctx);

        if (($s['nivel'] ?? '') === 'nuevo') {
            return 'Recopilando información — tu score se está activando. Sigue cotizando y dando seguimiento; en unos días el termómetro empieza a leerte.';
        }
        if ($m['asig'] === 0) {
            return 'No cotizaste en el período. Sin cotizaciones el sistema no tiene nada que medir — y tú no tienes de dónde sacar ventas. Manda propuestas hoy.';
        }

        $fuga = self::_detectar_fuga($m);

        // Semilla: rota por usuario y por día del año.
        $seed = ((int)($s['usuario_id'] ?? 0)) + (int)date('z');

        switch ($fuga) {
            case 'cierre_bajo':   return self::_fuga_cierre_bajo($m, $seed);
            case 'top':           return self::_fuga_top($m, $seed);
            default:              return self::_fuga_cierre_bajo($m, $seed); // fallback fase 1
        }
    }

    // ── Extracción de métricas (mismas claves que diagnostico()) ──

    private static function _metricas(array $s, ?array $ctx): array
    {
        $vist    = (int)($s['cot_vistas'] ?? 0);
        $cierres = (int)($s['conversiones'] ?? 0);
        $bench   = (float)($ctx['close_rate'] ?? 0.10);
        $tasa    = $vist > 0 ? $cierres / $vist : 0.0;
        $cal     = (int)($s['cots_calientes'] ?? $s['radar_benchmark'] ?? 0);
        $fb      = (int)($s['fb_total'] ?? $s['radar_views'] ?? 0);
        return [
            'score'      => (int)($s['score'] ?? 0),
            'asig'       => (int)($s['cot_asignadas'] ?? 0),
            'vist'       => $vist,
            'cierres'    => $cierres,
            'sin_cerrar' => max(0, $vist - $cierres),
            'dorm'       => (int)($s['cot_dormidas'] ?? 0),
            'nab'        => (int)($s['no_abiertas_5d'] ?? 0),
            'sin_abrir'  => max(0, (int)($s['cot_asignadas'] ?? 0) - $vist),
            'ign'        => max(0, $cal - $fb),          // calientes sin atender
            'vsp'        => (int)($s['ventas_sin_pago'] ?? 0),
            'mom'        => (float)($s['momentum'] ?? 1),
            'bench'      => $bench,
            'bench_pct'  => (int)round($bench * 100),
            'tasa'       => $tasa,
            'tasa_pct'   => (int)round($tasa * 100),
            'bticket'    => (int)($s['bonus_ticket'] ?? 0),      // vendió ticket alto
            'bcierre'    => (int)($s['bonus_cierre'] ?? 0),      // cierre sobre histórico
        ];
    }

    // ── Detector de fuga #1 (triage de gerente) ─────────────
    // Orden = de lo más urgente/valioso a lo menos. La primera que aplica manda.

    private static function _detectar_fuga(array $m): string
    {
        if ($m['score'] >= 80 || $m['bcierre'] >= 4) return 'top';
        // Cotiza y le abren, pero no cierra (el caso clásico del 55)
        if ($m['vist'] >= 8 && $m['tasa'] < $m['bench'] * 0.7) return 'cierre_bajo';
        return 'cierre_bajo'; // fase 1: resto cae aquí; fase 2 agrega apertura/dormidas/cobro/etc.
    }

    // ════════════════════════════════════════════════════════
    //  FUGA: CIERRE BAJO  (cotiza mucho, cierra poco)
    //  Tono: duro (tier bajo/regular). Diagnóstico → arma → a quién → cierre.
    // ════════════════════════════════════════════════════════

    private static function _fuga_cierre_bajo(array $m, int $seed): string
    {
        $partes = [];

        // ── OPENER: 6 esqueletos/ángulos distintos (varía la FORMA, no solo palabras) ──
        $v = $m['vist']; $c = $m['cierres']; $bp = $m['bench_pct']; $tp = $m['tasa_pct'];
        $sc = $m['sin_cerrar'];
        $openers = [
            // por el número, seco
            "{$c} de {$v}. Esa es tu tasa de cierre este período — " . self::_pl($tp, 'punto', 'puntos') . " contra los {$bp} que hace la empresa. No te faltan clientes; te falta rematar.",
            // por contraste (volumen vs cierre)
            "Cotizas de sobra y no cierras — ahí está todo tu problema. {$v} clientes vieron tu propuesta y solo " . self::_pl($c, 'compró', 'compraron') . ". El trabajo no es mandar más, es cerrar lo que ya mandaste.",
            // por pregunta
            "¿{$v} propuestas y " . ($c === 1 ? 'una sola venta' : "solo {$c} ventas") . "? Ese no es problema de suerte ni de precio: es de seguimiento. La empresa cierra {$bp}% y tú {$tp}%.",
            // por la fuga concreta
            "{$sc} clientes abrieron tu cotización y se fueron sin comprar. Cuando se te escapan tantos con interés, casi siempre es una de dos: no diste seguimiento, o no manejaste la objeción.",
            // por la consecuencia arreglable
            "Tu cierre está en {$tp}% — la mitad de lo que necesita la empresa. La buena noticia: con {$v} propuestas abiertas, el material para vender lo tienes. Falta convertirlo.",
            // por el diagnóstico directo
            "Estás llenando el embudo pero se vacía antes de cerrar. " . self::_pl($c, 'venta', 'ventas') . " de {$v} vistas. El cuello está en el remate, no en el volumen.",
        ];
        $partes[] = self::_pick($openers, $seed);

        // ── ARMA: usa el positivo (ticket alto) para AFILAR, no para elogiar ──
        if ($m['bticket'] >= 5) {
            $armas = [
                "Y ojo: cuando cierras, cierras grande — tu venta fue muy por encima del ticket promedio. O sea, sí sabes vender. Lo que no estás haciendo es dar seguimiento.",
                "No es que no sepas vender: tu último cierre fue de los grandes, arriba del ticket promedio. El problema no es tu pitch, es que sueltas a los demás.",
                "Tienes con qué — cerraste una venta de ticket alto. Si le dieras seguimiento al resto como a esa, otra sería la historia.",
            ];
            $partes[] = self::_pick($armas, $seed + 3);
        }

        // ── ACCIÓN: a QUIÉN atender, con datos reales, priorizando lo más caliente ──
        $acc = [];
        if ($m['ign'] > 0) {
            $n = $m['ign'];
            $pool = [
                "Arranca por lo caliente: " . self::_pl($n, 'cliente está activo', 'clientes están activos') . " en el Radar ahora mismo — " . ($n === 1 ? 'volvió' : 'volvieron') . ", " . ($n === 1 ? 'revisó' : 'revisaron') . " el precio, " . ($n === 1 ? 'está' : 'están') . " a un paso. No los has tocado. Esa es tu venta de hoy: llámalos antes de comer.",
                "El Radar te está gritando y no lo oyes: " . self::_pl($n, 'cliente en modo compra', 'clientes en modo compra') . " sin que los atiendas. Suéltalo todo y márcalos ya — es lo más cercano a una venta que tienes.",
                "Lo primero, sin excusas: " . self::_pl($n, 'oportunidad caliente', 'oportunidades calientes') . " en el Radar esperándote. Cada hora que pasa baja la probabilidad. Hoy, no mañana.",
            ];
            $acc[] = self::_pick($pool, $seed + 5);
        }
        if ($m['dorm'] > 0) {
            $d = $m['dorm'];
            $pool = [
                "Luego ve por " . ($d === 1 ? 'el cliente que miró y desapareció' : "los {$d} que miraron y desaparecieron") . ": un mensaje hoy los revive antes de que se enfríen del todo.",
                "Después, " . ($d === 1 ? 'el cliente dormido' : "los {$d} dormidos") . " — abrieron y no volviste a existir para ellos. Reactívalos uno por uno, no en bloque.",
                "Y no dejes morir a " . ($d === 1 ? 'ese cliente' : "esos {$d} clientes") . " que vieron y llevan una semana sin saber de ti. Eso es dinero enfriándose sobre la mesa.",
            ];
            $acc[] = self::_pick($pool, $seed + 7);
        }
        if (empty($acc)) {
            $acc[] = self::_pick([
                "Deja de perseguir clientes nuevos y trabaja los que ya levantaron la mano: revisa el Radar y ataca lo que tiene movimiento.",
                "Enfócate en seguimiento: entra al Radar, ve quién sigue con actividad y ciérralo. Ahí están tus ventas, no en cotizar más.",
            ], $seed + 5);
        }
        $partes[] = implode(' ', $acc);

        // ── CIERRE: consecuencia dura, UNA sola (tier bajo) ──
        $cierre = [
            "Si no cambias el seguimiento, cotizar más solo te va a cansar. Esto se mueve ya.",
            "El volumen sin cierre no sirve. Arregla el remate esta semana — los números tienen que subir.",
            "Así como vas, el mes no cierra. Y es 100% arreglable: es seguimiento, no talento. Manos a la obra.",
            "Cada cliente que dejas sin llamar es una venta que le regalas a la competencia. Deja de regalarlas.",
            "Tienes el material; falta la disciplina de seguimiento. Empieza hoy y en dos semanas se nota.",
        ];
        $partes[] = self::_pick($cierre, $seed + 11);

        return implode(' ', $partes);
    }

    // ════════════════════════════════════════════════════════
    //  FUGA: TOP  (score alto / cierra sobre su histórico)
    //  Tono: reconocimiento + reto, sin aflojar.
    // ════════════════════════════════════════════════════════

    private static function _fuga_top(array $m, int $seed): string
    {
        $partes = [];
        $openers = [
            "Vas volando. Cierras muy por encima de lo que esperaría la empresa de ti — eres de los que mueven el número.",
            "Mes de los buenos: tu cierre está claramente arriba del promedio. Así se ve un vendedor que domina su embudo.",
            "Estás en tu mejor forma. Los resultados hablan solos y jalan al equipo hacia arriba.",
            "Nivel top. No solo vendes: vendes bien y consistente. Eso es lo que separa al profesional del que tiene suerte.",
        ];
        $partes[] = self::_pick($openers, $seed);

        $reto = [];
        if ($m['ign'] > 0) {
            $reto[] = self::_pick([
                "No te relajes: aún tienes " . self::_pl($m['ign'], 'cliente caliente', 'clientes calientes') . " en el Radar sin atender. Ciérralos y el mes es histórico.",
                "Un detalle para ser perfecto: " . self::_pl($m['ign'], 'oportunidad', 'oportunidades') . " activa" . ($m['ign'] === 1 ? '' : 's') . " en el Radar esperándote. No dejes ese dinero en la mesa.",
            ], $seed + 4);
        } else {
            $reto[] = self::_pick([
                "El siguiente nivel: sostén el ritmo y comparte con el equipo qué estás haciendo distinto. Un líder multiplica, no solo produce.",
                "Mantén la disciplina que te trajo aquí y ayuda a subir a los que van atrás — eso te hace referente, no solo buen vendedor.",
                "Ya dominas tu juego; ahora vuélvelo repetible: documenta tu seguimiento para que sea tu estándar, no tu buen mes.",
            ], $seed + 4);
        }
        $partes[] = implode(' ', $reto);

        return implode(' ', $partes);
    }
}
