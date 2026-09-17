!/bin/bash


echo "1- gestion usuarios"
echo "2- firewall"
echo "3- respaldos"
echo "4- logs"
echo "#- salir"
read -p "opcion" opcion

case $opcion in

1) ./gastionusuarios.sh;;

2) ./scriptfirewall.sh;;

3) ./scriptrespaldo.sh;;

4) ./logs.sh;;

*) exit;;

esac
