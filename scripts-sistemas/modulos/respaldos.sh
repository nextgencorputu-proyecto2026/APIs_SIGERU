function gestion_respaldos() {
    echo "== Gestión de Respaldos =="
    echo "1) Crear respaldo ahora"
    echo "2) Listar respaldos"
    echo "3) Restaurar un respaldo"
    read -p "Opción: " opcion
    case $opcion in
        1)
            if sudo "$RUTA/backup.sh"; then
                registrar "Respaldo manual creado"
            else
                registrar "Falló el respaldo manual"
            fi
            ;;
        2) ls -lh "$DIR_RESPALDOS" ;;
        3) sudo "$RUTA/restaurar.sh" ;;
        *) echo "Opción inválida" ;;
    esac
}
