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

// La hora de estacionamiento la define el SERVIDOR (no el formulario),
// así nadie puede manipularla desde el navegador.
$hora = date('H:i');

$horaLiberacion = $_POST['hora_liberacion'] ?? '';
$idAuto = filter_input(INPUT_POST, 'vehiculo', FILTER_VALIDATE_INT);
$calle = trim($_POST['calle'] ?? '');

$lat = ($_POST['latitud'] ?? '') !== ''
    ? filter_var($_POST['latitud'], FILTER_VALIDATE_FLOAT)
    : null;

$lng = ($_POST['longitud'] ?? '') !== ''
    ? filter_var($_POST['longitud'], FILTER_VALIDATE_FLOAT)
    : null;

// Validar formato de la hora de liberación (HH:MM)
$patronHora = '/^([01]\d|2[0-3]):[0-5]\d$/';

if (
    !preg_match($patronHora, $horaLiberacion) ||
    !$idAuto ||
    $calle === '' ||
    mb_strlen($calle) > 150 ||
    $lat === false ||
    $lng === false ||
    $lat === null ||
    $lng === null ||
    $lat < -90 ||
    $lat > 90 ||
    $lng < -180 ||
    $lng > 180
) {
    volver('Revisá la hora de liberación, el vehículo, la calle y las coordenadas.');
}

// Horas iguales = duración cero (no tendría sentido).
// Si la liberación es menor que la hora actual se interpreta como
// "mañana" (cruza la medianoche), y eso está permitido.
if ($horaLiberacion === $hora) {
    volver('La hora de liberación no puede ser igual a la hora actual.');
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

    // Verificar que el vehículo pertenezca al usuario
    $s = $conexion->prepare(
        'SELECT id FROM automovil
         WHERE id = :auto AND id_usuario = :usuario'
    );
    $s->execute([
        ':auto' => $idAuto,
        ':usuario' => $usuario['id']
    ]);

    if (!$s->fetch()) {
        volver('El vehículo seleccionado no está asignado a tu usuario.');
    }

    // Rechazar calles marcadas como no disponibles (tramos en azul).
    $idTramo = filter_input(INPUT_POST, 'id_tramo', FILTER_VALIDATE_INT);

    if ($idTramo) {
        $s = $conexion->prepare(
            'SELECT 1 FROM tramos_no_disponibles
             WHERE id_tramo = :tramo LIMIT 1'
        );
        $s->execute([':tramo' => $idTramo]);

        if ($s->fetch()) {
            volver('Esa calle está marcada como no disponible y no se puede ocupar.');
        }
    }

    // Buscar la ocupación existente de ese usuario y automóvil
    $s2 = $conexion->prepare(
        'SELECT id
         FROM ocupa
         WHERE id_automovil = :id_automovil
           AND id_usuario = :id_usuario
         ORDER BY id DESC
         LIMIT 1'
    );
    $s2->execute([
        ':id_automovil' => $idAuto,
        ':id_usuario' => $usuario['id']
    ]);

    $fila = $s2->fetch(PDO::FETCH_ASSOC);

    if ($fila) {

        // Actualizar la ocupación existente
        $stmtUpdate = $conexion->prepare(
            'UPDATE ocupa
             SET longitud = :lng,
                 latitud = :lat,
                 calle = :calle,
                 hora_estacionamiento = :hora,
                 hora_liberacion = :hora_liberacion
             WHERE id = :id'
        );

        $stmtUpdate->execute([
            ':lng' => $lng,
            ':lat' => $lat,
            ':calle' => $calle,
            ':hora' => $hora,
            ':hora_liberacion' => $horaLiberacion,
            ':id' => $fila['id']
        ]);

        volver('La ocupación se actualizó correctamente.');
    }

    // Si no existe una ocupación anterior, insertar una nueva
    $s = $conexion->prepare(
        'INSERT INTO ocupa
         (id_usuario, id_automovil, hora_estacionamiento,
          hora_liberacion, calle, latitud, longitud)
         VALUES
         (:usuario, :auto, :hora, :hora_liberacion,
          :calle, :lat, :lng)'
    );

    $s->execute([
        ':usuario' => $usuario['id'],
        ':auto' => $idAuto,
        ':hora' => $hora,
        ':hora_liberacion' => $horaLiberacion,
        ':calle' => $calle,
        ':lat' => $lat,
        ':lng' => $lng
    ]);

    volver('La ocupación se guardó correctamente.');

} catch (PDOException $e) {

    error_log('Error al guardar ocupación: ' . $e->getMessage());

    volver(
        'No se pudo guardar la ocupación. Revisá la estructura de la tabla ocupa.'
    );
}