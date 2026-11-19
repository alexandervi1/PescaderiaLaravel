# Despliegue en VPS (Oracle Cloud) con Cloudflare

Stack: `caddy` (TLS con certificado de origen) → `app` (PHP 8.2/Apache, build `prod`) → `db` (MySQL 8).
Cloudflare termina la conexión pública; el origen sólo habla HTTPS con el certificado de Cloudflare.

## Checklist para el agente del VPS

Supuesto: el repositorio ya está clonado en `/opt/pescaderia` y el usuario tiene `docker` sin `sudo`.

1. `cd /opt/pescaderia && git pull --ff-only`
2. Verificar `deploy/.env.prod` (si no existe: `cp deploy/.env.prod.example deploy/.env.prod` y completar, ver abajo).
3. Verificar `deploy/certs/origin.pem` y `deploy/certs/origin.key`.
4. `chmod +x deploy/deploy.sh deploy/backup.sh`
5. `./deploy/deploy.sh`
6. Comprobar: `docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod ps` (los tres servicios `running`, `db` `healthy`).
7. `curl -sI https://APP_DOMAIN | head -1` debe responder `HTTP/2 200` (o `301` a `/`).

No hay paso manual de migración: el entrypoint del contenedor espera a MySQL, aplica `migrate --force`, siembra sólo si la tabla `usuario` está vacía y cachea config/rutas/vistas.

## 1. Preparar el VPS (una sola vez)

```bash
# Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker "$USER" && newgrp docker

# Swap si la instancia tiene 1 GB (el build de Composer lo agradece)
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

Firewall del host (las imágenes de Oracle traen reglas que rechazan todo salvo SSH):

```bash
sudo iptables -I INPUT 6 -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save          # Ubuntu/Debian
# Oracle Linux: sudo firewall-cmd --permanent --add-service=http --add-service=https && sudo firewall-cmd --reload
```

En la consola de Oracle: **Networking → VCN → Security Lists** debe permitir ingress TCP 80 y 443 desde `0.0.0.0/0`.

## 2. Secretos (`deploy/.env.prod`, `chmod 600`)

```bash
cp deploy/.env.prod.example deploy/.env.prod
echo "APP_KEY=base64:$(openssl rand -base64 32)"
echo "MYSQL_PASSWORD=$(openssl rand -hex 24)"
echo "MYSQL_ROOT_PASSWORD=$(openssl rand -hex 24)"
```

Rellenar como mínimo: `APP_DOMAIN`, `APP_URL` (`https://` + dominio), `APP_KEY`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `DB_PASSWORD` (igual a `MYSQL_PASSWORD`), `INITIAL_ADMIN_USER`, `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_PASSWORD`.
`INITIAL_ADMIN_PASSWORD` evita arrancar en producción con la contraseña conocida `admin123`; sólo se usa la primera vez que la base está vacía.

## 3. Cloudflare

1. **DNS**: registro `A` hacia la IP pública del VPS, proxied (nube naranja).
2. **SSL/TLS → Origin Server → Create Certificate**: hostnames `APP_DOMAIN` y `*.APP_DOMAIN`, validez 15 años. Guardar el PEM y la clave como `deploy/certs/origin.pem` / `origin.key` (`chmod 600 origin.key`).
3. **SSL/TLS → Overview**: modo **Full (strict)**. Nunca "Flexible" (bucle de redirecciones y tráfico en claro).
4. **SSL/TLS → Edge Certificates**: activar *Always Use HTTPS*.
5. Opcional pero recomendado: regla de rate limit en Cloudflare para `/login` y `/admin/login`, y *Bot Fight Mode*.

## 4. Verificación tras el despliegue

```bash
docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod logs --tail=60 app
docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod exec app php artisan about
```

- Home pública responde y muestra catálogo.
- Login del administrador funciona; cambiar la contraseña en **Configuración → Seguridad**.
- `http://` redirige a `https://` (lo hace Caddy).
- Cabeceras: `curl -sI https://APP_DOMAIN` muestra `strict-transport-security`, sin `Server` ni `X-Powered-By`.

## 5. Actualizaciones y rollback

```bash
./deploy/deploy.sh          # git pull + build + recreate (migraciones automáticas)
```

Rollback de código: `git checkout <commit-o-tag> && ./deploy/deploy.sh`. Las migraciones no se deshacen solas: restaurar el dump previo si el esquema cambió.

## 6. Backups

```bash
sudo mkdir -p /var/backups/pescaderia
sudo crontab -e
# 15 3 * * * cd /opt/pescaderia && ./deploy/backup.sh >> /var/log/pescaderia-backup.log 2>&1
```

Genera `mysqldump` comprimido + tar de `public/img/productos` y `public/img/negocio`, y borra lo de más de 14 días. Copiar `/var/backups/pescaderia` fuera del VPS (Object Storage, rclone) si los datos importan.

Restaurar:

```bash
gunzip -c /var/backups/pescaderia/pescaderia-XXXXXXXX.sql.gz \
  | docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod exec -T db \
      sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -u root "$MYSQL_DATABASE"'
```

## 7. Problemas típicos

| Síntoma | Causa / arreglo |
|---|---|
| Cloudflare 521/522 | Puerto 443 cerrado en Security List o iptables; `sudo ss -lntp` en el VPS debe mostrar `:443`. |
| Cloudflare 525 | Certificado de origen mal montado o modo no `Full (strict)`; revisar `docker compose ... logs caddy`. |
| 502 desde Caddy | `app` no está `healthy`; logs `app` (migración fallida, MySQL sin arrancar). |
| `SQLSTATE[HY000] [1045]` | `DB_PASSWORD` ≠ `MYSQL_PASSWORD` en `.env.prod` (MySQL sólo crea el usuario en el primer arranque del volumen). |
| Sesión que caduca al navegar | `APP_KEY` cambió entre despliegues o `SESSION_SECURE_COOKIE=true` con tráfico HTTP directo al origen. |
| Imágenes subidas no aparecen | Permisos de `public/img`; el contenedor corre como `www-data` y escribe en el bind-mount. |
| Build sin memoria | Añadir swap (paso 1). |
| Cambiar contraseña de MySQL con el volumen ya creado | `docker compose ... exec db mysql ...` o recrear con `down -v` (pierde datos). |

## Notas

- `app` no publica puertos: sólo Caddy lo alcanza por la red interna de Docker.
- Código y dependencias viajan en la imagen; sólo `public/img` (subidas) y `storage` (logs, sesiones) son bind mounts.
- Una sola instancia: `QUEUE_CONNECTION=sync`, sesiones y caché en fichero. Para escalar habría que mover sesiones/caché a Redis y publicar la app tras un balanceador.
- El `database.sqlite` del repo no se usa en producción.
