# Używanie Yii jako mikroframeworka

Yii może być z powodzeniem wykorzystywane bez dodatkowych funkcjonalności dostarczanych przez prosty i zaawansowany szablon aplikacji. Inaczej mówiąc, 
Yii już jest samo w sobie mikroframeworkiem. Do pracy z Yii nie jest wymagane, aby struktura folderów była dokładnie taka, jak pokazana w szablonach.

Jest to szczególnie korzystne, kiedy nie potrzebujesz gotowego kodu szablonów, jak w przypadku assetów luc widoków. Jednym z takich przypadków jest budowa JSON API. 
W tej sekcji pokażemy jak to zrobić.

## Instalacja Yii

Stwórz folder dla plików swojego projektu i ustaw go jako aktywną ścieżkę. Komendy używane w przykładach oparte są na składni UNIXowej, ale podobne dostępne są również w Windows.

```bash
mkdir micro-app
cd micro-app
```

> Note: minimalna wiedza na temat użytkowania Composera jest wymagana w celu kontynuacji. Jeśli nie wiesz, jak używać Composera, prosimy o zapoznanie się najpierw z [Przewodnikiem po Composerze](https://getcomposer.org/doc/00-intro.md).

Stwórz plik `composer.json` w folderze `micro-app`, używając swojego ulubionego edytora i dodaj co następuje:

```json
{
    "require": {
        "yiisoft/yii2": "~2.0.0"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    ]
}
```

Zapisz plik i uruchom komendę `composer install`. Dzięki temu zainstalujesz framework i wszystkie jego zależności.

## Tworzenie struktury projektu

Po zainstalowaniu frameworka, czas na utworzenie [punktu wejścia](structure-entry-scripts.md) dla aplikacji. Punkt wejścia to pierwszy plik, który będzie uruchamiany, 
podczas startu aplikacji. Ze względów bezpieczeństwa, zalecane jest, aby plik punktu wejścia umieścić w osobnym folderze, który będzie ustawiony jako bazowy folder aplikacji.

Stwórz folder `web` i umieść w nim plik `index.php` z następującą zawartością:

```php 
<?php

// zakomentuj poniższe dwie linie przy wydaniu aplikacji na środowisku produkcyjnym
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require __DIR__ . '/../config.php';
(new yii\web\Application($config))->run();
```

Stwórz również plik `config.php`, który będzie zawierał całą konfigurację aplikacji:

```php
<?php
return [
    'id' => 'micro-app',
    // ścieżką bazową aplikacji będzie folder `micro-app`
    'basePath' => __DIR__,
    // w tym miejscu określamy, gdzie aplikacja ma szukać wszystkich kontrolerów
    'controllerNamespace' => 'micro\controllers',
    // ustawiamy alias, aby umożliwić autoładowanie klas z przestrzeni nazw 'micro'
    'aliases' => [
        '@micro' => __DIR__,
    ],
];
```

> Info: Pomimo że konfiguracja mogłaby być przechowywana w pliku `index.php`, zalecane jest, aby zapisana była osobno.
> Dzięki temu może być również wykorzystywana dla aplikacji konsolowej, jak pokazano to poniżej.

Twój projekt jest już gotowy do rozpoczęcia kodowania. Od Ciebie również zależy struktura jego folderów, dopóki jak będziesz pamiętać o poprawnych przestrzeniach nazw.

## Tworzenie pierwszego kontrolera

Stwórz folder `controllers` i dodaj w nim plik `SiteController.php`, który będzie domyślnym kontrolerem obsługującym żądania bez wskazanej wyraźnie ścieżki.

```php
<?php

namespace micro\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return 'Hello World!';
    }
}
```

Jeśli chcesz użyć innej nazwy dla tego kontrolera, nie krępuj się - musisz jedynie skonfigurować odpowiednio [[yii\base\Application::$defaultRoute]].
Dla przykładu, jeśli chcesz go zmienić na `DefaultController`, ustaw `'defaultRoute' => 'default/index'` w konfiguracji.

W tym momencie struktura projektu powinna wyglądać jak poniżej:

```
micro-app/
├── composer.json
├── web/
    └── index.php
└── controllers/
    └── SiteController.php
```

Jeśli nie ustawiłeś jeszcze serwera web, być może zechcesz zerknąć na [pliki przykładów konfiguracji serwera web](start-installation.md#configuring-web-servers).
Inną opcją jest skorzystanie z komendy `yii serve`, która użyje wbudowanego w PHP serwera web. Możesz uruchomić ją z poziomu folderu `micro-app/` za pomocą:

    vendor/bin/yii serve --docroot=./web

Uruchomienie adresu URL aplikacji w przeglądarce powinno zaowocować teraz komunikatem "Hello World!", który jest zwracany w `SiteController::actionIndex()`.

> Info: W naszym przykładzie zmieniliśmy domyślną przestrzeń nazw aplikacji `app` na `micro`, aby zademonstrować, 
> że nie ma potrzeby być ograniczonym przez tę nazwę (w przypadku, gdyby ktoś myślał, że jednak jest). Po zmianie jej na inną,  
> należy jedynie zmodyfikować odpowiednio [[yii\base\Application::$controllerNamespace|przestrzeń nazw kontrolerów]] i ustawić właściwy alias.


## Tworzenie API REST

Aby zademonstrować, jak korzystać z naszego "mikroframeworka", stworzymy proste API REST dla postów.

Aby API mogło zwrócić jakieś dane, najpierw potrzebujemy ich bazy. Dodaj konfigurację połączenia z bazą danych do konfiguracji aplikacji:

```php
'components' => [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'sqlite:@micro/database.sqlite',
    ],
],
```

> Info: Używamy w tym przykładzie bazy danych sqlite dla uproszczenia. Aby zapoznać się z innymi opcjami, przejdź do [przewodnika po bazach danych](db-dao.md).

Następnie tworzymy [migrację bazodanową](db-migrations.md), aby skonstruować tabelę postów.
Upewnij się, że posiadasz oddzielny plik konfiguracji, jak zostało to opisane powyżej, ponieważ musimy teraz uruchomić komendę konsolową, jak poniżej.
Uruchomienie tych komend utworzy plik migracji i wprowadzi migrację do bazy danych:

    vendor/bin/yii migrate/create --appconfig=config.php create_post_table --fields="title:string,body:text"
    vendor/bin/yii migrate/up --appconfig=config.php

Stwórz folder `models` i plik `Post.php` w tym folderze. Poniżej znajdziesz kod dla modelu:

```php
<?php

namespace micro\models;

use yii\db\ActiveRecord;

class Post extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{posts}}';
    }
}
```

> Info: Tak utworzony model jest klasą ActiveRecord, która reprezentuje dane z tabeli `posts`.
> Zapoznaj się z [przewodnikiem po active record](db-active-record.md), aby uzyskać więcej informacji.

Aby obsłużyć posty w naszym API, dodaj `PostController` w `controllers`:

```php
<?php

namespace micro\controllers;

use yii\rest\ActiveController;

class PostController extends ActiveController
{
    public $modelClass = 'micro\models\Post';

    public function behaviors()
    {
        // wyłącz rateLimiter, który do pracy wymaga, aby użytkownik był zalogowany
        $behaviors = parent::behaviors();
        unset($behaviors['rateLimiter']);
        return $behaviors;
    }
}
```

W tym momencie nasze API obsługuje już następujące adresy URL:

- `/index.php?r=post` - wyświetla listę wszystkich postów
- `/index.php?r=post/view&id=1` - wyświetla post o ID 1
- `/index.php?r=post/create` - tworzy post
- `/index.php?r=post/update&id=1` - aktualizuje post o ID 1
- `/index.php?r=post/delete&id=1` - usuwa post o ID 1

Zapoznaj się z poniższymi wskazówkami, które pomogą Ci w dalszym rozwijaniu Twojej aplikacji:

- Aktualnie API rozpoznaje jedynie urlenkodowane dane formularza na wejściu - aby zmienić je w prawdziwe JSON API, 
  musisz skonfigurować [[yii\web\JsonParser]].
- Aby uczynić adresy URL, bardziej przyjaznymi dla użytkownika, musisz skonfigurować ruting.
  Zobacz [przewodnik po rutingu REST](rest-routing.md), który wyjaśnia, jak to zrobić.
- Dodatkowo przeczytaj też sekcję [Dalsze kroki](start-looking-ahead.md), która podpowie jak zaplanować rozwój projektu.
