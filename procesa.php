<?php
session_start();

require_once("conexion.php");

$user = $_POST["user"];
$contrasenia = $_POST["contrasenia"];

// Buscar usuario normal
$sql1 = "SELECT * FROM usuario 
         WHERE nombre_usuario = :nombreUsuario 
         AND contrasenia = :contrasenia";

$stmt1 = $conexion->prepare($sql1);

$stmt1->execute([
    ':nombreUsuario' => $user,
    ':contrasenia' => $contrasenia
]);

$fila1 = $stmt1->fetch(PDO::FETCH_ASSOC);


// Buscar administrador
$sql2 = "SELECT * FROM admin 
         WHERE nombre_admin = :nombre_admin 
         AND contrasenia = :contrasenia";

$stmt2 = $conexion->prepare($sql2);

$stmt2->execute([
    ':nombre_admin' => $user,
    ':contrasenia' => $contrasenia
]);

$fila2 = $stmt2->fetch(PDO::FETCH_ASSOC);


// Si es administrador
if ($fila2) {
    $_SESSION["usuario"] = $user;
    header("Location: principal_admin.html");
    exit();
}


// Si es usuario normal
if ($fila1) {
    $_SESSION["usuario"] = $user;
    header("Location: principal.html");
    exit();
}

header("Location: login.php?error=1");
exit();
?>