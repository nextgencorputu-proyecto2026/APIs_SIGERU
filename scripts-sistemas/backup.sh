#!/bin/bash

RUTA=$(cd "$(dirname "$0")" && pwd)
source "$RUTA/config.conf"

mkdir -p "$DIR_RESPALDOS"

FECHA=$(date +%Y-%m-%d_%H-%M-%S)
ARCHIVO=backup_sigeru_$FECHA

sudo docker exec "$CONTENEDOR_DB" sh -c "MYSQL_PWD=\"\$MYSQL_ROOT_PASSWORD\" mysqldump -u root $BASE_DATOS" > "$DIR_RESPALDOS/$ARCHIVO.sql"

if [ $? -ne 0 ]; then
    echo "Error: el backup falló"
    rm -f "$DIR_RESPALDOS/$ARCHIVO.sql"
    exit 1
fi

tar -czf "$DIR_RESPALDOS/$ARCHIVO.tar.gz" -C "$DIR_RESPALDOS" "$ARCHIVO.sql"
rm "$DIR_RESPALDOS/$ARCHIVO.sql"

if ! tar -tzf "$DIR_RESPALDOS/$ARCHIVO.tar.gz" > /dev/null; then
    echo "Error: el respaldo quedó dañado"
    exit 1
fi

echo "Backup local completado: $ARCHIVO.tar.gz"

find "$DIR_RESPALDOS" -name "backup_sigeru_*.tar.gz" -mtime +$RETENCION_LOCAL_DIAS -delete

