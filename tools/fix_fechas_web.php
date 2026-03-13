<?php
// Ejecutar via: https://tudominio.com/tools/fix_fechas_web.php?key=CAMBIA_ESTA_KEY
$key = 'closetfactory1';
if (($_GET['key'] ?? '') !== $key) { http_response_code(403); die('Forbidden'); }

require dirname(__DIR__) . '/bootstrap.php';

$pdo = DB::pdo();
$stmt = $pdo->prepare(
    "UPDATE cotizaciones 
     SET valida_hasta = NULL 
     WHERE valida_hasta IS NOT NULL 
       AND (valida_hasta = '0000-00-00' 
            OR YEAR(valida_hasta) < 2000)"
);
$stmt->execute();
echo "Filas corregidas: " . $stmt->rowCount();
