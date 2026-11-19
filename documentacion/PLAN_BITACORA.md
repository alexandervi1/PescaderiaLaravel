# Bitácora de ejecución

- [x] F0.1 — b3fda22 — commit del trabajo Docker pendiente (rama `mejora/produccion`) — pruebas: 50 passed
- [x] F1.1 — f476a31 — valida credenciales del admin inicial en producción — pruebas: 53 passed
- [x] F1.2 — 833198a — separa catálogo demo (SEED_DEMO) y omite datos personales en producción — pruebas: 56 passed
- [x] F1.3 — 9377d28 — Caddy rechaza tráfico que no venga de Cloudflare; lista regenerable con `deploy/update-cloudflare-ips.sh` — pruebas: 56 passed. `caddy validate` NO ejecutado: el daemon de Docker no está disponible en esta máquina.
- [x] F1.4 — 0eb79d9 — proxies de confianza desde `TRUSTED_PROXIES` vía `config('app.trusted_proxies')` — pruebas: 58 passed
- [x] F1.5 — 045c206 — checkout no filtra SQL; `QueryException` hereda de `RuntimeException`, así que se captura `PDOException` antes (desvío del plan, necesario para cumplir su propia prueba) — pruebas: 59 passed
- [x] F2.1 — d4f3da7 — `Pedido::ESTADOS` / `TRANSICIONES` / `puedeCambiarA()`; el select sólo ofrece transiciones válidas — pruebas: 63 passed
- [x] F2.2 — f9f96a8 — `cancelOrder()` y `changeStatus()` transaccionales con `lockForUpdate` en `OrderService`; el controlador ya no usa `DB::transaction` — pruebas: 63 passed
- [x] F2.3 — c1ac5b8 — 8 pruebas de transiciones, reposición exacta, doble cancelación, producto borrado y cliente sin permiso — pruebas: 71 passed
- [x] F3.1 — 0c6cbe2 — `GET /api/salud` comprueba la BD y responde 200/503 sin detalles; healthcheck apunta ahí — pruebas: 72 passed
- [x] F3.2 — 685cbb2 — `SESSION_ENCRYPT` y `SESSION_SAME_SITE` por entorno; `.env.prod.example` con `encrypt=true`, `lax` y 120 min — pruebas: 73 passed
- [x] F3.3 — ff7070a — middleware `CabecerasSeguridad` en el grupo `web` (nosniff, SAMEORIGIN, Referrer-Policy, Permissions-Policy) — pruebas: 73 passed
- [x] F3.4 — 73a3475 — limitadores `login-ip` (20/min) y `registro` (10/hora) aplicados a `POST /login` y `POST /registro` — pruebas: 75 passed
- [x] F3.5 — 19832a9 — imágenes de producto y logo: jpg/jpeg/png/webp, máximo 4000×4000, nombre UUID — pruebas: 78 passed. Las pruebas usan PNG generados sin `ext-gd` porque el PHP local no la trae.
- [x] F3.6 — ced3788 — auditoría de middleware en `Route::getRoutes()` y aislamiento de `/mis-pedidos/{id}` entre clientes — pruebas: 81 passed
- [x] F4.1 — 21e971c — `mem_limit` 512m/1g/128m, logs json-file 10m×5 y `no-new-privileges`; `docker compose config` válido — pruebas: 81 passed
- [x] F4.2 — 8dadf31 — `deploy.sh` ejecuta `backup.sh` si `db` está corriendo y aborta si falla — pruebas: 81 passed
- [x] F4.3 — b777cf5 — `deploy/restore.sh` con confirmación por `/dev/tty`; `backup.sh` valida `gzip -t` y tamaño > 1 KB — pruebas: 81 passed
- [x] F4.4 — 2db995e — `BACKUP_REMOTE` con `rclone copy --max-age 25h` y guía de Oracle Object Storage — pruebas: 81 passed
- [x] F4.5 — 2dd8427 — cron de `schedule:run` documentado. **Limpieza de carritos NO implementada**: `carrito` no tiene `updated_at` (sólo `fecha_agregado`), y el plan prohíbe inventarla — pruebas: 81 passed
- [x] F5.1 — bc05c8e — workflow con jobs `pruebas`, `auditoria`, `docker` y `shell`; shellcheck limpio (SC2016 intencional suprimido) — pruebas: 81 passed. `docker build` y la ejecución real de GitHub Actions no se pudieron verificar aquí (daemon ausente).
- [x] F5.2 — 1b1f4a7 — Larastan ^2.0 (v2.11.2), `phpstan.neon` nivel 5 sobre `app/`, línea base y `config.platform.php=8.2.2`; paso añadido al job `pruebas` — pruebas: 81 passed
- [x] F6.1 — 395bd68 — todos los `index()` del panel usan DataTables (se dejan como estaban); pedidos acotado a 90 días por defecto con selector y `?desde=` — pruebas: 82 passed
- [x] F6.2 — d00ca72 — migración nueva: `pedido(usuario_id, fecha_pedido)`, `pedido(estado)`, `producto(categoria_id)`; `carrito(usuario_id)` omitido porque el unique existente ya lo cubre — pruebas: 82 passed
- [x] F6.3 — 930a4f2 — `Model::preventLazyLoading` fuera de producción, N+1 corregidos en reportes y carrito, `event:cache` en el entrypoint — pruebas: 82 passed
- [x] F7.1 — 0233b64 — rama `mejora/laravel-10`: Laravel 10.50.4, Sanctum 3.3.3, Collision 7.12, PHPUnit 10.5.67, Ignition 2.9.1; `phpunit.xml` migrado; `QueryException` adaptada a la firma nueva; `composer update` con `--no-security-blocking` porque Laravel 10 arrastra avisos conocidos — pruebas: 82 passed, PHPStan limpio. NO verificado: `docker build --target prod` ni login manual (daemon Docker ausente).

## F7.2 — Planificación Laravel 10 → 11/12 (no ejecutado)

Cambios necesarios para que un humano lo planifique:

- **PHP:** subir a `>= 8.2` (`config.platform.php`, Dockerfile y CI). Laravel 12 exige 8.2+.
- **Estructura:** Laravel 11 introduce `bootstrap/app.php` como único punto de configuración y **elimina** `app/Http/Kernel.php`: migrar `$middleware`, `$middlewareGroups` y `$routeMiddleware` a `->withMiddleware()` y los alias a `->withMiddleware(fn (Middleware $m) => $m->alias([...]))`. Es opcional: la estructura 9/10 sigue funcionando.
- **casts:** adoptar `protected function casts(): array` en los modelos (el `$casts` actual sigue funcionando).
- **Sanctum 4:** `laravel/sanctum:^4.0`; revisar la ruta `/api/user` y el middleware `EnsureFrontendRequestsAreStateful` si se usa SPA.
- **Dev tooling:** `nunomaduro/collision:^8`, `phpunit/phpunit:^11`, `larastan/larastan:^3` (el paquete `nunomaduro/larastan` está abandonado a favor de `larastan/larastan`).
- **Config:** publicar `config/` con `php artisan config:publish` sólo para las claves que se hayan personalizado (app, session, database); el resto se puede borrar.
- **Cron de Artisan:** Laravel 11/12 ejecuta el scheduler programáticamente; revisar si el cron `schedule:run` sigue siendo el mecanismo elegido.
- **Riesgo:** las notificaciones de seguridad (`composer audit`) sobre Laravel 10 sólo desaparecen al subir a una versión con soporte; F7.1 no cierra ese punto.


## MEJORA FUTURA — CSP en modo Report-Only

No se añade `Content-Security-Policy`: el panel carga Bootstrap, DataTables, Chart.js y FontAwesome por CDN y usa scripts inline. El siguiente paso es desplegar una CSP `Content-Security-Policy-Report-Only` con `report-uri`/`report-to`, revisar los informes y sólo entonces pasarla a bloqueante.


## PENDIENTE DECISIÓN — cantidades fraccionarias (F2.4)

`carrito.cantidad` es `integer` y `pedidoproducto.cantidad` es `decimal(10,2)`. Vender por libra con decimales (p. ej. 2.5 lb) requiere decidir:

- **Opción A (mantener enteros):** documentar en el README que la venta por libra sólo admite libras enteras. Coste: se pierde la venta al peso fraccionaria; sin cambios de esquema.
- **Opción B (permitir fracciones):** migración nueva que cambie `carrito.cantidad` a `decimal(10,2)`, validación `numeric|min:0.25|multiple_of:0.25` sólo para productos con unidad de venta `lb`, y ajustes en `CarritoController` y sus vistas. Coste: toca esquema y validación; hay que revisar los cálculos de stock y los tests que asumen enteros.

Sin decisión del negocio no se implementa ninguna de las dos.

