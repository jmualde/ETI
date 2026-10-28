<?php
session_start();
require_once("conexion.php");
if (!isset($_SESSION["usuario"])) { header("Location: login.php"); exit(); }
$mensaje = $_SESSION['mensaje_ocupar'] ?? '';
unset($_SESSION['mensaje_ocupar']);
try {
    $stmt = $conexion->prepare("SELECT id FROM usuario WHERE nombre_usuario = :nombreUsuario");
    $stmt->execute([':nombreUsuario' => $_SESSION['usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) { header("Location: cerrar_sesion.php"); exit(); }
    $stmtAuto = $conexion->prepare("SELECT id, marca, modelo, color FROM automovil WHERE id_usuario = :idUsuario ORDER BY marca, modelo");
    $stmtAuto->execute([':idUsuario' => $usuario['id']]);
    $autos = $stmtAuto->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { error_log($e->getMessage()); $autos = []; $usuario = ['id' => 0]; $mensaje = 'No se pudieron cargar los datos.'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ocupar lugar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="disenio.css">
<style>
.ocupar-wrap{max-width:1400px;margin:28px auto;padding:0 18px}.ocupar-grid{display:grid;grid-template-columns:minmax(300px,380px) minmax(0,1fr);gap:22px;align-items:start}.panel-ocupar{background:#fff;border-radius:14px;padding:22px;box-shadow:0 4px 18px #00000012}.panel-ocupar label{font-weight:600;margin:12px 0 6px}.panel-ocupar .form-control{margin-bottom:8px}#mapa-estacionamiento{height:600px;width:100%;border-radius:12px;z-index:1}.leyenda{display:flex;flex-wrap:wrap;gap:9px 14px;margin-top:12px;font-size:.88rem}.leyenda span{display:inline-flex;align-items:center;gap:6px}.muestra{display:inline-block;width:24px;height:6px;border-radius:4px}.nota-demo{font-size:.85rem;color:#755b18;background:#fff7dc;padding:10px 12px;border-radius:8px;margin-top:12px}.estado-mapa{font-size:.9rem;color:#555;margin:8px 0}.btn-ocupar{margin-top:18px;width:100%}@media(max-width:900px){.ocupar-grid{grid-template-columns:1fr}#mapa-estacionamiento{height:430px}}
</style>
</head>
<body>
<div class="container-fluid p-0">
<nav class="tarjetaArriba-cuenta">
<a href="principal.php"><p class="textoBlanco-principal">Página Principal</p></a>
<a href="registra_auto.php"><p class="textoBlanco-principal">Registrar vehículo</p></a>
<a href="ocupar_lugar.php"><p class="textoBlanco-principal">Ocupar lugar</p></a>
<a href="cuenta.php"><p class="textoBlanco-principal">Cuenta</p></a>
</nav>
<main class="ocupar-wrap">
<h1 class="mb-2">Ocupar lugar</h1>
<p class="text-muted mb-4">Elegí la hora, tu vehículo y una calle cercana al Liceo Logosófico.</p>
<?php if ($mensaje): ?><div class="alert alert-info"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<div class="ocupar-grid">
<section class="panel-ocupar">
<h4>Datos del estacionamiento</h4>
<form action="guardar_ocupacion.php" method="post" id="form-ocupar">
<label for="hora">Hora a la que vas a estacionar</label>
<input type="time" class="form-control" id="hora" name="hora" required>
<label for="vehiculo">Vehículo</label>
<select class="form-select" id="vehiculo" name="vehiculo" required <?= count($autos) ? '' : 'disabled' ?>>
<option value="">Seleccioná tu auto</option>
<?php foreach ($autos as $auto): ?>
<option value="<?= (int)$auto['id'] ?>"><?= htmlspecialchars($auto['marca'].' '.$auto['modelo'].' · '.$auto['color'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
<?php if (!count($autos)): ?><div class="form-text text-danger">No tenés autos registrados. Registrá uno antes de continuar.</div><?php endif; ?>
<label for="calle">Calle seleccionada</label>
<input type="text" class="form-control" id="calle" name="calle" placeholder="Escribí la calle o seleccionala en el mapa" required maxlength="150">
<div class="form-text">También podés hacer clic en una calle coloreada del mapa.</div>
<input type="hidden" id="latitud" name="latitud"><input type="hidden" id="longitud" name="longitud">
<button type="submit" class="btn btn-primary btn-ocupar" <?= count($autos) ? '' : 'disabled' ?>>Confirmar ocupación</button>
</form>
<div class="nota-demo"><strong>Importante:</strong> los colores de probabilidad que ves ahora son de demostración. Para que reflejen probabilidades reales, hay que calcularlas a partir de los registros históricos de ocupación.</div>
</section>
<section class="panel-ocupar">
<h4>Elegí una calle en el mapa</h4>
<p class="text-muted mb-2">Zona delimitada alrededor del Liceo Logosófico, Av. Dr. Manuel Albo 2707, Montevideo.</p>
<div id="mapa-estacionamiento" aria-label="Mapa interactivo para seleccionar calle"></div>
<div id="estado-mapa" class="estado-mapa">Cargando calles de OpenStreetMap…</div>
<div class="leyenda" aria-label="Leyenda de disponibilidad">
<span><i class="muestra" style="background:#16a34a"></i>Alta (80–100%)</span>
<span><i class="muestra" style="background:#eab308"></i>Media (50–79%)</span>
<span><i class="muestra" style="background:#f97316"></i>Media-baja (30–49%)</span>
<span><i class="muestra" style="background:#dc2626"></i>Baja (1–29%)</span>
<span><i class="muestra" style="background:#2563eb"></i>No disponible</span>
</div>
</section>
</div>
</main>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
 const centro = [-34.89071, -56.15986]; // Liceo Logosófico, Manuel Albo 2707
 const mapa = L.map('mapa-estacionamiento', {scrollWheelZoom:true}).setView(centro, 16);
 const limites = L.latLngBounds([-34.8990,-56.1690],[-34.8825,-56.1500]);
 mapa.setMaxBounds(limites); mapa.setMaxZoom(19); mapa.setMinZoom(14);
 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19, attribution:'&copy; OpenStreetMap contributors'}).addTo(mapa);
 L.marker(centro).addTo(mapa).bindPopup('<strong>Liceo Logosófico</strong><br>Av. Dr. Manuel Albo 2707');
 const estado = document.getElementById('estado-mapa');
 const calleInput = document.getElementById('calle');
 const latInput = document.getElementById('latitud');
 const lngInput = document.getElementById('longitud');
 let seleccionado = null;
 const colores = [{min:80,color:'#16a34a',nombre:'Alta'},{min:50,color:'#eab308',nombre:'Media'},{min:30,color:'#f97316',nombre:'Media-baja'},{min:0,color:'#dc2626',nombre:'Baja'}];
 function clasificar(id, tags) {
   const parking = Object.entries(tags || {}).some(([k,v]) => /parking:lane/.test(k) && /no_parking|no_stopping/.test(String(v)));
   if (parking) return {color:'#2563eb',nombre:'No disponible',texto:'La información cartográfica indica restricción de estacionamiento'};
   // Resultado visual reproducible para la maqueta; no representa una predicción real.
   const n = Math.abs(Math.sin(Number(id) * 12.9898) * 43758.5453) % 100;
   const nivel = colores.find(c => n >= c.min) || colores[3];
   return {...nivel, texto:'Probabilidad ilustrativa de la maqueta'};
 }
 function seleccionar(feature, layer) {
   const props = feature.properties || {}; const tags = props.tags || {};
   const nombre = tags.name || props.name || tags['name:es'] || 'Calle seleccionada (sin nombre en OSM)';
   const c = clasificar(props.id || 1, tags);
   layer.bindPopup(`<strong>${nombre}</strong><br>Disponibilidad: ${c.nombre}<br><small>${c.texto}</small><br><button type="button" onclick="window.seleccionarCalleOSM(${JSON.stringify(String(nombre)).replace(/</g,'\\u003c')},${Number(layer.getLatLng ? layer.getLatLng().lat : centro[0])},${Number(layer.getLatLng ? layer.getLatLng().lng : centro[1])})">Elegir esta calle</button>`);
   layer.on('click', () => { calleInput.value = nombre; const ll = layer.getLatLng ? layer.getLatLng() : layer.getBounds().getCenter(); latInput.value=ll.lat.toFixed(7); lngInput.value=ll.lng.toFixed(7); if (seleccionado) seleccionado.setStyle({weight:5}); seleccionado=layer; if(layer.setStyle) layer.setStyle({weight:8,color:'#111827'}); });
 }
 window.seleccionarCalleOSM = (nombre,lat,lng) => {calleInput.value=nombre;latInput.value=lat;lngInput.value=lng;};
 const query = '[out:json][timeout:25];way["highway"~"^(residential|tertiary|secondary|primary|unclassified|living_street|service)$"](-34.8990,-56.1690,-34.8825,-56.1500);out geom tags;';
 fetch('https://overpass-api.de/api/interpreter?data='+encodeURIComponent(query))
  .then(r => {if(!r.ok) throw new Error('No se pudo consultar el mapa'); return r.json();})
  .then(data => {
    const features = data.elements.filter(e=>e.type==='way'&&e.geometry&&e.geometry.length>1).map(e=>({type:'Feature',properties:{id:e.id,tags:e.tags||{}},geometry:{type:'LineString',coordinates:e.geometry.map(p=>[p.lon,p.lat])}}));
    const capa = L.geoJSON({type:'FeatureCollection',features}, {style:f=>{const c=clasificar(f.properties.id,f.properties.tags);return {color:c.color,weight:5,opacity:.88};},onEachFeature:seleccionar}).addTo(mapa);
    if (!features.length) throw new Error('No se encontraron calles en la zona');
    estado.textContent = 'Calles cargadas. Seleccioná una línea para ver su estado y elegirla.';
  }).catch(err=>{estado.textContent='No se pudieron cargar las calles automáticamente. Podés ingresar el nombre de la calle manualmente. ('+err.message+')';});
 document.getElementById('form-ocupar').addEventListener('submit', e=>{
   if (!calleInput.value.trim()) {e.preventDefault(); calleInput.focus(); alert('Ingresá una calle o seleccioná una en el mapa.');}
 });
})();
</script>
</body></html>
