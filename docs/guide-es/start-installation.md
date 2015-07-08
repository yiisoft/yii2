Instalando Yii
==============

Yii puede ser instalado de dos maneras, usando [Composer](https://getcomposer.org/) o descargando un archivo comprimido.
Es preferible usar la primera forma, ya que te permite instalar [extensiones](structure-extensions.md) o actualizar Yii ejecutando un simple comando.

> Nota: A diferencia de Yii 1, la instalación estándar de Yii 2 resulta en la descarga e instalación tanto del framework como del esqueleto de la aplicación.


Instalando a través de Composer <span id="installing-via-composer"></span>
-------------------------------

Si aún no tienes Composer instalado, puedes hacerlo siguiendo las instrucciones que se encuentran en
[getcomposer.org](https://getcomposer.org/download/). En Linux y Mac OS X, se ejecutan los siguientes comandos:

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

En Windows, tendrás que descargar y ejecutar [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Por favor, consulta la [Documentación de Composer](https://getcomposer.org/doc/) si encuentras algún problema
o deseas obtener un conocimiento más profundo sobre su utilización.

Si ya tienes composer instalado asegurate que esté actualizado ejecutando `composer self-update`

Teniendo Composer instalado, puedes instalar Yii ejecutando los siguientes comandos en un directorio accesible vía Web:
Nota: es posible que en al ejecutar el primer comando te pida tu username 

    composer global require "fxp/composer-asset-plugin:~1.0.0"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

El comando anterior instala Yii dentro del directorio `basic`.

> Tip: Si quieres instalar la última versión de desarrollo de Yii, puedes utilizar el siguiente comando,
> que añade una [opción de estabilidad mínima](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Ten en cuenta que la versión de desarrollo de Yii no debería ser usada para producción ya que podría romper el funcionamiento actual de la aplicación.


Instalando desde un Archivo Comprimido <span id="installing-from-archive-file"></span>
--------------------------------------

Instalar Yii desde un archivo comprimido involucra dos pasos:

1. Descargar el archivo desde [yiiframework.com](http://www.yiiframework.com/download/yii2-basic).
2. Descomprimirlo en un directorio accesible vía Web.


Otras Opciones de Instalación <span id="other-installation-options"></span>
-----------------------------

Las instrucciones anteriores muestran cómo instalar Yii, lo que también crea una aplicación Web lista para ser usada.
Este es un buen punto de partida para pequeñas aplicaciones, o cuando apenas estás aprendiendo a utilizar Yii.

Pero también hay otras opciones de instalación disponibles:

* Si sólo quieres instalar el núcleo del framework y entonces crear una nueva aplicación desde cero,
  puedes seguir las instrucciones explicadas en [Generando una Aplicación desde Cero](tutorial-start-from-scratch.md).
* Si quisieras comenzar con una aplicación más avanzada, más adecuada para un entorno de desarrollo de equipo,
  deberías considerar instalar el [Template de Aplicación Avanzada](tutorial-advanced-app.md).


Verificando las Instalación <span id="verifying-installation"></span>
---------------------------

Después de la instalación, puedes acceder a la aplicación instalada a través de la siguiente URL:

```
http://localhost/basic/web/index.php
```

Esta URL da por hecho que Yii se instaló en un directorio llamado `basic`, directamente bajo el directorio del Servidor Web,
y que el Servidor Web está corriendo en tu máquina local (`localhost`). Sino, podrías necesitar ajustarlo de acuerdo a tu entorno de instalación.

![Instalación Correcta de Yii](images/start-app-installed.png)

Deberías ver la página mostrando "Congratulations!" en tu navegador. Si no ocurriera, por favor chequea que la instalación
de PHP satisface los requerimientos de Yii. Esto puedes hacerlo usando cualquiera de los siguientes procedimientos:

* Visitando la URL `http://localhost/basic/requirements.php` en tu navegador
* Corriendo los siguientes comandos:

  ```
  cd basic
  php requirements.php
  ```

Deberías configurar tu instalación de PHP para que satisfaga los requisitos mínimos de Yii. Lo que es más importante, debes tener PHP 5.4 o mayor.
También deberías instalar la [Extensión de PHP PDO](http://www.php.net/manual/es/pdo.installation.php) y el correspondiente driver de base de datos
(como `pdo_mysql` para bases de datos MySQL), si tu aplicación lo necesitara.


Configurando Servidores Web <span id="configuring-web-servers"></span>
---------------------------

> Información: Puedes saltear esta sección por ahora si sólo estás probando Yii sin intención de poner la aplicación en un servidor de producción.

La aplicación instalada debería estar lista para usar tanto con un [servidor HTTP Apache](http://httpd.apache.org/) como con un [servidor HTTP Nginx](http://nginx.org/),
en Windows, Mac OS X, o Linux.

En un servidor de producción, podrías querer configurar el servidor Web para que la aplicación sea accedida a través de la
URL `http://www.example.com/index.php` en vez de `http://www.example.com/basic/web/index.php`. Tal configuración
require apuntar el document root de tu servidor Web al directorio `basic/web`. También podrías querer ocultar `index.php`
de la URL, como se describe en la sección [Parseo y Generación de URLs](runtime-url-handling.md).
En esta sub-sección, aprenderás a configurar tu servidor Apache o Nginx para alcanzar estos objetivos.

> Información: Al definir `basic/web` como document root, también previenes que los usuarios finales accedan
al código privado o archivos con información sensible de tu aplicación que están incluidos en los directorios del mismo nivel
que `basic/web`. Denegando el acceso es una importante mejora en la seguridad.

> Información: En caso de que tu aplicación corra en un entorno de hosting compartido donde no tienes permisos para modificar
la configuración del servidor Web, aún puedes ajustar la estructura de la aplicación para mayor seguridad. Por favor consulta
la sección [Entorno de Hosting Compartido](tutorial-shared-hosting.md) para más detalles.


### Configuración Recomendada de Apache <span id="recommended-apache-configuration"></span>

Utiliza la siguiente configuración del archivo `httpd.conf` de Apache dentro de la configuración del virtual host. Ten en cuenta
que deberás reemplazar `path/to/basic/web` con la ruta real a `basic/web`.

```
# Definir el document root de "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    # Si el directorio o archivo existe, utiliza el request directamente
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Sino envía el request a index.php
    RewriteRule . index.php

    # ...más configuraciones...
</Directory>
```


### Configuración Recomendada de Nginx <span id="recommended-nginx-configuration"></span>

Deberías haber instalado PHP como un [FPM SAPI](http://php.net/install.fpm) para utilizar [Nginx](http://wiki.nginx.org/).
Utiliza la siguiente configuración de Nginx, reemplazando `path/to/basic/web` con la ruta real a `basic/web` y `mysite.local` con el
hostname real del servidor.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log main;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redireccionar a index.php todo lo que no sea un archivo real
        try_files $uri $uri/ /index.php?$args;
    }

    # descomentar para evitar el procesamiento de llamadas de Yii a archivos estáticos no existente
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Al utilizar esta configuración, también deberías definir `cgi.fix_pathinfo=0` en el archivo `php.ini`, y así
evitar muchas llamadas innecesarias del sistema a `stat()`.

Ten en cuenta también que al correr un servidor HTTPS, deberás agregar `fastcgi_param HTTPS on;` así Yii puede
detectar propiamente si la conexión es segura.
