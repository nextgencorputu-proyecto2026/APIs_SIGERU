#!/bin/bash

RUTA=$(cd "$(dirname "$0")" && pwd)
source "$RUTA/config.conf"

FECHA=$(date +%Y-%m-%d_%H-%M-%S)
ARCHIVO=backup_sigeru_$FECHA
SSH_OPCIONES="-i $SERVIDOR3_LLAVE -o BatchMode=yes -o ConnectTimeout=10"

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

if scp $SSH_OPCIONES "$DIR_RESPALDOS/$ARCHIVO.tar.gz" "$SERVIDOR3_USUARIO@$SERVIDOR3_IP:$SERVIDOR3_DIR/"; then
    echo "Copia a la VM respaldo completada"
    ssh $SSH_OPCIONES "$SERVIDOR3_USUARIO@$SERVIDOR3_IP" "find $SERVIDOR3_DIR -name 'backup_sigeru_*.tar.gz' -mtime +$RETENCION_DIAS -delete"
else
    echo "Error: no se pudo copiar el archivo a la VM respaldo"
    exit 2
fi
