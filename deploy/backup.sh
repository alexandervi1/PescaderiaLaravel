#!/usr/bin/env bash
# Backup diario: dump de MySQL + imágenes subidas desde el panel.
# Cron sugerido: 15 3 * * * /opt/pescaderia/deploy/backup.sh >> /var/log/pescaderia-backup.log 2>&1
set -euo pipefail

cd "$(dirname "$0")/.."

COMPOSE=(docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod)
DEST="${BACKUP_DIR:-/var/backups/pescaderia}"
STAMP="$(date +%Y%m%d-%H%M%S)"
KEEP_DAYS="${BACKUP_KEEP_DAYS:-14}"

mkdir -p "$DEST"

"${COMPOSE[@]}" exec -T db sh -c \
    'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump --single-transaction --quick --no-tablespaces -u root "$MYSQL_DATABASE"' \
    | gzip > "$DEST/pescaderia-$STAMP.sql.gz"

tar -czf "$DEST/uploads-$STAMP.tar.gz" public/img/productos public/img/negocio 2>/dev/null \
    || tar -czf "$DEST/uploads-$STAMP.tar.gz" public/img

find "$DEST" -type f -mtime "+$KEEP_DAYS" -delete

echo "OK $DEST/pescaderia-$STAMP.sql.gz $DEST/uploads-$STAMP.tar.gz"
