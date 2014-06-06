Установка
===============

Существует два способа, с помощью которых вы можете установить фреймворк Yii:
* при помощи [Composer](http://getcomposer.org/) (рекомендуется)
* скачав один из шаблонов приложения, содержащий все необходимые компоненты, включая сам Yii

# Установка с помощью Composer

Использование менеджера зависимостей [Composer](http://getcomposer.org/) является рекомендуемым способом для установки Yii. Если вы еще не установили Composer, вы можете скачать его http://getcomposer.org/, или запустить следующую команду, чтобы скачать и установить его:

```
curl -s http://getcomposer.org/installer | php
```

(Настоятельно рекомендуется выбирать [установку Composer глобально](https://getcomposer.org/doc/00-intro.md#globally))

В случае возникновения проблем или для дополнительной информации по установке ознакомьтесь с официальными инструкциями Composer:
* [Linux](http://getcomposer.org/doc/00-intro.md#installation-nix)
* [Windows](http://getcomposer.org/doc/00-intro.md#installation-windows)

Установив Composer, вы можете создать новый сайт на Yii, используя один из готовых к применению шаблонов. В зависимости от ваших нужд, подходящий шаблон может помочь вам в первоначальной инициализации вашего проекта.

На данный момент доступно два шаблона Yii приложений:
* [Шаблон базового приложения](https://github.com/yiisoft/yii2-app-basic), простой шаблон фронтенд приложения
* [Шаблон продвинутого приложения](https://github.com/yiisoft/yii2-app-advanced), состоящий из фронтенда, бэкенда, консольных компонент, общих для всего приложения компонент и поддержки окружений (environments)

Для установки шаблонов, ознакомьтесь с инструкциями по ссылкам выше. Чтобы узнать больше об идеях, стоящих за этими шаблонами, и предполагаемом использовании, ознакомьтесь с документацией по [базовому шаблону](http://www.yiiframework.com/doc-2.0/guide-apps-basic.html) и [продвинутому шаблону](tutorial-advanced-app.md).

Если вы не хотите пользоваться шаблонами и предпочитаете начинать с нуля, вы можете найти информацию в разделе о [создании приложения с нуля](tutorial-start-from-scratch.md). Этот подход рекомендуется только для продвинутых пользователей.

# Установка из zip

Установка из zip включает два шага:
- Скачивание шаблона приложения с [yiiframework.com](http://www.yiiframework.com/download/).
- Распаковка скаченного файла.

Если вы хотите получить только сам фреймворк Yii, вы можете скачать zip прямо с [github](https://github.com/yiisoft/yii2-framework/releases). Возможно вы захотите следовать шагам, описанным в разделе [создание приложения с нуля](tutorial-start-from-scratch.md), чтобы создать ваше приложение. Это рекомендуется исключительно для опытных пользователей.

> Подсказка: Фреймворк Yii сам по себе не требует установки в доступное из веб место (следует поступать как раз наоборот). Yii приложение имеет один входной скрипт, который и должен быть единственным файлом доступным веб-пользователям (то есть находиться в веб-директории). Другие PHP скрипты, включая сам фреймворк Yii, должны быть защищены от веб-пользователей, чтобы предотвратить возможные хакерские атаки на ваш сайт.

# Системные требования

Yii 2 требует PHP 5.4.0 или выше. Yii был протестирован на [Apache HTTP server](http://httpd.apache.org/) и [Nginx HTTP server](http://nginx.org/) под управление операционной системы Windows и Linux. Yii также может быть использован на других платформах с другими веб-серверами, предоставляющими возможность использования PHP 5.4.0 или выше.

После установки Yii, вы возможно захотите удостовериться, что ваш сервер полностью удовлетворяет требованиям Yii. Вы можете сделать это, запустив скрипт проверки в вашем браузере или при помощи консольной команды.

Если вы установили шаблон приложения Yii с помощью zip или Composer, вы найдете `requirements.php` файл в корневой директории вашего приложения.

Для того, чтобы запустить скрипт в командной строке, используйте следующую команду (после перехода в директорию, содержащую `requirements.php`):

```
php requirements.php
```

Для того, чтобы запустить скрипт из браузера, вы должны поместить его в вашу веб-директорию и перейти в браузере по ссылке `http://hostname/path/to/yii-app/requirements.php`.

# Рекомендуемая конфигурация Apache

Yii готов работать на Apache с настройками по умолчанию. В качестве меры безопасности, Yii по умолчанию содержит `.htaccess` файлы, чтобы предотвратить доступ к ограниченным ресурсам.

По умолчанию, запросы на Yii сайте поступают на инициализирующий скрипт, обычно называемый `index.php` и расположенный в home-директории приложения. Таким образом, URL представлен в формате `http://hostname/index.php/controller/action/param/value`.

Чтобы скрыть имя инициализирующего файла в URL, добавьте `mod_rewrite` инструкции в `.htaccess` файл в вашей веб-директории (или добавьте инструкции для виртуального хоста в конфигурации Apache `httpd.conf`, секция `Directory` для вашего webroot). Применимая конфигурация будет выглядеть:

```
RewriteEngine on

# If a directory or a file exists, use the request directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# Otherwise forward the request to index.php
RewriteRule . index.php
```

# Рекомендуемая конфигурация для Nginx

Yii также может быть использован с популярным веб-сервером Nginx, при условии установки PHP как [FPM SAPI](http://php.net/install.fpm). Ниже приведена простая конфигурация для сайта на базе Yii на Nginx. Конфигурация говорит серверу посылать все запросы для несуществующих ресурсов через инициализирующий файл, в результате чего получаются "красивые" URL без необходимости указывать `index.php`.

```
server {
    set $yii_bootstrap "index.php";
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/project/web;
    index       $yii_bootstrap;

    access_log  /path/to/project/log/access.log  main;
    error_log   /path/to/project/log/error.log;

    location / {
        # Redirect everything that isn't real file to yii bootstrap file including arguments.
        try_files $uri $uri/ /$yii_bootstrap?$args;
    }

    # uncomment to avoid processing of calls to unexisting static files by yii
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

Когды вы используете эту конфигурацию, вы должны устанавливать `cgi.fix_pathinfo=0` в `php.ini` файле, чтобы избежать множественных необязательных системных вызовов `stat()`.

Учтите, при использовании HTTPS сервера вы должны добавить `fastcgi_param HTTPS on;` для того, чтобы Yii мог корректно опознавать безопасные подключения.