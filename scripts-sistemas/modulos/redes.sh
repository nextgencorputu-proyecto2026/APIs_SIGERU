function probar_conectividad() {
    read -p "Host o IP a probar (Enter = 8.8.8.8): " destino
    if [ -z "$destino" ]; then
        destino="8.8.8.8"
    fi
    ping -c 4 "$destino"
    registrar "Red: ping a $destino"
}

function gestion_redes() {
    echo "== Gestión de Redes =="
    echo "1) Ver direcciones IP"
    echo "2) Ver rutas"
    echo "3) Ver configuración de red (netplan)"
    echo "4) Ver puertos en escucha"
    echo "5) Probar conectividad"
    read -p "Opción: " opcion
    case $opcion in
        1) ip a ;;
        2) ip route ;;
        3) sudo cat /etc/netplan/*.yaml ;;
        4) sudo ss -tulpn ;;
        5) probar_conectividad ;;
        *) echo "Opción inválida" ;;
    esac
}
