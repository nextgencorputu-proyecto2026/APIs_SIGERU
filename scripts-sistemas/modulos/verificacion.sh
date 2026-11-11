SSH_S3="-i $SERVIDOR3_LLAVE -o BatchMode=yes -o ConnectTimeout=5"

function ip_correcta() { ip a | grep -q "$IP_SERVIDOR/"; }
function ssh_activo() { systemctl is-active --quiet ssh || systemctl is-active --quiet ssh.socket; }
function firewall_activo() { sudo ufw status | grep -q -E "Status: active|Estado: activo"; }
function hora_sincronizada() { timedatectl show -p NTPSynchronized --value | grep -q "yes"; }
function docker_activo() { systemctl is-active --quiet docker; }
function mysql_corriendo() { sudo docker ps | grep -q "$CONTENEDOR_DB"; }
function cron_configurado() { sudo crontab -l | grep -q "backup.sh"; }
function respaldo_de_hoy() { ls "$DIR_RESPALDOS" 2>/dev/null | grep -q "backup_sigeru_$(date +%F)"; }
function disco_suficiente() { ! df -h / | grep -q -E " (9[0-9]|100)% "; }
function mysql_solo_local() { ! sudo ss -tulpn | grep -q -E "(0.0.0.0|\[::\]):3306 "; }
function servidor3_accesible() { sudo ssh $SSH_S3 "$SERVIDOR3_USUARIO@$SERVIDOR3_IP" true; }
function respaldo_de_hoy_en_servidor3() { sudo ssh $SSH_S3 "$SERVIDOR3_USUARIO@$SERVIDOR3_IP" "ls $SERVIDOR3_DIR" | grep -q "backup_sigeru_$(date +%F)"; }
function web_corriendo() {
    for c in apis_sigeru-mysql-1 apis_sigeru-api-1 apis_sigeru-api-usuarios-1 apis_sigeru-api-gestion-1 apis_sigeru-api-recoleccion-1 apis_sigeru-ui-1; do
        sudo docker ps | grep -q "$c" || return 1
    done
}

function base_con_tablas() {
    sudo docker exec "$CONTENEDOR_DB" sh -c "MYSQL_PWD=\"\$MYSQL_ROOT_PASSWORD\" mysql -u root -e \"SHOW TABLES;\" $BASE_DATOS" | grep -q "Tables_in"
}

function chequear() {
    descripcion="$1"
    shift
    if "$@" &>/dev/null; then
        echo "[ OK    ] $descripcion"
    else
        echo "[ FALLA ] $descripcion"
        fallas=$((fallas + 1))
    fi
}

function verificar_sistema() {
    echo "== Verificación del sistema =="
    fallas=0
    chequear "IP estática $IP_SERVIDOR configurada" ip_correcta
    chequear "Servicio SSH activo" ssh_activo
    chequear "Firewall (UFW) activo" firewall_activo
    chequear "Hora sincronizada" hora_sincronizada
    chequear "Docker activo" docker_activo
    chequear "Contenedor $CONTENEDOR_DB en ejecución" mysql_corriendo
    chequear "Aplicación SiGeRU (6 contenedores) en ejecución" web_corriendo
    chequear "Base $BASE_DATOS con tablas cargadas" base_con_tablas
    chequear "Respaldo automático configurado en cron" cron_configurado
    chequear "Respaldo de hoy en main" respaldo_de_hoy
    chequear "VM respaldo accesible por SSH" servidor3_accesible
    chequear "Copia de hoy en la VM respaldo" respaldo_de_hoy_en_servidor3
    chequear "Disco con menos del 90% de uso" disco_suficiente
    chequear "MySQL (3306) no expuesto a toda la red" mysql_solo_local
    echo "Resultado: $fallas verificación(es) con falla"
    registrar "Verificación del sistema: $fallas falla(s)"
}
