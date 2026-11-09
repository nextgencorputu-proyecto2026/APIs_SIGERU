function monitoreo_sistema() {
    echo "== Monitoreo del Sistema =="
    echo "1) Tiempo de actividad y carga"
    echo "2) Memoria RAM"
    echo "3) Almacenamiento en disco"
    echo "4) Procesos en ejecución"
    read -p "Opción: " opcion
    case $opcion in
        1) uptime ;;
        2) free -h ;;
        3) df -h ;;
        4) ps aux ;;
        *) echo "Opción inválida"; return ;;
    esac
    registrar "Monitoreo: opción $opcion"
}
