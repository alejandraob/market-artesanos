# Integracion PayWay - Market Artesanos de Catriel

## Estado: PENDIENTE - Esperando credenciales

---

## Que es PayWay?

PayWay (ex Decidir) es la pasarela de pagos de Prisma Medios de Pago. Permite cobrar online con tarjetas de credito y debito en Argentina.

---

## Requisitos previos

- [ ] Cuenta en PayWay (solicitar en https://www.payway.com.ar)
- [ ] API Key publica (para el frontend - tokenizar tarjeta)
- [ ] API Key privada (para el backend - ejecutar pagos)
- [ ] Acceso al ambiente Sandbox para pruebas
- [ ] Documentacion oficial / SDK actualizado

---

## Flujo de pago a implementar

```
1. Usuario completa el carrito y va al checkout
2. Frontend: captura datos de tarjeta con SDK JS de PayWay
3. Frontend: obtiene un token (payment_token) sin enviar datos sensibles al backend
4. Frontend: envia el token + datos del pedido al backend
5. Backend: ejecuta el cobro via API PayWay con el token + monto
6. Backend: recibe respuesta (aprobado/rechazado)
7. Backend: crea la orden si el pago fue aprobado
8. Frontend: muestra confirmacion o error al usuario
```

---

## Archivos a crear/modificar

### Backend (Laravel)
- `config/services.php` - Agregar credenciales PayWay
- `.env` - Variables PAYWAY_PUBLIC_KEY, PAYWAY_PRIVATE_KEY, PAYWAY_SANDBOX
- `app/Services/PayWayService.php` - Servicio para comunicarse con la API
- `app/Http/Controllers/OrderController.php` - Modificar checkout para procesar pago
- `routes/api.php` - Ruta para procesar pago si es necesario

### Frontend (Vue)
- `src/views/Checkout.vue` - Crear vista de checkout con formulario de pago
- `src/utils/payway.js` - Helper para cargar SDK JS de PayWay
- `index.html` - Incluir script de PayWay SDK (si es necesario)

---

## Variables de entorno necesarias

```env
PAYWAY_PUBLIC_KEY=tu_clave_publica
PAYWAY_PRIVATE_KEY=tu_clave_privada
PAYWAY_SANDBOX=true
```

---

## Tarjetas de prueba (Sandbox)

Estas son tarjetas tipicas de prueba de PayWay (confirmar con la documentacion oficial):

| Tarjeta | Numero | Vencimiento | CVV |
|---------|--------|-------------|-----|
| Visa (aprobada) | 4507990000004905 | Cualquier fecha futura | 123 |
| Mastercard (aprobada) | 5299910010000015 | Cualquier fecha futura | 123 |
| Visa (rechazada) | 4507990000004913 | Cualquier fecha futura | 123 |

> **Nota:** Verificar tarjetas de prueba con la documentacion oficial al recibir las credenciales.

---

## Medios de pago a soportar

- [ ] Visa (credito/debito)
- [ ] Mastercard (credito/debito)
- [ ] American Express
- [ ] Cabal
- [ ] Naranja
- [ ] Cuotas (segun promociones disponibles)

---

## Consideraciones

- **PCI Compliance**: Los datos de tarjeta NUNCA pasan por nuestro backend. Se tokenizan desde el frontend con el SDK JS.
- **Antifraude**: PayWay integra Cybersource. Evaluar si se activa.
- **Cuotas**: Definir si se ofrecen cuotas y con que recargo.
- **Reembolsos**: Implementar endpoint para devolucion si es necesario.
- **Notificaciones**: Configurar webhook de PayWay para recibir cambios de estado de pagos.

---

## Pasos para comenzar

1. Obtener credenciales de PayWay
2. Leer documentacion oficial de la API
3. Configurar ambiente Sandbox
4. Implementar el servicio backend (PayWayService.php)
5. Crear la vista de checkout en el frontend
6. Probar con tarjetas de prueba
7. Pasar a produccion
