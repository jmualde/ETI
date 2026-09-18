
<?php

session_start();
require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$campo = $_GET["campo"] ?? "";
$valor = $_GET["valor"] ?? "";


// DATOS DEL AUTO
if ($campo == "marca" || $campo == "modelo" || $campo == "color") {

    // ID del auto seleccionado
    $id_auto = $_GET["id_auto"] ?? "";

    if ($id_auto == "") {
        header("Location: cuenta.php");
        exit();
    }

    // Actualizar solamente ese auto
    $sql = "UPDATE automovil 
            SET $campo = ? 
            WHERE id = ? AND id_usuario = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $valor,
        $id_auto,
        $_SESSION["id_usuario"]
    ]);

}

// DATOS DEL USUARIO
elseif (
    $campo == "nombre_usuario" ||
    $campo == "email" ||
    $campo == "contrasenia"
) {

    $sql = "UPDATE usuario 
            SET $campo = ? 
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $valor,
        $_SESSION["id_usuario"]
    ]);

    if ($campo == "nombre_usuario") {
        $_SESSION["usuario"] = $valor;
    }

}

header("Location: cuenta.php");
exit();

?>
