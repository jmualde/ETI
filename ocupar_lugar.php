<?php
session_start();
require_once 'conexion.php';

date_default_timezone_set('America/Montevideo');
$horaActual = date('H:i');

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$mensaje = $_SESSION['mensaje_ocupar'] ?? '';
unset($_SESSION['mensaje_ocupar']);

// Liberar el bloqueo de la sesión para no frenar otras páginas.
// ($_SESSION sigue pudiéndose leer; solo deja de guardarse.)
session_write_close();

$autos = [];
$ocupaciones = [];
$tramosNoDisponibles = [];
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
    $usuario = ['id' => 0];
    $mensaje = 'No se pudieron cargar los datos.';
}

// Cargar los ID de los tramos marcados manualmente como no disponibles.
// Esta consulta es independiente para que un error en esta tabla
// no impida cargar los vehículos ni las ocupaciones.
try {
    $stmtTramos = $conexion->query(
        'SELECT id_tramo FROM tramos_no_disponibles'
    );
    $tramosNoDisponibles = $stmtTramos->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log('Error cargando tramos no disponibles: ' . $e->getMessage());
    $tramosNoDisponibles = [];
}

// Minutos que un lugar liberado se muestra en el mapa.
const MINUTOS_LIBERADO = 5;

$misOcupaciones = [];
$liberados = [];

// Consulta independiente: si falta la tabla, el resto de la página sigue andando.
try {
    $condicionActiva = "
        o.latitud IS NOT NULL
        AND o.longitud IS NOT NULL
        AND o.hora_estacionamiento IS NOT NULL
        AND o.hora_liberacion IS NOT NULL
        AND o.hora_estacionamiento <> o.hora_liberacion
        AND (
            (o.hora_estacionamiento < o.hora_liberacion
             AND CURTIME() >= o.hora_estacionamiento
             AND CURTIME() <  o.hora_liberacion)
            OR
            (o.hora_estacionamiento > o.hora_liberacion
             AND (CURTIME() >= o.hora_estacionamiento
                  OR CURTIME() <  o.hora_liberacion))
        )";

    // Ocupaciones activas del usuario (para el botón "Liberé mi lugar").
    if (!empty($usuario['id'])) {
        $stmtMis = $conexion->prepare("
            SELECT o.id, o.calle, o.hora_liberacion, a.marca, a.modelo
            FROM ocupa o
            INNER JOIN automovil a ON a.id = o.id_automovil
            WHERE o.id_usuario = :usuario AND $condicionActiva
            ORDER BY o.id DESC
        ");
        $stmtMis->execute([':usuario' => $usuario['id']]);
        $misOcupaciones = $stmtMis->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lugares liberados recientemente (de todos los usuarios).
    $stmtLib = $conexion->prepare("
        SELECT id, calle, latitud, longitud,
               TIMESTAMPDIFF(MINUTE, liberado_en, NOW()) AS minutos
        FROM lugares_liberados
        WHERE liberado_en >= NOW() - INTERVAL " . (int)MINUTOS_LIBERADO . " MINUTE
        ORDER BY liberado_en DESC
        LIMIT 100
    ");
    $stmtLib->execute();
    $liberados = $stmtLib->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Error cargando lugares liberados: ' . $e->getMessage());
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
            color: black;
        }

        .panel-ocupar {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 18px #00000012;

            h4{
                color: black;
            }
        }

        .panel-ocupar label {
            font-weight: 600;
            margin: 12px 0 6px;
        }

        .panel-ocupar .form-control {
            margin-bottom: 8px;



            label{
                color: black;
            }
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

        .mis-lugares {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid #0000001f;
        }

        .mis-lugares .item-lugar {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 10px 12px;
            margin-top: 8px;
            font-size: .9rem;
        }

        .icono-liberado {
            background: transparent;
            border: none;
        }

        .liberado-pin {
            position: relative;
            width: 44px;
            height: 44px;
        }

        .liberado-pin .pulso {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: #22c55e;
            opacity: .6;
            animation: pulso-liberado 1.6s ease-out infinite;
        }

        .liberado-pin .centro {
            position: absolute;
            inset: 7px;
            border-radius: 50%;
            background: #16a34a;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px #0000006b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
        }

        @keyframes pulso-liberado {
            0%   { transform: scale(.55); opacity: .85; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            .liberado-pin .pulso { animation: none; opacity: .35; }
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

                <form action="guardar_ocupacion.php" method="post" id="form-ocupar" class="form-control">

                    <label for="hora">Hora de estacionamiento</label>

                    <input type="time"
                           class="form-control"
                           id="hora"
                           name="hora"
                           value="<?= htmlspecialchars($horaActual, ENT_QUOTES, 'UTF-8') ?>"
                           readonly
                           required
                           disabled>

                    <p class="text-secondary mt-1">
                        La hora se establece automáticamente.
                    </p>

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
                    <input type="hidden" id="id_tramo" name="id_tramo">

                    <button type="submit"
                            class="btn btn-primary btn-ocupar"
                            <?= count($autos) ? '' : 'disabled' ?>>
                        Confirmar ocupación
                    </button>
                </form>

                <div class="mis-lugares">
                    <h5>Liberé mi lugar</h5>

                    <?php if (!count($misOcupaciones)): ?>
                        <p class="text-muted mb-0" style="font-size:.9rem">
                            No tenés ningún lugar ocupado ahora.
                        </p>
                    <?php else: ?>
                        <p class="text-muted mb-1" style="font-size:.85rem">
                            Si ya te vas, avisá para que otros vean el lugar libre.
                        </p>

                        <?php foreach ($misOcupaciones as $mi): ?>
                            <form action="guardar_liberacion.php"
                                  method="post"
                                  class="item-lugar">
                                <div>
                                    <strong><?= htmlspecialchars($mi['marca'] . ' ' . $mi['modelo'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <?= htmlspecialchars($mi['calle'], ENT_QUOTES, 'UTF-8') ?>
                                    · hasta <?= htmlspecialchars(substr($mi['hora_liberacion'], 0, 5), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <input type="hidden"
                                       name="id_ocupacion"
                                       value="<?= (int)$mi['id'] ?>">
                                <button type="submit"
                                        class="btn btn-success btn-sm mt-2 w-100">
                                    Avisar que liberé este lugar
                                </button>
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="nota-demo">
                    <strong>Importante:</strong>
                    los colores de disponibilidad son estimaciones y pueden
                    no coincidir con la situación real de la calle.
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
                    <span><i class="muestra" style="background:#16a34a"></i>Alta (80–99%)</span>
                    <span><i class="muestra" style="background:#eab308"></i>Media (50–79%)</span>
                    <span><i class="muestra" style="background:#f97316"></i>Media-baja (30–49%)</span>
                    <span><i class="muestra" style="background:#dc2626"></i>Baja (1–29%)</span>
                    <span><i class="muestra" style="background:#2563eb"></i>No disponible</span>
                    <span><i class="muestra" style="background:#9ca3af"></i>Sin datos</span>
                    <span><i style="display:inline-block;width:14px;height:14px;border-radius:50%;background:#16a34a;border:2px solid #fff;box-shadow:0 0 0 2px #22c55e"></i>Lugar recientemente liberado</span>
                </div>
            </section>

        </div>
    </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(() => {
    const centro = [-34.89071, -56.15986];

    // [sur, oeste] y [norte, este]
    const BBOX = [-34.8952, -56.1653, -34.8862, -56.1544];

    const limites = L.latLngBounds(
        [BBOX[0], BBOX[1]],
        [BBOX[2], BBOX[3]]
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
    const tramoInput = document.getElementById('id_tramo');

    // Cancela todos los pedidos en curso cuando el usuario se va de la página.
    const controladorPagina = new AbortController();
    window.addEventListener('pagehide', () => controladorPagina.abort());

    let seleccionado = null;
    let capaCalles = null;

    let temporizadorBusqueda = null;
    let marcadorManual = null;
    let numeroBusqueda = 0;

    // Probabilidades reales devueltas por calcular_probabilidades.php
    // Formato: { idTramo: 0..100 }
    let probabilidadesTramos = {};
    let fallaronProbabilidades = false;
    let ultimaFuente = 'servidor'; // de dónde vinieron las últimas calles

    const colores = [
        {min: 80, color: '#16a34a', nombre: 'Alta'},
        {min: 50, color: '#eab308', nombre: 'Media'},
        {min: 30, color: '#f97316', nombre: 'Media-baja'},
        {min: 0, color: '#dc2626', nombre: 'Baja'}
    ];

    function escaparHTML(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[caracter]);
    }

    async function buscarCoordenadas(calle, busquedaActual) {
        const url = new URL(
            'https://nominatim.openstreetmap.org/search'
        );

        url.searchParams.set('format', 'jsonv2');
        url.searchParams.set('q', calle + ', Montevideo, Uruguay');
        url.searchParams.set('limit', '5');
        url.searchParams.set('countrycodes', 'uy');
        url.searchParams.set('viewbox', '-56.1653,-34.8862,-56.1544,-34.8952');
        url.searchParams.set('bounded', '1');

        estado.textContent = 'Buscando la ubicación de la calle...';

        try {
            const respuesta = await fetch(url);

            if (!respuesta.ok) {
                throw new Error('No se pudo consultar OpenStreetMap.');
            }

            const resultados = await respuesta.json();

            // Evitar que una búsqueda anterior sobrescriba la más reciente.
            if (busquedaActual !== numeroBusqueda) {
                return;
            }

            if (!resultados.length) {
                latInput.value = '';
                lngInput.value = '';

                estado.textContent =
                    'No se encontró esa calle dentro de la zona del mapa.';
                return;
            }

            // Buscar un resultado que esté dentro de los límites del mapa.
            const resultado = resultados.find(r => {
                const punto = L.latLng(
                    Number(r.lat),
                    Number(r.lon)
                );

                return limites.contains(punto);
            });

            if (!resultado) {
                latInput.value = '';
                lngInput.value = '';

                estado.textContent =
                    'La calle no se encuentra dentro de la zona permitida.';
                return;
            }

            const lat = Number(resultado.lat);
            const lng = Number(resultado.lon);

            // Verificar que el punto no caiga en un tramo no disponible.
            const cercano = tramoMasCercano(L.latLng(lat, lng));

            if (cercano && estaBloqueado(cercano.id)) {
                latInput.value = '';
                lngInput.value = '';
                tramoInput.value = '';
                estado.textContent = MENSAJE_BLOQUEADO;
                return;
            }

            tramoInput.value = cercano ? cercano.id : '';

            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);

            // Eliminar el marcador manual anterior.
            if (marcadorManual) {
                mapa.removeLayer(marcadorManual);
            }

            const punto = L.latLng(lat, lng);

            marcadorManual = L.marker(punto)
                .addTo(mapa)
                .bindPopup(`
                    <strong>${escaparHTML(calle)}</strong>
                    <p>Ubicación encontrada para el estacionamiento.</p>
                `);

            mapa.setView(punto, 18);
            marcadorManual.openPopup();

            estado.textContent =
                'Ubicación encontrada. Ya podés confirmar la ocupación.';

        } catch (error) {
            if (busquedaActual !== numeroBusqueda) {
                return;
            }

            console.error('Error buscando la calle:', error);

            latInput.value = '';
            lngInput.value = '';

            estado.textContent =
                'No se pudo buscar la calle. Intentá nuevamente.';
        }
    }

    // ID de los tramos que se marcaron como no disponibles en MySQL.
    // PDO puede devolverlos como texto, por eso los convertimos a Number.
    const tramosNoDisponibles = new Set(
        (<?= json_encode(
            $tramosNoDisponibles,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) ?: '[]' ?>).map(Number)
    );

    function clasificar(id, tags) {
        const idTramo = Number(id);

        // La restricción manual tiene prioridad.
        if (tramosNoDisponibles.has(idTramo)) {
            return {
                color: '#2563eb',
                nombre: 'No disponible',
                texto: 'Este tramo está marcado manualmente como no disponible'
            };
        }

        const p = probabilidadesTramos[idTramo];

        if (typeof p !== 'number' || !Number.isFinite(p)) {
            return {
                color: '#9ca3af',
                nombre: 'Sin datos',
                texto: 'Todavía no hay datos suficientes'
            };
        }

        const nivel = colores.find(c => p >= c.min) || colores[3];

        return {
            ...nivel,
            texto: `Probabilidad estimada: ${Math.round(p)}%`
        };
    }

    function estaBloqueado(id) {
        return tramosNoDisponibles.has(Number(id));
    }

    const MENSAJE_BLOQUEADO =
        'Esa calle está marcada como no disponible y no se puede ocupar.';

    // Busca el tramo dibujado más cercano a un punto (máx. 30 m).
    function tramoMasCercano(latlng) {
        if (!capaCalles) return null;

        const zoom = 18;
        const p = mapa.project(latlng, zoom);
        const metrosPorPixel =
            156543.03 * Math.cos(latlng.lat * Math.PI / 180) / Math.pow(2, zoom);

        let mejor = null;

        capaCalles.eachLayer(layer => {
            if (typeof layer.getLatLngs !== 'function' || !layer.feature) return;

            const pts = layer.getLatLngs().map(ll => mapa.project(ll, zoom));

            for (let i = 1; i < pts.length; i++) {
                const d = L.LineUtil.pointToSegmentDistance(p, pts[i - 1], pts[i]) *
                    metrosPorPixel;

                if (d <= 30 && (!mejor || d < mejor.distancia)) {
                    mejor = {id: layer.feature.properties.id, distancia: d};
                }
            }
        });

        return mejor;
    }

    function seleccionar(feature, layer) {
        const props = feature.properties || {};
        const tags = props.tags || {};

        const nombre = tags.name ||
            props.name ||
            tags['name:es'] ||
            'Calle seleccionada (sin nombre en OSM)';

        const c = clasificar(props.id || 1, tags);

        const popup = document.createElement('div');
        popup.innerHTML = `
            <strong>${escaparHTML(nombre)}</strong><br>
            Disponibilidad: ${escaparHTML(c.nombre)}<br>
            <small>ID del tramo OSM: ${escaparHTML(props.id)}</small><br>
            <small>${escaparHTML(c.texto)}</small><br>
            ${estaBloqueado(props.id)
                ? '<span class="text-danger">No se puede ocupar esta calle.</span>'
                : '<button type="button" class="btn btn-sm btn-primary mt-2">Elegir esta calle</button>'}
        `;

        layer.bindPopup(popup);

        function elegirCalle() {
            let ll;

            clearTimeout(temporizadorBusqueda);
            numeroBusqueda++;

            // Las calles en azul no se pueden ocupar.
            if (estaBloqueado(props.id)) {
                calleInput.value = '';
                latInput.value = '';
                lngInput.value = '';
                tramoInput.value = '';

                if (seleccionado && seleccionado.setStyle) {
                    capaCalles.resetStyle(seleccionado);
                }
                seleccionado = null;

                estado.textContent = MENSAJE_BLOQUEADO;
                return;
            }

            if (marcadorManual) {
                mapa.removeLayer(marcadorManual);
                marcadorManual = null;
            }

            if (typeof layer.getBounds === 'function') {
                ll = layer.getBounds().getCenter();
            } else {
                return;
            }

            calleInput.value = nombre;
            latInput.value = ll.lat.toFixed(7);
            lngInput.value = ll.lng.toFixed(7);
            tramoInput.value = props.id;

            if (seleccionado && seleccionado.setStyle) {
                capaCalles.resetStyle(seleccionado);
            }

            seleccionado = layer;

            if (layer.setStyle) {
                layer.setStyle({weight: 8, color: '#111827'});
            }
        }

        layer.on('click', elegirCalle);

        layer.on('popupopen', () => {
            const boton = popup.querySelector('button');
            if (boton) boton.onclick = elegirCalle;
        });
    }

    // Ocupaciones activas recibidas desde PHP.
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
                          fill="none" stroke="#fef3c7" stroke-width="2.5"/>
                    <path d="M9 36 L12 38 M30 38 L33 36"
                          fill="none" stroke="#ef4444" stroke-width="2.5"/>
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

    // Si varios autos están en el mismo punto, se separan un poco
    // para que no queden uno encima del otro.
    const posicionesUsadas = {};

    // Dibujar únicamente las ocupaciones que devolvió la consulta SQL.
    ocupaciones.forEach(auto => {
        const lat = Number(auto.latitud);
        const lng = Number(auto.longitud);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
        if (lat === 0 || lng === 0) return;

        const clave = lat.toFixed(5) + ',' + lng.toFixed(5);
        const n = posicionesUsadas[clave] = (posicionesUsadas[clave] ?? -1) + 1;

        let latMarcador = lat;
        let lngMarcador = lng;

        if (n > 0) {
            const angulo = n * 2.4;
            const radio = 0.00007; // unos 7 metros
            latMarcador += radio * Math.sin(angulo);
            lngMarcador += radio * Math.cos(angulo);
        }

        const indice =
            Math.abs(Number(auto.id_automovil)) % coloresAutos.length;

        const marcaModelo = [auto.marca, auto.modelo]
            .filter(Boolean)
            .join(' ');

        const popup = `
            <div class="popup-auto">
                <strong>${escaparHTML(marcaModelo || 'Automóvil')}</strong>
                <hr>
                <div><strong>Color:</strong> ${escaparHTML(auto.color || 'No especificado')}</div>
                <div><strong>Calle:</strong> ${escaparHTML(auto.calle || 'No especificada')}</div>
                <div><strong>Estacionado:</strong> ${escaparHTML(String(auto.hora_estacionamiento).substring(0, 5))}</div>
                <div><strong>Se libera:</strong> ${escaparHTML(String(auto.hora_liberacion).substring(0, 5))}</div>
            </div>
        `;

        L.marker([latMarcador, lngMarcador], {
            icon: crearIconoAuto(coloresAutos[indice]),
            title: marcaModelo || 'Automóvil estacionado',
            zIndexOffset: 500
        })
        .addTo(mapa)
        .bindPopup(popup);
    });

    // Lugares recién liberados (icono verde pulsante).
    const liberados = <?= json_encode(
        $liberados,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?: '[]' ?>;

    const MINUTOS_LIBERADO = <?= (int)MINUTOS_LIBERADO ?>;

    function crearIconoLiberado() {
        return L.divIcon({
            html: '<div class="liberado-pin"><div class="pulso"></div><div class="centro">P</div></div>',
            className: 'icono-liberado',
            iconSize: [44, 44],
            iconAnchor: [22, 22],
            popupAnchor: [0, -22]
        });
    }

    liberados.forEach(lugar => {
        const lat = Number(lugar.latitud);
        const lng = Number(lugar.longitud);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        if (lat === 0 || lng === 0) return;

        const minutos = Math.max(0, Number(lugar.minutos) || 0);
        const hace = minutos < 1
            ? 'hace instantes'
            : `hace ${minutos} min`;

        const popup = `
            <div class="popup-auto">
                <strong>Lugar recientemente liberado</strong>
                <hr>
                <div><strong>Calle:</strong> ${escaparHTML(lugar.calle || 'No especificada')}</div>
                <div><strong>Liberado:</strong> ${escaparHTML(hace)}</div>
                <small>Se muestra durante ${MINUTOS_LIBERADO} minutos.</small>
            </div>
        `;

        const marcadorLiberado = L.marker([lat, lng], {
            icon: crearIconoLiberado(),
            title: 'Lugar recientemente liberado',
            zIndexOffset: 1000
        })
        .addTo(mapa)
        .bindPopup(popup);

        // Quitar el icono cuando se cumplen los minutos, sin recargar la página.
        const restanteMs = Math.max(
            1000,
            (MINUTOS_LIBERADO - minutos) * 60 * 1000
        );

        setTimeout(() => mapa.removeLayer(marcadorLiberado), restanteMs);
    });

    // Consulta de calles de OpenStreetMap mediante Overpass.
    const query =
        '[out:json][timeout:25];' +
        'way["highway"~' +
        '"^(residential|tertiary|secondary|primary|unclassified|living_street|service)$"]' +
        '(-34.8952,-56.1653,-34.8862,-56.1544);out geom tags;';

    const claveCache = 'calles_mapa_estacionamiento_v1';
    const duracionCache = 30 * 60 * 1000;

    function dibujarCalles(features) {
        if (capaCalles) {
            mapa.removeLayer(capaCalles);
        }

        seleccionado = null;

        capaCalles = L.geoJSON(
            {type: 'FeatureCollection', features},
            {
                style: feature => {
                    const c = clasificar(
                        feature.properties.id,
                        feature.properties.tags
                    );

                    return {
                        color: c.color,
                        weight: 5,
                        opacity: 0.88
                    };
                },
                onEachFeature: seleccionar
            }
        ).addTo(mapa);

        estado.textContent = fallaronProbabilidades
            ? 'Calles cargadas, pero no se pudo calcular la disponibilidad (se ven en gris). Hacé clic acá para reintentar.'
            : 'Calles cargadas. Seleccioná una línea para elegirla.';
    }

    // Pide las probabilidades al servidor. Si falla, no rompe nada:
    // las calles quedan en gris ("Sin datos").
    async function cargarProbabilidades(features) {
        probabilidadesTramos = {};
        fallaronProbabilidades = false;

        // Límite de 25 segundos para que nunca quede "Calculando..." para siempre.
        const controlador = new AbortController();
        const temporizador = setTimeout(() => controlador.abort(), 25000);
        controladorPagina.signal.addEventListener(
            'abort',
            () => controlador.abort()
        );

        try {
            const tramos = features.map(feature => ({
                id: feature.properties.id,
                coordenadas: feature.geometry.coordinates.map(c => [
                    Number(c[0].toFixed(6)),
                    Number(c[1].toFixed(6))
                ])
            }));

            const respuesta = await fetch('calcular_probabilidades.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    tramos: tramos,
                    bbox: BBOX
                }),
                signal: controlador.signal
            });

            if (!respuesta.ok) {
                throw new Error(`Error HTTP ${respuesta.status}`);
            }

            const datos = await respuesta.json();

            console.log('Probabilidades calculadas:', datos);

            probabilidadesTramos = datos.probabilidades || {};

        } catch (error) {
            console.warn('No se pudieron calcular probabilidades:', error);
            probabilidadesTramos = {};
            fallaronProbabilidades = true;
        } finally {
            clearTimeout(temporizador);
        }
    }

    const esperar = ms => new Promise(r => setTimeout(r, ms));

    // Consulta a los 3 servidores Overpass AL MISMO TIEMPO
    // y usa el primero que responda bien.
    async function obtenerCallesDirecto() {
        const servidores = [
            'https://overpass.private.coffee/api/interpreter',
            'https://overpass-api.de/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter'
        ];

        const controladores = servidores.map(() => new AbortController());

        controladorPagina.signal.addEventListener('abort', () => {
            controladores.forEach(c => c.abort());
        });

        const pedidos = servidores.map(async (servidor, i) => {
            const temporizador = setTimeout(
                () => controladores[i].abort(),
                20000
            );

            try {
                const respuesta = await fetch(servidor, {
                    method: 'POST',
                    headers: {
                        'Content-Type':
                            'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: 'data=' + encodeURIComponent(query),
                    signal: controladores[i].signal
                });

                if (!respuesta.ok) {
                    throw new Error(`Error HTTP ${respuesta.status}`);
                }

                const data = await respuesta.json();

                if (!data || !Array.isArray(data.elements)) {
                    throw new Error('Respuesta inválida de Overpass');
                }

                console.log('Calles recibidas desde:', servidor);
                return data;
            } finally {
                clearTimeout(temporizador);
            }
        });

        try {
            const data = await Promise.any(pedidos);
            controladores.forEach(c => c.abort());
            return data;
        } catch (error) {
            throw new Error('Ningún servidor de calles respondió.');
        }
    }

    // Primero pide las calles a NUESTRO servidor (calles.php, con caché).
    // Si eso falla, prueba directamente con los servidores de Overpass.
    async function obtenerCalles() {
        try {
            const controlador = new AbortController();
            const temporizador = setTimeout(() => controlador.abort(), 70000);
            controladorPagina.signal.addEventListener(
                'abort',
                () => controlador.abort()
            );

            try {
                const respuesta = await fetch('calles.php', {
                    signal: controlador.signal
                });

                if (!respuesta.ok) {
                    throw new Error(`Error HTTP ${respuesta.status}`);
                }

                const data = await respuesta.json();

                if (!data || !Array.isArray(data.elements) || !data.elements.length) {
                    throw new Error('Respuesta inválida de calles.php');
                }

                ultimaFuente = 'servidor';
                return data;
            } finally {
                clearTimeout(temporizador);
            }
        } catch (error) {
            if (controladorPagina.signal.aborted) throw error;
            console.warn('calles.php falló, probando Overpass directo:', error);
            const datosDirectos = await obtenerCallesDirecto();
            ultimaFuente = 'directo';
            return datosDirectos;
        }
    }

    // Reintenta automáticamente hasta 3 veces.
    async function obtenerCallesConReintentos() {
        let ultimoError = null;

        for (let intento = 1; intento <= 3; intento++) {
            if (controladorPagina.signal.aborted) break;

            try {
                return await obtenerCalles();
            } catch (error) {
                ultimoError = error;
                console.warn(`Intento ${intento} fallido:`, error);

                if (intento < 3) {
                    estado.textContent =
                        `Reintentando cargar las calles (${intento + 1}/3)...`;
                    await esperar(2000);
                }
            }
        }

        throw ultimoError || new Error('Carga cancelada.');
    }

    function convertirAFeatures(data) {
        return data.elements
            .filter(e =>
                e.type === 'way' &&
                Array.isArray(e.geometry) &&
                e.geometry.length > 1
            )
            .map(e => ({
                type: 'Feature',
                properties: {
                    id: e.id,
                    tags: e.tags || {}
                },
                geometry: {
                    type: 'LineString',
                    coordinates: e.geometry.map(p => [p.lon, p.lat])
                }
            }));
    }

    async function cargarCallesInterno(forzarConsulta = false) {
        let guardado = null;

        try {
            guardado = JSON.parse(
                localStorage.getItem(claveCache) || 'null'
            );
        } catch (error) {
            console.warn('No se pudo leer la caché del mapa.');
        }

        const cacheValida =
            guardado &&
            Array.isArray(guardado.features) &&
            guardado.features.length > 0;

        let dibujadoDesdeCache = false;

        // 1) Si hay calles guardadas (aunque sean viejas), mostrarlas YA
        //    y calcular los colores enseguida.
        if (!forzarConsulta && cacheValida) {
            dibujarCalles(guardado.features);
            dibujadoDesdeCache = true;

            sembrarServidor(guardado.features);

            estado.textContent = 'Calculando disponibilidad...';
            await cargarProbabilidades(guardado.features);
            dibujarCalles(guardado.features);

            // Caché reciente: no hace falta consultar Overpass.
            if (Date.now() - guardado.fecha < duracionCache) {
                return;
            }
        } else {
            estado.textContent = 'Cargando calles...';
        }

        // 2) Pedir calles actualizadas (en segundo plano si ya se dibujó caché).
        try {
            const data = await obtenerCallesConReintentos();
            const features = convertirAFeatures(data);

            if (!features.length) {
                throw new Error('No se encontraron calles.');
            }

            try {
                localStorage.setItem(
                    claveCache,
                    JSON.stringify({fecha: Date.now(), features})
                );
            } catch (error) {
                console.warn('No se pudo guardar la caché.');
            }

            if (ultimaFuente === 'directo') {
                sembrarServidor(features);
            }

            estado.textContent = 'Calculando disponibilidad...';
            await cargarProbabilidades(features);
            dibujarCalles(features);

        } catch (error) {
            if (controladorPagina.signal.aborted) return;

            console.error('Error al cargar calles:', error);

            if (dibujadoDesdeCache) {
                estado.textContent =
                    'Calles cargadas desde la caché. No se pudo actualizar el mapa.';
            } else {
                estado.textContent =
                    'No se pudieron cargar las calles. Hacé clic acá para reintentar.';
            }
        }
    }

    // Si este navegador tiene las calles pero el servidor no, se las envía
    // para que los demás dispositivos las reciban desde calles.php.
    async function sembrarServidor(features) {
        const clave = 'calles_sembradas_v1';

        try {
            const ultima = Number(localStorage.getItem(clave) || 0);
            if (Date.now() - ultima < 24 * 60 * 60 * 1000) return;

            const respuesta = await fetch('guardar_calles.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({features})
            });

            if (respuesta.ok) {
                localStorage.setItem(clave, String(Date.now()));
            }
        } catch (error) {
            console.warn('No se pudo enviar las calles al servidor:', error);
        }
    }

    // Evita lanzar dos cargas a la vez (por ejemplo, al hacer clic mientras carga).
    let cargandoCalles = false;

    async function cargarCalles(forzarConsulta = false) {
        if (cargandoCalles) return;
        cargandoCalles = true;

        try {
            await cargarCallesInterno(forzarConsulta);
        } finally {
            cargandoCalles = false;
        }
    }

    // Permitir volver a intentar la carga de calles haciendo clic en el estado.
    estado.style.cursor = 'pointer';
    estado.title = 'Hacé clic para volver a cargar las calles';
    estado.addEventListener('click', () => cargarCalles(true));

    cargarCalles();

    // Buscar las coordenadas cuando se escribe una calle manualmente.
    calleInput.addEventListener('input', () => {
        clearTimeout(temporizadorBusqueda);

        numeroBusqueda++;

        latInput.value = '';
        lngInput.value = '';
        tramoInput.value = '';

        if (marcadorManual) {
            mapa.removeLayer(marcadorManual);
            marcadorManual = null;
        }

        const calle = calleInput.value.trim();

        if (calle.length < 4) {
            estado.textContent =
                'Escribí al menos 4 caracteres para buscar la calle.';
            return;
        }

        const busquedaActual = numeroBusqueda;

        // Esperar un segundo para no consultar por cada tecla.
        temporizadorBusqueda = setTimeout(() => {
            buscarCoordenadas(calle, busquedaActual);
        }, 1000);
    });

    document.getElementById('form-ocupar').addEventListener('submit', e => {
        if (!calleInput.value.trim()) {
            e.preventDefault();
            calleInput.focus();
            alert('Ingresá una calle o seleccioná una en el mapa.');
            return;
        }

        if (!latInput.value || !lngInput.value) {
            e.preventDefault();
            alert('Esperá a que se encuentre la ubicación de la calle antes de confirmar.');
        }
    });

})();
</script>
</body>
</html>