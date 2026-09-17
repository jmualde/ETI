#!/bin/bash

echo "1- bloquear ip/rango"
echo "2- permitir ip/rango"
echo "3- bloquear puerto"
echo "4- permitir puerto"
echo "5- bloquear ip y puerto"
echo "6- permitir ip y puerto"
echo "7- salir"

read -p "opción: " opcion

case $opcion in

1)
    read -p "ip/rango: " ip
    sudo ufw deny from "$ip"
    echo "ip/rango bloqueado."
    ;;

2)
    read -p "ip/rango: " ip
    sudo ufw allow from "$ip"
    echo "ip/rango permitido."
    ;;

3)
    read -p "puerto: " puerto
    sudo ufw deny "$puerto"
    echo "puerto bloqueado."
    ;;

4)
    read -p "puerto: " puerto
    sudo ufw allow "$puerto"
    echo "puerto permitido."
    ;;

5)
    read -p "ip: " ip
    read -p "puerto: " puerto
    sudo ufw deny from "$ip" to any port "$puerto"
    echo "$ip bloqueada en el puerto: $puerto."
    ;;

6)
    read -p "ip: " ip
   read -p "puerto: " puerto
    sudo ufw allow from "$ip" to any port "$puerto"
    echo "$ip permitida en el puerto: $puerto."
    ;;

7)
    ;;

*)
    echo "opción incorrecta"
    ;;

esac
