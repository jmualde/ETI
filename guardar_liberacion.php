<?php
session_start();
require_once 'conexion.php';

date_default_timezone_set('America/Montevideo');

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

function volver($mensaje) {
    $_SESSION['mensaje_ocupar'] = $mensaje;
    header('Location: ocupar_lugar.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver('Solicitud inválida.');
}

$idOcupacion = filter_input(INPUT_POST, 'id_ocupacion', FILTER_VALIDATE_INT);

if (!$idOcupacion) {
    volver('No se pudo identificar el lugar a liberar.');
}

try {

    // Buscar usuario
    $s = $conexion->prepare(
        'SELECT id FROM usuario WHERE nombre_usuario = :usuario'
    );
    $s->execute([':usuario' => $_SESSION['usuario']]);
    $usuario = $s->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        volver('No se encontró tu usuario. Volvé a iniciar sesión.');
    }

    $conexion->beginTransaction();

    // La ocupación debe ser del usuario y estar ACTIVA ahora mismo.
    $s = $conexion->prepare("
        SELECT id, id_automovil, calle, latitud, longitud
        FROM ocupa
        WHERE id = :id
          AND id_usuario = :usuario
          AND latitud IS NOT NULL
          AND longitud IS NOT NULL
          AND hora_estacionamiento IS NOT NULL
          AND hora_liberacion IS NOT NULL
          AND hora_estacionamiento <> hora_liberacion
          AND (
              (hora_estacionamiento < hora_liberacion
               AND CURTIME() >= hora_estacionamiento
               AND CURTIME() <  hora_liberacion)
              OR
              (hora_estacionamiento > hora_liberacion
               AND (CURTIME() >= hora_estacionamiento
                    OR CURTIME() <  hora_liberacion))
          )
        LIMIT 1
        FOR UPDATE
    ");
    $s->execute([
        ':id' => $idOcupacion,
        ':usuario' => $usuario['id']
    ]);

    $ocupacion = $s->fetch(PDO::FETCH_ASSOC);

    if (!$ocupacion) {
        $conexion->rollBack();
        volver('Ese lugar ya no está ocupado por vos.');
    }

    // Terminar la ocupación ahora (deja de verse el auto en el mapa).
    $s = $conexion->prepare(
        'UPDATE ocupa SET hora_liberacion = CURTIME() WHERE id = :id'
    );
    $s->execute([':id' => $ocupacion['id']]);

    // Registrar el aviso de lugar liberado.
    $s = $conexion->prepare(
        'INSERT INTO lugares_liberados
         (id_usuario, id_automovil, calle, latitud, longitud, liberado_en)
         VALUES (:usuario, :auto, :calle, :lat, :lng, NOW())'
    );
    $s->execute([
        ':usuario' => $usuario['id'],
        ':auto' => $ocupacion['id_automovil'],
        ':calle' => $ocupacion['calle'],
        ':lat' => $ocupacion['latitud'],
        ':lng' => $ocupacion['longitud']
    ]);

    $conexion->commit();

    volver('¡Gracias! Avisaste que liberaste el lugar.');

} catch (PDOException $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log('Error al liberar lugar: ' . $e->getMessage());

    volver('No se pudo registrar la liberación. Revisá que exista la tabla lugares_liberados.');
}