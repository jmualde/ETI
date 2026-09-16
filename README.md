Sistema de Gestion de estacionamiento E.T.I.
El proyecto consiste en un sistema que guie a los funcionarios del liceo Logosofico para encontrar estacionamiento libre segun estadisticas.

Comenzando
Las siguientes instrucciones te perimitiran tener una copia del proyecto en funcionamiento con una maquina virtual con el proposito de desarrollar y hacer pruebas 

Pre-requisitos
se requiere el xampp con Apache y MySQL instalados y activados, para esto hay que ir a la pagina web de xampp y descargar la ultima version.
https://www.apachefriends.org/
Una vez descargada, ejecutar el archivo e seleccionar donde instalar la aplicacion. Despues de instalarla, ejecutarla Xampp como administrador y encender la 
opcion de Apache y la de Mysql
Instalación 🔧
Luego de cumplir con los pre-requisitos, vamos a la instalacion del sistena, para eso, entrar al repositorio de github y le damos a la opcion de "code"
y despues a la opcion de exportar como zip. Descargada esta verison comprimida del sistema, nos dirigimos donde descargamos el archivo zip y descomprimimos el archivo.
Cuando se descomprime queda una carpeta llamada ETI, esta misma la movemos a htdocs en caso de estar en Windows. En caso de estar con linux hay que entrar a 
var/www/HTML.


En caso de estar con linux para ejecutar el proyecto debemos crear la base de datos vamos al cmd y y ejecutamos mysql -u root -p
y pegamos el script de base de datos llamado "BaseDeDatosEstacionamiento.sql"

En caso de estar usando Xampp (Windows) debemos abrir el mismo y activar los servicios de mysql y apache,
y entrar al navegador de confianza y en la barra de busqueda escribir localhost y entrar a phpMyAdmin,
dar click a SQL y pegar el script de base de datos.

Por ultimo escribir en la barra de busqueda del navegador localhost/ETI/

Un ejemplo clave es cuando se registra el usuario en la base de datos, los datos del usuario aparecen 
en la tabla usuarios.

Construido con PHP,CSS y JavaScript

Autores
Juan Pablo Trelles, Facundo Centurion, Juan Ualde, Pedro Serra.
