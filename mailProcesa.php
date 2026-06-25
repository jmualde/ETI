<?php
$email = $_POST['mailRecuperacion'];
$sql = "SELECT * FROM usuarios WHERE email = :email";
$stmt = $conexion->prepare($sql);
$stmt->execute([
    ':email' => $email
]);

if ($stmt->rowCount() > 0) {
    echo "El mail ya existe en la base de datos";
    
} else {
    echo "El mail no está registrado";
}