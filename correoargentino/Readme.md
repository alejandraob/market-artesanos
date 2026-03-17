# Módulo de Intregración a Prestashop de Correo Argentino (v.1.0.6)

### Instalación desde el repositorio
Una vez realizada la clonación del repositorio se debe renombrar o copiar el archivo *env.sample* a *.env* de esta manera será posible operar sin problemas.
Es importante comprimir la carpeta donde se ha clonado el proyecto y asegurarse que el nombre tanto de la carpeta interna como del archivo comprimido sea **correoargentino** y **correoargentino.zip**.
#### Importante
Antes de cargar el archivo comprimido es necesario desinstalar la versión anterior del módulo asegurándose de eliminar la carpeta, para esto es necesario tildar la opción "Eliminar archivos" en el modal de confirmación.

### Sandbox
Por defecto, el módulo viene configurado para trabajar en modo sandbox, por lo cual estos servicios están apuntando a los entornos de TIARG. 
Al desactivar el modo sandbox se apunta a la URL exteriorizada proporcionada por Correo Argentino. De ser necesario se puede cambiar esta URL dentro del archivo *.env*
cambiando la clave **CA_API_URL**
