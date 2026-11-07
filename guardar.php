<?php
require_once("conexion.php");

$nombreUsuario = $_POST['nombreUsuario'];
$apellido = $_POST['apellido'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$contrasenia = $_POST['contrasenia'];

$sql2 = "SELECT id FROM usuario 
         WHERE nombre_usuario = :nombreUsuario 
         OR email = :email";

$stmt2 = $conexion->prepare($sql2);
$stmt2->execute([
    ':nombreUsuario' => $nombreUsuario,
    ':email' => $email
]);

$usuario = $stmt2->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    header("Location: registra.php?error=1");
    exit;
}


$sql = "INSERT INTO usuario (nombre_usuario, nombre, apellido, email, contrasenia) 
VALUES (:nombreUsuario, :nombre, :apellido, :email, :contrasenia)";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':nombreUsuario' => $nombreUsuario,
    ':nombre' => $nombre,
    ':apellido' => $apellido,
    ':email' => $email,
    ':contrasenia' => $contrasenia
]);
header("Location: login.php");
exit;
?>
