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

} catch (PDOException $e) {

    $fila = null;

}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="disenio.css?v=20260912">

    <title>Cuenta</title>
</head>

<body>
    <div class="container-fluid p-0">

        <nav class="tarjetaArriba-cuenta">

            <a href="principal.html">
                <p class="textoBlanco">Página Principal</p>
            </a>

            <a href="registra_auto.html">
                <p class="textoBlanco">Registrar vehículo</p>
            </a>

            <a href="ocupar_lugar.html">
                <p class="textoBlanco">Ocupar lugar</p>
            </a>

            <a href="cuenta.php">
                <p class="textoBlanco">Cuenta</p>
            </a>

        </nav><br><br><br>
<center>
        <h1>Tu cuenta</h1>
        </center>
<br><br>
        <div class=" row justify-content-center">

            <div class="col-12 col-md-6 col-lg-4">

                <label>Nombre de usuario:</label>

                <div class="d-flex gap-2 align-items-center">
                    <input class="campo-cuenta form control" type="text" value="<?= htmlspecialchars($fila["nombre_usuario"]) ?>" readonly>
                    <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('nombre_usuario')">Editar</button>
                </div>

                <br>
                <label>Correo electrónico:</label>

                <div class="d-flex gap-2 align-items-center">
                <input class="campo-cuenta form control" type="email" value="<?= htmlspecialchars($fila["email"]) ?>" readonly>
                <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('email')">Editar</button>
                
                </div>
                <br>
                   <label >Contraseña:</label>

                <div class="d-flex gap-2 align-items-center">
                <input class="campo-cuenta form control" type="password" value="<?= htmlspecialchars($fila["contrasenia"]) ?>" readonly>
                <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('contrasenia')">Editar</button>
                
                </div>

            </div>
        </div>
       

    </div>

</body>
</html>