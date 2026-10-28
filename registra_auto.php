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

    <link rel="stylesheet" href="disenio.css?v=<?= filemtime(__DIR__ . '/disenio.css') ?>">
</head>

<body data-theme="dark">
    <div class="container-fluid p-0">

    <nav class="tarjetaArriba">

        <a href="principal.php">
            <h6 class="textoBlanco">Página Principal</h6>
        </a>

        <a href="registra_auto.php">
            <h6 class="textoBlanco">Registrar vehículo</h6>
        </a>

        <a href="ocupar_lugar.php">
            <h6 class="textoBlanco">Ocupar lugar</h6>
        </a>

        <a href="cuenta.php">
            <h6 class="textoBlanco">Cuenta</h6>
        </a>

        <button
            class="botonTema"
            id="botonTema"
            type="button"
            aria-pressed="false">
            Activar modo claro
        </button>

    </nav>
    <br><br>
    
         <center>
                <h1 >Ingrese los datos del vehículo  </h1>
            </center>
            <br>
            <div class="row justify-content-center">

                   
            <div class="col-12 col-md-6 col-lg-4">

            <form id="form-auto" action="guardar_auto.php" method="POST">

                <!-- MARCA -->

                <div class="mb-3" id="campo-marca">

                    <label for="marca" class="textoBlanco">
                        Marca
                    </label>
                    <select
                        class="campo form-control"
                        id="marca"
                        name="marca"
                        required>

                        <option value="">
                            Seleccione una marca
                        </option>

                    </select>

                </div>


                <!-- MODELO + COLOR -->

                <div class="mb-3">

                    <div class="marca-color">

                        <div id="campo-modelo" class="flex-grow-1">
                            <label for="modelo" class="textoBlanco">
                                Modelo
                            </label>
                            <select
                                class="campo form-control"
                                id="modelo"
                                name="modelo"
                                required>

                                <option value="">
                                    Primero seleccione marca
                                </option>

                            </select>
                        </div>


                        <input
                            type="color"
                            class="campoColor"
                            id="color"
                            name="color">

                    </div>

                </div>

                <div id="campos-marca-modelo-manual"></div>

                <!-- BOTÓN -->

                <button
                    type="submit"
                    class="botonRegistra">

                    Registrar vehículo

                </button>

            </form>
            <br><br>
            <center>
                <button
                    type="button"
                    id="btn_no_disponible"
                    name="btn_no_disponible"
                    class="botonmarcanoDisponible"
                    onclick="editarMarcaModelo()">
                    ¿Falta tu marca/modelo?
                </button>
            </center>
            </div>

        </div>
</div>

    <!-- JAVASCRIPT -->

    <script src="tema.js"></script>
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

        function editarMarcaModelo() {
            const campoMarca = document.getElementById("campo-marca");
            const campoModelo = document.getElementById("campo-modelo");
            const selectMarca = document.getElementById("marca");
            const selectModelo = document.getElementById("modelo");
            const contenedorManual = document.getElementById("campos-marca-modelo-manual");

            if (contenedorManual.childElementCount > 0) {
                return;
            }

            campoMarca.hidden = true;
            campoModelo.hidden = true;
            selectMarca.disabled = true;
            selectModelo.disabled = true;

            const inputMarca = document.createElement("input");
            inputMarca.type = "text";
            inputMarca.classList.add("campo", "form-control", "mb-3");
            inputMarca.name = "marca";
            inputMarca.placeholder = "Ingrese la marca";
            inputMarca.required = true;

            const inputModelo = document.createElement("input");
            inputModelo.type = "text";
            inputModelo.classList.add("campo", "form-control", "mb-3");
            inputModelo.name = "modelo";
            inputModelo.placeholder = "Ingrese el modelo";
            inputModelo.required = true;

            contenedorManual.append(inputMarca, inputModelo);
        }

    </script>

</body>

</html>
