# Moving WHMCS out of Docker onto the host (Ubuntu/Debian)

Same server, same public IP, same domains — so the Virtutel firewall
registration, callback URL, SSL hostnames, and Stripe config all survive
unchanged. The move is: install the stack on the host, copy files + DB
out of the containers, point Apache at the docroot, cut over, keep the
containers stopped (not deleted) as the rollback.

Run everything as root (`sudo -i`). Assumes Ubuntu 22.04/24.04; Debian is
the same apart from the PHP repo step.

---

## 0. Discovery — learn what the containers actually hold

```bash
docker ps                                   # container names (web + db?)
docker inspect whmcs --format '{{json .Mounts}}' | python3 -m json.tool
# Find the DB credentials WHMCS uses:
grep -E "db_host|db_username|db_password|db_name" /home/korvix/whmcs/whmcs/configuration.php
```

Note down: the **docroot path on the host** (bind mount — likely
`/home/korvix/whmcs/whmcs`), the **db_host** (a container name means the
DB lives in Docker and must be dumped; `localhost` means it may already
be on the host), and the **db credentials**.

## 1. Install Apache + PHP 8.2 + MariaDB on the host

```bash
apt update
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php        # skip if php8.2 is already in apt
apt update
apt install -y apache2 mariadb-server \
  php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl php8.2-gd \
  php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl php8.2-soap \
  php8.2-bcmath php8.2-gmp php8.2-imap php8.2-opcache
a2enmod proxy_fcgi setenvif rewrite headers ssl
a2enconf php8.2-fpm
systemctl enable --now apache2 mariadb php8.2-fpm
mysql_secure_installation                    # set root pw, remove test db
```

## 2. ionCube Loader (WHMCS won't boot without it)

```bash
cd /tmp
wget https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz
tar xzf ioncube_loaders_lin_x86-64.tar.gz
PHP_EXT_DIR=$(php8.2 -i | awk '/^extension_dir/ {print $3; exit}')
cp ioncube/ioncube_loader_lin_8.2.so "$PHP_EXT_DIR/"
for sapi in fpm cli; do
  echo "zend_extension=$PHP_EXT_DIR/ioncube_loader_lin_8.2.so" \
    > /etc/php/8.2/$sapi/conf.d/00-ioncube.ini
done
systemctl restart php8.2-fpm
php8.2 -v          # must print "with the ionCube PHP Loader"
```

## 3. PHP settings WHMCS wants

```bash
for sapi in fpm cli; do
  PINI=/etc/php/8.2/$sapi/php.ini
  sed -i 's/^memory_limit.*/memory_limit = 256M/' $PINI
  sed -i 's/^upload_max_filesize.*/upload_max_filesize = 64M/' $PINI
  sed -i 's/^post_max_size.*/post_max_size = 64M/' $PINI
  sed -i 's/^max_execution_time.*/max_execution_time = 300/' $PINI
done
systemctl restart php8.2-fpm
```

## 4. Migrate the database

If db_host in configuration.php is a container name (e.g. `db`/`mysql`):

```bash
# Dump from the DB container (use the creds from step 0):
docker exec <db-container> mysqldump -u<db_user> -p'<db_pass>' \
  --single-transaction --routines <db_name> > /root/whmcs-dump.sql

# Create the DB + user on the host (SAME name/user/pass keeps
# configuration.php edits minimal — change the password later if you
# rotate, which you should since it was pasted in chat):
mysql -e "CREATE DATABASE <db_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER '<db_user>'@'localhost' IDENTIFIED BY '<db_pass>';"
mysql -e "GRANT ALL PRIVILEGES ON <db_name>.* TO '<db_user>'@'localhost'; FLUSH PRIVILEGES;"
mysql <db_name> < /root/whmcs-dump.sql
mysql <db_name> -e "SHOW TABLES;" | head       # sanity: tables imported
```

## 5. Move the docroot into place

```bash
mkdir -p /var/www
rsync -a /home/korvix/whmcs/whmcs/ /var/www/whmcs/
chown -R www-data:www-data /var/www/whmcs
find /var/www/whmcs -type d -exec chmod 755 {} +
find /var/www/whmcs -type f -exec chmod 644 {} +
chmod 400 /var/www/whmcs/configuration.php

# Point WHMCS at the host DB:
sed -i "s/\$db_host = .*/\$db_host = 'localhost';/" /var/www/whmcs/configuration.php
```

## 6. Apache vhost (all three hostnames, one docroot)

```bash
cat > /etc/apache2/sites-available/whmcs.conf <<'EOF'
<VirtualHost *:80>
    ServerName portal.korvix.co
    ServerAlias backend.korvix.co korvix.co www.korvix.co
    DocumentRoot /var/www/whmcs
    <Directory /var/www/whmcs>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/whmcs_error.log
    CustomLog ${APACHE_LOG_DIR}/whmcs_access.log combined
</VirtualHost>
EOF
a2dissite 000-default
a2ensite whmcs
apachectl configtest && systemctl reload apache2
```

## 7. Cut over from the container, then SSL

```bash
docker stop whmcs                # frees ports 80/443 (and db container if any)
docker update --restart=no whmcs # keep it around but never auto-start
systemctl reload apache2

apt install -y certbot python3-certbot-apache
certbot --apache -d portal.korvix.co -d backend.korvix.co \
  -d korvix.co -d www.korvix.co \
  --redirect -m lockie@vysion.com.au --agree-tos -n
```

(If the old certs live in the container, certbot simply issues fresh ones
— nothing to copy. Callbacks require the CA-signed cert on
backend.korvix.co, which this provides.)

## 8. WHMCS cron on the host

```bash
crontab -u www-data -l 2>/dev/null | grep -v 'whmcs/crons' | crontab -u www-data -
( crontab -u www-data -l 2>/dev/null; \
  echo "*/5 * * * * /usr/bin/php8.2 -q /var/www/whmcs/crons/cron.php" ) \
  | crontab -u www-data -
```

(If your install's crons dir was moved, the correct path is shown in
WHMCS admin → Configuration → System Settings → Automation Settings.)

## 9. Verify

```bash
curl -sI https://portal.korvix.co/ | head -3          # 200, no container involved
php8.2 -q /var/www/whmcs/crons/cron.php --force       # cron runs clean
curl -s https://api.whmcs.com >/dev/null && echo "outbound OK"   # the reason we left docker
```

Then in the browser: admin login → Utilities → System → System Health;
run a TestConnection on the Virtutel server record; download one invoice
PDF; run one customer line check.

WHMCS licensing: the licence is tied to domain + IP + directory. Same
domain and IP, new directory → if the admin area complains "Licence
Invalid", log into the WHMCS members area (or your licence reseller) and
hit **Reissue**, then reload the admin page.

## 10. Deploy script after the move

`update-whmcs-plugin.sh` now auto-detects: it prefers `/var/www/whmcs`,
falls back to the old bind-mount path, takes the file owner from
`configuration.php`, and reloads PHP-FPM/Apache when there's no running
`whmcs` container. No flags needed:

```bash
sudo bash /home/korvix/update-whmcs-plugin.sh /home/korvix/virtutel_nbn_vX.Y.Z.zip
```

## Rollback (if anything is wrong)

```bash
systemctl stop apache2
docker update --restart=unless-stopped whmcs
docker start whmcs               # (and the db container if separate)
```

The containers were stopped, not deleted, and their data untouched — the
old stack comes back exactly as it was. Delete them only after a week of
clean running:

```bash
docker rm whmcs && docker system prune
```
