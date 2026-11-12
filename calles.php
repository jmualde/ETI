<?php
// Entrega las calles de la zona del Liceo.
// El SERVIDOR las pide a Overpass y las guarda en caché, así el celular
// (u otro dispositivo) no depende de conectarse a esos servidores externos.
//
// DIAGNÓSTICO: abrí este archivo en el navegador (…/calles.php).
// Si falla, el JSON muestra el motivo exacto en "detalle".

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    exit();
}

// Liberar la sesión para no bloquear otras páginas.
session_write_close();

// Que el servidor termine de guardar la caché aunque el usuario se vaya.
ignore_user_abort(true);
set_time_limit(120);

header('Content-Type: application/json; charset=utf-8');

const DURACION_CACHE = 7 * 24 * 3600; // las calles casi no cambian: 7 días

// En XAMPP/Windows es muy común el error de certificados SSL (cURL error 60).
// Con true, si pasa eso se reintenta sin verificar el certificado.
// Solo se trasladan datos públicos de mapas. Poné false si preferís no hacerlo.
const PERMITIR_SSL_INSEGURO = true;

$archivoCache = sys_get_temp_dir() . '/calles_liceo_cache.json';

$query =
    '[out:json][timeout:25];' .
    'way["highway"~' .
    '"^(residential|tertiary|secondary|primary|unclassified|living_street|service)$"]' .
    '(-34.8952,-56.1653,-34.8862,-56.1544);out geom tags;';

function datosValidos(?string $json): bool
{
    if (!$json) return false;
    $d = json_decode($json, true);
    return is_array($d) && isset($d['elements']) && is_array($d['elements'])
        && count($d['elements']) > 0;
}

function pedir(string $servidor, string $query, bool $verificarSsl): array
{
    $ch = curl_init($servidor);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['data' => $query]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => $verificarSsl,
        CURLOPT_SSL_VERIFYHOST => $verificarSsl ? 2 : 0,
        CURLOPT_USERAGENT => 'EstacionamientoLiceo/1.0 (proyecto escolar)'
    ]);

    $cuerpo = curl_exec($ch);
    $resultado = [
        'cuerpo' => $cuerpo === false ? null : $cuerpo,
        'http' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'errno' => curl_errno($ch),
        'error' => curl_error($ch)
    ];
    curl_close($ch);

    return $resultado;
}

// 1) Caché vigente
if (is_file($archivoCache) && time() - filemtime($archivoCache) < DURACION_CACHE) {
    $contenido = file_get_contents($archivoCache);
    if (datosValidos($contenido)) {
        echo $contenido;
        exit();
    }
}

$detalle = [];

// 2) Pedir a Overpass (prueba un servidor tras otro)
if (!function_exists('curl_init')) {
    $detalle[] = 'La extensión cURL de PHP no está activada (extension=curl en php.ini).';
} else {
    $servidores = [
        'https://overpass.private.coffee/api/interpreter',
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
        'https://overpass.openstreetmap.fr/api/interpreter'
    ];

    foreach ($servidores as $servidor) {
        $r = pedir($servidor, $query, true);

        // Error de certificados SSL: reintentar sin verificar (si está permitido).
        if ($r['errno'] !== 0 && in_array($r['errno'], [35, 51, 58, 60, 77], true)
            && PERMITIR_SSL_INSEGURO) {
            $detalle[] = "$servidor: error SSL ({$r['errno']}) {$r['error']} — reintentando sin verificar certificado";
            $r = pedir($servidor, $query, false);
        }

        if ($r['http'] === 200 && datosValidos($r['cuerpo'])) {
            @file_put_contents($archivoCache, $r['cuerpo'], LOCK_EX);
            echo $r['cuerpo'];
            exit();
        }

        $detalle[] = "$servidor: HTTP {$r['http']}, cURL {$r['errno']} {$r['error']}";
        error_log('calles.php: ' . end($detalle));
    }
}

// 3) Si todo falló, usar la caché vieja si existe
if (is_file($archivoCache)) {
    $contenido = file_get_contents($archivoCache);
    if (datosValidos($contenido)) {
        echo $contenido;
        exit();
    }
}

http_response_code(502);
echo json_encode(
    ['error' => 'No se pudieron obtener las calles', 'detalle' => $detalle],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);