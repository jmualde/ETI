<?php
require_once ("conexion.php");
$sql = "SELECT * FROM usuarios";
$resultado = $conexion->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head> 

<style> 

body{
    background-color: #101214;
    color: white; 
    position: fixed;
    width: 100%;
    height: 100%;
}

.tarjeta{
    border: 1px ;
    padding: 50px;
    margin: 100px ; 
    width: 950px;
    background-color: #161a1d; 
    border-radius: 10px;
    box-shadow:  1px 1px 10px rgb(83, 83, 83) ;  
    border-color: darkslategray; 
    margin-left: 150px;
    margin-top: 90px;
    

} 

.botonIniciar{
    width: 450px;
    height: 55px;
    border-radius: 500px;
    background-color: white;
    font-weight: bold;
    border: none;
} 

.botonRegistra{
    width: 300px;
    height: 40px;
    border-radius: 500px;
    background: transparent;
    color: white;
    font-weight: bold ;
    border: 2px solid white ;
} 



.imagenauto{ 
    
border-radius: 10px;
width: 100%;
height: 500px;  

margin-left: 25px; 


} 

.gris{
color: #C7D1DB; 
font-weight: bold;
transition: 0.5s;
line-height: 1;
} 

.azul{
    color: #C7D1DB;
    font-weight: bold;
}


.gris:hover{
    color: #edf0f3;
    font-weight: bold; 
}

.campo{
    background: transparent;
    border:5px solid gray;
    border-radius: 10px; 
    width: 450px;
    height: 45px; 
    border-width: 1px; 
    color: white;


}





</style>

<body> 


       
<form method="POST" action="procesa.php"> 
    <div class="container">

        <div class="tarjeta"> 
            <div class="row"> 
                <div class="col-md-6"> 
                    
                    <h1 class="text-center">Inicio de sesión</h1>
                    <br><br>
                    
                <form>
                        
                    <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label"><p class="azul">Inicia sesión con tu usuario</p></label>
                            <input type="text" class="campo" id="user" name="user" placeholder=" Ingrese su usuario " >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" > <p class="gris">Contraseña</p></label>
                        <input type="password" class="campo" name="contrasenia" id ="contrasenia" placeholder="  Ingrese su contraseña aquí">
                    </div> 
                    
                    <form action="post" action="recuperacion.html">
                    <div class ="mb-3">
                        <button type="submit" class="btn btn-link" src="recuperacion.html" text-decoration: none;> <p class = "gris"> Ayuda, olvide mi contraseña</p></button> 
                    </div>
                    </form>
                    
                    <div class="mb-3">
                        <button type="submit" class="botonIniciar" id="btnIniciar" name="btnIniciar" >Iniciar sesión</button> 
                        <br><br>
                    </div>

                </form>
                </div>  

                <div class="col-md-6"> 
                    <center>
                        
                    <img src="pexels-kaue-barbier-710715348-30233581.jpg" class="imagenauto">
                    </center>
                </div>       
            </div>        
        </div>
    </div> 
</form>
    
<form method="POST" action="registra.html">
    <div class="mb-3">
        <center>
            <h3>¿Tu primera vez en nuestra web?</h3>
            <br>
            <button type="submit" class="botonRegistra" id="btnRegistra" name="btnRegistra" >Registrate</button>
        </center>

    </div>
</form>
 
</body>
</html>