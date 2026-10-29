
<?php
require_once("conexion.php");

$id = $_POST['id'];
$id_usuario = $_POST['id_usuario'];
$id_automovil = $_POST['id_automovil'];
$calle = $_POST['calle'];
$hora_estacionamiento = $_POST['hora_estacionamiento'];
$hora_liberacion = $_POST['hora_liberacion'];
$latitud = $_POST['latitud'];
$longitud = $_POST['longitud'];

$sql = "UPDATE ocupa
        SET id_usuario = ?,
            id_automovil = ?,
            calle = ?,
            hora_estacionamiento = ?,
            hora_liberacion = ?,
            latitud = ?,
            longitud = ?
        WHERE id = ?";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    $id_usuario,
    $id_automovil,
    $calle,
    $hora_estacionamiento,
    $hora_liberacion,
    $latitud,
    $longitud,
    $id
]);

header("Location: panel_admin.php");
exit();
?>
