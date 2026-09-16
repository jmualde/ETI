<?php
require_once ("conexion.php");
$id = $_GET['id'];

$sql = "SELECT * FROM automovil WHERE id = ?";
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
</head>
<body>
    <h1>Ingrese datos del vehiculo a editar</h1>
    <br>
    <form action="guardar_editar_automovil.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $id ?>">
    <label class="form-label">Marca</label>
    <input type="text" class="form-control" id="marca" name="marca" value="<?php echo $fila['marca']; ?>" required>
    <br>
    <label class="form-label">Modelo</label>
    <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo $fila['modelo']; ?>" required>
    <br>
    <label class="form-label">Color</label>
    <input type="color" class="form-control" id="color" name="color" value="<?php echo $fila['color']; ?>" required>
    <br>
    <label class="form-label">Id_usuario</label>
    <input type="number" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo $fila['id_usuario']; ?>" required>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</body>
</html>


 