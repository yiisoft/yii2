Wersjonowanie
=============

Cechą dobrego API jest jego *wersjonowanie*: zmiany i nowe funkcjonalności powinny być implementowane w nowych wersjach API, zamiast 
ciągłych modyfikacji jednej już istniejącej. W przeciwieństwie do aplikacji Web, nad którymi ma się pełną kontrolę zarówno po stronie 
klienta, jak i serwera, nad API zwykle nie posiada się kontroli po stronie klienta. Z tego powodu niezwykle istotnym jest, aby zachować 
pełną wsteczną kompatybilność (BC = backward compatibility), kiedy to tylko możliwe. Jeśli konieczne jest wprowadzenie zmiany, która 
może nie spełniać BC, należy wprowadzić ją w nowej wersji API, z kolejnym numerem. Istniejące klienty mogą wciąż używać starej, 
działającej wersji API, a nowe lub uaktualnione klienty mogą otrzymać nową funkcjonalność oferowaną przez kolejną wersję API. 

> Tip: Zapoznaj się z [Wersjonowaniem semantycznym](https://semver.org/lang/pl/), aby uzyskać więcej informacji na temat nazewnictwa 
  numerów wersji.

Jedną z często spotykanych implementacji wersjonowania API jest dodawanie numeru wersji w adresach URL API.
Dla przykładu `https://example.com/v1/users` oznacza punkt końcowy `/users` API w wersji 1. 

Inną metodą wersjonowania API, która zyskuje ostatnio popularność, jest umieszczanie numeru wersji w nagłówkach HTTP żądania. Zwykle 
używa się do tego nagłówka `Accept`:

```
// poprzez parametr
Accept: application/json; version=v1
// poprzez dostarczany typ zasobu
Accept: application/vnd.company.myapp-v1+json
```

Obie metody mają swoje wady i zalety i wciąż prowadzone są dyskusje na ich temat. Poniżej prezentujemy strategię wersjonowania, która 
w praktyczny sposób łączy je obie:

* Umieść każdą główną wersję implementacji API w oddzielnym module, którego ID odpowiada numerowi głównej wersji (np. `v1`, `v2`).
  Adresy URL API będą zawierały numery głównych wersji.
* Wewnątrz każdej głównej wersji (i w związku z tym w każdym odpowiadającym jej module), użyj nagłówka HTTP `Accept`, aby określić 
pomniejszy numer wersji i napisz warunkowy kod odpowiadający temu numerowi.

Każdy moduł obsługujący główną wersję powinien zawierać klasy zasobów i kontrolerów odpowiednie dla tej wersji. W celu lepszego 
rozdzielenia zadań kodu, możesz trzymać razem zestaw podstawowych wspólnych klas zasobów i kontrolerów w jednym miejscu i rozdzielać go 
na podklasy w każdym module wersji. W podklasie implementujesz bazowy kod, taki jak `Model::fields()`.

Struktura Twojego kodu może wyglądać jak poniższa:

```
api/
    common/
        controllers/
            UserController.php
            PostController.php
        models/
            User.php
            Post.php
    modules/
        v1/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
            Module.php
        v2/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
            Module.php
```

Konfiguracja Twojej aplikacji mogłaby wyglądać następująco:

```php
return [
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'app\modules\v2\Module',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/user', 'v1/post']],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v2/user', 'v2/post']],
            ],
        ],
    ],
];
```

Rezultatem powyższego kodu będzie skierowanie pod adresem `https://example.com/v1/users` do listy użytkowników w wersji 1, podczas gdy 
`https://example.com/v2/users` pokaże użytkowników w wersji 2.

Dzięki podziałowi na moduły, kod różnych głównych wersji może być dobrze izolowany, ale jednocześnie wciąż możliwe jest ponowne 
wykorzystanie wspólnego kodu poprzez wspólną bazę klas i dzielonych zasobów.

Aby prawidłowo obsłużyć pomniejsze numery wersji, możesz wykorzystać funkcjonalność negocjatora zawartości dostarczaną przez behavior 
[[yii\filters\ContentNegotiator|contentNegotiator]]. Ustawi on właściwość [[yii\web\Response::acceptParams]], kiedy już zostanie 
ustalone, który typ zasobów wspierać.

Przykładowo, jeśli żądanie jest wysłane z nagłówkiem HTTP `Accept: application/json; version=v1`, po negocjacji zawartości 
[[yii\web\Response::acceptParams]] będzie zawierać wartość `['version' => 'v1']`.

Bazując na informacji o wersji w `acceptParams`, możesz napisać obsługujący ją warunkowy kod w miejscach takich jak akcje, klasy 
zasobów, serializatory, itp., aby zapewnić odpowiednią funkcjonalność.

Ponieważ pomniejsze wersje z definicji wymagają zachowania wstecznej kompatybilności, w kodzie nie powinno znaleźć się zbyt wiele 
miejsc, gdzie numer wersji będzie sprawdzany. W przeciwnym wypadku możliwe, że konieczne będzie utworzenie kolejnej głównej wersji API.
