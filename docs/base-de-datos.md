# Base de Datos - Market Artesanos

## Motor de Base de Datos

- **MySQL** (via XAMPP)
- Base de datos: `market_artesanos`

---

## Tablas del Sistema

### users
Tabla principal de usuarios del sistema (clientes, artesanos, presidente, admin).

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| name | varchar(255) | Nombre completo |
| email | varchar(255, unique) | Correo electronico |
| email_verified_at | timestamp | Fecha de verificacion de email |
| password | varchar(255) | Contrasena hasheada |
| role | enum | Rol: `admin`, `presidente`, `cliente` |
| avatar | varchar(255) | Ruta de imagen de avatar |
| phone | varchar(255) | Numero de telefono |
| firebase_token | varchar(255) | Token para notificaciones push |
| remember_token | varchar(100) | Token de sesion |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

### artisans
Perfil extendido para usuarios que son artesanos. Relacion uno-a-uno con `users`.

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| user_id | bigint (FK -> users) | Usuario asociado |
| bio | text | Biografia del artesano |
| specialty | varchar(255) | Especialidad/rubros (texto libre) |
| location | varchar(255) | Ubicacion geografica |
| photo | varchar(255) | Foto de perfil del artesano |
| is_active | boolean | Estado activo/inactivo |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

### categories
Categorias de productos. Soporta jerarquia padre-hijo (subcategorias).

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| name | varchar(255) | Nombre de la categoria |
| slug | varchar(255, unique) | Slug para URLs |
| description | text | Descripcion |
| icon | varchar(255) | Icono representativo |
| parent_id | bigint (FK -> categories) | Categoria padre (null = raiz) |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

### artisan_category (tabla pivote)
Relacion muchos-a-muchos entre artesanos y categorias/rubros.

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| artisan_id | bigint (FK -> artisans) | Artesano |
| category_id | bigint (FK -> categories) | Categoria/rubro |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

**Constraint:** unique(artisan_id, category_id)

---

### products
Productos/muestras del catalogo.

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| artisan_id | bigint (FK -> artisans) | Artesano creador |
| category_id | bigint (FK -> categories) | Categoria del producto |
| name | varchar(255) | Nombre del producto |
| slug | varchar(255, unique) | Slug para URLs |
| description | text | Descripcion del producto |
| price | decimal(10,2) | Precio por unidad |
| stock | integer | Stock (referencial, no se descuenta) |
| images | json | Array de rutas de imagenes |
| is_featured | boolean | Producto destacado |
| is_active | boolean | Habilitado para mostrar en catalogo |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

### carts
Carrito de compras. Soporta usuarios autenticados y visitantes (por session_id).

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| user_id | bigint (FK -> users, nullable) | Usuario autenticado |
| session_id | varchar(255) | ID de sesion para visitantes |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

### cart_items
Items dentro de un carrito.

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| cart_id | bigint (FK -> carts) | Carrito al que pertenece |
| product_id | bigint (FK -> products) | Producto |
| quantity | integer | Cantidad solicitada |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

### orders
Pedidos/compras realizadas.

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| user_id | bigint (FK -> users) | Cliente que realizo la compra |
| total | decimal(10,2) | Monto total (productos + envio) |
| status | enum | Estado: `pending`, `paid`, `shipped`, `delivered`, `cancelled` |
| payment_id | varchar(255) | ID de pago (Mercado Pago) |
| shipping_tracking | varchar(255) | Numero de seguimiento (Correo Argentino) |
| shipping_cost | decimal(10,2) | Costo de envio |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

### order_items
Detalle de productos dentro de un pedido.

| Columna | Tipo | Descripcion |
|---------|------|-------------|
| id | bigint (PK) | Identificador unico |
| order_id | bigint (FK -> orders) | Pedido al que pertenece |
| product_id | bigint (FK -> products) | Producto comprado |
| quantity | integer | Cantidad |
| unit_price | decimal(10,2) | Precio unitario al momento de la compra |
| created_at | timestamp | Fecha de creacion |
| updated_at | timestamp | Fecha de actualizacion |

---

## Diagrama de Relaciones

```
users
  |-- 1:1 --> artisans
  |-- 1:N --> orders
  |-- 1:1 --> carts

artisans
  |-- N:M --> categories  (via artisan_category)
  |-- 1:N --> products

categories
  |-- 1:N --> categories  (subcategorias, via parent_id)
  |-- 1:N --> products
  |-- N:M --> artisans    (via artisan_category)

products
  |-- N:1 --> artisans
  |-- N:1 --> categories
  |-- 1:N --> cart_items
  |-- 1:N --> order_items

carts
  |-- N:1 --> users
  |-- 1:N --> cart_items

orders
  |-- N:1 --> users
  |-- 1:N --> order_items
```

## Tablas Auxiliares

| Tabla | Descripcion |
|-------|-------------|
| migrations | Control de migraciones de Laravel |
| password_reset_tokens | Tokens para restablecimiento de contrasena |
| personal_access_tokens | Tokens de autenticacion (Laravel Sanctum) |
| permissions | Permisos (Spatie Permission - disponible, no utilizado aun) |
| roles | Roles Spatie (disponible, no utilizado aun) |
| model_has_permissions | Pivote permisos-modelo (Spatie) |
| model_has_roles | Pivote roles-modelo (Spatie) |
| role_has_permissions | Pivote roles-permisos (Spatie) |
| failed_jobs | Registro de jobs fallidos de Laravel |
