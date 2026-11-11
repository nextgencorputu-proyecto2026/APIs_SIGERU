
#!/bin/bash

# ==========================================
# CONFIGURAR IP DEL SERVIDOR SIGERU
# ==========================================

set -e

# Ubicación de los proyectos
BACKEND="$(cd "$(dirname "$0")" && pwd)"
FRONTEND="$(dirname "$BACKEND")/Frontend_SIGERU"

# Detectar la IP del servidor
IP=$(hostname -I | tr ' ' '\n' | grep -E '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$' | head -n 1)

# Permitir indicar una IP manualmente
IP="${1:-$IP}"

if [ -z "$IP" ]; then
    echo "ERROR: No se pudo detectar la IP."
    exit 1
fi

if [ ! -d "$FRONTEND" ]; then
    echo "ERROR: No se encuentra Frontend_SIGERU."
    exit 1
fi

echo "======================================"
echo " CONFIGURACION DE IP - SIGERU"
echo "======================================"
echo "IP detectada: $IP"
echo "Backend: $BACKEND"
echo "Frontend: $FRONTEND"
echo ""

read -r -p "¿Aplicar los cambios? (s/n): " RESP

if [ "$RESP" != "s" ] && [ "$RESP" != "S" ]; then
    echo "Operacion cancelada."
    exit 0
fi

# Cambiar URLs de localhost a la IP del servidor
# No modifica conexiones internas como DB_HOST=mysql
# ni healthchecks que utilizan localhost.

find "$FRONTEND" "$BACKEND" \
    -type d \( \
        -name .git -o \
        -name vendor -o \
        -name node_modules -o \
        -name .venv -o \
        -name dist -o \
        -name build \
    \) -prune -o \
    -type f \( \
        -name "*.js" -o \
        -name "*.html" -o \
        -name "*.php" -o \
        -name "*.css" -o \
        -name "*.json" -o \
        -name "*.xml" -o \
        -name "*.conf" -o \
        -name "*.env" -o \
        -name ".env.*" \
    \) -print0 |
while IFS= read -r -d '' ARCHIVO; do

    if grep -qE 'https?://localhost' "$ARCHIVO"; then

        # Crear copia de seguridad
        cp -p "$ARCHIVO" "$ARCHIVO.bak-ip"

        # Reemplazar localhost únicamente en URLs HTTP/HTTPS
        sed -i \
            -e "s|http://localhost|http://$IP|g" \
            -e "s|https://localhost|https://$IP|g" \
            "$ARCHIVO"

        echo "Modificado: $ARCHIVO"
    fi

done

# Actualizar APP_URL en docker-compose.yml
# Sin modificar los healthchecks internos
COMPOSE="$BACKEND/docker-compose.yml"

if [ -f "$COMPOSE" ]; then
    cp -p "$COMPOSE" "$COMPOSE.bak-ip"

    sed -i -E \
        "s|(APP_URL:[[:space:]]*https?://)localhost|\1$IP|g" \
        "$COMPOSE"

    echo "Revisado: docker-compose.yml"
fi

echo ""
echo "======================================"
echo " CONFIGURACION FINALIZADA"
echo "======================================"
echo "IP configurada: $IP"
echo ""
echo "Ahora ejecute:"
echo "cd \"$BACKEND\""
echo "docker compose config"
echo "docker compose up -d --build"
