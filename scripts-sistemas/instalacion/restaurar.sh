#!/bin/bash
# restaurar.sh - Restaura la base proyecto_sigeru desde un respaldo .tar.gz
# Uso: ./restaurar.sh respaldos/backup_sigeru_FECHA.tar.gz

BASE="proyecto_sigeru"
USUARIO="root"
CLAVE="root"
ARCHIVO="$1"

if [ -z "$ARCHIVO" ]; then
    echo "Uso: ./restaurar.sh respaldos/archivo.tar.gz"
    echo "Respaldos disponibles:"
    ls -lt respaldos/*.tar.gz 2>/dev/null
    exit 1
fi

if [ ! -f "$ARCHIVO" ]; then
    echo "No existe el archivo $ARCHIVO"
    exit 1
fi

if ! tar -tzf "$ARCHIVO" > /dev/null; then
    echo "El archivo esta danado, no se puede restaurar."
    exit 2
fi

read -p "Se va a reemplazar la base $BASE. Escriba SI para continuar: " resp
if [ "$resp" != "SI" ]; then
    echo "Cancelado"
    exit 0
fi

mkdir -p restauracion
tar -xzf "$ARCHIVO" -C restauracion
SQL=$(tar -tzf "$ARCHIVO" | head -n 1)

echo "Restaurando..."
docker compose exec -T mysql mysql -u "$USUARIO" -p"$CLAVE" -e "DROP DATABASE IF EXISTS $BASE; CREATE DATABASE $BASE;" 2>/dev/null
docker compose exec -T mysql mysql -u "$USUARIO" -p"$CLAVE" "$BASE" < "restauracion/$SQL" 2>/dev/null

TABLAS=$(docker compose exec -T mysql mysql -u "$USUARIO" -p"$CLAVE" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$BASE';" 2>/dev/null)
rm -rf restauracion
echo "Restauracion terminada. Tablas en la base: $TABLAS"
