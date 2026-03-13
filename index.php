<?php
// ============================================================
//  CotizaApp — index.php
//  Entry point único — todo pasa por aquí
// ============================================================

define('COTIZAAPP', true);

// ─── Servir archivos estáticos de /uploads/ ─────────────────
$req_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/uploads/(.+)$#', $req_uri, $m)) {
    $file = __DIR__ . '/public/uploads/' . $m[1];
    // Seguridad: no permitir path traversal
    $real = realpath($file);
    $base = realpath(__DIR__ . '/public/uploads');
    if ($real && $base && str_starts_with($real, $base) && is_file($real)) {
        $mime = mime_content_type($real);
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000');
        readfile($real);
        exit;
    }
}

require_once __DIR__ . '/config.php';

// Iniciar sesión y detectar empresa
Auth::init();

// Registrar todas las rutas
Router::register_all();

// Despachar
Router::dispatch();
