<?php
// ============================================================
//  SuperAdmin — Toggle empresa activa/suspendida
//  POST /superadmin/empresa/:id/toggle
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();
csrf_check();

$empresa_id = (int)($id ?? 0);
$emp = DB::row("SELECT id, activa, slug FROM empresas WHERE id = ?", [$empresa_id]);
if (!$emp || $emp['slug'] === '_system') {
    http_response_code(404);
    die('Empresa no encontrada');
}

$nuevo_estado = $emp['activa'] ? 0 : 1;
DB::execute("UPDATE empresas SET activa = ? WHERE id = ?", [$nuevo_estado, $empresa_id]);

redirect('/superadmin');
