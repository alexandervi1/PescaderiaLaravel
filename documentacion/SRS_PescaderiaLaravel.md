# Especificación de Requisitos de Software (SRS)

## Comercial de Mariscos Don Walter (PescaderiaLaravel)

> Documento basado en el estándar IEEE Std 830-1998 · 16 de noviembre de 2026

## Índice

1. [Introducción](#1-introducción)
2. [Descripción General](#2-descripción-general)
3. [Requisitos Específicos](#3-requisitos-específicos)

---

## 1. Introducción

### 1.1. Propósito

El propósito de este documento es definir la Especificación de Requisitos de Software (SRS) para el sistema de e-commerce y gestión de la "Comercial de Mariscos Don Walter", desarrollado bajo el framework Laravel 12. Este documento detalla tanto las funcionalidades del frontend (tienda orientada al cliente) como del backend (panel CRM de administración).

### 1.2. Ámbito del Sistema

El software es una plataforma integral de comercio electrónico para pescado y mariscos de alta calidad. El sistema permite a los clientes explorar productos mediante una interfaz inmersiva, gestionar un carrito de compras y realizar pedidos. Simultáneamente, ofrece a la administración un Dashboard de gestión de inventarios, despachos, métricas financieras en tiempo real y configuraciones estrictas de seguridad (hardening y rotación de contraseñas).

### 1.3. Definiciones, Acrónimos y Abreviaturas

| Término | Significado |
|---|---|
| **SRS** | Software Requirements Specification |
| **PDP** | Product Details Page (Página de Detalles de Producto) |
| **CRM** | Customer Relationship Management |
| **ORM** | Object-Relational Mapping (Eloquent en Laravel) |
| **RUC** | Registro Único de Contribuyentes |

### 1.4. Referencias

- IEEE Std 830-1998, *IEEE Recommended Practice for Software Requirements Specifications*.
- Documentación oficial de Laravel 12 — <https://laravel.com/docs/12.x>.

### 1.5. Visión General del Documento

El presente documento está estructurado de acuerdo con el estándar IEEE 830-1998. La sección 2 ofrece una perspectiva general del sistema. La sección 3 describe exhaustivamente los requisitos funcionales, no funcionales y las interfaces externas.

---

## 2. Descripción General

### 2.1. Perspectiva del Producto

El sistema es una aplicación web autónoma que reemplaza los métodos tradicionales de venta física e inventario manual. Está diseñado sobre una arquitectura Modelo-Vista-Controlador (MVC) conectada a una base de datos relacional (MySQL).

### 2.2. Funciones del Producto

Las funciones principales del sistema incluyen:

- **Gestión de Catálogo:** CRUD de productos, categorías y unidades de medida, con factores de conversión validados entre unidad de compra y de venta.
- **Escaparate Digital (E-commerce):** catálogo con filtros y búsqueda, Página de Detalles (PDP) en tres columnas y carrito con validación de stock en servidor.
- **Gestión de Órdenes:** checkout transaccional con precio congelado por línea, filtro por estado y comprobante imprimible con los datos del negocio.
- **Clientes y Carritos:** administración de clientes y seguimiento de carritos activos.
- **Panel Analítico:** gráfico Chart.js de los últimos seis meses y reportes de ventas, más vendidos, bajo stock y stock por categoría.
- **Configuración:** parámetros del negocio (identidad, logo, IVA, umbral de bajo stock, serie de comprobante, zona horaria), seguridad de la cuenta e historial de cambios.
- **Módulo de Seguridad:** rotación de credenciales con plazo configurable y bitácora de cambios.

### 2.3. Características de los Usuarios

Existen 2 perfiles principales interactuando con la aplicación:

1. **Clientes:** consumidores finales de servicios web. Requieren una interfaz sumamente intuitiva y atractiva visualmente, sin requerir conocimientos técnicos.
2. **Administradores:** gestores logísticos y contables con requerimientos de alta disponibilidad sobre tableros de inventario y manipulación de datos sensibles.

### 2.4. Entorno Operativo

- Servidor web: mínimo Apache 2.4 o Nginx.
- Lenguaje base: PHP 8.2+.
- Base de datos: MySQL 5.7+ o MariaDB.
- Frameworks UI: Bootstrap 5.3, DataTables.

### 2.5. Restricciones de Diseño e Implementación

- La aplicación debe hacer uso exhaustivo de PDO Parameter Binding en Eloquent para mitigar inyecciones SQL.
- Las vistas deben adherirse estrictamente a la metodología *mobile-first* mediante el sistema grid de Bootstrap.
- El borrado de categorías no debe eliminar en cascada los productos en la base de datos (restricción `Set Null`).
- El borrado de un producto con pedidos asociados debe impedirse (restricción `Restrict`) para no alterar el historial.
- Los parámetros comerciales (IVA, umbral de bajo stock, serie de comprobante, zona horaria, plazo de rotación) se leen de configuración, no del código.
- La interfaz debe usar una paleta única basada en tokens y cumplir contraste WCAG AA en las pantallas de administración.

---

## 3. Requisitos Específicos

### 3.1. Requisitos de Interfaz Externa

#### 3.1.1. Interfaces de Usuario

El sistema presentará una separación de interfaces estricta entre administradores y clientes. La interfaz frontal debe incluir un *buy box* y compatibilidad con pantallas táctiles, mientras que el *backend dashboard* alojará paneles colapsables laterales y componentes DataTables.

#### 3.1.2. Interfaces de Hardware y Software

La plataforma deberá ejecutarse eficazmente sobre cualquier navegador web moderno (Chrome, Firefox, Safari, Edge). Carece de interfaces de integración al hardware más allá del soporte de impresión para los reportes o comprobantes en formato A4.

### 3.2. Requisitos Funcionales

#### RF-01: Autenticación de Usuarios

El sistema debe proveer vistas nativas e independientes para el registro de usuarios y el inicio de sesión. La verificación se realiza contra el nombre de usuario o el correo electrónico y una contraseña cifrada con Bcrypt. Los intentos fallidos se limitan a cinco por minuto por usuario e IP. Las validaciones devuelven HTTP 422 en peticiones JSON y redirección con errores en formularios HTML.

#### RF-02: Exploración de Productos E-commerce

Los clientes podrán explorar el catálogo y acceder a una PDP (Product Details Page) de tres columnas en escritorio —galería, ficha técnica y caja de compra— que pasa a dos columnas con la caja de compra a ancho completo en tablet. Los precios se muestran por unidad de venta (*lb, u*).

#### RF-03: Procesamiento del Carrito de Compras

El cliente debe poder agregar y modificar cantidades validando en servidor que la solicitud no supere la existencia disponible (*stockVenta*). El checkout se ejecuta en una transacción con bloqueo de las filas de producto, descuenta el stock en la unidad correcta y revierte todo si algún artículo no alcanza.

#### RF-04: Panel Estadístico de Despachos

El aplicativo facilitará a los administradores un gráfico Chart.js con los ingresos de los últimos seis meses y reportes de ventas por mes, productos más vendidos, bajo stock y stock por categoría, excluyendo los pedidos cancelados.

#### RF-05: Modulación de Seguridad Activa

La plataforma auditará a los administradores: si la cuenta conserva su contraseña inicial se mostrará una alerta crítica, y si la contraseña supera el plazo configurado (90 días por defecto) se mostrará un aviso. Cambiar usuario o contraseña exige la contraseña actual, cierra la sesión y queda registrado en la bitácora.

#### RF-06: Unidades de Medida

El administrador podrá crear, editar y eliminar unidades de medida con nombre y abreviatura únicos. Una unidad en uso por productos no puede eliminarse. El factor de conversión entre unidad de compra y de venta se calcula al guardar el producto y solo se admiten combinaciones soportadas.

#### RF-07: Administradores

El sistema permitirá crear, editar y eliminar cuentas de administrador con correo único. No se puede eliminar la propia cuenta ni la última cuenta de administrador, y una cuenta con pedidos asociados no puede eliminarse.

#### RF-08: Carritos Activos

El panel mostrará los carritos sin finalizar con cliente, producto, cantidad, precio, subtotal y fecha, junto al valor estimado total.

#### RF-09: Configuración del Negocio

El administrador podrá editar razón social, RUC, leyenda, dirección, teléfono, logo, IVA, umbral de bajo stock, serie de comprobante, plazo de rotación de contraseña y zona horaria. Los cambios afectan al pie de página, los comprobantes, los reportes y los nuevos pedidos.

#### RF-10: Bitácora de Configuración

Cada cambio de configuración o de credenciales se registrará con usuario, clave, valor anterior y valor nuevo, y será consultable desde la pestaña Historial.

### 3.3. Requisitos de Rendimiento

- **Tiempos de respuesta:** el sistema deberá responder a interacciones e-commerce (*add to cart*) en un lapso no superior a los 500 ms al servidor bajo demanda transaccional promedio.
- **Simultaneidad:** el checkout usa transacciones SQL con bloqueo de filas (`lockForUpdate`) para que dos operadores no sobrevendan el mismo producto.
- **Configuración:** los parámetros se leen de una caché invalidada al guardar, evitando una consulta por cada valor.

### 3.4. Atributos del Sistema

#### 3.4.1. Seguridad

- Uso exclusivo de directivas del ORM para evitar fallos de inyección SQL.
- Protección CSRF adherida globalmente en todos los formularios HTML de método POST/PUT/DELETE.
- Segmentación por middlewares en rutas administrativas y de clientes.
- Cambio de credenciales con contraseña actual obligatoria y correo único por cuenta.
- Archivos subidos con extensión derivada del contenido y borrado acotado a `public/img`.

#### 3.4.2. Fiabilidad y Mantenibilidad

La estructura de directorios sigue estrictamente el ecosistema formal de Laravel. El desacoplamiento entre los controladores y los modelos asegura un sencillo marco para escalar horizontalmente la plataforma. La lógica de precios y stock vive en servicios (`OrderService`, `PricingService`) y la suite de pruebas cubre los flujos críticos sobre SQLite en memoria.
