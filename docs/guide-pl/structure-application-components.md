Komponenty aplikacji
======================

Aplikacje są [lokatorami usług](concept-service-locator.md). Posiadają one zestawy *komponentów aplikacji*, 
które zajmują się dostarczaniem różnych serwisów do obsługi żądań. Dla przykładu,
komponent `urlManager` jest odpowiedzialny za przekierowania żądań do odpowiednich kontrolerów, 
komponent `db` dostarcza serwisy powiązane z bazami danych, itp.


Każdy komponent aplikacji posiada unikalne ID identyfikujące go w całej aplikacji.
Możesz dostać się do tego komponentu przez wyrażenie:

```php
\Yii::$app->componentID
```

Dla przykładu, możesz użyć `\Yii::$app->db` do uzyskania [[yii\db\Connection|połączenia z bazą danych]] lub `\Yii::$app->cache` do uzyskania 
[[yii\caching\Cache|dostępu do pamięci podręcznej]] zarejestrowanej w aplikacji.

Komponent jest tworzony przy pierwszym jego wywołaniu przez powyższe wyrażenie, każde kolejne wywołanie zwróci tą samą instancję tego komponentu.

Komponentami aplikacji może być każdy objekt. Możesz je zarejestrować przez skonfigurowanie parametru [[yii\base\Application::components|components]] w 
[konfiguracji aplikacji](structure-applications.md#application-configurations).
Dla przykładu:

```php
[
    'components' => [
        // rejestracja komponentu "cache" przy użyciu nazwy klasy
        'cache' => 'yii\caching\ApcCache',

        // rejestracja komponentu "db" przy użyciu tablicy konfiguracyjnej
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // rejestracja komponentu "search" przy użyciu funkcji anonimowej
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Info: Możesz zarejestrować tak wiele komponentów jak chcesz, jednak powinieneś robić to rozważnie.
> Komponenty aplikacji są podobne do zmiennych globalnych. 
> Używanie zbyt wielu komponentów może potencjalnie uczynić Twój kod trudniejszym do testowania i utrzymania.
> W wielu przypadkach możesz po prostu utworzyć lokalny komponent i użyć go, kiedy jest to konieczne.


## Bootstrapping komponentów <span id="bootstrapping-components"></span>

Tak, jak było wspomniane wcześniej, komponent aplikacji zostanie zinstancjowany tylko w momencie pierwszego wywołania.
Czasami jednak chcemy, aby komponent został zainstancjowany dla każdego żądania, nawet jeśli nie jest bezpośrednio wywoływany.
Aby to osiągnąć, możesz wylistować ID komponentów we właściwości [[yii\base\Application::bootstrap|bootstrap]] aplikacji.


Dla przykładu, następująca konfiguracja aplikacji zapewnia załadowanie komponentu `log` przy każdym żądaniu:

```php
[
    'bootstrap' => [
        'log',
    ],
    'components' => [
        'log' => [
            // konfiguracja komponentu `log`
        ],
    ],
]
```


## Podstawowe komponenty aplikacji <span id="core-application-components"></span>

Yii posiada podstawowe komponenty aplikacji ze stałymi ID oraz domyślną ich konfiguracją. Dla przykładu,
komponent [[yii\web\Application::request|request]] jest używany do zbierania informacji na temat żądania użytkownika 
oraz przekazanie go do [route'a](runtime-routing.md); [[yii\base\Application::db|db]] reprezentuje 
połączenie z bazą danych, dzięki któremu możesz wykonywać zapytania do bazy.
Z pomocą tych podstawowych komponentów aplikacja jest w stanie obsłużyć żądania użytkowników.

Poniżej znajduje się lista predefiniowanych podstawowych komponentów aplikacji. Możesz je konfigurować lub zmieniać,
tak jak z normalnymi komponentami. Podczas konfigurowania podstawowych komponentów aplikacji, w przypadku nie podania klasy, 
zostanie użyta klasa domyślna.

* [[yii\web\AssetManager|assetManager]]: zarządzanie zasobami oraz ich publikacja.
  Po więcej informacji zajrzyj do sekcji [Assets](structure-assets.md).
* [[yii\db\Connection|db]]: reprezentuje połączenie z bazą danych, dzięki której możliwe jest wykonywanie zapytań.
  Konfigurując ten komponent musisz określić klasę komponentu, tak samo jak inne wymagane właściwości, np. [[yii\db\Connection::dsn|dsn]].
  Po więcej informacji zajrzyj do sekcji [Obiekty dostępu do danych (DAO)](db-dao.md).
* [[yii\base\Application::errorHandler|errorHandler]]: obsługuje błędy oraz wyjątki PHP.
  Po więcej informacji zajrzyj do sekcji [Obsługa błędów](runtime-handling-errors.md).
* [[yii\i18n\Formatter|formatter]]: formatuje dane wyświetlane użytkownikom. Dla przykładu liczba może zostać wyświetlona z separatorem tysięcy. 
  Po więcej informacji zajrzyj do sekcji [Formatowanie danych](output-formatting.md).
* [[yii\i18n\I18N|i18n]]: wspiera tłumaczenie i formatowanie wiadomości.
  Po więcej informacji zajrzyj do sekcji [Internacjonalizacja](tutorial-i18n.md).
* [[yii\log\Dispatcher|log]]: zarządza logowaniem informacji oraz błędów
  Po więcej informacji zajrzyj do sekcji [Logowanie](runtime-logging.md).
* [[yii\swiftmailer\Mailer|mail]]: wspiera tworzenie oraz wysyłanie emaili.
  Po więcej informacji zajrzyj do sekcji [Wysyłanie poczty](tutorial-mailing.md).
* [[yii\base\Application::response|response]]: reprezentuje odpowiedź wysyłaną do użytkowników.
  Po więcej informacji zajrzyj do sekcji [Odpowiedzi](runtime-responses.md).
* [[yii\base\Application::request|request]]: reprezentuje żądanie otrzymane od użytkownika.
  Po więcej informacji zajrzyj do sekcji [Żądania](runtime-requests.md).
* [[yii\web\Session|session]]: reprezentuje informacje przetrzymywane w sesji. Ten komponent jest dostępny 
  tylko w [[yii\web\Application|aplikacjach WEB]].
  Po więcej informacji zajrzyj do sekcji [Sesje i ciasteczka](runtime-sessions-cookies.md).
* [[yii\web\UrlManager|urlManager]]: wspiera przetwarzania oraz tworzenie adresów URL.
  Po więcej informacji zajrzyj do sekcji [Przetwarzanie i tworzenie adresów URL](runtime-routing.md)
* [[yii\web\User|user]]: reprezentuje informacje dotyczące uwierzytelniania użytkownika. Ten komponent jest dostępny 
  tylko w [[yii\web\Application|aplikacjach WEB]].
  Po więcej informacji zajrzyj do sekcji [Uwierzytelnianie](security-authentication.md).
* [[yii\web\View|view]]: wspiera renderowanie widoków.
  Po więcej informacji zajrzyj do sekcji [Widoki](structure-views.md).
