<?php

require_once("conexion.php");
$id = $_POST['id'];
$nombre_usuario = $_POST['nombreUsuario'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$contrasenia = $_POST['contrasenia'];


$sql = "UPDATE usuario SET nombre_usuario = ?, nombre = ?, apellido = ?, email = ?, contrasenia = ? WHERE id = ?";
$stmt = $conexion->prepare($sql); 
$stmt->execute([
    $nombre_usuario,
    $nombre,
    $apellido,
    $email,
    $contrasenia,
    $id
]);

header("Location: panel_admin.php");
?> 