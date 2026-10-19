# Ejecución con Docker

Para ejecutar SiGeRU, los repositorios `APIs_SIGERU` y `Frontend_SIGERU` deben estar descargados en la misma carpeta y en la rama `develop`.

Es necesario tener Docker Desktop instalado y funcionando.

## Variables de entorno

Desde la carpeta `APIs_SIGERU`, copiar el archivo `.env.example` para crear el archivo `.env`:

```powershell
Copy-Item .env.example .env
```

Generar las claves necesarias ejecutando los siguientes comandos en PowerShell, desde `APIs_SIGERU`:

```powershell
function New-SigeruSecret {
    param([int]$Bytes = 32)
    $buffer = [byte[]]::new($Bytes)
    [System.Security.Cryptography.RandomNumberGenerator]::Fill($buffer)
    return [Convert]::ToBase64String($buffer)
}

$usuariosKey = "base64:$(New-SigeruSecret)"
$gestionKey = "base64:$(New-SigeruSecret)"
$recoleccionKey = "base64:$(New-SigeruSecret)"
$jwtSecret = New-SigeruSecret -Bytes 48

$envPath = Join-Path (Get-Location) ".env"
$contenido = Get-Content $envPath -Raw

$contenido = $contenido.Replace("USUARIOS_APP_KEY=", "USUARIOS_APP_KEY=$usuariosKey")
$contenido = $contenido.Replace("USUARIOS_JWT_SECRET=", "USUARIOS_JWT_SECRET=$jwtSecret")
$contenido = $contenido.Replace("GESTION_APP_KEY=", "GESTION_APP_KEY=$gestionKey")
$contenido = $contenido.Replace("RECOLECCION_APP_KEY=", "RECOLECCION_APP_KEY=$recoleccionKey")

[System.IO.File]::WriteAllText($envPath, $contenido)
```

Completar también las credenciales de MySQL en `.env`:

```dotenv
SIGERU_DB_USERNAME=sigeru
SIGERU_DB_PASSWORD=sigeru
```

Estas credenciales corresponden a la configuración de desarrollo definida actualmente en `docker-compose.yml`.

Las tres APIs utilizan el mismo secreto JWT, mientras que cada API tiene su propia clave Laravel.

No subir el archivo `.env` a GitHub.

## Iniciar el proyecto

Desde la carpeta `APIs_SIGERU`:

```bash
docker compose up --build -d
```

Para comprobar el estado de los servicios:

```bash
docker compose ps
```

Servicios disponibles:

- Frontend: http://localhost:5173
- API original: http://localhost:8000/api/health
- API Usuarios: http://localhost:8001
- API Gestión: http://localhost:8002
- API Recolección: http://localhost:8003
- MySQL: accesible internamente como `mysql:3306`

El primer inicio importa `proyecto_sigeru.sql` cuando el volumen de MySQL está vacío.

Las tres APIs comparten la base de datos `proyecto_sigeru`. Sus scripts de inicio no ejecutan automáticamente las migraciones de Laravel.

## Detener y reiniciar

Para detener los servicios sin eliminar los datos:

```bash
docker compose down
```

Para volver a iniciarlos:

```bash
docker compose up -d
```

Para reconstruir las imágenes después de actualizar el proyecto:

```bash
docker compose up --build -d
```

Para reinicializar completamente la base de desarrollo:

```bash
docker compose down -v
docker compose up --build -d
```

**Importante:** la opción `down -v` elimina todos los datos del volumen de MySQL. No debe utilizarse si se necesitan conservar.

## Secretos

Las claves `APP_KEY` y `JWT_SECRET` deben generarse para cada instalación y almacenarse en el archivo `.env` ubicado junto a `docker-compose.yml`.

En un despliegue real deben utilizarse contraseñas seguras y evitar los valores de desarrollo definidos en Docker Compose.

Nunca subir archivos `.env` con claves o contraseñas reales a GitHub.