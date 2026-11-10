<?php

require_once("conexion.php");
$id = $_POST['id'];
$latitud = $_POST['latitud'];
$longitud = $_POST['longitud'];
$id_usuario = $_POST['id_usuario'];
$hora_estacionamiento = $_POST['hora_estacionamiento'];
$hora_liberacion = $_POST['hora_liberacion'];
$id_automovil = $_POST['id_automovil'];


$sql = "UPDATE ocupa SET latitud = ?, longitud = ?, id_usuario = ?, hora_estacionamiento = ?, hora_liberacion = ?, id_automovil = ? WHERE id = ?";
$stmt = $conexion->prepare($sql); 
$stmt->execute([
    $latitud,
    $longitud,
    $id_usuario,
    $hora_estacionamiento,
    $hora_liberacion,
    $id_automovil,
    $id
]);

header("Location: panel_admin.php");
?> 