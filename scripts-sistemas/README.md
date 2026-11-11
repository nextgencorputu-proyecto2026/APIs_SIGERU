# Scripts de Sistemas Operativos - SiGeRU

Scripts de administración del servidor (Administración de Sistemas Operativos).

## Contenido

- `servidor.sh`: menú de administración (monitoreo, logs, respaldos, firewall, usuarios, grupos, redes, base de datos, contenedores y verificación).
- `backup.sh`: respaldo de la base en `.tar.gz` y copia a la VM respaldo.
- `restaurar.sh`: restauración desde un respaldo local o de la VM respaldo.
- `config.conf`: variables (contenedor, IPs, rutas).
- `modulos/`: un archivo por cada opción del menú.
- `instalacion/respaldo.sh` y `instalacion/restaurar.sh`: respaldo y restauración en `.tar.gz` sin VM respaldo, para usar en la instalación. Se copian dentro de `APIs_SIGERU` y se ejecutan desde ahí.

## Uso

Copiar el contenido de esta carpeta en la carpeta que contiene `APIs_SIGERU` (por ejemplo `~/sigeru`) y ejecutar:

    sudo ./servidor.sh
