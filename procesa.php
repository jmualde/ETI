<?php
require_once ("conexion.php");
$user=$_POST["user"];
$contrasenia=$_POST["contrasenia"];

$sql = "SELECT * FROM usuarios WHERE nombreUsuario = :nombreUsuario AND contrasenia = :contrasenia";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':nombreUsuario' => $user,
    ':contrasenia' => $contrasenia
]);

$fila = $stmt->fetch(PDO::FETCH_ASSOC);


session_start();

    if ($fila){
        $_SESSION["usuario"]=$user;
        header("Location: principal.html" );
        exit();
    }else{
        ?>
            Usuario o Contraseña incorrectos.<br>
            <a href="login.html">Pulse aquí para volver</a>
        <?php
}

    if (!isset($_SESSION["usuario"])) {
      header("Location: login.html");
      exit();
    }


    // FUTURO LOG OUT session_unset();




?>
