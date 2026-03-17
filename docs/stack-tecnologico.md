# Stack Tecnologico - Market Artesanos

## Arquitectura General

El proyecto sigue una arquitectura **API-first** con separacion completa entre backend y frontend:

- **Backend:** API REST con Laravel (PHP)
- **Frontend:** SPA (Single Page Application) con Vue.js
- **Comunicacion:** JSON via HTTP (Axios)

---

## Backend

### Lenguaje y Framework
| Componente | Version | Descripcion |
|------------|---------|-------------|
| PHP | 8.1+ | Lenguaje del servidor |
| Laravel | 10.x | Framework PHP principal |

### Paquetes Principales
| Paquete | Version | Funcion |
|---------|---------|---------|
| laravel/sanctum | 3.3 | Autenticacion por tokens (API) |
| spatie/laravel-permission | 6.0 | Sistema de roles y permisos (disponible) |
| mercadopago/dx-php | ^2.6 | SDK de Mercado Pago para procesamiento de pagos |
| kreait/laravel-firebase | 5.10 | Integracion con Firebase (notificaciones push) |
| guzzlehttp/guzzle | ^7.2 | Cliente HTTP para consumir APIs externas |

### Base de Datos
| Componente | Detalle |
|------------|---------|
| Motor | MySQL |
| Servidor | XAMPP (entorno local) |
| ORM | Eloquent (Laravel) |
| Migraciones | Laravel Migrations |

---

## Frontend

### Framework y Herramientas
| Componente | Version | Descripcion |
|------------|---------|-------------|
| Vue.js | 3.5 | Framework JavaScript reactivo |
| Vite | 6.3 | Build tool y servidor de desarrollo |
| Vue Router | 5.0 | Enrutamiento SPA |
| Pinia | 3.0 | Gestion de estado global |

### Librerias
| Paquete | Version | Funcion |
|---------|---------|---------|
| axios | ^1.13 | Cliente HTTP para consumir la API |
| uuid | ^13.0 | Generacion de IDs unicos (sesion de carrito) |
| @mercadopago/sdk-js | ^0.0.3 | SDK frontend de Mercado Pago |

### Estilos
| Componente | Version | Descripcion |
|------------|---------|-------------|
| Tailwind CSS | 4.2 | Framework de utilidades CSS |
| @tailwindcss/vite | 4.2 | Plugin de Tailwind para Vite |
| PostCSS | 8.5 | Procesador CSS |
| Autoprefixer | 10.4 | Prefijos CSS automaticos |

### Tema de Colores Personalizado
```css
--color-artisan-brown: #734A32   (marron principal)
--color-artisan-accent: #E9A106  (dorado/acento)
--color-artisan-dark: #333333    (texto oscuro)
--color-artisan-bg: #F9F7F2      (fondo crema)
--color-artisan-green: #6B705C   (verde oliva)
```

---

## Servicios Externos Integrados

### Correo Argentino (API de Envios)
- **Funcion:** Calculo de tarifas de envio y creacion de ordenes de despacho.
- **Modo:** Sandbox (en desarrollo) / Produccion.
- **Origen de envio:** CP 8307 (Catriel, Rio Negro).
- **Servicio implementado:** `App\Services\CorreoArgentinoService`

### Mercado Pago
- **Funcion:** Procesamiento de pagos online.
- **Estado:** SDK instalado (backend y frontend), integracion en desarrollo.

### Firebase
- **Funcion:** Notificaciones push a dispositivos.
- **Estado:** Paquete instalado, campo `firebase_token` en tabla users.

---

## Autenticacion y Seguridad

| Aspecto | Implementacion |
|---------|----------------|
| Autenticacion | Laravel Sanctum (tokens de API) |
| Hashing | bcrypt (via `Hash::make`) |
| Middleware de roles | `CheckRole` personalizado + bypass para admin |
| Proteccion de rutas | `auth:sanctum` + `role:admin,presidente` |
| CORS | Configurado para permitir comunicacion frontend-backend |

---

## Estructura de Directorios

```
market-artesanos/
├── backend/                    # API Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # 7 controladores
│   │   │   └── Middleware/     # CheckRole
│   │   ├── Models/             # 8 modelos Eloquent
│   │   └── Services/           # CorreoArgentinoService
│   ├── database/
│   │   ├── migrations/         # 14 migraciones
│   │   └── seeders/            # ArtisanSeeder
│   ├── routes/api.php          # Definicion de endpoints
│   └── config/                 # Configuraciones
├── frontend/                   # SPA Vue.js
│   ├── src/
│   │   ├── views/              # Paginas (Home, Catalog, ProductDetail, Cart, etc.)
│   │   ├── components/         # Componentes reutilizables
│   │   ├── stores/             # Pinia stores (auth)
│   │   ├── router/             # Vue Router config
│   │   ├── utils/              # Helpers (api.js, storageUrl)
│   │   └── style.css           # Estilos globales + tema Tailwind
│   └── public/                 # Assets estaticos
└── docs/                       # Documentacion del proyecto
```

---

## Entorno de Desarrollo

| Herramienta | Detalle |
|-------------|---------|
| Sistema Operativo | Windows 10 Pro |
| Servidor Local | XAMPP (Apache + MySQL) |
| Backend Server | `php artisan serve` (puerto 8000) |
| Frontend Server | `npm run dev` via Vite (puerto 5173) |
| Editor | Visual Studio Code |
