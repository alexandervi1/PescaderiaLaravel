# 🐟 Pescadería Don Walter - Plataforma E-commerce y Gestión

![Laravel](https://img.shields.io/badge/Laravel-9.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

Plataforma integral desarrollada en **Laravel 9** para la automatización, gestión y venta electrónica de productos del mar. El sistema cuenta con una fachada pública para clientes y un **potente panel de administración CRM** que controla el inventario, atiende carritos de compra y calcula rendimientos financieros mediante analítica avanzada.

---

## ✨ Características Principales

* **🏢 Dashboard Analítico Avanzado:** Panel de control de administrador con gráficos interactivos dinámicos y reportes financieros (Chart.js), alertas de bajo stock, y cola de despachos en tiempo real.
* **📦 Gestión Inteligente de Inventario y Productos:** Módulo de productos con múltiples unidades de medida de entrada/salida (Ej: Compras por Tonelada, Ventas por Libra/Unidad) con cálculo automatizado gracias a factores de conversión.
* **🏷️ Clasificación Dinámica (Categorías CRUD):** Administrador de categorías en tiempo real. Previene pérdida de datos al momento de eliminar clasificaciones, manteniendo el inventario a salvo ("Sin Categoría") y replicando cambios instantáneamente en la interfaz de clientes.
* **🛒 E-commerce Integrado (Estilo Amazon):** Explorador de catálogo con una **Página de Detalles del Producto (PDP)** inmersiva de 3 columnas, galería visual en alta resolución, y un **Panel de Compra (Buy Box)** con control de stock métrico exacto en tiempo real.
* **🧾 Comprobantes Electrónicos Paramétricos:** Generación de recibos con identificadores dinámicos que se nutren globalmente desde un centro de **Configuración de Negocio** manipulable directamente desde la UI.
* **🛡️ Seguridad Activa Modulada:** Interfaz de configuración segmentada (Negocio / Seguridad) que incluye auditoría de credenciales: **alerta crítica mientras la contraseña inicial no se haya rotado** y aviso de caducidad según el plazo configurado (90 días por defecto).
* **📊 Experiencia de Usuario (UI/UX) y Escalabilidad:** **Vistas nativas independientes para Autenticación** (Login y Registro) con validación en servidor vía Form Requests, e integración de *DataTables* para búsquedas instantáneas y paginación en el navegador.

---

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8, Laravel 9
* **Frontend:** Blade Templating, Bootstrap 5.3, Vanilla CSS
* **Base de Datos:** MySQL
* **Librerías Adicionales:** Chart.js (Analíticas), DataTables (Tablas interactivas jQuery), FontAwesome (Iconografía).

---

## 🚀 Requisitos e Instalación

Asegúrate de contar con **PHP >= 8.0**, **Composer** y **MySQL** instalados (o un entorno local como Laragon / XAMPP).

**1. Clonar el Repositorio**
```bash
git clone https://github.com/tu-usuario/PescaderiaLaravel.git
cd PescaderiaLaravel
```

**2. Instalar Dependencias**
```bash
composer install
```

**3. Configurar Entorno**
Duplica el archivo de configuración base y coloca tus credenciales de base de datos locales:
```bash
cp .env.example .env
```
> Asegúrate de crear una base de datos nueva en tu servidor local (Ej: `pescaderia_db`) y actualizar las variables `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en el archivo `.env`.

**4. Generar la Clave de Aplicación**
```bash
php artisan key:generate
```

**5. Migraciones y Seeders (Población de Datos)**
Construir toda la estructura de la base de datos y nutrirla con datos de prueba, administradores y unidades de conversión exactas:
```bash
php artisan migrate:fresh --seed
```

**6. Inicializar Servidor**
```bash
php artisan serve
```
La tienda estará corriendo por defecto en: `http://localhost:8000`

---

## 🔐 Credenciales Base (Solo Pruebas)

Al correr los *Seeders* se genera automáticamente una cuenta con privilegios máximos:

* **Rol:** Administrador
* **Usuario:** `admin123`
* **Contraseña:** `admin123`

> [!WARNING]  
> **Medida de Seguridad Estricta:** Al acceder con estas credenciales por primera vez, el sistema activará una **Alerta Crítica permanente** en la cabecera de todas las vistas administrativas. Esta alerta no desaparecerá hasta que modifiques el nombre de usuario y contraseña en el módulo de **Configuración > Seguridad**. Así mismo, la plataforma exigirá el cambio periódico de estas claves cada 90 días.

*(Una vez logueado, haz clic en tu nombre o perfil en la tienda pública para acceder al "Panel de Control").*

---

## 📂 Arquitectura Destacada

* Patrón MVC Riguroso (Model-View-Controller).
* Lógica de Negocio centralizada con la directiva **Services** (`OrderService.php` para manejo transaccional).
* Extracción y tipado de validaciones en **Form Requests** dedicados (`StoreProductoRequest`, `LoginRequest`).
* Código analizado y calificado con políticas Zero-Trust usando Intelephense y SonarLint.

Desarrollado con altos estándares de codificación empresarial. 💻
