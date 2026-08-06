<?php
// ============================================================
//  RitmoAsesor — Alarma de Ritmo Semanal por Asesor
//  SOLO LECTURA. No toca ActividadScore ni escribe en la Mesa.
//  Responde: "¿quién está bajando el ritmo de seguimiento?" con
//  indicadores ADELANTADOS de la PROPIA Mesa (no ventas cerradas,
//  que son el resultado atrasado ~28 días).
//
//  3 ejes, TODOS de la Mesa (cero contador paralelo):
//   1) 🔴 vencidas subiendo   — dejó pudrir el seguimiento
//      (resumen['vencidas'] hoy + tendencia de mesa_vencidos)
//   2) 🟡 por vencer sin tocar — vence hoy/mañana y no la ha movido
//      (reloj de Mesa::armar: estado 'hoy' o 'ok' que vence mañana)
//   3) 🗑 candado de descartes — descartó SIN seguimiento previo
//      (la puerta trasera: limpiar la mesa descartando, no trabajando)
//
//  Diseño: docs/alarma_ritmo_diseno.md
// ============================================================
defined('COTIZAAPP') or die;

class RitmoAsesor
{
    // Umbrales PROVISIONALES — se calibran con datos reales de producción
    // (no hay BD en el contenedor de build). Todos comentados donde se usan.
    private const ROJO_VENC_FOTO   = 6;  // vencidas AHORA que ya es emergencia
    private const AMBAR_VENC_FOTO  = 3;  // vencidas AHORA que ya avisan
    private const ROJO_VENC_SUBE   = 3;  // vencidas nuevas de la semana (subiendo)
    private const AMBAR_PORVENCER  = 3;  // por-vencer-sin-tocar que ya avisan
    private const ROJO_DUMP        = 3;  // descartes sin trabajar → emergencia
    private const VENTANA_DESC     = 7;  // días de la ventana de descartes

    /**
     * Ritmo del equipo de UNA empresa. Filas ordenadas por severidad
     * (el que más necesita presión, arriba).
     */
    public static function empresa(int $empresa_id): array
    {
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

        // 🔴 → 🟡 → 🟢; dentro de cada banda, mayor severidad arriba.
        $peso = ['rojo' => 0, 'amarillo' => 1, 'verde' => 2, 'sin_datos' => 3];
        usort($filas, function ($x, $y) use ($peso) {
            $px = $peso[$x['semaforo']] ?? 3;
            $py = $peso[$y['semaforo']] ?? 3;
            if ($px !== $py) return $px <=> $py;
            return $y['severidad'] <=> $x['severidad']; // más severo primero
        });
        return $filas;
    }

    /**
     * Ritmo de TODAS las empresas (vista superadmin). Solo empresas con la
     * Mesa activa (mesa_activa >= 1); sin Mesa no hay reloj ni señal.
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
            return [];
        }

        $out = [];
        foreach ($emps as $e) {
            $filas = self::empresa((int)$e['id']);
            $con_cartera = array_filter($filas, fn($f) => $f['semaforo'] !== 'sin_datos');
            if (!$con_cartera) continue;
            $out[] = ['empresa_id' => (int)$e['id'], 'empresa' => $e['nombre'], 'asesores' => $filas];
        }
        return $out;
    }

    // ── Cálculo por asesor, todo de la Mesa ──
    private static function _indicadores(int $empresa_id, int $uid, string $nombre): ?array
    {
        // 1) La Mesa del asesor: reloj de seguimiento por cotización + resumen.
        try {
            $mesa = Mesa::armar($empresa_id, $uid);
        } catch (Throwable $e) {
            error_log('[RitmoAsesor armar] ' . $e->getMessage());
            return null;
        }
        $mr    = $mesa['resumen'] ?? [];
        $rows  = $mesa['rows'] ?? [];
        $cartera  = (int)($mr['universo'] ?? count($rows));
        $vencidas = (int)($mr['vencidas'] ?? 0);   // FOTO de hoy: reloj ya pasó

        // 2) Por vencer SIN tocar (alarma temprana): vence hoy o mañana y no la
        //    movió hoy. El reloj lo da la propia Mesa (seguimiento.estado/vence).
        //    'hoy' = dias_venc 0 · 'ok' + vence==mañana = dias_venc -1. El rojo
        //    (vencida) es +1 día vencido — esta banda abre ANTES.
        $manana = date('Y-m-d', strtotime('+1 day'));
        $por_vencer = 0;
        foreach ($rows as $r) {
            if (!empty($r['es_fria']) || ($r['cat'] ?? '') === 'descartada_hoy') continue;
            if (!empty($r['atendida_hoy'])) continue; // ya la trabajó hoy
            $sg = $r['seguimiento'] ?? null;
            if (!$sg) continue;                        // vírgenes sin reloj: "por trabajar", otro eje
            $est = $sg['estado'] ?? '';
            if ($est === 'hoy') { $por_vencer++; continue; }
            if ($est === 'ok' && ($sg['vence'] ?? '') === $manana) $por_vencer++;
        }

        // 3) Tendencia de vencidas — el "empezó a soltar". mesa_vencidos guarda
        //    un registro diario por asesor (memoria permanente). Comparamos las
        //    cotizaciones que se le vencieron esta semana vs la anterior.
        $venc_now = 0; $venc_prev = 0;
        try {
            $venc_now = (int) DB::val(
                "SELECT COUNT(DISTINCT cotizacion_id) FROM mesa_vencidos
                  WHERE empresa_id = ? AND usuario_id = ?
                    AND fecha >= CURDATE() - INTERVAL 6 DAY",
                [$empresa_id, $uid]
            );
            $venc_prev = (int) DB::val(
                "SELECT COUNT(DISTINCT cotizacion_id) FROM mesa_vencidos
                  WHERE empresa_id = ? AND usuario_id = ?
                    AND fecha >= CURDATE() - INTERVAL 13 DAY
                    AND fecha <  CURDATE() - INTERVAL 6 DAY",
                [$empresa_id, $uid]
            );
        } catch (Throwable $e) {
            // mesa_vencidos sin migrar: sin tendencia, seguimos con la foto.
        }
        $venc_sube = $venc_now > $venc_prev;

        // 4) Candado de descartes — la puerta trasera. Descartes de la semana y,
        //    de esos, cuántos SIN seguimiento previo (nunca habló con el cliente
        //    y no agotó la escalera de 4 "no contestó") = "solo descarté".
        $descartes = 0; $sin_trabajo = 0;
        try {
            $dq = DB::query(
                "SELECT COUNT(*) AS descartes,
                        COALESCE(SUM(sin_trabajo), 0) AS sin_trabajo
                 FROM (
                    SELECT d.cid,
                      (NOT EXISTS (SELECT 1 FROM mesa_estados h
                                    WHERE h.cotizacion_id = d.cid
                                      AND h.area = 'contacto' AND h.estado = 'hablamos')
                       AND (SELECT COUNT(*) FROM mesa_estados nc
                             WHERE nc.cotizacion_id = d.cid
                               AND nc.area = 'contacto' AND nc.estado = 'no_contesta') < 4
                      ) AS sin_trabajo
                    FROM (
                        SELECT DISTINCT m.cotizacion_id AS cid
                          FROM mesa_estados m JOIN cotizaciones c ON c.id = m.cotizacion_id
                         WHERE m.empresa_id = ? AND m.area = 'postura' AND m.estado = 'descartada'
                           AND COALESCE(c.vendedor_id, c.usuario_id) = ?
                           AND m.created_at >= NOW() - INTERVAL " . self::VENTANA_DESC . " DAY
                        UNION
                        SELECT DISTINCT rf.cotizacion_id AS cid
                          FROM radar_feedback rf JOIN cotizaciones c ON c.id = rf.cotizacion_id
                         WHERE rf.empresa_id = ? AND rf.tipo = 'sin_interes'
                           AND COALESCE(c.vendedor_id, c.usuario_id) = ?
                           AND rf.updated_at >= NOW() - INTERVAL " . self::VENTANA_DESC . " DAY
                    ) d
                 ) z",
                [$empresa_id, $uid, $empresa_id, $uid]
            );
            if ($dq) {
                $descartes   = (int)$dq[0]['descartes'];
                $sin_trabajo = (int)$dq[0]['sin_trabajo'];
            }
        } catch (Throwable $e) {
            error_log('[RitmoAsesor desc] ' . $e->getMessage());
        }

        // Sin cartera y sin nada que medir → no es asesor operativo esta semana.
        if ($cartera === 0 && $vencidas === 0 && $por_vencer === 0
            && $venc_now === 0 && $descartes === 0) {
            return [
                'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => 'sin_datos',
                'vencidas' => 0, 'venc_now' => 0, 'venc_prev' => 0, 'venc_sube' => false,
                'por_vencer' => 0, 'descartes' => 0, 'sin_trabajo' => 0,
                'cartera' => 0, 'severidad' => 0,
                'motivo' => 'Sin cartera activa esta semana.',
            ];
        }

        // ── Semáforo (umbrales provisionales, a calibrar) ──
        $rojo  = false; $amar = false;
        // Candado descartes: tiró varias SIN haberlas trabajado.
        if ($sin_trabajo >= self::ROJO_DUMP)      $rojo = true;
        elseif ($sin_trabajo >= 1)                $amar = true;
        // Vencidas subiendo (el "empezó a soltar").
        if ($venc_sube && $venc_now >= self::ROJO_VENC_SUBE) $rojo = true;
        elseif ($venc_sube)                                  $amar = true;
        // Vencidas AHORA (foto): pila ya acumulada.
        if ($vencidas >= self::ROJO_VENC_FOTO)    $rojo = true;
        elseif ($vencidas >= self::AMBAR_VENC_FOTO) $amar = true;
        // Por vencer sin tocar (alarma temprana pura).
        if ($por_vencer >= self::AMBAR_PORVENCER) $amar = true;

        $sem = $rojo ? 'rojo' : ($amar ? 'amarillo' : 'verde');

        // Mensaje según el disparador dominante (nunca dice algo que no midió).
        $p = self::_primer($nombre);
        if ($sin_trabajo >= self::ROJO_DUMP) {
            $motivo = "Descartó {$sin_trabajo} sin haberlas trabajado esta semana — está limpiando la mesa, no dando seguimiento.";
        } elseif ($venc_sube && $venc_now >= self::ROJO_VENC_SUBE) {
            $motivo = "Se le vencieron {$venc_now} esta semana (antes {$venc_prev}) — el seguimiento se le está acumulando, presiona a {$p}.";
        } elseif ($vencidas >= self::ROJO_VENC_FOTO) {
            $motivo = "Tiene {$vencidas} cotizaciones con el seguimiento vencido — que las retome antes de que se enfríen.";
        } elseif ($sin_trabajo >= 1) {
            $motivo = "Descartó {$sin_trabajo} sin trabajar — ojo que no esté limpiando la mesa en vez de dar seguimiento.";
        } elseif ($venc_sube) {
            $motivo = "Empiezan a vencérsele seguimientos ({$venc_prev}→{$venc_now}) — vigílalo esta semana.";
        } elseif ($vencidas >= self::AMBAR_VENC_FOTO) {
            $motivo = "Tiene {$vencidas} con seguimiento vencido — aún a tiempo de retomarlas.";
        } elseif ($por_vencer >= self::AMBAR_PORVENCER) {
            $motivo = "{$por_vencer} vencen hoy/mañana y no las ha tocado — que las mueva antes de que se pongan rojas.";
        } else {
            $motivo = "Al corriente con su seguimiento.";
        }

        // Severidad para ordenar (más = más urgente).
        $severidad = $sin_trabajo * 4 + $vencidas * 2 + max(0, $venc_now - $venc_prev) * 3 + $por_vencer;

        return [
            'usuario_id' => $uid, 'nombre' => $nombre, 'semaforo' => $sem,
            'vencidas' => $vencidas, 'venc_now' => $venc_now, 'venc_prev' => $venc_prev,
            'venc_sube' => $venc_sube, 'por_vencer' => $por_vencer,
            'descartes' => $descartes, 'sin_trabajo' => $sin_trabajo,
            'cartera' => $cartera, 'severidad' => $severidad, 'motivo' => $motivo,
        ];
    }

    private static function _primer(string $nombre): string
    {
        return trim(explode(' ', trim($nombre))[0] ?? $nombre);
    }
}
