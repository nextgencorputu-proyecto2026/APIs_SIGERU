function pedir_contenedor() {
    sudo docker ps -a
    read -p "Nombre del contenedor: " contenedor
    if [ -z "$contenedor" ] || ! sudo docker ps -a | grep -q "$contenedor"; then
        echo "El contenedor no existe"
        return 1
    fi
}

function gestion_contenedores() {
    echo "== Gestión de Contenedores =="
    echo "1) Ver contenedores"
    echo "2) Ver uso de recursos"
    echo "3) Iniciar todos los contenedores"
    echo "4) Detener todos los contenedores"
    echo "5) Reiniciar un contenedor"
    echo "6) Ver logs de un contenedor"
    echo "7) Ver imágenes"
    read -p "Opción: " opcion
    case $opcion in
        1) sudo docker ps -a ;;
        2) sudo docker stats --no-stream ;;
        3)
            sudo docker compose -f "$COMPOSE_APP" up -d
            registrar "Contenedores: iniciados"
            ;;
        4)
            if confirmar "Se detendrán todos los contenedores"; then
                sudo docker compose -f "$COMPOSE_APP" down
                registrar "Contenedores: detenidos"
            else
                echo "Cancelado"
            fi
            ;;
        5)
            pedir_contenedor || return
            sudo docker restart "$contenedor"
            registrar "Contenedores: reiniciado $contenedor"
            ;;
        6)
            pedir_contenedor || return
            sudo docker logs --tail 20 "$contenedor"
            ;;
        7) sudo docker images ;;
        *) echo "Opción inválida" ;;
    esac
}
