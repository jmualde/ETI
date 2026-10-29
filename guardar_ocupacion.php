
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

$patronHora = '/^([01]\d|2[0-3]):[0-5]\d$/';

if (
    !preg_match($patronHora, $hora) ||
    !preg_match($patronHora, $horaLiberacion) ||
    !$idAuto ||
    $calle === '' ||
    mb_strlen($calle) > 150 ||
    $lat === false ||
    $lng === false
) {
    volver('Revisá las horas, el vehículo y la calle seleccionada.');
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

    // Verificar que el auto pertenezca al usuario
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

    // Buscar la última ocupación de ese usuario y automóvil
    $sql2 = 'SELECT id, hora_estacionamiento
             FROM ocupa
             WHERE id_automovil = :id_automovil
               AND id_usuario = :id_usuario
             ORDER BY hora_estacionamiento DESC, id DESC
             LIMIT 1';

    $s2 = $conexion->prepare($sql2);
    $s2->execute([
        ':id_automovil' => $idAuto,
        ':id_usuario' => $usuario['id']
    ]);

    $fila = $s2->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        // Solo actualizar si la hora nueva es más reciente
        if ($hora <= substr($fila['hora_estacionamiento'], 0, 5)) {
            volver(
                'La nueva hora debe ser posterior a la última ocupación.'
            );
        }

        // Actualizar exactamente la fila anterior por su ID
        $sqlUpdate = 'UPDATE ocupa
                      SET longitud = :lng,
                          latitud = :lat,
                          calle = :calle,
                          hora_estacionamiento = :hora,
                          hora_liberacion = :hora_liberacion
                      WHERE id = :id';

        $stmtUpdate = $conexion->prepare($sqlUpdate);

        $stmtUpdate->execute([
            ':lng' => $lng,
            ':lat' => $lat,
            ':calle' => $calle,
            ':hora' => $hora,
            ':hora_liberacion' => $horaLiberacion,
            ':id' => $fila['id']
        ]);

        volver('La ocupación anterior se actualizó correctamente.');
    }

    // Si no existe ninguna ocupación anterior, insertar una nueva
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
