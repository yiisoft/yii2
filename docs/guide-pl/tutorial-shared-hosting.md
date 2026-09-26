Współdzielone środowisko hostujące
==================================

Współdzielone środowiska hostujące są często ograniczone, jeśli chodzi o możliwości ich konfiguracji i struktury folderów. Pomimo to, wciąż, w większości przypadków, 
możesz w takim środowisku uruchomić Yii 2.0 po kilku drobnych modyfikacjach.

Wdrożenie podstawowej aplikacji
-------------------------------

W standardowym współdzielonym środowisku hostującym jest zwykle tylko jeden główny folder publiczny (webroot), zatem wygodniej jest stosować podstawowy szablon projektu. 
Korzystając z instrukcji w sekcji [Instalowanie Yii](start-installation.md), zainstaluj taki szablon lokalnie. Po udanej instalacji, dokonamy kilku modyfikacji, aby aplikacji 
mogła działać na współdzielonym środowisku.

### Zmiana nazwy webroota <span id="renaming-webroot"></span>

Połącz się ze swoim współdzielonym hostem za pomocą np. klienta FTP. Prawdopodobnie zobaczysz listę folderów podobną do poniższej.
 
```
config
logs
www
```

W tym przykładzie, `www` jest folderem webroot. Folder ten może mieć różne nazwy, zwykle stosowane są: `www`, `htdocs` i `public_html`.

Webroot w naszym podstawowym szablonie projektu nazywa się `web`. Przed skopiowaniem aplikacji na serwer, zmień nazwę lokalnego folderu webroot, aby odpowiadała folderowi 
na serwerze, czyli z `web` na `www`, `public_html` lub na inną nazwę, która używana jest na serwerze.

### Folder root FTP jest zapisywalny

Jeśli masz prawa zapisu w folderze poziomu root, czyli tam, gdzie znajdują się foldery `config`, `logs` i `www`, skopiuj foldery `assets`, `commands` itd. bezpośrednio w to 
miejsce.

### Dodatkowe opcje serwera <span id="add-extras-for-webserver"></span>

Jeśli Twój serwer to Apache, będziesz musiał dodać plik `.htaccess` z poniższą zawartością do folderu `web` (czy też `public_html`, bądź jakakolwiek jest jego nazwa), 
gdzie znajduje się plik `index.php`:

```
Options +FollowSymLinks
IndexIgnore */*

RewriteEngine on

# jeśli katalog lub plik istnieje, użyj go bezpośrednio
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# w innym przypadku przekieruj żądanie na index.php
RewriteRule . index.php
```

W przypadku serwera nginx nie powinieneś potrzebować dodatkowego pliku konfiguracyjnego.

### Sprawdzenie wymagań

Aby uruchomić Yii, Twój serwer musi spełniać jego wymagania. Minimalnym wymaganiem jest PHP w wersji 5.4. Możesz sprawdzić wszystkie wymagania, kopiując plik 
`requirements.php` z folderu root do folderu webroot i uruchamiając go w przeglądarce pod adresem `https://example.com/requirements.php`. 
Nie zapomnij o skasowaniu tego pliku po sprawdzeniu wymagań.


Wdrożenie zaawansowanej aplikacji
---------------------------------

Wdrażanie zaawansowanej aplikacji na współdzielonym środowisku jest odrobinę bardziej problematyczne, niż w przypadku podstawowej aplikacji, ponieważ wymaga ona dwóch folderów 
webroot, czego zwykle nie wspierają serwery środowisk współdzielonych. Będziemy musieli odpowiednio dostosować strukturę folderów.

### Przeniesienie skryptów wejściowych do jednego folderu webroot

Na początek potrzebujemy folderu webroot. Stwórz nowy folder i nazwij go tak, jak webroot docelowego serwera, jak opisane zostało to w 
[Zmiana nazwy webroota](#renaming-webroot) powyżej, np. `www` czy też `public_html`. Następnie utwórz poniższą strukturę, gdzie `www` jest folderem webroot, 
który właśnie stworzyłeś:

```
www
    admin
backend
common
console
environments
frontend
...
```

`www` będzie naszym folderem frontend, zatem przenieś tam zawartość `frontend/web`. Do folderu `www/admin` przenieś zawartość `backend/web`. 
W każdym przypadku będziesz musiał zmodyfikować ścieżki w plikach `index.php` i `index-test.php`.

### Rozdzielone sesje i ciasteczka

Backend i frontend zostały stworzone z myślą o uruchamianiu ich z poziomu oddzielnych domen. Jeśli uruchamiamy je z poziomu jednej domeny, frontend i backend będą dzielić 
te same ciasteczka, co może wywołać konflikty. Aby temu zapobiec, zmodyfikuj backendową konfigurację aplikacji `backend/config/main.php` jak poniżej:

```php
'components' => [
    'request' => [
        'csrfParam' => '_backendCSRF',
        'csrfCookie' => [
            'httpOnly' => true,
            'path' => '/admin',
        ],
    ],
    'user' => [
        'identityCookie' => [
            'name' => '_backendIdentity',
            'path' => '/admin',
            'httpOnly' => true,
        ],
    ],
    'session' => [
        'name' => 'BACKENDSESSID',
        'cookieParams' => [
            'path' => '/admin',
        ],
    ],
],
```
