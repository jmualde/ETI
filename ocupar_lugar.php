
<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$mensaje = $_SESSION['mensaje_ocupar'] ?? '';
unset($_SESSION['mensaje_ocupar']);

$autos = [];
$ocupaciones = [];
$callesProhibidas = [];
$usuario = ['id' => 0];

try {
    // Buscar usuario.
    $stmt = $conexion->prepare(
        'SELECT id FROM usuario WHERE nombre_usuario = :usuario'
    );
    $stmt->execute([':usuario' => $_SESSION['usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: cerrar_sesion.php');
        exit();
    }

    // Obtener las calles marcadas como prohibidas.
    $sql = "SELECT id_osm FROM calles_prohibidas";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $callesProhibidas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Vehículos del usuario.
    $stmtAuto = $conexion->prepare(
        'SELECT id, marca, modelo, color
         FROM automovil
         WHERE id_usuario = :usuario
         ORDER BY marca, modelo'
    );
    $stmtAuto->execute([':usuario' => $usuario['id']]);
    $autos = $stmtAuto->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ocupaciones ACTIVAS de todos los usuarios.
     *
     * Horario normal: estacionamiento <= hora actual < liberación.
     * Cruza medianoche: hora actual >= estacionamiento
     *                    OR hora actual < liberación.
     * Horas iguales: duración cero; no se considera activa.
     */
    $stmtOcupaciones = $conexion->prepare("
        SELECT
            o.id,
            o.id_automovil,
            o.hora_estacionamiento,
            o.hora_liberacion,
            o.calle,
            o.latitud,
            o.longitud,
            a.marca,
            a.modelo,
            a.color
        FROM ocupa o
        INNER JOIN automovil a ON a.id = o.id_automovil
        WHERE o.latitud IS NOT NULL
          AND o.longitud IS NOT NULL
          AND o.latitud BETWEEN -90 AND 90
          AND o.longitud BETWEEN -180 AND 180
          AND o.latitud <> 0
          AND o.longitud <> 0
          AND o.hora_estacionamiento IS NOT NULL
          AND o.hora_liberacion IS NOT NULL
          AND o.hora_estacionamiento <> o.hora_liberacion
          AND (
              (
                  o.hora_estacionamiento < o.hora_liberacion
                  AND CURTIME() >= o.hora_estacionamiento
                  AND CURTIME() < o.hora_liberacion
              )
              OR
              (
                  o.hora_estacionamiento > o.hora_liberacion
                  AND (
                      CURTIME() >= o.hora_estacionamiento
                      OR CURTIME() < o.hora_liberacion
                  )
              )
          )
    ");

    $stmtOcupaciones->execute();
    $ocupaciones = $stmtOcupaciones->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Error en ocupar_lugar.php: ' . $e->getMessage());

    $autos = [];
    $ocupaciones = [];
    $callesProhibidas = [];
    $usuario = ['id' => 0];

    $mensaje = 'No se pudieron cargar los datos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ocupar lugar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="disenio.css">

    <style>
        .ocupar-wrap {
            max-width: 1400px;
            margin: 28px auto;
            padding: 0 18px;
        }

        .ocupar-grid {
            display: grid;
            grid-template-columns: minmax(300px, 380px) minmax(0, 1fr);
            gap: 22px;
            align-items: start;
        }

        .panel-ocupar {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 18px #00000012;
        }

        .panel-ocupar label {
            font-weight: 600;
            margin: 12px 0 6px;
        }

        .panel-ocupar .form-control {
            margin-bottom: 8px;
        }

        #mapa-estacionamiento {
            height: 600px;
            width: 100%;
            border-radius: 12px;
            z-index: 1;
        }

        .leyenda {
            display: flex;
            flex-wrap: wrap;
            gap: 9px 14px;
            margin-top: 12px;
            font-size: .88rem;
        }

        .leyenda span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .muestra {
            display: inline-block;
            width: 24px;
            height: 6px;
            border-radius: 4px;
        }

        .nota-demo {
            font-size: .85rem;
            color: #755b18;
            background: #fff7dc;
            padding: 10px 12px;
            border-radius: 8px;
            margin-top: 12px;
        }

        .estado-mapa {
            font-size: .9rem;
            color: #555;
            margin: 8px 0;
        }

        .btn-ocupar {
            margin-top: 18px;
            width: 100%;
        }

        .icono-auto-estacionado {
            background: transparent;
            border: none;
        }

        .popup-auto {
            min-width: 190px;
            line-height: 1.6;
        }

        .popup-auto hr {
            margin: 6px 0;
        }

        @media (max-width: 900px) {
            .ocupar-grid {
                grid-template-columns: 1fr;
            }

            #mapa-estacionamiento {
                height: 430px;
            }
        }
    </style>
</head>

<body>
<div class="container-fluid p-0">

    <nav class="tarjetaArriba-cuenta">
        <a href="principal.php">
            <p class="textoBlanco-principal">Página Principal</p>
        </a>
        <a href="registra_auto.php">
            <p class="textoBlanco-principal">Registrar vehículo</p>
        </a>
        <a href="ocupar_lugar.php">
            <p class="textoBlanco-principal">Ocupar lugar</p>
        </a>
        <a href="cuenta.php">
            <p class="textoBlanco-principal">Cuenta</p>
        </a>
    </nav>

    <main class="ocupar-wrap">
        <h1 class="mb-2">Ocupar lugar</h1>
        <p class="mb-4">
            Elegí la hora, tu vehículo y una calle cercana al Liceo Logosófico.
        </p>

        <?php if ($mensaje): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="ocupar-grid">

            <section class="panel-ocupar">
                <h4>Datos del estacionamiento</h4>

                <form action="guardar_ocupacion.php"
                      method="post"
                      id="form-ocupar">

                    <label for="hora">Hora a la que vas a estacionar</label>
                    <input type="time"
                           class="form-control"
                           id="hora"
                           name="hora"
                           readonly>

                    <label for="hora_liberacion">Hora de liberación</label>
                    <input type="time"
                           id="hora_liberacion"
                           name="hora_liberacion"
                           class="form-control"
                           required>

                    <label for="vehiculo">Vehículo</label>
                    <select class="form-select"
                            id="vehiculo"
                            name="vehiculo"
                            required
                            <?= count($autos) ? '' : 'disabled' ?>>

                        <option value="">Seleccioná tu auto</option>

                        <?php foreach ($autos as $auto): ?>
                            <option value="<?= (int)$auto['id'] ?>">
                                <?= htmlspecialchars(
                                    $auto['marca'] . ' ' .
                                    $auto['modelo'] . ' · ' .
                                    $auto['color'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if (!count($autos)): ?>
                        <div class="form-text text-danger">
                            No tenés autos registrados.
                            Registrá uno antes de continuar.
                        </div>
                    <?php endif; ?>

                    <label for="calle">Calle seleccionada</label>
                    <input type="text"
                           class="form-control"
                           id="calle"
                           name="calle"
                           placeholder="Escribí la calle o seleccionala en el mapa"
                           required
                           maxlength="150">

                    <div class="form-text">
                        También podés hacer clic en una calle coloreada del mapa.
                    </div>

                    <input type="hidden" id="latitud" name="latitud">
                    <input type="hidden" id="longitud" name="longitud">

                    <button type="submit"
                            class="btn btn-primary btn-ocupar"
                            <?= count($autos) ? '' : 'disabled' ?>>
                        Confirmar ocupación
                    </button>
                </form>

                <div class="nota-demo">
                    <strong>Importante:</strong>
                    los colores de disponibilidad son ilustrativos y no representan
                    datos reales de estacionamiento.
                </div>
            </section>

            <section class="panel-ocupar">
                <h4>Elegí una calle en el mapa</h4>
                <p class="text-muted mb-2">
                    Zona alrededor del Liceo Logosófico,
                    Av. Dr. Manuel Albo 2707, Montevideo.
                </p>

                <div id="mapa-estacionamiento"
                     aria-label="Mapa interactivo para seleccionar calle"></div>

                <div id="estado-mapa" class="estado-mapa">
                    Preparando el mapa...
                </div>

                <div class="leyenda" aria-label="Leyenda de disponibilidad">
                    <span><i class="muestra" style="background:#16a34a"></i>Alta (75–100%)</span>
                    <span><i class="muestra" style="background:#eab308"></i>Media (50–74%)</span>
                    <span><i class="muestra" style="background:#f97316"></i>Media-baja (30–49%)</span>
                    <span><i class="muestra" style="background:#dc2626"></i>Baja (0–29%)</span>
                    <span><i class="muestra" style="background:#2563eb"></i>Prohibida</span>
                </div>
            </section>

        </div>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
        const ahora = new Date();

    const hora = String(ahora.getHours()).padStart(2, '0');
    const minutos = String(ahora.getMinutes()).padStart(2, '0');

    document.getElementById("hora").value = `${hora}:${minutos}`;
    
(() => {
    const centro = [-34.89071, -56.15986];

    const limites = L.latLngBounds(
        [-34.8952, -56.1653],
        [-34.8862, -56.1544]
    );

    const mapa = L.map('mapa-estacionamiento', {
        scrollWheelZoom: true,
        maxBounds: limites,
        maxBoundsViscosity: 1,
        minZoom: 15,
        maxZoom: 19
    }).fitBounds(limites);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapa);

    L.marker(centro)
        .addTo(mapa)
        .bindPopup('<strong>Liceo Logosófico</strong><br>Av. Dr. Manuel Albo 2707');

    const estado = document.getElementById('estado-mapa');
    const calleInput = document.getElementById('calle');
    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');

    let seleccionado = null;
    let capaCalles = null;
    let lugaresDemanda = [];

    // ==========================================
    // CONFIGURACIÓN DE CACHÉ
    // ==========================================

    const claveCache = 'calles_mapa_estacionamiento_v3';
    const duracionCache = 30 * 60 * 1000;

    // ==========================================
    // CALLES PROHIBIDAS
    // ==========================================

    const callesProhibidas = new Set(
        <?= json_encode(
            $callesProhibidas,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) ?: '[]' ?>.map(Number)
    );

    // ==========================================
    // FUNCIONES AUXILIARES
    // ==========================================

    function escaparHTML(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[caracter]);
    }

    function distanciaMetros(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const rad = grados => grados * Math.PI / 180;

        const dLat = rad(lat2 - lat1);
        const dLon = rad(lon2 - lon1);

        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos(rad(lat1)) *
            Math.cos(rad(lat2)) *
            Math.sin(dLon / 2) ** 2;

        return R * 2 * Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );
    }

    // ==========================================
    // DEMANDA SEGÚN HORARIO
    // ==========================================

    function obtenerDemandaHorario(tipo, fecha) {
        const hora = fecha.getHours();
        const minutos = fecha.getMinutes();
        const dia = fecha.getDay();

        const horario = hora + minutos / 60;
        const diaLaboral = dia >= 1 && dia <= 5;
        const sabado = dia === 6;

        switch (tipo) {

            case 'school':
            case 'college':
            case 'university':
            case 'kindergarten':

                if (!diaLaboral) return 10;

                if (
                    (horario >= 7 && horario <= 9) ||
                    (horario >= 11.5 && horario <= 14) ||
                    (horario >= 16 && horario <= 18)
                ) {
                    return 90;
                }

                return 15;

            case 'hospital':
                return 85;

            case 'clinic':
            case 'doctors':
                return horario >= 8 && horario <= 19 ? 70 : 25;

            case 'commercial':
            case 'marketplace':
            case 'restaurant':
            case 'fast_food':

                if (diaLaboral && horario >= 9 && horario <= 20) {
                    return 70;
                }

                if (sabado && horario >= 10 && horario <= 22) {
                    return 80;
                }

                return 25;

            case 'bus_station':
                return 65;

            default:
                return 20;
        }
    }

    // ==========================================
    // CALCULAR DISPONIBILIDAD DE CADA CALLE
    // ==========================================

    function clasificar(feature) {

        const idOSM = Number(feature.properties.id);

        // Si el tramo está prohibido, siempre será azul.
        if (callesProhibidas.has(idOSM)) {
            return {
                color: '#2563eb',
                nombre: 'Prohibida',
                disponibilidad: 0,
                prohibida: true
            };
        }

        const coordenadas = feature.geometry.coordinates;

        const lat = coordenadas.reduce(
            (suma, p) => suma + p[1], 0
        ) / coordenadas.length;

        const lng = coordenadas.reduce(
            (suma, p) => suma + p[0], 0
        ) / coordenadas.length;

        const ahora = new Date();

        let demandaTotal = 0;
        let pesoTotal = 0;

        lugaresDemanda.forEach(lugar => {

            const distancia = distanciaMetros(
                lat,
                lng,
                lugar.lat,
                lugar.lng
            );

            if (distancia > 500) return;

            const demanda = obtenerDemandaHorario(
                lugar.tipo,
                ahora
            );

            const influencia = 1 - distancia / 500;

            demandaTotal += demanda * influencia;
            pesoTotal += influencia;
        });

        // Promedio ponderado en lugar de sumar todas las demandas.
        let demandaPromedio = pesoTotal > 0
            ? demandaTotal / pesoTotal
            : 20;

        // Reducir la influencia general para evitar saturación.
        demandaPromedio = demandaPromedio * 0.65;

        const disponibilidad = 100 - demandaPromedio;

        if (disponibilidad >= 75) {
            return {
                color: '#16a34a',
                nombre: 'Alta',
                disponibilidad
            };
        }

        if (disponibilidad >= 50) {
            return {
                color: '#eab308',
                nombre: 'Media',
                disponibilidad
            };
        }

        if (disponibilidad >= 30) {
            return {
                color: '#f97316',
                nombre: 'Media-baja',
                disponibilidad
            };
        }

        return {
            color: '#dc2626',
            nombre: 'Baja',
            disponibilidad
        };
    }

    // ==========================================
    // SELECCIONAR UNA CALLE
    // ==========================================

    function seleccionar(feature, layer) {

        const props = feature.properties || {};
        const tags = props.tags || {};

        const nombre =
            tags.name ||
            props.name ||
            tags['name:es'] ||
            'Calle seleccionada (sin nombre en OSM)';

        const idOSM = Number(props.id);
        const c = clasificar(feature);

        const popup = document.createElement('div');

        popup.innerHTML = `
            <strong>${escaparHTML(nombre)}</strong><br>

            <strong>ID OpenStreetMap:</strong> ${idOSM}<br>

            <strong>Estado:</strong>
            ${c.prohibida
                ? 'Prohibida para estacionar'
                : 'Sin restricción registrada en el sistema'
            }<br>

            <strong>Disponibilidad:</strong>
            ${escaparHTML(c.nombre)}<br>

            <small>
                Disponibilidad estimada:
                ${c.disponibilidad.toFixed(0)}%
            </small><br>

            <button type="button"
                    class="btn btn-sm btn-primary mt-2">
                Elegir esta calle
            </button>
        `;

        layer.bindPopup(popup);

        function elegirCalle() {

            if (typeof layer.getBounds !== 'function') {
                return;
            }

            const ll = layer.getBounds().getCenter();

            calleInput.value = nombre;
            latInput.value = ll.lat.toFixed(7);
            lngInput.value = ll.lng.toFixed(7);

            if (seleccionado && seleccionado.setStyle) {
                capaCalles.resetStyle(seleccionado);
            }

            seleccionado = layer;

            // Mantener el color original del tramo.
            const estilo = clasificar(feature);

            layer.setStyle({
                weight: 8,
                color: estilo.color
            });
        }

        layer.on('click', elegirCalle);

        layer.on('popupopen', () => {
            const boton = popup.querySelector('button');

            if (boton) {
                boton.onclick = elegirCalle;
            }
        });
    }

    // ==========================================
    // OCUPACIONES ACTIVAS
    // ==========================================

    const ocupaciones = <?= json_encode(
        $ocupaciones,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?: '[]' ?>;

    const coloresAutos = [
        '#2563eb', '#dc2626', '#16a34a', '#9333ea',
        '#ea580c', '#0891b2', '#be185d', '#475569'
    ];

    function crearIconoAuto(color) {

        const dibujo = `
            <svg xmlns="http://www.w3.org/2000/svg"
                 width="42" height="48" viewBox="0 0 42 48">

                <ellipse cx="21" cy="24" rx="19" ry="22"
                         fill="#fff" opacity="0.95"/>

                <g stroke="#1f2937" stroke-width="1.5"
                   stroke-linejoin="round">

                    <rect x="5" y="13" width="6" height="10"
                          rx="2" fill="#111827"/>

                    <rect x="31" y="13" width="6" height="10"
                          rx="2" fill="#111827"/>

                    <rect x="5" y="28" width="6" height="10"
                          rx="2" fill="#111827"/>

                    <rect x="31" y="28" width="6" height="10"
                          rx="2" fill="#111827"/>

                    <path d="M13 5 Q21 2 29 5 L34 13 L35 35
                             L29 43 Q21 46 13 43 L7 35 L8 13 Z"
                          fill="${color}"/>

                    <path d="M13 10 Q21 7 29 10 L31 17 L11 17 Z"
                          fill="#bfdbfe"/>

                    <path d="M11 20 L31 20 L31 30 L11 30 Z"
                          fill="#dbeafe"/>

                    <path d="M11 32 L31 32 L29 39
                             Q21 42 13 39 Z"
                          fill="#bfdbfe"/>

                    <path d="M9 12 L12 10 M30 10 L33 12"
                          fill="none" stroke="#fef3c7"
                          stroke-width="2.5"/>

                    <path d="M9 36 L12 38 M30 38 L33 36"
                          fill="none" stroke="#ef4444"
                          stroke-width="2.5"/>
                </g>
            </svg>
        `;

        return L.divIcon({
            html: dibujo,
            className: 'icono-auto-estacionado',
            iconSize: [42, 48],
            iconAnchor: [21, 24],
            popupAnchor: [0, -22]
        });
    }

    ocupaciones.forEach(auto => {

        const lat = Number(auto.latitud);
        const lng = Number(auto.longitud);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
        if (lat === 0 || lng === 0) return;

        const indice =
            Math.abs(Number(auto.id_automovil)) % coloresAutos.length;

        const marcaModelo = [auto.marca, auto.modelo]
            .filter(Boolean)
            .join(' ');

        const popup = `
            <div class="popup-auto">
                <strong>${escaparHTML(marcaModelo || 'Automóvil')}</strong>
                <hr>
                <div><strong>Color:</strong>
                    ${escaparHTML(auto.color || 'No especificado')}
                </div>
                <div><strong>Calle:</strong>
                    ${escaparHTML(auto.calle || 'No especificada')}
                </div>
                <div><strong>Estacionado:</strong>
                    ${escaparHTML(String(auto.hora_estacionamiento).substring(0, 5))}
                </div>
                <div><strong>Se libera:</strong>
                    ${escaparHTML(String(auto.hora_liberacion).substring(0, 5))}
                </div>
            </div>
        `;

        L.marker([lat, lng], {
            icon: crearIconoAuto(coloresAutos[indice]),
            title: marcaModelo || 'Automóvil estacionado',
            zIndexOffset: 500
        })
        .addTo(mapa)
        .bindPopup(popup);
    });

    // ==========================================
    // CONSULTA A OPENSTREETMAP
    // ==========================================

    const query =
        '[out:json][timeout:25];' +

        // Calles dentro de los límites del mapa.
        'way["highway"~' +
        '"^(residential|tertiary|secondary|primary|unclassified|living_street|service)$"]' +
        '(-34.8952,-56.1653,-34.8862,-56.1544);' +
        'out geom tags;' +

        // Lugares de interés: área ampliada unos 500 metros.
        '(' +

        'nwr["amenity"~"school|hospital|clinic|doctors|university|college|kindergarten|marketplace|restaurant|fast_food|bus_station"]' +
        '(-34.9002,-56.1708,-34.8812,-56.1489);' +

        // Limitar los comercios para reducir la carga de Overpass.
        'nwr["shop"~"supermarket|convenience|mall"]' +
        '(-34.9002,-56.1708,-34.8812,-56.1489);' +

        ');' +

        'out center tags;';

    // ==========================================
    // DIBUJAR CALLES
    // ==========================================

    function dibujarCalles(features, lugares = []) {

        lugaresDemanda = lugares;

        if (capaCalles) {
            mapa.removeLayer(capaCalles);
        }

        capaCalles = L.geoJSON(
            {
                type: 'FeatureCollection',
                features
            },
            {
                style: feature => {

                    const c = clasificar(feature);

                    return {
                        color: c.color,
                        weight: 5,
                        opacity: 0.88
                    };
                },

                onEachFeature: seleccionar
            }
        ).addTo(mapa);

        estado.textContent =
            'Calles cargadas. Seleccioná una línea para elegirla.';
    }

    // ==========================================
    // OBTENER DATOS DE OVERPASS
    // ==========================================

    async function obtenerCalles() {

        const servidores = [
            'https://overpass.private.coffee/api/interpreter',
            'https://overpass-api.de/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter'
        ];

        let ultimoError = null;

        for (const servidor of servidores) {

            try {

                console.log('Intentando cargar datos:', servidor);

                const respuesta = await fetch(servidor, {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/x-www-form-urlencoded;charset=UTF-8'
                    },

                    body: 'data=' + encodeURIComponent(query),
                    signal: AbortSignal.timeout(45000)
                });

                if (!respuesta.ok) {
                    throw new Error(`Error HTTP ${respuesta.status}`);
                }

                const data = await respuesta.json();

                if (!data || !Array.isArray(data.elements)) {
                    throw new Error('Respuesta inválida de Overpass');
                }

                console.log(
                    'Elementos recibidos:',
                    data.elements.length
                );

                return data;

            } catch (error) {

                ultimoError = error;

                console.warn(
                    'Falló el servidor:',
                    servidor,
                    error
                );
            }
        }

        throw ultimoError ||
            new Error('No se pudieron obtener los datos');
    }

    // ==========================================
    // CARGAR CALLES Y LUGARES
    // ==========================================

    async function cargarCalles(forzarConsulta = false) {

        let guardado = null;

        try {

            guardado = JSON.parse(
                localStorage.getItem(claveCache) || 'null'
            );

        } catch (error) {
            console.warn('No se pudo leer la caché.');
        }

        const cacheValida =
            guardado &&
            Array.isArray(guardado.features) &&
            guardado.features.length > 0;

        // Cargar datos guardados.
        if (!forzarConsulta && cacheValida) {

            dibujarCalles(
                guardado.features,
                guardado.lugares || []
            );

            if (Date.now() - guardado.fecha < duracionCache) {
                return;
            }

            estado.textContent =
                'Calles guardadas cargadas; actualizando...';
        }

        if (!cacheValida || forzarConsulta) {
            estado.textContent = 'Cargando calles...';
        }

        try {

            const data = await obtenerCalles();

            // Separar las calles.
            const elementosCalles = data.elements.filter(e =>
                e.type === 'way' &&
                Array.isArray(e.geometry) &&
                e.geometry.length > 1 &&
                e.tags?.highway
            );

            // Obtener hospitales, liceos y comercios.
            const nuevosLugares = data.elements
                .filter(e => {

                    const tags = e.tags || {};

                    return (
                        tags.amenity ||
                        tags.shop
                    ) && (
                        e.lat !== undefined ||
                        e.center?.lat !== undefined
                    );
                })
                .map(e => {

                    const tags = e.tags || {};

                    return {
                        lat: Number(e.lat ?? e.center.lat),
                        lng: Number(e.lon ?? e.center.lon),
                        tipo: tags.amenity || 'commercial'
                    };
                })
                .filter(l =>
                    Number.isFinite(l.lat) &&
                    Number.isFinite(l.lng)
                );

            // Convertir las calles a GeoJSON.
            const features = elementosCalles.map(e => ({

                type: 'Feature',

                properties: {
                    id: e.id,
                    tags: e.tags || {}
                },

                geometry: {
                    type: 'LineString',

                    coordinates: e.geometry.map(p => [
                        p.lon,
                        p.lat
                    ])
                }
            }));

            if (!features.length) {
                throw new Error('No se encontraron calles.');
            }

            // Dibujar calles y lugares.
            dibujarCalles(features, nuevosLugares);

            // Guardar todo en la caché.
            localStorage.setItem(
                claveCache,
                JSON.stringify({
                    fecha: Date.now(),
                    features,
                    lugares: nuevosLugares
                })
            );

            console.log('Calles:', features.length);
            console.log('Lugares de demanda:', nuevosLugares.length);

        } catch (error) {

            console.error('Error al cargar calles:', error);

            if (cacheValida) {

                dibujarCalles(
                    guardado.features,
                    guardado.lugares || []
                );

                estado.textContent =
                    'Calles cargadas desde caché. No se pudo actualizar.';

            } else {

                estado.textContent =
                    'No se pudieron cargar las calles. Revisá la conexión e intentá recargar.';
            }
        }
    }

    // ==========================================
    // RECARGAR MAPA
    // ==========================================

    estado.style.cursor = 'pointer';
    estado.title = 'Hacé clic para volver a cargar las calles';

    estado.addEventListener('click', () => {
        cargarCalles(true);
    });

    cargarCalles();

    // ==========================================
    // VALIDAR CALLE SELECCIONADA
    // ==========================================

    calleInput.addEventListener('input', () => {
        latInput.value = '';
        lngInput.value = '';
    });

    document.getElementById('form-ocupar')
        .addEventListener('submit', e => {

            if (!calleInput.value.trim()) {
                e.preventDefault();
                calleInput.focus();

                alert(
                    'Ingresá una calle o seleccioná una en el mapa.'
                );
            }
        });

})();
</script>
</body>
</html>
