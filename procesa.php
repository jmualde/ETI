<?php

<<<<<<< Updated upstream

$sql = "SELECT * FROM usuarios WHERE nombreUsuario = :nombreUsuario AND contrasenia = :contrasenia";
=======
require_once("conexion.php");

$user = $_POST["user"];
$contrasenia = $_POST["contrasenia"];

$sql = "SELECT * FROM usuarios 
        WHERE nombreUsuario = :nombreUsuario 
        AND contrasenia = :contrasenia";

>>>>>>> Stashed changes
$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':nombreUsuario' => $user,
    ':contrasenia' => $contrasenia
]);

$fila = $stmt->fetch(PDO::FETCH_ASSOC);

session_start();

<<<<<<< Updated upstream
    if ($fila){
        $_SESSION["usuario"]=$user;
        header("Location: principal.html" );
        exit();
    }else{
   header("Location: login.php");


   
}


    if (!isset($_SESSION["usuario"])) {
      header("Location: login.php");
      exit();
    }


    // FUTURO LOG OUT session_unset();




?>
=======
if ($fila) {

    $_SESSION["usuario"] = $user;
    $_SESSION["idUsuario"] = $fila["id"];

    header("Location: principal.html");
    exit();

} else {

    header("Location: login.php?error=1");
    exit();

}

?>
>>>>>>> Stashed changes
