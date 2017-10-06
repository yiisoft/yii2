Styl kodowania bazowych plików frameworka Yii 2
===============================================

Poniższy styl kodowania jest stosowany w kodzie frameworka Yii 2.x i oficjalnych rozszerzeniach. Jeśli planujesz wysłać prośbę
o dołączenie kodu do bazowego frameworka, powinieneś rozważyć stosowanie takiego samego stylu. Nie zmuszamy jednak nikogo do
stosowania go we własnych aplikacjach. Wybierz styl, który najbardziej odpowiada Twoim potrzebom.

Możesz pobrać gotową konfigurację dla CodeSniffera pod adresem: https://github.com/yiisoft/yii2-coding-standards

## 1. Omówienie

Używamy przede wszystkim standardu kodowania
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md), zatem wszystko, co dotyczy
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) dotyczy również naszego stylu
kodowania.

- Pliki MUSZĄ używać tagów `<?php` albo `<?=`.
- Na końcu pliku powinna znajdować się pusta linia.
- Pliki MUSZĄ używać kodowania UTF-8 bez znacznika BOM dla kodu PHP.
- Kod MUSI używać 4 spacji do wcięć, a nie tabulatorów.
- Nazwy klas MUSZĄ być zadeklarowane w formacie `StudlyCaps`.
- Stałe w klasach MUSZĄ być zadeklarowane wyłącznie wielimi literami z łącznikiem w postaci podkreślnika.
- Nazwy metod klasy MUSZĄ być zadeklarowane w formacie `camelCase`.
- Nazwy właściwości klasy MUSZĄ być zadeklarowane w formacie `camelCase`.
- Nazwy właściwości klasy MUSZĄ zaczynać się podkreślnikiem, jeśli są prywatne.
- Należy używać `elseif` zamiast `else if`.

## 2. Pliki

### 2.1. Tagi PHP

- Kod PHP MUSI używać tagów `<?php ?>` lub `<?=`; NIE MOŻE używać innych tagów takich jak `<?`.
- W przypadku, gdy plik zawiera tylko kod PHP, nie powinien kończyć się tagiem `?>`.
- Nie należy dodawać spacji na końcach linii.
- Nazwa każdego pliku zawierającego kod PHP powinna kończyć się rozszerzeniem `.php`.

### 2.2. Kodowanie znaków

Kod PHP MUSI używać wyłącznie UTF-8 bez znacznika BOM.

## 3. Nazwy klas

Nazwy klas MUSZĄ być zadeklarowane w formacie `StudlyCaps`. Przykładowo `Controller`, `Model`.

## 4. Klasy

Termin "klasa" odnosi się tutaj do wszystkich klas i interfejsów.

- Klasy powinny być nazwane w formacie `CamelCase`.
- Otwierający nawias klamrowy powinien zawsze pojawić się w linii pod nazwą klasy.
- Każda klasa musi posiadać blok dokumentacji dostosowany do składni PHPDoc.
- Kod klasy musi być wcięty za pomocą 4 spacji.
- W pojedynczym pliku PHP powinna znajdować się tylko jedna klasa.
- Wszystkie klasy powinny być zadeklarowane w przestrzeni nazw.
- Nazwa klasy powinna odpowiadać nazwie pliku. Przestrzeń nazw klasy powinna odpowiadać strukturze folderów.

```php
/**
 * Dokumentacja
 */
class MyClass extends \yii\base\BaseObject implements MyInterface
{
    // kod
}
```

### 4.1. Stałe

Stałe klasy MUSZĄ być zadeklarowane wyłącznie wielkimi literami z łącznikiem w postaci podkreślnika.
Dla przykładu:

```php
<?php
class Foo
{
    const VERSION = '1.0';
    const DATE_APPROVED = '2012-06-01';
}
```
### 4.2. Właściwości

- Deklarując publiczne elementy klasy należy używać wprost słowa kluczowego `public`.
- Publiczne i chronione zmienne powinny być zadeklarowane na początku klasy, przed deklaracjami metod.
  Prywatne zmienne również powinny być zadeklarowane na początku klasy, ale mogą być również dodane zaraz przed metodami,
  które ich używają w przypadku, gdy są stosowane tylko w kilku z nich.
- Kolejność deklaracji właściwości w klasie powinna być rosnąca według ich widoczności: od publicznych, przez chronione, do prywatnych.
- Nie ma ścisłych zasad dotyczących kolejności właściwości o tej samej widoczności.
- Dla zapewnienia lepszej czytelności kodu, nie powinno być żadnych pustych linii pomiędzy deklaracjami właściwości, a sekcje
  deklaracji właściwości i metod klasy powinny być rozdzielona dwoma pustymi liniami. Pojedyncza pusta linia powinna być dodana
  pomiędzy grupami o różnej widoczności.
- Prywatne zmienne powinny być nazwane w formacie `$_varName`.
- Publiczne elementy klasy i niezależne zmienne powinny być nazwane w formacie `$camelCase` z pierwszą literą małą.
- Należy używać opisowych nazw. Należy unikać używania zmiennych takich jak `$i` i `$j`.

Przykładowo:

```php
<?php
class Foo
{
    public $publicProp1;
    public $publicProp2;

    protected $protectedProp;

    private $_privateProp;


    public function someMethod()
    {
        // ...
    }
}
```

### 4.3. Metody

- Funkcje i metody klasy powinny być nazwane w formacie `camelCase` z pierwszą literą małą.
- Nazwy powinny być opisowe i wskazywać cel istnienia funkcji.
- Metody klasy powinny zawsze deklarować widoczność używając modyfikatorów `private`, `protected` i `public`. `var` nie jest dozwolony.
- Otwierający nawias klamrowy funkcji powinien znajdować się w linii pod jej deklaracją.

```php
/**
 * Dokumentacja
 */
class Foo
{
    /**
     * Dokumentacja
     */
    public function bar()
    {
        // code
        return $value;
    }
}
```

### 4.4 Bloki dokumentacji

`@param`, `@var`, `@property` oraz `@return` muszą używać typów zadeklarowanych jako `bool`, `int`, `string`, `array` lub `null`.
Można również używać nazw klas jak `Model` lub `ActiveRecord`. Dla typowanych tablic należy używać `ClassName[]`.

### 4.5 Konstruktory

- Należy używać `__construct` zamiast konstruktorów w stylu PHP 4.

## 5 PHP

### 5.1 Typowanie

- Wszystkie typy i wartości PHP powinny być zapisywane małymi literami, łącznie z `true`, `false`, `null` i `array`.

Zmiana typu istniejącej zmiennej jest uznawana za złą praktykę. Należy unikać pisania kodu w ten sposób, chyba że jest to naprawdę konieczne.

```php
public function save(Transaction $transaction, $argument2 = 100)
{
    $transaction = new Connection; // źle
    $argument2 = 200; // dobrze
}
```

### 5.2 Łańcuchy znaków

- Jeśli łańcuch znaków nie zawiera zmiennych lub pojedynczych cudzysłowów, należy używać pojedynczych cudzysłowów.

```php
$str = 'Like this.';
```

- Jeśli łańcuch znaków zawiera pojedyncze cudzysłowy, można użyć podwójnych cudzysłowów, aby uniknąć dodatkowego pomijania (znaku ucieczki).

#### Zastępowanie zmiennych

```php
$str1 = "Hello $username!";
$str2 = "Hello {$username}!";
```

Poniższy zapis jest niedozwolony:

```php
$str3 = "Hello ${username}!";
```

#### Konkatenacja

Należy dodać spacje przed i po kropce podczas łączenia łańcuchów:

```php
$name = 'Yii' . ' Framework';
```

W przypadku długich łańcuchów format jest następujący:

```php
$sql = "SELECT *"
    . "FROM `post` "
    . "WHERE `id` = 121 ";
```

### 5.3 Tablice

W przypadku tablic używamy krótkiej składni PHP 5.4.

#### Indeksowane numerycznie

- Nie należy używać ujemnych liczb jako indeksów tablicy.

Należy używać następującego formatu podczas deklarowania tablicy:

```php
$arr = [3, 14, 15, 'Yii', 'Framework'];
```

W przypadku, gdy ilość elementów jest zbyt duża dla pojedynczej linii:

```php
$arr = [
    3, 14, 15,
    92, 6, $test,
    'Yii', 'Framework',
];
```

#### Asocjacyjne

Należy używać następującego formatu podczas deklarowania tablicy asocjacyjnej:

```php
$config = [
    'name' => 'Yii',
    'options' => ['usePHP' => true],
];
```

### 5.4 Instrukcje kontrolne

- Warunkowe instrukcje kontrolne muszą mieć pojedynczą spację przed i po nawiasie.
- Operatory wewnątrz nawiasów powinny być oddzielone spacjami.
- Otwierający nawias klamrowy powinien znajdować się w tej samej linii.
- Zamykający nawias klamrowy powinien znajdować się w nowej linii.
- Należy zawsze używać nawiasów klamrowych, nawet dla pojedynczych instrukcji.

```php
if ($event === null) {
    return new Event();
}
if ($event instanceof CoolEvent) {
    return $event->instance();
}
return null;


// poniższy zapis jest NIEDOZWOLONY:
if (!$model && null === $event)
    throw new Exception('test');
```

Należy unikać stosowania `else` po `return`, kiedy ma to sens.
Należy używać [guard conditions](http://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html).

```php
$result = $this->getResult();
if (empty($result)) {
    return true;
} else {
    // przetwarzanie wyniku
}
```

wygląda lepiej w postaci

```php
$result = $this->getResult();
if (empty($result)) {
   return true;
}

// przetwarzanie wyniku
```

#### Instrukcja switch

Należy używać następującego formatu dla instrukcji switch:

```php
switch ($this->phpType) {
    case 'string':
        $a = (string) $value;
        break;
    case 'integer':
    case 'int':
        $a = (int) $value;
        break;
    case 'boolean':
        $a = (bool) $value;
        break;
    default:
        $a = null;
}
```

### 5.5 Wywołania funkcji

```php
doIt(2, 3);

doIt(['a' => 'b']);

doIt('a', [
    'a' => 'b',
    'c' => 'd',
]);
```

### 5.6 Deklaracje funkcji anonimowych (lambdy)

Należy zwrócić uwagę na spację pomiędzy słowem `function`/`use` a otwierającym nawiasem:

```php
// dobrze
$n = 100;
$sum = array_reduce($numbers, function ($r, $x) use ($n) {
    $this->doMagic();
    $r += $x * $n;
    return $r;
});

// źle
$n = 100;
$mul = array_reduce($numbers, function($r, $x) use($n) {
    $this->doMagic();
    $r *= $x * $n;
    return $r;
});
```

Dokumentacja
------------

- Należy stosować dokumentację zgodnie ze składnią [phpDoc](http://phpdoc.org/).
- Kod bez dokumentacji jest niedozwolony.
- Każdy plik klasy musi zawierać blok dokumentacji "poziomu pliku" na początku pliku i blok dokumentacji "poziomu klasy"
  zaraz nad klasą.
- Nie ma konieczności używania `@return`, jeśli metoda niczego nie zwraca.
- Wszystkie wirtualne właściwości w klasach, które rozszerzają `yii\base\BaseObject` są udokumentowane za pomocą tagu `@property`
  w bloku dokumentacji klasy.
  Adnotacje te są automatycznie generowane z tagów `@return` lub `@param` w odpowiednich getterach lub setterach przez
  uruchomienie `./build php-doc` w folderze build.
  Można dodać tag `@property` do gettera lub settera, aby wprost określić informację dla dokumentacji właściwości zadeklarowanej
  w tych metodach, kiedy opis różni się od tego, co znajduje się w `@return`. Poniżej znajduje się przykład:

  ```php
    <?php
    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use `null` to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     * ...
     */
    public function getErrors($attribute = null)
  ```

#### Poziom pliku

```php
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
```

#### Poziom klasy

```php
/**
 * Component is the base class that provides the *property*, *event* and *behavior* features.
 *
 * @include @yii/docs/base-Component.md
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends \yii\base\BaseObject
```


#### Poziom funkcji / metody

```php
/**
 * Returns the list of attached event handlers for an event.
 * You may manipulate the returned [[Vector]] object by adding or removing handlers.
 * For example,
 *
 * ```
 * $component->getEventHandlers($eventName)->insertAt(0, $eventHandler);
 * ```
 *
 * @param string $name the event name
 * @return Vector list of attached event handlers for the event
 * @throws Exception if the event is not defined
 */
public function getEventHandlers($name)
{
    if (!isset($this->_e[$name])) {
        $this->_e[$name] = new Vector;
    }
    $this->ensureBehaviors();
    return $this->_e[$name];
}
```

#### Markdown

Jak widać w powyższych przykładach, używamy składni markdown do formatowania komentarzy phpDoc.

W dokumentacji stosowana jest dodatkowa składnia do linkowania klas, metod i właściwości:

- `[[canSetProperty]]` utworzy link do metody lub właściwości `canSetProperty` w tej samej klasie.
- `[[Component::canSetProperty]]` utworzy link do metody `canSetProperty` w klasie `Component` w tej samej przestrzeni nazw.
- `[[yii\base\Component::canSetProperty]]` utworzy link do metody `canSetProperty` w klasie `Component` w przestrzeni nazw `yii\base`.
- `[[Component]]` utworzy link do klasy `Component` w tej samej przestrzeni nazw. Można tutaj również dodać przestrzeń nazw.

Aby nadać powyższym linkom inną etykietę niż nazwa klasy lub metody, można użyć składni pokazanej w poniższym przykładzie:

```
... as displayed in the [[header|header cell]].
```

Część przed | jest linkowaną metodą, właściwością lub klasą, a część po | jest etykietą linku.

Możliwe jest też linkowanie do Przewodnika używając następującej składni:

```markdown
[link to guide](guide:file-name.md)
[link to guide](guide:file-name.md#subsection)
```


#### Komentarze

- Komentarze jednolinijkowe powinny zaczynać się od `//` a nie od `#`.
- Komentarze jednolinijkowe powinny znajdować się w osobnej linii.

Dodatkowe zasady
----------------

### `=== []` vs `empty()`

Należy używać `empty()`, kiedy jest to możliwe.

### Wielokrotne punkty powrotu

Należy powracać (return) wcześnie, kiedy tylko instrukcje warunkowe zaczynają się zagnieżdżać. Nie ma to znaczenia w przypadku krótkich metod.

### `self` vs. `static`

Należy zawsze używać `static` z wyjątkiem poniższych przypadków:

- odwołania do stałych MUSZĄ odbywać się za pomocą `self`: `self::MY_CONSTANT`
- odwołania do prywatnych statycznych właściwości MUSZĄ odbywać się za pomocą `self`: `self::$_events`
- można używać `self` do wywołania metod, kiedy ma to sens, jak w przypadku wywołań rekurencyjnych aktualnej implementacji zamiast rozszerzania implementacji klas.

### Wartość dla "nie rób czegoś"

Właściwości pozwalające na skonfigurowanie komponentu, tak, aby nie robił czegoś, powinny przyjmować wartość `false`. `null`, `''` lub `[]` nie powinny być traktowane w ten sposób.

### Nazwy folderów/przestrzeni nazw

- należy używać małych liter
- należy używać liczby mnogiej rzeczowników, które reprezentują obiekty (np. validators)
- należy używać liczby pojedynczej dla nazw reprezentujących funkcjonalności (np. web)
- preferowane są przestrzenie nazw będące pojedynczym słowem
- w przypadku, gdy pojedyncze słowo nie jest wystarczające, należy użyć formatu camelCase
