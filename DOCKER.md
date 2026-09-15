# Ejecución con Docker

Desde la carpeta `APIs_SIGERU`:

```bash
docker compose up --build -d
```

Servicios disponibles:

- Frontend: <http://localhost:5173>
- API: <http://localhost:8000/api/health>
- MySQL: accesible internamente como `mysql:3306`

El primer inicio importa `proyecto_sigeru.sql` y después ejecuta las migraciones de Laravel. El dump sólo se importa cuando el volumen de MySQL está vacío.

Para reinicializar completamente la base de desarrollo:

```bash
docker compose down -v
docker compose up --build -d
```

La opción `down -v` elimina todos los datos del volumen; no debe utilizarse si se necesitan conservar.

## Secretos

Los valores incluidos en Compose son únicamente valores de respaldo para desarrollo local. En un despliegue real deben proporcionarse valores propios:

```bash
APP_KEY=base64:... JWT_SECRET=... docker compose up --build -d
```

También pueden definirse `APP_KEY` y `JWT_SECRET` en un archivo `.env` ubicado junto a `docker-compose.yml`.
