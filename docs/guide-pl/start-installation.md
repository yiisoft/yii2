Instalacja Yii
==============

Yii możesz zainstalować na dwa sposoby, korzystając z [Composera] (https://getcomposer.org/) lub pobierając plik archiwum.
Pierwszy z nich jest najlepszym sposobem, ponieważ pozwala Ci na instalację nowych [rozszerzeń](structure-extensions.md) lub aktualizację Yii przez wywołanie jednej komendy.

Standardowa instalacja Yii skutkuje pobraniem i zainstalowaniem całego framework'a oraz szablonu projektu.
Szablon projektu jest działającym projektem Yii zawierającym w sobie podstawowe funkcjonalności, takie jak logowanie, formularz kontaktowy itp.
Jego kod jest zorganizowany w zalecany sposób, dlatego może służyć jako dobry start dla Twojego projektu.
    
W tej oraz kilku kolejnych sekcjach opiszemy jak zainstalować Yii z tzw. "podstawowym szablonem projektu" oraz jak zaimplementować w nim nowe funkcjonalności. 
Yii dostarcza również drugi [zaawansowany szablon projektu](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), który jest lepszy
dla programistów tworzących wielowarstwowe aplikacje.

> Info: Podstawowy szablon projektu jest odpowiedni dla 90% aplikacji webowych. Główną różnicą w porównaniu do zaawansowanego szablonu projektu jest organizacja kodu.
Jeśli dopiero zaczynasz swoją przygodę z Yii, mocno zalecamy trzymać się podstawowego szablonu ze względu na jego prostotę oraz wystarczającą funkcjonalność.

Instalacja przez Composer <span id="installing-via-composer"></span>
-----------------------

Jeśli nie posiadasz jeszcze zainstalowanego Composer'a to możesz to zrobić podążając według zamieszczonych na [getcomposer.org](https://getcomposer.org/download/).
W systemach operacyjnych Linux i Mac OS X należy wywołać następujące komendy:

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

W systemie Windows należy pobrać i uruchomić [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

W przypadku napotkania jakichkolwiek problemów lub chęci zdobycia większej ilości informacji na temat korzystania z Composer'a zalecamy odniesienie się do [dokumentacji](https://getcomposer.org/doc/)

Jeśli posiadałeś już wcześniej zainstalowanego Composer'a, upewnij się, że jest on zaktualizowany. Composer możesz zaktualizować wywołując komendę 'composer self-update'.

Z zainstalowanym Composer'em możesz przejść do instalacji Yii wywołując poniższe komendy w katalogu dostępnym w sieci web:

    composer global require "fxp/composer-asset-plugin:~1.0.0"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

Pierwsza komenda instaluje [wtyczkę zasobów](https://github.com/francoispluchino/composer-asset-plugin/), która pozwala na zarządzanie zasobami [Bower'a](http://bower.io) oraz [paczkami zależności NPM](https://www.npmjs.com/) przez Composer.
Komendę tą wystarczy wywołać raz, po czym wtyczka będzie już na stałe zainstalowana.
Druga komenda instaluje Yii w katalogu `basic`. Jeśli chcesz, możesz wybrać katalog o innej nazwie.

> Note: Podczas instalacji Composer może zapytać o Twoje dane uwierzytelniające do Github'a. Jest to normalna sytuacja, ponieważ Composer potrzebuje wystarczającego limitu prędkości API 
> do pobrania informacji o pakiecie zależnym z Github'a. Więcej szczegółów znajdziesz w [dokumentacji Composer'a](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Tip: Jeśli chcesz zainstalować najnowszą wersję deweloperską Yii użyj poniższej komendy, która dodaje [opcję stabilności](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>   composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Pamiętaj, że wersja deweloperska Yii nie powinna być używana w wersjach produkcyjnych aplikacji, ponieważ mogą wystąpić niespodziewane błędy.

Instalacja z pliku archiwum <span id="installing-from-archive-file"></span>
-------------------------------

Instalacja Yii z pliku archiwum dotyczy trzech kroków:

1. Pobranie pliku archiwum z [yiiframework.com](http://www.yiiframework.com/download/).
2. Rozpakowanie pliku archiwum do katalogu dostępnego w sieci web.
3. Zmodyfikowanie pliku `config/web.php` przez dodanie sekretnego klucza do elementu konfiguracji `cookieValidationKey`
    (jest to wykonywane automatycznie jeśli instalujesz Yii używając Composer'a):

    ```php
   // !!! wprowadź sekretny klucz tutaj - jest to wymagane przez walidację ciasteczek
   'cookieValidationKey' => 'enter your secret key here',
   ```

Inne opcje instalacji <span id="other-installation-options"></span>
--------------------------

Powyższe instrukcje instalacji pokazują jak zainstalować Yii oraz utworzyć podstawową, działającą aplikację Web, która "działa po wyjęciu z pudełka".
To podejście jest dobrym punktem startowym dla większości projektów, małych bądź dużych. Jest to szczególnie korzystne gdy zaczynasz naukę Yii.

Jednak dostępne są również inne opcje instalacji:

* Jeśli chcesz zainstalować wyłącznie framework i sam budować aplikację od podstaw
* Jeśli chcesz utworzyć bardziej wyrafinowaną aplikację, lepiej nadającą się do zespołu programistycznego, powinienieś rozważyć instalację [zaawansowanego szablonu aplikacji](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md).


Sprawdzenie instalacji <span id="verifying-installation"></span>
--------------------------

Po instalacji możesz użyć swojej przeglądarki aby uzyskać dostęp do zainstalowanej aplikacji Yii przechodząc pod adres:

```
http://localhost/basic/web/index.php
```

Ten adres zakłada, że zainstalowałeś Yii w katalogu o nazwie `basic`, bezpośrednio jako dokument katalogu głównym serwera sieci Web, oraz, że serwer jest uruchomiony na Twojej lokalnej maszynie ('localhost').
Być może będziesz musiał dostosować go do Twojego środowiska instalacyjnego.

![Pomyślna instalacja Yii](images/start-app-installed.png)
Na stronie w przeglądarce powinienieś zobaczyć napis "Congratulations!". Jeśli nie, sprawdź czy zainstalowana wersja PHP jest wystarczająca do wymagań Yii.
Możesz sprawdzić minimalne wymagania na dwa sposoby:

* Używając przeglądarki przejdź pod adres `http://localhost/basic/requirements.php`
* Wywołaj poniższą komendę:

    ```
    cd basic
    php requirements.php
    ```

Powinienieś skonfigurować swoją instalację PHP tak, aby spełniała minimalne wymogi Yii. Najważniejszym z nich jest posiadanie PHP w wersji 5.4 lub wyżej. Powinienieś również zainstalować 
[rozszerzenie PDO](http://www.php.net/manual/en/pdo.installation.php) oraz odpowiedni sterownik bazy danych (np. `pdo_mysql` dla bazy danych MySQL) jeśli Twoja aplikacja potrzebuje bazy danych.


Konfigurowanie serwerów WWW <span id="configuring-web-servers"></span>
-----------------------

> Info: Możesz pominąć tą sekcję jeśli tylko testujesz Yii bez zamiaru zamieszczania aplikacji na serwerze produkcyjnym.

The application installed according to the above instructions should work out of box with either
an [Apache HTTP server](http://httpd.apache.org/) or an [Nginx HTTP server](http://nginx.org/), on
Windows, Mac OS X, or Linux running PHP 5.4 or higher. Yii 2.0 is also compatible with facebook's
[HHVM](http://hhvm.com/). However, there are some edge cases where HHVM behaves different than native
PHP, so you have to take some extra care when using HHVM.

On a production server, you may want to configure your Web server so that the application can be accessed
via the URL `http://www.example.com/index.php` instead of `http://www.example.com/basic/web/index.php`. Such configuration
requires pointing the document root of your Web server to the `basic/web` folder. You may also
want to hide `index.php` from the URL, as described in the [Routing and URL Creation](runtime-routing.md) section.
In this subsection, you'll learn how to configure your Apache or Nginx server to achieve these goals.

> Info: By setting `basic/web` as the document root, you also prevent end users from accessing
your private application code and sensitive data files that are stored in the sibling directories
of `basic/web`. Denying access to those other folders is a security improvement.

> Info: If your application will run in a shared hosting environment where you do not have permission
to modify its Web server configuration, you may still adjust the structure of your application for better security. Please refer to
the [Shared Hosting Environment](tutorial-shared-hosting.md) section for more details.


### Recommended Apache Configuration <span id="recommended-apache-configuration"></span>

Use the following configuration in Apache's `httpd.conf` file or within a virtual host configuration. Note that you
should replace `path/to/basic/web` with the actual path for `basic/web`.

```
# Set document root to be "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # use mod_rewrite for pretty URL support
    RewriteEngine on
    # If a directory or a file exists, use the request directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Otherwise forward the request to index.php
    RewriteRule . index.php

    # ...other settings...
</Directory>
```


### Recommended Nginx Configuration <span id="recommended-nginx-configuration"></span>

To use [Nginx](http://wiki.nginx.org/), you should install PHP as an [FPM SAPI](http://php.net/install.fpm).
You may use the following Nginx configuration, replacing `path/to/basic/web` with the actual path for 
`basic/web` and `mysite.local` with the actual hostname to serve.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

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
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

When using this configuration, you should also set `cgi.fix_pathinfo=0` in the `php.ini` file
in order to avoid many unnecessary system `stat()` calls.

Also note that when running an HTTPS server, you need to add `fastcgi_param HTTPS on;` so that Yii
can properly detect if a connection is secure.
