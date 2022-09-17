Instalar Yii
============

Puedes instalar Yii de dos maneras, utilizando el administrador de paquetes [Composer](https://getcomposer.org/) o descargando un archivo comprimido.
La forma recomendada es la primera, ya que te permite instalar nuevas [extensions](structure-extensions.md) o actualizar Yii con sólo ejecutar un comando.

La instalación estándar de Yii cuenta tanto con el framework como un template de proyecto instalados.
Un template de proyecto es un proyecto Yii funcional que implementa algunas características básicas como: login, formulario de contacto, etc. 
El código está organizado de una forma recomendada. Por lo tanto, puede servir como un buen punto de partida para tus proyectos.
    
En esta y en las próximas secciones, describiremos cómo instalar Yii con el llamado *Template de Proyecto Básico*
y cómo implementar nuevas características por encima del template. Yii también provee otro template llamado
[Template de Proyecto Avanzado](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md) qué es mejor para desarrollar aplicaciones con varios niveles
en el entorno de un equipo de desarrollo.

> Info: El Template de Proyecto Básico es adecuado para desarrollar el 90 porciento de las aplicaciones Web. Difiere del
  Template de Proyecto Avanzado principalmente en cómo está organizado el código. Si eres nuevo en Yii, te recomendamos
  utilizar el Template de Proyecto Básico por su simplicidad pero funcionalidad suficiente.


Instalando via Composer <span id="installing-via-composer"></span>
-------------------------------

Si aún no tienes Composer instalado, puedes hacerlo siguiendo las instrucciones que se encuentran en
[getcomposer.org](https://getcomposer.org/download/). En Linux y Mac OS X, se ejecutan los siguientes comandos:

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

En Windows, tendrás que descargar y ejecutar [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Por favor, consulta la [Documentación de Composer](https://getcomposer.org/doc/) si encuentras algún problema
o deseas obtener un conocimiento más profundo sobre su utilización.

Si ya tienes composer instalado, asegúrate de tener una versión actualizada. Puedes actualizar Composer
ejecutando el comando `composer self-update`

Teniendo Composer instalado, puedes instalar Yii ejecutando los siguientes comandos en un directorio accesible vía Web:

```bash
composer global require "fxp/composer-asset-plugin:^1.4.1"
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

El primer comando instala [composer asset plugin](https://github.com/fxpio/composer-asset-plugin),
que permite administrar dependencias de paquetes bower y npm a través de Composer. Sólo necesitas ejecutar este comando
una vez. El segundo comando instala Yii en un directorio llamado `basic`. Puedes elegir un nombre de directorio diferente si así lo deseas.

> Note: Durante la instalación, Composer puede preguntar por tus credenciales de acceso de Github. Esto es normal ya que Composer 
> necesita obtener suficiente límite de acceso de la API para traer la información de dependencias de Github. Para más detalles, 
> consulta la [documentación de Composer](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Tip: Si quieres instalar la última versión de desarrollo de Yii, puedes utilizar uno de los siguientes comandos,
> que agregan una [opción de estabilidad](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
> ```bash
> composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
> ```
>
> Ten en cuenta que la versión de desarrollo de Yii no debería ser utilizada en producción ya que podría romper tu código actual.


Instalar desde un Archivo Comprimido <span id="installing-from-archive-file"></span>
------------------------------------

Instalar Yii desde un archivo comprimido involucra tres pasos:

1. Descargar el archivo desde [yiiframework.com](https://www.yiiframework.com/download/yii2-basic).
2. Descomprimirlo en un directorio accesible vía Web.
3. Modificar el archivo `config/web.php` introduciendo una clave secreta para el ítem de configuración `cookieValidationKey`
   (esto se realiza automáticamente si estás instalando Yii a través de Composer):

   ```php
   // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
   'cookieValidationKey' => 'enter your secret key here',
   ```


Otras Opciones de Instalación <span id="other-installation-options"></span>
-----------------------------

Las instrucciones anteriores muestran cómo instalar Yii, lo que también crea una aplicación Web lista para ser usada.
Este es un buen punto de partida para la mayoría de proyectos, tanto grandes como pequeños. Es especialmente adecuado si recién
estás aprendiendo a utilizar Yii.

Pero también hay otras opciones de instalación disponibles:

* Si sólo quieres instalar el núcleo del framework y entonces crear una nueva aplicación desde cero,
  puedes seguir las instrucciones explicadas en [Generando una Aplicación desde Cero](tutorial-start-from-scratch.md).
* Si quisieras comenzar con una aplicación más avanzada, más adecuada para un entorno de desarrollo de equipo,
  deberías considerar instalar el [Template de Aplicación Avanzada](tutorial-advanced-app.md).


Verificando las Instalación <span id="verifying-installation"></span>
---------------------------

Una vez finalizada la instalación, o bien configura tu servidor web (mira la sección siguiente) o utiliza
el [servidor web incluido en PHP](https://www.php.net/manual/es/features.commandline.webserver.php) ejecutando el siguiente
comando de consola estando parado en el directorio `web` de la aplicación:
 
```bash
php yii serve
```

> Note: Por defecto el servidor HTTP escuchará en el puerto 8080. De cualquier modo, si el puerto está en uso o deseas 
servir varias aplicaciones de esta manera, podrías querer especificar qué puerto utilizar. Sólo agrega el argumento --port:

```bash
php yii serve --port=8888
```

Puedes utilizar tu navegador para acceder a la aplicación instalada de Yii en la siguiente URL:

```
http://localhost:8080/.
```

![Instalación Correcta de Yii](images/start-app-installed.png)

Deberías ver la página mostrando "Congratulations!" en tu navegador. Si no ocurriera, por favor chequea que la instalación
de PHP satisfaga los requerimientos de Yii. Esto puedes hacerlo usando cualquiera de los siguientes procedimientos:

* Copiando `/requirements.php` a `/web/requirements.php` y visitando la URL `http://localhost/basic/requirements.php` en tu navegador
* Corriendo los siguientes comandos:

  ```bash
  cd basic
  php requirements.php
  ```
  
Deberías configurar tu instalación de PHP para que satisfaga los requisitos mínimos de Yii. Lo que es más importante,
debes tener PHP 5.4 o mayor. También deberías instalar la [Extensión de PHP PDO](https://www.php.net/manual/es/pdo.installation.php)
y el correspondiente driver de base de datos (como `pdo_mysql` para bases de datos MySQL), si tu aplicación lo necesitara.


Configurar Servidores Web <span id="configuring-web-servers"></span>
-------------------------

> Info: Puedes saltear esta sección por ahora si sólo estás probando Yii sin intención
  de poner la aplicación en un servidor de producción.

La aplicación instalada siguiendo las instrucciones mencionadas debería estar lista para usar tanto
con un [servidor HTTP Apache](https://httpd.apache.org/) como con un [servidor HTTP Nginx](https://nginx.org/),
en Windows, Mac OS X, o Linux utilizando PHP 5.4 o mayor. Yii 2.0 también es compatible con [HHVM](https://hhvm.com/)
de Facebook. De todos modos, hay algunos casos donde HHVM se comporta diferente del
PHP oficial, por lo que tendrás que tener cuidados extra al utilizarlo.

En un servidor de producción, podrías querer configurar el servidor Web para que la aplicación sea accedida
a través de la URL `https://www.example.com/index.php` en vez de `https://www.example.com/basic/web/index.php`. Tal configuración
require apuntar el document root de tu servidor Web a la carpeta `basic/web`. También podrías
querer ocultar `index.php` de la URL, como se describe en la sección [Parseo y Generación de URLs](runtime-url-handling.md).
En esta sub-sección, aprenderás a configurar tu servidor Apache o Nginx para alcanzar estos objetivos.

> Info: Al definir `basic/web` como document root, también previenes que los usuarios finales accedan
al código privado o archivos con información sensible de tu aplicación que están incluidos en los directorios del mismo nivel
que `basic/web`. Denegando el acceso es una importante mejora en la seguridad.

> Info: En caso de que tu aplicación corra en un entorno de hosting compartido donde no tienes permisos para modificar
la configuración del servidor Web, aún puedes ajustar la estructura de la aplicación para mayor seguridad. Por favor consulta
la sección [Entorno de Hosting Compartido](tutorial-shared-hosting.md) para más detalles.


### Configuración Recomendada de Apache <span id="recommended-apache-configuration"></span>

Utiliza la siguiente configuración del archivo `httpd.conf` de Apache dentro de la configuración del virtual host. Ten en cuenta
que deberás reemplazar `path/to/basic/web` con la ruta real a `basic/web`.

```apache
# Definir el document root como "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # utiliza mod_rewrite para soporte de URLs amigables
    RewriteEngine on
    # Si el directorio o archivo existe, utiliza la petición directamente
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Sino, redirige la petición a index.php
    RewriteRule . index.php

    # ...otras configuraciones...
</Directory>
```


### Configuración Recomendada de Nginx <span id="recommended-nginx-configuration"></span>

Para utilizar [Nginx](https://wiki.nginx.org/), debes instalar PHP como un [FPM SAPI](https://www.php.net/manual/es/install.fpm.php).
Utiliza la siguiente configuración de Nginx, reemplazando `path/to/basic/web` con la ruta real a
`basic/web` y `mysite.test` con el hostname real a servir.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.test;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redireccionar a index.php todo lo que no sea un archivo real
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # descomentar para evitar el procesamiento de llamadas de Yii a archivos estáticos no existente
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
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
