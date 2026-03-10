<?php
// ============================================================
//  CotizaApp — modules/auth/landing.php
//  GET / — Página raíz de cotiza.cloud (sin subdominio)
//  Redirige al registro si no hay empresa activa
// ============================================================

defined('COTIZAAPP') or die;

// Redirigir directo al registro
redirect(BASE_URL . '/registro');
