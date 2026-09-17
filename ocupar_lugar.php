<?php 
session_start();
require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

try {



    $sql = "SELECT * FROM usuario  WHERE nombre_usuario = :nombreUsuario";


    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':nombreUsuario' => $_SESSION["usuario"]
    ]);

    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar auto
    $sqlAuto = "SELECT * FROM automovil WHERE id_usuario = :idUsuario";

    $stmtAuto = $conexion->prepare($sqlAuto);

    $stmtAuto->execute([
        ':idUsuario' => $fila["id"]
    ]);

    $auto = $stmtAuto->fetch(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    echo "Error: " . $e->getMessage();

}

?>
A futuro...