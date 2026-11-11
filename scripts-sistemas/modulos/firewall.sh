function permitir_puerto() {
    read -p "Puerto (1-65535): " puerto
    if [ "$puerto" -ge 1 ] 2>/dev/null && [ "$puerto" -le 65535 ]; then
        sudo ufw allow "$puerto/tcp"
        registrar "Firewall: puerto $puerto/tcp permitido"
    else
        echo "Puerto inválido"
    fi
}

function gestion_firewall() {
    echo "== Gestión de Firewall (UFW) =="
    echo "1) Ver estado"
    echo "2) Ver reglas detalladas"
    echo "3) Permitir SSH"
    echo "4) Permitir HTTP (80)"
    echo "5) Permitir HTTPS (443)"
    echo "6) Permitir otro puerto"
    read -p "Opción: " opcion
    case $opcion in
        1) sudo ufw status ;;
        2) sudo ufw status verbose ;;
        3) sudo ufw allow ssh; registrar "Firewall: SSH permitido" ;;
        4) sudo ufw allow 80/tcp; registrar "Firewall: puerto 80 permitido" ;;
        5) sudo ufw allow 443/tcp; registrar "Firewall: puerto 443 permitido" ;;
        6) permitir_puerto ;;
        *) echo "Opción inválida" ;;
    esac
}
