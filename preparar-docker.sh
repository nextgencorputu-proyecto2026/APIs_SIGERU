#!/usr/bin/env bash
set -euo pipefail

cd -- "$(dirname -- "$0")"

if [[ ! -f .env ]]; then
    if [[ ! -f .env.example ]]; then
        echo "Error: no se encontro .env.example." >&2
        exit 1
    fi

    cp .env.example .env
    echo "Archivo .env creado."
fi

if ! command -v openssl >/dev/null 2>&1; then
    echo "Error: se necesita openssl para generar las claves." >&2
    exit 1
fi

USUARIOS_APP_KEY="base64:$(openssl rand -base64 32 | tr -d '\n')"
GESTION_APP_KEY="base64:$(openssl rand -base64 32 | tr -d '\n')"
RECOLECCION_APP_KEY="base64:$(openssl rand -base64 32 | tr -d '\n')"
USUARIOS_JWT_SECRET="$(openssl rand -base64 48 | tr -d '\n')"

set_if_empty() {
    local name="$1"
    local value="$2"

    if ! grep -q "^${name}=" .env; then
        echo "Error: falta la variable ${name} en .env." >&2
        exit 1
    fi

    sed -i "s|^${name}=[[:space:]]*$|${name}=${value}|" .env
}

set_if_empty "USUARIOS_APP_KEY" "$USUARIOS_APP_KEY"
set_if_empty "GESTION_APP_KEY" "$GESTION_APP_KEY"
set_if_empty "RECOLECCION_APP_KEY" "$RECOLECCION_APP_KEY"
set_if_empty "USUARIOS_JWT_SECRET" "$USUARIOS_JWT_SECRET"

unset USUARIOS_APP_KEY GESTION_APP_KEY RECOLECCION_APP_KEY USUARIOS_JWT_SECRET

echo
echo "Configuracion de SiGeRU preparada correctamente."
echo "Las claves ya configuradas se conservaron."
echo
echo "Ahora podes ejecutar:"
echo "docker compose up -d --build"
