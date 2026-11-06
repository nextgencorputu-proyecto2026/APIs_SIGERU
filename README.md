
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
<<<<<<< HEAD
=======

2. Copiar el archivo de variables de entorno:
   cp env.example .env

3. Editar el archivo .env y completar los datos de conexión a la base de datos:
   DB_DATABASE=proyecto_sigeru
   DB_USERNAME=root
   DB_PASSWORD=

4. Generar la clave de la aplicación:
   php artisan key:generate

5. Dar permisos de escritura a las carpetas de storage y cache (necesario para que Laravel pueda guardar logs y archivos temporales):
   chmod 755 -R ./storage

6. Instalar las dependencias del proyecto:
   composer update

7. Levantar el proyecto. Elegir una de las dos opciones:
   - Para desarrollo local: php artisan serve
   - Para producción/servidor: configurar Apache/Nginx apuntando a la carpeta ./public

8. Cargar la base de datos (si es la primera vez):
   a) Importar el dump disponible en el Anexo 4 del documento integrador, en la base indicada en tu .env (proyecto_sigeru).
   b) Ejecutar las migraciones para completar/actualizar el esquema:
      php artisan migrate
      

#Guia de como levantar el proyecto con Docker

Requisito: tener Docker Desktop instalado y abierto.

1. Clonar ambos repositorios dentro de la Carpeta_Contenedora, tal como se indica arriba.

2. Desde la carpeta APIs_SIGERU, levantar todo con un solo comando:
   docker compose up -d --build

   Este comando crea los servicios api, ui y mysql, e importa automáticamente 
   proyecto_sigeru.sql en el primer arranque.

3. Verificar que los tres servicios estén healthy:
   docker compose ps

4. La aplicación queda disponible en:
   - Frontend (UI): http://localhost:5173
   - API: http://localhost:8000/api

5. Para detener el entorno (conservando la base de datos):
   docker compose down

## Usuarios de prueba

Ingresar desde [Login](http://localhost:5173/login.html):

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administrador | administrador@sigeru.test | Prueba123! |
| Operario | operario@sigeru.test | Prueba123! |
| Chofer | chofer@sigeru.test | Prueba123! |

La API está disponible en `http://localhost:8000/api`; el inicio de sesión usa `POST /api/auth/login` con `email` y `password`.


# .env Repositorio(APIs_SIGERU)

APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:8HAA1y8gjcLU1PihPN9t3rfLnvjWJ1x4ep06irz5FyI=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=proyecto_sigeru
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

JWT_SECRET=tHkquf6aGzcc7BoB83gk5aYvwPQ6eCop0HlMN0jTc0VnBIMhsTDOk3mYoBzs2Djo
SIGERU_DB_USERNAME=sigeru
RECOLECCION_APP_KEY=base64:x4/dm6h/zgqkAANnBsRU0Lv04vX2e8y7GkE+hUu1e1w=
USUARIOS_APP_KEY=base64:Lh5TBWoooKzXfcoNDcnXy50YWmfTKy+MpIfvkkM2SDE=
USUARIOS_JWT_SECRET=b8qpwiVCBvbnvrVGT082WhKxlz3kSV6ef0N1HjlHBZbM3L7oGrNd8WsJPlATm094
GESTION_APP_KEY=base64:snR7TD03m9RSMV48N01TH6YoJBBiUMae2y2BtSqU2zI=
SIGERU_DB_PASSWORD=sigeru
TELEGRAM_BOT_TOKEN=8932026752:AAEu5nrORdhAx21gO5-PQ5rg76rUlO3gpXY
TELEGRAM_CHAT_ID=-5539596526

# .env (api-gestion)

APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:snR7TD03m9RSMV48N01TH6YoJBBiUMae2y2BtSqU2zI=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# .env (api-recoleccion)

APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:x4/dm6h/zgqkAANnBsRU0Lv04vX2e8y7GkE+hUu1e1w=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

TELEGRAM_BOT_TOKEN=8932026752:AAEu5nrORdhAx21gO5-PQ5rg76rUlO3gpXY
TELEGRAM_CHAT_ID=-5539596526

# .env (api-usuarios)

APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:Lh5TBWoooKzXfcoNDcnXy50YWmfTKy+MpIfvkkM2SDE=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

JWT_SECRET=b8qpwiVCBvbnvrVGT082WhKxlz3kSV6ef0N1HjlHBZbM3L7oGrNd8WsJPlATm094
>>>>>>> release/1.0
