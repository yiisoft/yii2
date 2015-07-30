Установка Yii
==============

Вы можете установить Yii двумя способами: используя [Composer](https://getcomposer.org/) или скачав архив.
Первый способ предпочтительнее так как позволяет установить новые [расширения](structure-extensions.md)
или обновить Yii одной командой.

> Примечание: В отличие от Yii 1, после стандартной установки Yii 2 мы получаем как фреймворк, так и шаблон приложения.


Установка при помощи Composer <span id="installing-via-composer"></span>
-----------------------

Если Composer еще не установлен это можно сделать по инструкции на
[getcomposer.org](https://getcomposer.org/download/), или одним из нижеперечисленных способов. На Linux или Mac 
используйте следующую команду:

  ```
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  ```

На Windows, скачайте и запустите [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

В случае возникновения проблем или если вам необходима дополнительная информация, обращайтесь
к [документации Composer](https://getcomposer.org/doc/).

Если у вас уже установлен Composer, обновите его при помощи `composer self-update`.

После установки Composer устанавливать Yii можно запустив следующую команду в папке доступной через веб:

    composer global require "fxp/composer-asset-plugin:~1.0.0"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

Первая команда устанавливает [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/),
который позволяет управлять зависимостями пакетов bower и npm через Composer. Эту команду достаточно выполнить один раз.
Вторая команда устанавливает Yii в директорию `basic`. Если хотите, можете выбрать другое имя директории.

Composer установит Yii (шаблонное приложение basic) в папку `basic`.

> Примечание: В процессе установки Composer может запросить логин и пароль от Github потому как у API Github имеется
> ограничение на количество запросов. Это нормально потому как Composer в процессе работы запрашивает у Github большое
>  количество информации для каждого пакета. Вход на Github повышает ограничение по запросам API и Composer может
> продолжить свою работу. Подробнее об этом можно прочитать в
> [документации Composer](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Подсказка: Если вы хотите установить последнюю нестабильную ревизию Yii, можете использовать следующую команду,
> в которой присутствует [опция stability](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Старайтесь не использовать нестабильную версию Yii на рабочих серверах потому как она может внезапно поломать код.


Установка из архива <span id="installing-from-archive-file"></span>
-------------------------------

Установка Yii из архива состоит из трёх шагов:

1. Скачайте архив с [yiiframework.com](http://www.yiiframework.com/download/);
2. Распакуйте скачанный архив в папку, доступную из Web.
3. В файле `config/web.php` добавьте секретный ключ в значение `cookieValidationKey` (при установке через Composer
   это происходит автоматически):

   ```php
   // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
   'cookieValidationKey' => 'enter your secret key here',
   ```

Другие опции установки <span id="other-installation-options"></span>
--------------------------

Выше приведены инструкции по установке Yii в виде базового приложения готового к работе.
Это отличный вариант для небольших проектов или для тех, кто только начинает изучать Yii.

Есть два основных варианта такой установки:

* Если вам нужен только сам фреймворк и вы хотели бы создать приложение с нуля, воспользуйтесь инструкцией, описанной в
разделе «[Создание приложения с нуля](tutorial-start-from-scratch.md)».
* Если хотите начать с более продвинутого приложения, хорошо подходящего для работы в команде, используйте
[шаблон приложения advanced](tutorial-advanced-app.md).


Проверка установки <span id="verifying-installation"></span>
----------------------

После установки приложение будет доступно по следующему URL:

```
http://localhost/basic/web/index.php
```

Здесь подразумевается, что вы установили приложение в директорию `basic` в корневой директории вашего веб сервера
сервер работает локально (`localhost`). Вам может потребоваться предварительно его настроить.

![Успешно установленный Yii](images/start-app-installed.png)

Вы должны увидеть страницу приветствия «Congratulations!». Если нет — проверьте требования Yii одним из способов:

* Браузером перейдите по адресу `http://localhost/basic/requirements.php`
* Или выполните команду в консоли: 

  ```
  cd basic
  php requirements.php
  ```

Для корректной работы фреймворка вам необходима установка PHP, соответствующая его минимальным требованиям. Основное
требование — PHP версии 5.4 и выше. Если ваше приложение работает с базой данных, необходимо установить
[расширение PHP PDO](http://www.php.net/manual/ru/pdo.installation.php) и соответствующий драйвер 
(например, `pdo_mysql` для MySQL).


Настройка веб сервера <span id="configuring-web-servers"></span>
-----------------------

> Информация: можете пропустить этот подраздел если вы только начали знакомиться с фреймворком и пока не разворачиваете
  его на рабочем сервере.

Приложение, установленное по инструкциям, приведённым выше, будет работать сразу как с [Apache](http://httpd.apache.org/),
так и с [Nginx](http://nginx.org/) под Windows и Linux с установленным PHP 5.4 и выше. Yii 2.0 также совместим с
[HHVM](http://hhvm.com/). Тем не менее, в некоторых случаях поведение при работе с HHVM отличается от обычного PHP.
Будьте внимательны.

На рабочем сервере вам наверняка захочется изменить URL приложения с `http://www.example.com/basic/web/index.php`
на `http://www.example.com/index.php`. Для этого необходимо изменить корневую директорию в настройках веб сервера так,
чтобы та указывала на `basic/web`. Дополнительно можно спрятать `index.php` следуя описанию в разделе
«[Разбор и генерация URL](runtime-url-handling.md)». Далее будет показано как настроить Apache и Nginx.

> Информация: Устанавливая `basic/web` корневой директорией веб сервера вы защищаете от нежелательного доступа код и данные,
  находящиеся на одном уровне с `basic/web`. Это делает приложение более защищенным.

> Информация: Если приложение работает на хостинге где нет доступа к настройкам веб сервера, то можно изменить структуру
  приложения как описано в разделе «[Работа на Shared хостинге](tutorial-shared-hosting.md)».


### Рекомендуемые настройки Apache <span id="recommended-apache-configuration"></span>

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


### Рекомендуемые настройки Nginx <span id="recommended-nginx-configuration"></span>

PHP должен быть установлен как [FPM SAPI](http://php.net/manual/ru/install.fpm.php) для [Nginx](http://wiki.nginx.org/).
Используйте следующие параметры Nginx и не забудьте заменить `path/to/basic/web` на корректный путь к `basic/web` и
`mysite.local` на ваше имя хоста.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## слушаем ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/project/log/access.log;
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
