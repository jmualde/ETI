#!/bin/bash

while [[ $opcion != 5 ]]; do
echo "Elegi una opcion "
echo "1- Crear grupo"
echo "2- Crear usario"
echo "3- Modificar usuario"
echo "4- Eliminar usuario"
echo "5- EXIT"
read opcion

case $opcion in
    1)
        read -p "Ingresar nombre del grupo: " grupo
        sudo groupadd "$grupo"
        echo "Grupo '$grupo' creado"
        ;;
    2)
        read -p "Ingrese el nombre del usuario: " usuario
        read -p "Ingrese el grupo principal: " grupo
        read -p "Ingrese el directorio home (por ejemplo /home/$usuario): " home
        sudo useradd -m -d "$home" -g "$grupo" "$usuario"
        echo "Dale una contraseña para el usuario:"
        sudo passwd "$usuario"
        ;;
    3)
        read -p "Ingrese el nombre del usuario a modificar: " usuario

        if ! id "$usuario" &>/dev/null; then
            echo "El usuario '$usuario' no existe."
        else
            echo "Que desea modificar?"
            echo "1- Cambiar nombre de usuario"
            echo "2- Cambiar directorio home"
            echo "3- Cambiar grupo principal"
            echo "4- Bloquear usuario"
            echo "5- Desbloquear usuario"
            read -p "Opcion: " opcionmod

            case $opcionmod in
                1)
                    read -p "Ingrese el nuevo nombre de usuario: " nuevonombre
                    sudo usermod -l "$nuevonombre" "$usuario"
                    echo "Usuario renombrado a '$nuevonombre'"
                    ;;
                2)
                    read -p "Ingrese el nuevo directorio home: " nuevohome
                    sudo usermod -m -d "$nuevohome" "$usuario"
                    echo "Directorio home actualizado a '$nuevohome'"
                    ;;
                3)
                    read -p "Ingrese el nuevo grupo principal: " nuevogrupo
                    sudo usermod -g "$nuevogrupo" "$usuario"
                    echo "Grupo principal actualizado a '$nuevogrupo'"
                    ;;
                4)
                    sudo usermod -L "$usuario"
                    echo "Usuario '$usuario' bloqueado"
                    ;;
                5)
                    sudo usermod -U "$usuario"
                    echo "Usuario '$usuario' desbloqueado"
                    ;;
                *)
                    echo "Opcion no valida."
                    ;;
            esac
        fi
        ;;
    4)
        read -p "Ingrese el nombre del usuario a eliminar: " usuario

        if ! id "$usuario" &>/dev/null; then
            echo "El usuario '$usuario' no existe."
        else
            read -p "Desea eliminar tambien su directorio home? (s/n): " borrarhome
            if [[ $borrarhome == "s" ]]; then
                sudo userdel -r "$usuario"
                echo "Usuario '$usuario' y su directorio home eliminados"
            else
                sudo userdel "$usuario"
                echo "Usuario '$usuario' eliminado"
            fi
        fi
        ;;
    5) ;;
    *)
        echo "Opción no válida."
        ;;
esac
done
