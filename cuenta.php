<?php

session_start();
require_once("conexion.php");

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

try {

<<<<<<< Updated upstream
    $sql = "SELECT * FROM usuarios WHERE nombreUsuario = :nombreUsuario";
=======
    // Buscar usuario
    $sql = "SELECT * FROM usuario 
            WHERE nombre_usuario = :nombreUsuario";
>>>>>>> Stashed changes

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':nombreUsuario' => $_SESSION["usuario"]
    ]);

    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

<<<<<<< Updated upstream
    if ($fila) {


    } else {
        echo "No se encontró el usuario.";
    }
=======

    // Buscar auto
    $sqlAuto = "SELECT * FROM automovil 
                WHERE id_usuario = :idUsuario";

    $stmtAuto = $conexion->prepare($sqlAuto);

    $stmtAuto->execute([
        ':idUsuario' => $fila["id"]
    ]);

    $auto = $stmtAuto->fetch(PDO::FETCH_ASSOC);
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
<body class="pagina-cuenta">
    <div class="container-fluid">
         <nav class="tarjetaArriba-principal">
=======
<style>
.img {
  height: 150px;
  width: 150px;
  border-radius: 50%;
  object-fit: cover;
  background: #dfdfdf;
}
</style>
<body>
    <div class="container-fluid p-0">

        <nav class="tarjetaArriba-cuenta">
>>>>>>> Stashed changes

            <a href="principal.html">
                <p class="textoBlanco-principal">Página Principal</p>
            </a>

            <a href="#quienes_somos">
                <p class="textoBlanco-principal">¿Quiénes somos?</p>
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

<<<<<<< Updated upstream
        </nav>
        <br>
        <center><h1>Tu cuenta</h1></center>
        <br><br>
            <center>  
                <div class="textoBlanco-principal">
              Nombre de usuario: <?php echo $fila['nombreUsuario']; ?>  <br>
                Correo electrónico: <?php echo $fila['email'] ?><br> 
                </div>

                <button class="boton-logout"> logout </button> <button class="boton-guardar"> guardar y salir</button>
        </center>
 
   
        <br><br>
    
=======
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
                
                <form action="principal.html" method="post">
                    <button class="btn btn-danger "style=" background-color: transparent; border-color: green; color: green;" type="submit">menu principal</button>
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
>>>>>>> Stashed changes

</body>
</html>