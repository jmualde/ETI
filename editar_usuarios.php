<?php
require_once ("conexion.php");
$id = $_GET['id'];

$sql = "SELECT * FROM usuario WHERE id = ?";
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
    <title>Editar Usuario</title>

     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
</head>
<body>
    <h1>Ingrese datos del usuario a editar</h1>
    <br>
    <form action="guardar_editar_usuario.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $id ?>">
    <label class="form-label">Nombre de usuario</label>
    <input type="text" class="form-control" id="nombreUsuario" name="nombreUsuario" value="<?php echo $fila['nombre_usuario']; ?>" required>
    <br>
    <label class="form-label">Nombre</label>
    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $fila['nombre']; ?>" required>
    <br>
    <label class="form-label">Apellido</label>
    <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo $fila['apellido']; ?>" required>
    <br>
    <label class="form-label">Email</label>

    <input type="email" class="form-control" id="email" name="email" value="<?php echo $fila['email']; ?>" required>
    <br>
    <label class="form-label">Contraseña</label>
    <input type="password" class="form-control" id="contrasenia" name="contrasenia" value="<?php echo $fila['contrasenia']; ?>" required>
    <br>
    <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </form>
</body>
</html>


 