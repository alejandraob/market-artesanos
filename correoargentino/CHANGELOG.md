### Changelog

Registros de cambios

### v.1.3.3
- Se corrige un error al indexar la carpeta vendor
### v1.3.2
- Se agrega soporte a PSR-4

### v1.3.1

- Se corrige un error que bloqueaba la funcionalidad principal de sucursales cuando existia mas de un idioma. 

### v1.3.0

- Se remueve la funcionalidad de click en registros.

### v1.2.9

- Se agrega la version de la API.

### v1.2.8

- Se agrega la URL de producción en la variable de entorno.

### v1.2.7

- Se cambia la URL de la API por una exteriorizada y con SSL válido.
- Se realizan mejoras y se eliminan archivos innecesarios.

### v1.2.6

- Se agrega la posibilidad de operar sin SSL activando el modo de prueba.
- Se agregan mejoras en el listado, tanto en la descarga de PDF como en la navegación.
- Se agregan mejoras visuales en el menu de acciones, agregando iconos a cada acción.

### v1.2.5

- Se agrega la ruta de api en el archivo de ejemplo de configuración de variables de entorno.

### v1.2.4

- Se adecua el servicio de agencias al nuevo formato y se agrega la opción de retiro (pickup_availability) en el request.

### v1.2.3

- Se agrega la restricción para ver las acciones en el detalle de la orden.

### v1.2.2

- Se agrega la validación del email en el formulario de configuración cuando este está definido.
- Se agregan nuevas traducciones (mensaje de email no válido).

### v1.2.1

- Se corrigen el error en el modo sandbox, ahora es posible establecer una URL de prueba.
- Se mejora la solicitud de las agencias y se implemente el endpoint real.
- Se permite ver las opciones "Cancelar" y "Rótulo" de del listado solo cuando la orden esta como pagada o en espera de pago.
- Se cambia la forma de autenticación usando un Apikey.

### v1.2.0

- Se cambia la forma de autenticación implementando el uso de ApiKey.
- Se agregan nuevas traducciones y se corrigen existentes.

### v1.1.6

- Se corrigen los mensajes de error que solo eran visibles en modo test.
- Se ocultan las acciones de "Imprimir Rotulo y Cancelar" cuando no existe un tracking asociado.

### v1.1.5

- Se agregan nuevas traducciones.
- Se corrigen algunas validaciones.
- Se limitan algunos campos.

### v1.1.4

- Se agregan nuevas validaciones en el código postal.
- Se corrige un defecto en la validación de la altura de calle.
- Se aplica un nuevo hook para prevenir compras en carritos que ya han elegido un tipo de envío y el módulo se ha desactivado.
- Se agregan nuevas traducciones.

Nota: Si el comprador ya ha elegido el tipo de servicio de Correo Argentino durante el checkout y se desactiva el módulo, no se debería avanzar al siguiente paso (método de pago y confirmación), en ese caso se obtiene un mensaje que dice que no hay medios de envíos disponibles.
Ahora bien, si el comprador ya se encuentra en la pantalla de pago y confirmación, la orden finalizara exitosamente. Esto es porque en esa etapa Prestashop no verifica el medio de envío, ya que esto se hace en el paso anterior.
De igual manera al acceder a los detalles de la orden desde el BackOffice, en la pestaña `Transportista` el valor debe ser vacío.

### v1.1.3

- Se agrega la traducción de Rótulo desde el archivo del módulo.
- Se permite mostrar el listado de sucursales nuevamente.

### v1.1.2

- Se agrega un nuevo archivo de traducción en español (es.php).

### v1.1.1

- Se activa por defecto la opción de envío gratuito.
- Muestra el mensaje de éxito al actualizar la configuración.
- Se capturan y muestran los mensajes de error de la API de CorreoArgentino.
- Se cambian los métodos de algunas traducciones.

### v1.1.0

- Se desactiva por defecto el cálculo de tarifas de envío.
- Se muestran solo los dos tipos de envío `Sucursal y Domicilio`.
- Se verifican las traducciones.
- Se validan los campos de celular y teléfono cuando están definidos.

### v1.0.9

- Se corrigen y se agregan nuevas validaciones en el formulario de direcciones.
- Se agregan nuevas traducciones.

### v1.0.8

- Se agregan validaciones en el formulario de configuración.
- Se agregan nuevas traducciones.
- Se corrigen algunas acciones.
- Se eliminan las acciones del toolbar del listado.

### v1.0.7

- Corregir ruta de acceso al archivo `agencies.json`.

### v1.0.6

- Se agregan mensajes de error con credenciales inválidas.
- Se corrige el método `login` para retornar un valor booleano.
- Se agrega el archivo CHANGELOG para registrar los cambios de cada versión.
