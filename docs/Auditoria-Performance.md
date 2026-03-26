# Market Artesanos - Auditoria de Performance (Lighthouse)

Auditoria realizada el 26/03/2026 con Google Lighthouse sobre `http://localhost:5173`.

---

## Resultados por pagina

### Home (`/`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **57** | Naranja |
| Accessibility | 88 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 92 | Verde |
| FCP | 4.4s | Rojo |
| LCP | 5.1s | Rojo |
| TBT | 0ms | Verde |
| CLS | 0 | Verde |
| Speed Index | 4.4s | Rojo |

### Catalogo (`/catalogo`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **69** | Naranja |
| Accessibility | 85 | Naranja |
| Best Practices | 96 | Verde |
| SEO | 92 | Verde |
| FCP | 1.4s | Naranja |
| LCP | 3.7s | Rojo |
| TBT | 0ms | Verde |
| CLS | 0.13 | Naranja |
| Speed Index | 1.8s | Naranja |

### Nosotros (`/nosotros`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **58** | Naranja |
| Accessibility | 77 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 83 | Naranja |
| FCP | 1.6s | Naranja |
| LCP | 2.2s | Naranja |
| TBT | 0ms | Verde |
| CLS | 0.767 | Rojo |
| Speed Index | 1.6s | Naranja |

### Contacto (`/contacto`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **61** | Naranja |
| Accessibility | 89 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 92 | Verde |
| FCP | 1.5s | Naranja |
| LCP | 2.0s | Naranja |
| TBT | 0ms | Verde |
| CLS | 0.683 | Rojo |
| Speed Index | 1.5s | Naranja |

### Pedido Confirmado (`/pedido-confirmado/:id`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **54** | Naranja |
| Accessibility | 71 | Naranja |
| Best Practices | 96 | Verde |
| SEO | 83 | Naranja |
| FCP | 1.6s | Naranja |
| LCP | 3.0s | Rojo |
| TBT | 30ms | Verde |
| CLS | 0.589 | Rojo |
| Speed Index | 1.6s | Naranja |

**Problemas especificos de Pedido Confirmado:**
- Imagenes de productos sin `alt`
- Contraste: fecha en text-green-600, telefonos y emails de artesanos en text-artisan-accent, "Productos en tu pedido" en text-gray-400
- CLS 0.589 por footer
- Network payload: 2,860 KiB (imagenes sin cache)

### Checkout (`/checkout`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **53** | Naranja |
| Accessibility | 70 | Naranja |
| Best Practices | 96 | Verde |
| SEO | 83 | Naranja |
| FCP | 1.6s | Naranja |
| LCP | 3.3s | Rojo |
| TBT | 10ms | Verde |
| CLS | 0.589 | Rojo |
| Speed Index | 1.6s | Naranja |

**Problemas especificos de Checkout:**
- Imagenes de productos sin `alt` en resumen del pedido
- Select de provincia sin label asociado
- Doble llamada a `/api/cart` (navbar + checkout)
- Contraste: textos gray-400 (por calcular, cantidades x1, "Calcula el envio...", "Volver al carrito" en text-artisan-accent)
- Network payload total: 2,909 KiB (imagenes de productos sin cache)
- CLS 0.589 por footer

### Carrito (`/carrito`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **64** | Naranja |
| Accessibility | 82 | Naranja |
| Best Practices | 96 | Verde |
| SEO | 83 | Naranja |
| FCP | 1.5s | Naranja |
| LCP | 2.9s | Rojo |
| TBT | 0ms | Verde |
| CLS | 0.264 | Rojo |
| Speed Index | 1.5s | Naranja |

**Problemas especificos de Carrito:**
- Imagenes de productos sin `alt` attribute
- Imagenes enormes para thumbnails: 837 KiB (1229x1230) mostrada a 96px, 444 KiB (1281x1280) mostrada a 96px. Total 1,282 KiB sin cache.
- Contraste: precios en text-artisan-accent, boton "Eliminar" en text-red-500
- Headings desordenados: h3 para nombre producto despues de h1
- CLS 0.264 por footer
- Doble llamada a `/api/cart` (navbar + pagina)
- robots.txt invalido

**Bug encontrado:** Al navegar desde el detalle del producto al carrito via navbar, la pagina no carga. Errores en consola:
- `[Vue warn]: Component inside <Transition> renders non-element root node` - ProductDetail.vue tiene multiples nodos raiz que no son compatibles con `<Transition>`
- Se necesita recargar la pagina (F5) para que funcione

### Detalle de Producto (`/producto/:slug`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **35** | Rojo |
| Accessibility | 70 | Naranja |
| Best Practices | 96 | Verde |
| SEO | 83 | Naranja |
| FCP | 4.6s | Rojo |
| LCP | 6.2s | Rojo |
| TBT | 10ms | Verde |
| CLS | 0.554 | Rojo |
| Speed Index | 4.6s | Rojo |

**Problemas especificos de Detalle Producto:**
- LCP 6.2s: la imagen del producto pesa 444 KiB (1281x1920) mostrada a 185x278. Sin cache, sin WebP. Es el LCP element.
- Imagen del producto necesita `fetchpriority="high"` (es el LCP)
- Thumbnails de galeria sin `alt` attribute
- Botones de thumbnails sin nombre accesible
- Botones compartir (WhatsApp, Facebook) sin `aria-label`
- Contraste: "Por Atencio Carla" (text-artisan-accent), "COMPARTIR:" (gray-400), "Aun no hay resenas" (gray-400)
- CLS 0.554 por footer
- robots.txt: Vite sirve el index.html como robots.txt (22 errores). Necesita archivo real en `/public/robots.txt`
- SEO 83: falta meta description dinamica por producto

### Terminos y Condiciones (`/terminos-y-condiciones`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **61** | Naranja |
| Accessibility | 88 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 92 | Verde |
| FCP | 1.5s | Naranja |
| LCP | 2.0s | Naranja |
| TBT | 0ms | Verde |
| CLS | 0.771 | Rojo |
| Speed Index | 1.5s | Naranja |

**Problemas especificos:** Mismos problemas globales (CLS footer, contraste, links sin nombre, headings). Sin problemas propios adicionales.

### Preguntas Frecuentes (`/preguntas-frecuentes`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **60** | Naranja |
| Accessibility | 88 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 92 | Verde |
| FCP | 1.5s | Naranja |
| LCP | 2.1s | Naranja |
| TBT | 0ms | Verde |
| CLS | 0.771 | Rojo |
| Speed Index | 1.5s | Naranja |

**Problemas especificos de FAQ:**
- CLS 0.771 (el peor del sitio) causado por footer (logo sin dimensiones)
- Contraste insuficiente: subtitulo gray-500, texto de la seccion "No encontraste...", textos footer
- Links sin nombre: carrito, redes footer
- Headings desordenados: h4 en footer

### Login (`/login`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **69** | Naranja |
| Accessibility | 73 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 92 | Verde |
| FCP | 1.6s | Naranja |
| LCP | 1.9s | Naranja |
| TBT | 0ms | Verde |
| CLS | 0.318 | Rojo |
| Speed Index | 1.6s | Naranja |

**Problemas especificos de Login:**
- CLS 0.318 causado por footer (logo sin dimensiones)
- Accessibility 73:
  - Boton ojito sin `aria-label`
  - Input de contrasena (PasswordInput) sin label asociado
  - Touch target del ojito demasiado pequeno
  - Contraste insuficiente: link "Olvidaste tu contrasena?" (text-artisan-accent), textos footer
  - Links sin nombre: carrito, redes footer
  - Headings desordenados: h4 en footer
- Logo navbar: falta `fetchpriority="high"`
- Latencia critica: 668ms (la mas baja del sitio, pocas API calls)

### Mi Perfil (`/mi-perfil`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **61** | Naranja |
| Accessibility | 61 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 92 | Verde |
| FCP | 1.6s | Naranja |
| LCP | 1.9s | Naranja |
| TBT | 0ms | Verde |
| CLS | 0.678 | Rojo |
| Speed Index | 1.6s | Naranja |

**Problemas especificos de Mi Perfil:**
- CLS 0.678 causado por el footer (logo sin dimensiones)
- Accessibility 61 (el peor del sitio):
  - Botones del ojito de contrasena sin `aria-label`
  - Inputs de contrasena sin labels asociados (el PasswordInput no propaga el label)
  - Touch targets del ojito demasiado pequenos
  - Contraste insuficiente en: rol "PRESIDENTE" (text-artisan-accent), "Email verificado" (text-green-600), fecha "Miembro desde" (text-gray-400), placeholder contrasena
  - Links sin nombre: carrito, redes footer
  - Headings desordenados: h3 y h4 despues de h1

### Dashboard (`/dashboard`)
| Metrica | Valor | Score |
|---------|-------|-------|
| Performance | **34** | Rojo |
| Accessibility | 88 | Naranja |
| Best Practices | 100 | Verde |
| SEO | 92 | Verde |
| FCP | 4.9s | Rojo |
| LCP | 5.9s | Rojo |
| TBT | 0ms | Verde |
| CLS | 0.624 | Rojo |
| Speed Index | 4.9s | Rojo |

**Nota:** Score de 34 obtenido navegando desde la seccion de artesanos (carga pesada). El dashboard es la pagina mas lenta del sitio por la cantidad de datos que carga (8 API calls en cascada + Dashboard.vue pesa 237 KiB).

**Problemas especificos de Dashboard:**
- CLS 0.624 causado por el footer (logo sin dimensiones)
- Dashboard.vue pesa 237 KiB (archivo mas grande del proyecto)
- 8 llamadas API en cascada (artisans, products, categories, clients, orders, wishlist, user, cart)
- Latencia maxima de cadena critica: 2,649ms
- Contraste insuficiente en: badges del sidebar, textos gray-400 de KPIs, fecha de pedidos, textos del footer
- Links sin nombre accesible: carrito navbar, redes sociales footer
- Headings desordenados: h3 en panel despues de h1
- Logo navbar sin fetchpriority=high
- Imagenes sin width/height explicitos

---

**Problemas especificos de Contacto:**
- CLS 0.683 causado por el footer (imagen del logo en el footer causa layout shift)
- Contraste insuficiente en: etiqueta de rol en navbar (text-artisan-accent sobre fondo claro), textos grises sobre fondo artisan-bg, textos en footer
- Links sin nombre accesible: icono del carrito (navbar), iconos de redes sociales (contacto y footer)
- Headings desordenados: h4 en footer despues de h2 en contenido
- robots.txt invalido (22 errores)
- Image delivery: logo sin optimizar (69 KiB ahorro estimado)

---

## Problemas principales identificados

### 1. Imagenes sin optimizar (ahorro estimado: 1,276 KiB)

**Problema:** Las imagenes se sirven en formato original (JPG/PNG) sin redimensionar ni convertir.
- `logo-sinfondo.png`: 70.2 KiB, se muestra a 80x73 pero el archivo es 674x612
- `manos-bordado.jpg`: 64.1 KiB, se muestra a 736x507 pero el archivo es 936x489

**Solucion:**
- Convertir imagenes estaticas a WebP (logo, hero, fondo)
- Redimensionar al tamano real de visualizacion
- Para imagenes de productos (subidas por admin): implementar redimensionado automatico en el backend al subir (Intervention Image)
- Usar `srcset` para servir diferentes tamanos segun pantalla

**Estado:** Pendiente

### 2. JavaScript sin minificar (ahorro estimado: 621 KiB)

**Problema:** En modo desarrollo (`npm run dev`), Vite sirve JS sin minificar. Los archivos mas grandes:
- `@vite/client`: 178 KiB
- `chunk-JVJKII2E.js` (Vue runtime): 374 KiB
- `pinia.js`: 167 KiB
- `vue-router.js`: 198 KiB

**Solucion:** Esto se resuelve automaticamente al hacer `npm run build` para produccion. Vite aplica minificacion, tree-shaking y code-splitting.

**Estado:** Se resuelve en el build de produccion (no requiere accion adicional)

### 3. JavaScript no utilizado (ahorro estimado: 427 KiB)

**Problema:** Se cargan modulos completos aunque solo se usen partes:
- Vue devtools (solo desarrollo): 109 KiB sin usar
- Vue runtime: 113 KiB sin usar de 249 KiB total
- Pinia devtools: 99 KiB sin usar
- vue-router devtools: 60 KiB sin usar

**Solucion:** La mayor parte es codigo de devtools que se elimina en produccion con `npm run build`. Para optimizar mas:
- Lazy loading de rutas (ya implementado en la mayoria)
- Dynamic imports para componentes pesados

**Estado:** Se resuelve en el build de produccion

### 4. Cache ineficiente (ahorro estimado: 1,282 KiB)

**Problema:** Los recursos no tienen headers de cache adecuados.

**Solucion:** Configurar headers de cache en el servidor de produccion:
- Archivos estaticos con hash (JS, CSS): `Cache-Control: max-age=31536000, immutable`
- Imagenes: `Cache-Control: max-age=86400`
- HTML: `Cache-Control: no-cache`

**Estado:** Pendiente (se configura en el servidor de produccion)

### 5. Cadena de requests criticos (latencia maxima: 2,397ms)

**Problema:** La cadena de carga es:
```
HTML -> main.js -> App.vue -> auth.js -> api.js -> axios.js -> /api/products (2,397ms)
                                                             -> /api/categories (2,239ms)
                                                             -> /api/wishlist (2,080ms)
                                                             -> /api/user (1,854ms)
                                                             -> /api/cart (1,641ms)
```
Se hacen 5 llamadas a la API en cascada al cargar la pagina.

**Solucion:**
- Precargar datos criticos (categories, featured products) en el HTML inicial (SSR parcial) - complejo
- Reducir llamadas: combinar endpoints o cargar bajo demanda
- La wishlist y el user solo deberian cargarse si hay token (ya implementado parcialmente)
- Usar `<link rel="preconnect" href="http://127.0.0.1:8000">` para iniciar conexion al backend antes

**Estado:** Pendiente (mejoras incrementales)

### 6. Layout Shift en Nosotros (CLS: 0.767)

**Problema:** Pagina Nosotros tiene un CLS muy alto. Las imagenes de artesanos y la foto principal causan saltos de layout al cargarse porque no tienen dimensiones reservadas.

**Solucion:**
- Agregar `width` y `height` explicitos a las imagenes
- Usar `aspect-ratio` en CSS para reservar espacio antes de que cargue la imagen
- Aplicar esqueletos de carga (skeleton) mientras cargan las imagenes

**Estado:** Parcialmente resuelto (logo footer/navbar con dimensiones, falta imagenes de Nosotros)

### 7. Layout Shift en Catalogo (CLS: 0.13)

**Problema:** Las tarjetas de productos causan un shift menor al cargarse.

**Solucion:**
- Las ProductCard ya tienen `aspect-[4/5]` que deberia reservar espacio
- Verificar que el buscador y las categorias no causen reflow al cargar datos

**Estado:** Pendiente (CLS bajo, prioridad baja)

### 8. Layout Shift global por footer (CLS: 0.3-0.77)

**Problema:** El logo del footer sin dimensiones causaba layout shift en TODAS las paginas (0.3 a 0.77).

**Solucion:** Agregado `width="56" height="56"` al logo del footer y `width="80" height="80" fetchpriority="high"` al logo del navbar.

**Estado:** CORREGIDO (26/03/2026)

### 9. Bug de navegacion ProductDetail (Transition)

**Problema:** Al navegar desde ProductDetail a otra pagina, el carrito no cargaba. ProductDetail tenia multiples nodos raiz incompatibles con `<Transition>`.

**Solucion:** Envuelto todo el template en un unico `<div>` raiz.

**Estado:** CORREGIDO (26/03/2026)

### 10. Router next() deprecated

**Problema:** Vue Router mostraba warning por uso de callback `next()` en navigation guards.

**Solucion:** Modernizado `beforeEach` para usar `return` en vez de `next()`.

**Estado:** CORREGIDO (26/03/2026)

---

## Problemas de accesibilidad (aplican a todo el sitio)

### A1. Contraste insuficiente (GLOBAL)

**Elementos afectados:**
- Etiqueta de rol en navbar (`text-artisan-accent` sobre fondo claro) - contraste bajo
- Textos `text-gray-400` y `text-gray-500` sobre fondo `bg-artisan-bg` (#F9F7F2) - contraste bajo
- Textos `text-gray-400` en footer sobre fondo `bg-artisan-dark` - contraste bajo
- Placeholders en inputs

**Solucion:** Color accent cambiado de #E9A106 a #C98A05 (ratio 4.5:1, cumple WCAG AA). Grises del footer cambiados de gray-400 a gray-300 para mejor contraste sobre fondo oscuro.

**Estado:** CORREGIDO (26/03/2026)

### A2. Links sin nombre accesible (GLOBAL)

**Elementos afectados:**
- Icono del carrito en navbar (solo tiene SVG, no tiene `aria-label`)
- Iconos de Facebook e Instagram en contacto y footer (solo SVG)

**Solucion:** Agregado `aria-label` a todos los links/botones de solo iconos:
- Carrito navbar: `aria-label="Carrito de compras"`
- Facebook/Instagram footer y contacto: `aria-label="Facebook"` / `aria-label="Instagram"`
- Compartir en ProductDetail: `aria-label="Compartir en WhatsApp"` / `aria-label="Compartir en Facebook"`
- Ojito contrasena: `aria-label` dinamico ("Mostrar/Ocultar contrasena") + padding aumentado para touch target

**Estado:** CORREGIDO (26/03/2026)

### A3. Headings desordenados (GLOBAL)

**Problema:** El footer usa `h4` que rompe la secuencia descendente de headings de la pagina.

**Solucion:** Cambiados todos los `<h4>` del footer por `<p>` con las mismas clases de estilo.

**Estado:** CORREGIDO (26/03/2026)

### A4. robots.txt invalido

**Problema:** Vite genera un robots.txt con errores en desarrollo. En produccion se debe crear uno correcto.

**Solucion:** Creado `frontend/public/robots.txt` con `User-agent: * / Allow: /` y referencia a sitemap.

**Estado:** CORREGIDO (26/03/2026)

---

## Metricas positivas

- **TBT (Total Blocking Time): 0ms** en todas las paginas - excelente, no hay JS bloqueante
- **Best Practices: 96-100** - muy bien
- **SEO: 83-92** - bien (mejora con meta description por pagina)

---

## Plan de mejora de performance

### Prioridad 1 - Pre-deploy (mejora inmediata):
1. ~~Build de produccion (`npm run build`)~~ - se aplica al deployar
2. ~~Convertir imagenes de productos a WebP~~ - HECHO (ImageService + comando optimize)
3. ~~Agregar `width`/`height` a imagenes para reducir CLS~~ - HECHO (logo navbar/footer)
4. Convertir imagenes estaticas a WebP (logo-sinfondo.png, manos-bordado.jpg, asociacion-artesanos.jpg)
5. Eliminar doble llamada a /api/cart (navbar + pagina)

### Prioridad 2 - Post-deploy:
6. Configurar headers de cache en el servidor (nginx/apache)
7. Cache de datos en Pinia con TTL (categories, etc.)
8. Agregar `<link rel="preconnect">` al backend API en produccion

### Prioridad 3 - Optimizacion avanzada:
9. Implementar service worker para cache offline
10. CDN para assets estaticos
11. Evaluar SSR parcial para Home y paginas estaticas

---

## Analisis de Trace (Chrome DevTools Performance) - 26/03/2026

Trace grabado post-optimizacion de imagenes, cache limpio.

### Comparacion antes/despues de optimizaciones

| Metrica | Trace anterior | Trace actual | Cambio |
|---------|---------------|--------------|--------|
| Layout shifts (sin input) | 53 | **16** | -70% |
| CLS acumulado sesion | 8.68 | **1.86** | -78% |
| Imagenes JPG cargadas | Si (.jpg pesados) | **No (todo WebP)** | Resuelto |

### Hallazgos del trace

**1. Imagenes estaticas se recargan multiples veces**
- `logo-sinfondo.png`: 7 cargas durante la sesion
- `manos-bordado.jpg`: 4 cargas
- Causa: sin headers de cache en desarrollo. Se resuelve configurando cache en produccion.
- Estado: Pendiente (configuracion de servidor)

**2. Doble llamada a /api/cart**
- El navbar llama `GET /api/cart` para el contador Y la pagina de carrito/checkout tambien lo llama.
- Son 2 requests identicos en paralelo.
- Estado: Pendiente de optimizacion

**3. Total de 89 API calls en una sesion de navegacion completa**
- Incluye: login, navegacion por catalogo, producto, carrito, checkout, confirmacion, contacto, nosotros
- Muchas llamadas se repiten al navegar (categories se llama cada vez que se entra al catalogo)
- Optimizacion futura: cache de datos en Pinia con TTL

**4. Layout shifts restantes (top 3)**
- Score 0.42: navegacion entre paginas (contenido cambia altura, footer se mueve)
- Score 0.27: carga de datos asincrona en pagina
- Score 0.27: misma causa en otra navegacion
- Causa raiz: en SPA, al cambiar de pagina el contenido viejo se va y el nuevo llega con distinta altura
- Solucion parcial aplicada: min-height en main + contain en footer

### Imagenes cargadas (confirmacion WebP)
- Productos: `.webp` (72 KiB y 94 KiB vs 444 KiB y 837 KiB originales)
- Artesano: `.webp` (9 KiB vs 18 KiB original)
- Estaticas pendientes de conversion: `logo-sinfondo.png` (70 KiB), `manos-bordado.jpg` (64 KiB)

---

## Nota sobre entorno de desarrollo vs produccion

Muchas de las metricas de Lighthouse van a mejorar significativamente en produccion:
- **Minify JS**: se aplica automaticamente con `npm run build`
- **Unused JS**: devtools se eliminan en produccion
- **Cache**: se configura en el servidor web
- **Performance score**: se espera subir de ~57-69 a ~80-90 con el build de produccion

Problemas resueltos:
1. ~~**Imagenes productos**~~ - RESUELTO: WebP automatico al subir + comando optimize (-87%)
2. ~~**CLS footer**~~ - PARCIALMENTE RESUELTO: min-height + contain + dimensiones logo
3. ~~**Accesibilidad**~~ - RESUELTO: aria-labels, alt, contraste, headings (promedio 79->92)
4. ~~**SEO**~~ - RESUELTO: meta tags, titulos, robots.txt (100 en casi todas)

Problemas resueltos (ronda 2):
5. ~~**Imagenes estaticas**~~ - RESUELTO: logo (-84%), manos (-55%), asociacion (-8%) convertidas a WebP
6. ~~**Doble request /api/cart**~~ - RESUELTO: cart store centralizado, navbar solo carga si no loaded
7. ~~**Doble click en eliminar**~~ - RESUELTO: flag deletingItem/deletingCategory previene doble ejecucion

Problemas pendientes para produccion:
1. **Cache headers** - configurar en servidor web (nginx/apache)
2. **CLS navegacion SPA** - shifts al cambiar pagina son inherentes a SPA sin SSR
3. **Categories se recarga** en cada visita al catalogo - cache con TTL en Pinia (mejora futura)
