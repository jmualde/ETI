
<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

function volver($mensaje) {
    $_SESSION['mensaje_ocupar'] = $mensaje;
    header('Location: ocupar_lugar.php');
    exit();
}

$hora = $_POST['hora'] ?? '';
$horaLiberacion = $_POST['hora_liberacion'] ?? '';
$idAuto = filter_input(INPUT_POST, 'vehiculo', FILTER_VALIDATE_INT);
$calle = trim($_POST['calle'] ?? '');

$lat = ($_POST['latitud'] ?? '') !== ''
    ? filter_var($_POST['latitud'], FILTER_VALIDATE_FLOAT)
    : null;

$lng = ($_POST['longitud'] ?? '') !== ''
    ? filter_var($_POST['longitud'], FILTER_VALIDATE_FLOAT)
    : null;

// Validar ambas horas
$patronHora = '/^([01]\d|2[0-3]):[0-5]\d$/';

if (
    !preg_match($patronHora, $hora) ||
    !preg_match($patronHora, $horaLiberacion) ||
    !$idAuto ||
    $calle === '' ||
    mb_strlen($calle) > 150
) {
    volver('Revisá las horas, el vehículo y la calle seleccionada.');
}

try {
    // Buscar al usuario que inició sesión
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

    // Guardar la ocupación con las dos horas
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
    volver('No se pudo guardar la ocupación. Revisá la estructura de la tabla ocupa.');
}
