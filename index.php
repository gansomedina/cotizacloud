<?php
// ============================================================
//  CotizaApp — index.php
//  Entry point único — todo pasa por aquí
// ============================================================

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
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

// ─── Servir archivos estáticos de /assets/ (JS, CSS, uploads, etc.) ──
if (preg_match('#^/assets/(.+)$#', $req_uri, $m)) {
    $candidates = [
        __DIR__ . '/assets/' . $m[1],
        __DIR__ . '/public/assets/' . $m[1],
    ];
    foreach ($candidates as $file) {
        $real = realpath($file);
        if ($real && is_file($real) && (
            str_starts_with($real, realpath(__DIR__ . '/assets') ?: '') ||
            str_starts_with($real, realpath(__DIR__ . '/public/assets') ?: '')
        )) {
            $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
            $mimes = ['js' => 'application/javascript', 'css' => 'text/css', 'svg' => 'image/svg+xml',
                       'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
            $mime = $mimes[$ext] ?? mime_content_type($real);
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=31536000');
            readfile($real);
            exit;
        }
    }
}

// ─── Service Worker (Web Push) — debe servirse desde raíz ───
if ($req_uri === '/sw.js') {
    $swFile = __DIR__ . '/public/sw.js';
    if (is_file($swFile)) {
        header('Content-Type: application/javascript');
        header('Cache-Control: no-cache');
        header('Service-Worker-Allowed: /');
        readfile($swFile);
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
