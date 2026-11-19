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

DUMP_FILE="$DEST/pescaderia-$STAMP.sql.gz"
UPLOADS_FILE="$DEST/uploads-$STAMP.tar.gz"

# shellcheck disable=SC2016  # las variables las expande sh dentro del contenedor db.
"${COMPOSE[@]}" exec -T db sh -c \
    'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump --single-transaction --quick --no-tablespaces -u root "$MYSQL_DATABASE"' \
    | gzip > "$DUMP_FILE"

if ! gzip -t "$DUMP_FILE" 2>/dev/null; then
    echo "error: el respaldo $DUMP_FILE está corrupto" >&2
    exit 1
fi

if [ "$(wc -c < "$DUMP_FILE")" -le 1024 ]; then
    echo "error: el respaldo $DUMP_FILE pesa menos de 1 KB; no se considera válido" >&2
    exit 1
fi

tar -czf "$UPLOADS_FILE" public/img/productos public/img/negocio 2>/dev/null \
    || tar -czf "$UPLOADS_FILE" public/img

find "$DEST" -type f -mtime "+$KEEP_DAYS" -delete

if [ -n "${BACKUP_REMOTE:-}" ]; then
    if command -v rclone >/dev/null 2>&1; then
        echo "==> copia remota en $BACKUP_REMOTE"
        rclone copy "$DEST" "$BACKUP_REMOTE" --max-age 25h
    else
        echo "aviso: BACKUP_REMOTE está definido pero rclone no está instalado; se omite la copia remota" >&2
    fi
fi

echo "OK $DUMP_FILE $UPLOADS_FILE"
