Aliasy
======

Aliasy używane są do reprezentowania ścieżek do plików lub adresów URL i pozwalają uniknąć konieczności wielokrotnego definiowania ich 
w kodzie aplikacji. Alias musi zaczynać się od znaku `@`, dla odróżnienia od zwykłej ścieżki i adresu URL. W przypadku zdefiniowania 
aliasu bez tego znaku, będzie on automatycznie dodany na początku.

Yii korzysta z wielu predefiniowanych aliasów. Dla przykładu, alias `@yii` reprezentuje ścieżkę instalacji frameworka, a `@web` bazowy 
adres URL aktualnie uruchomionej aplikacji Web.

Definiowanie aliasów <span id="defining-aliases"></span>
--------------------

Możesz zdefiniować alias do ścieżki pliku lub adresu URL wywołując [[Yii::setAlias()]]:

```php
// alias do ścieżki pliku
Yii::setAlias('@foo', '/path/to/foo');

// alias do adresu URL
Yii::setAlias('@bar', 'http://www.example.com');
```

> Note: *nie* jest konieczne, aby aliasowana ścieżka pliku lub URL wskazywał istniejący plik lub zasób.

Mając już zdefiniowany alias, możesz rozbudować go, tworząc nowy alias (bez konieczności wywołania [[Yii::setAlias()]]), dodając 
ukośnik `/` i kolejne segmenty ścieżki. Aliasy zdefiniowane za pomocą [[Yii::setAlias()]] nazywane są *bazowymi aliasami*, a te, które 
je rozbudowują *aliasami pochodnymi*. Dla przykładu, `@foo` jest aliasem bazowym, a `@foo/bar/file.php` pochodnym.

Możesz definiować aliasy używając innych aliasów (zarówno bazowych, jak i pochodnych):

```php
Yii::setAlias('@foobar', '@foo/bar');
```

Aliasy bazowe są zwykle definiowane podczas fazy [bootstrappingu](runtime-bootstrapping.md).
Możliwe jest wywołanie [[Yii::setAlias()]] już w [skrypcie wejściowym](structure-entry-scripts.md).
[Aplikacja](structure-applications.md) dla wygody deweloperów posiada właściwość `aliases`, którą można zmodyfikować 
w [konfiguracji](concept-configurations.md):

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/path/to/foo',
        '@bar' => 'http://www.example.com',
    ],
];
```


Rozwiązywanie aliasów <span id="resolving-aliases"></span>
---------------------

Możesz wywołać [[Yii::getAlias()]], aby rozwiązać alias, czyli zamienić go na ścieżkę pliku lub adres URL, który reprezentuje. 
Dotyczy to zarówno bazowych aliasów, jak i pochodnych:

```php
echo Yii::getAlias('@foo');               // wyświetla: /path/to/foo
echo Yii::getAlias('@bar');               // wyświetla: http://www.example.com
echo Yii::getAlias('@foo/bar/file.php');  // wyświetla: /path/to/foo/bar/file.php
```

Ścieżka/URL reprezentowany przez pochodny alias jest ustalany poprzez zamianę części z bazowym aliasem na jego rozwiązaną 
reprezentację.

> Note: Metoda [[Yii::getAlias()]] nie sprawdza, czy reprezentowana ścieżka/URL wskazuje na istniejący plik lub zasób.


Alias bazowy może również zawierać ukośnik `/`. Metoda [[Yii::getAlias()]] potrafi określić, która część aliasu jest aliasem bazowym 
i prawidłowo określić odpowiadającą mu ścieżkę pliku lub URL:

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
Yii::getAlias('@foo/test/file.php');  // wyświetla: /path/to/foo/test/file.php
Yii::getAlias('@foo/bar/file.php');   // wyświetla: /path2/bar/file.php
```

Gdyby `@foo/bar` nie był zdefiniowany jako bazowy alias, ostatnia instrukcja wyświetliłaby `/path/to/foo/bar/file.php`.


Korzystanie z aliasów <span id="using-aliases"></span>
---------------------

Aliasy są rozwiązywane automatycznie w wielu miejscach w Yii bez konieczności wywołania bezpośrednio metody [[Yii::getAlias()]]. 
Przykładowo, [[yii\caching\FileCache::cachePath]] akceptuje zarówno ścieżkę pliku, jak i alias ją reprezentujący, odróżniając je 
od siebie dzięki prefiksowi `@`.

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

Aby sprawdzić, czy dana właściwość lub parametr metody wspierają użycie aliasów, zapoznaj się z dokumentacją API.


Predefiniowane aliasy <span id="predefined-aliases"></span>
---------------------

Yii predefiniuje zestaw aliasów do łatwego wskazywania często używanych ścieżek plików i adresów URL:

- `@yii`, folder, w którym znajduje się plik `BaseYii.php` (nazywany także folderem frameworka).
- `@app`, [[yii\base\Application::basePath|bazowa ścieżka]] aktualnie używanej aplikacji.
- `@runtime`, [[yii\base\Application::runtimePath|ścieżka cyklu życia]] aktualnie używanej aplikacji. Domyślnie wskazuje na 
  `@app/runtime`.
- `@webroot`, folder bazowy Web aktualnie używanej aplikacji Web. Określany jest jako lokalizacja folderu zawierającego 
  [skrypt wejścia](structure-entry-scripts.md).
- `@web`, bazowy adres URL aktualnie używanej aplikacji Web. Wskazuje na tą samą wartość co [[yii\web\Request::baseUrl]].
- `@vendor`, [[yii\base\Application::vendorPath|folder pakietów composera]]. Domyślnie wskazuje na `@app/vendor`.
- `@bower`, bazowy folder zawierający [pakiety bowera](http://bower.io/). Domyślnie wskazuje na `@vendor/bower`.
- `@npm`, bazowy folder zawierający [pakiety npm](https://www.npmjs.org/). Domyślnie wskazuje na `@vendor/npm`.

Alias `@yii` jest definiowany poprzez dołączenie pliku `Yii.php` w [skrypcie wejścia](structure-entry-scripts.md).
Pozostałe aliasy są definiowane w konstruktorze aplikacji podczas ładowania [konfiguracji](concept-configurations.md).


Aliasy rozszerzeń <span id="extension-aliases"></span>
-----------------

Instalacja [rozszerzenia](structure-extensions.md) za pomocą composera automatycznie definiuje dla niego alias.
Każdy z takich aliasów jest nazwany zgodnie z bazową przestrzenią nazw rozszerzenia określonej w swoim pliku `composer.json` 
i reprezentuje bazowy folder pakietu. Dla przykładu, instalując rozszerzenie `yiisoft/yii2-jui` automatycznie uzyskasz alias 
`@yii/jui` zdefiniowany podczas fazy [bootstrappingu](runtime-bootstrapping.md), będący odpowiednikiem wywołania:

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```
