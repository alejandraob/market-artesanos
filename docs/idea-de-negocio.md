# Idea de Negocio - Market Artesanos

## Descripcion General

Market Artesanos es una plataforma de comercio electronico desarrollada para la **Asociacion de Artesanos de Catriel, Rio Negro, Argentina**. El sitio funciona como vitrina digital y canal de venta para los artesanos locales, permitiendo que sus productos lleguen a clientes de todo el pais.

## Modelo de Negocio

A diferencia de un e-commerce tradicional, los productos publicados en el sitio son **muestras ilustrativas**. Cada pieza es artesanal y se elabora **a pedido** del cliente. Esto significa que:

- Las imagenes del catalogo representan el estilo y tipo de trabajo del artesano, no el producto final exacto.
- Una vez confirmada y pagada la compra, se le proporcionan al cliente los datos de contacto del artesano.
- El cliente coordina directamente con el artesano los detalles especificos: color, material, medidas y personalizacion.
- El plazo de elaboracion y envio es de **15 a 20 dias habiles**.

## Roles del Sistema

### Administrador
- Acceso total al sistema.
- Supervisa todas las operaciones.
- Puede realizar cualquier accion que hagan los demas roles.

### Presidente (Encargado de la Asociacion)
- Registra y gestiona los artesanos (datos personales, foto de perfil, especialidad, rubros/categorias).
- Administra el catalogo de productos: crea, edita, habilita/deshabilita productos, asignandolos a un artesano y una categoria.
- Visualiza la lista de clientes registrados.
- Consulta los detalles de las compras/pedidos realizados.
- Gestiona las categorias de productos.

### Cliente
- Navega el catalogo y visualiza los productos.
- Se registra y gestiona su perfil (nombre, email, telefono, contrasena).
- Agrega productos al carrito y realiza compras.
- Consulta su historial de compras.
- Post-compra, recibe los datos del artesano para coordinar los detalles del producto.

## Flujo de Compra

1. El cliente navega el catalogo y selecciona un producto (muestra).
2. Elige la cantidad deseada y agrega al carrito.
3. En el carrito se le recuerda que los productos son elaborados a pedido.
4. Finaliza la compra (pago via Mercado Pago - en integracion).
5. Se le entregan los datos del artesano para coordinar detalles.
6. El artesano elabora el producto personalizado.
7. Se gestiona el envio a traves de Correo Argentino (API integrada).
8. El producto llega al cliente en un plazo de 15 a 20 dias habiles.

## Artesanos Fundadores

La asociacion cuenta con 15 artesanos fundadores con especialidades que abarcan:

- Tejido (crochet, dos agujas, telar, totora, macrame)
- Ceramica y porcelana fria
- Trabajo en madera, cuero y herreria
- Bijouteria y joyeria artesanal
- Bordado a mano y textil
- Pintura decorativa
- Sahumerios artesanales
- Alambrismo
- Fotografia de naturaleza
