Autoładowanie klas
==================

Yii opiera się na [mechanizmie automatycznego ładowania klas](https://www.php.net/manual/pl/language.oop5.autoload.php) służącym do 
zlokalizowania i dołączenia wszystkich wymaganych plików klas. Wbudowany wysoce wydajny autoloader klas, zgodny ze 
[standardem PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), jest instalowany po załączeniu 
pliku `Yii.php`.

> Note: Dla uproszczenia opisów, w tej sekcji zostanie omówione jedynie autoładowanie klas. Należy mieć jednak na uwadze, że poniższe 
  informacje odnoszą się również do autoładowania interfejsów i traitów.


Korzystanie z autoloadera Yii <span id="using-yii-autoloader"></span>
-----------------------------

Aby skorzystać z autoloadera klas Yii, powinieneś przestrzegać dwóch prostych zasad tworzenia i nazywania własnych klas:

* Każda klasa musi znajdować się w [przestrzeni nazw](https://www.php.net/manual/pl/language.namespaces.php) (np. `foo\bar\MyClass`)
* Każda klasa musi być zapisana jako oddzielny plik, do którego ścieżka określona jest poniższym algorytmem:

```php
// $className jest w pełni uściśloną nazwą klasy bez początkowego odwrotnego ukośnika
$classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php');
```

Przykładowo, jeśli nazwa klasy i przestrzeń nazw to `foo\bar\MyClass`, odpowiadającym ścieżce pliku klasy [aliasem](concept-aliases.md) 
jest `@foo/bar/MyClass.php`. Aby ten alias mógł być przetłumaczony na ścieżkę pliku, `@foo` lub `@foo/bar` musi być 
[aliasem bazowym](concept-aliases.md#defining-aliases).

Używając [podstawowego szablonu projektu](start-installation.md), możesz umieścić swoje klasy w bazowej przestrzeni nazw `app`, dzięki 
czemu mogą być autoładowane przez Yii bez potrzeby definiowania nowego aliasu. Dzieje się tak dzięki temu, że `@app` jest 
[predefiniowanym aliasem](concept-aliases.md#predefined-aliases) i klasa `app\components\MyClass` może być odszukana w pliku 
`AppBasePath/components/MyClass.php`, zgodnie z opisanym algorytmem.

W [zaawansowanym szablonie projektu](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md) każdy poziom 
aplikacji posiada swój własny bazowy alias. Dla przykładu, front-end określony jest przez bazowy alias `@frontend`, a back-end - 
`@backend`. Dzięki temu możesz umieścić klasy front-endu w przestrzeni nazw `frontend`, a klasy back-endu w przestrzeni nazw `backend`. 
Wszystkie te klasy będą automatycznie załadowane przez autoloader Yii.


Mapa klas <span id="class-map"></span>
---------

Autoloader klas Yii wspiera mechanizm *mapy klas*, która mapuje nazwy klas do odpowiadających im ścieżek plików. 
Kiedy autoloader ładuje klasę, najpierw sprawdza czy klasa znajduje się w mapie. Jeśli tak, odpowiadająca nazwie ścieżka pliku zostanie 
dołączona od razu, bez dalszej weryfikacji, co jest powodem, dla którego autoładowanie klas jest błyskawiczne. Wszystkie podstawowe 
klasy Yii są autoładowane właśnie w ten sposób.

Możesz dodać klasę do mapy klas, przechowywanej w `Yii::$classMap`, za pomocą instrukcji:

```php
Yii::$classMap['foo\bar\MyClass'] = 'path/to/MyClass.php';
```

Do określenia ścieżek plików klas można użyć [aliasów](concept-aliases.md). Zapisywanie mapy klas powinno odbywać się w procesie 
[bootstrappingu](runtime-bootstrapping.md), aby mapa była gotowa zanim rozpocznie się korzystanie z klas.


Korzystanie z innych autoloaderów <span id="using-other-autoloaders"></span>
---------------------------------

Ponieważ Yii opiera się głównie na composerze, jako menedżerze pakietów zależności, zalecane jest również zainstalowanie autoloadera 
composera. Jeśli używasz zewnętrznych bibliotek, korzystających z własnych autoloaderów, powinieneś również je zainstalować.

Używając autoloadera Yii razem z innymi autoloaderami, powinieneś dołączyć plik `Yii.php` *po* wszystkich pozostałych autoloaderach. Dzięki temu 
autoloader Yii jako pierwszy odpowie na żądanie autoładowania klasy. Dla przykładu, poniższy kod znajduje się 
w [skrypcie wejściowym](structure-entry-scripts.md) [podstawowego szablonu projektu](start-installation.md). Pierwsza linia jest 
instrukcją instalacji autoloadera composera, a druga instaluje autoloader Yii:

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```

Możesz używać jedynie autoloadera composera bez autoloadera Yii, ale wydajność autoładowania klas może być wtedy obniżona i, dodatkowo, 
musisz przestrzegać zasad ustalonych przez composera, aby Twoje klasy mogły być autoładowane.

> Info: Jeśli nie chcesz korzystać z autoloadera Yii, musisz stworzyć swoją własną wersję pliku `Yii.php` i dołączyć ją 
  w [skrypcie wejściowym](structure-entry-scripts.md).


Autoładowanie klas rozszerzeń <span id="autoloading-extension-classes"></span>
-----------------------------

Autoloader Yii potrafi również automatycznie ładować klasy [rozszerzeń](structure-extensions.md). Jedynym wymaganiem ze strony 
rozszerzenia jest prawidłowy zapis sekcji `autoload` w swoim pliku `composer.json`. Szczegóły na temat specyfikacji `autoload` znajdują 
się [dokumentacji composera](https://getcomposer.org/doc/04-schema.md#autoload).

Jeśli nie korzystasz z autoloadera Yii, autoloader composera załaduje dla Ciebie automatycznie klasy rozszerzeń.
