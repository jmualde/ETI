<?php
require_once ("conexion.php");

$id= $_GET['id'];
$sql = "DELETE FROM automovil WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
header("Location: panel_admin.php");
?>