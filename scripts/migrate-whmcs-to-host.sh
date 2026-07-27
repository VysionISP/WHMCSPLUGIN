#!/usr/bin/env bash
#
# WHMCS docker -> host migration for kvx-mel1-server01-whmcs01.
#   Usage:  sudo bash migrate-whmcs-to-host.sh          # run the migration
#           sudo bash migrate-whmcs-to-host.sh rollback # back to docker
#
# Phases: checks -> stack install -> DB migration -> docroot -> web server
# -> cutover -> verify. Prompts before the web-server swap and the
# container stop; everything before those points is non-destructive.
# Containers are stopped, never deleted — rollback restores them.
set -euo pipefail

SRC="${SRC:-/home/korvix/whmcs/whmcs}"
DEST="${DEST:-/var/www/whmcs}"
WEB_CT="${WEB_CT:-whmcs}"
DB_CT="${DB_CT:-whmcs-db}"
PHPV="${PHPV:-8.2}"
DOMAINS="${DOMAINS:-portal.korvix.co backend.korvix.co}"
STATE=/root/.whmcs-migration
mkdir -p "$STATE"

say()  { echo -e "\n\033[1;36m==> $*\033[0m"; }
warn() { echo -e "\033[1;33m    WARN: $*\033[0m"; }
die()  { echo -e "\033[1;31m    ABORT: $*\033[0m"; exit 1; }
confirm() {
  read -r -p "    $1 [y/N] " a
  [[ "$a" =~ ^[Yy]$ ]] || die "declined"
}

[ "$(id -u)" = 0 ] || die "run with sudo"

# ---------------------------------------------------------------- rollback
if [ "${1:-}" = "rollback" ]; then
  say "Rolling back to the docker stack"
  systemctl stop apache2 2>/dev/null || true
  systemctl stop nginx 2>/dev/null || true
  if [ -f "$STATE/disabled-nginx-site" ]; then
    while read -r f; do mv "$f.kx-disabled" "$f" 2>/dev/null || true; done < "$STATE/disabled-nginx-site"
    systemctl start nginx
  fi
  if [ -f "$STATE/disabled-apache-site" ]; then
    while read -r s; do a2ensite "$s" >/dev/null; done < "$STATE/disabled-apache-site"
    a2dissite whmcs-host 2>/dev/null || true
    systemctl start apache2
  fi
  docker update --restart=unless-stopped "$WEB_CT" "$DB_CT" >/dev/null 2>&1 || true
  docker start "$DB_CT" "$WEB_CT"
  say "Docker stack restarted — verify the site, then investigate."
  exit 0
fi

# ------------------------------------------------------------- phase 0: checks
say "Phase 0: pre-flight checks"
[ -f "$SRC/configuration.php" ] || die "no WHMCS at $SRC"
docker ps --format '{{.Names}}' | grep -qx "$WEB_CT" || die "container $WEB_CT not running"
docker ps --format '{{.Names}}' | grep -qx "$DB_CT" || die "container $DB_CT not running"

DB_NAME=$(sudo grep -oP "\\\$db_name\s*=\s*'\K[^']+" "$SRC/configuration.php")
DB_USER=$(sudo grep -oP "\\\$db_username\s*=\s*'\K[^']+" "$SRC/configuration.php")
DB_PASS=$(sudo grep -oP "\\\$db_password\s*=\s*'\K[^']+" "$SRC/configuration.php")
[ -n "$DB_NAME" ] && [ -n "$DB_USER" ] || die "could not parse DB creds from configuration.php"
echo "    DB: $DB_NAME / $DB_USER (password read from configuration.php)"

echo "    Checking HOST outbound HTTPS (the reason we're leaving docker)..."
if curl -sSm 10 -o /dev/null -w '%{http_code}' https://api.whmcs.com | grep -qE '^[23]'; then
  echo "    outbound OK"
else
  warn "host cannot reach https://api.whmcs.com — fix host networking/proxy FIRST or the move solves nothing"
  confirm "continue anyway?"
fi

FRONT=""
LISTEN=$(ss -tlnp 2>/dev/null | grep -E ':(80|443)\s' || true)
echo "$LISTEN" | grep -q nginx && FRONT=nginx
echo "$LISTEN" | grep -qE 'apache2|httpd' && FRONT=apache
echo "    Ports 80/443 currently served by: ${FRONT:-nothing detected}"
[ -n "$FRONT" ] || warn "nothing on 80/443 — assuming fresh apache install will own them"

# ------------------------------------------------------- phase 1: host stack
say "Phase 1: installing PHP $PHPV + MariaDB + web server packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
if ! apt-cache show "php$PHPV-fpm" >/dev/null 2>&1; then
  apt-get install -y -qq software-properties-common
  add-apt-repository -y ppa:ondrej/php
  apt-get update -qq
fi
apt-get install -y -qq mariadb-server \
  "php$PHPV" "php$PHPV-fpm" "php$PHPV-cli" "php$PHPV-mysql" "php$PHPV-curl" \
  "php$PHPV-gd" "php$PHPV-mbstring" "php$PHPV-xml" "php$PHPV-zip" \
  "php$PHPV-intl" "php$PHPV-soap" "php$PHPV-bcmath" "php$PHPV-gmp" \
  "php$PHPV-imap" "php$PHPV-opcache" rsync curl
if [ "$FRONT" = "nginx" ]; then
  apt-get install -y -qq nginx
else
  apt-get install -y -qq apache2
  a2enmod proxy_fcgi setenvif rewrite headers ssl >/dev/null
  a2enconf "php$PHPV-fpm" >/dev/null
fi
systemctl enable --now mariadb "php$PHPV-fpm" >/dev/null

# ionCube
if ! php"$PHPV" -v | grep -q ionCube; then
  say "Installing ionCube loader"
  cd /tmp
  curl -fsSLo ioncube.tgz https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz
  tar xzf ioncube.tgz
  EXTD=$(php"$PHPV" -i | awk '/^extension_dir/ {print $3; exit}')
  cp "ioncube/ioncube_loader_lin_$PHPV.so" "$EXTD/"
  for sapi in fpm cli; do
    echo "zend_extension=$EXTD/ioncube_loader_lin_$PHPV.so" > "/etc/php/$PHPV/$sapi/conf.d/00-ioncube.ini"
  done
  systemctl restart "php$PHPV-fpm"
  php"$PHPV" -v | grep -q ionCube || die "ionCube failed to load"
fi

for sapi in fpm cli; do
  PINI="/etc/php/$PHPV/$sapi/php.ini"
  sed -i 's/^memory_limit.*/memory_limit = 256M/;s/^upload_max_filesize.*/upload_max_filesize = 64M/;s/^post_max_size.*/post_max_size = 64M/;s/^max_execution_time.*/max_execution_time = 300/' "$PINI"
done
systemctl restart "php$PHPV-fpm"

# --------------------------------------------------------- phase 2: database
say "Phase 2: migrating the database out of $DB_CT"
if ! mysql -e "USE $DB_NAME" 2>/dev/null; then
  docker exec "$DB_CT" mysqldump -u"$DB_USER" -p"$DB_PASS" \
    --single-transaction --routines "$DB_NAME" > "$STATE/whmcs-dump.sql"
  [ -s "$STATE/whmcs-dump.sql" ] || die "dump is empty"
  echo "    dump: $(du -h "$STATE/whmcs-dump.sql" | cut -f1)"
  mysql -e "CREATE DATABASE \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
  mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"
  mysql "$DB_NAME" < "$STATE/whmcs-dump.sql"
else
  warn "host DB $DB_NAME already exists — skipping dump/import (delete it to redo)"
fi
TABLES=$(mysql -N "$DB_NAME" -e "SHOW TABLES" | wc -l)
echo "    $TABLES tables on host DB"
[ "$TABLES" -gt 50 ] || die "host DB looks incomplete"

# ---------------------------------------------------------- phase 3: docroot
say "Phase 3: copying docroot to $DEST"
mkdir -p "$DEST"
rsync -a --delete "$SRC/" "$DEST/"
sed -i "s/\$db_host = .*/\$db_host = 'localhost';/" "$DEST/configuration.php"
chown -R www-data:www-data "$DEST"
find "$DEST" -type d -exec chmod 755 {} +
find "$DEST" -type f -exec chmod 644 {} +
chmod 400 "$DEST/configuration.php"

# ------------------------------------------------------- phase 4: web server
say "Phase 4: web server config for: $DOMAINS"
PRIMARY=$(echo "$DOMAINS" | awk '{print $1}')
CERT=""
for d in $DOMAINS; do
  [ -d "/etc/letsencrypt/live/$d" ] && CERT="/etc/letsencrypt/live/$d" && break
done
[ -n "$CERT" ] && echo "    reusing existing cert: $CERT" \
  || warn "no letsencrypt cert found on host — will need certbot after cutover"

if [ "$FRONT" = "nginx" ]; then
  cat > /etc/nginx/sites-available/whmcs-host <<EOF
server {
    listen 80;
    server_name $DOMAINS;
    return 301 https://\$host\$request_uri;
}
server {
    listen 443 ssl http2;
    server_name $DOMAINS;
$( [ -n "$CERT" ] && printf '    ssl_certificate %s/fullchain.pem;\n    ssl_certificate_key %s/privkey.pem;' "$CERT" "$CERT" )
    root $DEST;
    index index.php;
    client_max_body_size 64m;
    location / { try_files \$uri \$uri/ /index.php\$is_args\$args; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php$PHPV-fpm.sock;
    }
    location ~* /(vendor|node_modules)/ { deny all; }
    error_page 404 /404.php;
}
EOF
else
  cat > /etc/apache2/sites-available/whmcs-host.conf <<EOF
<VirtualHost *:80>
    ServerName $PRIMARY
    ServerAlias $(echo "$DOMAINS" | cut -d' ' -f2-)
    DocumentRoot $DEST
    <Directory $DEST>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorDocument 404 /404.php
</VirtualHost>
$( if [ -n "$CERT" ]; then cat <<SSL
<VirtualHost *:443>
    ServerName $PRIMARY
    ServerAlias $(echo "$DOMAINS" | cut -d' ' -f2-)
    DocumentRoot $DEST
    SSLEngine on
    SSLCertificateFile $CERT/fullchain.pem
    SSLCertificateKeyFile $CERT/privkey.pem
    <Directory $DEST>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorDocument 404 /404.php
</VirtualHost>
SSL
fi )
EOF
fi

# ----------------------------------------------------------- phase 5: cutover
say "Phase 5: CUTOVER — this swaps live traffic to the host install"
confirm "stop container '$WEB_CT' and switch the web server now?"

docker update --restart=no "$WEB_CT" >/dev/null
docker stop "$WEB_CT" >/dev/null
echo "    container '$WEB_CT' stopped (kept for rollback)"

if [ "$FRONT" = "nginx" ]; then
  : > "$STATE/disabled-nginx-site"
  grep -rl "127.0.0.1:8080" /etc/nginx/sites-enabled/ 2>/dev/null | while read -r f; do
    real=$(readlink -f "$f"); mv "$real" "$real.kx-disabled"; echo "$real" >> "$STATE/disabled-nginx-site"
  done
  ln -sf /etc/nginx/sites-available/whmcs-host /etc/nginx/sites-enabled/whmcs-host
  nginx -t && systemctl reload nginx
else
  : > "$STATE/disabled-apache-site"
  grep -rl "127.0.0.1:8080" /etc/apache2/sites-enabled/ 2>/dev/null | while read -r f; do
    s=$(basename "$f" .conf); a2dissite "$s" >/dev/null; echo "$s" >> "$STATE/disabled-apache-site"
  done
  a2ensite whmcs-host >/dev/null
  apachectl configtest && systemctl reload apache2 || systemctl restart apache2
fi

# Keep the DB container running until you're happy, but stop new writes to
# it: WHMCS now talks to localhost. Cron:
crontab -u www-data -l 2>/dev/null | grep -v 'crons/cron.php' | crontab -u www-data - 2>/dev/null || true
( crontab -u www-data -l 2>/dev/null; echo "*/5 * * * * /usr/bin/php$PHPV -q $DEST/crons/cron.php" ) | crontab -u www-data -

# ------------------------------------------------------------ phase 6: verify
say "Phase 6: verify"
for d in $DOMAINS; do
  code=$(curl -sk -o /dev/null -w '%{http_code}' "https://$d/" --resolve "$d:443:127.0.0.1" || echo fail)
  echo "    https://$d/ -> HTTP $code"
done
php"$PHPV" -q "$DEST/crons/cron.php" --force >/dev/null 2>&1 && echo "    cron runs clean" || warn "cron run reported errors — check admin > Automation Status"

say "DONE — manual checklist"
cat <<EOF
    1. Log into admin. If 'Licence Invalid' appears: reissue the licence
       in the WHMCS members area, reload.
    2. Configuration > System Settings > Mail: SMTP host must be
       127.0.0.1 port 1025 (proton-bridge container — still running).
       Send a test email.
    3. Run a Virtutel TestConnection, download one invoice PDF, run one
       customer line check.
    4. Rotate the DB password (it was shared in chat):
         mysql -e "ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY 'NEWPASS'; FLUSH PRIVILEGES;"
         then update \$db_password in $DEST/configuration.php
    5. No cert found? Run:
         apt install -y certbot python3-certbot-${FRONT:-apache}
         certbot --${FRONT:-apache} $(for d in $DOMAINS; do printf ' -d %s' "$d"; done) --redirect
    6. After a clean week:  docker stop $DB_CT && docker rm $WEB_CT $DB_CT
    Rollback any time:  sudo bash $0 rollback
EOF
