Behaviory
=========

Behaviory są instancjami klasy [[yii\base\Behavior]] lub jej pochodnych. Behaviory, zwane także 
[domieszkami](https://pl.wikipedia.org/wiki/Domieszka_(programowanie_obiektowe)), pozwalają na wzbogacenie funkcjonalności 
już istniejącej klasy [[yii\base\Component|komponentu]] bez konieczności modyfikacji jej struktury dziedziczenia.  
Dołączenie behavioru "wstrzykuje" jego metody i właściwości do komponentu, dzięki czemu są one dostępne w taki sam sposób, 
jakby były zdefiniowane od razu w klasie komponentu. Ponadto behavior może reagować na [eventy](concept-events.md) wywołane 
przez komponent, co pozwala na modyfikowanie sposobu, w jaki kod komponentu jest wykonywany.


Definiowane behaviorów <span id="defining-behaviors"></span>
----------------------

Aby zdefiniować behavior, stwórz klasę, która rozszerza [[yii\base\Behavior]] lub jej klasę potomną. Przykładowo:

```php
namespace app\components;

use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public $prop1;

    private $_prop2;

    public function getProp2()
    {
        return $this->_prop2;
    }

    public function setProp2($value)
    {
        $this->_prop2 = $value;
    }

    public function foo()
    {
        // ...
    }
}
```

Powyższy kod definiuje klasę behavioru `app\components\MyBehavior` z dwoma właściwościami `prop1` i `prop2` oraz jedną metodą 
`foo()`. Zwróć uwagę na to, że właściwość `prop2` jest zdefiniowana poprzez getter `getProp2()` i setter `setProp2()`. 
Jest to możliwe dzięki temu, że [[yii\base\Behavior]] rozszerza [[yii\base\BaseObject]], przez co ma możliwość definiowania 
[właściwości](concept-properties.md) za pomocą getterów i setterów.

Komponent, po załączeniu tego behavioru, będzie również posiadał właściwości `prop1` i `prop2` oraz metodę `foo()`.

> Tip: Wewnątrz behavioru możesz odwołać się do komponentu, do którego jest on załączony, przez właściwość 
  [[yii\base\Behavior::owner]].

> Note: Jeśli nadpisujesz metody [[yii\base\Behavior::__get()]] i/lub [[yii\base\Behavior::__set()]] behavioru, musisz również 
  nadpisać [[yii\base\Behavior::canGetProperty()]] i/lub [[yii\base\Behavior::canSetProperty()]].

Obsługa eventów komponentu
--------------------------

Jeśli behavior powinien reagować na eventy wywołane przez komponent, do którego jest załączony, należy nadpisać jego metodę 
[[yii\base\Behavior::events()]]. Dla przykładu:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```

Metoda [[yii\base\Behavior::events()|events()]] powinna zwrócić listę eventów i odpowiadających im uchwytów.
Powyższy przykład deklaruje, że event [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] istnieje i jego 
uchwytem jest metoda `beforeValidate()`. Do określenia uchwytów eventów możesz użyć następujących formatów:

* łańcuch znaków odnoszący się do nazwy metody w klasie behavioru, jak w przykładzie powyżej,
* tablica obiektu lub nazwy klasy i nazwy metody w postaci łańcucha znaków (bez nawiasów), np. `[$obiekt, 'nazwaMetody']`,
* funkcja anonimowa.

Sygnatura funkcji uchwytu eventu powinna wyglądać jak poniżej, gdzie `$event` odwołuje się do obsługiwanego eventu. 
W sekcji [Eventy](concept-events.md) znajdziesz więcej szczegółów dotyczących samych eventów.

```php
function ($event) {
}
```

Załączanie behaviorów <span id="attaching-behaviors"></span>
---------------------

Możesz załączyć behavior do [[yii\base\Component|komponentu]] zarówno statycznie, jak i dynamicznie. Pierwszy sposób jest 
częściej wykorzystywany w praktyce.

Aby załączyć behavior statycznie, nadpisz metodę [[yii\base\Component::behaviors()|behaviors()]] w klasie komponentu, do której 
behavior ma być załączony. Metoda [[yii\base\Component::behaviors()|behaviors()]] powinna zwracać listę 
[konfiguracji](concept-configurations.md) behaviorów.  
Każda konfiguracja behavioru może być zarówno nazwą klasy behavioru jak i tablicą konfiguracyjną:

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // anonimowy behavior, tylko nazwa klasy behavioru
            MyBehavior::class,

            // imienny behavior, tylko nazwa klasy behavioru
            'myBehavior2' => MyBehavior::class,

            // anonimowy behavior, tablica konfiguracyjna
            [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // imienny behavior, tablica konfiguracyjna
            'myBehavior4' => [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],
        ];
    }
}
```

Możesz przypisać konkretną nazwę dla behavioru, definiując klucz tablicy odpowiadający jego konfiguracji - w tym przypadku 
mówimy o *imiennym behaviorze*. W powyższym przykładzie znajdują się dwa imienne behaviory: `myBehavior2` i `myBehavior4`. 
Jeśli behavior nie ma przypisanej nazwy, nazywamy go *anonimowym*.


Aby załączyć behavior dynamicznie, wywołaj metodę [[yii\base\Component::attachBehavior()]] na komponencie, do którego behavior 
ma być załączony:

```php
use app\components\MyBehavior;

// załącz obiekt behavioru
$component->attachBehavior('myBehavior1', new MyBehavior);

// załącz klasę behavioru
$component->attachBehavior('myBehavior2', MyBehavior::class);

// załącz tablicę konfiguracyjną
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::class,
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```

Możesz załączyć wiele behaviorów jednocześnie, korzystając z metody [[yii\base\Component::attachBehaviors()]]:

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior,  // imienny behavior
    MyBehavior::class,          // anonimowy behavior
]);
```

Możliwe jest również załączenie behaviorów poprzez [konfigurację](concept-configurations.md), jak widać to poniżej: 

```php
[
    'as myBehavior2' => MyBehavior::class, // zwróć uwagę na konstrukcję "as nazwaBehavioru"

    'as myBehavior3' => [
        'class' => MyBehavior::class,
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```

Więcej szczegółów znajdziesz w sekcji [Konfiguracje](concept-configurations.md#configuration-format).


Korzystanie z behaviorów <span id="using-behaviors"></span>
------------------------

Aby użyć behavioru, najpierw załącz go do [[yii\base\Component|komponentu]] zgodnie z powyższymi instrukcjami. Kiedy behavior 
jest już załączony, korzystanie z niego jest bardzo proste.

Możesz uzyskać dostęp do *publicznej* zmiennej lub [właściwości](concept-properties.md) zdefiniowanej przez getter i/lub setter 
behavioru z poziomu komponentu, do którego jest on załączony:

```php
// "prop1" jest właściwością zdefiniowaną w klasie behavioru
echo $component->prop1;
$component->prop1 = $value;
```

Możesz również wywołać *publiczną* metodę behavioru w podobny sposób:

```php
// foo() jest publiczną metodą zdefiniowaną w klasie behavioru
$component->foo();
```

Jak widać, pomimo że `$component` nie definiuje `prop1` ani `foo()`, można ich użyć tak, jakby były zdefiniowane przez 
komponent, dzięki załączonemu behaviorowi.

Jeśli dwa behaviory definiują tą samą właściwość lub metodę i oba są załączone do tego samego komponentu, behavior, który 
został załączony jako *pierwszy*, będzie obsługiwał wywołaną właściwość lub metodę.

Behavior może być powiązany z konkretną nazwą podczas załączania do komponentu - w takim przypadku można odwołać się do 
obiektu behavioru, korzystając z jego nazwy:

```php
$behavior = $component->getBehavior('myBehavior');
```

Można również uzyskać listę wszystkich behaviorów załączonych do komponentu:

```php
$behaviors = $component->getBehaviors();
```


Odłączanie behaviorów <span id="detaching-behaviors"></span>
---------------------

Aby odłączyć behavior, wywołaj metodę [[yii\base\Component::detachBehavior()]] z nazwą przypisaną temu behaviorowi:

```php
$component->detachBehavior('myBehavior1');
```

Można również odłączyć *wszystkie* behaviory jednocześnie:

```php
$component->detachBehaviors();
```


Korzystanie z `TimestampBehavior` <span id="using-timestamp-behavior"></span>
---------------------------------

Behavior [[yii\behaviors\TimestampBehavior]] pozwala na automatyczne aktualizowanie atrybutów znaczników czasu dla modelu 
[[yii\db\ActiveRecord|Active Record]] za każdym razem, gdy model jest zapisywany za pomocą metod `insert()`, `update()` lub 
`save()`.

Załącz ten behavior do klasy [[yii\db\ActiveRecord|Active Record]], której chcesz użyć:

```php
namespace app\models\User;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                // opcjonalnie jeśli używasz kolumny typu datetime zamiast uniksowego znacznika czasu:
                // 'value' => new Expression('NOW()'),
            ],
        ];
    }
}
```

Powyższa konfiguracja behavioru określa, co powinno stać się z wierszem danych podczas:

* dodawania; behavior powinien ustawić aktualny uniksowy znacznik czasu dla atrybutów `created_at` i `updated_at`.
* aktualizacji; behavior powinien ustawić aktualny uniksowy znacznik czasu dla atrybutu `updated_at`.

> Note: Aby powyższa implementacja zadziałała dla bazy danych MySQL, zadeklaruj kolumny (`created_at`, `updated_at`) jako 
  int(11) na potrzeby przechowania uniksowego znacznika czasu.

Z tak wprowadzonym kodem, po zapisie obiektu `User` zobaczysz, że jego atrybuty `created_at` i `updated_at` zostały 
automatycznie ustawione na aktualny uniksowy znacznik czasu:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // wyświetli znacznik czasu z momentu zapisu
```

[[yii\behaviors\TimestampBehavior|TimestampBehavior]] oferuje również użyteczną metodę 
[[yii\behaviors\TimestampBehavior::touch()|touch()]], która ustawia aktualny znacznik czasu określonemu atrybutowi i zapisuje 
go w bazie danych:

```php
$user->touch('login_time');
```

Inne behaviory
--------------

Poniżej znajdziesz kilka behaviorów wbudowanych lub też dostępnych w zewnętrznych bibliotekach:

- [[yii\behaviors\BlameableBehavior]] - automatycznie wypełnia wskazane atrybuty ID aktualnego użytkownika.
- [[yii\behaviors\SluggableBehavior]] - automatycznie wypełnia wskazany atrybut wartością, która może być użyta jako poprawna 
  część adresu URL (slug).
- [[yii\behaviors\AttributeBehavior]] - automatycznie ustawia określoną wartość jednemu lub więcej atrybutom obiektu 
  ActiveRecord w momencie wystąpienia konkretnych eventów.
- [yii2tech\ar\softdelete\SoftDeleteBehavior](https://github.com/yii2tech/ar-softdelete) - udostępnia metody do 
  "miękkiego usunięcia" ActiveRecordu i przywrócenia go z powrotem np. poprzez ustawienie flagi lub statusu oznaczającego 
  rekord jako usunięty.
- [yii2tech\ar\position\PositionBehavior](https://github.com/yii2tech/ar-position) - pozwala na zarządzanie kolejnością 
  rekordów w polu typu integer.

Różnice pomiędzy behaviorami a traitami <span id="comparison-with-traits"></span>
---------------------------------------

Pomimo że behaviory są podobne do [traitów](https://www.php.net/traits) w taki sposób, że również "wstrzykują" swoje 
właściwości i metody do klasy, struktury te różnią się w wielu aspektach. Obie mają swoje wady i zalety, jak opisano to 
poniżej, i powinny być raczej traktowane jako swoje uzupełnienia, a nie alternatywy.


### Zalety używania behaviorów <span id="pros-for-behaviors"></span>

Klasy behaviorów, tak jak zwyczajne klasy, pozwalają na dziedziczenie. Traity można raczej nazwać wspieranym przez język 
programowania "kopiuj-wklej", jako że nie oferują dziedziczenia.

Behaviory można załączać i odłączać od komponentu dynamicznie bez konieczności modyfikowania klasy komponentu.  
Aby użyć traita, konieczne jest zmodyfikowanie kodu klasy, która będzie go używać.

Behaviory są konfigurowalne w przeciwieństwie do traitów.

Behaviory mogą modyfikować wykonywanie kodu komponentu, poprzez reagowanie na jego eventy.

W przypadku, gdy zdarza się konflikt nazw pomiędzy różnymi behaviorami załączonymi do tego samego komponentu, jest on 
automatycznie rozwiązywany przez przyznanie pierwszeństwa behaviorowi załączonemu jako pierwszy.  
Konflikty nazw spowodowane przez różne traity wymagają ręcznego rozwiązania poprzez zmianę nazw dotkniętych problemem 
właściwości i metod.


### Zalety używania traitów <span id="pros-for-traits"></span>

Traity są znacznie bardziej wydajne niż behaviory, ponieważ behaviory są obiektami, które wymagają czasu i pamięci.

Środowiska IDE o wiele lepiej wspierają traity, ponieważ są one natywnymi konstruktami języka programowania.
