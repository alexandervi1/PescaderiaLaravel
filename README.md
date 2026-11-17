# 🐟 Pescadería Don Walter — Plataforma E-commerce y Gestión

![Laravel](https://img.shields.io/badge/Laravel-9.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

Plataforma integral en **Laravel 9** para la venta y gestión de productos del mar. Tiene una fachada pública para clientes y un panel de administración que controla catálogo, inventario, pedidos, clientes y configuración del negocio.

---

## ✨ Características

* **🏢 Dashboard analítico:** indicadores de productos, clientes, pedidos e ingresos, gráfico de ventas de los últimos 6 meses (Chart.js), alertas de bajo stock y pedidos recientes.
* **📦 Inventario con unidades de medida:** cada producto define unidad de compra y de venta (Tonelada, Libra, Unidad) con factor de conversión validado; el stock se descuenta en la unidad correcta.
* **🏷️ Catálogo:** CRUD de productos y categorías. Al eliminar una categoría sus productos quedan "Sin categoría"; un producto con pedidos no se puede borrar.
* **🛒 E-commerce:** catálogo con filtros y búsqueda, ficha de producto en 3 columnas (galería, ficha, panel de compra) y carrito con validación de stock en servidor.
* **🧾 Pedidos y comprobantes:** checkout transaccional con precio congelado por línea, IVA configurable, serie de comprobante y recibo imprimible con los datos del negocio.
* **👥 Clientes y administradores:** alta y edición de clientes; gestión de cuentas de administrador con protección del último admin y de la propia cuenta.
* **🛍️ Carritos activos:** seguimiento de carritos sin finalizar con cliente, producto, cantidad y valor estimado.
* **⚙️ Configuración del negocio:** razón social, RUC, dirección, teléfono, leyenda, logo, IVA, umbral de bajo stock, serie de comprobante, rotación de contraseña y zona horaria.
* **🛡️ Seguridad:** alerta crítica mientras la contraseña inicial no se rote, aviso de caducidad configurable, contraseña actual obligatoria para cambiar credenciales, correo único y bitácora de cambios de configuración.

---

## 🧩 Módulos del panel

| Sección | Módulos |
|---|---|
| — | Dashboard |
| Catálogo | Productos · Categorías · Unidades de medida |
| Ventas | Pedidos (filtro por estado) · Clientes · Carritos |
| Análisis | Reportes: ventas, más vendidos, bajo stock, stock por categoría |
| Sistema | Configuración (negocio / seguridad / historial) · Administradores |

---

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8, Laravel 9
* **Frontend:** Blade, Bootstrap 5.3, CSS propio con tokens de diseño
* **Base de datos:** MySQL (SQLite para desarrollo local y pruebas)
* **Librerías:** Chart.js, DataTables, FontAwesome (vía CDN)

---

## 🚀 Requisitos e Instalación

Necesitas **PHP >= 8.0**, **Composer** y **MySQL** (o Laragon / XAMPP).

**1. Clonar el repositorio**
```bash
git clone https://github.com/alexandervi1/PescaderiaLaravel.git
cd PescaderiaLaravel
```

**2. Instalar dependencias**
```bash
composer install
```

**3. Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```
Ajusta `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`. Para SQLite local: `DB_CONNECTION=sqlite` y `DB_DATABASE=/ruta/absoluta/database/database.sqlite`.

**4. Migrar y poblar**
```bash
php artisan migrate:fresh --seed
```
Crea la estructura, el administrador inicial, las unidades de medida, las categorías y el catálogo de ejemplo.

**5. Iniciar el servidor**
```bash
php artisan serve
```
Disponible en `http://localhost:8000`.

---

## 🔐 Credenciales base (solo pruebas)

* **Rol:** Administrador
* **Usuario:** `admin123` (o el correo `maritza@gmail.com`)
* **Contraseña:** `admin123`

> [!WARNING]
> Al entrar con estas credenciales aparece una **alerta crítica permanente** hasta que cambies usuario o contraseña en **Configuración → Seguridad**. El cambio exige la contraseña actual y cierra la sesión. El aviso de caducidad usa el plazo configurado (90 días por defecto).

---

## 🧪 Pruebas

La suite corre sobre SQLite en memoria (configurado en `phpunit.xml`), sin tocar tu base de datos de desarrollo:

```bash
php artisan test
```

Cubre flujo de pedidos (totales, IVA, stock, rollback), seguridad y throttling de login, gestión de administradores, unidades, carritos, filtros de pedidos, configuración, bitácora, backfill de IVA y un smoke test de las pantallas públicas y de administración.

---

## 🎨 Sistema visual

* Un único stylesheet (`public/css/style.css`) con **tokens**: primitivos de marca, semánticos (`--color-primary`, `--color-surface`, `--color-text-muted`, estados) y puente a Bootstrap (`--bs-primary-rgb`, `--bs-info`).
* Sin colores hex en las vistas: se usan clases (`text-brand`, `bg-brand`, `btn-brand-dark`, `stat-card-*`) o variables.
* Contraste verificado en las 22 pantallas del panel (WCAG AA) y responsive comprobado de 320 a 1920 px, incluido iPad Pro 13" en ambas orientaciones.
* Componentes consistentes: tarjetas con cabecera y contador, tablas con scroll propio, badges de estado con fondo tenue, objetivos táctiles de 44 px en tablet.

---

## 📂 Arquitectura

* **MVC** con controladores delgados y lógica de negocio en servicios: `OrderService` (checkout transaccional con bloqueo de stock) y `PricingService` (única autoridad de subtotal, IVA y total).
* **Form Requests** para validación (`StoreProductoRequest`, `UpdateAdministradorRequest`, `LoginRequest`, `RegisterRequest`…).
* **View Composer** (`AdminLayoutComposer`) para los contadores del menú y el aviso de rotación; datos del negocio compartidos por composer.
* **Configuración cacheada** (`Configuracion::valores()`), invalidada por eventos del modelo y memoizada por petición.
* **Migraciones aditivas** para cambios de esquema (`iva_pct`, `configuracion_log`).
* Modelos con reglas de dominio: `Producto::descontarStock/reponerStock`, `scopeBajoStock`, `factorConversion`.

---

## 📄 Documentación

* Especificación de requisitos: [`documentacion/SRS_PescaderiaLaravel.tex`](documentacion/SRS_PescaderiaLaravel.tex) (compilable con `pdflatex`; hay una versión en PDF junto al `.tex`).
* Contratos y esquemas en `contracts/` y `schemas/` pertenecen a otro producto y no aplican a esta aplicación.

---

## 📝 Alcance

Implementado: catálogo, carrito, pedidos con comprobante, reportes, configuración, unidades, clientes, administradores y carritos activos.

No implementado: pasarela de pago, facturación electrónica SRI, notificaciones por correo y CRUD sin recarga de página.
