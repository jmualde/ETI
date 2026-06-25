<?php
$host="127.0.0.1";
$user="root";
$password="";
$bd="estacionamiento";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$bd", 
    $user, 
    $password
    );
    $conexion->setAttribute(
        PDO::ATTR_ERRMODE, 
        PDO::ERRMODE_EXCEPTION
        );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}









?>