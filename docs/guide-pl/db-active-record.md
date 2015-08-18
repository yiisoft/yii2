Active Record
=============

[Active Record](http://en.wikipedia.org/wiki/Active_record_pattern) zapewnia zorientowany obiektowo interfejs dostępu i manipulacji danymi 
zapisanymi w bazie danych. Klasa typu Active Record jest powiązana z tabelą bazodanową, a instacja tej klasy odpowiada pojedynczemu wierszowi 
w tabeli - *atrybut* obiektu Active Record reprezentuje wartość konkretnej kolumny w tym wierszu. Zamiast pisać bezpośrednie kwerendy bazy danych, 
można skorzystać z atrybutów i metod klasy Active Record.

Dla przykładu, załóżmy, że `Customer` jest klasą Active Record, powiązaną z tabelą `customer` i `name` jest kolumną w tabeli `customer`. 
Aby dodać nowy wiersz do tabeli `customer` wystarczy wykonać następujący kod:

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

Kod z przykładu jest odpowiednikiem poniższej komendy SQL dla MySQL, która jest mniej intuicyjna, bardziej podatna na błędy i, co bardzo 
prawdopodobne, niekompatybilna z innymi rodzajami baz danych:

```php
$db->createCommand('INSERT INTO `customer` (`name`) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

Yii zapewnia wsparcie Active Record dla następujących typów relacyjnych baz danych:

* MySQL 4.1 lub nowszy: poprzez [[yii\db\ActiveRecord]]
* PostgreSQL 7.3 lub nowszy: poprzez [[yii\db\ActiveRecord]]
* SQLite 2 i 3: poprzez [[yii\db\ActiveRecord]]
* Microsoft SQL Server 2008 lub nowszy: poprzez [[yii\db\ActiveRecord]]
* Oracle: poprzez [[yii\db\ActiveRecord]]
* CUBRID 9.3 lub nowszy: poprzez [[yii\db\ActiveRecord]] (Zwróć uwagę, że z powodu [błędu](http://jira.cubrid.org/browse/APIS-658) 
  w rozszerzeniu PDO cubrid, umieszczanie wartości w cudzysłowie nie będzie działać, zatem wymagane jest zainstalowanie CUBRID 9.3 zarówno 
  jako klienta jak i serwer)
* Sphinx: poprzez [[yii\sphinx\ActiveRecord]], wymaga rozszerzenia `yii2-sphinx`
* ElasticSearch: poprzez [[yii\elasticsearch\ActiveRecord]], wymaga rozszerzenia `yii2-elasticsearch`

Dodatkowo Yii wspiera również Active Record dla następujących baz danych typu NoSQL:

* Redis 2.6.12 lub nowszy: poprzez [[yii\redis\ActiveRecord]], wymaga rozszerzenia `yii2-redis`
* MongoDB 1.3.0 lub nowszy: poprzez [[yii\mongodb\ActiveRecord]], wymaga rozszerzenia `yii2-mongodb`

W tej sekcji przewodnika opiszemy sposób użycia Active Record dla baz relacyjnych, jednakże większość zagadnień można zastosować również dla NoSQL.


## Deklarowanie klas Active Record <span id="declaring-ar-classes"></span>

Na początek zadeklaruj klasę typu Active Record rozszerzając [[yii\db\ActiveRecord]]. Ponieważ każda klasa Active Record 
jest powiązana z tabelą bazy danych, należy nadpisać metodę [[yii\db\ActiveRecord::tableName()|tableName()]], aby wskazać 
odpowiednią tabelę.

W poniższym przykładzie deklarujemy klasę Active Record nazwaną `Customer` dla tabeli `customer` w bazie danych.

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @return string nazwa tabeli powiązanej z klasą ActiveRecord.
     */
    public static function tableName()
    {
        return 'customer';
    }
}
```

Instancje Active Record są traktowane jak [modele](structure-models.md). Z tego powodu zwykle dodajemy klasy Active Record 
do przestrzeni nazw `app\models` (lub innej, przeznaczonej dla klas modeli). 

Dzięki temu, że [[yii\db\ActiveRecord]] rozszerza [[yii\base\Model]], dziedziczy *wszystkie* funkcjonalności [modelu](structure-models.md), 
takie jak atrybuty, zasady walidacji, serializację danych itd.


## Łączenie się z bazą danych <span id="db-connection"></span>

Domyślnie Active Record używa [komponentu aplikacji](structure-application-components.md) `db` jako [[yii\db\Connection|łącznika z bazą]], 
do uzyskania dostępu i manipulowania danymi z bazy danych. Jak zostało to już wyjaśnione w sekcji [Obiekty dostępu do danych (DAO)](db-dao.md), 
możesz skonfigurować komponent `db` w pliku konfiguracyjnym aplikacji jak poniżej:

```php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=testdb',
            'username' => 'demo',
            'password' => 'demo',
        ],
    ],
];
```

Jeśli chcesz użyć innego połączenia do bazy danych niż za pomocą komponentu `db`, musisz nadpisać metodę [[yii\db\ActiveRecord::getDb()|getDb()]]:

```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        // użyj komponentu aplikacji "db2"
        return \Yii::$app->db2;  
    }
}
```


## Kwerendy <span id="querying-data"></span>

Po zadeklarowaniu klasy Active Record, możesz użyć jej do pobrania danych z powiązanej tabeli bazy danych.
Proces ten zwykle sprowadza się do następujących trzech kroków:

1. Stworzenie nowego obiektu kwerendy za pomocą metody [[yii\db\ActiveRecord::find()]];
2. Zbudowanie obiektu kwerendy za pomocą [metod konstruktora kwerend](db-query-builder.md#building-queries);
3. Wywołanie [metod kwerendy](db-query-builder.md#query-methods) w celu uzyskania danych jako instancji klasy Active Record.

Jak widać, procedura jest bardzo podobna do tej używanej przy [konstruktorze kwerend](db-query-builder.md). Jedyna różnica jest taka, że 
zamiast użycia operatora `new` do stworzenia obiektu kwerendy, wywołujemy metodę [[yii\db\ActiveRecord::find()]], która zwraca 
nowy obiekt kwerendy klasy [[yii\db\ActiveQuery]].

Poniżej znajdziesz kilka przykładów pokazujących jak używać Active Query do pobierania danych:

```php
// zwraca pojedynczego klienta o ID 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::find()
    ->where(['id' => 123])
    ->one();

// zwraca wszystkich aktywnych klientów posortowanych po ID
// SELECT * FROM `customer` WHERE `status` = 1 ORDER BY `id`
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// zwraca liczbę aktywnych klientów
// SELECT COUNT(*) FROM `customer` WHERE `status` = 1
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// zwraca wszystkich klientów w tablicy zaindeksowanej wg ID
// SELECT * FROM `customer`
$customers = Customer::find()
    ->indexBy('id')
    ->all();
```

W powyższych przykładach `$customer` jest obiektem typu `Customer`, a `$customers` jest tablicą obiektów typu `Customer`. W obu przypadkach 
dane pobrane są z tabeli `customer`.

> Info: Dzięki temu, że [[yii\db\ActiveQuery]] rozszerza klasę [[yii\db\Query]], możesz użyć *wszystkich* metod dotyczących kwerend i ich budowania 
> opisanych w sekcji [Konstruktor kwerend](db-query-builder.md).

Ponieważ zwykle kwerendy korzystają z zapytań zawierających klucz główny lub też zestaw wartości dla kilku kolumn, Yii udostępnia dwie skrótowe metody, 
pozwalające na szybsze ich użycie:

- [[yii\db\ActiveRecord::findOne()]]: zwraca pojedynczą instancję klasy Active Record, zawierającą dane z pierwszego pobranego odpowiadającego zapytaniu 
wiersza danych.
- [[yii\db\ActiveRecord::findAll()]]: zwraca tablicę instancji klasy Active Record zawierających *wszystkie* wyniki zapytania.

Obie metody mogą przyjmować jeden z następujących formatów parametrów:

- wartość skalarna: wartość jest traktowana jako wartość klucza głównego, który należy odszukać. Yii automatycznie ustali, która kolumna jest kluczem 
  głównym, odczytując informacje ze schematu bazy.
- tablica wartości skalarnych: tablica jest traktowana jako lista poszukiwanych wartości klucza głównego.
- tablica asocjacyjna: klucze tablicy są poszukiwanymi nazwami kolumn a wartości tablicy są odpowiadającymi im wartościami kolumn. Po więcej 
  szczegółów zajrzyj do rozdziału [Format asocjacyjny](db-query-builder.md#hash-format).
  
Poniższy kod pokazuje jak mogę być użyte opisane metody:

```php
// zwraca pojedynczego klienta o ID 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// zwraca klientów o ID 100, 101, 123 i 124
// SELECT * FROM `customer` WHERE `id` IN (100, 101, 123, 124)
$customers = Customer::findAll([100, 101, 123, 124]);

// zwraca aktywnego klienta o ID 123
// SELECT * FROM `customer` WHERE `id` = 123 AND `status` = 1
$customer = Customer::findOne([
    'id' => 123,
    'status' => Customer::STATUS_ACTIVE,
]);

// zwraca wszystkich nieaktywnych klientów
// SELECT * FROM `customer` WHERE `status` = 0
$customers = Customer::findAll([
    'status' => Customer::STATUS_INACTIVE,
]);
```

> Note: Ani metoda [[yii\db\ActiveRecord::findOne()]] ani [[yii\db\ActiveQuery::one()]] nie dodaje `LIMIT 1` do wygenerowanej 
> kwerendy SQL. Jeśli zapytanie może zwrócić więcej niż jeden wiersz danych, należy wywołać bezpośrednio `limit(1)`, w celu zwiększenia 
> wydajności aplikacji, np. `Customer::find()->limit(1)->one()`.

Oprócz korzystania z metod konstruktora kwerend możesz również użyć surowych zapytań SQL w celu pobrania danych do obiektu Active Record za 
pomocą metody [[yii\db\ActiveRecord::findBySql()]]:

```php
// zwraca wszystkich nieaktywnych klientów
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

Nie wywołuj dodatkowych metod konstruktora kwerend po wywołaniu [[yii\db\ActiveRecord::findBySql()|findBySql()]], ponieważ zostaną one pominięte.


## Dostęp do danych <span id="accessing-data"></span>

Jak wspomniano wyżej, dane pobrane z bazy danych są dostępne w obiekcie Active Record i każdy wiersz wyniku zapytania odpowiada pojedynczej 
instancji Active Record. Możesz odczytać wartości kolumn odwołując się do atrybutów obiektu Active Record, dla przykładu:

```php
// "id" i "email" są nazwami kolumn w tabeli "customer"
$customer = Customer::findOne(123);
$id = $customer->id;
$email = $customer->email;
```

> Note: nazwy atrybutów Active Record odpowiadają nazwom powiązanych z nimi kolumn z uwzględnieniem wielkości liter.
> Yii automatycznie definiuje atrybut Active Record dla każdej kolumny powiązanej tabeli.
> NIE należy definiować ich własnoręcznie. 

Ponieważ atrybuty Active Record nazywane są zgodnie z nazwami kolumn, możesz natknąć się na kod PHP typu 
`$customer->first_name`, gdzie podkreślniki używane są do oddzielenia poszczególnych słów w nazwach atrybutów, jeśli kolumny tabeli nazywane są 
właśnie w ten sposób. Jeśli masz wątpliwości dotyczące spojności takiego stylu programowania, powinieneś zmienić odpowiednio nazwy kolumn tabeli 
(używając np. camelCase).


### Transformacja danych <span id="data-transformation"></span>

Często zdarza się, że dane wprowadzane i/lub wyświetlane zapisane są w formacie różniącym się od tego używanego w bazie danych. Dla przykładu, 
w bazie danych przechowywane są daty urodzin klientów jako uniksowe znaczniki czasu, podczas gdy w większości przypadków pożądana forma zapisu daty 
to `'RRRR/MM/DD'`. Aby osiągnąć ten cel, można zdefiniować metody *transformujące dane* w klasie `Customer`
Active Record class like the following:

```php
class Customer extends ActiveRecord
{
    // ...

    public function getBirthdayText()
    {
        return date('Y/m/d', $this->birthday);
    }
    
    public function setBirthdayText($value)
    {
        $this->birthday = strtotime($value);
    }
}
```

Od tego momentu, w kodzie PHP, zamiast odwołać się do `$customer->birthday`, można użyć `$customer->birthdayText`, co pozwala na 
wprowadzenie i wyświetlenie daty urodzin klienta w formacie `'RRRR/MM/DD'`.

> Tip: Powyższy przykład pokazuje podstawowy sposób transformacji danych. Podczas zwyczajowej pracy z formularzami danych można skorzystać z 
> [DateValidator](tutorial-core-validators.md#date) i [[yii\jui\DatePicker|DatePicker]], co jest prostsze w użyciu i posiadające więcej możliwości.


### Pobieranie danych jako tablice <span id="data-in-arrays"></span>

Pobieranie danych jako obiekty Active Record jest wygodne i elastyczne, ale nie zawsze pożądane, zwłaszcza kiedy konieczne jest 
uzyskanie ogromnej liczby danych, z powodu użycia sporej ilości pamięci. W takim przypadku można pobrać dane jako tablicę PHP wywołując metodę 
[[yii\db\ActiveQuery::asArray()|asArray()]] przed wykonaniem kwerendy:

```php
// zwraca wszystkich klientów
// każdy klient jest zwracany w postaci tablicy asocjacyjnej
$customers = Customer::find()
    ->asArray()
    ->all();
```

> Note: Powyższy sposób zwiększa wydajność aplikacji i pozwala na zmniejszenie zużycia pamięci, ale ponieważ jest on znacznie bliższy niskiej warstwie 
> abstrakcji DB, traci się większość funkcjonalności Active Record. Bardzo ważną różnicą jest zwracany typ danych dla wartości kolumn. Kiedy dane zwracane 
> są jako obiekt Active Record, wartości kolumn są automatycznie odpowiednio rzutowane zgodnie z typem kolumny; przy danych zwracanych jako tablice 
> wartości kolumn są zawsze typu string (jako rezultat zapytania PDO bez żadnego przetworzenia), niezależnie od typu kolumny.
   

### Pobieranie danych seriami <span id="data-in-batches"></span>

W sekcji [Konstruktor kwerend](db-query-builder.md) wyjaśniliśmy, że można użyć *kwerendy serii*, aby zmniejszyć zużycie pamięci przy pobieraniu 
dużej ilości danych z bazy. Tej samej techniki można użyć w przypadku Active Record. Dla przykładu:

```php
// pobiera dziesięciu klientów na raz
foreach (Customer::find()->batch(10) as $customers) {
    // $customers jest tablicą dziesięciu lub mniej obiektów Customer
}

// pobiera dziesięciu klientów na raz i iteruje po nich pojedynczo
foreach (Customer::find()->each(10) as $customer) {
    // $customer jest obiektem Customer
}

// kwerenda seryjna z gorliwym ładowaniem
foreach (Customer::find()->with('orders')->each() as $customer) {
    // $customer jest obiektem Customer
}
```


## Zapisywanie danych <span id="inserting-updating-data"></span>

Używając Active Record możesz w łatwy sposób zapisać dane w bazie, w następujących krokach:

1. Przygotowanie instancji Active Record
2. Przypisanie nowych wartości do atrybutów Active Record
3. Wywołanie metody [[yii\db\ActiveRecord::save()]] w celu zapisania danych w bazie.

Przykład:

```php
// dodaj nowy wiersz danych
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();

// zaktualizuj istniejący wiersz danych
$customer = Customer::findOne(123);
$customer->email = 'james@newexample.com';
$customer->save();
```

Metoda [[yii\db\ActiveRecord::save()|save()]] może zarówno dodawać jak i aktualizować wiersz danych, w zależności od stanu instacji Active Record. 
Jeśli instancja została dopiero utworzona poprzez operator `new`, wywołanie [[yii\db\ActiveRecord::save()|save()]] spowoduje dodanie nowego wiersza. 
Jeśli instacja jest wynikiem użycia kwerendy, wywołanie [[yii\db\ActiveRecord::save()|save()]] zaktualizuje wiersz danych powiązanych z instancją. 

Można odróżnić dwa stany instancji Active Record sprawdzając wartość jej właściwości [[yii\db\ActiveRecord::isNewRecord|isNewRecord]]. Jest ona także 
używana przez [[yii\db\ActiveRecord::save()|save()]] w poniższy sposób:

```php
public function save($runValidation = true, $attributeNames = null)
{
    if ($this->getIsNewRecord()) {
        return $this->insert($runValidation, $attributeNames);
    } else {
        return $this->update($runValidation, $attributeNames) !== false;
    }
}
```

> Tip: Możesz również wywołać [[yii\db\ActiveRecord::insert()|insert()]] lub [[yii\db\ActiveRecord::update()|update()]] bezpośrednio, aby dodać lub 
> uaktualnić wiersz.
  

### Walidacja danych <span id="data-validation"></span>

Dzięki temu, że [[yii\db\ActiveRecord]] rozszerza klasę [[yii\base\Model]], korzysta z tych samych mechanizmów [walidacji danych](input-validation.md).
Możesz definiować zasady walidacji nadpisując metodę [[yii\db\ActiveRecord::rules()|rules()]] i uruchamiać procedurę walidacji wywołując metodę 
[[yii\db\ActiveRecord::validate()|validate()]].

Wywołanie [[yii\db\ActiveRecord::save()|save()]] automatycznie wywołuje również [[yii\db\ActiveRecord::validate()|validate()]]. 
Dopiero po pomyślnym przejściu walidacji rozpocznie się proces zapisywania danych; w przeciwnym wypadku zostanie zwrócona flaga false, a komunikaty o 
błędach walidacji można odczytać sprawdzając właściwość [[yii\db\ActiveRecord::errors|errors]]. 

> Tip: Jeśli masz pewność, że dane nie potrzebują przechodzić procesu walidacji (np. pochodzą z zaufanych źródeł), możesz wywołać `save(false)`, 
> aby go pominąć.


### Masowe przypisywanie <span id="massive-assignment"></span>

Tak jak w zwyczajnych [modelach](structure-models.md), instancje Active Record posiadają również mechanizm [masowego przypisywania](structure-models.md#massive-assignment).
Funkcjonalność ta umożliwia przypisanie wartości wielu atrybutom Active Record za pomocą pojedynczej instrukcji PHP, jak pokazano to poniżej. 
Należy jednak pamiętać, że w ten sposób mogą być przypisane tylko [bezpieczne atrybuty](structure-models.md#safe-attributes).

```php
$values = [
    'name' => 'James',
    'email' => 'james@example.com',
];

$customer = new Customer();

$customer->attributes = $values;
$customer->save();
```


### Aktualizowanie liczników <span id="updating-counters"></span>

Jednym z częstych zadań jest zmniejszanie lub zwiększanie wartości kolumny w tabeli bazy danych. Takie kolumny nazywamy licznikami.
Metoda [[yii\db\ActiveRecord::updateCounters()|updateCounters()]] służy do aktualizacji jednego lub wielu liczników.
Przykład:

```php
$post = Post::findOne(100);

// UPDATE `post` SET `view_count` = `view_count` + 1 WHERE `id` = 100
$post->updateCounters(['view_count' => 1]);
```

> Note: Jeśli używasz [[yii\db\ActiveRecord::save()]] do aktualizacji licznika, możesz otrzymać nieprawidłowe rezultaty, ponieważ jest możliwe, że 
  ten sam licznik zostanie odczytany i zapisany jednocześnie przez wiele zapytań.


### Brudne atrybuty <span id="dirty-attributes"></span>

Kiedy wywołujesz [[yii\db\ActiveRecord::save()|save()]], aby zapisać instancję Active Record, tylko *brudne atrybuty* są zapisywane. 
Atrybut uznawany jest za *brudny* jeśli jego wartość została zmodyfikowana od momentu pobrania z bazy danych lub ostatniego zapisu. 
Pamiętaj, że walidacja danych zostanie przeprowadzona niezależnie od tego, czy instancja Active Record zawiera brudne atrybuty czy też nie.

Active Record automatycznie tworzy listę brudnych atrybutów, poprzez porównanie starej wartości atrybutu do aktualnej. Możesz wywołać metodę 
[[yii\db\ActiveRecord::getDirtyAttributes()]], aby otrzymać najnowszą listę brudnych atrybutów. Dodatkowo można wywołać 
[[yii\db\ActiveRecord::markAttributeDirty()]], aby oznaczyć konkretny atrybut jako brudny.

Jeśli chcesz sprawdzić wartość atrybutu sprzed ostatniej zmiany, możesz wywołać [[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] lub 
[[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]].

> Note: Porównanie starej i nowej wartości atrybutu odbywa się za pomocą operatora `===`, zatem atrybut zostanie uznany za brudny nawet jeśli 
> ma tą samą wartość, ale jest innego typu. Taka sytuacja zdarza się często, kiedy model jest aktualizowany danymi pochodzącymi z formularza 
> HTML, gdzie każda wartość jest reprezentowana jako string.
> Aby upewnić się, że wartości będą odpowiednich typów, np. integer, możesz zaaplikować [filtr walidacji](input-validation.md#data-filtering):
> `['attributeName', 'filter', 'filter' => 'intval']`.


### Domyślne wartości atrybutów <span id="default-attribute-values"></span>

Niektóre z kolumn tabeli bazy danych mogą mieć przypisane domyślne wartości w bazie danych. W przypadku, gdy chcesz wypełnić takimi wartościami 
internetowy formularz dla instancji Active Record, zamiast ponownie ustalać wszystkie domyślne wartości możesz wywołać metodę 
[[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]], która przypisze wszystkie domyślne wartości odpowiednim atrybutom:

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz otrzyma domyślną wartość, zadeklarowaną przy definiowaniu kolumny "xyz"
```


### Aktualizowanie wielu wierszy jednocześnie <span id="updating-multiple-rows"></span>

Metody przedstawione powyżej działają na pojedynczych instancjach Active Record, dodając lub aktualizując indywidualne wiersze tabeli. 
Aby uaktualnić wiele wierszy jednocześnie, należy wywołać statyczną metodę [[yii\db\ActiveRecord::updateAll()|updateAll()]].

```php
// UPDATE `customer` SET `status` = 1 WHERE `email` LIKE `%@example.com%`
Customer::updateAll(['status' => Customer::STATUS_ACTIVE], ['like', 'email', '@example.com']);
```

W podobny sposób można wywołać [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]], aby uaktualnić liczniki wielu wierszy w tym samym czasie.

```php
// UPDATE `customer` SET `age` = `age` + 1
Customer::updateAllCounters(['age' => 1]);
```


## Usuwanie danych <span id="deleting-data"></span>

Aby usunąć pojedynczy wiersz danych, utwórz najpierw instancję Active Record odpowiadającą temu wierszowi, a następnie wywołaj metodę 
[[yii\db\ActiveRecord::delete()]].

```php
$customer = Customer::findOne(123);
$customer->delete();
```

Możesz również wywołać [[yii\db\ActiveRecord::deleteAll()]], aby usunąć kilka lub wszystkie wiersze danych. Dla przykładu:

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> Note: Należy być bardzo ostrożnym przy wywoływaniu [[yii\db\ActiveRecord::deleteAll()|deleteAll()]], ponieważ w efekcie można całkowicie usunąć 
> wszystkie dane z tabeli bazy, jeśli popełni się błąd przy ustalaniu warunków dla metody.


## Cykl życia Active Record <span id="ar-life-cycles"></span>

Istotnym elementem pracy z Yii jest zrozumienie cyklu życia Active Record w zależności od metodyki jego użycia.
Podczas każdego cyklu wykonywane są określone sekwencje metod i aby dopasować go do własnych potrzeb, wytarczy je nadpisać. 
Można również śledzić i odpowiadać na eventy Active Record uruchamiane podczas cyklu życia, aby wstrzyknąć swój własny kod. 
Takie eventy są szczególnie użyteczne podczas tworzenia wpływających na cykl życia [behaviorów](concept-behaviors.md) Active Record.

Poniżej znajdziesz wyszczególnione cykle życia Active Record wraz z metodami/eventami, które są w nie zaangażowane.


### Cykl życia nowej instancji <span id="new-instance-life-cycle"></span>

Podczas tworzenia nowej instancji Active Record za pomocą operatora `new`, zachodzi następujący cykl:

1. Konstruktor klasy.
2. [[yii\db\ActiveRecord::init()|init()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]].


### Cykl życia przy pobieraniu danych <span id="querying-data-life-cycle"></span>

Podczas pobierania danych za pomocą jednej z [metod kwerendy](#querying-data), każdy świeżo wypełniony obiekt Active Record przechodzi następujący cykl:

1. Konstruktor klasy.
2. [[yii\db\ActiveRecord::init()|init()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]].
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]].


### Cykl życia przy zapisywaniu danych <span id="saving-data-life-cycle"></span>

Podczas wywołania [[yii\db\ActiveRecord::save()|save()]], w celu dodania lub uaktualnienia danych instancji Active Record, zachodzi następujący cykl:

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]]. 
   Jeśli metoda zwróci false lub właściwość [[yii\base\ModelEvent::isValid]] ma wartość false, kolejne kroki są pomijane.
2. Proces walidacji danych. Jeśli proces zakończy się niepowodzeniem, kolejne kroki po kroku 3. są pomijane. 
3. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]].
4. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] lub 
   [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]]. Jeśli metoda zwróci false lub właściwość [[yii\base\ModelEvent::isValid]] ma 
   wartość false, kolejne kroki są pomijane.
5. Proces właściwego dodawania lub aktulizowania danych.
6. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] lub 
   [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]].
   

### Cykl życia przy usuwaniu danych <span id="deleting-data-life-cycle"></span>

Podczas wywołania [[yii\db\ActiveRecord::delete()|delete()]], w celu usunięcia danych instancji Active Record instance, zachodzi następujący cykl:

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]]. 
   Jeśli metoda zwróci false lub właściwość [[yii\base\ModelEvent::isValid]] ma wartość false, kolejne kroki są pomijane.
2. Proces właściwego usuwania danych.
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]].


> Note: Wywołanie poniższych metod NIE uruchomi żadnego z powyższych cykli:
>
> - [[yii\db\ActiveRecord::updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()]] 


## Praca z transakcjami <span id="transactional-operations"></span>

Są dwa sposoby użycia [transakcji](db-dao.md#performing-transactions) podczas pracy z Active Record. 

Pierwszy zakłada bezpośrednie ujęcie wywołań metod Active Record w blok transakcji, jak pokazano to poniżej:

```php
$customer = Customer::findOne(123);

Customer::getDb()->transaction(function($db) use ($customer) {
    $customer->id = 200;
    $customer->save();
    // ...inne operacje bazodanowe...
});

// lub alternatywnie

$transaction = Customer::getDb()->beginTransaction();
try {
    $customer->id = 200;
    $customer->save();
    // ...inne operacje bazodanowe...
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

Drugi sposób polega na utworzeniu listy operacji bazodanowych, które wymagają transakcji za pomocą metody [[yii\db\ActiveRecord::transactions()]]. 
Dla przykładu:

```php
class Customer extends ActiveRecord
{
    public function transactions()
    {
        return [
            'admin' => self::OP_INSERT,
            'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            // powyższy zapis jest odpowiednikiem następującego skróconego:
            // 'api' => self::OP_ALL,
        ];
    }
}
```

Metoda [[yii\db\ActiveRecord::transactions()]] powinna zwracać tablicę, której klucze są nazwami [scenariuszy](structure-models.md#scenarios) 
a wartości to operacje bazodanowe, które powinny być objęte transakcją. Używaj następujących stałych do określenia typu operacji:

* [[yii\db\ActiveRecord::OP_INSERT|OP_INSERT]]: operacja dodawania wykonywana za pomocą [[yii\db\ActiveRecord::insert()|insert()]];
* [[yii\db\ActiveRecord::OP_UPDATE|OP_UPDATE]]: operacja aktualizacji wykonywana za pomocą [[yii\db\ActiveRecord::update()|update()]];
* [[yii\db\ActiveRecord::OP_DELETE|OP_DELETE]]: operacja usuwania wykonywana za pomocą [[yii\db\ActiveRecord::delete()|delete()]].

Używaj operatora `|`, aby podać więcej niż jedną operację za pomocą powyższych stałych. Możesz również użyć stałej dla skróconej definicji 
wszystkich trzech powyższych operacji [[yii\db\ActiveRecord::OP_ALL|OP_ALL]].


## Optymistyczna blokada <span id="optimistic-locks"></span>

Optymistyczne blokowanie jest jednym ze sposobów uniknięcia konfliktów, które mogą wystąpić, kiedy pojedynczy wiersz danych jest aktualizowany przez 
kilku użytkowników. Dla przykładu, użytkownik A i użytkownik B edytują artykuł wiki w tym samym czasie - po tym jak użytkownik A zapisał już swoje 
zmiany, użytkownik B klika przycisk "Zapisz", aby również wykonać identyczną operację. Ponieważ użytkownik B pracował w rzeczywistości na "starej" wersji 
artykułu, byłoby wskazane powstrzymać go przed nadpisaniem wersji użytkownika A i wyświelić jakiś komunikat z wyjaśnieniem sytuacji.

Optymistyczne blokowanie rozwiązuje ten problem za pomocą dodatkowej kolumny w bazie przechowującej numer wersji każdego wiersza.
Kiedy taki wiersz jest zapisywany z wcześniejszym numerem wersji niż aktualna rzucany jest wyjątek [[yii\db\StaleObjectException]], który powstrzymuje 
zapis wiersza. Optymistyczne blokowanie może być użyte tylko przy aktualizacji lub usuwaniu istniejącego wiersza za pomocą odpowiednio 
[[yii\db\ActiveRecord::update()]] lub [[yii\db\ActiveRecord::delete()]].

Aby skorzystać z optymistycznej blokady:

1. Stwórz kolumnę w tabeli bazy danych powiązaną z klasą Active Record do przechowywania numeru wersji każdego wiersza.
   Kolumna powinna być typu big integer (w MySQL `BIGINT DEFAULT 0`).
2. Nadpisz metodę [[yii\db\ActiveRecord::optimisticLock()]], aby zwrócić nazwę tej kolumny.
3. W formularzu pobierającym dane od użytkownika, dodaj ukryte pole, gdzie przechowasz aktualny numer wersji uaktualnianego wiersza. 
   Upewnij się, że atrybut wersji ma dodaną zasadę walidacji i przechodzi poprawnie proces walidacji.
4. W akcji kontrolera uaktualniającej wiersz za pomocą Active Record, użyj bloku try-catch, aby wyłapać wyjątek [[yii\db\StaleObjectException]]. 
   Zaimplemetuj odpowiednią logikę biznesową (np. scalenie zmian, wyświetlenie komunikatu o nieaktualnej wersji, itp.), aby rozwiązać konflikt.
   
Dla przykładu, załóżmy, że kolumna wersji nazywa się `version`. Implementację optymistycznego blokowania można wykonać za pomocą następującego kodu:

```php
// ------ kod widoku -------

use yii\helpers\Html;

// ...inne pola formularza
echo Html::activeHiddenInput($model, 'version');


// ------ kod kontrolera -------

use yii\db\StaleObjectException;

public function actionUpdate($id)
{
    $model = $this->findModel($id);

    try {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    } catch (StaleObjectException $e) {
        // logika rozwiązująca konflikt
    }
}
```


## Working with Relational Data <span id="relational-data"></span>

Besides working with individual database tables, Active Record is also capable of bringing together related data,
making them readily accessible through the primary data. For example, the customer data is related with the order
data because one customer may have placed one or multiple orders. With appropriate declaration of this relation,
you may be able to access a customer's order information using the expression `$customer->orders` which gives
back the customer's order information in terms of an array of `Order` Active Record instances.


### Declaring Relations <span id="declaring-relations"></span>

To work with relational data using Active Record, you first need to declare relations in Active Record classes.
The task is as simple as declaring a *relation method* for every interested relation, like the following,

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

In the above code, we have declared an `orders` relation for the `Customer` class, and a `customer` relation
for the `Order` class. 

Each relation method must be named as `getXyz`. We call `xyz` (the first letter is in lower case) the *relation name*.
Note that relation names are *case sensitive*.

While declaring a relation, you should specify the following information:

- the multiplicity of the relation: specified by calling either [[yii\db\ActiveRecord::hasMany()|hasMany()]]
  or [[yii\db\ActiveRecord::hasOne()|hasOne()]]. In the above example you may easily read in the relation 
  declarations that a customer has many orders while an order only has one customer.
- the name of the related Active Record class: specified as the first parameter to 
  either [[yii\db\ActiveRecord::hasMany()|hasMany()]] or [[yii\db\ActiveRecord::hasOne()|hasOne()]].
  A recommended practice is to call `Xyz::className()` to get the class name string so that you can receive
  IDE auto-completion support as well as error detection at compiling stage. 
- the link between the two types of data: specifies the column(s) through which the two types of data are related.
  The array values are the columns of the primary data (represented by the Active Record class that you are declaring
  relations), while the array keys are the columns of the related data.

  An easy rule to remember this is, as you see in the example above, you write the column that belongs to the related
  Active Record directly next to it. You see there that `customer_id` is a property of `Order` and `id` is a property
  of `Customer`.
  

### Accessing Relational Data <span id="accessing-relational-data"></span>

After declaring relations, you can access relational data through relation names. This is just like accessing
an object [property](concept-properties.md) defined by the relation method. For this reason, we call it *relation property*.
For example,

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
// $orders is an array of Order objects
$orders = $customer->orders;
```

> Info: When you declare a relation named `xyz` via a getter method `getXyz()`, you will be able to access
  `xyz` like an [object property](concept-properties.md). Note that the name is case sensitive.
  
If a relation is declared with [[yii\db\ActiveRecord::hasMany()|hasMany()]], accessing this relation property
will return an array of the related Active Record instances; if a relation is declared with 
[[yii\db\ActiveRecord::hasOne()|hasOne()]], accessing the relation property will return the related
Active Record instance or null if no related data is found.

When you access a relation property for the first time, a SQL statement will be executed, like shown in the
above example. If the same property is accessed again, the previous result will be returned without re-executing
the SQL statement. To force re-executing the SQL statement, you should unset the relation property
first: `unset($customer->orders)`.

> Note: While this concept looks similar to the [object property](concept-properties.md) feature, there is an
> important difference. For normal object properties the property value is of the same type as the defining getter method.
> A relation method however returns an [[yii\db\ActiveQuery]] instance, while accessing a relation property will either
> return a [[yii\db\ActiveRecord]] instance or an array of these.
> 
> ```php
> $customer->orders; // is an array of `Order` objects
> $customer->getOrders(); // returns an ActiveQuery instance
> ```
> 
> This is useful for creating customized queries, which is described in the next section.


### Dynamic Relational Query <span id="dynamic-relational-query"></span>

Because a relation method returns an instance of [[yii\db\ActiveQuery]], you can further build this query
using query building methods before performing DB query. For example,

```php
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `subtotal` > 200 ORDER BY `id`
$orders = $customer->getOrders()
    ->where(['>', 'subtotal', 200])
    ->orderBy('id')
    ->all();
```

Unlike accessing a relation property, each time you perform a dynamic relational query via a relation method, 
a SQL statement will be executed, even if the same dynamic relational query was performed before.

Sometimes you may even want to parametrize a relation declaration so that you can more easily perform
dynamic relational query. For example, you may declare a `bigOrders` relation as follows, 

```php
class Customer extends ActiveRecord
{
    public function getBigOrders($threshold = 100)
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])
            ->where('subtotal > :threshold', [':threshold' => $threshold])
            ->orderBy('id');
    }
}
```

Then you will be able to perform the following relational queries:

```php
// SELECT * FROM `order` WHERE `subtotal` > 200 ORDER BY `id`
$orders = $customer->getBigOrders(200)->all();

// SELECT * FROM `order` WHERE `subtotal` > 100 ORDER BY `id`
$orders = $customer->bigOrders;
```


### Relations via a Junction Table <span id="junction-table"></span>

In database modelling, when the multiplicity between two related tables is many-to-many, 
a [junction table](https://en.wikipedia.org/wiki/Junction_table) is usually introduced. For example, the `order`
table and the `item` table may be related via a junction table named `order_item`. One order will then correspond
to multiple order items, while one product item will also correspond to multiple order items.

When declaring such relations, you would call either [[yii\db\ActiveQuery::via()|via()]] or [[yii\db\ActiveQuery::viaTable()|viaTable()]]
to specify the junction table. The difference between [[yii\db\ActiveQuery::via()|via()]] and [[yii\db\ActiveQuery::viaTable()|viaTable()]]
is that the former specifies the junction table in terms of an existing relation name while the latter directly
the junction table. For example,

```php
class Order extends ActiveRecord
{
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaTable('order_item', ['order_id' => 'id']);
    }
}
```

or alternatively,

```php
class Order extends ActiveRecord
{
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems');
    }
}
```

The usage of relations declared with a junction table is the same as that of normal relations. For example,

```php
// SELECT * FROM `order` WHERE `id` = 100
$order = Order::findOne(100);

// SELECT * FROM `order_item` WHERE `order_id` = 100
// SELECT * FROM `item` WHERE `item_id` IN (...)
// returns an array of Item objects
$items = $order->items;
```


### Lazy Loading and Eager Loading <span id="lazy-eager-loading"></span>

In [Accessing Relational Data](#accessing-relational-data), we explained that you can access a relation property
of an Active Record instance like accessing a normal object property. A SQL statement will be executed only when
you access the relation property the first time. We call such relational data accessing method *lazy loading*.
For example,

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$orders = $customer->orders;

// no SQL executed
$orders2 = $customer->orders;
```

Lazy loading is very convenient to use. However, it may suffer from a performance issue when you need to access
the same relation property of multiple Active Record instances. Consider the following code example. How many 
SQL statements will be executed?

```php
// SELECT * FROM `customer` LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SELECT * FROM `order` WHERE `customer_id` = ...
    $orders = $customer->orders;
}
```

As you can see from the code comment above, there are 101 SQL statements being executed! This is because each
time you access the `orders` relation property of a different `Customer` object in the for-loop, a SQL statement 
will be executed.

To solve this performance problem, you can use the so-called *eager loading* approach as shown below,

```php
// SELECT * FROM `customer` LIMIT 100;
// SELECT * FROM `orders` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->with('orders')
    ->limit(100)
    ->all();

foreach ($customers as $customer) {
    // no SQL executed
    $orders = $customer->orders;
}
```

By calling [[yii\db\ActiveQuery::with()]], you instruct Active Record to bring back the orders for the first 100
customers in one single SQL statement. As a result, you reduce the number of the executed SQL statements from 101 to 2!

You can eagerly load one or multiple relations. You can even eagerly load *nested relations*. A nested relation is a relation
that is declared within a related Active Record class. For example, `Customer` is related with `Order` through the `orders`
relation, and `Order` is related with `Item` through the `items` relation. When querying for `Customer`, you can eagerly
load `items` using the nested relation notation `orders.items`. 

The following code shows different usage of [[yii\db\ActiveQuery::with()|with()]]. We assume the `Customer` class
has two relations `orders` and `country`, while the `Order` class has one relation `items`.

```php
// eager loading both "orders" and "country"
$customers = Customer::find()->with('orders', 'country')->all();
// equivalent to the array syntax below
$customers = Customer::find()->with(['orders', 'country'])->all();
// no SQL executed 
$orders= $customers[0]->orders;
// no SQL executed 
$country = $customers[0]->country;

// eager loading "orders" and the nested relation "orders.items"
$customers = Customer::find()->with('orders.items')->all();
// access the items of the first order of the first customer
// no SQL executed
$items = $customers[0]->orders[0]->items;
```

You can eagerly load deeply nested relations, such as `a.b.c.d`. All parent relations will be eagerly loaded.
That is, when you call [[yii\db\ActiveQuery::with()|with()]] using `a.b.c.d`, you will eagerly load
`a`, `a.b`, `a.b.c` and `a.b.c.d`.  

> Info: In general, when eagerly loading `N` relations among which `M` relations are defined with a 
  [junction table](#junction-table), a total number of `N+M+1` SQL statements will be executed.
  Note that a nested relation `a.b.c.d` counts as 4 relations.

When eagerly loading a relation, you can customize the corresponding relational query using an anonymous function.
For example,

```php
// find customers and bring back together their country and active orders
// SELECT * FROM `customer`
// SELECT * FROM `country` WHERE `id` IN (...)
// SELECT * FROM `order` WHERE `customer_id` IN (...) AND `status` = 1
$customers = Customer::find()->with([
    'country',
    'orders' => function ($query) {
        $query->andWhere(['status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

When customizing the relational query for a relation, you should specify the relation name as an array key
and use an anonymous function as the corresponding array value. The anonymous function will receive a `$query` parameter
which represents the [[yii\db\ActiveQuery]] object used to perform the relational query for the relation.
In the code example above, we are modifying the relational query by appending an additional condition about order status.

> Note: If you call [[yii\db\Query::select()|select()]] while eagerly loading relations, you have to make sure
> the columns referenced in the relation declarations are being selected. Otherwise, the related models may not 
> be loaded properly. For example,
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // $orders[0]->customer is always null. To fix the problem, you should do the following:
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### Joining with Relations <span id="joining-with-relations"></span>

> Note: The content described in this subsection is only applicable to relational databases, such as 
  MySQL, PostgreSQL, etc.

The relational queries that we have described so far only reference the primary table columns when 
querying for the primary data. In reality we often need to reference columns in the related tables. For example,
we may want to bring back the customers who have at least one active order. To solve this problem, we can
build a join query like the following:

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id`
// WHERE `order`.`status` = 1
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->select('customer.*')
    ->leftJoin('order', '`order`.`customer_id` = `customer`.`id`')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->with('orders')
    ->all();
```

> Note: It is important to disambiguate column names when building relational queries involving JOIN SQL statements. 
  A common practice is to prefix column names with their corresponding table names.

However, a better approach is to exploit the existing relation declarations by calling [[yii\db\ActiveQuery::joinWith()]]:

```php
$customers = Customer::find()
    ->joinWith('orders')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->all();
```

Both approaches execute the same set of SQL statements. The latter approach is much cleaner and drier, though. 

By default, [[yii\db\ActiveQuery::joinWith()|joinWith()]] will use `LEFT JOIN` to join the primary table with the 
related table. You can specify a different join type (e.g. `RIGHT JOIN`) via its third parameter `$joinType`. If
the join type you want is `INNER JOIN`, you can simply call [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]], instead.

Calling [[yii\db\ActiveQuery::joinWith()|joinWith()]] will [eagerly load](#lazy-eager-loading) the related data by default.
If you do not want to bring in the related data, you can specify its second parameter `$eagerLoading` as false. 

Like [[yii\db\ActiveQuery::with()|with()]], you can join with one or multiple relations; you may customize the relation
queries on-the-fly; you may join with nested relations; and you may mix the use of [[yii\db\ActiveQuery::with()|with()]]
and [[yii\db\ActiveQuery::joinWith()|joinWith()]]. For example,

```php
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->andWhere(['>', 'subtotal', 100]);
    },
])->with('country')
    ->all();
```

Sometimes when joining two tables, you may need to specify some extra conditions in the `ON` part of the JOIN query.
This can be done by calling the [[yii\db\ActiveQuery::onCondition()]] method like the following:

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id` AND `order`.`status` = 1 
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->onCondition(['order.status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

This above query brings back *all* customers, and for each customer it brings back all active orders.
Note that this differs from our earlier example which only brings back customers who have at least one active order.

> Info: When [[yii\db\ActiveQuery]] is specified with a condition via [[yii\db\ActiveQuery::onCondition()|onCondition()]],
  the condition will be put in the `ON` part if the query involves a JOIN query. If the query does not involve
  JOIN, the on-condition will be automatically appended to the `WHERE` part of the query.


### Inverse Relations <span id="inverse-relations"></span>

Relation declarations are often reciprocal between two Active Record classes. For example, `Customer` is related 
to `Order` via the `orders` relation, and `Order` is related back to `Customer` via the `customer` relation.

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

Now consider the following piece of code:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// SELECT * FROM `customer` WHERE `id` = 123
$customer2 = $order->customer;

// displays "not the same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

We would think `$customer` and `$customer2` are the same, but they are not! Actually they do contain the same
customer data, but they are different objects. When accessing `$order->customer`, an extra SQL statement
is executed to populate a new object `$customer2`.

To avoid the redundant execution of the last SQL statement in the above example, we should tell Yii that
`customer` is an *inverse relation* of `orders` by calling the [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] method
like shown below:

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

With this modified relation declaration, we will have:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// No SQL will be executed
$customer2 = $order->customer;

// displays "same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

> Note: Inverse relations cannot be defined for relations involving a [junction table](#junction-table).
  That is, if a relation is defined with [[yii\db\ActiveQuery::via()|via()]] or [[yii\db\ActiveQuery::viaTable()|viaTable()]],
  you should not call [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] further.


## Saving Relations <span id="saving-relations"></span>

When working with relational data, you often need to establish relationships between different data or destroy
existing relationships. This requires setting proper values for the columns that define the relations. Using Active Record,
you may end up writing the code like the following:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

// setting the attribute that defines the "customer" relation in Order
$order->customer_id = $customer->id;
$order->save();
```

Active Record provides the [[yii\db\ActiveRecord::link()|link()]] method that allows you to accomplish this task more nicely:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

$order->link('customer', $customer);
```

The [[yii\db\ActiveRecord::link()|link()]] method requires you to specify the relation name and the target Active Record
instance that the relationship should be established with. The method will modify the values of the attributes that
link two Active Record instances and save them to the database. In the above example, it will set the `customer_id`
attribute of the `Order` instance to be the value of the `id` attribute of the `Customer` instance and then save it
to the database.

> Note: You cannot link two newly created Active Record instances.

The benefit of using [[yii\db\ActiveRecord::link()|link()]] is even more obvious when a relation is defined via
a [junction table](#junction-table). For example, you may use the following code to link an `Order` instance
with an `Item` instance:

```php
$order->link('items', $item);
```

The above code will automatically insert a row in the `order_item` junction table to relate the order with the item.

> Info: The [[yii\db\ActiveRecord::link()|link()]] method will NOT perform any data validation while
  saving the affected Active Record instance. It is your responsibility to validate any input data before
  calling this method.

The opposite operation to [[yii\db\ActiveRecord::link()|link()]] is [[yii\db\ActiveRecord::unlink()|unlink()]]
which breaks an existing relationship between two Active Record instances. For example,

```php
$customer = Customer::find()->with('orders')->all();
$customer->unlink('orders', $customer->orders[0]);
```

By default, the [[yii\db\ActiveRecord::unlink()|unlink()]] method will set the foreign key value(s) that specify
the existing relationship to be null. You may, however, choose to delete the table row that contains the foreign key value
by passing the `$delete` parameter as true to the method.
 
When a junction table is involved in a relation, calling [[yii\db\ActiveRecord::unlink()|unlink()]] will cause
the foreign keys in the junction table to be cleared, or the deletion of the corresponding row in the junction table
if `$delete` is true.


## Cross-Database Relations <span id="cross-database-relations"></span> 

Active Record allows you to declare relations between Active Record classes that are powered by different databases.
The databases can be of different types (e.g. MySQL and PostgreSQL, or MS SQL and MongoDB), and they can run on 
different servers. You can use the same syntax to perform relational queries. For example,

```php
// Customer is associated with the "customer" table in a relational database (e.g. MySQL)
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // a customer has many comments
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
    }
}

// Comment is associated with the "comment" collection in a MongoDB database
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // a comment has one customer
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}

$customers = Customer::find()->with('comments')->all();
```

You can use most of the relational query features that have been described in this section. 
 
> Note: Usage of [[yii\db\ActiveQuery::joinWith()|joinWith()]] is limited to databases that allow cross-database JOIN queries.
  For this reason, you cannot use this method in the above example because MongoDB does not support JOIN.


## Customizing Query Classes <span id="customizing-query-classes"></span>

By default, all Active Record queries are supported by [[yii\db\ActiveQuery]]. To use a customized query class
in an Active Record class, you should override the [[yii\db\ActiveRecord::find()]] method and return an instance
of your customized query class. For example,
 
```php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class Comment extends ActiveRecord
{
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}

class CommentQuery extends ActiveQuery
{
    // ...
}
```

Now whenever you are performing a query (e.g. `find()`, `findOne()`) or defining a relation (e.g. `hasOne()`) 
with `Comment`, you will be working with an instance of `CommentQuery` instead of `ActiveQuery`.

> Tip: In big projects, it is recommended that you use customized query classes to hold most query-related code
  so that the Active Record classes can be kept clean.

You can customize a query class in many creative ways to improve your query building experience. For example,
you can define new query building methods in a customized query class: 

```php
class CommentQuery extends ActiveQuery
{
    public function active($state = true)
    {
        return $this->andWhere(['active' => $state]);
    }
}
```

> Note: Instead of calling [[yii\db\ActiveQuery::where()|where()]], you usually should call
  [[yii\db\ActiveQuery::andWhere()|andWhere()]] or [[yii\db\ActiveQuery::orWhere()|orWhere()]] to append additional
  conditions when defining new query building methods so that any existing conditions are not overwritten.

This allows you to write query building code like the following:
 
```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

You can also use the new query building methods when defining relations about `Comment` or performing relational query:

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::className(), ['customer_id' => 'id'])->active();
    }
}

$customers = Customer::find()->with('activeComments')->all();

// or alternatively
 
$customers = Customer::find()->with([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

> Info: In Yii 1.1, there is a concept called *scope*. Scope is no longer directly supported in Yii 2.0,
  and you should use customized query classes and query methods to achieve the same goal.


## Selecting extra fields

When Active Record instance is populated from query results, its attributes are filled up by corresponding column
values from received data set.

You are able to fetch additional columns or values from query and store it inside the Active Record.
For example, assume we have a table named 'room', which contains information about rooms available in the hotel.
Each room stores information about its geometrical size using fields 'length', 'width', 'height'.
Imagine we need to retrieve list of all available rooms with their volume in descendant order.
So you can not calculate volume using PHP, because we need to sort the records by its value, but you also want 'volume'
to be displayed in the list.
To achieve the goal, you need to declare an extra field in your 'Room' Active Record class, which will store 'volume' value:

```php
class Room extends \yii\db\ActiveRecord
{
    public $volume;

    // ...
}
```

Then you need to compose a query, which calculates volume of the room and performs the sort:

```php
$rooms = Room::find()
    ->select([
        '{{room}}.*', // select all columns
        '([[length]] * [[width]].* [[height]]) AS volume', // calculate a volume
    ])
    ->orderBy('volume DESC') // apply sort
    ->all();

foreach ($rooms as $room) {
    echo $room->volume; // contains value calculated by SQL
}
```

Ability to select extra fields can be exceptionally useful for aggregation queries.
Assume you need to display a list of customers with the count of orders they have made.
First of all, you need to declare a `Customer` class with 'orders' relation and extra field for count storage:

```php
class Customer extends \yii\db\ActiveRecord
{
    public $ordersCount;

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}
```

Then you can compose a query, which joins the orders and calculates their count:

```php
$customers = Customer::find()
    ->select([
        '{{customer}}.*', // select all customer fields
        'COUNT({{order}}.id) AS ordersCount' // calculate orders count
    ])
    ->joinWith('orders') // ensure table junction
    ->groupBy('{{customer}}.id') // group the result to ensure aggregation function works
    ->all();
```
