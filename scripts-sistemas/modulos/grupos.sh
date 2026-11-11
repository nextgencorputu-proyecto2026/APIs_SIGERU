function listar_grupos() {
    tail -n 15 /etc/group
}

function crear_grupo() {
    read -p "Nombre del nuevo grupo: " grupo
    if [ -z "$grupo" ]; then
        echo "Debe escribir un nombre"
        return
    fi
    if existe_grupo "$grupo"; then
        echo "El grupo ya existe"
        return
    fi
    sudo groupadd "$grupo" && registrar "Grupo creado: $grupo"
}

function agregar_a_grupo() {
    read -p "Usuario: " usuario
    read -p "Grupo: " grupo
    if ! existe_usuario "$usuario"; then
        echo "El usuario no existe"
        return
    fi
    if ! existe_grupo "$grupo"; then
        echo "El grupo no existe"
        return
    fi
    if [ "$grupo" = "sudo" ] || [ "$grupo" = "docker" ]; then
        if ! confirmar "El grupo $grupo da privilegios de administrador"; then
            echo "Cancelado"
            return
        fi
    fi
    sudo usermod -aG "$grupo" "$usuario" && registrar "Usuario $usuario agregado al grupo $grupo"
}

function quitar_de_grupo() {
    read -p "Usuario: " usuario
    read -p "Grupo: " grupo
    if ! existe_usuario "$usuario"; then
        echo "El usuario no existe"
        return
    fi
    if ! existe_grupo "$grupo"; then
        echo "El grupo no existe"
        return
    fi
    if confirmar "Se quitará a $usuario del grupo $grupo"; then
        sudo deluser "$usuario" "$grupo" && registrar "Usuario $usuario quitado del grupo $grupo"
    else
        echo "Cancelado"
    fi
}

function ver_miembros() {
    read -p "Grupo: " grupo
    if ! existe_grupo "$grupo"; then
        echo "El grupo no existe"
        return
    fi
    grep "^$grupo:" /etc/group
}

function gestion_grupos() {
    echo "== Gestión de Grupos =="
    echo "1) Listar grupos"
    echo "2) Crear grupo"
    echo "3) Agregar usuario a un grupo"
    echo "4) Quitar usuario de un grupo"
    echo "5) Ver miembros de un grupo"
    read -p "Opción: " opcion
    case $opcion in
        1) listar_grupos ;;
        2) crear_grupo ;;
        3) agregar_a_grupo ;;
        4) quitar_de_grupo ;;
        5) ver_miembros ;;
        *) echo "Opción inválida" ;;
    esac
}
