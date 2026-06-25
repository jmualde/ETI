<?php
require_once("conexion.php");
$nombreUsuario = $_POST['nombreUsuario'];
$apellido = $_POST['apellido'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$contrasenia = $_POST['contrasenia'];

$sql = "INSERT INTO usuarios (nombreUsuario, nombre, apellido, email, contrasenia) 
VALUES (:nombreUsuario, :nombre, :apellido, :email, :contrasenia)";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':nombreUsuario' => $nombreUsuario,
    ':nombre' => $nombre,
    ':apellido' => $apellido,
    ':email' => $email,
    ':contrasenia' => $contrasenia
]);
header("Location: principal.html");
exit;
?>