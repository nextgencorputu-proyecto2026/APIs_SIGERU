# Scripts de Sistemas Operativos - SiGeRU

Scripts de administración del servidor (Administración de Sistemas Operativos).

## Contenido

- `servidor.sh`: menú de administración (monitoreo, logs, respaldos, firewall, usuarios, grupos, redes, base de datos, contenedores y verificación).
- `backup.sh`: respaldo de la base en `.tar.gz` en la carpeta `respaldos`. Lo ejecuta el cron todos los días a las 2:00 y al iniciar el servidor.
- `restaurar.sh`: restauración de la base desde un respaldo de la carpeta `respaldos`.
- `config.conf`: variables (contenedor, IP, rutas y retención).
- `modulos/`: un archivo por cada opción del menú.
- `instalacion/respaldo.sh` y `instalacion/restaurar.sh`: respaldo y restauración en `.tar.gz` para usar en la instalación. Se copian dentro de `APIs_SIGERU` y se ejecutan desde ahí.

El respaldo principal del sistema lo hace el contenedor `respaldo` (carpeta `respaldo/` de `APIs_SIGERU`), que genera una copia al iniciar y cada 24 horas en su propio volumen y permite restaurarla.

## Uso

Copiar el contenido de esta carpeta en la carpeta que contiene `APIs_SIGERU` (por ejemplo `~/sigeru`) y ejecutar:

    sudo ./servidor.sh