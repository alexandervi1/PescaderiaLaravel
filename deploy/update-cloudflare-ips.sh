#!/usr/bin/env bash
# Regenera deploy/cloudflare-ips.caddy con los rangos oficiales de Cloudflare.
# Idempotente: si la descarga o la validación fallan, no toca el archivo actual.
set -euo pipefail

cd "$(dirname "$0")"

DESTINO="cloudflare-ips.caddy"
TEMPORAL="$(mktemp)"
trap 'rm -f "$TEMPORAL"' EXIT

CIDRS=()

for url in https://www.cloudflare.com/ips-v4 https://www.cloudflare.com/ips-v6; do
	contenido="$(curl -fsS --max-time 20 "$url")" || {
		echo "error: no se pudo descargar $url" >&2
		exit 1
	}

	while IFS= read -r linea; do
		linea="$(printf '%s' "$linea" | tr -d '\r' | tr -d '[:space:]')"
		[ -z "$linea" ] && continue

		if printf '%s' "$linea" | grep -Eq '^[0-9]{1,3}(\.[0-9]{1,3}){3}/[0-9]{1,2}$'; then
			CIDRS+=("$linea")
		elif printf '%s' "$linea" | grep -Eq '^[0-9A-Fa-f:]+/[0-9]{1,3}$' && printf '%s' "$linea" | grep -q ':'; then
			CIDRS+=("$linea")
		else
			echo "error: '$linea' no tiene forma de CIDR; se conserva la lista anterior" >&2
			exit 1
		fi
	done <<< "$contenido"
done

if [ "${#CIDRS[@]}" -eq 0 ]; then
	echo "error: Cloudflare no devolvió ningún rango; se conserva la lista anterior" >&2
	exit 1
fi

{
	echo "# Generado por deploy/update-cloudflare-ips.sh — no editar a mano."
	echo "(solo_cloudflare) {"
	printf '\t@fuera_de_cloudflare not remote_ip'
	printf ' %s' "${CIDRS[@]}"
	printf '\n'
	echo '	abort @fuera_de_cloudflare'
	echo '}'
} > "$TEMPORAL"

mv "$TEMPORAL" "$DESTINO"
trap - EXIT

echo "cloudflare-ips.caddy actualizado con ${#CIDRS[@]} rangos."
