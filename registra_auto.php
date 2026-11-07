<?php
session_start();
require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar vehículo</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="disenio.css?v=20261106">
    <style>
        html, body {
            min-height: 100%;
            margin: 0;
            padding: 0;
            background: #080808;
            font-family: "Inter", sans-serif;
        }

        .registro-auto-layout {
            position: relative;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            min-height: 100vh;
        }

        .registro-auto-layout > .nav-registro-auto {
            position: absolute;
            z-index: 1;
            top: 0;
            left: 50%;
            width: max-content;
            max-width: calc(100% - 24px);
            transform: translateX(-50%);
            background: rgba(16, 18, 20, 0.58);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
            backdrop-filter: blur(8px);
        }

        .registro-auto-form-panel {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
            padding: 28px clamp(24px, 5vw, 84px) 48px;
            background: #080808;
        }

        .registro-auto-content {
            width: min(100%, 470px);
            margin: auto;
            padding: 48px 0;
        }

        .registro-auto-content h1 {
            margin: 0 0 36px;
            color: #fff;
            font-size: clamp(28px, 3vw, 38px);
            font-weight: 600;
            text-align: left;
        }

        .registro-auto-content label.textoBlanco {
            display: block;
            margin-bottom: 8px;
            padding: 0;
            border-radius: 0;
            color: #dedede;
            font-size: 15px;
            text-align: left;
        }

        .registro-auto-content .form-control {
            height: 52px;
            border: 1px solid #444;
            border-radius: 12px;
            background-color: #151515;
            color: #fff;
        }

        .registro-auto-content .form-control:focus {
            border-color: #aaa;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.12);
        }

        .registro-auto-content .botonRegistra {
            height: 52px;
            margin-top: 12px;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .registro-auto-content .botonRegistra:hover {
            background-color: #dedede;
            transform: translateY(-1px);
        }

        .registro-auto-image {
            position: relative;
            background: url("cdmx.jpg") center 78% / cover no-repeat;
        }

        .registro-auto-image::before {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #080808 0%, rgba(8, 8, 8, 0.72) 12%, rgba(8, 8, 8, 0.08) 52%, rgba(8, 8, 8, 0.18) 100%);
            content: "";
        }

        @media (max-width: 767.98px) {
            .registro-auto-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .registro-auto-form-panel {
                min-height: 100vh;
                padding: 20px 20px 40px;
            }

            .registro-auto-image {
                min-height: 280px;
                background-position: center 82%;
            }

            .registro-auto-image::before {
                background: linear-gradient(180deg, rgba(8, 8, 8, 0.08) 0%, rgba(8, 8, 8, 0.72) 100%);
            }
        }

        @media (max-width: 576px) {
            .registro-auto-layout > .nav-registro-auto {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                width: calc(100% - 20px);
                max-width: none;
                padding: 8px 6px;
            }

            .registro-auto-layout > .nav-registro-auto a,
            .registro-auto-layout > .nav-registro-auto .textoBlanco {
                width: 100%;
                max-width: 100%;
                padding: 6px 2px;
                font-size: 11px;
                line-height: 1.15;
                text-align: center;
                word-break: normal;
            }
        }

        @media (max-width: 380px) {
            .registro-auto-layout > .nav-registro-auto {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
</head>

<body>
    <main class="registro-auto-layout">
        <nav class="tarjetaArriba nav-registro-auto" aria-label="Navegación principal">
            <a href="principal.php">
                <p class="textoBlanco">Página Principal</p>
            </a>

            <a href="registra_auto.php">
                <p class="textoBlanco">Registrar vehículo</p>
            </a>

            <a href="ocupar_lugar.php">
                <p class="textoBlanco">Ocupar lugar</p>
            </a>

            <a href="cuenta.php">
                <p class="textoBlanco">Cuenta</p>
            </a>
        </nav>

        <section class="registro-auto-form-panel">
            <div class="registro-auto-content">
                <h1>Ingrese los datos del vehículo</h1>
                <form action="guardar_auto.php" method="POST">
                    <div class="mb-3">
                        <label for="marca" class="textoBlanco">Marca</label>
                        <select class="campo form-control" id="marca" name="marca" required>
                            <option value="">Seleccione una marca</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modelo" class="textoBlanco">Modelo</label>
                        <div class="marca-color">
                            <select class="campo form-control" id="modelo" name="modelo" required>
                                <option value="">Primero seleccione marca</option>
                            </select>
                            <input type="color" class="campoColor" id="color" name="color">
                        </div>
                    </div>
                    <button type="submit" class="botonRegistra">Registrar vehículo</button>
                </form>
            </div>
        </section>

        <div class="registro-auto-image" aria-hidden="true"></div>
    </main>

    <!-- JAVASCRIPT -->

    <script>

        const selectMarca =
            document.getElementById("marca");


        /* CARGAR MARCAS */

        fetch("obtener_marcas.php")

            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        "Error HTTP: " + response.status
                    );

                }

                return response.json();

            })

            .then(data => {

                console.log(
                    "Datos recibidos:",
                    data
                );


                selectMarca.innerHTML = "";


                const opcionInicial =
                    document.createElement("option");


                opcionInicial.value = "";

                opcionInicial.textContent =
                    "Seleccione una marca";

                opcionInicial.disabled = true;

                opcionInicial.selected = true;


                selectMarca.appendChild(
                    opcionInicial
                );


                data.data.forEach(marca => {

                    const opcion =
                        document.createElement("option");


                    opcion.value =
                        marca.name;

                    opcion.textContent =
                        marca.name;


                    selectMarca.appendChild(
                        opcion
                    );

                });

            })


            .catch(error => {

                console.error(
                    "Error al cargar las marcas:",
                    error
                );


                selectMarca.innerHTML = "";


                const opcionError =
                    document.createElement("option");


                opcionError.value = "";

                opcionError.textContent =
                    "No se pudieron cargar las marcas";


                selectMarca.appendChild(
                    opcionError
                );

            });


        const selectModelo =
            document.getElementById("modelo");


        /* CARGAR MODELOS */

        selectMarca.addEventListener(
            "change",
            function () {

                const marcaSeleccionada =
                    this.value;


                selectModelo.innerHTML = "";


                const cargando =
                    document.createElement("option");


                cargando.value = "";

                cargando.textContent =
                    "Cargando modelos...";


                selectModelo.appendChild(
                    cargando
                );


                fetch(
                    "obtener_modelos.php?marca=" +
                    encodeURIComponent(
                        marcaSeleccionada
                    )
                )

                    .then(response => {

                        if (!response.ok) {

                            throw new Error(
                                "Error HTTP: " +
                                response.status
                            );

                        }

                        return response.json();

                    })

                    .then(data => {

                        console.log(
                            "Modelos recibidos:",
                            data
                        );


                        selectModelo.innerHTML =
                            "";


                        const opcionInicial =
                            document.createElement(
                                "option"
                            );


                        opcionInicial.value =
                            "";

                        opcionInicial.textContent =
                            "Seleccione un modelo";

                        opcionInicial.disabled =
                            true;

                        opcionInicial.selected =
                            true;


                        selectModelo.appendChild(
                            opcionInicial
                        );


                        data.data.forEach(
                            modelo => {

                                const opcion =
                                    document.createElement(
                                        "option"
                                    );


                                opcion.value =
                                    modelo.name;

                                opcion.textContent =
                                    modelo.name;


                                selectModelo.appendChild(
                                    opcion
                                );

                            }
                        );

                    })


                    .catch(error => {

                        console.error(
                            "Error al cargar los modelos:",
                            error
                        );


                        selectModelo.innerHTML =
                            "";


                        const opcionError =
                            document.createElement(
                                "option"
                            );


                        opcionError.value =
                            "";

                        opcionError.textContent =
                            "No se pudieron cargar los modelos";


                        selectModelo.appendChild(
                            opcionError
                        );

                    });

            }
        );

    </script>

</body>

</html>