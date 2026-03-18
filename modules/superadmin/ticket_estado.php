<?php
// ============================================================
//  SuperAdmin — Cambiar estado de ticket
//  POST /superadmin/ticket/:id/estado
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();
csrf_check();

$ticket_id = (int)($id ?? 0);
$estado = $_POST['estado'] ?? '';

if (!in_array($estado, ['abierto', 'en_proceso', 'cerrado'])) {
    http_response_code(400);
    die('Estado inválido');
}

$ticket = DB::row("SELECT id FROM tickets_soporte WHERE id = ?", [$ticket_id]);
if (!$ticket) {
    http_response_code(404);
    die('Ticket no encontrado');
}

DB::execute("UPDATE tickets_soporte SET estado = ? WHERE id = ?", [$estado, $ticket_id]);

redirect('/superadmin');
