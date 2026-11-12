<?php
// Recibe las calles que un navegador ya pudo descargar (formato GeoJSON simple)
// y las guarda en la caché del servidor, para que calles.php las entregue
// a todos los dispositivos aunque Overpass no responda.

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    exit();
}

session_write_close();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

const DURACION_CACHE = 7 * 24 * 3600;
const MAX_BYTES = 10 * 1024 * 1024;
const MAX_CALLES = 3000;

// Zona del Liceo (con un pequeño margen).
const LAT_MIN = -34.8952 - 0.003;
const LAT_MAX = -34.8862 + 0.003;
const LNG_MIN = -56.1653 - 0.003;
const LNG_MAX = -56.1544 + 0.003;

$archivoCache = sys_get_temp_dir() . '/calles_liceo_cache.json';

// Si ya hay una caché vigente, no hace falta reemplazarla.
if (is_file($archivoCache) && time() - filemtime($archivoCache) < DURACION_CACHE) {
    $actual = json_decode((string)file_get_contents($archivoCache), true);
    if (is_array($actual) && !empty($actual['elements'])) {
        echo json_encode(['ok' => true, 'ya_existia' => true]);
        exit();
    }
}

$cuerpo = file_get_contents('php://input', false, null, 0, MAX_BYTES + 1);

if ($cuerpo === false || strlen($cuerpo) > MAX_BYTES) {
    http_response_code(413);
    echo json_encode(['error' => 'Datos demasiado grandes']);
    exit();
}

$entrada = json_decode($cuerpo, true);

if (!is_array($entrada) || !isset($entrada['features']) || !is_array($entrada['features'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit();
}

$elementos = [];

foreach (array_slice($entrada['features'], 0, MAX_CALLES) as $f) {
    $id = $f['properties']['id'] ?? null;
    $coords = $f['geometry']['coordinates'] ?? null;

    if (!is_numeric($id) || !is_array($coords) || count($coords) < 2) {
        continue;
    }

    $geometria = [];
    $valido = true;

    foreach ($coords as $c) {
        if (!is_array($c) || count($c) < 2 || !is_numeric($c[0]) || !is_numeric($c[1])) {
            $valido = false;
            break;
        }

        $lng = (float)$c[0];
        $lat = (float)$c[1];

        if ($lat < LAT_MIN || $lat > LAT_MAX || $lng < LNG_MIN || $lng > LNG_MAX) {
            $valido = false;
            break;
        }

        $geometria[] = ['lat' => $lat, 'lon' => $lng];
    }

    if (!$valido) {
        continue;
    }

    // Solo se conservan las etiquetas necesarias.
    $tags = [];
    foreach (['name', 'highway'] as $clave) {
        if (isset($f['properties']['tags'][$clave]) && is_string($f['properties']['tags'][$clave])) {
            $tags[$clave] = mb_substr($f['properties']['tags'][$clave], 0, 150);
        }
    }

    $elementos[] = [
        'type' => 'way',
        'id' => (int)$id,
        'tags' => (object)$tags,
        'geometry' => $geometria
    ];
}

if (count($elementos) < 10) {
    http_response_code(400);
    echo json_encode(['error' => 'Muy pocas calles válidas']);
    exit();
}

file_put_contents(
    $archivoCache,
    json_encode(['elements' => $elementos], JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

echo json_encode(['ok' => true, 'guardadas' => count($elementos)]);