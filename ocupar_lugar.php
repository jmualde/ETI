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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocupar lugar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
    <link rel="stylesheet" href="disenio.css?v=<?= filemtime(__DIR__ . '/disenio.css') ?>">
    <style>
        body {
            min-height: 100vh;
        }

        .mensaje-futuro {
            margin-top: 48px;
            color: var(--ui-text, white);
            text-align: center;
        }
    </style>
</head>

<body data-theme="dark">
    <div class="container-fluid p-0">
        <nav class="tarjetaArriba">
            <a href="principal.php">
                <h6 class="textoBlanco">Página Principal</h6>
            </a>

            <a href="registra_auto.php">
                <h6 class="textoBlanco">Registrar vehículo</h6>
            </a>

            <a href="ocupar_lugar.php">
                <h6 class="textoBlanco">Ocupar lugar</h6>
            </a>

            <a href="cuenta.php">
                <h6 class="textoBlanco">Cuenta</h6>
            </a>

            <button
                class="botonTema"
                id="botonTema"
                type="button"
                aria-pressed="false">
                Activar modo claro
            </button>
        </nav>

        <p class="mensaje-futuro">A futuro...</p>
    </div>

    <script src="tema.js"></script>
</body>

</html>