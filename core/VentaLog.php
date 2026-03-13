<?php
// ============================================================
//  CotizaApp — core/VentaLog.php
//  Helper estático para registrar eventos en venta_log
// ============================================================

class VentaLog
{
    /**
     * Registra un evento en venta_log.
     * Nunca lanza excepciones — fallo silencioso para no romper flujos principales.
     */
    public static function registrar(
        int    $venta_id,
        int    $empresa_id,
        string $evento,
        ?string $detalle   = null,
        ?int   $usuario_id = null
    ): void {
        try {
            DB::execute(
                "INSERT INTO venta_log (venta_id, empresa_id, usuario_id, evento, detalle)
                 VALUES (?,?,?,?,?)",
                [$venta_id, $empresa_id, $usuario_id, $evento, $detalle]
            );
        } catch (Throwable) {
            // Silencioso — el log nunca debe romper la operación principal
        }
    }

    /**
     * Obtiene los últimos N eventos de una venta.
     */
    public static function obtener(int $venta_id, int $limite = 30): array
    {
        return DB::query(
            "SELECT l.*, u.nombre AS usuario_nombre
             FROM venta_log l
             LEFT JOIN usuarios u ON u.id = l.usuario_id
             WHERE l.venta_id = ?
             ORDER BY l.created_at DESC
             LIMIT ?",
            [$venta_id, $limite]
        ) ?: [];
    }

    /**
     * Etiqueta legible + ícono para cada evento.
     */
    public static function label(string $evento): array
    {
        return match ($evento) {
            'abono_registrado'    => ['💰', 'Pago registrado'],
            'abono_cancelado'     => ['↩️',  'Pago cancelado'],
            'estado_cambiado'     => ['🔄', 'Estado actualizado'],
            'item_agregado'       => ['➕', 'Artículo agregado'],
            'item_editado'        => ['✏️',  'Artículo editado'],
            'item_eliminado'      => ['🗑',  'Artículo eliminado'],
            'descuento_agregado'  => ['🏷️', 'Descuento agregado'],
            'descuento_eliminado' => ['✕',  'Descuento eliminado'],
            'cliente_cambiado'    => ['👤', 'Cliente cambiado'],
            'cotizacion_guardada' => ['💾', 'Cotización guardada'],
            'venta_creada'        => ['🎉', 'Venta creada'],
            default               => ['📌', ucfirst(str_replace('_', ' ', $evento))],
        };
    }
}
