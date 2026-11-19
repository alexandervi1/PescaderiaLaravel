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

## MEJORA FUTURA — CSP en modo Report-Only

No se añade `Content-Security-Policy`: el panel carga Bootstrap, DataTables, Chart.js y FontAwesome por CDN y usa scripts inline. El siguiente paso es desplegar una CSP `Content-Security-Policy-Report-Only` con `report-uri`/`report-to`, revisar los informes y sólo entonces pasarla a bloqueante.


## PENDIENTE DECISIÓN — cantidades fraccionarias (F2.4)

`carrito.cantidad` es `integer` y `pedidoproducto.cantidad` es `decimal(10,2)`. Vender por libra con decimales (p. ej. 2.5 lb) requiere decidir:

- **Opción A (mantener enteros):** documentar en el README que la venta por libra sólo admite libras enteras. Coste: se pierde la venta al peso fraccionaria; sin cambios de esquema.
- **Opción B (permitir fracciones):** migración nueva que cambie `carrito.cantidad` a `decimal(10,2)`, validación `numeric|min:0.25|multiple_of:0.25` sólo para productos con unidad de venta `lb`, y ajustes en `CarritoController` y sus vistas. Coste: toca esquema y validación; hay que revisar los cálculos de stock y los tests que asumen enteros.

Sin decisión del negocio no se implementa ninguna de las dos.

