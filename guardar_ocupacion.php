<?php
session_start();
require_once 'conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit(); }
function volver($mensaje) { $_SESSION['mensaje_ocupar'] = $mensaje; header('Location: ocupar_lugar.php'); exit(); }
$hora = $_POST['hora'] ?? '';
$idAuto = filter_input(INPUT_POST, 'vehiculo', FILTER_VALIDATE_INT);
$calle = trim($_POST['calle'] ?? '');
$lat = ($_POST['latitud'] ?? '') !== '' ? filter_var($_POST['latitud'], FILTER_VALIDATE_FLOAT) : null;
$lng = ($_POST['longitud'] ?? '') !== '' ? filter_var($_POST['longitud'], FILTER_VALIDATE_FLOAT) : null;
if (!$hora || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora) || !$idAuto || $calle === '' || mb_strlen($calle) > 150) volver('Revisá la hora, el vehículo y la calle seleccionada.');
try {
 $s = $conexion->prepare('SELECT id FROM usuario WHERE nombre_usuario = :usuario');
 $s->execute([':usuario' => $_SESSION['usuario']]); $usuario = $s->fetch(PDO::FETCH_ASSOC);
 if (!$usuario) volver('No se encontró tu usuario. Volvé a iniciar sesión.');
 $s = $conexion->prepare('SELECT id FROM automovil WHERE id = :auto AND id_usuario = :usuario');
 $s->execute([':auto' => $idAuto, ':usuario' => $usuario['id']]);
 if (!$s->fetch()) volver('El vehículo seleccionado no está asignado a tu usuario.');
 // Requiere ejecutar la migración SQL incluida en el proyecto antes de usar este guardado.
 $s = $conexion->prepare('INSERT INTO ocupa (coordenadaX, coordenadaY, id_usuario, fecha_inicio, fecha_fin, id_automovil, hora_estacionamiento, calle, latitud, longitud) VALUES (NULL, NULL, :usuario, CURDATE(), NULL, :auto, :hora, :calle, :lat, :lng)');
 $s->execute([':usuario'=>$usuario['id'], ':auto'=>$idAuto, ':hora'=>$hora, ':calle'=>$calle, ':lat'=>$lat, ':lng'=>$lng]);
 volver('La solicitud de ocupación se guardó correctamente.');
} catch (PDOException $e) { error_log($e->getMessage()); volver('No se pudo guardar. Verificá que ejecutaste la migración SQL y volvé a intentarlo.'); }
