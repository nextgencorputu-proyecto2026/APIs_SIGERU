#!/bin/bash
# respaldo.sh - Genera un respaldo comprimido (.tar.gz) de la base proyecto_sigeru
# Uso: ./respaldo.sh   (desde la carpeta APIs_SIGERU, con el sistema levantado)

BASE="proyecto_sigeru"
USUARIO="root"
CLAVE="root"
CARPETA="respaldos"
FECHA=$(date +%F_%H-%M-%S)
SQL="backup_sigeru_$FECHA.sql"
ARCHIVO="backup_sigeru_$FECHA.tar.gz"

mkdir -p "$CARPETA"

echo "Generando volcado de la base $BASE..."
docker compose exec -T mysql mysqldump -u "$USUARIO" -p"$CLAVE" "$BASE" > "$CARPETA/$SQL" 2>/dev/null

if [ ! -s "$CARPETA/$SQL" ]; then
    echo "Error: no se pudo generar el volcado. Verifique que el sistema este levantado (docker compose ps)."
    rm -f "$CARPETA/$SQL"
    exit 1
fi

echo "Comprimiendo..."
tar -czf "$CARPETA/$ARCHIVO" -C "$CARPETA" "$SQL"
rm -f "$CARPETA/$SQL"

echo "Verificando el respaldo..."
if tar -tzf "$CARPETA/$ARCHIVO" > /dev/null; then
    echo "Respaldo creado correctamente: $CARPETA/$ARCHIVO"
    ls -lh "$CARPETA/$ARCHIVO"
else
    echo "Error: el archivo de respaldo esta danado."
    exit 2
fi
