function consultar_logs() {
    echo "== Logs del Sistema =="
    echo "1) Últimos 20 eventos"
    echo "2) Últimos errores del sistema"
    echo "3) Logs del servicio SSH"
    echo "4) Registro de este script"
    read -p "Opción: " opcion
    case $opcion in
        1) sudo journalctl -n 20 ;;
        2) sudo journalctl -p err -n 20 ;;
        3) sudo journalctl -u ssh ;;
        4) tail -n 20 "$LOG" ;;
        *) echo "Opción inválida" ;;
    esac
}
