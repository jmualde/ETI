<?php

require_once("conexion.php");
$id = $_POST['id'];
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$color = $_POST['color'];
$id_usuario = $_POST['id_usuario'];


$sql = "UPDATE automovil SET marca = ?, modelo = ?, color = ?, id_usuario = ? WHERE id = ?";
$stmt = $conexion->prepare($sql); 
$stmt->execute([
    $marca,
    $modelo,
    $color,
    $id_usuario,
    $id
]);

header("Location: panel_admin.php");

?> 