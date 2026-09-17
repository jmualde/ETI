<?php

session_start();
require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

try {



    $sql = "SELECT * FROM usuario  WHERE nombre_usuario = :nombreUsuario";


    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':nombreUsuario' => $_SESSION["usuario"]
    ]);

    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar auto
    $sqlAuto = "SELECT * FROM automovil WHERE id_usuario = :idUsuario";

    $stmtAuto = $conexion->prepare($sqlAuto);

    $stmtAuto->execute([
        ':idUsuario' => $fila["id"]
    ]);

    $auto = $stmtAuto->fetch(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    echo "Error: " . $e->getMessage();

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <link rel="stylesheet" href="disenio.css">
    <title>Cuenta</title>
</head>
<body class="pagina-cuenta">
    <div class="container-fluid">
         <nav class="tarjetaArriba-principal">

<body>
    <div class="container-fluid p-0">

        <nav class="tarjetaArriba-cuenta">


            <a href="principal.php">
                <p class="textoBlanco-principal">Página Principal</p>
            </a>

            <a href="registra_auto.html">
                <p class="textoBlanco-principal">Registrar vehículo</p>
            </a>

            <a href="ocupar_lugar.php">
                <p class="textoBlanco-principal">Ocupar lugar</p>
            </a>

            <a href="cuenta.php">
                <p class="textoBlanco-principal">Cuenta</p>
            </a>


        </nav>
        <br>
    

        </nav><br><br><br>
<center>
        <h1>Tu cuenta</h1>
        </center> 

<br><br>

        <div class=" row justify-content-center">

            <div class="col-12 col-md-6 col-lg-4">

                <label>Nombre de usuario:</label>

                <div class="d-flex gap-2 align-items-center">
                    <input class="campo-cuenta form control" type="text" id="nombre_usuario" value="<?= htmlspecialchars($fila["nombre_usuario"]) ?>" readonly>
                    <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('nombre_usuario')">Editar</button>
                </div>

                <br>
                <label>Correo electrónico:</label>

                <div class="d-flex gap-2 align-items-center">
                <input class="campo-cuenta form control" type="email" id="email" value="<?= htmlspecialchars($fila["email"]?? '') ?>" readonly>
                <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('email')">Editar</button>
                
                </div>
                <br>
                   <label >Contraseña:</label>

                <div class="d-flex gap-2 align-items-center">
                <input class="campo-cuenta form control" type="password" id="contrasenia" value="<?= htmlspecialchars($fila["contrasenia"]) ?>" readonly>
                <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('contrasenia')">Editar</button>
                
                </div>
                <br>
                </div>
                <div class="col-12 col-md-6 col-lg-4" >
                    <label>Marca del auto</label>
                    <div class="d-flex gap-2 align-items-center">
                      <input class="campo-cuenta form-control" type="text" id="marca" value="<?= htmlspecialchars($auto["marca"]) ?>" readonly> 
                      <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('marca')">Editar</button>
                      </div>                
                      <br>
                      <label>Modelo:</label>
                      <div class="d-flex gap-2 align-items-center">
                      <input class="campo-cuenta form-control"type="text" id="modelo" value="<?= htmlspecialchars($auto["modelo"] ) ?>"readonly>
                      <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('modelo')">Editar</button>
                      </div>
                      <br>
                      <label> Color: </label>
                      <div class="d-flex gap-2 align-items-center">
                      <input class="campo-cuenta form-control"id="color"type="text"value="<?= htmlspecialchars($auto["color"] ) ?>"readonly> 
                      <button class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;" onclick="editar('color')">Editar</button>
                      </div>




                </div>
                <div class="d-flex justify-content-center gap-2">
                <form action="cerrar_sesion.php" method="post">
                    <button class="btn btn-danger "style=" background-color: transparent; border-color: #dc3545; color: #dc3545;" type="submit">Cerrar sesión</button> 
                    
                </form>
                
                <form action="principal.php" method="post">
                    <button class="btn btn-danger "style=" background-color: transparent; border-color: green; color: green;" type="submit">Menu principal</button>
                </form> 

                 </div>
       
        </div>
       

    </div>
    
    <script> 


        function editar(id) {
            const input = document.getElementById(id);

            input.readOnly = false;
            input.focus();

            let boton = document.createElement("button");

            boton.innerHTML = "Guardar";

            boton.className = "btn btn-primary";
            boton.setAttribute("style", "background-color: white; color: black; border: 1px solid white; max-height: 40px;");
        
            boton.onclick = function() {
                input.readOnly = true;
                window.location.href="actualizar_cuenta.php?campo=" + encodeURIComponent(id) + "&valor=" + encodeURIComponent(input.value);
                boton.remove();
            }
            
                input.parentNode.appendChild(boton);
               
            
        }
    </script>

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