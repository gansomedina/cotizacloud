<?php
// ============================================================
//  Supervisor — Ejecutivo consolidado
//
//  REUSA modules/superadmin/executive.php, no lo duplica: se le pasan dos
//  variables y él filtra sus empresas y oculta las secciones que no le tocan.
//  Es una página oscura y completa (con su propio <html>), por eso vive aparte
//  del panel con pestañas y no dentro del layout de la app.
// ============================================================
defined('COTIZAAPP') or die;
supervisor_requerir();

$modo_supervisor = true;
$empresas_cfg_ok = supervisor_empresas();

require dirname(__DIR__) . '/superadmin/executive.php';
