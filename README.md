Repo para respaldar los scripts de bash/S.O

explicación breve de cada script: 

firewall: Este script permite gestionar reglas de firewall mediante UFW. El usuario puede elegir entre bloquear o permitir:
- Una IP o rango de IP.
- Un puerto específico.
- Una combinación de IP + puerto.
funciona mediante un menú interactivo usando case, las reglas se aplican mediante comandos ufw.

respaldos: sirve para automatizar la creación de respaldos comprimidos de un directorio, con la posibilidad de copiarlos a un servidor remoto por scp y de programar su ejecución usando cron.
Funcionalidades:
Programación automática con cron: permite configurar una tarea cron para ejecutar el script en el horario que el usuario quiera (minuto, hora, día, mes, día de la semana).
Validación de directorio: solicita la ruta del directorio a respaldar y no continúa hasta que el usuario ingrese una ruta válida.
Respaldo local o remoto: el usuario elige si el respaldo se guarda solo de forma local o si además se envía a un servidor remoto por scp.
Compresión: genera un archivo .tar.gz con el nombre Respaldo_YYYYMMDD.tar.gz, usando la fecha del día.
Envío remoto: si se eligió modo remoto, solicita IP, usuario y ruta de destino, y transfiere el archivo comprimido por scp.

gestión de usuarios: Este script esta hecho para la administración de usuarios y grupos en el servidor. 
Funciones:
- Crear grupo: crea un nuevo grupo en el sistema (groupadd).
- Crear usuario: crea un usuario nuevo (useradd), permitiendo definir su grupo principal, directorio home y establecer su contraseña.
- Modificar usuario: permite editar un usuario existente (nombre, directorio  home, grupo principal) o bloquear/desbloquear su cuenta(usermod).
- Eliminar usuario: elimina un usuario del sistema, con la opción de borrar también su directorio home (userdel -r).
Antes de modificar o eliminar, el script valida que el usuario exista usando `id`.

logs: Script para consultar los logs más relevantes del servidor. 
Funciones:
- Ver inicios de sesión: filtra las líneas `session opened` de `/var/log/auth.log`, que registra cada vez que un usuario abre sesión en el sistema.
- Ver inicios SSH: filtra las líneas relacionadas a `sshd` en `/var/log/auth.log`, mostrando las conexiones remotas.
- Ver uso de sudo: filtra las líneas `sudo:`, mostrando qué usuarios ejecutaron comandos con privilegios de sudo.
El script usa `cat` para leer los archivos de log en texto plano y `grep` para 
filtrar únicamente las líneas relevantes a cada tipo de evento.

gestión general: es un script hecho para poder ejecutar todos los scripts anteriores. cuenta con un menú de opciones en el cual te permite elegir que script queres ejecutar.
