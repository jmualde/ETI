<?php
session_start();

require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
$auto_select = $_POST['auto_seleccionado'];
$sql = "SELECT * FROM automovil WHERE id_usuario = ? AND id = ? ";
$stmt = $conexion->prepare($sql);
    $stmt->execute([
        $_SESSION["id_usuario"],
        $auto_select
    ]);

    $fila_select = $stmt->fetch(PDO::FETCH_ASSOC);
    $marca = $fila_select['marca'];
    $modelo = $fila_select['modelo'];
    $color = $fila_select['color'];
    header("Location: cuenta.php?auto=" . urlencode($auto_select) . "&marca=" . urlencode($marca) . "&modelo=" . urlencode($modelo) . "&color=" . urlencode($color));
    exit();
?>