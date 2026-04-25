#!/bin/sh
set -e

# Render nginx config with Railway PORT
envsubst '$PORT' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Ensure volume directories exist
DATA_DIR="${DATA_DIR:-/data}"
mkdir -p "${DATA_DIR}/etalons" "${DATA_DIR}/probes"

# Railway volume can be owned by root; make it writable for PHP-FPM (www-data).
chown -R www-data:www-data "${DATA_DIR}" || true
chmod -R u+rwX,g+rwX "${DATA_DIR}" || true

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
