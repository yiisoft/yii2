Instalacja Yii
==============

Yii możesz zainstalować na dwa sposoby, korzystając z [Composera] (https://getcomposer.org/) lub pobierając plik archiwum.
Pierwszy z nich jest najlepszym sposobem, ponieważ pozwala na instalację nowych [rozszerzeń](structure-extensions.md) lub aktualizację Yii przez wywołanie jednej komendy.

Standardowa instalacja Yii skutkuje pobraniem i zainstalowaniem całego framework'a oraz szablonu projektu.
Szablon projektu jest działającym projektem Yii zawierającym w sobie podstawowe funkcjonalności, takie jak logowanie, formularz kontaktowy itp.
Jego kod jest zorganizowany w zalecany sposób, dlatego może służyć jako dobry start dla Twojego projektu.
    
W tej oraz kilku kolejnych sekcjach opiszemy jak zainstalować Yii z tzw. "podstawowym szablonem projektu" oraz jak zaimplementować w nim nowe funkcjonalności. 
Yii dostarcza również drugi, [zaawansowany szablon projektu](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), który jest lepszy
dla programistów tworzących wielowarstwowe aplikacje.

> Info: Podstawowy szablon projektu jest odpowiedni dla 90% aplikacji webowych. Główną różnicą, w porównaniu do zaawansowanego szablonu projektu, jest organizacja kodu.
> Jeśli dopiero zaczynasz swoją przygodę z Yii, mocno zalecamy trzymać się podstawowego szablonu, ze względu na jego prostotę oraz wystarczającą funkcjonalność.

Instalacja przez Composer <span id="installing-via-composer"></span>
-----------------------

Jeśli nie posiadasz jeszcze zainstalowanego Composera to możesz to zrobić podążając według zamieszczonych na [getcomposer.org](https://getcomposer.org/download/) instrukcji.
W systemach operacyjnych Linux i Mac OS X należy wywołać następujące komendy:

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

W systemie Windows należy pobrać i uruchomić [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

W przypadku napotkania jakichkolwiek problemów lub chęci zdobycia większej ilości informacji na temat korzystania z Composer'a zalecamy odniesienie się do 
[dokumentacji](https://getcomposer.org/doc/)

Jeśli posiadałeś już wcześniej zainstalowanego Composera, upewnij się, że jest on zaktualizowany. Composer można zaktualizować wywołując komendę 'composer self-update'.

Z zainstalowanym Composerem możesz przejść do instalacji Yii wywołując poniższe komendy w katalogu dostępnym z poziomu sieci web:

```bash
composer global require "fxp/composer-asset-plugin:^1.2.0"
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

Pierwsza komenda instaluje [wtyczkę zasobów](https://github.com/francoispluchino/composer-asset-plugin/), która pozwala na zarządzanie zasobami [Bowera](http://bower.io) oraz 
[paczkami zależności NPM](https://www.npmjs.com/) przez Composer.
Komendę tę wystarczy wywołać raz, po czym wtyczka będzie już na stałe zainstalowana.
Druga komenda instaluje Yii w katalogu `basic`. Jeśli chcesz, możesz wybrać katalog o innej nazwie.

> Note: Podczas instalacji Composer może zapytać o Twoje dane uwierzytelniające w serwisie Github. Jest to normalna sytuacja, ponieważ Composer potrzebuje wystarczającego limitu 
> prędkości API do pobrania informacji o pakiecie zależnym z Githuba. Więcej szczegółów znajdziesz w 
> [dokumentacji Composera](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Tip: Jeśli chcesz zainstalować najnowszą wersję deweloperską Yii, użyj poniższej komendy, która dodaje [opcję stabilności](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
> ```bash
> composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
> ```
>
> Pamiętaj, że wersja deweloperska Yii nie powinna być używana w wersjach produkcyjnych aplikacji, ponieważ mogą wystąpić niespodziewane błędy.

Instalacja z pliku archiwum <span id="installing-from-archive-file"></span>
-------------------------------

Instalacja Yii z pliku archiwum składa się z trzech kroków:

1. Pobranie pliku archiwum z [yiiframework.com](http://www.yiiframework.com/download/).
2. Rozpakowanie pliku archiwum do katalogu dostępnego z poziomu sieci web.
3. Zmodyfikowanie pliku `config/web.php` przez dodanie sekretnego klucza do elementu konfiguracji `cookieValidationKey`
   (jest to wykonywane automatycznie, jeśli instalujesz Yii używając Composera):

    ```php
   // !!! wprowadź sekretny klucz tutaj - jest to wymagane do walidacji ciasteczek
   'cookieValidationKey' => 'enter your secret key here',
   ```

Inne opcje instalacji <span id="other-installation-options"></span>
--------------------------

Powyższe instrukcje instalacji pokazują, jak zainstalować Yii oraz utworzyć podstawową, działającą aplikację Web, która "działa po wyjęciu z pudełka".
To podejście jest dobrym punktem startowym dla większości projektów, małych bądź dużych. Jest to szczególnie korzystne, gdy zaczynasz naukę Yii.

Dostępne są również inne opcje instalacji:

* Jeśli chcesz zainstalować wyłącznie framework i sam budować aplikację od podstaw
* Jeśli chcesz utworzyć bardziej wyrafinowaną aplikację, lepiej nadającą się dla zespołu programistycznego, powinienieś rozważyć instalację 
  [zaawansowanego szablonu aplikacji](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md).


Sprawdzenie instalacji <span id="verifying-installation"></span>
--------------------------

Po zakończeniu instalacji, skonfiguruj swój serwer (zobacz następną sekcję) lub użyj 
[wbudowanego serwera PHP](https://secure.php.net/manual/en/features.commandline.webserver.php), uruchamiając poniższą komendę w konsoli z poziomu folderu 
`web` projektu:
 
```bash
php yii serve
```

> Note: Domyślnym portem, na którym serwer HTTP nasłuchuje, jest 8080. Jeśli jednak ten port jest już w użyciu lub też chcesz obsłużyć wiele aplikacji w ten sposób, 
możesz podać inny numer portu, dodając argument --port:

```bash
php yii serve --port=8888
```

Możesz teraz użyć swojej przeglądarki, aby uzyskać dostęp do zainstalowanej aplikacji Yii przechodząc pod adres:

```
http://localhost:8080/
```

![Poprawna instalacja Yii](images/start-app-installed.png)

Powinienieś zobaczyć stronę z napisem "Congratulations!" (Gratulacje!). Jeśli nie, sprawdź czy zainstalowane elementy środowiska spełniają wymagania Yii. 
Możesz sprawdzić minimalne wymagania na dwa sposoby:

* Skopiuj plik `/requirements.php` do `/web/requirements.php`, a następnie przejdź do przeglądarki i uruchom go przechodząc pod adres `http://localhost/requirements.php`
* Lub też uruchom następujące komendy:

  ```bash
  cd basic
  php requirements.php
  ```

Powinienieś skonfigurować swoją instalację PHP tak, aby spełniała minimalne wymogi Yii. Najważniejszym z nich jest posiadanie PHP w wersji 5.4 lub wyższej.
Powinienieś również zainstalować [rozszerzenie PDO](http://www.php.net/manual/en/pdo.installation.php) oraz odpowiedni sterownik bazy danych (np. `pdo_mysql` dla bazy danych 
MySQL), jeśli Twoja aplikacja potrzebuje bazy danych.


Konfigurowanie serwerów WWW <span id="configuring-web-servers"></span>
-----------------------

> Info: Możesz pominąć tą sekcję, jeśli tylko testujesz Yii, bez zamiaru zamieszczania aplikacji na serwerze produkcyjnym.

Aplikacja zainstalowana według powyższych instrukcji powinna działać na [serwerze Apache HTTP](http://httpd.apache.org/) oraz [serwerze Nginx HTTP](http://nginx.org/), na systemie 
operacyjnym Windows, Mac OS X oraz Linux, posiadającym zainstalowane PHP 5.4 lub wyższe.
Yii 2.0 jest również kompatybilne z [facebook'owym HHVM](http://hhvm.com/). Są jednak przypadki, gdzie Yii zachowuje się inaczej w HHVM niż w natywnym PHP, dlatego powinieneś zachować 
szczególną ostrożność używając HHVM.

Na serwerze produkcyjnym będziesz chciał skonfigurować swój serwer Web tak, aby aplikacja była dostępna pod adresem `http://www.example.com/index.php` zamiast 
`http://www.example.com/basic/web/index.php`.
Taka konfiguracja wymaga wskazania głównego katalogu serwera jako katalogu `basic/web`. Jeśli chcesz ukryć `index.php` w adresie URL, skorzystaj z informacji opisanych w dziale 
[routing i tworzenie adresów URL](runtime-routing.md).
W tej sekcji dowiesz się, jak skonfigurować Twój serwer Apache lub Nginx, aby osiągnąć te cele.

> Info: Ustawiając `basic/web` jako główny katalog serwera unikasz niechcianego dostępu użytkowników końcowych do prywatnego kodu oraz wrażliwych plików aplikacji, które są 
przechowywane w katalogu `basic`. 
Blokowanie dostępu do tych folderów jest dużą poprawą bezpieczeństwa.

> Info: W przypadku, gdy Twoja aplikacja działa na wspólnym środowisku hostingowym, gdzie nie masz dostępu do modyfikowania konfiguracji serwera, nadal możesz zmienić strukturę 
> aplikacji dla lepszej ochrony. 
> Po więcej informacji zajrzyj do działu [wspólne środowisko hostingowe](tutorial-shared-hosting.md).

### Zalecane ustawienia Apache <span id="recommended-apache-configuration"></span>

Użyj następującej konfiguracji serwera Apache w pliku `httpd.conf` lub w konfiguracji wirtualnego hosta.
Pamiętaj, że musisz zamienić ścieżkę `path/to/basic/web` na aktualną ścieżkę do `basic/web` Twojej aplikacji.

```apache
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
Możesz użyć przedstawionej poniżej konfiguracji Nginx, zastępując jedynie ścieżkę `path/to/basic/web` aktualną ścieżką do `basic/web` Twojej aplikacji oraz 
`mysite.local` aktualną nazwą hosta.

```nginx
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## nasłuchuj ipv4
    #listen [::]:80 default_server ipv6only=on; ## nasłuchuj ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Przekieruj wszystko co nie jest prawdziwym plikiem na index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # dkomentuj aby uniknąć przetwarzania żądań do nieistniejących plików przez Yii
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

W przypadku użycia tej konfiguracji, powinienieś ustawić również `cgi.fix_pathinfo=0` w pliku `php.ini`, aby zapobiec wielu zbędnym wywołaniom 'stat()'.

Należy również pamiętać, że podczas pracy na serwerze HTTPS musisz dodać `fastcgi_param HTTPS on;`, aby Yii prawidłowo wykrywało, że połączenie jest bezpieczne.
