#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

: "${DB_HOST:=mysql}"
: "${DB_NAME:=proyecto_sigeru}"
: "${DB_USER:?Definir DB_USER}"
: "${DB_PASSWORD:?Definir DB_PASSWORD}"
export MYSQL_PWD="$DB_PASSWORD"
DESTINO=/respaldos

ayuda() {
    printf 'Uso:\n  respaldo.sh crear\n  respaldo.sh listar\n  respaldo.sh verificar ARCHIVO.tar.gz\n  respaldo.sh restaurar ARCHIVO.tar.gz\n'
}
archivo_seguro() {
    [[ "${1:-}" =~ ^backup_sigeru_[0-9]{8}_[0-9]{6}\.tar\.gz$ ]] || {
        echo "Nombre de respaldo no válido." >&2
        exit 2
    }
    [[ -f "$DESTINO/$1" ]] || {
        echo "No existe: $DESTINO/$1" >&2
        exit 2
    }
}
mysql_cli() {
    mysql --protocol=tcp -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" "$@"
}
case "${1:-}" in
    crear)
        mkdir -p "$DESTINO"
        temporal="$(mktemp -d)"
        archivo_parcial=""
        trap 'rm -rf -- "$temporal"; if [[ -n "$archivo_parcial" ]]; then rm -f -- "$archivo_parcial"; fi' EXIT
        # El cliente MySQL debe conectarse por TCP: la inicialización temporal del
        # servidor MySQL oficial no admite conexiones TCP.
        mysql_cli -e 'SELECT 1' >/dev/null
        mysqldump --protocol=tcp -h "$DB_HOST" -u "$DB_USER" \
            --single-transaction --quick --no-tablespaces \
            --set-gtid-purged=OFF "$DB_NAME" > "$temporal/base.sql"
        test -s "$temporal/base.sql" || { echo "Dump SQL vacío." >&2; exit 1; }
        nombre="backup_sigeru_$(date +%Y%m%d_%H%M%S).tar.gz"
        archivo_parcial="$DESTINO/$nombre.parcial"
        tar -czf "$archivo_parcial" -C "$temporal" base.sql
        tar -tzf "$archivo_parcial" | grep -qx 'base.sql'
        mv -- "$archivo_parcial" "$DESTINO/$nombre"
        archivo_parcial=""
        # Conservar respaldos de los últimos 30 días.
        find "$DESTINO" -maxdepth 1 -type f -name 'backup_sigeru_*.tar.gz' -mtime +29 -delete
        echo "Respaldo completo: $DESTINO/$nombre"
        ;;
    listar)
        ls -lh "$DESTINO"/backup_sigeru_*.tar.gz
        ;;
    verificar)
        archivo_seguro "${2:-}"
        tar -tzf "$DESTINO/$2" | grep -qx 'base.sql'
        echo "Archivo TAR.GZ verificado: $2"
        ;;
    restaurar)
        archivo_seguro "${2:-}"
        echo "ATENCIÓN: se reemplazará el contenido de la base $DB_NAME."
        echo "Realice un respaldo manual antes de esta operación."
        tar -tzf "$DESTINO/$2" | grep -qx 'base.sql'
        tar -xOzf "$DESTINO/$2" base.sql | mysql_cli
        echo "Restauración finalizada desde $2"
        ;;
    *) ayuda; exit 2 ;;
esac
