<?php
// ============================================================
//  RitmoAsesor — Alarma de Ritmo Semanal por Asesor
//  SOLO LECTURA. No toca ActividadScore ni la Mesa.
//  Responde: "¿quién bajó el ritmo esta semana?" con indicadores
//  ADELANTADOS (toques de seguimiento), no ventas cerradas (atrasado).
//  Diseño: docs/alarma_ritmo_diseno.md
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    // Estados "vivos" de una cotización (lead activo en el pipeline).
    private const VIVOS = "'enviada','vista'";
    // Ventana del pipeline ACTIVO (~3 ciclos de venta). Sin esto, "leads vivos"
    // contaría el cementerio de cotizaciones viejas nunca cerradas → huérfanos
    // inflados (caso real Abigail: 190). Solo lo reciente es "el trabajo de ahora".
    private const VENTANA = 90;

    /**
     * Ritmo del equipo de UNA empresa. Devuelve filas ya ordenadas por
     * caída (el que más bajó, primero — el que necesita presión).
     */
    public static function empresa(int $empresa_id): array
    {
        // Asesores activos con cartera. Excluye superadmin (no tiene cartera
        // propia — misma regla que el termómetro).
        $asesores = DB::query(
            "SELECT id, nombre FROM usuarios
              WHERE empresa_id = ? AND activo = 1 AND rol <> 'superadmin'
              ORDER BY nombre ASC",
            [$empresa_id]
        );

        $filas = [];
        foreach ($asesores as $a) {
            $r = self::_indicadores($empresa_id, (int)$a['id'], $a['nombre']);
            if ($r !== null) $filas[] = $r;
        }

        // Orden: 🔴 rojo primero, luego 🟡, luego 🟢; dentro de cada uno, la
        // mayor caída de toques arriba.
        $peso = ['rojo' => 0, 'amarillo' => 1, 'verde' => 2, 'sin_datos' => 3];
        usort($filas, function ($x, $y) use ($peso) {
            $px = $peso[$x['semaforo']] ?? 3;
            $py = $peso[$y['semaforo']] ?? 3;
            if ($px !== $py) return $px <=> $py;
            return $x['delta_pct'] <=> $y['delta_pct']; // más negativo primero
        });
        return $filas;
    }

    /**
     * Ritmo de TODAS las empresas (vista superadmin). Agrupa por empresa.
     * Solo empresas con la Mesa activa (mesa_activa >= 1); sin toques no hay
     * señal y saldría 🔴 falso.
     */
    public static function todas(): array
    {
        try {
            $emps = DB::query(
                "SELECT id, nombre FROM empresas
                  WHERE mesa_activa >= 1 AND slug <> '_system'
                  ORDER BY nombre ASC"
            );
        } catch (Throwable $e) {
            // mesa_activa sin migrar en algún entorno: no reventar.
            $emps = DB::query("SELECT id, nombre FROM empresas WHERE slug <> '_system' ORDER BY nombre ASC");
        }

        $out = [];
        foreach ($emps as $e) {
            $filas = self::empresa((int)$e['id']);
            // Solo mostrar empresas que sí tienen asesores con cartera.
            $con_cartera = array_filter($filas, fn($f) => $f['semaforo'] !== 'sin_datos');
            if (!$con_cartera) continue;
            $out[] = ['empresa_id' => (int)$e['id'], 'empresa' => $e['nombre'], 'asesores' => $filas];
        }
        return $out;
    }

    // ── Cálculo de los 4 indicadores de un asesor ──
    private static function _indicadores(int $empresa_id, int $uid, string $nombre): ?array
    {
        try {
            // 1. Toques de la semana (area='contacto') — señal principal.
            $toques_now = (int) DB::val(
                "SELECT COUNT(*) FROM mesa_estados
                  WHERE empresa_id = ? AND usuario_id = ? AND area = 'contacto'
                    AND created_at >= NOW() - INTERVAL 7 DAY",
                [$empresa_id, $uid]
            );
            $toques_prev = (int) DB::val(
                "SELECT COUNT(*) FROM mesa_estados
                  WHERE empresa_id = ? AND usuario_id = ? AND area = 'contacto'
                    AND created_at >= NOW() - INTERVAL 14 DAY
                    AND created_at <  NOW() - INTERVAL 7 DAY",
                [$empresa_id, $uid]
            );

            // 2. Leads activos (vivos) del asesor — como usuario_id o vendedor_id.
            //    Acotado al pipeline reciente (VENTANA) para no contar el cementerio.
            $leads = (int) DB::val(
                "SELECT COUNT(*) FROM cotizaciones
                  WHERE empresa_id = ? AND estado IN (" . self::VIVOS . ")
                    AND (usuario_id = ? OR vendedor_id = ?)
                    AND created_at >= NOW() - INTERVAL " . self::VENTANA . " DAY",
                [$empresa_id, $uid, $uid]
            );

            // 3. Huérfanos: leads vivos del pipeline reciente (7 a VENTANA días de
            //    edad) sin ningún toque en los últimos 7 días (se están soltando).
            $huerfanos = (int) DB::val(
                "SELECT COUNT(*) FROM cotizaciones c
                  WHERE c.empresa_id = ? AND c.estado IN (" . self::VIVOS . ")
                    AND (c.usuario_id = ? OR c.vendedor_id = ?)
                    AND c.created_at >= NOW() - INTERVAL " . self::VENTANA . " DAY
                    AND c.created_at < NOW() - INTERVAL 7 DAY
                    AND NOT EXISTS (
                        SELECT 1 FROM mesa_estados m
                         WHERE m.cotizacion_id = c.id AND m.area = 'contacto'
                           AND m.created_at >= NOW() - INTERVAL 7 DAY)",
                [$empresa_id, $uid, $uid]
            );

            // 4. Cotizaciones nuevas de la semana (flujo de entrada).
            $nuevas = (int) DB::val(
                "SELECT COUNT(*) FROM cotizaciones
                  WHERE empresa_id = ? AND (usuario_id = ? OR vendedor_id = ?)
                    AND created_at >= NOW() - INTERVAL 7 DAY",
                [$empresa_id, $uid, $uid]
            );
        } catch (Throwable $e) {
            error_log('[RitmoAsesor] ' . $e->getMessage());
            return null;
        }

        // Sin cartera viva y sin actividad → no es asesor operativo esta semana.
        if ($leads === 0 && $toques_now === 0 && $toques_prev === 0) {
            return [
                'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => 'sin_datos',
                'toques_now' => 0, 'toques_prev' => 0, 'leads' => 0, 'ratio' => 0.0,
                'huerfanos' => 0, 'nuevas' => $nuevas, 'delta_pct' => 0.0, 'motivo' => 'Sin cartera activa esta semana.',
            ];
        }

        $ratio = $leads > 0 ? round($toques_now / $leads, 2) : 0.0;
        // Delta de toques semana vs semana (para ordenar).
        if ($toques_prev > 0) {
            $delta_pct = ($toques_now - $toques_prev) / $toques_prev;
        } else {
            $delta_pct = $toques_now > 0 ? 1.0 : 0.0; // de 0 a algo = mejora; de 0 a 0 = plano
        }
        $huerf_ratio = $leads > 0 ? $huerfanos / $leads : 0.0;

        // ── Disparadores (cada uno con su propio mensaje) ──
        // El % solo cuenta como "caída" si la base anterior es significativa
        // (toques_prev >= 5): así no marcamos "bajó" cuando en realidad SUBIÓ
        // desde una base chica (1 → 34 no es una caída, es un repunte).
        $parado     = $toques_now === 0 && $leads > 3;
        $cayo       = $toques_prev >= 5 && $delta_pct <= -0.30;
        $cayo_leve  = $toques_prev >= 5 && $delta_pct <= -0.10;
        $huerf_alto = $huerf_ratio >= 0.5 && $huerfanos >= 5;
        $huerf_med  = $huerf_ratio >= 0.3 && $huerfanos >= 4;

        if ($parado || $cayo || $huerf_alto) {
            $sem = 'rojo';
        } elseif ($cayo_leve || $huerf_med) {
            $sem = 'amarillo';
        } else {
            $sem = 'verde';
        }

        // Mensaje según el DISPARADOR real (nunca dice "bajó" si en verdad subió).
        $primer = self::_primer($nombre);
        if ($parado) {
            $motivo = "Sin seguimientos esta semana con $leads leads vivos — habla con $primer hoy.";
        } elseif ($cayo) {
            $motivo = "Bajó el ritmo fuerte (de {$toques_prev} a {$toques_now} toques) — presiona esta semana.";
        } elseif ($huerf_alto) {
            $motivo = "$huerfanos leads sin tocar en +7 días — se están enfriando, que retome seguimiento.";
        } elseif ($cayo_leve) {
            $motivo = "Bajó un poco el ritmo (de {$toques_prev} a {$toques_now} toques) — vigilar.";
        } elseif ($huerf_med) {
            $motivo = "$huerfanos leads empiezan a enfriarse — que retome seguimiento.";
        } else {
            $motivo = "Mantiene el ritmo.";
        }

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem,
            'toques_now' => $toques_now, 'toques_prev' => $toques_prev,
            'leads' => $leads, 'ratio' => $ratio, 'huerfanos' => $huerfanos,
            'nuevas' => $nuevas, 'delta_pct' => $delta_pct, 'motivo' => $motivo,
        ];
    }

    private static function _primer(string $nombre): string
    {
        return trim(explode(' ', trim($nombre))[0] ?? $nombre);
    }
}
