function mysql_root() {
    sudo docker exec -i "$CONTENEDOR_DB" sh -c "MYSQL_PWD=\"\$MYSQL_ROOT_PASSWORD\" mysql -t -u root $1"
}

function base_activa() {
    if ! sudo docker ps | grep -q "$CONTENEDOR_DB"; then
        echo "El contenedor $CONTENEDOR_DB no está en ejecución"
        return 1
    fi
}

function gestion_basedatos() {
    echo "== Gestión de Base de Datos =="
    echo "1) Estado del contenedor MySQL"
    echo "2) Listar bases de datos"
    echo "3) Listar tablas y cantidad de filas"
    echo "4) Tamaño de la base"
    echo "5) Usuarios de MySQL"
    read -p "Opción: " opcion
    case $opcion in
        1) sudo docker ps -a | grep "$CONTENEDOR_DB" ;;
        2) base_activa || return; echo "SHOW DATABASES;" | mysql_root "" ;;
        3) base_activa || return; echo "SELECT table_name AS tabla, table_rows AS filas FROM information_schema.tables WHERE table_schema='$BASE_DATOS' ORDER BY table_name;" | mysql_root "" ;;
        4) base_activa || return; echo "SELECT ROUND(SUM(data_length+index_length)/1024/1024,2) AS MB FROM information_schema.tables WHERE table_schema='$BASE_DATOS';" | mysql_root "" ;;
        5) base_activa || return; echo "SELECT user, host FROM mysql.user;" | mysql_root "" ;;
        *) echo "Opción inválida" ;;
    esac
    registrar "Base de datos: opción $opcion"
}
