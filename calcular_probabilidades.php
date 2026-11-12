<?php
// Calcula la probabilidad (0-100) de encontrar lugar en cada tramo de calle.
// Recibe por POST (JSON): { tramos: [{id, coordenadas: [[lon,lat],...]}], bbox: [...] }
// Devuelve: { "probabilidades": { "idTramo": 0..100 } }

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    exit();
}

// MUY IMPORTANTE: liberar la sesión antes de cualquier cálculo,
// así este archivo no bloquea la carga de las demás páginas.
session_write_close();

header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';

const REDUCCION_POR_AUTO = 10; // cada auto estacionado baja la probabilidad en 10 puntos
const DISTANCIA_MAX_M = 30;    // un auto cuenta para un tramo si está a menos de esto
const MAX_TRAMOS      = 3000;

/*
 * ESTRUCTURAS que atraen gente y quitan lugares en las calles cercanas.
 *
 *  - lat / lng : ubicación de la estructura
 *  - radio     : distancia en metros hasta donde llega su efecto
 *  - reduccion : puntos de probabilidad que resta una calle pegada a la estructura
 *                (el efecto baja de forma pareja hasta 0 en el borde del radio)
 *
 * Para agregar otra, copiá una línea y cambiá los valores.
 */
$estructuras = [
    ['nombre' => 'Liceo Logosófico',            'lat' => -34.89071, 'lng' => -56.15986, 'radio' => 150, 'reduccion' => 30],
    ['nombre' => 'Instituto Crandon',           'lat' => -34.88887, 'lng' => -56.16044, 'radio' => 400, 'reduccion' => 40],
    ['nombre' => 'Supermercado Disco',          'lat' => -34.88970, 'lng' => -56.16071, 'radio' => 120, 'reduccion' => 25],
    ['nombre' => 'Hospital Británico',          'lat' => -34.89443, 'lng' => -56.16287, 'radio' => 200, 'reduccion' => 35],
    ['nombre' => 'Médica Uruguaya (Sede Central)', 'lat' => -34.89287, 'lng' => -56.16275, 'radio' => 200, 'reduccion' => 35],
    ['nombre' => 'Estadio Centenario',          'lat' => -34.8944,  'lng' => -56.1528,  'radio' => 550, 'reduccion' => 40],
];

$entrada = json_decode(file_get_contents('php://input'), true);

if (!is_array($entrada) || !isset($entrada['tramos']) || !is_array($entrada['tramos'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit();
}

$tramosEntrada = array_slice($entrada['tramos'], 0, MAX_TRAMOS);

// Proyección simple a metros (suficiente para una zona de ~1 km).
$lat0 = -34.89;
$kx = 111320 * cos(deg2rad($lat0));
$ky = 110540;

function proyectar(float $lat, float $lng, float $kx, float $ky): array
{
    return [$lng * $kx, $lat * $ky];
}

function distanciaPuntoSegmento(array $p, array $a, array $b): float
{
    $dx = $b[0] - $a[0];
    $dy = $b[1] - $a[1];
    $largo2 = $dx * $dx + $dy * $dy;

    if ($largo2 == 0.0) {
        return hypot($p[0] - $a[0], $p[1] - $a[1]);
    }

    $t = (($p[0] - $a[0]) * $dx + ($p[1] - $a[1]) * $dy) / $largo2;
    $t = max(0.0, min(1.0, $t));

    return hypot($p[0] - ($a[0] + $t * $dx), $p[1] - ($a[1] + $t * $dy));
}

// 1) Preparar tramos: puntos en metros y largo total.
$tramos = [];

foreach ($tramosEntrada as $t) {
    if (!isset($t['id']) || !isset($t['coordenadas']) || !is_array($t['coordenadas'])) {
        continue;
    }

    $puntos = [];
    foreach ($t['coordenadas'] as $c) {
        if (is_array($c) && count($c) >= 2 && is_numeric($c[0]) && is_numeric($c[1])) {
            $puntos[] = proyectar((float)$c[1], (float)$c[0], $kx, $ky);
        }
    }

    if (count($puntos) < 2) {
        continue;
    }

    $largo = 0.0;
    for ($i = 1; $i < count($puntos); $i++) {
        $largo += hypot(
            $puntos[$i][0] - $puntos[$i - 1][0],
            $puntos[$i][1] - $puntos[$i - 1][1]
        );
    }

    $tramos[(int)$t['id']] = ['puntos' => $puntos, 'largo' => $largo];
}

// 2) Ocupaciones activas ahora mismo (una sola consulta).
$ocupaciones = [];

try {
    $stmt = $conexion->query("
        SELECT latitud, longitud
        FROM ocupa
        WHERE latitud IS NOT NULL
          AND longitud IS NOT NULL
          AND latitud <> 0
          AND longitud <> 0
          AND hora_estacionamiento IS NOT NULL
          AND hora_liberacion IS NOT NULL
          AND hora_estacionamiento <> hora_liberacion
          AND (
              (hora_estacionamiento < hora_liberacion
               AND CURTIME() >= hora_estacionamiento
               AND CURTIME() <  hora_liberacion)
              OR
              (hora_estacionamiento > hora_liberacion
               AND (CURTIME() >= hora_estacionamiento
                    OR CURTIME() <  hora_liberacion))
          )
    ");
    $ocupaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('calcular_probabilidades.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudieron leer las ocupaciones']);
    exit();
}

// 3) Asignar cada auto al tramo más cercano.
$ocupados = [];

foreach ($ocupaciones as $o) {
    $p = proyectar((float)$o['latitud'], (float)$o['longitud'], $kx, $ky);

    $mejorId = null;
    $mejorDist = INF;

    foreach ($tramos as $id => $tramo) {
        $pts = $tramo['puntos'];
        for ($i = 1; $i < count($pts); $i++) {
            $d = distanciaPuntoSegmento($p, $pts[$i - 1], $pts[$i]);
            if ($d < $mejorDist) {
                $mejorDist = $d;
                $mejorId = $id;
            }
        }
    }

    if ($mejorId !== null && $mejorDist <= DISTANCIA_MAX_M) {
        $ocupados[$mejorId] = ($ocupados[$mejorId] ?? 0) + 1;
    }
}

// 4) Probabilidad = 100%
//      - 10 puntos por cada auto estacionado en el tramo
//      - el efecto de cada estructura cercana (según la distancia).
$probabilidades = [];

foreach ($tramos as $id => $tramo) {
    $usados = $ocupados[$id] ?? 0;
    $resta = REDUCCION_POR_AUTO * $usados;

    foreach ($estructuras as $e) {
        $punto = proyectar((float)$e['lat'], (float)$e['lng'], $kx, $ky);

        // Distancia desde la estructura hasta el punto más cercano del tramo.
        $distancia = INF;
        $pts = $tramo['puntos'];
        for ($i = 1; $i < count($pts); $i++) {
            $distancia = min(
                $distancia,
                distanciaPuntoSegmento($punto, $pts[$i - 1], $pts[$i])
            );
        }

        if ($distancia < $e['radio']) {
            $resta += $e['reduccion'] * (1 - $distancia / $e['radio']);
        }
    }

    $probabilidades[$id] = max(0, (int)round(100 - $resta));
}

echo json_encode(
    ['probabilidades' => $probabilidades],
    JSON_NUMERIC_CHECK
);