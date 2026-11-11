#!/bin/bash
# restaurar.sh - Restaura la base desde un respaldo de /respaldos
# Uso: docker compose exec respaldo restaurar.sh backup_sigeru_FECHA.tar.gz

ARCHIVO="$1"

if [ -z "$ARCHIVO" ]; then
    echo "Uso: restaurar.sh NOMBRE_DEL_RESPALDO.tar.gz"
    echo "Respaldos disponibles:"
    ls -lt /respaldos/*.tar.gz 2>/dev/null
    exit 1
fi

cd /respaldos || exit 1

if [ ! -f "$ARCHIVO" ]; then
    echo "No existe /respaldos/$ARCHIVO"
    exit 1
fi

if ! tar -tzf "$ARCHIVO" > /dev/null; then
    echo "El archivo esta danado, no se puede restaurar"
    exit 2
fi

read -p "Se va a reemplazar la base $BASE. Escriba SI para continuar: " resp
if [ "$resp" != "SI" ]; then
    echo "Cancelado"
    exit 0
fi

mkdir -p /tmp/restauracion
tar -xzf "$ARCHIVO" -C /tmp/restauracion
SQL=$(tar -tzf "$ARCHIVO" | head -n 1)

echo "Restaurando..."
mysql -h "$MYSQL_HOST" -u root -e "DROP DATABASE IF EXISTS $BASE; CREATE DATABASE $BASE;"
mysql -h "$MYSQL_HOST" -u root "$BASE" < "/tmp/restauracion/$SQL"
rm -rf /tmp/restauracion

TABLAS=$(mysql -h "$MYSQL_HOST" -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$BASE';")
echo "Restauracion terminada. Tablas en la base: $TABLAS"
