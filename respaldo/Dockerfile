# Servicio de respaldo y recuperacion de SiGeRU
# Usa la misma imagen de MySQL para tener mysqldump y mysql en la misma version
FROM mysql:8.4.11

RUN microdnf install -y tar && microdnf clean all

COPY respaldo.sh restaurar.sh servicio.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/respaldo.sh /usr/local/bin/restaurar.sh /usr/local/bin/servicio.sh

VOLUME /respaldos

ENTRYPOINT ["servicio.sh"]
