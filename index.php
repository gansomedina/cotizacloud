<?php
// ============================================================
//  CotizaApp — index.php
//  Entry point único — todo pasa por aquí
// ============================================================

define('COTIZAAPP', true);

// ─── Servir archivos estáticos de /uploads/ y /assets/ ──────
$req_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/uploads/(.+)$#', $req_uri, $m)) {
    // Buscar primero en ROOT/uploads/, luego en ROOT/public/uploads/
    $candidates = [
        __DIR__ . '/uploads/' . $m[1],
        __DIR__ . '/public/uploads/' . $m[1],
    ];
    foreach ($candidates as $file) {
        $real = realpath($file);
        $base1 = realpath(__DIR__ . '/uploads');
        $base2 = realpath(__DIR__ . '/public/uploads');
        if ($real && is_file($real) &&
            (($base1 && str_starts_with($real, $base1)) || ($base2 && str_starts_with($real, $base2)))) {
            $mime = mime_content_type($real);
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=31536000');
            readfile($real);
            exit;
        }
    }
}

// ─── Servir archivos estáticos de /assets/ (JS, CSS, etc.) ──
if (preg_match('#^/assets/(.+)$#', $req_uri, $m)) {
    $file = __DIR__ . '/assets/' . $m[1];
    $real = realpath($file);
    $base = realpath(__DIR__ . '/assets');
    if ($real && is_file($real) && $base && str_starts_with($real, $base)) {
        $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $mimes = ['js' => 'application/javascript', 'css' => 'text/css', 'svg' => 'image/svg+xml'];
        $mime = $mimes[$ext] ?? mime_content_type($real);
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
