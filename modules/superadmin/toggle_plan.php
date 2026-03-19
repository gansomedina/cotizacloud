<?php
// ============================================================
//  SuperAdmin — Toggle plan trial/pro
//  POST /superadmin/empresa/:id/plan
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();
csrf_check();

$empresa_id = (int)($id ?? 0);
$emp = DB::row("SELECT id, plan, slug FROM empresas WHERE id = ?", [$empresa_id]);
if (!$emp || $emp['slug'] === '_system') {
    http_response_code(404);
    die('Empresa no encontrada');
}

$nuevo_plan = ($emp['plan'] ?? 'trial') === 'trial' ? 'pro' : 'trial';
DB::execute("UPDATE empresas SET plan = ? WHERE id = ?", [$nuevo_plan, $empresa_id]);

redirect('/superadmin/empresa/' . $empresa_id);
