<?php
session_start();
require_once ('conexion.php');

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
 
$campo = $_GET['campo'];
$valor = $_GET['valor'];


$sql = "UPDATE usuario SET $campo = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);

$stmt->execute([
    $valor,
    $_SESSION['id_usuario']
]);

if ($campo == "nombre_usuario") {
    $_SESSION["usuario"] = $valor;
}

header("Location: cuenta.php");
exit();


?>