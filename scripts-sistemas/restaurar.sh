#!/bin/bash

RUTA=$(cd "$(dirname "$0")" && pwd)
source "$RUTA/config.conf"

SSH_OPCIONES="-i $SERVIDOR3_LLAVE -o BatchMode=yes -o ConnectTimeout=10"
DIR_RESTAURACION="$RUTA/restauracion"
DESCARGA=""
SQL=""

mysql_root() {
    docker exec -i "$CONTENEDOR_DB" sh -c "MYSQL_PWD=\"\$MYSQL_ROOT_PASSWORD\" mysql -u root $1"
}

limpiar() {
    if [ -n "$DESCARGA" ]; then
        rm -f "$DESCARGA"
    fi
    if [ -n "$SQL" ]; then
        rm -f "$DIR_RESTAURACION/$SQL"
    fi
}

if [ "$(whoami)" != "root" ]; then
    echo "Ejecutar con sudo"
    exit 1
fi

if ! docker ps | grep -q "$CONTENEDOR_DB"; then
    echo "El contenedor $CONTENEDOR_DB no está corriendo"
    exit 1
fi

mkdir -p "$DIR_RESTAURACION"

ARCHIVO="$1"
if [ -z "$ARCHIVO" ]; then
    echo "Origen del respaldo:"
    echo "1) main (carpeta local)"
    echo "2) VM respaldo (copia remota)"
    read -p "Opción: " ORIGEN
    case $ORIGEN in
        1)
            ls -lt "$DIR_RESPALDOS"
            read -p "Nombre del archivo a restaurar: " NOMBRE
            ARCHIVO="$DIR_RESPALDOS/$NOMBRE"
            ;;
        2)
            if ! ssh $SSH_OPCIONES "$SERVIDOR3_USUARIO@$SERVIDOR3_IP" "ls -lt $SERVIDOR3_DIR"; then
                echo "No se pudo obtener la lista de copias desde respaldo"
                exit 1
            fi
            read -p "Nombre del archivo a restaurar: " NOMBRE
            if [ -z "$NOMBRE" ]; then
                echo "Opción inválida"
                exit 1
            fi
            if ! scp $SSH_OPCIONES "$SERVIDOR3_USUARIO@$SERVIDOR3_IP:$SERVIDOR3_DIR/$NOMBRE" "$DIR_RESTAURACION/"; then
                echo "No se pudo copiar el archivo desde respaldo"
                exit 1
            fi
            ARCHIVO="$DIR_RESTAURACION/$NOMBRE"
            DESCARGA="$ARCHIVO"
            ;;
        *)
            echo "Opción inválida"
            exit 1
            ;;
    esac
fi

if [ ! -f "$ARCHIVO" ]; then
    echo "No existe el archivo de respaldo"
    limpiar
    exit 1
fi

if ! tar -tzf "$ARCHIVO" > /dev/null; then
    echo "El respaldo está dañado"
    limpiar
    exit 1
fi

echo "Se va a reemplazar la base $BASE_DATOS con: $ARCHIVO"
read -p "Escriba SI para continuar: " R
if [ "$R" != "SI" ] && [ "$R" != "si" ] && [ "$R" != "Si" ]; then
    echo "Cancelado"
    limpiar
    exit 0
fi

EXISTE=$(echo "SHOW DATABASES LIKE '$BASE_DATOS';" | mysql_root "-N")
if [ -n "$EXISTE" ]; then
    "$RUTA/backup.sh"
    CODIGO=$?
    if [ "$CODIGO" -eq 1 ]; then
        echo "No se pudo hacer el respaldo de seguridad, se cancela"
        limpiar
        exit 1
    fi
    if [ "$CODIGO" -eq 2 ]; then
        echo "Aviso: el respaldo de seguridad quedó solo en main"
    fi
fi

SQL=$(tar -tzf "$ARCHIVO")
tar -xzf "$ARCHIVO" -C "$DIR_RESTAURACION"

echo "DROP DATABASE IF EXISTS $BASE_DATOS; CREATE DATABASE $BASE_DATOS CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" | mysql_root "" > /dev/null

if ! mysql_root "$BASE_DATOS" < "$DIR_RESTAURACION/$SQL" > /dev/null; then
    echo "Falló la restauración"
    limpiar
    exit 1
fi

limpiar

TABLAS=$(echo "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$BASE_DATOS';" | mysql_root "-N")
echo "Restauración completada. Tablas: $TABLAS"
echo "$(date '+%F %T') [$USER] restauración de $ARCHIVO ($TABLAS tablas)" >> "$LOG"
