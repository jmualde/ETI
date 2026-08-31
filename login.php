<?php
require_once ("conexion.php");
$sql = "SELECT * FROM usuarios";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="disenio.css">
        
</head>

<body>

<div class="contenedor">

    
    <div class="tarjeta">

        <!-- FORMULARIO -->
        <div class="columna-formulario">

            <h3 class="text-center">
                Inicio de sesión
            </h3>

            <form method="POST" action="procesa.php">

                <div class="mb-3">
                    <label for="user" class="form-label">
                        <span class="azul">
                            Inicia sesión con tu usuario
                        </span>
                    </label>

                    <input
                        type="text"
                        class="campo"
                        id="user"
                        name="user"
                        placeholder="Ingrese su usuario"
                    >
                </div>

                <div class="mb-3">
                    <label for="contrasenia" class="form-label">
                        <span class="gris">
                            Contraseña
                        </span>
                    </label>

                    <input
                        type="password"
                        class="campo"
                        name="contrasenia"
                        id="contrasenia"
                        placeholder="Ingrese su contraseña aquí"
                    >
                </div>

                <div class="mb-3">
                    <button
                        type="submit"
                        class="botonIniciar"
                        id="btnIniciar"
                        name="btnIniciar"
                    >
                        Iniciar sesión
                    </button>
                </div>

            </form>

           
            <div class="text-center">
                <form action="olvidoContrasenia.html" method="POST">

                    <button type="submit" class="botonAyuda">
                        <span class="gris">
                            Ayuda, olvidé mi contraseña
                        </span>
                    </button>

                </form>
            </div>

        </div>

        
        <div class="columna-imagen">

            <img
                src="pexels-kaue-barbier-710715348-30233581.jpg"
                class="imagenauto"
                alt="Automóvil"
            >

        </div>

    </div>

    
    <div class="registro">

        <h5>
            ¿Tu primera vez en nuestra web?
        </h5>

        <form method="POST" action="registra.html">

            <button
                type="submit"
                class="botonRegistra"
                id="btnRegistra"
                name="btnRegistra"
            >
                Registrate
            </button>

        </form>

    </div>

</div>

</body>
</html>
