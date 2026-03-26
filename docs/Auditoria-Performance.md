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

**Estado:** Pendiente

### 7. Layout Shift en Catalogo (CLS: 0.13)

**Problema:** Las tarjetas de productos causan un shift menor al cargarse.

**Solucion:**
- Las ProductCard ya tienen `aspect-[4/5]` que deberia reservar espacio
- Verificar que el buscador y las categorias no causen reflow al cargar datos

**Estado:** Pendiente

---

## Metricas positivas

- **TBT (Total Blocking Time): 0ms** en todas las paginas - excelente, no hay JS bloqueante
- **Best Practices: 96-100** - muy bien
- **SEO: 83-92** - bien (mejora con meta description por pagina)

---

## Plan de mejora de performance

### Prioridad 1 - Pre-deploy (mejora inmediata):
1. Build de produccion (`npm run build`) - resuelve JS sin minificar y JS no utilizado
2. Convertir imagenes estaticas a WebP y redimensionar (logo, hero, nosotros)
3. Agregar `width`/`height` a imagenes para reducir CLS
4. Agregar `<link rel="preconnect">` al backend API

### Prioridad 2 - Post-deploy:
5. Configurar headers de cache en el servidor
6. Implementar redimensionado automatico de imagenes al subir (Intervention Image)
7. Evaluar SSR parcial o pre-rendering para paginas estaticas (Home, Nosotros, FAQ)

### Prioridad 3 - Optimizacion avanzada:
8. Implementar service worker para cache offline
9. Lazy loading de componentes pesados
10. CDN para assets estaticos

---

## Nota sobre entorno de desarrollo vs produccion

Muchas de las metricas de Lighthouse van a mejorar significativamente en produccion:
- **Minify JS**: se aplica automaticamente con `npm run build`
- **Unused JS**: devtools se eliminan en produccion
- **Cache**: se configura en el servidor web
- **Performance score**: se espera subir de ~57-69 a ~80-90 con el build de produccion

Los problemas reales que necesitan atencion manual son:
1. **Imagenes** (conversion a WebP, redimensionado)
2. **CLS** (dimensiones explicitas en imagenes)
3. **Cadena de requests** (preconnect, reducir waterfall)
