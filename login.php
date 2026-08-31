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
    <style>
    

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            background-color: #101214;
            color: white;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            align-items: center;
            min-height: 100vh;
            padding: 30px 15px;
        }

       
        .contenedor {
            width: 100%;
            max-width: 1000px;
        }

        
        .tarjeta {
            width: 100%;
            min-height: 450px;
            padding: 40px;
            display: flex;
            align-items: center;

            background-color: #161a1d;
            border-radius: 15px;
            border: 1px solid darkslategray;

            box-shadow: 1px 1px 10px rgb(83, 83, 83);
        }

        
        .columna-formulario,
        .columna-imagen {
            width: 50%;
            padding: 20px;
        }

        
        h3 {
            margin-bottom: 30px;
        }

        
        .campo {
            width: 100%;
            height: 45px;
            padding: 0 15px;

            background-color: transparent;
            color: white;

            border: 1px solid gray;
            border-radius: 10px;

            outline: none;
        }

        .campo:focus {
            border-color: white;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.3);
        }

        .campo::placeholder {
            color: #999;
        }

        
        .gris {
            color: #C7D1DB;
            font-weight: bold;
            transition: 0.3s;
        }

        .gris:hover {
            color: #edf0f3;
        }

        .azul {
            color: #C7D1DB;
            font-weight: bold;
        }

        
        .botonIniciar {
            width: 100%;
            height: 45px;

            border-radius: 25px;
            background-color: white;
            color: black;

            font-weight: bold;
            border: none;

            cursor: pointer;
            transition: 0.3s;
        }

        .botonIniciar:hover {
            background-color: #d8d8d8;
            transform: scale(1.02);
        }

       
        .botonRegistra {
            width: 250px;
            max-width: 100%;

            height: 45px;

            border-radius: 25px;
            background: transparent;
            color: white;

            font-weight: bold;
            border: 2px solid white;

            cursor: pointer;
            transition: 0.3s;
        }

        .botonRegistra:hover {
            background-color: white;
            color: #101214;
        }

        
        .imagenauto {
            display: block;

            width: 100%;
            max-width: 450px;
            height: 350px;

            object-fit: cover;
            border-radius: 10px;

            margin: 0 auto;
        }

    
        .botonAyuda {
            background: none;
            border: none;
            padding: 0;

            color: #C7D1DB;
            text-decoration: none;
        }

        .botonAyuda:hover {
            color: white;
        }

    
        .registro {
            text-align: center;
            margin-top: 30px;
        }

        .registro h5 {
            margin-bottom: 15px;
        }

       
        @media (max-width: 768px) {

            body {
                padding: 20px 15px;
            }

            .tarjeta {
                flex-direction: column;
                padding: 25px;
            }

            .columna-formulario,
            .columna-imagen {
                width: 100%;
                padding: 15px;
            }

            .columna-imagen {
                order: -1;
            }

            .imagenauto {
                height: 250px;
                max-width: 100%;
            }
        }

      
        @media (max-width: 480px) {

            body {
                padding: 15px 10px;
            }

            .tarjeta {
                padding: 20px 15px;
                min-height: auto;
                border-radius: 12px;
            }

            .columna-formulario,
            .columna-imagen {
                padding: 10px 5px;
            }

            h3 {
                font-size: 24px;
                margin-bottom: 25px;
            }

            .imagenauto {
                height: 180px;
            }

            .campo {
                height: 43px;
                font-size: 14px;
            }

            .botonIniciar {
                height: 43px;
            }

            .botonRegistra {
                width: 100%;
            }

            .registro {
                margin-top: 25px;
            }
        }
    </style>
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
