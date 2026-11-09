#!/bin/bash

RUTA=$(cd "$(dirname "$0")" && pwd)

if [ ! -f "$RUTA/config.conf" ]; then
    echo "Falta el archivo config.conf"
    exit 1
fi
source "$RUTA/config.conf"

for modulo in comun monitoreo logs respaldos firewall usuarios grupos redes basedatos contenedores verificacion
do
    if [ ! -f "$RUTA/modulos/$modulo.sh" ]; then
        echo "Falta el módulo $modulo"
        exit 1
    fi
    source "$RUTA/modulos/$modulo.sh"
done

function menu_principal() {
    echo ""
    echo "===== Script de Administración del Servidor - SiGeRU ====="
    echo "1) Monitoreo del Sistema"
    echo "2) Logs del Sistema"
    echo "3) Gestión de Respaldos"
    echo "4) Gestión de Firewall"
    echo "5) Gestión de Usuarios"
    echo "6) Gestión de Grupos"
    echo "7) Gestión de Redes"
    echo "8) Gestión de Base de Datos"
    echo "9) Gestión de Contenedores"
    echo "10) Verificar el sistema"
    echo "0) Salir"
    read -p "Seleccione una opción: " opcion

    case $opcion in
        1) monitoreo_sistema ;;
        2) consultar_logs ;;
        3) gestion_respaldos ;;
        4) gestion_firewall ;;
        5) gestion_usuarios ;;
        6) gestion_grupos ;;
        7) gestion_redes ;;
        8) gestion_basedatos ;;
        9) gestion_contenedores ;;
        10) verificar_sistema ;;
        0) registrar "Fin de sesión"; exit 0 ;;
        *) echo "Opción inválida" ;;
    esac
}

registrar "Inicio de sesión"

while true
do
    menu_principal
done
