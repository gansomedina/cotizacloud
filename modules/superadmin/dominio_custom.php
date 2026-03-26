<?php
// ============================================================
//  SuperAdmin — Guardar dominio custom de empresa
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();
csrf_check();

$empresa_id = (int)($id ?? 0);
$emp = DB::row("SELECT id, slug FROM empresas WHERE id = ?", [$empresa_id]);
if (!$emp) { http_response_code(404); die('Empresa no encontrada'); }

$dominio = trim(strtolower(input('dominio_custom')));

// Vacío = quitar dominio custom
if ($dominio === '') {
    DB::execute("UPDATE empresas SET dominio_custom = NULL WHERE id = ?", [$empresa_id]);
    flash('ok', 'Dominio custom eliminado');
    redirect("/superadmin/empresa/{$empresa_id}");
}

// Validar formato: solo letras, números, puntos y guiones
if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]*[a-z0-9])?)+$/', $dominio)) {
    flash('error', 'Formato de dominio inválido');
    redirect("/superadmin/empresa/{$empresa_id}");
}

// No permitir subdominios de cotiza.cloud (esos se manejan automáticamente)
if (str_ends_with($dominio, '.' . strtolower(BASE_DOMAIN)) || $dominio === strtolower(BASE_DOMAIN)) {
    flash('error', 'No usar subdominios de ' . BASE_DOMAIN . ' — esos se asignan automáticamente');
    redirect("/superadmin/empresa/{$empresa_id}");
}

// Verificar que no esté en uso por otra empresa
$existente = DB::val("SELECT id FROM empresas WHERE dominio_custom = ? AND id != ?", [$dominio, $empresa_id]);
if ($existente) {
    flash('error', 'Ese dominio ya está asignado a otra empresa');
    redirect("/superadmin/empresa/{$empresa_id}");
}

DB::execute("UPDATE empresas SET dominio_custom = ? WHERE id = ?", [$dominio, $empresa_id]);
flash('ok', "Dominio custom configurado: {$dominio}");
redirect("/superadmin/empresa/{$empresa_id}");
