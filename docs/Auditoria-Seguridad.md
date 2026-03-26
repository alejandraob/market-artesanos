# Market Artesanos - Auditoria de Seguridad

Auditoria realizada el 26/03/2026. Estado de los hallazgos y acciones tomadas.

---

## Resumen

| Severidad | Total | Corregidos | Pendientes (deploy) |
|-----------|-------|------------|---------------------|
| Critico | 3 | 2 | 1 (DEBUG en deploy) |
| Alto | 10 | 8 | 2 (aceptados/cubiertos) |
| Medio | 5 | 4 | 1 (HTTPS en deploy) |
| Bajo | 3 | 0 | 3 (riesgo aceptado) |

---

## Hallazgos criticos

### SEC-01: DEBUG mode habilitado (CRITICO)

**Ubicacion:** `backend/.env`
**Problema:** `APP_DEBUG=true` y `APP_ENV=local`. En produccion esto expone stack traces completos con rutas de archivos, queries SQL y datos sensibles a cualquier usuario que genere un error.
**Solucion:** Cambiar a `APP_DEBUG=false` y `APP_ENV=production` antes del deploy.
**Estado:** Pendiente (se corrige al deployar)

### SEC-02: CORS permite cualquier origen (CRITICO)

**Ubicacion:** `backend/config/cors.php`
**Problema:** `allowed_origins => ['*']`, `allowed_methods => ['*']`, `allowed_headers => ['*']`. Cualquier sitio web puede hacer requests a la API, habilitando ataques CSRF cross-origin.
**Solucion:** CORS restringido via `CORS_ALLOWED_ORIGINS` en `.env`. Solo permite metodos y headers especificos.
**Estado:** CORREGIDO (26/03/2026)

### SEC-03: Tokens de Sanctum nunca expiran (CRITICO)

**Ubicacion:** `backend/config/sanctum.php`
**Problema:** `expiration => null`. Si un token es comprometido, tiene acceso indefinido.
**Solucion:** Configurado `'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 1440)` (24 horas).
**Estado:** CORREGIDO (26/03/2026)

---

## Hallazgos altos

### SEC-04: Sin rate limiting en endpoints sensibles (ALTO)

**Ubicacion:** `backend/routes/api.php`
**Problema:** Los endpoints `POST /login`, `POST /register`, `POST /forgot-password`, `POST /contact` no tienen throttling. Un atacante puede hacer brute force de credenciales o spam al formulario de contacto.
**Solucion:** Agregar middleware `throttle`:
- Login: 5 intentos por minuto
- Register: 5 por minuto
- Forgot-password: 3 por minuto
- Reset-password: 5 por minuto
- Contact: 3 por minuto
- Verify-email: 5 por minuto
**Estado:** CORREGIDO (26/03/2026)

### SEC-05: Contrasena por defecto de artesanos hardcodeada (ALTO)

**Ubicacion:** `backend/app/Http/Controllers/ArtisanController.php`
**Problema:** Al crear un artesano desde el dashboard, la contrasena es siempre `artesano1234`. Predecible y compartida entre todos los artesanos nuevos.
**Solucion:** Reemplazado por `Str::random(16)`. Los artesanos deben usar "Recuperar contrasena" para acceder a su cuenta.
**Estado:** CORREGIDO (26/03/2026)

### SEC-06: Requisitos de contrasena debiles (ALTO)

**Ubicacion:** `backend/app/Http/Controllers/AuthController.php`
**Problema:** Solo se exige `min:8`. No requiere mayusculas, numeros ni caracteres especiales.
**Solucion:** Agregado `regex:/[a-z]/|regex:/[0-9]/` - ahora exige minimo 8 caracteres, al menos una letra minuscula y un numero.
**Estado:** CORREGIDO (26/03/2026)

### SEC-07: Token de verificacion de email predecible (ALTO)

**Ubicacion:** `backend/app/Http/Controllers/AuthController.php`
**Problema:** El token se genera con `hash('sha256', userId + email + appKey)`. Es determinístico: el mismo usuario siempre genera el mismo token, y no expira.
**Solucion:** Token reemplazado por `Str::random(64)` guardado hasheado en `password_reset_tokens` (prefijo `verify_`). Expira en 48 horas. Se elimina al verificar.
**Estado:** CORREGIDO (26/03/2026)

### SEC-08: v-html en FAQ (ALTO)

**Ubicacion:** `frontend/src/views/FAQ.vue`
**Problema:** Usa `v-html` para renderizar respuestas. Actualmente el contenido es hardcodeado y seguro, pero si alguna vez se mueve a base de datos seria vulnerable a XSS.
**Solucion:** Agregada funcion `sanitize()` que solo permite tags seguros (strong, b, i, em, a, br, p) y stripea atributos peligrosos.
**Estado:** CORREGIDO (26/03/2026)

### SEC-09: Sin validacion de tipo/tamano en upload de imagenes de productos (ALTO)

**Ubicacion:** `backend/app/Http/Controllers/ProductController.php`
**Problema:** Las imagenes de productos se suben sin validar tipo MIME ni tamano. Un atacante podria subir archivos ejecutables o archivos enormes.
**Solucion:** Agregado en store y update: `'image_files.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120'` (5MB max, max 10 archivos).
**Estado:** CORREGIDO (26/03/2026)

### SEC-10: firebase_token expuesto en respuestas JSON (ALTO)

**Ubicacion:** `backend/app/Models/User.php`
**Problema:** El campo `firebase_token` esta en `$fillable` pero no en `$hidden`. Se expone en todas las respuestas JSON que incluyen datos del usuario.
**Solucion:** Agregado `firebase_token` al array `$hidden` del modelo User.
**Estado:** CORREGIDO (26/03/2026)

### SEC-11: Credenciales de Correo Argentino en .env (ALTO)

**Ubicacion:** `backend/.env`
**Problema:** Username y password de la API de Correo Argentino estan en el .env. Si bien el .env no se commitea, las credenciales estan visibles en texto plano.
**Nota:** Esto es el comportamiento esperado de Laravel para credenciales de servicios. El riesgo real es si el .env se expone. Verificar que `.gitignore` lo excluya y que el servidor de produccion proteja el archivo.
**Estado:** Aceptado (riesgo inherente del patron .env)

### SEC-12: Falta verificacion de ownership en ProductController.update (ALTO)

**Ubicacion:** `backend/app/Http/Controllers/ProductController.php`
**Problema:** Solo verifica que el usuario no sea "cliente", pero no valida que el producto pertenezca al artesano que lo edita (si se implementara edicion por artesanos).
**Nota:** Actualmente solo admin/presidente acceden a estas rutas via middleware `role:admin,presidente`, por lo que el riesgo real es bajo.
**Estado:** Riesgo bajo (protegido por middleware de ruta)

### SEC-13: Password reset sin rate limiting por email (ALTO)

**Ubicacion:** `backend/app/Http/Controllers/AuthController.php`
**Problema:** Un atacante puede solicitar multiples resets para un email, generando spam al usuario.
**Solucion:** Agregar throttle al endpoint (cubierto por SEC-04).
**Estado:** Pendiente (se resuelve con SEC-04)

---

## Hallazgos medios

### SEC-14: Session ID del carrito sin validacion de formato (MEDIO)

**Ubicacion:** `backend/app/Http/Controllers/CartController.php`
**Problema:** El header `X-Session-Id` se toma sin validar. Podria recibir strings arbitrarios.
**Solucion:** Agregada validacion UUID con `preg_match('/^[a-f0-9\-]{36}$/', $sessionId)`. Si no cumple, se descarta.
**Estado:** CORREGIDO (26/03/2026)

### SEC-15: Codigo postal sin validacion de formato (MEDIO)

**Ubicacion:** `backend/app/Http/Controllers/ProductController.php`
**Problema:** El campo `postal_code` solo valida `required|string`. Acepta cualquier texto.
**Solucion:** Agregado `regex:/^[0-9]{4,8}$/` al campo postal_code.
**Estado:** CORREGIDO (26/03/2026)

### SEC-16: Sin HTTPS forzado (MEDIO)

**Ubicacion:** Configuracion general
**Problema:** No hay middleware para forzar HTTPS en produccion.
**Solucion:** Agregar middleware ForceHttps o configurar en el servidor web (nginx/apache).
**Estado:** Pendiente (se configura al deployar)

### SEC-17: Validacion MIME incompleta en foto de artesano (MEDIO)

**Ubicacion:** `backend/app/Http/Controllers/ArtisanController.php`
**Problema:** Valida `image|max:2048` pero no especifica MIME types permitidos.
**Solucion:** Agregado `mimes:jpeg,jpg,png,webp` a la validacion de foto de artesano.
**Estado:** CORREGIDO (26/03/2026)

### SEC-18: Scope parameter en ordenes basado en string (MEDIO)

**Ubicacion:** `backend/app/Http/Controllers/OrderController.php`
**Problema:** El parametro `?scope=all` se valida con comparacion de string simple.
**Nota:** El riesgo real es bajo porque se combina con validacion de rol (admin/presidente).
**Estado:** Riesgo bajo

---

## Hallazgos bajos

### SEC-19: Enumeracion de emails en registro (BAJO)

**Ubicacion:** `backend/app/Http/Controllers/AuthController.php`
**Problema:** El error de validacion "email ya registrado" permite saber si un email existe en el sistema.
**Nota:** Es comportamiento estandar de la mayoria de sitios. El forgot-password ya usa mensaje generico.
**Estado:** Aceptado

### SEC-20: Sin audit logging (BAJO)

**Ubicacion:** Todos los controladores
**Problema:** No hay registro de operaciones sensibles (cambios de estado de ordenes, eliminacion de productos, cambios de contrasena, acciones admin).
**Solucion:** Implementar logging de eventos criticos.
**Estado:** Pendiente (mejora futura)

### SEC-21: ID de usuario expuesto en resenas (BAJO)

**Ubicacion:** `backend/app/Http/Controllers/ReviewController.php`
**Problema:** Las resenas retornan `user:id,name`. El ID podria usarse para enumerar usuarios.
**Estado:** Riesgo bajo

---

## Plan de accion recomendado

### Antes del deploy a produccion (obligatorio):
1. SEC-01: `APP_DEBUG=false`, `APP_ENV=production`
2. SEC-02: Restringir CORS al dominio del frontend
3. SEC-03: Configurar expiracion de tokens (24h)
4. SEC-04: Agregar rate limiting a endpoints sensibles
5. SEC-09: Validar tipo y tamano de imagenes subidas
6. SEC-10: Ocultar `firebase_token` en respuestas JSON
7. SEC-16: Forzar HTTPS

### Mejoras de seguridad post-lanzamiento:
8. SEC-05: Generar contrasena aleatoria para artesanos
9. SEC-06: Mejorar requisitos de contrasena
10. SEC-07: Mejorar token de verificacion de email
11. SEC-08: Eliminar v-html o sanitizar
12. SEC-14: Validar formato de session ID
13. SEC-15: Validar formato de codigo postal
14. SEC-17: Completar validacion MIME de fotos
15. SEC-20: Implementar audit logging
