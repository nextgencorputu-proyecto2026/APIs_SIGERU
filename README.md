#APIs_SIGERU
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
   - API: http://localhost:8080/api

5. Para detener el entorno (conservando la base de datos):
   docker compose down