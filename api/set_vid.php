<?php
// ============================================================
//  CotizaApp — api/set_vid.php
//  GET /api/set-vid?v=VISITOR_ID&next=URL_SIGUIENTE
//  Guarda cookie cz_vid en este dominio y redirige al siguiente
//  Se usa en la cadena de sync al login
// ============================================================

// No requiere COTIZAAPP — se ejecuta en dominios custom directamente
header('Cache-Control: no-cache, no-store');

$vid  = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', ($_GET['v'] ?? '')), 0, 64);
$next = trim($_GET['next'] ?? '');

if ($vid) {
    setcookie('cz_vid', $vid, time() + 730 * 86400, '/', '', true, false);
}

if ($next && filter_var($next, FILTER_VALIDATE_URL)) {
    header('Location: ' . $next, true, 302);
} else {
    // Sin siguiente — mostrar confirmación simple
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><p>Sync completado.</p><script>window.close();</script></body></html>';
}
exit;
