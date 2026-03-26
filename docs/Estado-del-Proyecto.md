# Market Artesanos - Estado del Proyecto y Pendientes

Documento generado el 25/03/2026. Refleja el estado actual del sistema y los pendientes para llegar a produccion.

---

## Indice

1. [Resumen del sistema](#1-resumen-del-sistema)
2. [Stack tecnologico](#2-stack-tecnologico)
3. [Modulos implementados](#3-modulos-implementados)
4. [Modelo de negocio - Aclaraciones importantes](#4-modelo-de-negocio---aclaraciones-importantes)
5. [Pendientes criticos para salir a produccion](#5-pendientes-criticos-para-salir-a-produccion)
6. [Pendientes importantes para buena experiencia](#6-pendientes-importantes-para-buena-experiencia)
7. [Pendientes deseables (mejoran conversion)](#7-pendientes-deseables-mejoran-conversion)
8. [Bugs y riesgos detectados](#8-bugs-y-riesgos-detectados)
9. [Dependencias externas en espera](#9-dependencias-externas-en-espera)
10. [Estructura de archivos](#10-estructura-de-archivos)
11. [Base de datos - Esquema actual](#11-base-de-datos---esquema-actual)
12. [API - Endpoints existentes](#12-api---endpoints-existentes)
13. [Orden de prioridad recomendado](#13-orden-de-prioridad-recomendado)

---

## 1. Resumen del sistema

Market Artesanos es un ecommerce desarrollado para una asociacion de artesanos. Permite a los clientes explorar un catalogo de productos artesanales organizados por categorias, agregar productos al carrito, cotizar envio a traves de Correo Argentino (PaqAr), y realizar pedidos.

El sistema cuenta con un panel de administracion para gestionar artesanos, productos, categorias, clientes y ordenes.

---

## 2. Stack tecnologico

| Capa | Tecnologia |
|------|-----------|
| Frontend | Vue 3 (Composition API) + Vite 6 |
| Estado (frontend) | Pinia |
| Estilos | Tailwind CSS 4 |
| HTTP Client | Axios |
| Backend | Laravel 10 (PHP) |
| Autenticacion | Laravel Sanctum (tokens) |
| Base de datos | MySQL |
| Servidor local | XAMPP |
| Envio | Correo Argentino API PaqAr v2 |
| Pago (pendiente) | PayWay (en espera de credenciales) |

---

## 3. Modulos implementados

### 3.1 Frontend - Paginas existentes

| Pagina | Ruta | Acceso | Estado |
|--------|------|--------|--------|
| Home | `/` | Publico | Completo |
| Catalogo | `/catalogo` | Publico | Completo |
| Detalle de producto | `/producto/:slug` | Publico | Completo |
| Carrito | `/carrito` | Publico | Completo |
| Nosotros | `/nosotros` | Publico | Completo |
| Login | `/login` | Publico | Completo |
| Registro | `/registro` | Publico | Completo |
| Mi Perfil | `/mi-perfil` | Autenticado | Completo |
| Dashboard Admin | `/dashboard` | Admin/Presidente | Completo |

### 3.2 Frontend - Componentes

- **AppNavbar** - Navegacion principal con logo, menu, carrito, controles de sesion
- **AppFooter** - Footer con links, ubicacion, email de contacto
- **ProductCard** - Tarjeta de producto reutilizable (imagen, precio, artesano, categoria)

### 3.3 Backend - Controladores

| Controlador | Funcionalidades |
|------------|----------------|
| AuthController | Registro, login, logout, perfil de usuario, actualizar perfil |
| ProductController | CRUD de productos, filtrado por categoria/destacados, cotizacion de envio |
| CartController | Carrito (ver, agregar, actualizar cantidad, eliminar). Soporta usuarios autenticados y sesiones de invitado |
| OrderController | Listar ordenes del usuario, checkout (crear orden desde carrito) |
| CategoryController | Listar categorias con subcategorias, crear categoria. **Faltan update y destroy** |
| ArtisanController | CRUD de artesanos, listado publico y admin |
| ClientController | Listar clientes, ver detalle de cliente con sus ordenes |

### 3.4 Backend - Modelos y relaciones

```
User (1) ----> (1) Artisan
User (1) ----> (*) Order
Order (1) ----> (*) OrderItem
OrderItem (*) ----> (1) Product
Product (*) ----> (1) Artisan
Product (*) ----> (1) Category
Category (1) ----> (*) Category (subcategorias)
Artisan (*) <---> (*) Category (pivot: artisan_category)
Cart (1) ----> (*) CartItem
CartItem (*) ----> (1) Product
```

### 3.5 Backend - Servicios

- **CorreoArgentinoService**: Integra con la API PaqAr v2 para autenticacion, cotizacion de tarifas, creacion de ordenes de envio, cancelacion, etiquetas y tracking.

### 3.6 Autenticacion y roles

- Autenticacion por token Bearer (Laravel Sanctum)
- Carrito de invitado con session_id (UUID via header `X-Session-Id`)
- Middleware `CheckRole` para proteger rutas admin
- Guards en el router de Vue para proteger rutas del frontend

**Roles del sistema:**

| Rol | Descripcion | Permisos |
|-----|-------------|----------|
| `admin` | Administrador de sistemas / informatico. Tiene acceso total al sistema para pruebas y mantenimiento. Puede operar desde la vista del presidente para verificar funcionalidades. | Acceso completo a todo |
| `presidente` | Representante de la asociacion de artesanos. Es quien opera el sistema en el dia a dia: carga artesanos con su categoria/rubro, crea productos de cada artesano, administra categorias/rubros, gestiona ordenes. | Dashboard, ABM de artesanos, productos, categorias, clientes, ordenes |
| `cliente` | Usuario registrado en el sitio. Puede navegar el catalogo, agregar productos al carrito, comprar, ver su perfil e historial de pedidos. | Catalogo, carrito, checkout, perfil, historial de compras |
| (sin registro) | Visitante / publico general. Puede navegar todo el sitio y ver productos, pero si quiere comprar debe registrarse primero. | Catalogo, detalle de producto, carrito (sin checkout) |

### 3.7 Panel de administracion

El dashboard incluye:
- KPIs resumidos (artesanos, productos, clientes, ordenes)
- ABM de artesanos (crear, editar, eliminar, activar/desactivar)
- ABM de productos (crear, editar, eliminar, activar/desactivar)
- Listado de clientes con fecha de registro
- Listado de ordenes con seguimiento de estado

---

## 4. Modelo de negocio - Aclaraciones importantes

### No hay sistema de stock

Los productos son artesanales y **se fabrican bajo demanda** una vez confirmada la compra. El artesano no mantiene un inventario/stock previo. Por lo tanto:

- El campo `stock` en la tabla `products` **no se utiliza como stock real**.
- El cliente puede solicitar la cantidad que necesite (1, 2, 5, etc.).
- **Limite de cantidad y tiempos de entrega:**
  - De 1 a 5 unidades: tiempo estimado de **15 a 20 dias habiles**.
  - Mas de 5 unidades: el tiempo de entrega **aumenta** y se debe informar al cliente que el plazo sera mayor (a coordinar con el artesano).

### Flujo post-compra: contacto directo con el artesano

Una vez confirmada la compra, el sistema debe mostrar al cliente la **informacion de contacto del artesano** que fabrica el producto comprado. Esto permite que el cliente:

- Comunique detalles de personalizacion (colores, medidas, inscripciones, etc.)
- Coordine directamente con el artesano las especificaciones del pedido
- Consulte sobre el avance de la fabricacion

La informacion de contacto a mostrar incluye: nombre del artesano, telefono y/o email, ubicacion.

---

## 5. Pendientes criticos para salir a produccion

Estos items son **bloqueantes** para poder operar como tienda online.

### 5.1 Pagina de Checkout

**Estado:** No existe.

El carrito actualmente no lleva a ninguna pagina de finalizacion de compra. Se necesita una vista `/checkout` que incluya:

- Resumen del pedido (productos, cantidades, subtotal)
- Formulario de datos de envio (nombre, direccion, codigo postal, localidad, provincia, telefono)
- Cotizacion de envio integrada (ya existe el endpoint `POST /api/shipping-rates`)
- Informacion sobre tiempos de entrega segun cantidad (ver seccion 4)
- Boton de confirmar pedido
- Integracion con medio de pago (pendiente de credenciales, ver seccion 9)

### 5.2 Limite de cantidad y aviso de tiempos de entrega

**Estado:** No existe.

Se debe implementar logica para informar al cliente sobre los tiempos de fabricacion:

- En el detalle de producto y en el checkout, mostrar el tiempo estimado segun la cantidad solicitada.
- Si la cantidad supera 5 unidades, mostrar un aviso claro: "Para pedidos de mas de 5 unidades, el tiempo de entrega puede superar los 20 dias habiles. El artesano te contactara para coordinar el plazo."
- Considerar si se quiere establecer un limite maximo de unidades por pedido o dejarlo abierto.

### 5.3 Pantalla de confirmacion con datos del artesano

**Estado:** No existe.

Una vez que la compra se confirma, el cliente debe ver una pantalla (y/o recibir un email) con:

- Confirmacion del pedido (numero de orden, detalle de lo comprado)
- Datos de contacto del artesano: nombre, telefono, email, ubicacion
- Instrucciones: "Contacta al artesano para coordinar los detalles de tu pedido (colores, medidas, personalizacion, etc.)"
- Tiempo estimado de fabricacion segun la cantidad

Esto tambien deberia quedar visible en la seccion "Mis pedidos" del perfil del usuario.

### 5.4 Gestion de estados de orden desde el admin

**Estado:** No existe endpoint para actualizar el estado.

El admin puede ver las ordenes pero no puede cambiar su estado. Se necesita:

- Endpoint `PATCH /api/orders/{id}/status` para actualizar el estado (pending -> paid -> shipped -> delivered / cancelled)
- Boton en el dashboard para cambiar estado de cada orden
- Opcionalmente, agregar numero de seguimiento de envio desde el panel

### 5.5 Emails transaccionales

**Estado:** Mail configurado con Mailpit (desarrollo). No se envia ningun email.

Emails minimos necesarios:

- **Confirmacion de registro**: Bienvenida al nuevo usuario
- **Confirmacion de pedido**: Detalle de la compra + datos de contacto del artesano
- **Cambio de estado del pedido**: Notificar al cliente cuando su pedido cambia a "enviado" o "entregado"
- **Notificacion al artesano**: Cuando recibe un nuevo pedido, avisarle con los datos del cliente y el producto solicitado

### 5.6 Recuperacion de contrasena

**Estado:** La tabla `password_reset_tokens` existe pero no hay endpoints ni vistas.

Se necesita:

- Endpoint `POST /api/forgot-password` (envia email con token)
- Endpoint `POST /api/reset-password` (valida token y actualiza password)
- Vista `/recuperar-contrasena` en el frontend
- Vista `/restablecer-contrasena/:token` en el frontend

---

## 6. Pendientes importantes para buena experiencia

Estos items no son bloqueantes pero mejoran significativamente la experiencia del usuario.

### 6.1 Buscador de productos

No hay busqueda por texto. Solo se filtra por categoria. Se necesita:

- Endpoint `GET /api/products?search=texto` que busque en nombre y descripcion
- Componente de busqueda en el navbar o en el catalogo

### 6.2 Paginas legales

Obligatorias para operar un ecommerce en Argentina:

- **Terminos y Condiciones**: Incluir que los productos son artesanales fabricados bajo demanda, tiempos de entrega, politica de cambios
- **Politica de Privacidad**: Tratamiento de datos personales (Ley 25.326)
- **Politica de Devoluciones**: Adaptada a productos hechos a medida (boton de arrepentimiento, excepciones por personalizacion)

### 6.3 Pagina de Contacto

No hay formulario de contacto. Solo un email estatico en el footer. Se necesita:

- Vista `/contacto` con formulario (nombre, email, asunto, mensaje)
- Endpoint que envie el formulario por email al administrador

### 6.4 Verificacion de email

El campo `email_verified_at` existe y el evento esta registrado pero no se aplica. Se recomienda:

- Enviar email de verificacion al registrarse
- Exigir verificacion antes de poder comprar

### 6.5 CategoryController incompleto

Las rutas `PUT /api/categories/{category}` y `DELETE /api/categories/{category}` estan definidas pero los metodos `update()` y `destroy()` **no estan implementados** en el controlador. El admin no puede editar ni eliminar categorias.

### 6.6 Paginacion en el catalogo (frontend)

El backend pagina a 15 productos por pagina, pero el frontend no muestra controles de paginacion. Con muchos productos, la pagina no mostraria todos.

---

## 7. Pendientes deseables (mejoran conversion)

Estos items son opcionales pero aportan valor al negocio.

| # | Feature | Descripcion |
|---|---------|-------------|
| 1 | Sistema de resenas/valoraciones | Permitir a clientes dejar opiniones y puntuaciones de productos |
| 2 | Lista de deseos (wishlist) | Guardar productos favoritos para comprar despues |
| 3 | ~~Cupones y descuentos~~ | DESCARTADO - La asociacion decidio no implementar este sistema |
| 4 | FAQ / Preguntas frecuentes | Pagina con respuestas a dudas comunes (tiempos, personalizacion, envios, etc.) |
| 5 | Notificaciones push (Firebase) | El campo `firebase_token` y el paquete ya existen. Falta implementar |
| 6 | SEO basico | Meta tags dinamicos, sitemap.xml, Open Graph tags para redes sociales |
| 7 | Compartir producto en redes | Botones para compartir en WhatsApp, Instagram, Facebook |
| 8 | Imagenes optimizadas | Lazy loading, formatos WebP, thumbnails para mejorar velocidad de carga |
| 9 | Prevenir doble click en botones | Deshabilitar botones de accion (agregar al carrito, confirmar pedido, guardar, etc.) mientras se procesa la solicitud para evitar envios duplicados |
| 10 | Ojito para visualizar contrasena | Agregar icono de ojo en los campos de contrasena (login, registro, perfil) para poder ver/ocultar la contrasena ingresada |
| 11 | Reemplazar alert() nativos por notificaciones Vue | Sustituir todos los `alert()` del navegador y notificaciones nativas por un sistema de notificaciones/toasts elegante (ej: vue-toastification o componente propio) que sea consistente con el diseno del sitio |

---

## 8. Bugs y riesgos detectados

### 8.1 Credenciales de Firebase en el repositorio

El archivo `backend/storage/app/firebase-credentials.json` podria estar incluido en el repositorio. Verificar que `.gitignore` lo excluya. Si ya fue commiteado, eliminarlo del historial.

### 8.2 Spatie Permission sin uso

El paquete `spatie/laravel-permission` esta instalado y las tablas de permisos estan creadas, pero **no se usan**. Los roles estan hardcodeados como enum en la tabla `users`. Se recomienda elegir un enfoque y limpiar el otro para evitar confusion.

### 8.3 Carritos abandonados

El carrito de invitado usa `session_id` (UUID) pero no hay mecanismo de limpieza. Con el tiempo, se acumularan registros huerfanos en las tablas `carts` y `cart_items`. Se recomienda un comando artisan programado para limpiar carritos sin actividad mayor a X dias.

### 8.4 Campo stock en productos

El campo `stock` existe en la tabla `products` pero segun el modelo de negocio no aplica (fabricacion bajo demanda). Opciones:

- Reutilizarlo como "limite maximo por pedido" si se necesita
- Ignorarlo y dejarlo en 0/null
- Eliminarlo con una migracion para evitar confusiones futuras

---

## 9. Dependencias externas en espera

### 9.1 Medio de pago - PayWay

**Estado:** En espera de usuario y clave API.

El SDK de MercadoPago esta instalado (`mercadopago/dx-php`) pero se decidio usar PayWay. La guia de integracion esta documentada en `Guia-Integracion-PayWay.md`.

Una vez recibidas las credenciales:
- Integrar el SDK de PayWay en el backend
- Crear endpoint de procesamiento de pago
- Integrar formulario de pago en la vista de checkout
- Manejar callbacks de confirmacion/rechazo

### 9.2 Correo Argentino - API PaqAr

**Estado:** En espera de usuario y clave API de produccion.

El servicio `CorreoArgentinoService` ya esta implementado y funcional en modo sandbox. La cotizacion de envio ya funciona en el detalle de producto.

Una vez recibidas las credenciales de produccion:
- Configurar variables de entorno (`CORREO_ARGENTINO_API_KEY`, `CORREO_ARGENTINO_AGREEMENT`)
- Cambiar `CORREO_ARGENTINO_SANDBOX=false`
- Testear con datos reales

---

## 10. Estructura de archivos

```
market-artesanos/
├── backend/                          # Laravel 10
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── ArtisanController.php
│   │   │   │   ├── CartController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── ClientController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   └── ProductController.php
│   │   │   └── Middleware/
│   │   │       └── CheckRole.php
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   ├── Artisan.php
│   │   │   ├── Product.php
│   │   │   ├── Category.php
│   │   │   ├── Order.php
│   │   │   ├── OrderItem.php
│   │   │   ├── Cart.php
│   │   │   └── CartItem.php
│   │   └── Services/
│   │       └── CorreoArgentinoService.php
│   ├── database/
│   │   ├── migrations/               # 15 migraciones
│   │   └── seeders/
│   │       ├── DatabaseSeeder.php
│   │       ├── UserSeeder.php
│   │       ├── CategorySeeder.php
│   │       └── ArtisanSeeder.php
│   ├── routes/
│   │   └── api.php                   # Todas las rutas API
│   └── config/
│       ├── sanctum.php
│       ├── services.php              # Config Correo Argentino
│       └── mail.php
├── frontend/                         # Vue 3 + Vite
│   └── src/
│       ├── views/
│       │   ├── Home.vue
│       │   ├── Catalog.vue
│       │   ├── ProductDetail.vue
│       │   ├── Cart.vue
│       │   ├── Nosotros.vue
│       │   ├── Profile.vue
│       │   ├── auth/
│       │   │   ├── Login.vue
│       │   │   └── Register.vue
│       │   └── admin/
│       │       └── Dashboard.vue
│       ├── components/
│       │   ├── common/
│       │   │   └── ProductCard.vue
│       │   └── layout/
│       │       ├── AppNavbar.vue
│       │       └── AppFooter.vue
│       ├── router/index.js
│       ├── stores/auth.js
│       └── utils/api.js
├── deploy/                           # Copia para deploy produccion
├── docs/                             # Documentacion
├── Guia-Integracion-PayWay.md
├── Guia-Actualizar-Sitio-Produccion.md
└── market_artesanos.sql              # Dump de la base de datos
```

---

## 11. Base de datos - Esquema actual

### Tabla: users
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint PK | Auto increment |
| name | string | |
| email | string unique | |
| email_verified_at | timestamp nullable | Sin uso activo |
| password | string | Hashed |
| role | enum (admin, presidente, cliente) | |
| avatar | string nullable | |
| phone | string nullable | |
| firebase_token | string nullable | Sin uso activo |
| timestamps | | |

### Tabla: categories
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint PK | |
| name | string | |
| slug | string unique | |
| description | text nullable | |
| icon | string nullable | |
| parent_id | FK categories nullable | Subcategorias |
| timestamps | | |

### Tabla: artisans
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint PK | |
| user_id | FK users | |
| bio | text nullable | |
| specialty | string nullable | |
| location | string nullable | |
| photo | string nullable | |
| is_active | boolean | |
| timestamps | | |

### Tabla: products
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint PK | |
| artisan_id | FK artisans | |
| category_id | FK categories | |
| name | string | |
| slug | string unique | |
| description | text | |
| price | decimal(10,2) | |
| stock | integer | **No se usa - fabricacion bajo demanda** |
| images | json | Array de rutas de imagenes |
| is_featured | boolean | |
| is_active | boolean | |
| weight | unsigned int | Gramos (para envio) |
| height | unsigned int | CM (para envio) |
| width | unsigned int | CM (para envio) |
| depth | unsigned int | CM (para envio) |
| timestamps | | |

### Tabla: orders
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint PK | |
| user_id | FK users | |
| total | decimal(10,2) | Incluye envio |
| status | enum (pending, paid, shipped, delivered, cancelled) | |
| payment_id | string nullable | Para integracion de pago |
| shipping_tracking | string nullable | Numero de seguimiento |
| shipping_cost | decimal(10,2) | |
| timestamps | | |

### Tabla: order_items
| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint PK | |
| order_id | FK orders | |
| product_id | FK products | |
| quantity | integer | |
| unit_price | decimal(10,2) | |
| timestamps | | |

### Tabla: carts / cart_items
Estructura similar a orders/order_items. El cart soporta `user_id` (autenticado) o `session_id` (invitado).

### Tabla: artisan_category (pivot)
Relacion muchos a muchos entre artesanos y categorias.

---

## 12. API - Endpoints existentes

### Publicos (sin autenticacion)

```
POST   /api/register                  Registro de usuario
POST   /api/login                     Login

GET    /api/categories                Listar categorias con subcategorias
GET    /api/categories/{id}           Categoria con subcategorias y productos

GET    /api/artisans                  Listar artesanos activos
GET    /api/artisans/{id}             Detalle de artesano

GET    /api/products                  Listar productos (filtros: category_id, featured)
GET    /api/products/{slug}           Detalle de producto

POST   /api/shipping-rates            Cotizar envio (Correo Argentino)

GET    /api/cart                      Ver carrito
POST   /api/cart                      Agregar producto al carrito
PUT    /api/cart/{itemId}             Actualizar cantidad
DELETE /api/cart/{itemId}             Eliminar item del carrito
```

### Autenticados (requieren token)

```
POST   /api/logout                    Cerrar sesion
GET    /api/user                      Perfil del usuario
PUT    /api/user                      Actualizar perfil

GET    /api/orders                    Mis ordenes
POST   /api/orders/checkout           Crear orden desde carrito
```

### Admin / Presidente

```
POST   /api/categories                Crear categoria
PUT    /api/categories/{id}           Editar categoria (NO IMPLEMENTADO)
DELETE /api/categories/{id}           Eliminar categoria (NO IMPLEMENTADO)

GET    /api/admin/artisans            Listar todos los artesanos
POST   /api/artisans                  Crear artesano
PUT    /api/artisans/{id}             Editar artesano
DELETE /api/artisans/{id}             Eliminar artesano

GET    /api/admin/products            Listar todos los productos
POST   /api/products                  Crear producto
PUT    /api/products/{id}             Editar producto
PATCH  /api/products/{id}/toggle-active  Activar/desactivar producto
DELETE /api/products/{id}             Eliminar producto

GET    /api/admin/clients             Listar clientes
GET    /api/admin/clients/{id}        Detalle de cliente
```

### Endpoints que faltan por crear

```
PATCH  /api/orders/{id}/status        Cambiar estado de orden (admin)
POST   /api/forgot-password           Solicitar recuperacion de contrasena
POST   /api/reset-password            Restablecer contrasena con token
GET    /api/products?search=texto     Busqueda por texto (agregar al existente)
POST   /api/contact                   Enviar formulario de contacto
```

---

## 13. Orden de prioridad recomendado

### Fase 1 - Minimo viable para vender

1. **Pagina de Checkout** - Vista completa con resumen, datos de envio, cotizacion, tiempos de entrega segun cantidad
2. **Pantalla de confirmacion con datos del artesano** - Post-compra, mostrar contacto del artesano para coordinar detalles
3. **Gestion de estados de orden (admin)** - Endpoint + UI para cambiar estado de pedidos
4. **Emails transaccionales** - Confirmacion de pedido (con datos del artesano), notificacion al artesano de nuevo pedido
5. **Recuperar contrasena** - Flujo completo (email + reset)
6. **Integracion PayWay** - Cuando lleguen las credenciales
7. **Integracion Correo Argentino produccion** - Cuando lleguen las credenciales

### Fase 2 - Experiencia completa

8. Paginas legales (T&C, Privacidad, Devoluciones)
9. Buscador de productos
10. Pagina de contacto con formulario
11. Completar CategoryController (update/destroy)
12. Paginacion en el catalogo frontend
13. Verificacion de email

### Fase 3 - Mejoras de conversion

14. FAQ / Preguntas frecuentes
15. SEO basico (meta tags, sitemap, Open Graph)
16. Sistema de resenas/valoraciones
17. Lista de deseos
18. Cupones y descuentos
19. Compartir en redes sociales
20. Limpieza de carritos abandonados (comando programado)
