<?php
$user=$_POST["user"];
$contrasenia=$_POST["contrasenia"];

$usuario="usuario";
$contra="123";

session_start();

    if ($user === $usuario && $contrasenia === $contrasenia){
        $_SESSION["$usuario"]=$user;
        header("Location: principal.html" );
        exit();
    }else{
        ?>
            Usuario o Contraseña incorrectos.<br>
            <a href="login.html">Pulse aquí para volver</a>
        <?php
}

    if (!isset($_SESSION[usuario])) {
      heder("Location: login.html");
      exit();
    }


    // FUTURO LOG OUT session_unset();




?>