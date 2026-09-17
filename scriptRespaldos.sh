#!/bin/bash

read -p "¿Desea programar este script para que se ejecute automáticamente con cron? (s/n) " programar

if [[ $programar == s ]]; then
    read -p "Ingrese la ruta completa" ruta

    read -p "minuto (0-59): " minuto
    read -p "hora (0-23): " hora
    read -p "día del mes (1-31, o * para todos): " dia_mes
    read -p "el mes (1-12, o * para todos): " mes
    read -p "día de la semana (0-6, donde 0=domingo, o * para todos): " dia_semana

    CRON_JOB="$minuto $hora $dia_mes $mes $dia_semana $ruta"

    (crontab -l 2>/dev/null; echo "$CRON_JOB") | sort -u | crontab -

    echo "Tarea programada correctamente en cron:"
    echo "$CRON_JOB"
fi
clear
read -p "Para hacer un respaldo local ingrese (l) para remoto ingrese (r) " como
read -p "Ingrese ruta del directorio a respaldar: " dir
existe=0

while [ $existe != 1 ]
do
if [[ -d $dir ]];then
echo "El directorio existe"
existe=1
else
read -p "El directorio no existe intente otra vez " dir
fi
done

if [[ $como == r ]]; then
read -p "Ingrese IP destino: " ip
read -p "Ingrese usuario destino: " usuario
fi

fecha=$(date '+%Y%m%d')
nombreRespaldo="Respaldo_${fecha}.tar.gz"
tar -czf "$nombreRespaldo" "$dir"

if [[ $? -eq 0 ]]; then

echo "Archivo comprimido exitosamente"
if [[ $como == r ]]; then
read -p "Ingrese la ruta donde quiere que se copie el respaldo: " rutaRespaldo

scp "$nombreRespaldo" "${usuario}@${ip}:${rutaRespaldo}"
fi
fi
