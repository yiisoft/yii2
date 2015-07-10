Instalacja Yii
==============

Yii możesz zainstalować na dwa sposoby, korzystając z [Composera] (https://getcomposer.org/) lub pobierając plik archiwum.
Pierwszy z nich jest najlepszym sposobem, ponieważ pozwala Ci na instalację nowych [rozszerzeń](structure-extensions.md) lub aktualizację Yii przez wywołanie jednej komendy.

Standardowa instalacja Yii skutkuje pobraniem i zainstalowaniem całego framework'a oraz szablonu projektu.
Szablon projektu jest działającym projektem Yii zawierającym w sobie podstawowe funkcjonalności, takie jak logowanie, formularz kontaktowy itp.
Jego kod jest zorganizowany w zalecany sposób, dlatego może służyć jako dobry start dla Twojego projektu.
    
W tej oraz kilku kolejnych sekcjach opiszemy jak zainstalować Yii z tzw. "podstawowym szablonem projektu" oraz jak zaimplementować w nim nowe funkcjonalności. 
Yii dostarcza również drugi, [zaawansowany szablon projektu](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), który jest lepszy
dla programistów tworzących wielowarstwowe aplikacje.

> Info: Podstawowy szablon projektu jest odpowiedni dla 90% aplikacji webowych. Główną różnicą, w porównaniu do zaawansowanego szablonu projektu, jest organizacja kodu.
Jeśli dopiero zaczynasz swoją przygodę z Yii, mocno zalecamy trzymać się podstawowego szablonu, ze względu na jego prostotę oraz wystarczającą funkcjonalność.

Instalacja przez Composer <span id="installing-via-composer"></span>
-----------------------

Jeśli nie posiadasz jeszcze zainstalowanego Composer'a to możesz to zrobić podążając według zamieszczonych na [getcomposer.org](https://getcomposer.org/download/) instrukcji.
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

Po instalacji możesz użyć swojej przeglądarki aby uzyskać dostęp do swojej aplikacji Yii przechodząc pod adres:

```
http://localhost/basic/web/index.php
```

Adres zakłada, że zainstalowałeś Yii w katalogu o nazwie `basic`, bezpośrednio jako katalog głównego katalogu serwera, oraz, że serwer jest uruchomiony na Twojej lokalnej maszynie ('localhost').
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
[rozszerzenie PDO](http://www.php.net/manual/en/pdo.installation.php) oraz odpowiedni sterownik bazy danych (np. `pdo_mysql` dla bazy danych MySQL), jeśli Twoja aplikacja potrzebuje bazy danych.


Konfigurowanie serwerów WWW <span id="configuring-web-servers"></span>
-----------------------

> Info: Możesz pominąć tą sekcję jeśli tylko testujesz Yii, bez zamiaru zamieszczania aplikacji na serwerze produkcyjnym.

Aplikacja zainstalowana według powyższych instrukcji powinna działać na [serwerze Apache HTTP](http://httpd.apache.org/) oraz [serwerze Nginx HTTP](http://nginx.org/), na systemie operacyjnym Windows, Mac OS X oraz Linux posiadających zainstalowany PHP 5.4 lub wyżej.
Yii 2.0 jest również kompatybilne z [facebook'owym HHVM](http://hhvm.com/). Są jednak przypadki, gdzie Yii zachowuje się inaczej w HHVM niż w natywnym PHP, dlatego powinieneś zachować szczególną ostrożność używając HHVM.

Na serwerze produkcyjnym będziesz chciał skonfigurować swój serwer Web tak, aby aplikacja była dostępna pod adresem `http://www.example.com/index.php` zamiast `http://www.example.com/basic/web/index.php`.
Taka konfiguracja wymaga wskazania głównego katalogu serwera na katalog `basic/web`. Jeśli chcesz ukryć `index.php` z adresu URL skorzystaj z informacji opisanych w [Routing and URL Creation](runtime-routing.md)
W tej sekcji dowiesz sie jak skonfigurować Twój serwer Apache lub Nginx aby osiągnąć te cele.

> Info: Ustawiając `basic/web` jako główny katalog serwera zapobiegasz niechcianego dostępu użytkowników końcowych do prywatnego kodu oraz wrażliwych plików aplikacji, które są przechowywane w katalogu `basic`. 
Blokowanie dostępu do tych folderów jest dużą poprawą bezpieczeństwa.

> Info: W przypadku, gdy Twoja aplikacja działa na wspólnym środowisku hostingowym, gdzie nie masz dostępu do modyfikowania konfiguracji serwera, nadal możesz zmienić strukturę aplikacji dla lepszej ochrony. 
Po więcej informacji zajrzyj do działu [wspólne środowisko hostingowe](tutorial-shared-hosting.md)

### Zalecane ustawienia Apache <span id="recommended-apache-configuration"></span>

Użyj następującej konfiguracji serwera Apache w pliku `httpd.conf` lub w konfiguracji wirtualnego hosta.
Pamiętaj, że musisz zamienić ścieżkę `path/to/basic/web` na aktualną ścieżkę do `basic/web` Twojej aplikacji.

```
# Ustaw główny katalog na "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # użyj mod_rewrite do wsparcia "ładnych URL'i"
    RewriteEngine on
    # Jeśli katalog lub plik istnieje, użyj go bezpośrednio
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # W innym przypadku przekieruj żądanie na index.php
    RewriteRule . index.php

    # ...inne ustawienia...
</Directory>
```

### Zalecane ustawienia Nginx <span id="recommended-nginx-configuration"></span>

Aby użyć [Nginx](http://wiki.nginx.org/) powinienieś zainstalować PHP jako [FPM SAPI](http://php.net/install.fpm).
Możesz użyć przedstawionej poniżej konfiguracji Nginx, zastępując jedynie ścieżkę `path/to/basic/web` na aktualną ścieżkę do `basic/web` Twojej aplikacji oraz `mysite.local` na aktualną nazwę hosta.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## nasłuchuj ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Przekieruj wszystko co nie jest prawdziwym plikiem na index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # odkomentuj aby uniknąć przetwarzania żądań do nieistniejących plików przez Yii
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

W przypadku użycia tej konfiguracji, powinienieś ustawić również `cgi.fix_pathinfo=0` w pliku `php.ini` aby zapobiec wielu zbędnych wywołań 'stat()'.

Należy również pamiętać, że podczas pracy na serwerze HTTPS musisz dodać `fastcgi_param HTTPS on;` aby Yii prawidłowo wykrywało, że połączenie jest bezpieczne.