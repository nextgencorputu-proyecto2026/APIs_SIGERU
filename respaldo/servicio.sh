#!/bin/bash
# servicio.sh - Proceso principal del contenedor de respaldo
# Espera a que MySQL este listo y genera un respaldo cada INTERVALO segundos

echo "Servicio de respaldo iniciado"

until mysqladmin ping -h "$MYSQL_HOST" -u root --silent; do
    echo "Esperando a MySQL..."
    sleep 5
done

while true; do
    respaldo.sh
    sleep "$INTERVALO"
done
