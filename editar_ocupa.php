<?php
require_once ("conexion.php");
$id = $_GET['id'];

$sql = "SELECT * FROM ocupa WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    $id
    ]);

$fila = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ocupacion</title>

     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
        <link rel="stylesheet" href="disenio.css">
</head>
<body>
    <h1 style="text-align: center;">Ingrese datos de la ocupacion</h1>
    <br>
    <div class="tarjeta-registrar">
    <form action="guardar_editar_ocupa.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id ?>">

        
        <label class="form-label">Id Ocupacion</label>
        <input type="number" class="form-control" id="id" name="id" value="<?php echo $fila['id']; ?>" required>
        <br>
        

        <label class="form-label">Id Usuario</label>
        <input type="number" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo $fila['id_usuario']; ?>" required>
        <br>

        <label class="form-label">Id Automovil</label>
        <input type="number" class="form-control" id="id_automovil" name="id_automovil" value="<?php echo $fila['id_automovil']; ?>" required>
        <br>

        <label class="form-label">Hora Estacionamiento</label>
        <input type="hour" class="form-control" id="hora_estacionamiento" name="hora_estacionamiento" value="<?php echo $fila['hora_estacionamiento']; ?>" required>
        <br>

        <label class="form-label">Hora Liberacion</label>
        <input type="hour" class="form-control" id="hora_liberacion" name="hora_liberacion" value="<?php echo $fila['hora_liberacion']; ?>" required>
        <br>

        <label class="form-label">Calle</label>
        <input type="text" class="form-control" id="calle" name="calle" value="<?php echo $fila['calle']; ?>" required>
        <br>

        <label class="form-label">Latitud</label>
        <input type="decimal" class="form-control" id="latitud" name="latitud" value="<?php echo $fila['latitud']; ?>" required>
        <br>

        <label class="form-label">Longitud</label>
        <input type="decimal" class="form-control" id="longitud" name="longitud" value="<?php echo $fila['longitud']; ?>" required>
        <br>


        <button type="submit" class="botonRegistra">Guardar cambios</button>
    </form>
</body>
</html>