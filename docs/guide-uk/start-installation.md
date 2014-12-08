Встановлення Yii
================

Ви можете встановити Yii двома шляхами: використовуючи [Composer](http://getcomposer.org/) або завантаживши архів.
Перший варіант бажаніший тому, що дозволить встановити всі нові [розширення](structure-extensions.md)
або оновити Yii однією командою.


Встановлення за допомогою Composer <a name="installing-via-composer"></a>
----------------------------------

Якщо Composer все ще не встановлено, то це можна зробити за допомогою інструкції на [getcomposer.org](https://getcomposer.org/download/), або одним із перерахованих способів:

* на Linux або Mac, використовуйте наступну команду:

  ```
  curl -s http://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  ```
* на Windows, завантажте і запустіть [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

В разі наявності проблем або якщо вам необхідна додаткова інформація, зверніться до [документації Composer](https://getcomposer.org/doc/) .

Після встановлення Composer встановити Yii можна виконавши наступну команду з директорії, яка доступна через Web:

```
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

Composer встановить Yii (базовий додаток basic) в директорію `basic`.

> **Підказка**: Якщо хочете встановити останню нестабільну версію Yii, ви можете добавити ключ `stability`:
```
composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
```
Варто замітити, що нестабільну версію Yii неможна використовувати на робочому сервері.


Встановлення з архіву <a name="installing-from-archive-file"></a>
-------------------------------

Встановлення Yii з архіву складається з двох кроків:

1. Завантажте архів за адресою [yiiframework.com](http://www.yiiframework.com/download/yii2-basic);
2. Розпакуйте архів в директорію, доступну через Web.


Інші параметри встановлення <a name="other-installation-options"></a>
--------------------------

Вище наведені інструкції по встановленню Yii у вигляді базового додатку готового до роботи.
Це відмінний варіант для невеликих проектів або для тих, хто тільки розпочинає вивчати Yii.

Є два основних варіанта данного встановлення:

* Якщо вам потрібен тільки один фреймворк і ви хотіли б створити додаток з нуля, використовуйте інструкцію, яка описана у розділі «[Створення додатка з нуля](tutorial-start-from-scratch.md)».
* Якщо хочете розпочати з більш насиченого додатка, який добре підходить для роботи в команді, використовуйте
[шаблон додатка advanced](tutorial-advanced-app.md).


Перевірка встановлення <a name="verifying-installation"></a>
----------------------

Якщо ви встановили додаток в теку `basic` базової директорії вашого веб сервера і ім’я сервера `hostname`,
запустити додаток можна відкривши наступний URL через браузер:

```
http://hostname/basic/web/index.php
```

![Успішно встановленний Yii](../guide/images/start-app-installed.png)

Ви повинні побачити сторінку привітання «Congratulations!». Якщо ні — провірте вимоги Yii одним із способів:

* Браузером перейдіть на адресу `http://hostname/basic/requirements.php`
* Або виконайте команду в консолі: 

  ```
  cd basic
  php requirements.php
  ```

Для коректної роботи фреймворка вам необхідно мати PHP, який відповідає його мінімальним вимогам. Основна вимога — PHP версії 5.4 и вище. Якщо ваш додаток працює з базою даних, необхідно встановити
[розширення PHP PDO](http://www.php.net/manual/ru/pdo.installation.php) і відповідний драйвер 
(наприклад, `pdo_mysql` для MySQL).


Налаштування веб сервера <a name="configuring-web-servers"></a>
-----------------------

> Інформація: можете пропустити даний підрозділ, якщо ви тільки розпочали знайомитися з фреймворком і не розгортаєте його на робочому сервері.

Додаток, встановлений за інструкціями, наведеними вище, буде працювати зразу як з [Apache](http://httpd.apache.org/),
так і з [Nginx](http://nginx.org/) під Windows і Linux.

На рабочому сервері вам напевно захочеться змінити URL додатку з `http://hostname/basic/web/index.php`
на `http://hostname/index.php`. Для цього необхідно змінити кореневу директорію в налаштуваннях веб сервера так, щоб ті
вказували на `basic/web`. Додатково можно сховати `index.php` відповідно описанню в розділі «[Розбір і генерація URL](runtime-url-handling.md)». 
Далі буде показано як налаштувати Apache і Nginx.

> Інформація: Встанновлюючи `basic/web` кореневою директорією веб сервера ви захищаете від небажаного доступа код і дані, які знаходяться на одному рівні з `basic/web`. Це робить додаток більш захищенним.

> Інформація: Якщо додаток працює на хостингу, де немає доступу до налаштувань сервера, то можна змінити структуру додатка, як описано в розділі «[Робота на Shared хостингу](tutorial-shared-hosting.md)».


### Рекомендовані налаштування Apache <a name="recommended-apache-configuration"></a>

Добавте наступне в `httpd.conf` Apache або в конфігураційний файл віртуального хоста. Не забудьте замінити
`path/to/basic/web` на коректний шлях до `basic/web`.

```
# Встановлюємо кореневою директорією "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    # Якщо запитувана в URL директорія або файл відсутні звертаємось до них напряму
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Якщо ні - перенаправляємо запит на index.php
    RewriteRule . index.php

    # ...інші налаштування...
</Directory>
```


### Рекомендовані налаштування Nginx <a name="recommended-nginx-configuration"></a>

PHP повинен бути встановлений як [FPM SAPI](http://php.net/manual/ru/install.fpm.php) для [Nginx](http://wiki.nginx.org/).
Використовуйте наступні параметри Nginx і не забудьте замінити `path/to/basic/web` на коректний шлях до `basic/web`.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## слухаєм ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/project/log/access.log main;
    error_log   /path/to/project/log/error.log;

    location / {
        # Перенаправляємо всі запити до неіснуючих директорій або файлів на index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # розкоментуйте строки нище для запобігання обробки Yii звернень до неіснуючих статичних файлів
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

Використовуючи дану конфігурацію встановіть `cgi.fix_pathinfo=0` в `php.ini` щоб запобігти зайвим системним визовам `stat()`.

Врахуйте також, що при використанні HTTPS необхідно задавати `fastcgi_param HTTPS on;` щоб Yii міг корректно оприділяти захищене
з’єднання.
