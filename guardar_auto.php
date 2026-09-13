
<?php

session_start();
require_once("conexion.php");

$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$color = $_POST['color'];

$idUsuario = $_SESSION['idUsuario'];

$sql = "INSERT INTO Automovil (marca, modelo, color, idUsuario) 
        VALUES (:marca, :modelo, :color, :idUsuario)";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':marca' => $marca,
    ':modelo' => $modelo,
    ':color' => $color,
    ':idUsuario' => $idUsuario
]);

header("Location: principal.html");
exit;

?>
