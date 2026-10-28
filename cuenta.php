
<?php

session_start();

require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

try {

    // Buscar usuario
    $sql = "SELECT * FROM usuario WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $_SESSION["id_usuario"]
    ]);

    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar TODOS los autos del usuario
    $sqlAuto = "SELECT * FROM automovil WHERE id_usuario = ?";
    $stmtAuto = $conexion->prepare($sqlAuto);
    $stmtAuto->execute([
        $_SESSION["id_usuario"]
    ]);

    $autos_select = $stmtAuto->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">

    <link rel="stylesheet" href="disenio.css">

    <title>Cuenta</title>
<style>
 #color:disabled {
    opacity: 1 !important;
    background-color: transparent !important;
    filter: none !important;
}

input:disabled,
select:disabled {
    opacity: 1 !important;
    background-color: transparent !important;
    color: white !important;
    border-color: white !important;
}
</style>
</head>

<body class="pagina-cuenta">

<div class="container-fluid p-0">

    <nav class="tarjetaArriba-cuenta">

        <a href="principal.php">
            <p class="textoBlanco-principal">Página Principal</p>
        </a>

        <a href="registra_auto.php">
            <p class="textoBlanco-principal">Registrar vehículo</p>
        </a>

        <a href="ocupar_lugar.php">
            <p class="textoBlanco-principal">Ocupar lugar</p>
        </a>

        <a href="cuenta.php">
            <p class="textoBlanco-principal">Cuenta</p>
        </a>

    </nav>

    <br><br><br>

    <div class="text-center">

        <h1>Tu cuenta</h1>

    </div>

    <br><br>

    <div class="row justify-content-center">

        <!-- DATOS DEL USUARIO -->

        <div class="col-12 col-md-6 col-lg-4">

            <label>Nombre de usuario:</label>

            <div class="d-flex gap-2 align-items-center">

                <input
                    class="campo-cuenta form-control"
                    type="text"
                    id="nombre_usuario"
                    value="<?= htmlspecialchars($fila["nombre_usuario"]) ?>"
                    readonly
                >

                <button
                    class="btn btn-primary"
                    style="background-color: white; color: black; border: 1px solid white; max-height: 40px;"
                    onclick="editar('nombre_usuario')"
                    id="btnNombreUsuario"
                >
                    Editar
                </button>

            </div>

            <br>

            <label>Correo electrónico:</label>

            <div class="d-flex gap-2 align-items-center">

                <input
                    class="campo-cuenta form-control"
                    type="email"
                    id="email"
                    value="<?= htmlspecialchars($fila["email"] ?? '') ?>"
                    readonly
                >

                <button
                    class="btn btn-primary"
                    style="background-color: white; color: black; border: 1px solid white; max-height: 40px;"
                    onclick="editar('email')"
                    id="btnEmail"
                >
                    Editar
                </button>

            </div>


            <br>

            <label>Contraseña:</label>

            <div class="d-flex gap-2 align-items-center">

                <input
                    class="campo-cuenta form-control"
                    type="password"
                    id="contrasenia"
                    value="<?= htmlspecialchars($fila["contrasenia"]) ?>"
                    readonly
                >

                <button
                    class="btn btn-primary"
                    style="background-color: white; color: black; border: 1px solid white; max-height: 40px;"
                    onclick="editar('contrasenia')"
                    id="btnContrasenia"
                >
                    Editar
                </button>

            </div>

        </div>


        <!-- AUTOS -->

        <div class="col-12 col-md-6 col-lg-4">

            <label>Elige vehículo para editar:</label>
            <form action="elegir_auto.php" method="POST">
            <div class="d-flex gap-2 align-items-center">
            <select
            class="campo-cuenta form-control"
            id="auto_seleccionado"
            name="auto_seleccionado"
            onchange="cambiarAuto()"
            >
    <?php if (count($autos_select) > 0) { ?>

        <?php foreach ($autos_select as $auto) { ?>

            <option
                value="<?= htmlspecialchars($auto['id']) ?>"
                 <?= (isset($_GET['auto']) && $_GET['auto'] == $auto['id']) ? 'selected' : '' ?> 
            >
                <?= htmlspecialchars($auto['marca']) . " - " . htmlspecialchars($auto['modelo']); ?>
            </option>

             <?php } ?>

            <?php } else { ?>

            <option value="">No tenés vehiculos registrados</option>

            <?php } ?>
            </select>


            <button type="sumbit" class="btn btn-primary" style="background-color: white; color: black; border: 1px solid white; max-height: 40px;">Elegir</button>
            </form>
            </div>

            <br><br>



            <!-- MARCA -->

            <label>Marca del auto:</label>

            <div class="d-flex gap-2 align-items-center">

                <SELECT
                    class="campo-cuenta form-control"
                    id="marca"
                    disabled
                >
            <option value="<?= $marca = $_GET['marca'] ?? ''?>"><?= $marca = $_GET['marca'] ?? ''?></option>
            </SELEct>

                <button
                    class="btn btn-primary"
                    style="background-color: white; color: black; border: 1px solid white; max-height: 40px;"
                    onclick="editar('marca')"
                    id="btnMarca"
                >
                    Editar
                </button>

            </div>

            <br>


            <!-- MODELO -->

            <label>Modelo:</label>

            <div class="d-flex gap-2 align-items-center">

                <SELECT
                    class="campo-cuenta form-control"
                    id="modelo"
                    disabled
                >
                <option value="<?= $modelo = $_GET['modelo'] ?? ''?>"><?= $modelo = $_GET['modelo'] ?? ''?></option>
                </SELEct>

                <button
                    class="btn btn-primary"
                    style="background-color: white; color: black; border: 1px solid white; max-height: 40px;"
                    onclick="editar('modelo')"
                    id="btnModelo"
                >
                    Editar
                </button>

            </div>

            <br>


            <!-- COLOR -->

            <label>Color:</label>

            <div class="d-flex gap-2 align-items-center">

                <input
                    class="campo-cuenta form-control"
                    id="color"
                    type="color"
                    value="<?= $color = $_GET['color'] ?? ''?>"
                    disabled
                >

                <button
                    class="btn btn-primary"
                    style="background-color: white; color: black; border: 1px solid white; max-height: 40px;"
                    onclick="editar('color')"
                    id="btnColor"
                >
                    Editar
                </button>

            </div>

        </div>

    </div>


    <br><br>


    <!-- BOTONES -->

    <div class="d-flex justify-content-center gap-2">

        <form action="cerrar_sesion.php" method="post">

            <button
                class="btn btn-danger"
                style="background-color: transparent; border-color: #dc3545; color: #dc3545;"
                type="submit"
            >
                Cerrar sesión
            </button>

        </form>

        <form action="principal.php" method="post">

            <button
                class="btn btn-danger"
                style="background-color: transparent; border-color: green; color: green;"
                type="submit"
            >
                Menú principal
            </button>

        </form>

    </div>

</div>


<script>

// Guardamos los autos que vienen desde PHP
const autos = <?= json_encode($autos_select) ?>;

document.getElementById("marca").addEventListener("change", function () {

    const marcaSeleccionada = this.value;
    const selectModelo = document.getElementById("modelo");

    selectModelo.disabled = true;

    selectModelo.innerHTML = "";

    const cargando =
        document.createElement("option");

    cargando.textContent = "Cargando modelos...";

    selectModelo.appendChild(cargando);


    fetch(
        "obtener_modelos.php?marca=" +
        encodeURIComponent(marcaSeleccionada)
    )
        .then(response => response.json())
        .then(data => {

            selectModelo.innerHTML = "";

            const opcionInicial =
                document.createElement("option");

            opcionInicial.value = "";
            opcionInicial.textContent = "Seleccione un modelo";

            selectModelo.appendChild(opcionInicial);


            data.data.forEach(modelo => {

                const opcion =
                    document.createElement("option");

                opcion.value = modelo.name;
                opcion.textContent = modelo.name;

                selectModelo.appendChild(opcion);

            });

        })
        .catch(error => {

            console.error(
                "Error al cargar los modelos:",
                error
            );

        });
});

function cargarModelos() {

    const selectModelo = document.getElementById("modelo");
    const marcaSeleccionada = document.getElementById("marca").value;

    selectModelo.disabled = true;

    selectModelo.innerHTML = "";


    fetch(
        "obtener_modelos.php?marca=" +
        encodeURIComponent(marcaSeleccionada)
    )
    .then(response => response.json())
    .then(data => {

        selectModelo.innerHTML = "";

        const opcionInicial = document.createElement("option");

        opcionInicial.value = "";
        opcionInicial.textContent = "Seleccione un modelo";

        selectModelo.appendChild(opcionInicial);

        data.data.forEach(modelo => {

            const opcion = document.createElement("option");

            opcion.value = modelo.name;
            opcion.textContent = modelo.name;

            selectModelo.appendChild(opcion);

        });

        selectModelo.disabled = false;

    })
    .catch(error => {

        console.error("Error al cargar los modelos:", error);

    });
}
// Función para editar
function editar(id) {

    const input = document.getElementById(id);

    let botonEditar;

    if (id === "color") {

        botonEditar = document.getElementById("btnColor");
        input.disabled = false;

    } else if (id === "marca") {

        botonEditar = document.getElementById("btnMarca");
        input.disabled = false;

        fetch("obtener_marcas.php")
            .then(response => response.json())
            .then(data => {

                const marcaActual = input.value;

                input.innerHTML = "";

                const opcionInicial = document.createElement("option");
                opcionInicial.value = "";
                opcionInicial.textContent = "Seleccione una marca";

                input.appendChild(opcionInicial);

                data.data.forEach(marca => {

                    const opcion = document.createElement("option");

                    opcion.value = marca.name;
                    opcion.textContent = marca.name;

                    if (marca.name === marcaActual) {
                        opcion.selected = true;
                    }

                    input.appendChild(opcion);
                });
                 cargarModelos();   
            });

    } else if (id === "modelo") {
    botonEditar = document.getElementById("btnModelo");
    input.disabled = false;
    cargarModelos();

    } else if (id === "nombre_usuario") {

        botonEditar = document.getElementById("btnNombreUsuario");
        input.readOnly = false;

    } else if (id === "email") {

        botonEditar = document.getElementById("btnEmail");
        input.readOnly = false;

    } else if (id === "contrasenia") {

        botonEditar = document.getElementById("btnContrasenia");
        input.readOnly = false;
    }


    input.focus();

    botonEditar.style.display = "none";


    const botonGuardar = document.createElement("button");

    botonGuardar.innerHTML = "Guardar";

    botonGuardar.className = "btn btn-primary";

    botonGuardar.style =
        "background-color: white; color: black; border: 1px solid white; max-height: 40px;";


    botonGuardar.onclick = function () {

        const autoId =
            document.getElementById("auto_seleccionado").value;

        let url =
            "actualizar_cuenta.php?campo="
            + encodeURIComponent(id)
            + "&valor="
            + encodeURIComponent(input.value);


        if (id === "marca" || id === "modelo" || id === "color") {

            url +=
                "&id_auto="
                + encodeURIComponent(autoId);
        }


        fetch(url)
            .then(response => {

                if (!response.ok) {
                    throw new Error("Error al guardar");
                }

                return response.text();

            })
            .then(data => {

                input.disabled = true;
                input.readOnly = true;

                botonGuardar.remove();

                botonEditar.style.display = "block";

            })
            .catch(error => {

                console.error(error);
                alert("Hubo un error al guardar");

            });

    };


    input.parentNode.appendChild(botonGuardar);
}



</script>

</body>

</html>
