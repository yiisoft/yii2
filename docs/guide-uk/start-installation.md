Встановлення Yii
================

Ви можете встановити Yii двома шляхами: використовуючи менеджер пакетів [Composer](http://getcomposer.org/) 
або завантаживши архів. Перший варіант э бажанішим, тому що дозволить встановити всі нові 
[розширення](structure-extensions.md) або оновити Yii однією командою.

> Примітка: На відміну від Yii 1, після стандартного встановлення Yii 2 ми отримуємо як фреймворк, так і шаблон додатка.


Встановлення за допомогою Composer <span id="installing-via-composer"></span>
----------------------------------

Якщо у вас все ще не вставновлено Composer, то це можна зробити за допомогою інструкції на [getcomposer.org](https://getcomposer.org/download/).
Користувачам Linux та Mac OS X потрібно виконати наступні команди:

    curl -s http://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

При роботі з Windows, необхідно завантажити та запустити [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

В разі наявності проблем або якщо вам необхідна додаткова інформація, зверніться до [документації Composer](https://getcomposer.org/doc/).

Якщо ж Composer вже було встановлено раніше, переконайтесь, що використовуюєте його останню версію.
Ви можете оновити Composer простою командою `composer self-update`.

Після встановлення Composer, встановити Yii можна виконавши наступну команду з директорії, яка доступна через Web:

    composer global require "fxp/composer-asset-plugin:1.0.0"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

Перша команда встановить [плагін ресурсів composer (composer-asset-plugin)](https://github.com/francoispluchino/composer-asset-plugin/),
що дозволить керувати залежностями пакетів Bower та NPM за допомогою Composer. Цю команду потрібно виконати лише один раз.
Друга команда встановить Yii у директорію під назвою `basic`. За бажанням, ви можете обрати іншу директорію.

> Примітка: Під час встановлення може статися так, що Composer запитає облікові дані від вашого профілю на Github,
> через встановлені обмеження запитів Github API. Це є нормальним, оскільки Composer повинен отримати багато інформації
> для всіх пакетів із Github. Надання облікових даних профіля Github збільшить кількість запитів до API, потрібних для
> подальшої роботи Composer. Для більш детальної інформації, будь ласка, зверніться до
> [документації Composer](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Підказка: Якщо ви хочете встановити останню нестабільну версію Yii, ви можете виконати наступну команду,
> яка додає опцію [stability](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Варто зауважити, що нестабільну версію Yii не можна використовувати на робочому сервері, оскільки вона може порушити
> виконання робочого коду.


Встановлення з архіву <span id="installing-from-archive-file"></span>
---------------------

Встановлення Yii з архіву складається з трьох кроків:

1. Завантажте архів за адресою [yiiframework.com](http://www.yiiframework.com/download/);
2. Розпакуйте архів в директорію, доступну через Web.
3. Відредагуйте файл конфігурації `config/web.php` - необхідно заповнити секретний ключ до пункту `cookieValidationKey`
   (це виконуєтся автоматично при вставленні Yii через Composer):

   ```php
   // !!! встановити секретний ключ до наступного пункту (якщо порожній) - це необхідно для валідації кукі
   'cookieValidationKey' => 'enter your secret key here',
   ```


Інші параметри встановлення <span id="other-installation-options"></span>
---------------------------

Вище наведені інструкції по встановленню Yii, які також створюють базовий веб-додаток, готового до роботи.
Це відмінний варіант для невеликих проектів або для тих, хто тільки розпочинає вивчати Yii.

Але є ще й інші варіанти встановлення:

* Якщо вам потрібен тільки один фреймворк і ви хотіли б створити додаток з нуля, використовуйте інструкцію, 
  що описана у розділі [Створення додатка з нуля](tutorial-start-from-scratch.md).
* Якщо хочете розпочати з більш насиченого додатка, який добре підходить для роботи в команді, використовуйте
  [разширений шаблон додатка](tutorial-advanced-app.md).


Перевірка встановлення <span id="verifying-installation"></span>
----------------------

Після встановлення, ви можете перевірити за допомогою браузера свій встановлений додаток Yii за наступним URL:

```
http://localhost/basic/web/index.php
```

Даний URL передбачає встановлення додатка в директорію `basic` базової директорії вашого локального веб-сервера (`localhost`).
Можливо вам знадобиться підкорегувати налаштування свого сервера.

![Успішно встановленний Yii](images/start-app-installed.png)

Ви повинні побачити сторінку браузера із привітанням "Congratulations!". Якщо ні — провірте, чи задовільняють
налаштування PHP вимогам Yii одним із способів:

* Браузером перейдіть на URL `http://localhost/basic/requirements.php`
* Або виконайте наступні команди в консолі: 

  ```
  cd basic
  php requirements.php
  ```

Для коректної роботи фреймворка вам необхідно мати PHP, який відповідає його мінімальним вимогам. 
Основна вимога — PHP версії 5.4 або вище. Якщо ваш додаток працює з базою даних, необхідно встановити
[розширення PHP PDO](http://www.php.net/manual/en/pdo.installation.php) та відповідний драйвер 
(наприклад, `pdo_mysql` для MySQL).


Налаштування веб серверів <span id="configuring-web-servers"></span>
-------------------------

> Інформація: можете пропустити даний підрозділ, якщо ви тільки розпочали знайомитися з фреймворком 
  і не розгортаєте його на робочому сервері.

Додаток, встановлений за інструкціями, наведеними вище, буде працювати одразу як з [Apache HTTP server](http://httpd.apache.org/),
так і з [Nginx HTTP server](http://nginx.org/) під Windows, Mac OS X чи Linux із встановленим PHP 5.4 або вище.
Yii 2.0 також сумісний із віртуальною машиною [HHVM](http://hhvm.com/) фейсбука, однак є деякі крайні випадки, 
де HHVM поводиться інакше, ніж рідний PHP, тому ви повинні бути дуже уважними при використанні HHVM.  

На рабочому сервері вам напевно захочеться змінити URL додатку з `http://www.example.com/basic/web/index.php`
на `http://www.example.com/index.php`. Для цього необхідно змінити кореневу директорію в налаштуваннях веб сервера на `basic/web`.
Додатково можно сховати `index.php` із URL, як це описано у розділі [Маршрутизація та створення URL](runtime-routing.md). 
Далі буде показано як налаштувати Apache і Nginx для цих цілей.

> Інформація: Встанновлюючи `basic/web` кореневою директорією веб-сервера, ви забороняєте кінцевим користувачам доступ
  до приватного коду додатка та важливим даним, які знаходяться на одному рівні з `basic/web`. Це робить додаток більш захищенним.

> Інформація: Якщо додаток працює на хостингу, де немає доступу до налаштувань сервера, ви всеодно можете змінити структуру
  додатка для покращення безпеки, як описано в розділі [Робота на shared хостингу](tutorial-shared-hosting.md).


### Рекомендовані налаштування Apache <span id="recommended-apache-configuration"></span>

Додайте наступний код до файлу конфігурации Apache `httpd.conf` або в конфігураційний файл віртуального хоста. 
Не забудьте замінити `path/to/basic/web` на коректний шлях до `basic/web`.

```
# Встановлюємо кореневою директорією "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # використаємо mod_rewrite для підтримки гарних URL
    RewriteEngine on
    # Якщо запитуваний файл або директорія існують - звертаємось до них напряму
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Якщо ні - перенаправляємо запит на index.php
    RewriteRule . index.php

    # ...інші налаштування...
</Directory>
```


### Рекомендовані налаштування Nginx <span id="recommended-nginx-configuration"></span>

Для використання [Nginx](http://wiki.nginx.org/) вам потрібно встановити PHP як [FPM SAPI](http://php.net/install.fpm).
Використовуйте наступні параметри Nginx, замінивши `path/to/basic/web` на коректний шлях до `basic/web`,
а `mysite.local` на бажаний домен.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## слухаємо ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Перенаправляємо всі запити від неіснуючих директорій або файлів на index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # розкоментуйте строки нижче для запобігання обробки звернень Yii до неіснуючих статичних файлів
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Використовуючи дану конфігурацію встановіть `cgi.fix_pathinfo=0` в `php.ini`, щоб запобігти зайвим системним викликам `stat()`.

Врахуйте також, що при використанні HTTPS необхідно задавати `fastcgi_param HTTPS on;` щоб Yii міг корректно 
визначати захищене з’єднання.
