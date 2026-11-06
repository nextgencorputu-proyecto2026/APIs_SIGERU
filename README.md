
# APIs_SIGERU

#Nombre equipo: NextGen Corp
#Integrantes:
-Mateo Rodríguez
-Guillermo Gutiérrez
-Leonardo Barreneche
-Sebastian Ovelar

#Breve descripción: SIGERU (Sistema de Gestión de Residuos Urbanos) es un sistema que permite administrar la recolección de residuos urbanos: contenedores, camiones, cuadrillas, rutas e incidencias, facilitando la gestión municipal.

#Estructura del proyecto a ejecutar, las sub-carpetas deben estar con los nombres indicados:

Carpeta_Contenedora/
    │
    ├── Frontend_SIGERU/      # Frontend 
    │
    └── APIs_SIGERU/        # Backend 

#Guia de como levantar el proyecto

Branch a utilizar: develop

Requisitos previos:
- PHP 8.x
- Composer
- MySQL 8 corriendo (local o en Docker)


1. Clonar ambos repositorios dentro de la Carpeta_Contenedora:
   git clone https://github.com/nextgencorputu-proyecto2026/Frontend_SIGERU.git
   git clone https://github.com/nextgencorputu-proyecto2026/APIs_SIGERU.git

2. Crear el archivo de variables de entorno:
    cd ~/sigeru/APIs_SIGERU
    cp .env.example .env
    chmod 600 .env

3. Generar las claves de las APIs ejecutando script existente en el repositorio:
    bash ./preparar-docker.sh

4. Configurar la base de datos y Telegram:
    En el mismo archivo .env (se abre con nano .env) se revisan estas variables:
    SIGERU_DB_USERNAME y SIGERU_DB_PASSWORD: usuario y contraseña con los que las APIs se conectan a MySQL. Deben coincidir con MYSQL_USER y MYSQL_PASSWORD del docker-compose.yml. Por defecto ya vienen como sigeru / sigeru.
    TELEGRAM_BOT_TOKEN y TELEGRAM_CHAT_ID: token del bot y número del grupo de Telegram que recibe los avisos. Son opcionales: si quedan vacíos, el sistema funciona igual pero no envía notificaciones.
    Guardar con Ctrl + O, Enter, y salir con Ctrl + X.

5. Configurar la dirección del servidor en el Frontend:
    hostname -I  (anotar el resultado de IP)

    cd ~/sigeru/Frontend_SIGERU

    find . -type f \( -name "*.js" -o -name "*.html" \) -exec sed -i 's|localhost|IP_SERVIDOR|g' {} +

6. Levantar el sistema con Docker:
    cd ~/sigeru/APIs_SIGERU

    docker compose up -d --build

7. Verificar los contenedores, tienen que aparecer los seis servicios (api,         api-usuarios, api-gestion, api-recoleccion, ui y mysql) en estado running y, después de unos segundos, healthy:
    docker compose ps

8. Probar la aplicación desde otro equipo:
    http://IP-del-servidor:5173

        Rol                 Correo
    Administrador       administrador@sigeru.test
    Operario            operario@sigeru.test
    Chofer              chofer@sigeru.test




