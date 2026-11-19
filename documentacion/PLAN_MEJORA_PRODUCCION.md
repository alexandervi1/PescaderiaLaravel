# Plan de mejora para producción — Pescadería Don Walter

> **Destinatario:** agente de código (DeepSeek V4.1 Flash).
> **Fecha:** 2026-11-19 · **Rama base:** `master` · **Stack:** Laravel 9, PHP 8.2 (Docker), MySQL 8, Caddy + Cloudflare.
> **Estado de partida:** 50 pruebas en verde (`php artisan test`). Dockerización y despliegue escritos pero **sin commitear**.

---

## 0. Reglas de ejecución (leer antes de empezar; aplican a TODO el plan)

1. **Una subfase = un commit.** No mezcles subfases. Mensaje de commit en formato Conventional Commits en español (se indica en cada subfase).
2. **Orden estricto.** Ejecuta las fases en orden (F0 → F7). Dentro de una fase, las subfases en orden. No saltes una subfase porque "parece hecha": verifica sus criterios de aceptación.
3. **Puerta de calidad tras cada subfase** (obligatoria, no avances si falla):
   ```bash
   php artisan test
   ```
   Debe terminar con `0 failed`. El número de pruebas solo puede subir, nunca bajar.
4. **Prohibido:**
   - Editar migraciones existentes en `database/migrations/`. Todo cambio de esquema va en una migración **nueva**.
   - Borrar o debilitar pruebas existentes para que pasen.
   - Cambiar vistas Blade, CSS o textos de UI salvo que la subfase lo pida explícitamente.
   - Añadir dependencias de Composer/npm salvo que la subfase lo pida.
   - Commitear `.env`, `deploy/.env.prod`, `deploy/certs/`, `database/database.sqlite` o `.phpunit.result.cache`.
   - Usar `git push --force`, `git reset --hard` o `--no-verify`.
5. **Convenciones del código existente** (respétalas):
   - Nombres de dominio en español (`Pedido`, `Producto`, `descontarStock`, `obtenerIva`).
   - Tablas en singular sin `s`: `usuario`, `producto`, `pedido`, `pedidoproducto`, `carrito`. PK: `<tabla>_id`.
   - Roles: `rol_id` 1 = Administrador, 2 = Cliente, 3 = Visitante (`Usuario::esAdmin()`, etc.).
   - Lógica de negocio en `app/Services/`; controladores delgados; validación en `app/Http/Requests/`.
   - Las pruebas usan SQLite en memoria (`phpunit.xml`) y `RefreshDatabase`. Toma `tests/Feature/OrderFlowTest.php` como modelo de estilo.
6. **Si algo no cuadra** con lo que describe este plan (archivo inexistente, firma distinta), **detente en esa subfase** y deja una nota en `documentacion/PLAN_BITACORA.md` explicando qué encontraste. No improvises un rediseño.
7. **Bitácora:** al terminar cada subfase añade una línea a `documentacion/PLAN_BITACORA.md`:
   `- [x] F1.2 — <hash corto> — <resumen de una línea> — pruebas: <N> passed`

### Mapa de fases

| Fase | Nombre | Prioridad | Bloquea producción |
|---|---|---|---|
| F0 | Preparación y línea base | Crítica | Sí |
| F1 | Seguridad crítica | Crítica | Sí |
| F2 | Integridad de pedidos e inventario | Crítica | Sí |
| F3 | Endurecimiento de la aplicación | Alta | Sí |
| F4 | Infraestructura y operación | Alta | Sí |
| F5 | Integración continua | Alta | No (recomendada antes) |
| F6 | Rendimiento y escalabilidad | Media | No |
| F7 | Actualización de framework (Laravel 9 → 10) | Alta (Laravel 9 sin soporte) | No (pero planificar) |

---

## F0 — Preparación y línea base

### F0.1 — Rama de trabajo y commit del trabajo Docker pendiente
- **Objetivo:** dejar el árbol limpio antes de cualquier cambio.
- **Pasos:**
  1. `git checkout -b mejora/produccion`
  2. Verifica que `git status` muestra exactamente estos cambios (ningún otro): `.gitattributes`, `.gitignore`, `README.md`, `app/Http/Middleware/TrustProxies.php`, `database/seeders/DatabaseSeeder.php`, y sin seguimiento: `.dockerignore`, `Dockerfile`, `deploy/`, `docker-compose.prod.yml`, `docker-compose.yml`, `docker/`, `documentacion/PLAN_MEJORA_PRODUCCION.md` (este plan).
  3. Confirma que `deploy/.env.prod` y `deploy/certs/` **no** aparecen (están en `.gitignore`). Si aparecen, detente.
  4. `git add` de esos archivos y commit.
- **Commit:** `build(docker): añade imagen, compose local/prod y scripts de despliegue`
- **Aceptación:** `git status` limpio; `php artisan test` → 50 passed.

### F0.2 — Bitácora del plan
- **Pasos:** crea `documentacion/PLAN_BITACORA.md` con el encabezado `# Bitácora de ejecución` y la línea de F0.1.
- **Commit:** `docs: inicia bitácora del plan de mejora`

---

## F1 — Seguridad crítica

### F1.1 — El seeder no debe crear un administrador sin credenciales en producción
- **Problema:** `deploy/.env.prod.example` trae `INITIAL_ADMIN_USER=` vacío. `env('X', 'admin123')` devuelve `''` (no el default) cuando la variable existe vacía, así que el admin se crearía con usuario y contraseña vacíos.
- **Archivos:** `database/seeders/DatabaseSeeder.php`, prueba nueva `tests/Feature/SeederProduccionTest.php`.
- **Pasos:**
  1. En `DatabaseSeeder`, antes del bloque `// ── 5. Usuario Admin ──`, añade un método privado `credencialesAdmin(): array` que:
     - Lee `INITIAL_ADMIN_USER`, `INITIAL_ADMIN_PASSWORD`, `INITIAL_ADMIN_EMAIL` con `env()` y aplica `trim()`.
     - Si `app()->environment('production')`:
       - Lanza `\RuntimeException` con mensaje claro si alguna está vacía.
       - Lanza `\RuntimeException` si la contraseña tiene menos de 12 caracteres o es igual a `admin123`.
       - Lanza `\RuntimeException` si el correo no pasa `filter_var(..., FILTER_VALIDATE_EMAIL)`.
     - Fuera de producción: si alguna está vacía usa los valores actuales por defecto (`admin123`, `admin123`, `maritza@gmail.com`).
  2. Usa ese método al crear el usuario admin. Si es producción, `password_changed_at` = `now()` (la credencial ya es propia, no debe salir la alerta de "contraseña inicial").
     - **Antes de esto**, busca con `grep -rn "password_changed_at" app/` cómo se detecta la "contraseña inicial sin rotar" y confirma que poner `now()` desactiva la alerta. Si la lógica es otra, anótalo en la bitácora y no cambies `password_changed_at`.
  3. Pruebas (3 casos): producción con variables vacías lanza excepción; producción con contraseña `admin123` lanza excepción; entorno `testing` sin variables crea `admin123`. Usa `$this->app['env'] = 'production'` y `putenv()` / limpieza en `tearDown`.
- **Commit:** `fix(seed): exige credenciales de admin válidas al sembrar en producción`
- **Aceptación:** pruebas nuevas en verde; `php artisan migrate:fresh --seed` en local sigue creando `admin123`.

### F1.2 — Separar datos de demostración de los datos base
- **Problema:** en producción se siembran 18 productos de ejemplo y datos personales reales (nombre, dirección y teléfono de "Maritza Rosero").
- **Archivos:** `database/seeders/DatabaseSeeder.php`, nuevo `database/seeders/DemoCatalogoSeeder.php`, `docker/entrypoint.sh`, `deploy/.env.prod.example`, `docker-compose.yml`, `README.md`.
- **Pasos:**
  1. Mueve el bloque `// ── 6. Productos ──` completo a `DemoCatalogoSeeder::run()` (sin cambios en los datos).
  2. En `DatabaseSeeder`, al final: `if (filter_var(env('SEED_DEMO', ! app()->environment('production')), FILTER_VALIDATE_BOOLEAN)) { $this->call(DemoCatalogoSeeder::class); }`.
  3. En el admin creado en producción: `nombre_completo` = `env('INITIAL_ADMIN_NAME', 'Administrador')`, `direccion` y `telefono` = `null` **si las columnas lo permiten** (revisa `2024_01_01_000004_create_usuarios_table.php`; si no son nullable, usa cadena vacía `''`). Fuera de producción conserva los datos actuales.
  4. Añade `SEED_DEMO=false` e `INITIAL_ADMIN_NAME=` a `deploy/.env.prod.example`; añade `SEED_DEMO: "${SEED_DEMO:-true}"` al servicio `app` de `docker-compose.yml`.
  5. Documenta `SEED_DEMO` en la sección Docker del `README.md` (una viñeta).
- **Commit:** `refactor(seed): separa el catálogo demo y omite datos personales en producción`
- **Aceptación:** `migrate:fresh --seed` local → 18 productos. Con `SEED_DEMO=false` → 0 productos, roles/unidades/categorías/configuración presentes. Pruebas en verde (los tests que dependan del catálogo sembrado deben seguir pasando; si alguno falla, llama a `DemoCatalogoSeeder` en ese test, no cambies sus aserciones).

### F1.3 — Origen accesible solo desde Cloudflare
- **Problema:** el puerto 443 está abierto a `0.0.0.0/0` y Caddy copia `CF-Connecting-IP` a `X-Forwarded-For` sin validar el origen. Un atacante que llame directo a la IP del VPS puede falsificar su IP y evadir el *throttle* de login (`AuthController::login`, clave `login|usuario|ip`).
- **Archivos:** `deploy/Caddyfile`, nuevo `deploy/cloudflare-ips.caddy`, nuevo `deploy/update-cloudflare-ips.sh`, `deploy/deploy.sh`, `docker-compose.prod.yml`, `deploy/README.md`.
- **Pasos:**
  1. Crea `deploy/update-cloudflare-ips.sh` (bash, `set -euo pipefail`) que descarga `https://www.cloudflare.com/ips-v4` y `https://www.cloudflare.com/ips-v6`, valida que cada línea tenga forma de CIDR (si alguna no, aborta sin tocar el archivo) y escribe `deploy/cloudflare-ips.caddy` con este formato exacto:
     ```
     # Generado por deploy/update-cloudflare-ips.sh — no editar a mano.
     (solo_cloudflare) {
     	@fuera_de_cloudflare not remote_ip <cidr1> <cidr2> ...
     	abort @fuera_de_cloudflare
     }
     ```
     Escribe a un archivo temporal y haz `mv` atómico al final.
  2. Ejecuta el script una vez y commitea el `cloudflare-ips.caddy` generado (sirve de valor inicial).
  3. En `deploy/Caddyfile`: al inicio `import /etc/caddy/cloudflare-ips.caddy`; dentro del bloque del sitio, primera línea `import solo_cloudflare`. Cambia `header_up X-Forwarded-For {http.request.header.CF-Connecting-IP}` para que solo se aplique cuando la cabecera exista (usa un matcher `@con_cf header CF-Connecting-IP *` y un `reverse_proxy` con `header_up` dentro de `handle @con_cf`, o deja la línea tal cual: tras el filtro por IP, toda petición viene de Cloudflare y siempre trae la cabecera — **elige esta segunda opción, es más simple** y añade un comentario explicándolo).
  4. En `docker-compose.prod.yml`, servicio `caddy`, monta `./deploy/cloudflare-ips.caddy:/etc/caddy/cloudflare-ips.caddy:ro`.
  5. En `deploy/deploy.sh`, antes de `build`, llama a `./deploy/update-cloudflare-ips.sh || echo "aviso: no se pudieron actualizar las IPs de Cloudflare; se usa la lista existente"`.
  6. En `deploy/README.md`, sección de firewall: indica que Caddy rechaza conexiones que no vienen de Cloudflare y recomienda, como defensa adicional, restringir la Security List de Oracle a los rangos de Cloudflare. Añade una fila en la tabla de problemas: "403/conexión cortada al probar directo a la IP → esperado".
  7. Valida la sintaxis: `docker run --rm -v "$PWD/deploy:/etc/caddy" -e APP_DOMAIN=ejemplo.com caddy:2-alpine caddy validate --config /etc/caddy/Caddyfile --adapter caddyfile`. Si Docker no está disponible, anótalo en la bitácora (**no** marques la subfase como validada).
- **Commit:** `fix(deploy): acepta tráfico de origen solo desde Cloudflare`
- **Aceptación:** `caddy validate` sin errores; script idempotente; `.sh` con finales de línea LF.

### F1.4 — Proxies de confianza configurables
- **Problema:** `TrustProxies::$proxies = '*'` es correcto en producción (la app no publica puertos) pero en `docker-compose.yml` local la app expone el 8080 y cualquiera en la red puede falsificar `X-Forwarded-*`.
- **Archivos:** `app/Http/Middleware/TrustProxies.php`, `docker-compose.prod.yml`, `.env.example`.
- **Pasos:**
  1. Sustituye la propiedad por un constructor o por el método `proxies()` que lea `env('TRUSTED_PROXIES')`: si es `*` → `'*'`; si es lista separada por comas → array; si está vacía → `null` (no confiar en nadie).
     - **Importante:** con `config:cache`, `env()` fuera de `config/` devuelve `null`. Por eso: crea la clave `'trusted_proxies' => env('TRUSTED_PROXIES')` en `config/app.php` y lee `config('app.trusted_proxies')` en el middleware.
  2. En `docker-compose.prod.yml` servicio `app`: `TRUSTED_PROXIES: "*"`. En `.env.example`: `TRUSTED_PROXIES=` (vacío).
  3. Prueba: con `config(['app.trusted_proxies' => null])` una petición con `X-Forwarded-For: 1.2.3.4` mantiene `request()->ip()` = `127.0.0.1`; con `'*'` devuelve `1.2.3.4`. (Define una ruta de prueba dentro del test con `Route::get(...)`.)
- **Commit:** `fix(http): proxies de confianza configurables por entorno`

### F1.5 — No exponer mensajes internos al cliente en el checkout
- **Problema:** `PedidoController::process` captura `\Exception` y muestra `$e->getMessage()`; un `QueryException` filtraría SQL al cliente.
- **Archivo:** `app/Http/Controllers/PedidoController.php`, prueba en `tests/Feature/OrderFlowTest.php`.
- **Pasos:**
  1. Captura primero `\RuntimeException` (mensajes de negocio pensados para el cliente) y devuelve `back()->with('error', $e->getMessage())`.
  2. Luego captura `\Throwable`: `report($e);` y `back()->with('error', 'No pudimos procesar tu pedido. Inténtalo de nuevo en unos minutos.')`.
  3. Prueba: haz que `OrderService` lance `\Illuminate\Database\QueryException` (vía `$this->mock(OrderService::class)`) y verifica que la sesión **no** contiene el texto SQL y sí el mensaje genérico.
- **Commit:** `fix(pedidos): oculta errores internos en el checkout`

---

## F2 — Integridad de pedidos e inventario

### F2.1 — Máquina de estados explícita del pedido
- **Problema:** `Admin/PedidoController::update` permite cualquier transición salvo reactivar un cancelado; p. ej. `Entregado → Cancelado` repone stock de mercadería ya entregada, y `Entregado → Pendiente` es posible.
- **Archivos:** `app/Models/Pedido.php`, `app/Http/Controllers/Admin/PedidoController.php`.
- **Pasos:**
  1. En `Pedido` añade constantes `ESTADO_PENDIENTE = 'Pendiente'`, `ESTADO_ENVIADO = 'Enviado'`, `ESTADO_ENTREGADO = 'Entregado'`, `ESTADO_CANCELADO = 'Cancelado'`, y `public const ESTADOS = [...]` con los cuatro.
  2. Añade `public const TRANSICIONES` con exactamente:
     - `Pendiente` → `Enviado`, `Cancelado`
     - `Enviado` → `Entregado`, `Cancelado`
     - `Entregado` → *(ninguna)*
     - `Cancelado` → *(ninguna)*
  3. Añade `public function puedeCambiarA(string $estado): bool` (devuelve `true` si `$estado === $this->estado`, para que guardar sin cambios no falle).
  4. En `Admin/PedidoController`: reemplaza la lista literal de estados en `index()` y en la regla de validación de `update()` por `Pedido::ESTADOS` (`Rule::in(Pedido::ESTADOS)`). Si `! $pedido->puedeCambiarA($nuevo)`, `back()->with('error', "No se puede pasar un pedido de {$pedido->estado} a {$nuevo}.")`.
  5. Revisa la vista `resources/views/admin/pedidos/show.blade.php`: si el `<select>` de estado lista los cuatro estados, **solo** limita las opciones a `[$pedido->estado, ...Pedido::TRANSICIONES[$pedido->estado]]` y deshabilita el formulario si no hay transiciones. No cambies estilos.
- **Commit:** `feat(pedidos): define transiciones de estado válidas`

### F2.2 — Cancelación transaccional con bloqueo, dentro de `OrderService`
- **Problema:** la cancelación repone stock sin `lockForUpdate`; un checkout simultáneo puede pisar el valor. Además la lógica vive en el controlador.
- **Archivos:** `app/Services/OrderService.php`, `app/Http/Controllers/Admin/PedidoController.php`.
- **Pasos:**
  1. Añade `public function cancelOrder(Pedido $pedido): Pedido` en `OrderService`:
     - `DB::transaction`: recarga el pedido con `Pedido::whereKey($pedido->pedido_id)->lockForUpdate()->firstOrFail()`.
     - Si ya está cancelado o `! puedeCambiarA(Cancelado)` → `RuntimeException` con mensaje para el usuario.
     - Lee las líneas de `pedidoproducto` del pedido; bloquea los productos con `Producto::whereIn(...)->lockForUpdate()->get()->keyBy('producto_id')` (mismo patrón que `processOrder`).
     - Por cada línea `reponerStock((float) $linea->cantidad)`. Si el producto ya no existe, continúa (no falla).
     - Cambia estado a `Cancelado`, guarda, devuelve `$pedido->fresh()`.
  2. En el controlador, la rama de cancelación llama a `$orderService->cancelOrder($pedido)` dentro de `try/catch (RuntimeException)`.
  3. Añade un método similar `changeStatus(Pedido $pedido, string $estado): Pedido` para las demás transiciones (valida con `puedeCambiarA`) y haz que el controlador lo use. El controlador ya no debe contener `DB::transaction`.
- **Commit:** `refactor(pedidos): centraliza cancelación y cambios de estado en OrderService`

### F2.3 — Pruebas de la máquina de estados y la cancelación
- **Archivo nuevo:** `tests/Feature/OrderStatusTest.php`.
- **Casos mínimos (uno por test):**
  1. `Pendiente → Cancelado` repone el stock exacto (producto en libras con factor 2204.62 y producto en unidades).
  2. `Enviado → Cancelado` repone el stock.
  3. `Entregado → Cancelado` es rechazado y el stock no cambia.
  4. `Cancelado → Pendiente` es rechazado.
  5. `Entregado → Pendiente` es rechazado.
  6. Cancelar dos veces no repone dos veces.
  7. Cancelar un pedido cuyo producto fue borrado no lanza error. (Si borrar un producto con pedidos está bloqueado por FK/regla, salta este caso con `markTestSkipped` y explica por qué.)
  8. Un cliente (rol 2) que hace `PUT admin/pedidos/{id}` recibe redirección a login y el estado no cambia.
- **Commit:** `test(pedidos): cubre transiciones de estado y reposición de stock`

### F2.4 — Coherencia de tipos de cantidad (carrito entero vs. pedido decimal)
- **Contexto:** `carrito.cantidad` es `integer`, `pedidoproducto.cantidad` es `decimal(10,2)`. Vender por libra con decimales (p. ej. 2.5 lb) es una **decisión de negocio**.
- **Acción del agente:** **no implementes cambios.** Añade en la bitácora una entrada `PENDIENTE DECISIÓN` describiendo: opción A (mantener enteros, documentarlo en el README) u opción B (migración nueva que cambie `carrito.cantidad` a `decimal(10,2)`, validación `numeric|min:0.25|multiple_of:0.25` solo para productos con unidad de venta `lb`, y ajustes en `CarritoController` y vistas). Sigue con la siguiente subfase.
- **Commit:** `docs(plan): registra decisión pendiente sobre cantidades fraccionarias`

---

## F3 — Endurecimiento de la aplicación

### F3.1 — Endpoint de salud
- **Problema:** el healthcheck de producción pide `/`, que renderiza el catálogo completo y crea sesión en cada chequeo.
- **Archivos:** `routes/web.php` (o `routes/api.php`), nuevo `app/Http/Controllers/SaludController.php`, `docker-compose.prod.yml`.
- **Pasos:**
  1. Controlador invocable que ejecuta `DB::select('select 1')` y responde `response()->json(['estado' => 'ok'], 200)`; ante excepción, `report($e)` y `['estado' => 'error']` con 503. No incluyas detalles del error en la respuesta.
  2. Ruta `GET /salud` en `routes/api.php` (sin sesión ni CSRF). Ojo: el prefijo de `api.php` es `/api` → la URL final es `/api/salud`. Usa esa.
  3. En `docker-compose.prod.yml` cambia el healthcheck a `http://127.0.0.1/api/salud`.
  4. Prueba: `GET /api/salud` → 200 y JSON `{"estado":"ok"}`.
- **Commit:** `feat(ops): añade endpoint de salud para healthchecks`

### F3.2 — Configuración segura de sesión y cookies en producción
- **Archivos:** `deploy/.env.prod.example`, `config/session.php` (solo si hace falta).
- **Pasos:**
  1. Verifica en `config/session.php` que `secure`, `http_only` y `same_site` lean de entorno o tengan valores seguros (`http_only` true, `same_site` `lax`).
  2. Añade a `.env.prod.example`: `SESSION_LIFETIME=120`, `SESSION_ENCRYPT=true` (comprueba que la clave `encrypt` exista en `config/session.php` y lea `SESSION_ENCRYPT`; si está fija en `false`, cámbiala a `env('SESSION_ENCRYPT', false)`), `SESSION_SAME_SITE=lax` (misma comprobación).
- **Commit:** `chore(config): endurece la sesión en producción`

### F3.3 — Cabeceras de seguridad en la app (independientes del proxy)
- **Archivos:** nuevo `app/Http/Middleware/CabecerasSeguridad.php`, `app/Http/Kernel.php`.
- **Pasos:**
  1. Middleware que añade a cada respuesta: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(), microphone=(), geolocation=()`.
  2. **No añadas** `Content-Security-Policy` en esta subfase (el proyecto carga Bootstrap, Chart.js, DataTables y FontAwesome por CDN y scripts inline; una CSP mal hecha rompe el panel). Deja en la bitácora una entrada `MEJORA FUTURA: CSP en modo Report-Only`.
  3. Regístralo en el grupo `web` de `Kernel.php`.
  4. Prueba: `GET /` devuelve las cuatro cabeceras.
- **Commit:** `feat(http): añade cabeceras de seguridad desde la aplicación`

### F3.4 — Throttle adicional por IP y en registro
- **Problema:** el límite de login es por usuario+IP; se pueden probar muchos usuarios distintos desde una IP. El registro no tiene límite.
- **Archivos:** `routes/web.php`, `app/Providers/RouteServiceProvider.php`.
- **Pasos:**
  1. En `RouteServiceProvider::configureRateLimiting()` define `RateLimiter::for('login-ip', fn ($r) => Limit::perMinute(20)->by($r->ip()))` y `RateLimiter::for('registro', fn ($r) => Limit::perHour(10)->by($r->ip()))`.
  2. Aplica `->middleware('throttle:login-ip')` a `POST /login` y `->middleware('throttle:registro')` a `POST /registro`. Mantén el limitador existente en `AuthController`.
  3. Pruebas: 21 POST a `/login` con usuarios distintos desde la misma IP → el 21.º responde 429. 11 registros → el 11.º responde 429.
- **Commit:** `feat(auth): limita intentos de login por IP y registros por hora`

### F3.5 — Validación de subidas de imagen
- **Archivos:** `app/Http/Requests/Admin/StoreProductoRequest.php`, `UpdateProductoRequest.php`, `app/Http/Controllers/Admin/ProductoController.php` (`guardarImagen`), `ConfiguracionController.php` (`guardarLogo`).
- **Pasos:**
  1. Verifica que la regla de `imagen` sea `nullable|image|mimes:jpg,jpeg,png,webp|max:2048` y añade `dimensions:max_width=4000,max_height=4000`. Mismo criterio para `negocio_logo`.
  2. Verifica que `guardarImagen`/`guardarLogo` generan el nombre con `Str::uuid()` (o `hashName()`) y **nunca** usan `getClientOriginalName()`. Si lo usan, cámbialo.
  3. Prueba: subir un `.php` renombrado o un `UploadedFile::fake()->create('x.php', 10, 'application/x-php')` es rechazado; subir `UploadedFile::fake()->image('a.jpg')` funciona.
- **Commit:** `fix(admin): refuerza validación y nombres de imágenes subidas`

### F3.6 — Auditoría de autorización de rutas
- **Archivo nuevo:** `tests/Feature/AutorizacionRutasTest.php`.
- **Pasos:** recorre `Route::getRoutes()` y, para cada ruta con nombre que empiece por `admin.`, comprueba que su middleware incluye `auth` y `admin`. Para `carrito.*` y `pedidos.*`, que incluye `auth` y `cliente`. Además, prueba que un cliente no puede ver `/mis-pedidos/{id}` de otro cliente (404).
- **Commit:** `test(auth): verifica middleware de todas las rutas protegidas`

---

## F4 — Infraestructura y operación

### F4.1 — Límites, privilegios y logs de contenedores
- **Archivos:** `docker-compose.prod.yml`.
- **Pasos:**
  1. Añade `deploy.resources.limits` razonables: `app` 512M de memoria, `db` 1G, `caddy` 128M. (En compose v2 sin swarm usa `mem_limit`.)
  2. Añade `logging: { driver: json-file, options: { max-size: "10m", max-file: "5" } }` a los tres servicios (evita llenar el disco del VPS).
  3. Añade `security_opt: ["no-new-privileges:true"]` a `app` y `caddy`.
  4. Verifica con `docker compose -f docker-compose.prod.yml --env-file deploy/.env.prod.example config` (debe fallar solo por variables `:?` vacías; si falla por otra cosa, corrígelo).
- **Commit:** `chore(deploy): limita recursos y rota logs de contenedores`

### F4.2 — Migraciones seguras en despliegue
- **Problema:** el entrypoint ejecuta `migrate --force` en cada arranque sin respaldo previo.
- **Archivos:** `deploy/deploy.sh`.
- **Pasos:** antes de `build`, si el contenedor `db` está corriendo, ejecuta `./deploy/backup.sh` y aborta el despliegue si falla. Imprime la ruta del respaldo para el rollback.
- **Commit:** `chore(deploy): respalda la base antes de cada despliegue`

### F4.3 — Script de restauración y prueba de respaldo
- **Archivos:** nuevo `deploy/restore.sh`, `deploy/README.md`.
- **Pasos:**
  1. `restore.sh <archivo.sql.gz> [uploads.tar.gz]`: pide confirmación escribiendo `RESTAURAR` (lee de `/dev/tty`), detiene `app`, restaura con `gunzip -c | docker compose ... exec -T db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -u root "$MYSQL_DATABASE"'`, extrae imágenes si se pasa el tar, y arranca `app`.
  2. En `backup.sh`, tras generar el `.sql.gz`, verifica con `gzip -t` y que el tamaño sea > 1 KB; si no, sale con código 1.
  3. Documenta en `deploy/README.md` una sección "Restaurar" y un simulacro mensual recomendado.
- **Commit:** `feat(deploy): script de restauración y verificación de respaldos`

### F4.4 — Copias fuera del servidor
- **Acción del agente:** documentar (no implementar credenciales) en `deploy/README.md` cómo enviar `/var/backups/pescaderia` a almacenamiento externo (Oracle Object Storage o rclone) con un ejemplo de cron. Añade la variable opcional `BACKUP_REMOTE` en `backup.sh`: si está definida y `rclone` existe, ejecuta `rclone copy "$DEST" "$BACKUP_REMOTE" --max-age 25h`.
- **Commit:** `docs(deploy): copia remota opcional de respaldos`

### F4.5 — Tareas programadas y limpieza
- **Archivos:** `app/Console/Kernel.php`, `deploy/README.md`.
- **Pasos:**
  1. Programa en `schedule()`: limpieza de carritos con más de 30 días sin actualizar **solo si** la tabla `carrito` tiene `updated_at` (revisa la migración; si no la tiene, no lo implementes y anótalo en la bitácora).
  2. Documenta cómo ejecutar el scheduler en el VPS: cron del host `* * * * * docker compose -f /opt/pescaderia/docker-compose.prod.yml --env-file /opt/pescaderia/deploy/.env.prod exec -T app php artisan schedule:run >/dev/null 2>&1`.
- **Commit:** `feat(ops): programa limpieza periódica`

---

## F5 — Integración continua

### F5.1 — Workflow de pruebas
- **Archivo nuevo:** `.github/workflows/ci.yml`.
- **Contenido requerido:**
  - Disparadores: `push` a `master` y `pull_request`.
  - Job `pruebas`: `ubuntu-latest`, `shivammathur/setup-php@v2` con PHP `8.2` y extensiones `pdo_sqlite, mbstring, fileinfo`; cache de `vendor` por hash de `composer.lock`; `composer install --no-interaction --prefer-dist`; `cp .env.example .env && php artisan key:generate`; `php artisan test`.
  - Job `auditoria`: `composer audit --no-dev` (no bloqueante al principio: `continue-on-error: true`).
  - Job `docker`: `docker build --target prod -t pescaderia:ci .` (solo construye, no publica).
  - Job `shell`: `shellcheck docker/entrypoint.sh deploy/*.sh`.
- **Commit:** `ci: añade pruebas, auditoría, build Docker y shellcheck`
- **Aceptación:** corrige cualquier aviso de `shellcheck` en los scripts en esta misma subfase.

### F5.2 — Análisis estático
- **Pasos:** añade `nunomaduro/larastan` en `require-dev` (versión compatible con Laravel 9, `^2.0`), `phpstan.neon` con `level: 5` sobre `app/`, y genera la línea base con `--generate-baseline`. Añade el paso al job `pruebas`.
- **Commit:** `ci: análisis estático con Larastan nivel 5 y línea base`

---

## F6 — Rendimiento y escalabilidad

### F6.1 — Paginación en listados del panel
- **Problema:** `Admin/PedidoController::index` (y probablemente clientes, productos, carritos) cargan todo con `->get()`.
- **Pasos:**
  1. Revisa cada `index()` en `app/Http/Controllers/Admin/`. **Si la vista usa DataTables** (paginación en el cliente), no la cambies todavía: anota en la bitácora cuántas filas tolera y sigue. Solo pagina con `->paginate(25)->withQueryString()` los listados **sin** DataTables, añadiendo `{{ $x->links() }}` en la vista.
  2. Para pedidos (crece sin límite) aplica un límite temporal: filtro de rango de fechas por defecto de los últimos 90 días, con parámetro `?desde=` para ampliar.
- **Commit:** `perf(admin): pagina listados sin DataTables y acota pedidos por fecha`

### F6.2 — Índices de base de datos
- **Pasos:** migración **nueva** que añade índices (si no existen): `pedido(usuario_id, fecha_pedido)`, `pedido(estado)`, `carrito(usuario_id)`, `producto(categoria_id)`. Laravel 9 no tiene `Schema::hasIndex`: consulta los índices existentes en cada migración original y solo añade los que falten. Implementa `down()`.
- **Commit:** `perf(db): añade índices para consultas frecuentes`

### F6.3 — N+1 y caché de producción
- **Pasos:**
  1. En `AppServiceProvider::boot()`, `Model::preventLazyLoading(! app()->isProduction());`. Ejecuta la suite: corrige con `with()` cada lazy loading que aparezca (no desactives la protección).
  2. Verifica que el entrypoint en producción ejecuta `config:cache`, `route:cache`, `view:cache` (ya lo hace) y añade `event:cache`.
- **Commit:** `perf: previene lazy loading y elimina consultas N+1`

---

## F7 — Actualización de framework

> Laravel 9 dejó de recibir parches de seguridad en febrero de 2024. Esta fase es obligatoria a medio plazo. Hazla **solo** con F0–F5 terminadas y la CI en verde.

### F7.1 — Laravel 9 → 10
- **Pasos:**
  1. Rama nueva `mejora/laravel-10` desde `mejora/produccion`.
  2. Sigue la guía oficial de actualización 9→10: `laravel/framework:^10.0`, `laravel/sanctum:^3.2`, `nunomaduro/collision:^7.0`, `phpunit/phpunit:^10.0`, `spatie/laravel-ignition:^2.0`, `"php": "^8.1"`, `"minimum-stability": "stable"`. Ajusta `larastan` a `^2.6`.
  3. `composer update` y corrige roturas: `$dates` en modelos → `$casts`; `Bus::dispatchNow` → `dispatchSync`; `phpunit.xml` migrado con `vendor/bin/phpunit --migrate-configuration`.
  4. Puerta: `php artisan test` en verde + `docker build --target prod` + arranque con `docker compose up` y login manual de admin y cliente.
- **Commit(s):** `build: actualiza a Laravel 10` (+ commits `fix:` por cada rotura).

### F7.2 — Laravel 10 → 11/12 (planificación)
- **Acción del agente:** no ejecutar. Redacta en la bitácora la lista de cambios necesarios (estructura simplificada opcional, PHP ≥ 8.2, Sanctum 4, `casts()` como método) para que un humano decida cuándo hacerlo.

---

## Checklist final antes del primer despliegue a producción

- [ ] F0–F4 completas y en la bitácora.
- [ ] CI (F5.1) en verde en el último commit.
- [ ] `deploy/.env.prod` completo: `APP_KEY` (`php artisan key:generate --show`), contraseñas MySQL largas y aleatorias, `INITIAL_ADMIN_*` reales, `SEED_DEMO=false`, `APP_DEBUG=false`.
- [ ] Certificado de origen de Cloudflare en `deploy/certs/` y modo SSL **Full (strict)**.
- [ ] Prueba manual: entrar directo a la IP del VPS por 443 → la conexión se rechaza.
- [ ] Respaldo inicial hecho y restauración ensayada en un entorno descartable (F4.3).
- [ ] Cron de `backup.sh` y de `schedule:run` instalados.
- [ ] Decisión tomada sobre cantidades fraccionarias (F2.4).
