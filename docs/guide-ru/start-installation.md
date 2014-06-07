Установка Yii
==============

Вы можете установить Yii двумя способами: используя [Composer](http://getcomposer.org/) или скачав архив.
Первый способ предпочтительнее т.к. позволяет установить установить новые [расширения](structure-extensions.md)
или обновить Yii одной командой.


Установка при помощи Composer <a name="installing-via-composer"></a>
-----------------------

Если Composer еще не установлен это просто сделать по инструкции на
[getcomposer.org](https://getcomposer.org/download/), или одним из нижеперечисленных способов:

* на Linux или Mac, используйте следующую команду:

  ```
  curl -s http://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  ```
* на Windows, скачайте и запустите [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Вы можете обращаться к документации [Composer Documentation](https://getcomposer.org/doc/) в случае возникновения проблем или если будет необходима более детальная информация.

После установки Composer можно устанавливать Yii. Запуститие команду :

```
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

в папке доступной через Web. Composer установит Yii (шаблонное приложение basic) в папку `basic`.

> Совет: Если хотите установить последнюю (экспериментальную) версию Yii, Вы можете добавить ключ `stability`:
```
composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
```
Обратите внимание: не используйте экспериментальную версию Yii в продакшн т.к. данный релиз не стабилен.


Установка из архива <a name="installing-from-archive-file"></a>
-------------------------------

Установка Yii из архива состоит из двух шагов:

1. Скачайте архив по адресу [yiiframework.com](http://www.yiiframework.com/download/yii2-basic);
2. Распакуйте скачанный архив в папку, доступную из Web.


Другие опции установки <a name="other-installation-options"></a>
--------------------------

Ниже приведены инструкции, которые покажут как установить Yii в виде базового приложения готового к работе.
Это отличный вариант для небольших проектов или для тех, кто только начинает изучать Yii.

Есть два основных варианта такой установки:

* Если Вам нужен только сам фреймворк и Вы хотели бы создать приложение "с чистого листа" воспользуйтесь инструкцией [простой шаблон приложения](tutorial-start-from-scratch.md).
* Если хотите начать с более продвинутого приложения которое поддерживает командную среду разработки и разделено на несколько слоев (frontend/backend) [продвинутый шаблон приложения](tutorial-advanced-app.md).


Проверка установки <a name="verifying-installation"></a>
----------------------

После установки приложения можно зайти браузером на сервер, где происходила установка Yii приложения. Если Вы, по примеру выше, развернули приложение в директории `basic` в корне (DocumentRoot) вашего Web сервера, то URL доступа к точке входа в приложение будет следующим:

```
http://hostname/basic/web/index.php
```

![Successful Installation of Yii](images/start-app-installed.png)

В результате, Вы должны увидеть страницу приветствия "Congratulations!". Если нет - проверьте в первую очередь требования и зависимости Yii одним из способов:

* Браузером перейдите по адресу `http://hostname/basic/requirements.php`
* Или выполните команду в консоли: 

  ```
  cd basic
  php requirements.php
  ```

Для корректной работы фреймворка Вам нужно настроить PHP в соответствии с требованиями Yii приведенными в этом скрипте.
Самое важное - PHP версии 5.4 и выше. Так же, необходимо установить [PDO PHP Extension](http://www.php.net/manual/en/pdo.installation.php) и соответствующий драйвер 
(Например, `pdo_mysql` для MySQL), если Вы планируете использовать базы данных в своем приложении.


Настройка Web сервера <a name="configuring-web-servers"></a>
-----------------------

> Замечание: можете пропустить этот подраздел если Вы лишь тестируете приложение и не разворачиваете его на продакшн сервере.

The application installed according to the above instructions should work out of box with either
an [Apache HTTP server](http://httpd.apache.org/) or an [Nginx HTTP server](http://nginx.org/), on
either Windows or Linux.

On a production server, you may want to configure your Web server so that the application can be accessed
via the URL `http://hostname/index.php` instead of `http://hostname/basic/web/index.php`. This
requires pointing the document root of your Web server to the `basic/web` folder. And you may also
want to hide `index.php` from the URL, as described in the [URL Parsing and Generation](runtime-url-handling.md) section.
In this subsection, we will show how to configure your Apache or Nginx server to achieve these goals.

> Info: By setting `basic/web` as the document root, you also prevent end users from accessing
your private application code and sensitive data files that are stored in the sibling directories
of `basic/web`. This makes your application more secure.

> Info: If your application will run in a shared hosting environment where you do not have the permission
to modify its Web server setting, you may adjust the structure of your application. Please refer to
the [Shared Hosting Environment](tutorial-shared-hosting.md) section for more details.


### Recommended Apache Configuration <a name="recommended-apache-configuration"></a>

Use the following configuration in Apache's `httpd.conf` file or within a virtual host configuration. Note that you
should replace `path/to/basic/web` with the actual path of `basic/web`.

```
# Set document root to be "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    # If a directory or a file exists, use the request directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Otherwise forward the request to index.php
    RewriteRule . index.php

    # ...other settings...
</Directory>
```


### Recommended Nginx Configuration <a name="recommended-nginx-configuration"></a>

You should have installed PHP as an [FPM SAPI](http://php.net/install.fpm) for [Nginx](http://wiki.nginx.org/).
Use the following Nginx configuration and replace `path/to/basic/web` with the actual path of `basic/web`.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/project/log/access.log main;
    error_log   /path/to/project/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
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

When using this configuration, you should set `cgi.fix_pathinfo=0` in the `php.ini` file
in order to avoid many unnecessary system `stat()` calls.

Also note that when running an HTTPS server you need to add `fastcgi_param HTTPS on;` so that Yii
can properly detect if a connection is secure.
