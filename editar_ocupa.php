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
    <title>Editar Automovil</title>

     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
        <link rel="stylesheet" href="disenio.css">
</head>
<body>
    <h1>Ingrese datos de la ocupacion</h1>
    <br>
    <form action="guardar_editar_ocupa.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $id ?>">
    <label class="form-label">Latitud</label>
    <input type="text" class="form-control" id="latitud" name="latitud" value="<?php echo $fila['latitud']; ?>" required>
    <br>
    <label class="form-label">Longitud</label>
    <input type="text" class="form-control" id="longitud" name="longitud" value="<?php echo $fila['longitud']; ?>" required>
    <br>
    <label class="form-label">id_automovil</label>
    <input type="number" class="form-control" id="id_automovil" name="id_automovil" value="<?php echo $fila['id_automovil']; ?>" required>
    <br>
    <label class="form-label">hora_estacionamiento</label>
    <input type="number" class="form-control" id="hora_estacionamiento" name="hora_estacionamiento" value="<?php echo $fila['hora_estacionamiento']; ?>" required>
    <br>
    <label class="form-label">hora_liberacion</label>
    <input type="number" class="form-control" id="hora_liberacion" name="hora_liberacion" value="<?php echo $fila['hora_liberacion']; ?>" required>
    <br>
    <label class="form-label">Id_usuario</label>
    <input type="number" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo $fila['id_usuario']; ?>" required>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</body>
</html>