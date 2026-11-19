#!/usr/bin/env bash
# Restaura un respaldo de deploy/backup.sh.
# Uso: ./deploy/restore.sh <archivo.sql.gz> [uploads.tar.gz]
set -euo pipefail

cd "$(dirname "$0")/.."

DUMP="${1:-}"
UPLOADS="${2:-}"

[ -n "$DUMP" ] || { echo "uso: $0 <archivo.sql.gz> [uploads.tar.gz]" >&2; exit 1; }
[ -f "$DUMP" ] || { echo "error: no existe $DUMP" >&2; exit 1; }

if [ -n "$UPLOADS" ] && [ ! -f "$UPLOADS" ]; then
	echo "error: no existe $UPLOADS" >&2
	exit 1
fi

COMPOSE=(docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod)

printf 'Esto REEMPLAZA la base de datos. Escribe RESTAURAR para continuar: ' > /dev/tty
read -r CONFIRMA < /dev/tty
[ "$CONFIRMA" = "RESTAURAR" ] || { echo "cancelado" >&2; exit 1; }

echo "==> deteniendo app"
"${COMPOSE[@]}" stop app

echo "==> restaurando base"
# shellcheck disable=SC2016  # las variables las expande sh dentro del contenedor db.
gunzip -c "$DUMP" | "${COMPOSE[@]}" exec -T db sh -c \
	'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -u root "$MYSQL_DATABASE"'

if [ -n "$UPLOADS" ]; then
	echo "==> restaurando imágenes"
	tar -xzf "$UPLOADS" -C .
fi

echo "==> arrancando app"
"${COMPOSE[@]}" start app

echo "OK: restaurado $DUMP"
