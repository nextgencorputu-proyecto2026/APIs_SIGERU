registrar() {
    echo "$(date '+%F %T') [$USER] $1" >> "$LOG"
}

confirmar() {
    read -p "$1 (escriba SI para continuar): " respuesta
    [ "$respuesta" = "SI" ] || [ "$respuesta" = "si" ] || [ "$respuesta" = "Si" ]
}

existe_usuario() {
    [ -n "$1" ] && grep -q "^$1:" /etc/passwd
}

existe_grupo() {
    [ -n "$1" ] && grep -q "^$1:" /etc/group
}
