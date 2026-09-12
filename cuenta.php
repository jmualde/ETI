<?php
session_start();
require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

try {

    $sql = "SELECT * FROM usuario WHERE nombre_usuario = :nombreUsuario";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':nombreUsuario' => $_SESSION["usuario"]
    ]);

    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila) {

        echo "Usuario: " . $fila["nombre_usuario"] . "<br>";
        echo "Nombre: " . $fila["nombre"] . "<br>";
        echo "Email: " . $fila["email"] . "<br>";

    } else {
        echo "No se encontró el usuario.";
    }

} catch (PDOException $e) {

    echo "Error: " . $e->getMessage();

}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="disenio.css">

    <title>Cuenta</title>
</head>

<body>


        <nav class="tarjetaArriba-cuenta">

            <a href="principal.html">
                <p class="textoBlanco-cuenta">Página Principal</p>
            </a>

            <a href="#quienes_somos">
                <p class="textoBlanco-cuenta">¿Quiénes somos?</p>
            </a>

            <a href="registra_auto.html">
                <p class="textoBlanco-cuenta">Registrar vehículo</p>
            </a>

            <a href="ocupar_lugar.html">
                <p class="textoBlanco-cuenta">Ocupar lugar</p>
            </a>

            <a href="cuenta.php">
                <p class="textoBlanco-cuenta">Cuenta</p>
            </a>

        </nav>

        <br>

        <h1 class="titulo-cuenta">Tu cuenta</h1>

        <section class="seccionPrincipal">

            <div class="contenido">

                <label>Nombre de usuario:</label>
                <input class="campo-cuenta" type="text">

                <br>

                <label>Correo electrónico:</label>
                <input class="campo-cuenta" type="email">

            </div>

        </section>

    </div>

</body>
</html>