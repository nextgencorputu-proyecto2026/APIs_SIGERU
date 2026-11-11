FROM mysql:8.4.11

# Usar herramientas de MySQL 8.4, compatible con el servidor del proyecto.
# tar es necesario porque el docente exige archivos .tar.gz.
RUN microdnf install -y tar gzip && microdnf clean all

COPY respaldo.sh /respaldo.sh
RUN chmod +x /respaldo.sh