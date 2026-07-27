<?php

require_once("conexion.php");
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$color = $_POST['color'];

$sql = "INSERT INTO Automovil (marca,modelo,color) 
VALUES (:marca, :modelo, :color)";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':marca' => $marca,
    ':modelo' => $modelo,
    ':color' => $color
]);
header("Location: principal.html");
exit;




?>