# A

## alias

Alias es un string utilizado por Yii para referirse a una clase o directorio tal como `@app/vendor`.

## aplicación

La aplicación es el objeto central durante la solicitud HTTP. Contiene un número de componentes con los que toma información de la solicitud y la envía al controlador apropiado para posterior procesamiento.

El objeto de la aplicación es instanciado como un singleton por el script de entrada. El singleton de la aplicación puede ser accedido desde cualquier lugar a través de `\Yii::$app`.

## assets

Asset se refiere a un archivo de recurso. Típicamente contiene JavaScript o CSS pero puede ser cualquier otra cosa que sea accesible vía HTTP.

## atributo

Un atributo es una propiedad de un modelo (una variable miembro de clase o una propiedad mágica definida vía `__get()`/`__set()`) que almacena **datos de negocio**.

# B

## bundle

Bundle, conocido como paquete en Yii 1.1, se refiere a un número de recursos y un archivo de configuración que describe dependencias y lista recursos.

# C

## configuración

Configuración puede referirse tanto al proceso de establecer propiedades de un objeto como a un archivo de configuración que almacena la definición de propiedades para un objeto o clase de objetos.

# E

## extensión

Extensión es un grupo de clases, paquete de recursos y configuraciones que agrega más características a la aplicación.

# I

## instalación

Instalación es el proceso de preparar algo para trabajar, desde seguir un archivo léame hasta ejecutar un script preparado especialmente para tal fin. En el caso de Yii, define permisos y chequea los requerimientos para el funcionamiento del software.

# M

## módulo

Módulo es una sub-aplicación que contiene elementos MVC en sí mismo, como modelos, vistas, controladores, etc. y puede ser utilizado dentro de la aplicación principal. Típicamente remitiendo las solicitudes al módulo en vez de manejándolo desde controladores.

# N

## namespace

Namespace (espacio de nombres) se refiere a una [característica de PHP](http://php.net/manual/es/language.namespaces.php) activamente utilizada en Yii 2.

# P

## paquete

[Ver bundle](#bundle).

# V

## vendor

Vendor (proveedor) es una organización o un desarrollador individual que provee código en forma de extensiones, módulos o librerías.
