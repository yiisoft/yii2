Crear tu propia estructura de Aplicación
========================================

> Note: Esta sección se encuentra en desarrollo.

Mientras que los templates de proyectos [basic](https://github.com/yiisoft/yii2-app-basic) y [advanced](https://github.com/yiisoft/yii2-app-advanced)
son grandiosos para la mayoría de tus necesidades, podrías querer crear tu propio template de proyecto del cual
partir todos tus proyectos.

Los templates de proyectos en Yii son simplemente repositorios conteniendo un archivo `composer.json`, y registrado como un paquete de Composer.
Cualquier repositorio puede ser identificado como paquete Composer, haciéndolo instalable a través del comando de Composer `create-project`.

Dado que es un poco demasiado comenzar tu template de proyecto desde cero, es mejor utilizar uno de los
templates incorporados como una base. Utilicemos el template básico aquí.

Clonar el Template Básico
-------------------------

El primer paso es clonar el template básico de Yii desde su repositorio Git:

```bash
git clone git@github.com:yiisoft/yii2-app-basic.git
```

Entonces espera que el repositorio sea descargado a tu computadora. Dado que los cambios realizados al template no serán enviados al repositorio, puedes eliminar el directorio `.git`
y todo su contenido de la descarga.

Modificar los Archivos
----------------------

A continuación, querrás modificar el archivo `composer.json` para que refleje tu template. Cambia los valores de `name`, `description`, `keywords`, `homepage`, `license`, y `support`
de forma que describa tu nuevo template. También ajusta las opciones `require`, `require-dev`, `suggest`, y demás para que encajen con los requerimientos de tu template.

> Note: En el archivo `composer.json`, utiliza el parámetro `writable` (bajo `extra`) para especificar
> permisos-por-archivo a ser definidos después de que la aplicación es creada a partir del template.

Luego, pasa a modificar la estructura y contenido de la aplicación como te gustaría que sea por defecto. Finalmente, actualiza el archivo README para que sea aplicable a tu template.

Hacer un Paquete
----------------

Con el template definido, crea un repositorio Git a partir de él, y sube tus archivos ahí. Si tu template va a ser de código abierto, [Github](http://github.com) es el mejor lugar para alojarlo. Si tu intención es que el template no sea colaborativo, cualquier sitio de repositorios Git servirá.

Ahora, necesitas registrar tu paquete para Composer. Para templates públicos, el paquete debe ser registrado en [Packagist](https://packagist.org/).
Para templates privados, es un poco más complicado registrarlo. Puedes ver instrucciones para hacerlo en la [documentación de Composer](https://getcomposer.org/doc/05-repositories.md#hosting-your-own).

Utilizar el Template
--------------------

Eso es todo lo que se necesita para crear un nuevo template de proyecto Yii. Ahora puedes crear tus propios proyectos a partir de este template:

```
composer global require "fxp/composer-asset-plugin:^1.3.1"
composer create-project --prefer-dist --stability=dev mysoft/yii2-app-coolone new-project
```
