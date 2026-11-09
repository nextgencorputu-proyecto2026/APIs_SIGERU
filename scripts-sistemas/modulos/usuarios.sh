function listar_usuarios() {
    grep "/home/" /etc/passwd
}

function crear_usuario() {
    read -p "Nombre del nuevo usuario: " usuario
    if [ -z "$usuario" ]; then
        echo "Debe escribir un nombre"
        return
    fi
    if existe_usuario "$usuario"; then
        echo "El usuario ya existe"
        return
    fi
    sudo adduser "$usuario" && registrar "Usuario creado: $usuario"
}

function eliminar_usuario() {
    read -p "Usuario a eliminar: " usuario
    if ! existe_usuario "$usuario"; then
        echo "El usuario no existe"
        return
    fi
    if [ "$(id -u "$usuario")" -lt 1000 ]; then
        echo "No se pueden eliminar usuarios del sistema"
        return
    fi
    if [ "$usuario" = "$USER" ]; then
        echo "No puede eliminar su propio usuario"
        return
    fi
    if confirmar "Se eliminará el usuario $usuario"; then
        sudo deluser "$usuario" && registrar "Usuario eliminado: $usuario"
    else
        echo "Cancelado"
    fi
}

function cambiar_password() {
    read -p "Usuario: " usuario
    if ! existe_usuario "$usuario"; then
        echo "El usuario no existe"
        return
    fi
    sudo passwd "$usuario" && registrar "Contraseña cambiada: $usuario"
}

function gestion_usuarios() {
    echo "== Gestión de Usuarios =="
    echo "1) Listar usuarios"
    echo "2) Crear usuario"
    echo "3) Eliminar usuario"
    echo "4) Cambiar contraseña"
    read -p "Opción: " opcion
    case $opcion in
        1) listar_usuarios ;;
        2) crear_usuario ;;
        3) eliminar_usuario ;;
        4) cambiar_password ;;
        *) echo "Opción inválida" ;;
    esac
}
