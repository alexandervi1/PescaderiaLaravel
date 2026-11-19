#!/usr/bin/env bash
# Despliegue/actualización en el VPS. Idempotente: seguro de repetir.
set -euo pipefail

cd "$(dirname "$0")/.."

COMPOSE=(docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod)

[ -f deploy/.env.prod ] || { echo "Falta deploy/.env.prod (copia deploy/.env.prod.example)." >&2; exit 1; }
[ -f deploy/certs/origin.pem ] || { echo "Falta deploy/certs/origin.pem (Cloudflare Origin Certificate)." >&2; exit 1; }

echo "==> git pull"
git pull --ff-only

echo "==> build"
"${COMPOSE[@]}" build app

echo "==> up"
"${COMPOSE[@]}" up -d --wait --wait-timeout 240

echo "==> limpieza de imágenes antiguas"
docker image prune -f

echo "==> estado"
"${COMPOSE[@]}" ps
