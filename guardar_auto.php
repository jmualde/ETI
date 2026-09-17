<?php
require_once("conexion.php");

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$color = $_POST['color'];

$sql = "INSERT INTO automovil (id_usuario, marca, modelo, color) 
VALUES (:id_usuario, :marca, :modelo, :color)";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':id_usuario' => $id_usuario,
    ':marca' => $marca,
    ':modelo' => $modelo,
    ':color' => $color
]);
header("Location: principal.html");
exit;

?>