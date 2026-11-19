# Bitácora de ejecución

- [x] F0.1 — b3fda22 — commit del trabajo Docker pendiente (rama `mejora/produccion`) — pruebas: 50 passed
- [x] F1.1 — f476a31 — valida credenciales del admin inicial en producción — pruebas: 53 passed
- [x] F1.2 — 833198a — separa catálogo demo (SEED_DEMO) y omite datos personales en producción — pruebas: 56 passed
- [x] F1.3 — 9377d28 — Caddy rechaza tráfico que no venga de Cloudflare; lista regenerable con `deploy/update-cloudflare-ips.sh` — pruebas: 56 passed. `caddy validate` NO ejecutado: el daemon de Docker no está disponible en esta máquina.
- [x] F1.4 — 0eb79d9 — proxies de confianza desde `TRUSTED_PROXIES` vía `config('app.trusted_proxies')` — pruebas: 58 passed
- [x] F1.5 — 045c206 — checkout no filtra SQL; `QueryException` hereda de `RuntimeException`, así que se captura `PDOException` antes (desvío del plan, necesario para cumplir su propia prueba) — pruebas: 59 passed
