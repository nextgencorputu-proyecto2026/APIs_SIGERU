# Guía de instalación y ejecución de SiGeRU con Docker

Esta guía explica cómo configurar, iniciar y ejecutar SiGeRU utilizando Docker.

Los repositorios `APIs_SIGERU` y `Frontend_SIGERU` deben estar ubicados en la misma carpeta y en la rama `develop`.

## 1. Preparar las variables de entorno

Abrir PowerShell desde la carpeta `APIs_SIGERU` y ejecutar:

```powershell

.\preparar-docker.ps1

```

Este script realiza automáticamente las siguientes tareas:

- Crea el archivo `.env` a partir de `.env.example` si todavía no existe.
- Genera las claves necesarias para las APIs de Usuarios, Gestión y Recolección.
- Configura el secreto JWT compartido para la autenticación.
- Conserva las claves existentes si el archivo `.env` ya estaba configurado.

No es necesario generar las claves manualmente ni crear archivos `.env` individuales para cada API.

**Si PowerShell bloquea la ejecución del script**, ejecutar:

```powershell

Set-ExecutionPolicy -Scope Process -ExecutionPolicy RemoteSigned

```

Después, volver a ejecutar:

```powershell

.\preparar-docker.ps1

```

Este permiso se aplica solamente a la sesión actual de PowerShell.

## 2. Iniciar SiGeRU

Desde la carpeta `APIs_SIGERU`, ejecutar:

```powershell

docker compose up -d --build

```

Este comando construye e inicia los contenedores necesarios para ejecutar SiGeRU.

Docker se encarga de preparar las APIs, el frontend y la base de datos MySQL.

La primera ejecución puede demorar algunos minutos mientras se descargan las imágenes y se instalan las dependencias.

## 3. Verificar los contenedores

Una vez finalizada la instalación, ejecutar:

```powershell

docker compose ps

```

Deben aparecer los seis servicios del proyecto:

- `api`
- `api-usuarios`
- `api-gestion`
- `api-recoleccion`
- `ui`
- `mysql`

Los servicios deberían aparecer en estado de ejecución y, una vez completado el inicio, como saludables (`healthy`).

## 4. Acceder a SiGeRU

Abrir el navegador e ingresar a:

**http://localhost:5173**

También se encuentran disponibles los siguientes servicios:

| Servicio | Dirección |
|---|---|
| Frontend SiGeRU | http://localhost:5173 |
| API original | http://localhost:8000/api/health |
| API Usuarios | http://localhost:8001 |
| API Gestión | http://localhost:8002 |
| API Recolección | http://localhost:8003 |

La base de datos MySQL funciona dentro de Docker y no necesita iniciarse manualmente.

## 5. Base de datos

SiGeRU utiliza la base de datos MySQL `proyecto_sigeru`.

En la primera ejecución, cuando el volumen de MySQL está vacío, Docker importa automáticamente el archivo:

`proyecto_sigeru.sql`

Este archivo contiene la estructura y los datos iniciales del proyecto.

La API original ejecuta automáticamente las migraciones pendientes de Laravel al iniciar.

Las APIs de Usuarios, Gestión y Recolección no ejecutan migraciones automáticamente durante su arranque.

Las migraciones específicas de testing se utilizan para las pruebas automatizadas y no forman parte de la inicialización normal de la base de datos.

No es necesario importar manualmente el archivo SQL ni crear la base de datos desde phpMyAdmin.

## 6. Detener y reiniciar SiGeRU

Para detener los contenedores sin eliminar los datos:

```powershell
docker compose down
```

Para volver a iniciar el proyecto:

```powershell
docker compose up -d
```

Para reconstruir los contenedores después de realizar cambios o actualizar el proyecto:

```powershell
docker compose up -d --build
```

## 7. Reiniciar completamente la base de datos

Si es necesario reinicializar el entorno de desarrollo desde cero, se pueden ejecutar:

```powershell
docker compose down -v
docker compose up -d --build
```

**IMPORTANTE:** el comando `docker compose down -v` elimina el volumen de MySQL y todos los datos almacenados en él.

Utilizarlo únicamente cuando se quiera reiniciar completamente la base de datos y no sea necesario conservar la información existente.

## 8. Consideraciones importantes

- Ejecutar los comandos de Docker Compose desde la carpeta `APIs_SIGERU`.
- Mantener ambos repositorios en carpetas hermanas.
- No modificar ni subir el archivo `.env` a GitHub.
- Las claves Laravel y JWT se generan automáticamente mediante `preparar-docker.ps1`.
- Las tres APIs de Usuarios, Gestión y Recolección comparten el mismo secreto JWT.
- Para utilizar las notificaciones de Telegram, configurar `TELEGRAM_BOT_TOKEN` y `TELEGRAM_CHAT_ID` en el `.env` principal.
- No es necesario instalar XAMPP, PHP, Composer ni MySQL por separado.

Esta configuración está destinada al desarrollo y las pruebas locales de SiGeRU. Para un despliegue público se deben utilizar credenciales seguras y una configuración apropiada para producción.