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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">

    <title>Ocupar Lugar</title>
    <link rel="stylesheet" href="disenio.css">
</head>
<body>
<div class="container-fluid p-0">

    <nav class="tarjetaArriba-cuenta">

        <a href="principal.php">
            <p class="textoBlanco-principal">Página Principal</p>
        </a>

        <a href="registra_auto.php">
            <p class="textoBlanco-principal">Registrar vehículo</p>
        </a>

        <a href="ocupar_lugar.php">
            <p class="textoBlanco-principal">Ocupar lugar</p>
        </a>

        <a href="cuenta.php">
            <p class="textoBlanco-principal">Cuenta</p>
        </a>

    </nav>
    <br><br><br>
    <div class="row justify-content-center">
        <center>
        <h1>Ingrese datos para ocupar lugar</h1>
        </center>
        
    <div class="col-12 col-md-6 col-lg-4">
        <br><br>
        <form action="">
        <label >Ingrese hora que desea ocupar </label>
        <input type="time" class="form-control campo-cuenta" id="hora" name="hora" required>
        <br><br>
        <label >Ingrese vehiculo con el que desea ocupar el lugar </label>
        <select class="form-control campo-cuenta" id="vehiculo" name="vehiculo" required>
            <?php if ($auto): ?>
                <option value="<?php echo $auto['id']; ?>"><?php echo $auto['marca'] . ' ' . $auto['modelo']; ?></option>
            <?php else: ?>
                <option value="">No hay vehículos registrados</option>
            <?php endif; ?>
        </select>

        <br><br>
        <button type="submit" class="botonRegistra" onclick="ocupaLugar()">Ocupar Lugar</button>
        </form>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <br><br><br>
        <div class="tarjeta tarjeta-guia-colores">
            
            <ul class="guia-colores">
                <H4>Guía de Colores</H4>
                <li class="fila-color">
                    <img src="verde.png" height="20px" width="20px" style="margin-right: 14px; margin-left: 7px;">
                    <label style="font-weight: bold;">100% - 80%</label>
                </li>
                <li class="fila-color">
                    <img src="amarillo.png" height="20px" width="20px" style="margin-right: 14px;">
                    <label style="font-weight: bold;">80% - 50%</label>
                </li>
                <li class="fila-color">
                    <img src="naranja.png" height="20px" width="20px" style="margin-right: 14px;">
                    <label style="font-weight: bold;">50% - 30%</label>
                </li>
                <li class="fila-color">
                    <img src="rojo.png" height="20px" width="20px" style="margin-right: 14px; margin-left: -14px;">
                    <label style="font-weight: bold;">  30% - 1%</label>
                </li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>