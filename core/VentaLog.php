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
            'abono_registrado'    => [ico('money',14,'#16a34a'), 'Pago registrado'],
            'abono_cancelado'     => [ico('x',14,'#dc2626'),  'Pago cancelado'],
            'estado_cambiado'     => [ico('check',14,'#2563eb'), 'Estado actualizado'],
            'item_agregado'       => [ico('check',14,'#16a34a'), 'Artículo agregado'],
            'item_editado'        => [ico('edit',14,'#d97706'),  'Artículo editado'],
            'item_eliminado'      => [ico('x',14,'#dc2626'),  'Artículo eliminado'],
            'descuento_agregado'  => [ico('tag',14,'#7c3aed'), 'Descuento agregado'],
            'descuento_eliminado' => [ico('x',14,'#dc2626'),  'Descuento eliminado'],
            'cliente_cambiado'    => [ico('eye',14,'#2563eb'), 'Cliente cambiado'],
            'cotizacion_guardada' => [ico('check',14,'#16a34a'), 'Cotización guardada'],
            'venta_creada'        => [ico('check',14,'#16a34a'), 'Venta creada'],
            default               => [ico('file',14,'#64748b'), ucfirst(str_replace('_', ' ', $evento))],
        };
    }
}
