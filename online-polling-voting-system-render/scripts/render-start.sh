#!/bin/sh
set -eu
PORT="${PORT:-10000}"
case "$PORT" in *[!0-9]*|'') echo 'Invalid PORT' >&2; exit 1;; esac
sed -i "s/^Listen 80$/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
php scripts/install.php
php scripts/render-admin.php
exec apache2-foreground
