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

Приложение, установленное по инструкциям данного раздела находится в рабочем состоянии сразу же после установки как с Web сервером [Apache](http://httpd.apache.org/), так и с [Nginx HTTP server](http://nginx.org/), как в окружении Windows, так и в Linux.

На рабочем сервере Вам наверняка захочется изменить URL путь к приложению на более удобный, такой как  `http://hostname/index.php` вместо `http://hostname/basic/web/index.php`. Для этого лишь нужно изменить корневую директорию в настройках Web сервера так, что бы та указывала на `basic/web`. Так же, можно спрятать `index.php` из URL строки, подробности описаны в разделе [Разбор и генерация URL](runtime-url-handling.md). 
В данном подразделе мы увидим как настроить Apache и Nginx соответствующим образом.

> Замечание: Устанавливая `basic/web` корневой директорией Web сервера Вы защищаете от нежелательного доступа через Web программную часть приложения и данные, находящиеся на одном уровне с `basic/web`. Такие настройки делают Ваш сервер более защищенным.

> Замечание: Если Вы работаете на хостинге где нет доступа к настройкам Web сервера, то можно настроить под себя структуру приложения как это описано в разделе [Работа на Shared хостинге](tutorial-shared-hosting.md).


### Рекомендуемые настройки Apache <a name="recommended-apache-configuration"></a>

Добавьте следующую конфигурацию в `httpd.conf` Web сервера Apache или в конфигурационный файл виртуального сервера. Не забудьте заменить `path/to/basic/web` на свой корректный путь к `basic/web`.

```
# Устанавливаем корневой директорией "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    # Если запрашиваемая в URL директория или файл сущесвуют обращаемся к ним напрямую
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Если нет - перенаправляем запрос на index.php
    RewriteRule . index.php

    # ...прочие настройки...
</Directory>
```


### Рекомендуемые параметры для Nginx <a name="recommended-nginx-configuration"></a>

PHP должен быть установлен как [FPM SAPI](http://php.net/install.fpm) для [Nginx](http://wiki.nginx.org/).
Используйте следующие параметры Nginx и не забудьте заменить `path/to/basic/web` на свой корректный путь к `basic/web`.

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
        # Перенаправляем все запросы к несуществующим директориям и файлам к index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # раскомментируйте строки ниже во избежание обработки Yii обращений к несуществующим статическим файлам
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

Используя данную конфигурацию установите параметр `cgi.fix_pathinfo=0` в `php.ini` что бы предотвратить лишние системные вызовы `stat()`.

Учтите что используя HTTPS необходимо задавать `fastcgi_param HTTPS on;` что бы Yii мог корректно определять защищенное соединение.
