<?php
session_start();
require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

try {

    $sql = "SELECT * FROM usuarios WHERE nombreUsuario = :nombreUsuario";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':nombreUsuario' => $_SESSION["usuario"]
    ]);

    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila) {

        echo "Usuario: " . $fila["nombreUsuario"] . "<br>";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <link rel="stylesheet" href="disenio.css">
    <title>Cuenta</title>
</head>
<body class="pagina-cuenta">
    <div class="container-fluid">
         <nav class="tarjetaArriba-principal">

            <a href="principal.html">
                <p class="textoBlanco-principal">Página Principal</p>
            </a>

            <a href="#quienes_somos">
                <p class="textoBlanco-principal">¿Quiénes somos?</p>
            </a>

            <a href="registra_auto.html">
                <p class="textoBlanco-principal">Registrar vehículo</p>
            </a>

            <a href="ocupar_lugar.html">
                <p class="textoBlanco-principal">Ocupar lugar</p>
            </a>

            <a href="cuenta.html">
                <p class="textoBlanco-principal">Cuenta</p>
            </a>

        </nav>
        <br>
        <center><h1>Tu cuenta</h1></center>
        <section class=" seccionPrincipal">
            <div class="contenido">
                Nombre de usuario: <input class="campo-cuenta" type="text" ><br>
                Correo electrónico: <input class="campo-cuenta" type="email" ><br>
                

                

            
        </div>
   
        <br><br>
    

</body>
</html>