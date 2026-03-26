# Market Artesanos - Registro de Avances

Documento que registra las tareas completadas, correcciones realizadas y mejoras aplicadas durante el desarrollo.

---

## Fase 1 - Minimo viable para vender

### 1.1 Pagina de Checkout (COMPLETADO - 25/03/2026)

**Backend:**
- Migracion: nuevos campos en tabla `orders`: `shipping_name`, `shipping_address`, `shipping_city`, `shipping_province`, `shipping_postal_code`, `shipping_phone`
- Model `Order`: campos agregados al `$fillable`
- `OrderController@checkout`: ahora valida y guarda datos de envio. Retorna pedido con datos del artesano (`items.product.artisan.user`). Llamada a Correo Argentino envuelta en try/catch para no bloquear el pedido si falla la API
- Nuevo metodo `OrderController@show`: obtiene una orden individual con datos del artesano
- Nueva ruta: `GET /api/orders/{id}`

**Frontend:**
- `Checkout.vue` (`/checkout`): formulario de envio (nombre, direccion, ciudad, provincia con selector, CP, telefono), cotizacion de envio con seleccion de opcion, resumen del pedido, aviso de tiempos segun cantidad (>5 unidades = plazo mayor), validacion de campos, pre-fill con datos del usuario logueado
- `OrderConfirmation.vue` (`/pedido-confirmado/:id`): mensaje de exito, datos de contacto del artesano (nombre, telefono con link a WhatsApp, email, ubicacion, productos correspondientes), aviso de tiempos, detalle completo del pedido y direccion de envio
- `Profile.vue`: cada pedido ahora tiene link "Ver detalle y datos del artesano"
- Router: rutas `/checkout` y `/pedido-confirmado/:id` (requieren autenticacion)

### 1.2 Pantalla de confirmacion con datos del artesano + flujo simulado de pago y envio (COMPLETADO - 25/03/2026)

**Flujo simulado de pago:**
- El checkout ahora tiene 2 pasos: formulario de datos de envio -> pantalla de pago simulado
- La pantalla de pago muestra el total, aviso de que PayWay esta en integracion, y boton "Simular Pago"
- Al presionar "Simular Pago" se crea el pedido y redirige a la confirmacion
- Cuando PayWay este integrado, se reemplaza esta pantalla por el formulario de pago real

**Envio simulado (fallback):**
- Si la API de Correo Argentino no responde o no devuelve resultados, el checkout muestra tarifas estimadas simuladas:
  - Envio estandar: $8.500 (5 a 7 dias habiles)
  - Envio expreso: $14.500 (2 a 4 dias habiles)
- Se muestra un aviso indicando que son tarifas estimadas en modo simulacion
- Cuando la API de produccion este disponible, las tarifas reales reemplazaran automaticamente a las simuladas

**Pantalla de confirmacion (ya existia del punto 1.1):**
- `OrderConfirmation.vue` muestra datos de contacto del artesano (nombre, telefono con link a WhatsApp, email, ubicacion)
- Indica que productos del pedido corresponden a cada artesano
- Visible tambien desde "Mis pedidos" en el perfil

**Archivos modificados:** `Checkout.vue`

### 1.3 Gestion de estados de orden desde admin (COMPLETADO - 25/03/2026)

**Backend:**
- Nuevo endpoint `PATCH /api/orders/{id}/status` (solo admin/presidente)
- Acepta `status` (pending, paid, shipped, delivered, cancelled) y `shipping_tracking` opcional
- Validacion de estados permitidos

**Frontend (Dashboard):**
- Seccion de pedidos rediseñada con tarjetas expandibles (click para ver detalle)
- Filtro por estado (Todos, Pendiente, Pagado, Enviado, Entregado, Cancelado)
- Cada pedido expandido muestra: productos con imagen, direccion de envio completa, costo de envio y tracking
- Formulario inline para cambiar estado y asignar numero de seguimiento
- Feedback visual al actualizar ("Estado actualizado correctamente")

**Archivos modificados:** `OrderController.php`, `api.php`, `Dashboard.vue`

### 1.4 Emails transaccionales (COMPLETADO - 25/03/2026)

**Configuracion:**
- Mail driver configurado como `log` para desarrollo (emails van a `storage/logs/laravel.log`)
- From: `asociaciondeartesanosdecatriel@gmail.com` / "Asociacion de Artesanos"
- Cuando se configure un SMTP real (ej: Gmail App Password), cambiar `MAIL_MAILER=smtp` en `.env`

**Emails implementados:**

1. **Confirmacion de pedido al cliente** (`OrderConfirmedMail`)
   - Se envia al hacer checkout
   - Incluye: tabla de productos con precios, costo de envio, total, direccion de envio, datos de contacto de cada artesano (nombre, especialidad, telefono, email, ubicacion), tiempo estimado de entrega

2. **Notificacion al artesano** (`NewOrderForArtisanMail`)
   - Se envia a cada artesano cuyos productos fueron comprados
   - Incluye: productos que le corresponden con cantidades, datos del cliente (nombre, email, telefono), direccion de envio, aviso si cantidad > 5

3. **Cambio de estado del pedido** (`OrderStatusChangedMail`)
   - Se envia al cliente cuando el admin cambia el estado del pedido
   - Incluye: estado anterior y nuevo, mensaje contextual segun el nuevo estado (pagado, enviado con tracking, entregado, cancelado)

**Archivos creados:**
- `app/Mail/OrderConfirmedMail.php`
- `app/Mail/NewOrderForArtisanMail.php`
- `app/Mail/OrderStatusChangedMail.php`
- `resources/views/emails/order-confirmed.blade.php`
- `resources/views/emails/new-order-artisan.blade.php`
- `resources/views/emails/order-status-changed.blade.php`

**Archivos modificados:** `OrderController.php`, `.env`

### 1.5 Recuperar contrasena (COMPLETADO - 25/03/2026)

**Backend:**
- `POST /api/forgot-password`: recibe email, genera token, lo guarda en `password_reset_tokens`, y envia email con link al frontend
- `POST /api/reset-password`: recibe email, token y nueva contrasena. Valida token (expira en 60 min), actualiza password y elimina el token
- Mensaje generico "Si el email existe..." para no revelar si un email esta registrado
- Vista HTML del email con boton estilizado apuntando a `FRONTEND_URL/restablecer-contrasena/:token?email=...`

**Frontend:**
- `ForgotPassword.vue` (`/recuperar-contrasena`): formulario con email, muestra mensaje de exito/error
- `ResetPassword.vue` (`/restablecer-contrasena/:token`): formulario con nueva contrasena + confirmacion, lee token y email de la URL, link al login al completar
- Link "Olvidaste tu contrasena?" agregado en la pagina de login

**Archivos creados:** `ForgotPassword.vue`, `ResetPassword.vue`, `emails/reset-password.blade.php`
**Archivos modificados:** `AuthController.php`, `api.php`, `router/index.js`, `Login.vue`, `.env`, `config/app.php`

### 1.6 Integracion PayWay

**Estado:** En espera de credenciales

### 1.7 Integracion Correo Argentino produccion

**Estado:** En espera de credenciales

---

## Fase 2 - Experiencia completa

### 2.1 Paginas legales (COMPLETADO - 25/03/2026)

**Paginas creadas:**

1. **Terminos y Condiciones** (`/terminos-y-condiciones`): informacion general, productos artesanales bajo demanda, tiempos de elaboracion, precios, envios, cancelaciones, propiedad intelectual
2. **Politica de Privacidad** (`/politica-de-privacidad`): responsable del tratamiento, datos recopilados, finalidad, comparticion con artesanos y Correo Argentino, seguridad, derechos del titular (Ley 25.326), cookies/localStorage
3. **Politica de Devoluciones** (`/politica-de-devoluciones`): derecho de arrepentimiento con excepcion por personalizacion (Ley 24.240 art. 34), productos con defectos, variaciones artesanales, cancelacion pre-elaboracion, procedimiento de reclamo

**Footer:** se agrego columna "Legal" con links a las 3 paginas. Grid reestructurado a 4 columnas.

**Archivos creados:** `views/legal/TermsConditions.vue`, `views/legal/PrivacyPolicy.vue`, `views/legal/ReturnsPolicy.vue`
**Archivos modificados:** `router/index.js`, `AppFooter.vue`

### 2.2 Buscador de productos (COMPLETADO - 25/03/2026)

**Backend:**
- Filtro `search` agregado al endpoint `GET /api/products`. Busca en `name` y `description` con `LIKE`. Compatible con los filtros existentes (category_id, featured).

**Frontend:**
- Barra de busqueda agregada en el catalogo, arriba del grid de productos
- Busqueda con debounce de 400ms (no dispara request en cada tecla)
- Boton X para limpiar la busqueda
- Query param `?buscar=texto` en la URL para mantener estado al navegar
- Mensaje "No se encontraron productos" actualizado segun si hay busqueda activa o filtro de categoria

**Archivos modificados:** `ProductController.php`, `Catalog.vue`

### 2.3 Pagina de contacto con formulario (COMPLETADO - 25/03/2026)

**Backend:**
- Nuevo `ContactController@send` con endpoint `POST /api/contact`
- Valida nombre, email, asunto y mensaje
- Envia email a `asociaciondeartesanosdecatriel@gmail.com` con reply-to al email del visitante
- Vista HTML del email con los datos del formulario

**Frontend:**
- `Contact.vue` (`/contacto`): formulario (nombre, email, asunto, mensaje), info de contacto (ubicacion, email), iconos de redes sociales (Facebook e Instagram con links reales)
- Link "Contacto" agregado en el navbar y en el footer
- Footer actualizado con iconos de Facebook e Instagram

**Redes sociales:**
- Facebook: https://www.facebook.com/share/1DnUUAh8ye/
- Instagram: @asociacionartesanoscatriel

**Archivos creados:** `ContactController.php`, `Contact.vue`, `emails/contact.blade.php`
**Archivos modificados:** `api.php`, `router/index.js`, `AppNavbar.vue`, `AppFooter.vue`

### 2.4 Completar CategoryController + ABM en dashboard (COMPLETADO - 25/03/2026)

**Backend:**
- `CategoryController@update`: edita nombre, descripcion, parent_id. Genera slug automatico.
- `CategoryController@destroy`: elimina categoria. Protegido: no permite eliminar si tiene productos o subcategorias asociadas.
- `CategoryController@store`: corregido para aceptar `parent_id` (antes no se podian crear subcategorias).

**Frontend (Dashboard):**
- Nueva seccion "Categorias" en el sidebar del panel admin
- Vista jerarquica: categorias principales con sus subcategorias anidadas
- Crear categoria principal o subcategoria (boton "+ Agregar subcategoria" dentro de cada padre)
- Editar y eliminar categorias con modal
- Selector de categoria padre en el formulario
- Proteccion: si se intenta eliminar una categoria con productos o subcategorias, muestra mensaje de error del backend

**Archivos modificados:** `CategoryController.php`, `Dashboard.vue`

### 2.5 Paginacion en el catalogo frontend (COMPLETADO - 26/03/2026)

**Implementacion:**
- Controles de paginacion debajo del grid de productos (flechas prev/next + numeros de pagina)
- Paginas visibles inteligentes: muestra 1, ..., paginas cercanas, ..., ultima (para no saturar con muchos numeros)
- Pagina actual resaltada con estilo activo
- Al cambiar categoria o busqueda, se resetea automaticamente a pagina 1
- Estado persistido en la URL (`?pagina=2`) para compartir links
- Scroll al inicio al cambiar de pagina
- El backend ya paginaba a 15 productos por pagina, solo faltaba el frontend

**Archivos modificados:** `Catalog.vue`

### 2.6 Verificacion de email (COMPLETADO - 26/03/2026)

**Backend:**
- `AuthController@sendVerificationEmail`: genera token con hash(id + email + app_key), envia email con link al frontend
- `POST /api/verify-email`: recibe id y token, valida, marca `email_verified_at`
- `POST /api/resend-verification`: reenvía email de verificacion (autenticado)
- `OrderController@checkout`: bloquea la compra si `email_verified_at` es null (retorna 403)
- El email de verificacion se envia automaticamente al registrarse

**Frontend:**
- `VerifyEmail.vue` (`/verificar-email?id=X&token=Y`): procesa el link del email, muestra exito o error, actualiza el usuario local
- `Checkout.vue`: banner rojo si email no verificado, con boton "Reenviar email de verificacion". Boton de pago deshabilitado
- `Profile.vue`: indicador "Email no verificado" con boton de reenvio, o "Email verificado" en verde

**Archivos creados:** `VerifyEmail.vue`, `emails/verify-email.blade.php`
**Archivos modificados:** `AuthController.php`, `OrderController.php`, `api.php`, `router/index.js`, `Checkout.vue`, `Profile.vue`

---

## Fase 3 - Mejoras de conversion

### 3.1 FAQ / Preguntas frecuentes (COMPLETADO - 26/03/2026)

Pagina `/preguntas-frecuentes` con 11 preguntas en acordeon expandible. Temas: productos a pedido, tiempos, personalizacion, envios, cancelaciones, defectos, medios de pago. Link a contacto al final. Agregado al footer.

### 3.2 SEO basico (COMPLETADO - 26/03/2026)

- `index.html`: lang="es", meta description, keywords, Open Graph tags, favicon con logo
- Titulos dinamicos por pagina via `router.afterEach` ("Catalogo | Artesanos de Catriel")

### 3.3 Sistema de resenas/valoraciones (COMPLETADO - 26/03/2026)

- Modelo `Review` (user_id, product_id, rating 1-5, comment). Una resena por usuario por producto.
- Endpoints: `GET /products/{id}/reviews`, `POST /products/{id}/reviews` (auth), `DELETE /reviews/{id}`
- ProductDetail: promedio de estrellas, formulario con estrellas clickeables + comentario, lista de resenas con nombre, fecha y rating

### 3.4 Lista de deseos / Wishlist (COMPLETADO - 26/03/2026)

- Modelo `Wishlist` (user_id, product_id, unique)
- Endpoints: `GET /wishlist`, `POST /wishlist/toggle` (auth)
- Store Pinia `wishlist.js` con fetch, toggle, isInWishlist
- Boton de corazon en cada ProductCard (relleno rojo si esta en favoritos)
- Pagina `/favoritos` con grid de productos favoritos y boton para quitar
- Se carga automaticamente al iniciar si el usuario esta autenticado

### 3.5 Cupones y descuentos

**Estado:** Pendiente (requiere definicion de reglas de negocio)

### 3.6 Compartir producto en redes (COMPLETADO - 26/03/2026)

Botones de WhatsApp y Facebook en ProductDetail para compartir el link del producto.

### 3.7 Limpieza de carritos abandonados (COMPLETADO - 26/03/2026)

Comando artisan `php artisan carts:clean --days=7`. Elimina carritos de invitado sin actividad por X dias. Puede programarse con cron.

### 3.8 Prevenir doble click (COMPLETADO - 26/03/2026)

Todos los botones de accion ya tienen `:disabled` con variable de estado (`loading`, `processing`, `saving`, etc.) que se activa mientras se procesa la solicitud.

### 3.9 Ojito para visualizar contrasena (COMPLETADO - 26/03/2026)

Componente `PasswordInput.vue` reutilizable con toggle mostrar/ocultar. Aplicado en Login, Register y Profile.

### 3.10 Reemplazar alert() nativos por toasts (COMPLETADO - 26/03/2026)

- Store `toast.js` con show(), success(), error(), info()
- Componente `AppToast.vue` fijo en esquina superior derecha con animacion slide-in
- Estilos por tipo: verde (success), rojo (error), marron (info)
- Reemplazados todos los `alert()` de Dashboard.vue y ProductDetail.vue

### 3.11 Imagenes optimizadas

**Estado:** Pendiente

---

## Correcciones y mejoras aplicadas

### Fix: Admin/Presidente veia pedidos de otros usuarios en Mi Perfil (25/03/2026)

**Problema:** El endpoint `GET /api/orders` devolvia todas las ordenes si el usuario era admin o presidente. Esto causaba que en "Mi Perfil" se mostraran pedidos de otros clientes como si fueran propios.

**Solucion:** El endpoint ahora siempre filtra por `user_id` del usuario logueado (para el perfil). Se agrego el parametro `?scope=all` que solo funciona para admin/presidente y se usa exclusivamente en el dashboard para ver todas las ordenes.

**Archivos modificados:** `OrderController.php`, `Dashboard.vue`

---

### Fix: CorreoArgentinoService crasheaba sin credenciales (25/03/2026)

**Problema:** Al simular el pago, el `OrderController@checkout` instanciaba `CorreoArgentinoService` que asignaba `null` a propiedades tipadas como `string` (`$apiKey`, `$agreement`) porque no habia credenciales en el `.env`.

**Solucion:** Cast a `(string)` en el constructor del servicio para que use string vacio cuando las credenciales no estan configuradas. El try/catch del checkout ya maneja el fallo de la API silenciosamente.

**Archivos modificados:** `CorreoArgentinoService.php`

---

### Fusion de carrito invitado -> usuario (25/03/2026)

**Problema:** Un visitante agregaba productos al carrito (guardados con `session_id`). Al hacer login o registro, se creaba un carrito nuevo vacio y los productos del carrito de invitado se perdian.

**Solucion:** Nuevo metodo `AuthController@mergeGuestCart` que se ejecuta en login y register. Busca el carrito de invitado por `session_id` (header `X-Session-Id`) y mueve los items al carrito del usuario. Si el producto ya existia, suma cantidades. Luego elimina el carrito de invitado.

**Archivos modificados:** `AuthController.php`

---

### Contador de carrito en el navbar (25/03/2026)

**Problema:** El icono del carrito en el navbar no mostraba la cantidad de items cargados.

**Solucion:**
- Nuevo store `stores/cart.js` (Pinia) con estado `count` y acciones `fetchCount()` / `clear()`
- `AppNavbar.vue`: badge amarillo sobre el icono del carrito con la cantidad total
- `ProductDetail.vue`, `Cart.vue`, `Checkout.vue`: actualizan el contador al agregar/modificar/eliminar items o confirmar pedido
- `Login.vue` y `Register.vue`: refrescan el contador despues de autenticarse (solucionaba que al re-loguearse no aparecia el badge)

**Archivos modificados:** `AppNavbar.vue`, `ProductDetail.vue`, `Cart.vue`, `Checkout.vue`, `Login.vue`, `Register.vue`
**Archivos creados:** `stores/cart.js`

---

### Error de registro no mostraba detalle (25/03/2026)

**Problema:** Al fallar el registro (ej: contrasena menor a 8 caracteres), solo aparecia un `alert("Error al registrarse")` generico sin indicar el motivo.

**Solucion:** Se reemplazo el `alert()` por un mensaje inline que muestra los errores de validacion del backend (ej: "La contrasena debe tener al menos 8 caracteres"). Mismo tratamiento aplicado al login.

**Archivos modificados:** `Register.vue`, `Login.vue`

---

### Variable de entorno del frontend apuntaba a IP invalida (25/03/2026)

**Problema:** El archivo `frontend/.env` tenia `VITE_API_URL=http://172.26.2.64:8000/api` (IP que ya no respondia). El frontend no podia comunicarse con el backend.

**Solucion:** Se cambio a `http://127.0.0.1:8000/api`. Requirio reinicio del servidor Vite.

**Archivos modificados:** `frontend/.env`

---

### Documentacion de roles actualizada (25/03/2026)

Se actualizo la seccion 3.6 del documento Estado-del-Proyecto.md con la tabla detallada de roles:
- `admin`: administrador de sistemas / informatico, acceso total para pruebas
- `presidente`: representante de la asociacion, opera el sistema dia a dia (carga artesanos, productos, categorias, gestiona ordenes)
- `cliente`: usuario registrado, puede comprar
- Visitante sin registro: puede navegar pero debe registrarse para comprar

---

### Nuevos items en pendientes deseables (25/03/2026)

Se agregaron a la seccion 7 del documento Estado-del-Proyecto.md:
- #9: Prevenir doble click en botones de accion
- #10: Ojito para visualizar contrasena en formularios
- #11: Reemplazar alert() nativos por sistema de notificaciones/toasts de Vue
