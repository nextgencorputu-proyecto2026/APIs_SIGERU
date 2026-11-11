#!/bin/bash
# respaldo.sh - Genera un respaldo .tar.gz de la base en /respaldos
# Uso: docker compose exec respaldo respaldo.sh

FECHA=$(date +%F_%H-%M-%S)
SQL="backup_sigeru_$FECHA.sql"
ARCHIVO="backup_sigeru_$FECHA.tar.gz"

cd /respaldos || exit 1

echo "Generando volcado de la base $BASE..."
if ! mysqldump -h "$MYSQL_HOST" -u root --single-transaction --routines "$BASE" > "$SQL"; then
    echo "Error: no se pudo generar el volcado"
    rm -f "$SQL"
    exit 1
fi

tar -czf "$ARCHIVO" "$SQL"
rm -f "$SQL"

if tar -tzf "$ARCHIVO" > /dev/null; then
    echo "Respaldo creado: /respaldos/$ARCHIVO"
    ls -lh "$ARCHIVO"
else
    echo "Error: el respaldo esta danado"
    exit 2
fi

# Borrar respaldos con mas dias que DIAS_RETENCION
find /respaldos -name "backup_sigeru_*.tar.gz" -mtime +"$DIAS_RETENCION" -delete
