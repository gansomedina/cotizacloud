<?php
// ============================================================
//  CotizaApp — core/ActividadScore.php  v1.0
//  Motor APC (Auto-adjusting Productivity Composite)
//
//  3 dimensiones:
//    Presencia  (15%) — días activos / días laborales
//    Gestión    (55%) — acciones / carga activa / semanas
//    Conversión (30%) — cotizaciones cerradas / carga activa
//
//  3 perspectivas auto-ajustables:
//    Proporcional a carga (50%) — normalizado por volumen
//    Momentum vs EMA      (25%) — comparación contra sí mismo
//    Percentil en equipo  (25%) — posición relativa
//
//  α EMA = 0.3 (30% dato reciente, 70% historia)
// ============================================================

class ActividadScore
{
    private const EMA_ALPHA   = 0.3;
    private const PERIODO     = 30;   // días rolling
    private const DIAS_SEMANA = 5;    // días laborales por semana

    // ─── Registrar actividad ──────────────────────────────
    public static function registrar(int $usuario_id, int $empresa_id, string $tipo, ?int $ref_id = null): void
    {
        // Throttle: no registrar la misma acción del mismo ref en los últimos 5 min
        if ($ref_id) {
            $reciente = DB::val(
                "SELECT id FROM actividad_log
                 WHERE usuario_id=? AND tipo=? AND ref_id=? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                 LIMIT 1",
                [$usuario_id, $tipo, $ref_id]
            );
            if ($reciente) return;
        } else {
            // Para acciones sin ref (radar_view, login), throttle 1 min
            $reciente = DB::val(
                "SELECT id FROM actividad_log
                 WHERE usuario_id=? AND tipo=? AND ref_id IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                 LIMIT 1",
                [$usuario_id, $tipo]
            );
            if ($reciente) return;
        }

        DB::execute(
            "INSERT INTO actividad_log (usuario_id, empresa_id, tipo, ref_id) VALUES (?,?,?,?)",
            [$usuario_id, $empresa_id, $tipo, $ref_id]
        );
    }

    // ─── Calcular score de un usuario ─────────────────────
    public static function calcular(int $usuario_id, int $empresa_id): array
    {
        $ahora = time();
        $periodo = self::PERIODO;

        // ═══ SEÑALES CRUDAS (últimos 30 días) ═══

        // Días activos (logins distintos)
        $dias_activos = (int)DB::val(
            "SELECT COUNT(DISTINCT DATE(created_at)) FROM actividad_log
             WHERE usuario_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );

        // Acciones de gestión (radar + quote_view + client_view)
        $acciones = (int)DB::val(
            "SELECT COUNT(*) FROM actividad_log
             WHERE usuario_id=? AND tipo IN ('radar_view','quote_view','client_view')
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $periodo]
        );

        // Conversiones (cotizaciones aceptadas/convertidas asignadas a este vendedor)
        $conversiones = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE vendedor_id=? AND empresa_id=?
             AND estado IN ('aceptada','convertida','aceptada_cliente')
             AND accion_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$usuario_id, $empresa_id, $periodo]
        );

        // Carga activa (cotizaciones en estado activo asignadas)
        $carga_activa = (int)DB::val(
            "SELECT COUNT(*) FROM cotizaciones
             WHERE vendedor_id=? AND empresa_id=?
             AND estado IN ('borrador','enviada','vista')",
            [$usuario_id, $empresa_id]
        );

        // ═══ DIMENSIÓN 1: TASAS PROPORCIONALES A CARGA ═══

        $dias_laborales = max(($periodo / 7) * self::DIAS_SEMANA, 1);
        $semanas        = max($periodo / 7, 1);
        $carga          = max($carga_activa, 1); // evitar /0

        // Presencia: días activos / días laborales (0 a 1)
        $tasa_presencia = min($dias_activos / $dias_laborales, 1.0);

        // Gestión: acciones por cotización activa por semana
        $tasa_gestion = $acciones / $carga / $semanas;

        // Conversión: % de carga que convirtió
        $tasa_conversion = $conversiones / $carga;

        // ═══ DIMENSIÓN 2: MOMENTUM (EMA) ═══

        $prev = DB::row(
            "SELECT ema_gestion, ema_presencia, ema_conversion FROM usuario_score WHERE usuario_id=?",
            [$usuario_id]
        );

        $alpha = self::EMA_ALPHA;

        if ($prev && ($prev['ema_gestion'] > 0 || $prev['ema_presencia'] > 0)) {
            // Actualizar EMA
            $ema_gestion    = $alpha * $tasa_gestion    + (1 - $alpha) * (float)$prev['ema_gestion'];
            $ema_presencia  = $alpha * $tasa_presencia  + (1 - $alpha) * (float)$prev['ema_presencia'];
            $ema_conversion = $alpha * $tasa_conversion + (1 - $alpha) * (float)$prev['ema_conversion'];

            // Momentum: ratio actual vs EMA
            $ema_composite = ($ema_gestion * 0.55 + $ema_presencia * 0.15 + $ema_conversion * 0.30);
            $cur_composite = ($tasa_gestion * 0.55 + $tasa_presencia * 0.15 + $tasa_conversion * 0.30);

            if ($ema_composite > 0) {
                $momentum = $cur_composite / $ema_composite;
            } else {
                $momentum = $cur_composite > 0 ? 1.5 : 1.0;
            }
        } else {
            // Primera vez: EMA = valor actual, momentum neutro
            $ema_gestion    = $tasa_gestion;
            $ema_presencia  = $tasa_presencia;
            $ema_conversion = $tasa_conversion;
            $momentum       = 1.0;
        }

        // Convertir momentum a score 0-1 con sigmoid suave
        // momentum 0.5 → ~0.25, 1.0 → 0.50, 1.5 → ~0.75, 2.0 → ~0.88
        $momentum_score = 1.0 / (1.0 + exp(-3.0 * ($momentum - 1.0)));

        // ═══ DIMENSIÓN 3: PERCENTIL EN EQUIPO ═══

        // Calcular tasa compuesta para todos los usuarios activos de la empresa
        $team = DB::query(
            "SELECT u.id,
                    COALESCE(us.tasa_gestion, 0) AS tg,
                    COALESCE(us.score, 0) AS sc
             FROM usuarios u
             LEFT JOIN usuario_score us ON us.usuario_id = u.id
             WHERE u.empresa_id = ? AND u.activo = 1",
            [$empresa_id]
        );

        $team_size = count($team);
        $percentil = 0.50; // default neutral

        if ($team_size >= 2) {
            // Score compuesto del usuario actual
            $mi_score_prop = $tasa_presencia * 0.15 + $tasa_gestion * 0.55 + $tasa_conversion * 0.30;

            // Obtener scores de todos
            $scores_equipo = [];
            foreach ($team as $t) {
                if ((int)$t['id'] === $usuario_id) {
                    $scores_equipo[] = $mi_score_prop;
                } else {
                    $scores_equipo[] = (float)$t['tg']; // usar tasa_gestion guardada
                }
            }
            sort($scores_equipo);

            // Posición del usuario
            $pos = array_search($mi_score_prop, $scores_equipo);
            if ($pos === false) $pos = 0;
            $percentil = $team_size > 1 ? $pos / ($team_size - 1) : 0.50;
        }

        // ═══ SCORE FINAL ═══

        // Proporcional: combinar las 3 tasas con sigmoid para cada una
        $s_presencia  = self::sigmoid($tasa_presencia, 0.50, 4.0);  // 50% asistencia = midpoint
        $s_gestion    = self::sigmoid($tasa_gestion, 1.0, 2.0);     // 1 acción/cot/semana = midpoint
        $s_conversion = self::sigmoid($tasa_conversion, 0.15, 8.0); // 15% conversión = midpoint

        $proporcional = $s_presencia * 0.15 + $s_gestion * 0.55 + $s_conversion * 0.30;

        if ($team_size >= 2) {
            $final = $proporcional * 0.50 + $momentum_score * 0.25 + $percentil * 0.25;
        } else {
            $final = $proporcional * 0.65 + $momentum_score * 0.35;
        }

        $score = (int)round($final * 100);
        $score = max(0, min(100, $score));

        // Nivel
        if ($score >= 86) $nivel = 'top';
        elseif ($score >= 61) $nivel = 'activo';
        elseif ($score >= 31) $nivel = 'regular';
        else $nivel = 'bajo';

        // ═══ GUARDAR ═══

        DB::execute(
            "INSERT INTO usuario_score
             (usuario_id, empresa_id, score, nivel, dias_activos, acciones, conversiones,
              carga_activa, tasa_gestion, ema_gestion, ema_presencia, ema_conversion,
              momentum, percentil)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
              score=VALUES(score), nivel=VALUES(nivel),
              dias_activos=VALUES(dias_activos), acciones=VALUES(acciones),
              conversiones=VALUES(conversiones), carga_activa=VALUES(carga_activa),
              tasa_gestion=VALUES(tasa_gestion),
              ema_gestion=VALUES(ema_gestion), ema_presencia=VALUES(ema_presencia),
              ema_conversion=VALUES(ema_conversion),
              momentum=VALUES(momentum), percentil=VALUES(percentil),
              updated_at=NOW()",
            [
                $usuario_id, $empresa_id, $score, $nivel,
                $dias_activos, $acciones, $conversiones, $carga_activa,
                round($tasa_gestion, 3),
                round($ema_gestion, 3), round($ema_presencia, 3), round($ema_conversion, 3),
                round($momentum, 2), round($percentil, 2),
            ]
        );

        return [
            'score'          => $score,
            'nivel'          => $nivel,
            'dias_activos'   => $dias_activos,
            'acciones'       => $acciones,
            'conversiones'   => $conversiones,
            'carga_activa'   => $carga_activa,
            'tasa_gestion'   => round($tasa_gestion, 3),
            'momentum'       => round($momentum, 2),
            'percentil'      => round($percentil, 2),
            'team_size'      => $team_size,
        ];
    }

    // ─── Recalcular toda la empresa ───────────────────────
    public static function recalcular_empresa(int $empresa_id): void
    {
        $usuarios = DB::query(
            "SELECT id FROM usuarios WHERE empresa_id = ? AND activo = 1",
            [$empresa_id]
        );
        foreach ($usuarios as $u) {
            self::calcular((int)$u['id'], $empresa_id);
        }
    }

    // ─── Obtener score guardado (sin recalcular) ──────────
    public static function obtener(int $usuario_id): ?array
    {
        return DB::row("SELECT * FROM usuario_score WHERE usuario_id = ?", [$usuario_id]);
    }

    // ─── Obtener scores de toda la empresa ────────────────
    public static function equipo(int $empresa_id): array
    {
        return DB::query(
            "SELECT us.*, u.nombre, u.rol
             FROM usuario_score us
             JOIN usuarios u ON u.id = us.usuario_id
             WHERE us.empresa_id = ? AND u.activo = 1
             ORDER BY us.score DESC",
            [$empresa_id]
        );
    }

    // ─── Sigmoid helper ───────────────────────────────────
    private static function sigmoid(float $x, float $midpoint, float $steepness): float
    {
        return 1.0 / (1.0 + exp(-$steepness * ($x - $midpoint)));
    }
}
