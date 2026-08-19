<?php
// ============================================================
//  Supervisor — Dashboard Ejecutivo acotado a sus sucursales
//
//  NO duplica el ejecutivo: lo REUSA. Define dos variables y hace require de
//  modules/superadmin/executive.php, que respeta ambas:
//
//    $modo_supervisor  → cambia el guard y oculta las secciones que no le tocan
//    $empresas_cfg_ok  → los IDs que SÍ puede ver
//
//  Duplicar el archivo habría significado mantener dos ejecutivos: cada
//  arreglo o métrica nueva habría que hacerla dos veces, y tarde o temprano
//  uno se queda atrás.
// ============================================================
defined('COTIZAAPP') or die;

// Guard propio. El ejecutivo lo vuelve a verificar por su cuenta — no se
// confía en que quien haga el require ya lo haya hecho.
supervisor_requerir();

$modo_supervisor = true;
$empresas_cfg_ok = supervisor_empresas();

require dirname(__DIR__) . '/superadmin/executive.php';
