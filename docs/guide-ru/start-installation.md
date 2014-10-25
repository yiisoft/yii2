Установка Yii
==============

Вы можете установить Yii двумя способами: используя [Composer](http://getcomposer.org/) или скачав архив.
Первый способ предпочтительнее так как позволяет установить новые [расширения](structure-extensions.md)
или обновить Yii одной командой.


Установка при помощи Composer <a name="installing-via-composer"></a>
-----------------------

Если Composer еще не установлен это можно сделать по инструкции на
[getcomposer.org](https://getcomposer.org/download/), или одним из нижеперечисленных способов:

* на Linux или Mac, используйте следующую команду:

  ```
  curl -s http://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  ```
* на Windows, скачайте и запустите [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

В случае возникновения проблем или если вам необходима дополнительная информация, обращайтесь
к [документации Composer](https://getcomposer.org/doc/) .

После установки Composer устанавливать Yii можно запустив следующую команду в папке доступной через веб:

```
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

Composer установит Yii (шаблонное приложение basic) в папку `basic`.

> **Подсказка**: Если хотите установить последнюю нестабильную версию Yii, вы можете добавить ключ `stability`:
```
composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
```
Стоит отметить, что нестабилную версию Yii нельзя использовать на рабочем сервере.


Установка из архива <a name="installing-from-archive-file"></a>
-------------------------------

Установка Yii из архива состоит из двух шагов:

1. Скачайте архив по адресу [yiiframework.com](http://www.yiiframework.com/download/yii2-basic);
2. Распакуйте скачанный архив в папку, доступную из Web.


Другие опции установки <a name="other-installation-options"></a>
--------------------------

Выше приведены инструкции по установке Yii в виде базового приложения готового к работе.
Это отличный вариант для небольших проектов или для тех, кто только начинает изучать Yii.

Есть два основных варианта такой установки:

* Если вам нужен только сам фреймворк и вы хотели бы создать приложение с нуля, воспользуйтесь инструкцией, описанной в
разделе «[Создание приложения с нуля](tutorial-start-from-scratch.md)».
* Если хотите начать с более продвинутого приложения, хорошо подходящего для работы в команде, используйте
[шаблон приложения advanced](tutorial-advanced-app.md).


Проверка установки <a name="verifying-installation"></a>
----------------------

Если вы установили приложение в директорию `basic` в корневой директории вашего веб сервера и имя сервера `hostname`,
запустить приложение можно открыв следующий URL через браузер:

```
http://hostname/basic/web/index.php
```

![Успешно установленный Yii](images/start-app-installed.png)

Вы должны увидеть страницу приветствия «Congratulations!». Если нет — проверьте требования Yii одним из способов:

* Браузером перейдите по адресу `http://hostname/basic/requirements.php`
* Или выполните команду в консоли: 

  ```
  cd basic
  php requirements.php
  ```

Для корректной работы фреймворка вам необходима установка PHP, соответствующая его минимальным требованиям. Основное
требование — PHP версии 5.4 и выше. Если ваше приложение работает с базой данных, необходимо установить
[расширение PHP PDO](http://www.php.net/manual/ru/pdo.installation.php) и соответствующий драйвер 
(например, `pdo_mysql` для MySQL).


Настройка веб сервера <a name="configuring-web-servers"></a>
-----------------------

> Информация: можете пропустить этот подраздел если вы только начали знакомиться с фреймворком и пока не разворачиваете
  его на рабочем сервере.

Приложение, установленное по инструкциям, приведённым выше, будет работать сразу как с [Apache](http://httpd.apache.org/),
так и с [Nginx](http://nginx.org/) под Windows и Linux.

На рабочем сервере вам наверняка захочется изменить URL приложения с `http://hostname/basic/web/index.php`
на `http://hostname/index.php`. Для этого необходимо изменить корневую директорию в настройках веб сервера так, чтобы та
указывала на `basic/web`. Дополнительно можно спрятать `index.php` следуя описанию в разделе «[Разбор и генерация URL](runtime-url-handling.md)». 
Далее будет показано как настроить Apache и Nginx.

> Информация: Устанавливая `basic/web` корневой директорией веб сервера вы защищаете от нежелательного доступа код и данные,
  находящиеся на одном уровне с `basic/web`. Это делает приложение более защищенным.

> Информация: Если приложение работает на хостинге где нет доступа к настройкам веб сервера, то можно изменить структуру
  приложения как описано в разделе «[Работа на Shared хостинге](tutorial-shared-hosting.md)».


### Рекомендуемые настройки Apache <a name="recommended-apache-configuration"></a>

Добавьте следующее в `httpd.conf` Apache или в конфигурационный файл виртуального хоста. Не забудьте заменить
`path/to/basic/web` на корректный путь к `basic/web`.

```
# Устанавливаем корневой директорией "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    # Если запрашиваемая в URL директория или файл существуют обращаемся к ним напрямую
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Если нет - перенаправляем запрос на index.php
    RewriteRule . index.php

    # ...прочие настройки...
</Directory>
```


### Рекомендуемые настройки Nginx <a name="recommended-nginx-configuration"></a>

PHP должен быть установлен как [FPM SAPI](http://php.net/manual/ru/install.fpm.php) для [Nginx](http://wiki.nginx.org/).
Используйте следующие параметры Nginx и не забудьте заменить `path/to/basic/web` на корректный путь к `basic/web`.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## слушаем ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/project/log/access.log main;
    error_log   /path/to/project/log/error.log;

    location / {
        # Перенаправляем все запросы к несуществующим директориям и файлам на index.php
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

Используя данную конфигурацию установите `cgi.fix_pathinfo=0` в `php.ini` чтобы предотвратить лишние системные
вызовы `stat()`.

Учтите, что используя HTTPS необходимо задавать `fastcgi_param HTTPS on;` чтобы Yii мог корректно определять защищенное
соединение.
