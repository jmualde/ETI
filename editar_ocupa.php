
<?php
require_once("conexion.php");

$id = $_GET['id'];

$sql = "SELECT * FROM ocupa WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);

$fila = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ocupación</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
</head>
<body>

    <h1>Ingrese los datos de la ocupación a editar</h1>
    <br>

    <form action="guardar_editar_ocupa.php" method="POST">

        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <label class="form-label">ID Usuario</label>
        <input type="number" class="form-control" name="id_usuario"
            value="<?php echo $fila['id_usuario']; ?>" required>
        <br>

        <label class="form-label">ID Automóvil</label>
        <input type="number" class="form-control" name="id_automovil"
            value="<?php echo $fila['id_automovil']; ?>" required>
        <br>

        <label class="form-label">Calle</label>
        <input type="text" class="form-control" name="calle"
            value="<?php echo $fila['calle']; ?>" required>
        <br>

        <label class="form-label">Hora de ocupación</label>
        <input type="time" class="form-control" name="hora_estacionamiento"
            value="<?php echo substr($fila['hora_estacionamiento'], 0, 5); ?>" required>
        <br>

        <label class="form-label">Hora de liberación</label>
        <input type="time" class="form-control" name="hora_liberacion"
            value="<?php echo substr($fila['hora_liberacion'], 0, 5); ?>" required>
        <br>

        <label class="form-label">Latitud</label>
        <input type="number" step="any" class="form-control" name="latitud"
            value="<?php echo $fila['latitud']; ?>" required>
        <br>

        <label class="form-label">Longitud</label>
        <input type="number" step="any" class="form-control" name="longitud"
            value="<?php echo $fila['longitud']; ?>" required>
        <br>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>

        <a href="panel_admin.php" class="btn btn-secondary">Cancelar</a>

    </form>

</body>
</html>
