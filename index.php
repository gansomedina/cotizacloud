<?php
// ============================================================
//  CotizaApp — index.php
//  Entry point único — todo pasa por aquí
// ============================================================

define('COTIZAAPP', true);

require_once __DIR__ . '/config.php';

// Iniciar sesión y detectar empresa
Auth::init();

// Registrar todas las rutas
Router::register_all();

// Despachar
Router::dispatch();
