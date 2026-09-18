<?php
require_once ("conexion.php");
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet">

    <title>Pagina Principal</title>

    <style>

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #101214;
            color: white;
            font-family: "Inter", sans-serif;
            overflow-x: hidden;
        }

        .tarjetaArriba-principal {
            background-color: #181C1F;
            width: 100%;
            min-height: 60px;

            display: flex;
            justify-content: center;
            align-items: center;

            gap: 35px;
            padding: 10px 20px;

            flex-wrap: wrap;
        }

        .tarjetaArriba-principal a {
            text-decoration: none;
            color: white;
            margin: 0;
        }

        .textoBlanco-principal {
            color: white;
            margin: 0;
            font-family: "Inter", sans-serif;
            transition: 0.3s;
        }

           .textoPanel {
            color: white;
            margin: 0;
            font-family: "Inter", sans-serif;
            font-size: 20px;
            font-weight: bold;
        }

        .textoBlanco-principal:hover {
            color: #c7c7c7;
        }

        .textoPrincipal {
            font-size: clamp(40px, 5vw, 70px);
            font-weight: bold;
            color: #f0e8d8;

            margin: 0;
            max-width: 900px;
        }

        /* =========================
           TABLET
        ========================= */

        @media (max-width: 991px) {

            .tarjetaArriba-principal {
                gap: 20px;
            }

            .seccionPrincipal {
                padding-top: 70px;
                padding-bottom: 70px;
            }

            .textoPrincipal {
                text-align: center;
                margin: 0 auto;
            }

            

        }

        /* =========================
           CELULAR
        ========================= */

        @media (max-width: 576px) {

            .tarjetaArriba-principal {
                min-height: auto;

                flex-direction: column;
                gap: 12px;

                padding: 18px 10px;
            }

            .tarjetaArriba-principal a {
                font-size: 14px;
            }

            .seccionPrincipal {
                padding: 50px 15px;
            }

            .textoPrincipal {
                font-size: 40px;
                line-height: 1.1;
            }
        }

    </style>
</head>


<body>

    <div class="container-fluid p-0">


        <nav class="tarjetaArriba-principal">

            <a href="principal_admin.html">
                <p class="textoBlanco-principal">Página Principal</p>
            </a>

            <a href="registra_auto.html">
                <p class="textoBlanco-principal">Registrar vehículo</p>
            </a>

            <a href="ocupar_lugar.html">
                <p class="textoBlanco-principal">Ocupar lugar</p>
            </a>

            <a href="cuenta.php">
                <p class="textoBlanco-principal">Cuenta</p>
            </a>

            <a href="panel_admin.php">
                <p class="textoBlanco-principal">Panel de administración</p>
            </a>

        </nav>
<br>
            <div class="container">

                <div class="row align-items-center">
                    <center>
                    <h1 class="textoPrincipal">Panel de Administración</h1>
                    </center>
                <div class="col-12">
                    <br><br>
                    <label class="textoPanel">Usuarios :</label>
        <br>
        
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre de usuario</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Apellido</th>
                                <th scope="col">Correo electrónico</th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $sql1 = "SELECT * FROM usuario";
                            $resultado1 = $conexion->query($sql1);

                            while ($fila1 = $resultado1->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . $fila1['id'] . "</td>";
                                echo "<td>" . $fila1['nombre_usuario'] . "</td>";
                                echo "<td>" . $fila1['nombre'] . "</td>";
                                echo "<td>" . $fila1['apellido'] . "</td>";
                                echo "<td>" . $fila1['email'] . "</td>";
                                echo "<td><button class='boton' onclick='editar_usuario(" . $fila1['id'] . ")'>Editar</button></td>";
                                echo "<td><button class='boton' onclick='eliminar_usuario(" . $fila1['id'] . ")'>Eliminar</button></td>";
                                echo "</tr>";
                            }
                            ?>

                        </tbody>
                    </table>
                    <br>
                    <label class="textoPanel">Automoviles :</label>
                    <br>
                                 <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Marca</th>
                                <th scope="col">Modelo</th>
                                <th scope="col">Color</th>
                                <th scope="col">ID Usuario</th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $sql2 = "SELECT * FROM automovil";
                            $resultado2 = $conexion->query($sql2);

                            while ($fila2 = $resultado2->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . $fila2['id'] . "</td>";
                                echo "<td>" . $fila2['marca'] . "</td>";
                                echo "<td>" . $fila2['modelo'] . "</td>";
                                echo "<td>" . $fila2['color'] . "</td>";
                                echo "<td>" . $fila2['id_usuario'] . "</td>";
                                echo "<td><button class='boton' onclick='editar_automovil(" . $fila2['id'] . ")'>Editar</button></td>";
                                echo "<td><button class='boton' onclick='eliminar_automovil(" . $fila2['id'] . ")'>Eliminar</button></td>";
                                echo "</tr>";
                            }
                            ?>

                        </tbody>
                    </table>
                    <br>
                        <label class="textoPanel">Ocupar lugar :</label>
                    <br>
                                     <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">CoordenadaX</th>
                                <th scope="col">CoordenadaY</th>
                                <th scope="col">ID Usuario</th>
                                <th scope="col">Fecha_inicio</th>
                                <th scope="col">Fecha_fin</th>
                                <th scope="col">ID Automovil</th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $sql3 = "SELECT * FROM ocupa";
                            $resultado3 = $conexion->query($sql3);

                            while ($fila3 = $resultado3->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . $fila3['id'] . "</td>";
                                echo "<td>" . $fila3['coordenadaX'] . "</td>";
                                echo "<td>" . $fila3['coordenadaY'] . "</td>";
                                echo "<td>" . $fila3['id_usuario'] . "</td>";
                                echo "<td>" . $fila3['fecha_inicio'] . "</td>";
                                echo "<td>" . $fila3['fecha_fin'] . "</td>";
                                echo "<td>" . $fila3['id_automovil'] . "</td>";
                                echo "<td><button class='boton' onclick='editar_ocupa(" . $fila3['id'] . ")'>Editar</button></td>";
                                echo "<td><button class='boton' onclick='eliminar_ocupa(" . $fila3['id'] . ")'>Eliminar</button></td>";
                                echo "</tr>";
                            }
                            ?>

                        </tbody>
                    </table>
                                 

                </div>

            </div>


          



    </div>
    <script>
        function editar_usuario(id) {
         window.location.href = "editar_usuarios.php?id=" + id;
        }

        function eliminar_usuario(id) {
            window.location.href = "eliminar_usuarios.php?id=" + id;
        }

        function editar_automovil(id) {
         window.location.href = "editar_automovil.php?id=" + id;
        }

        function eliminar_automovil(id) {
            window.location.href = "eliminar_automovil.php?id=" + id;
        }

        function editar_ocupa(id) {
            window.location.href = "editar_ocupa.php?id=" + id;
        }

        function eliminar_ocupa(id) {
            window.location.href = "eliminar_ocupa.php?id=" + id;
        }
             

        function editar_ocupa(id) {
            window.location.href = "editar_ocupa.php?id=" + id;
        }

        function eliminar_ocupa(id) {
            window.location.href = "eliminar_ocupa.php?id=" + id;
        }
    </script>

</body>

</html>
