#!/bin/bash
while [[ $opcion != 4 ]]; do
echo "opcion "
echo "1- inicios de sesion"
echo "2- ssh"
echo "3- sudo"
echo "4- EXIT"
read opcion

case $opcion in
    1)
        echo "inicios de sesion"
        cat /var/log/auth.log | grep "session opened"
        ;;
    2)
        echo "inicios con ssh"
        cat /var/log/auth.log | grep "sshd"
        ;;
    3)
        echo "uso de sudo"
        cat /var/log/auth.log | grep "sudo:"
        ;;

    4);;

    *) "opcion incorrecta";;
esac
done
