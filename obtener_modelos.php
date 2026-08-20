<?php

$apiKey = 'a617667a-a7a2-442e-836e-3d1a874bf794';
$apiSecret = '34ac4d7d071ef76fbf9631cbe509ccc5';

$marca = $_GET['marca'] ?? '';

if ($marca === '') {
    http_response_code(400);

    header('Content-Type: application/json');

    echo json_encode([
        'error' => 'No se recibió ninguna marca'
    ]);

    exit;
}

$url = 'https://carapi.app/api/models/v2?make=' . urlencode($marca);

$ch = curl_init();

curl_setopt_array($ch, [

    CURLOPT_URL => $url,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'api-key: ' . $apiKey,
        'api-secret: ' . $apiSecret
    ]

]);

$respuesta = curl_exec($ch);

if ($respuesta === false) {

    http_response_code(500);

    header('Content-Type: application/json');

    echo json_encode([
        'error' => curl_error($ch)
    ]);

    curl_close($ch);

    exit;
}

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);

if ($httpCode !== 200) {

    http_response_code($httpCode);

    header('Content-Type: application/json');

    echo json_encode([
        'error' => 'Error en CarAPI',
        'codigo' => $httpCode
    ]);

    exit;
}

header('Content-Type: application/json');

echo $respuesta;

?>