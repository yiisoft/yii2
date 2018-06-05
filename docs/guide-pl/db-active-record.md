Active Record
=============

[Active Record](http://en.wikipedia.org/wiki/Active_record_pattern) zapewnia zorientowany obiektowo interfejs dostępu i manipulacji danymi 
zapisanymi w bazie danych. Klasa typu Active Record jest powiązana z tabelą bazodanową, a instacja tej klasy odpowiada pojedynczemu wierszowi 
w tabeli - *atrybut* obiektu Active Record reprezentuje wartość konkretnej kolumny w tym wierszu. Zamiast pisać bezpośrednie kwerendy bazy danych, 
można skorzystać z atrybutów i metod klasy Active Record.

Dla przykładu, załóżmy, że `Customer` jest klasą Active Record, powiązaną z tabelą `customer` i `name` jest kolumną w tabeli `customer`. 
Aby dodać nowy wiersz do tabeli `customer`, wystarczy wykonać następujący kod:

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
* PostgreSQL 8.4 lub nowszy: poprzez [[yii\db\ActiveRecord]]
* SQLite 2 i 3: poprzez [[yii\db\ActiveRecord]]
* Microsoft SQL Server 2008 lub nowszy: poprzez [[yii\db\ActiveRecord]]
* Oracle: poprzez [[yii\db\ActiveRecord]]
* CUBRID 9.3 lub nowszy: poprzez [[yii\db\ActiveRecord]] (zwróć uwagę, że z powodu [błędu](http://jira.cubrid.org/browse/APIS-658) 
  w rozszerzeniu PDO cubrid, umieszczanie wartości w cudzysłowie nie będzie działać, zatem wymagane jest zainstalowanie CUBRID 9.3 zarówno 
  jako klienta jak i serwer)
* Sphinx: poprzez [[yii\sphinx\ActiveRecord]], wymaga rozszerzenia `yii2-sphinx`
* ElasticSearch: poprzez [[yii\elasticsearch\ActiveRecord]], wymaga rozszerzenia `yii2-elasticsearch`

Dodatkowo Yii wspiera również Active Record dla następujących baz danych typu NoSQL:

* Redis 2.6.12 lub nowszy: poprzez [[yii\redis\ActiveRecord]], wymaga rozszerzenia `yii2-redis`
* MongoDB 1.3.0 lub nowszy: poprzez [[yii\mongodb\ActiveRecord]], wymaga rozszerzenia `yii2-mongodb`

W tej sekcji przewodnika opiszemy sposób użycia Active Record dla baz relacyjnych, jednakże większość zagadnień można zastosować również dla NoSQL.


## Deklarowanie klas Active Record <span id="declaring-ar-classes"></span>

Na początek zadeklaruj klasę typu Active Record rozszerzając [[yii\db\ActiveRecord|ActiveRecord]].
 
### Deklarowanie nazwy tabeli

Domyślnie każda klasa Active Record jest powiązana ze swoją tabelą w bazie danych.
Metoda [[yii\db\ActiveRecord::tableName()|tableName()]] zwraca nazwę tabeli konwertując nazwę klasy za pomocą [[yii\helpers\Inflector::camel2id()]].
Możesz przeciążyć tę metodę, jeśli tabela nie jest nazwana zgodnie z tą konwencją.

Identycznie zastosowany może być domyślny prefiks tabeli [[yii\db\Connection::$tablePrefix|tablePrefix]]. Przykładowo, jeśli 
[[yii\db\Connection::$tablePrefix|tablePrefix]] to `tbl_`, tabelą klasy `Customer` staje się `tbl_customer`, a dla `OrderItem` jest to `tbl_order_item`. 

Jeśli nazwa tabeli zostanie podana jako `{{%NazwaTabeli}}`, znak procent `%` zostanie zamieniony automatycznie na prefiks tabeli. 
Dla przykładu, `{{%post}}` staje się `{{tbl_post}}`. Nawiasy wokół nazwy tabeli są używane dla odpowiedniego [podawania nazw w kwerendach SQL](db-dao.md#quoting-table-and-column-names).

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
        return '{{customer}}';
    }
}
```

### Aktywne rekordy nazywane są "modelami"
Instancje Active Record są traktowane jak [modele](structure-models.md). Z tego powodu zwykle dodajemy klasy Active Record 
do przestrzeni nazw `app\models` (lub innej, przeznaczonej dla klas modeli). 

Dzięki temu, że [[yii\db\ActiveRecord|ActiveRecord]] rozszerza [[yii\base\Model|Model]], dziedziczy *wszystkie* funkcjonalności [modelu](structure-models.md), 
takie jak atrybuty, zasady walidacji, serializację danych itd.


## Łączenie się z bazą danych <span id="db-connection"></span>

Domyślnie Active Record używa [komponentu aplikacji](structure-application-components.md) `db` jako [[yii\db\Connection|połączenia z bazą danych]], 
do uzyskania dostępu i manipulowania jej danymi. Jak zostało to już wyjaśnione w sekcji [Obiekty dostępu do danych (DAO)](db-dao.md), 
komponent `db` można skonfigurować w pliku konfiguracyjnym aplikacji jak poniżej:

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

1. Stworzenie nowego obiektu kwerendy za pomocą metody [[yii\db\ActiveRecord::find()|find()]];
2. Zbudowanie obiektu kwerendy za pomocą [metod konstruktora kwerend](db-query-builder.md#building-queries);
3. Wywołanie [metod kwerendy](db-query-builder.md#query-methods) w celu uzyskania danych jako instancji klasy Active Record.

Jak widać, procedura jest bardzo podobna do tej używanej przy [konstruktorze kwerend](db-query-builder.md). Jedyna różnica jest taka, że 
zamiast użycia operatora `new` do stworzenia obiektu kwerendy, wywołujemy metodę [[yii\db\ActiveRecord::find()|find()]], która zwraca 
nowy obiekt kwerendy klasy [[yii\db\ActiveQuery|ActiveQuery]].

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

> Info: Dzięki temu, że [[yii\db\ActiveQuery|ActiveQuery]] rozszerza klasę [[yii\db\Query|Query]], możesz użyć *wszystkich* metod dotyczących kwerend i ich budowania 
> opisanych w sekcji [Konstruktor kwerend](db-query-builder.md).

Ponieważ zwykle kwerendy korzystają z zapytań zawierających klucz główny lub też zestaw wartości dla kilku kolumn, Yii udostępnia dwie skrótowe metody, 
pozwalające na szybsze ich użycie:

- [[yii\db\ActiveRecord::findOne()|findOne()]]: zwraca pojedynczą instancję klasy Active Record, zawierającą dane z pierwszego pobranego odpowiadającego zapytaniu 
wiersza danych.
- [[yii\db\ActiveRecord::findAll()|findAll()]]: zwraca tablicę instancji klasy Active Record zawierających *wszystkie* wyniki zapytania.

Obie metody mogą przyjmować jeden z następujących formatów parametrów:

- wartość skalarna: wartość jest traktowana jako wartość klucza głównego, który należy odszukać. Yii automatycznie ustali, która kolumna jest kluczem 
  głównym, odczytując informacje ze schematu bazy.
- tablica wartości skalarnych: tablica jest traktowana jako lista poszukiwanych wartości klucza głównego.
- tablica asocjacyjna: klucze tablicy są poszukiwanymi nazwami kolumn a wartości tablicy są odpowiadającymi im wartościami kolumn. Po więcej 
  szczegółów zajrzyj do rozdziału [Format asocjacyjny](db-query-builder.md#hash-format).
  
Poniższy kod pokazuje, jak mogą być użyte opisane metody:

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

> Note: Ani metoda [[yii\db\ActiveRecord::findOne()|findOne()]] ani [[yii\db\ActiveQuery::one()|one()]] nie dodaje `LIMIT 1` do wygenerowanej 
> kwerendy SQL. Jeśli zapytanie może zwrócić więcej niż jeden wiersz danych, należy wywołać bezpośrednio `limit(1)`, w celu zwiększenia 
> wydajności aplikacji, np. `Customer::find()->limit(1)->one()`.

Oprócz korzystania z metod konstruktora kwerend możesz również użyć surowych zapytań SQL w celu pobrania danych do obiektu Active Record za 
pomocą metody [[yii\db\ActiveRecord::findBySql()|findBySql()]]:

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
`$customer->first_name`, gdzie podkreślniki używane są do oddzielenia poszczególnych słów w nazwach atrybutów, w przypadku, gdy kolumny tabeli nazywane są 
właśnie w ten sposób. Jeśli masz wątpliwości dotyczące spojności takiego stylu programowania, powinieneś zmienić odpowiednio nazwy kolumn tabeli 
(używając np. formatowania typu "camelCase").


### Transformacja danych <span id="data-transformation"></span>

Często zdarza się, że dane wprowadzane i/lub wyświetlane zapisane są w formacie różniącym się od tego używanego w bazie danych. Dla przykładu, 
w bazie danych przechowywane są daty urodzin klientów jako uniksowe znaczniki czasu, podczas gdy w większości przypadków pożądana forma zapisu daty 
to `'RRRR/MM/DD'`. Aby osiągnąć ten format, można zdefiniować metody *transformujące dane* w klasie `Customer`:

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
> [DateValidator](tutorial-core-validators.md#date) i [[yii\jui\DatePicker|DatePicker]], co jest prostsze w użyciu i daje więcej możliwości.


### Pobieranie danych jako tablice <span id="data-in-arrays"></span>

Pobieranie danych jako obiekty Active Record jest wygodne i elastyczne, ale nie zawsze pożądane, zwłaszcza kiedy konieczne jest 
uzyskanie ogromnej liczby danych, z powodu użycia sporej ilości pamięci. W takim przypadku można pobrać dane jako tablicę PHP, wywołując metodę 
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
3. Wywołanie metody [[yii\db\ActiveRecord::save()|save()]] w celu zapisania danych w bazie.

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

> Tip: Możesz również wywołać [[yii\db\ActiveRecord::insert()|insert()]] lub [[yii\db\ActiveRecord::update()|update()]] bezpośrednio, aby, odpowiednio, 
> dodać lub uaktualnić wiersz.
  

### Walidacja danych <span id="data-validation"></span>

Dzięki temu, że [[yii\db\ActiveRecord|ActiveRecord]] rozszerza klasę [[yii\base\Model|Model]], korzysta z tych samych mechanizmów [walidacji danych](input-validation.md).
Możesz definiować zasady walidacji nadpisując metodę [[yii\db\ActiveRecord::rules()|rules()]] i uruchamiać procedurę walidacji wywołując metodę 
[[yii\db\ActiveRecord::validate()|validate()]].

Wywołanie [[yii\db\ActiveRecord::save()|save()]] automatycznie wywołuje również metodę [[yii\db\ActiveRecord::validate()|validate()]]. 
Dopiero po pomyślnym przejściu walidacji rozpocznie się proces zapisywania danych; w przeciwnym wypadku zostanie zwrócona flaga `false` - komunikaty z 
błędami walidacji można odczytać sprawdzając właściwość [[yii\db\ActiveRecord::errors|errors]]. 

> Tip: Jeśli masz pewność, że dane nie potrzebują przechodzić procesu walidacji (np. pochodzą z zaufanych źródeł), możesz wywołać `save(false)`, 
> aby go pominąć.


### Masowe przypisywanie <span id="massive-assignment"></span>

Tak jak w zwyczajnych [modelach](structure-models.md), instancje Active Record posiadają również mechanizm 
[masowego przypisywania](structure-models.md#massive-assignment). Funkcjonalność ta umożliwia przypisanie wartości wielu atrybutom Active Record za 
pomocą pojedynczej instrukcji PHP, jak pokazano to poniżej. 
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

> Note: Jeśli używasz [[yii\db\ActiveRecord::save()|save()]] do aktualizacji licznika, możesz otrzymać nieprawidłowe rezultaty, ponieważ jest możliwe, że 
> ten sam licznik zostanie odczytany i zapisany jednocześnie przez wiele zapytań.


### Brudne atrybuty <span id="dirty-attributes"></span>

Kiedy wywołujesz [[yii\db\ActiveRecord::save()|save()]], aby zapisać instancję Active Record, tylko *brudne atrybuty* są zapisywane. 
Atrybut uznawany jest za *brudny* jeśli jego wartość została zmodyfikowana od momentu pobrania z bazy danych lub ostatniego zapisu. 
Pamiętaj, że walidacja danych zostanie przeprowadzona niezależnie od tego, czy instancja Active Record zawiera brudne atrybuty czy też nie.

Active Record automatycznie tworzy listę brudnych atrybutów, poprzez porównanie starej wartości atrybutu do aktualnej. Możesz wywołać metodę 
[[yii\db\ActiveRecord::getDirtyAttributes()|getDirtyAttributes()]], aby otrzymać najnowszą listę brudnych atrybutów. Dodatkowo można wywołać 
[[yii\db\ActiveRecord::markAttributeDirty()|markAttributeDirty()]], aby oznaczyć konkretny atrybut jako brudny.

Jeśli chcesz sprawdzić wartość atrybutu sprzed ostatniej zmiany, możesz wywołać [[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] lub 
[[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]].

> Note: Porównanie starej i nowej wartości atrybutu odbywa się za pomocą operatora `===`, zatem atrybut zostanie uznany za brudny nawet jeśli 
> ma tą samą wartość, ale jest innego typu. Taka sytuacja zdarza się często, kiedy model jest aktualizowany danymi pochodzącymi z formularza 
> HTML, gdzie każda wartość jest reprezentowana jako string.
> Aby upewnić się, że wartości będą odpowiednich typów, np. integer, możesz zaaplikować [filtr walidacji](input-validation.md#data-filtering):
> `['attributeName', 'filter', 'filter' => 'intval']`.  Działa on z wszystkimi funkcjami PHP rzutującymi typy jak [intval()](http://php.net/manual/en/function.intval.php), 
> [floatval()](http://php.net/manual/en/function.floatval.php), [boolval](http://php.net/manual/en/function.boolval.php), itp...


### Domyślne wartości atrybutów <span id="default-attribute-values"></span>

Niektóre z kolumn tabeli bazy danych mogą mieć przypisane domyślne wartości w bazie danych. W przypadku, gdy chcesz wypełnić takimi wartościami 
formularz dla instancji Active Record, zamiast ponownie ustawiać wszystkie domyślne wartości, możesz wywołać metodę 
[[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]], która przypisze wszystkie domyślne wartości odpowiednim atrybutom:

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz otrzyma domyślną wartość, zadeklarowaną przy definiowaniu kolumny "xyz"
```


### Rzutowanie typów atrybutów <span id="attributes-typecasting"></span>

Po wypełnieniu rezultatem kwerendy, [[yii\db\ActiveRecord]] przeprowadza automatyczne rzutowanie typów na wartościach swoich atrybutów, 
używając do tego celu informacji zawartych w [schemacie tabeli bazy danych](db-dao.md#database-schema). Pozwala to na prawidłowe przedstawienie 
danych pobranych z kolumny tabeli zadeklarowanej jako liczba całkowita, w postaci wartości typu PHP integer w instancji klasy ActiveRecord (typu boolean jako boolean itp.).
Mechanizm rzutowania ma jednak kilka ograniczeń:

* Wartości typu zmiennoprzecinkowego nie są konwertowane na float, a zamiast tego są przedstawiane jako łańcuch znaków, aby zachować dokładność ich liczbowej prezentacji.
* Konwersja typu integer zależy od zakresu liczb całkowitych używanego systemu operacyjnego.
  Wartości kolumn zadeklarowanych jako 'unsigned integer' lub 'big integer' będą przekonwertowane do PHP integer tylko na systemach 64-bitowych,
  a na 32-bitowych będą przedstawione jako łańcuchy znaków.

Zwróć uwagę na to, że rzutowanie typów jest wykonywane tylko podczas wypełniania instancji ActiveRecord rezultatem kwerendy. Automatyczna konwersja nie jest przeprowadzana 
dla wartości załadowanych poprzez żądanie HTTP lub ustawionych bezpośrednio dla właściwości klasy.
Schemat tabeli będzie również użyty do przygotowania instrukcji SQL przy zapisywaniu danych ActiveRecord, aby upewnić się, że wartości są przypisane w kwerendzie z prawidłowymi typami. 
Atrybuty instancji ActiveRecord nie będą jednak przekonwertowane w procesie zapisywania.

> Tip: możesz użyć [[yii\behaviors\AttributeTypecastBehavior]], aby skonfigurować proces rzutowania typów dla wartości atrybutów w momencie ich walidacji lub zapisu.


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
[[yii\db\ActiveRecord::delete()|delete()]].

```php
$customer = Customer::findOne(123);
$customer->delete();
```

Możesz również wywołać [[yii\db\ActiveRecord::deleteAll()|deleteAll()]], aby usunąć kilka lub wszystkie wiersze danych. Dla przykładu:

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> Note: Należy być bardzo ostrożnym przy wywoływaniu [[yii\db\ActiveRecord::deleteAll()|deleteAll()]], ponieważ w efekcie można całkowicie usunąć 
> wszystkie dane z tabeli bazy, jeśli popełni się błąd przy ustalaniu warunków dla metody.


## Cykl życia Active Record <span id="ar-life-cycles"></span>

Istotnym elementem pracy z Yii jest zrozumienie cyklu życia Active Record w zależności od metodyki jego użycia.
Podczas każdego cyklu wykonywane są określone sekwencje metod i aby dopasować go do własnych potrzeb, wystarczy je nadpisać. 
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
   Jeśli metoda zwróci `false` lub właściwość [[yii\base\ModelEvent::isValid|isValid]] ma wartość `false`, kolejne kroki są pomijane.
2. Proces walidacji danych. Jeśli proces zakończy się niepowodzeniem, kolejne kroki po kroku 3. są pomijane. 
3. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]].
4. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] lub 
   [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]]. Jeśli metoda zwróci `false` lub właściwość [[yii\base\ModelEvent::isValid|isValid]] ma 
   wartość `false`, kolejne kroki są pomijane.
5. Proces właściwego dodawania lub aktulizowania danych.
6. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] lub 
   [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]].
   

### Cykl życia przy usuwaniu danych <span id="deleting-data-life-cycle"></span>

Podczas wywołania [[yii\db\ActiveRecord::delete()|delete()]], w celu usunięcia danych instancji Active Record, zachodzi następujący cykl:

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]]. 
   Jeśli metoda zwróci `false` lub właściwość [[yii\base\ModelEvent::isValid|isValid]] ma wartość `false`, kolejne kroki są pomijane.
2. Proces właściwego usuwania danych.
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: uruchamia event [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]].


> Note: Wywołanie poniższych metod NIE uruchomi żadnego z powyższych cykli:
>
> - [[yii\db\ActiveRecord::updateAll()|updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()|deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()|updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] 


### Odświeżanie cyklu życia danych <span id="refreshing-data-life-cycle"></span>

Wywołanie [[yii\db\ActiveRecord::refresh()|refresh()]] w celu odświeżenia instancji Active Record, uruchamia event 
[[yii\db\ActiveRecord::EVENT_AFTER_REFRESH|EVENT_AFTER_REFRESH]], o ile odświeżenie się powiedzie i metoda zwróci `true`.


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
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```

> Note: w powyższym kodzie znajdują się dwa bloki catch dla kompatybilności 
> z PHP 5.x i PHP 7.x. `\Exception` implementuje [interfejs `\Throwable`](http://php.net/manual/en/class.throwable.php)
> od PHP 7.0, zatem można pominąć część z `\Exception`, jeśli Twoja aplikacja używa tylko PHP 7.0 lub wyższego.

Drugi sposób polega na utworzeniu listy operacji bazodanowych, które wymagają transakcji za pomocą metody [[yii\db\ActiveRecord::transactions()|transactions()]]. 
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

Metoda [[yii\db\ActiveRecord::transactions()|transactions()]] powinna zwracać tablicę, której klucze są nazwami [scenariuszy](structure-models.md#scenarios), 
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
artykułu, byłoby wskazane powstrzymać go przed nadpisaniem wersji użytkownika A i wyświelić komunikat wyjaśniający sytuację.

Optymistyczne blokowanie rozwiązuje ten problem za pomocą dodatkowej kolumny w bazie przechowującej numer wersji każdego wiersza.
Kiedy taki wiersz jest zapisywany z wcześniejszym numerem wersji niż aktualna rzucany jest wyjątek [[yii\db\StaleObjectException|StaleObjectException]], który powstrzymuje 
zapis wiersza. Optymistyczne blokowanie może być użyte tylko przy aktualizacji lub usuwaniu istniejącego wiersza za pomocą odpowiednio 
[[yii\db\ActiveRecord::update()|update()]] lub [[yii\db\ActiveRecord::delete()|delete()]].

Aby skorzystać z optymistycznej blokady:

1. Stwórz kolumnę w tabeli bazy danych powiązaną z klasą Active Record do przechowywania numeru wersji każdego wiersza.
   Kolumna powinna być typu big integer (przykładowo w MySQL `BIGINT DEFAULT 0`).
2. Nadpisz metodę [[yii\db\ActiveRecord::optimisticLock()|optimisticLock()]], aby zwrócić nazwę tej kolumny.
3. W formularzu pobierającym dane od użytkownika, dodaj ukryte pole, gdzie przechowasz aktualny numer wersji uaktualnianego wiersza. 
   Upewnij się, że atrybut wersji ma dodaną zasadę walidacji i przechodzi poprawnie jej proces.
4. W akcji kontrolera uaktualniającej wiersz za pomocą Active Record, użyj bloku try-catch, aby wyłapać wyjątek [[yii\db\StaleObjectException|StaleObjectException]]. 
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


## Praca z danymi relacji <span id="relational-data"></span>

Oprócz korzystania z indywidualnych tabel bazy danych, Active Record umożliwia również na uzyskanie danych relacji, 
pozwalając na odczytanie ich z poziomu głównego obiektu. Dla przykładu, dane klienta są powiązane relacją z danymi zamówienia, 
ponieważ jeden klient może złożyć jedno lub wiele zamówień. Odpowiednio deklarując tę relację, można uzyskać dane zamówienia klienta, 
używając wyrażenia `$customer->orders`, które zwróci informacje o zamówieniu klienta jako tablicę instancji `Order` typu Active Record.


### Deklarowanie relacji <span id="declaring-relations"></span>

Aby móc pracować z relacjami używając Active Record, najpierw musisz je zadeklarować w obrębie klasy.
Deklaracja odbywa się za pomocą utworzenia prostej *metody relacyjnej* dla każdej relacji osobno, jak w przykładach poniżej:

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

W powyższym kodzie, zadeklarowano relację `orders` dla klasy `Customer` i relację `customer` dla klasy `Order`. 

Każda metoda relacyjna musi mieć nazwę utworzoną według wzoru `getXyz`. `xyz` (pierwsza litera jest mała) jest *nazwą relacji*.
Zwróć uwagę na to, że nazwy relacji *uwzględniają wielkość liter*.

Deklarując relację powinno się zwrócić uwagę na następujące dane:

- mnogość relacji: określona przez wywołanie odpowiednio [[yii\db\ActiveRecord::hasMany()|hasMany()]]
  lub [[yii\db\ActiveRecord::hasOne()|hasOne()]]. W powyższym przykładzie można łatwo zobaczyć w definicji relacji, że 
  klient może mieć wiele zamówień, podczas gdy zamówienie ma tylko jednego klienta.
- nazwę powiązanej klasy Active Record: określoną jako pierwszy argument w [[yii\db\ActiveRecord::hasMany()|hasMany()]] lub 
  [[yii\db\ActiveRecord::hasOne()|hasOne()]].
  Rekomendowany sposób uzyskania nazwy klasy to wywołanie `Xyz::className()`, dzięki czemu możemy posiłkować się wsparciem autouzupełniania IDE 
  i wykrywaniem błędów na poziomie kompilacji. 
- powiązanie pomiędzy dwoma rodzajami danych: określone jako kolumna(y), poprzez którą dane nawiązują relację.
  Wartości tablicy są kolumnami głównych danych (reprezentowanymi przez klasę Active Record, w której deklaruje się relacje), a klucze tablicy są 
  kolumnami danych relacyjnych.

  Aby łatwo opanować technikę deklarowania relacji wystarczy zapamiętać, że kolumnę należącą do relacyjnej klasy Active Record zapisuje się zaraz obok 
  jej nazwy (jak to widać w przykładzie powyżej - `customer_id` jest właściwością `Order` a `id` jest właściwością `Customer`).
  

### Uzyskiwanie dostępu do danych relacji <span id="accessing-relational-data"></span>

Po zadeklarowaniu relacji, możesz uzyskać dostęp do danych poprzez jej nazwę. Odbywa się to w taki sam sposób jak uzyskiwanie dostępu do 
[właściwości](concept-properties.md) obiektu zdefiniowanego w metodzie relacyjnej. Właśnie dlatego też nazywamy je *właściwościami relacji*.
Przykład:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
// $orders jest tablicą obiektów typu Order
$orders = $customer->orders;
```

> Info: Deklarując relację o nazwie `xyz` poprzez metodę-getter `getXyz()`, uzyskasz dostęp do `xyz` jak do [właściwości obiektu](concept-properties.md). 
> Zwróć uwagę na to, że nazwa uwzględnia wielkość liter.
  
Jeśli relacja jest zadeklarowana poprzez [[yii\db\ActiveRecord::hasMany()|hasMany()]], zwraca tablicę powiązanych instancji Active Record; 
jeśli deklaracja odbywa się poprzez [[yii\db\ActiveRecord::hasOne()|hasOne()]], zwraca pojedynczą powiązaną instancję Active Record lub wartość `null`, 
w przypadku, gdy nie znaleziono powiązanych danych.

Podczas pierwszego odwołania się do właściwości relacji wykonywana jest kwerenda SQL, tak jak pokazano to w przykładzie powyżej. 
Odwołanie się do tej samej właściwości kolejny raz zwróci poprzedni wynik, bez wykonywanie ponownie kwerendy. Aby wymusić wykonanie kwerendy w takiej 
sytuacji, należy najpierw usunąć z pamięci właściwość relacyjną poprzez `unset($customer->orders)`.

> Note: Pomimo podobieństwa mechanizmu relacji do [właściwości obiektu](concept-properties.md), jest tutaj znacząca różnica. 
> Wartości właściwości zwykłych obiektów są tego samego typu jak definiująca je metoda-getter.
> Metoda relacyjna zwraca jednak instancję [[yii\db\ActiveQuery|ActiveQuery]], a właściwości relacji są instancjami [[yii\db\ActiveRecord|ActiveRecord]] lub tablicą takich obiektów.
> 
> ```php
> $customer->orders; // tablica obiektów `Order`
> $customer->getOrders(); // instancja ActiveQuery
> ```
> 
> Taka funkcjonalność jest użyteczna przy tworzeniu kwerend dostosowanych do potrzeb programisty, co opisane jest w następnej sekcji.


### Dynamiczne kwerendy relacyjne <span id="dynamic-relational-query"></span>

Dzięki temu, że metoda relacyjna zwraca instancję [[yii\db\ActiveQuery|ActiveQuery]], możliwe jest dalsze rozbudowanie takiej kwerendy korzystając z 
metod konstruowania kwerend. Dla przykładu:

```php
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `subtotal` > 200 ORDER BY `id`
$orders = $customer->getOrders()
    ->where(['>', 'subtotal', 200])
    ->orderBy('id')
    ->all();
```

Inaczej niż w przypadku właściwości relacji, za każdym razem, gdy wywyłujesz dynamiczną kwerendę relacyjną poprzez metodę relacji, wykonywane jest 
zapytanie do bazy, nawet jeśli identyczna kwerenda została już wywołana wcześniej.

Możliwe jest także sparametryzowanie deklaracji relacji, dzięki czemu można w łatwiejszy sposób wykonywać relacyjne kwerendy. Dla przykładu, możesz 
zadeklarować relację `bigOrders` jak to pokazano poniżej: 

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

Dzięki czemu możesz wykonać następujące relacyjne kwerendy:

```php
// SELECT * FROM `order` WHERE `subtotal` > 200 ORDER BY `id`
$orders = $customer->getBigOrders(200)->all();

// SELECT * FROM `order` WHERE `subtotal` > 100 ORDER BY `id`
$orders = $customer->bigOrders;
```


### Relacje za pomocą tabeli węzła <span id="junction-table"></span>

W projekcie bazy danych, kiedy połączenie pomiędzy dwoma relacyjnymi tabelami jest typu wiele-do-wielu, zwykle stosuje się tzw. 
[tabelę węzła](https://en.wikipedia.org/wiki/Junction_table). Dla przykładu, tabela `order` i tabela `item` mogą być powiązane poprzez węzeł nazwany 
`order_item`. Jedno zamówienie będzie posiadało wiele produktów zamówienia (pozycji), a każdy indywidualny produkt będzie także powiązany z wieloma 
pozycjami zamówienia.

Deklarując takie relacje, możesz wywołać zarówno metodę [[yii\db\ActiveQuery::via()|via()]] jak i [[yii\db\ActiveQuery::viaTable()|viaTable()]], aby 
określić tabelę węzła. Różnica pomiędzy [[yii\db\ActiveQuery::via()|via()]] i [[yii\db\ActiveQuery::viaTable()|viaTable()]] jest taka, że pierwsza metoda 
definiuje tabelę węzła dla istniejącej nazwy relacji, podczas gdy druga definiuje bezpośrednio węzeł. Przykład:

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

lub alternatywnie,

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

Sposób użycia relacji zadeklarowanych z pomocą tabeli węzła jest taki sam jak dla zwykłych relacji. Dla przykładu:

```php
// SELECT * FROM `order` WHERE `id` = 100
$order = Order::findOne(100);

// SELECT * FROM `order_item` WHERE `order_id` = 100
// SELECT * FROM `item` WHERE `item_id` IN (...)
// zwraca tablicę obiektów Item
$items = $order->items;
```


### Pobieranie leniwe i gorliwe <span id="lazy-eager-loading"></span>

W sekcji [Uzyskiwanie dostępu do danych relacji](#accessing-relational-data) wyjaśniliśmy, że można uzyskać dostęp do właściwości relacji instancji 
Active Record w identyczny sposób jak w przypadku zwykłych właściwości obiektu. Kwerenda SQL zostanie wykonana tylko w momencie pierwszego 
odwołania się do właściwości relacji. Taki sposób uzyskiwania relacyjnych danych nazywamy *pobieraniem leniwym*.
Przykład:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$orders = $customer->orders;

// bez wykonywania zapytania SQL
$orders2 = $customer->orders;
```

Leniwe pobieranie jest bardzo wygodne w użyciu, może jednak powodować spadek wydajności aplikacji, kiedy konieczne jest uzyskanie dostępu do 
tej samej relacyjnej właściwości dla wielu instancji Active Record. Rozważmy poniższy przykład - ile zapytań SQL zostanie wykonanych?

```php
// SELECT * FROM `customer` LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SELECT * FROM `order` WHERE `customer_id` = ...
    $orders = $customer->orders;
}
```

Jak wynika z opisu powyżej, zostanie wykonanych aż 101 kwerend SQL! Dzieje się tak, ponieważ za każdym razem, gdy uzyskujemy dostęp do właściwości 
relacyjnej `orders` dla kolejnego obiektu `Customer` w pętli, wykonywane jest nowe zapytanie SQL.

Aby rozwiązać ten wydajnościowy problem, należy użyć tak zwanego *gorliwego pobierania*, jak w przykładzie poniżej:

```php
// SELECT * FROM `customer` LIMIT 100;
// SELECT * FROM `orders` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->with('orders')
    ->limit(100)
    ->all();

foreach ($customers as $customer) {
    // kwerenda SQL nie jest wykonywana
    $orders = $customer->orders;
}
```

Wywołanie metody [[yii\db\ActiveQuery::with()|with()]] powoduje pobranie zamówień dla pierwszych 100 klientów w pojedynczej kwerendzie SQL, dzięki czemu 
redukujemy ilość zapytań ze 101 do 2!

Możliwe jest gorliwe pobranie jednej lub wielu relacji, a nawet gorliwe pobranie *zagnieżdżonych relacji*. Zagnieżdżona relacja to taka, która 
została zadeklarowana w relacyjnej klasie Active Record. Dla przykładu, `Customer` jest powiązany z `Order` poprzez relację `orders`, a `Order` 
jest powiązany z `Item` poprzez relację `items`. Ładując dane dla `Customer`, możesz gorliwie pobrać `items` używając notacji zagnieżdżonej 
relacji `orders.items`. 

Poniższy kod pokazuje różne sposoby użycia [[yii\db\ActiveQuery::with()|with()]]. Zakładamy, że klasa `Customer` posiada dwie relacje `orders` i 
`country`, a klasa `Order` jedną relację `items`.

```php
// gorliwe pobieranie "orders" i "country"
$customers = Customer::find()->with('orders', 'country')->all();
// odpowiednik powyższego w zapisie tablicowym
$customers = Customer::find()->with(['orders', 'country'])->all();
// kwerenda SQL nie jest wykonywana
$orders= $customers[0]->orders;
// kwerenda SQL nie jest wykonywana
$country = $customers[0]->country;

// gorliwe pobieranie "orders" i zagnieżdżonej relacji "orders.items"
$customers = Customer::find()->with('orders.items')->all();
// uzyskanie dostępu do produktów pierwszego zamówienia pierwszego klienta
// kwerenda SQL nie jest wykonywana
$items = $customers[0]->orders[0]->items;
```

Możesz pobrać gorliwie także głęboko zagnieżdżone relacje, jak np. `a.b.c.d`. Każda z kolejnych następujących po sobie relacji zostanie pobrana gorliwie - 
wywołując [[yii\db\ActiveQuery::with()|with()]] z `a.b.c.d`, pobierzesz `a`, `a.b`, `a.b.c` i `a.b.c.d`.  

> Info: Podsumowując, podczas gorliwego pobierania `N` relacji, pośród których `M` relacji jest zdefiniowanych za pomocą 
> [tabeli węzła](#junction-table), zostanie wykonanych łącznie `N+M+1` kwerend SQL.
> Zwróć uwagę na to, że zagnieżdżona relacja `a.b.c.d` jest liczona jako 4 relacje.

Podczas gorliwego pobierania relacji, możesz dostosować kwerendę do własnych potrzeb korzystając z funkcji anonimowej.
Przykład:

```php
// znajdź klientów i pobierz ich kraje zamieszkania i aktywne zamówienia
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

Dostosowując relacyjną kwerendę należy podać nazwę relacji jako klucz tablicy i użyć funkcji anonimowej jako odpowiadającej kluczowi wartości. 
Funkcja anonimowa otrzymuje parametr `$query`, reprezentujący obiekt [[yii\db\ActiveQuery|ActiveQuery]], służący do wykonania relacyjnej kwerendy.
W powyższym przykładzie modyfikujemy relacyjną kwerendę dodając warunek ze statusem zamówienia.

> Note: Wywołując [[yii\db\Query::select()|select()]] podczas gorliwego pobierania relacji, należy upewnić się, że kolumny określone w deklaracji 
> relacji znajdują się na liście pobieranych. W przeciwnym razie powiązany model może nie zostać poprawnie załadowany. Przykład:
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // $orders[0]->customer ma zawsze wartość `null`. Aby rozwiązać ten problem, należy użyć:
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### Przyłączanie relacji <span id="joining-with-relations"></span>

> Note: Zawartość tej sekcji odnosi się tylko do relacyjnych baz danych, takich jak MySQL, PostgreSQL, itp.

Relacyjne kwerendy opisane do tej pory jedynie nawiązują do głównych kolumn tabeli podczas pobierania danych. W rzeczywistości często musimy 
odnieść się do kolumn w powiązanych tabelach. Przykładowo chcemy pobrać klientów, którzy złożyli przynajmniej jedno aktywne zamówienie - możemy tego 
dokonać za pomocą następującej przyłączającej kwerendy:

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

> Note: Podczas tworzenia relacyjnych kwerend zawierających instrukcję SQL JOIN koniecznym jest ujednoznacznienie nazw kolumn. 
> Standardową praktyką w takim wypadku jest poprzedzenie nazwy kolumny odpowiadającą jej nazwą tabeli.

Jeszcze lepszym rozwiązaniem jest użycie istniejącej deklaracji relacji wywołując metodę [[yii\db\ActiveQuery::joinWith()|joinWith()]]:

```php
$customers = Customer::find()
    ->joinWith('orders')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->all();
```

Oba rozwiązania wykonują te same zestawy instrukcji SQL, ale ostatnie jest o wiele schludniejsze. 

[[yii\db\ActiveQuery::joinWith()|joinWith()]] domyślnie korzysta z `LEFT JOIN` do przyłączenia głównej tabeli z relacyjną. 
Możesz określić inny typ przyłączenia (np. `RIGHT JOIN`) podając trzeci parametr `$joinType`. Jeśli chcesz użyć typu przyłączenia `INNER JOIN`, 
możesz bezpośrednio wywołać metodę [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]].

Wywołanie [[yii\db\ActiveQuery::joinWith()|joinWith()]] domyślnie [pobierze gorliwie](#lazy-eager-loading) dane relacyjne.
Jeśli nie chcesz pobierać danych w ten sposób, możesz ustawić drugi parametr `$eagerLoading` na `false`. 

Tak jak w przypadku [[yii\db\ActiveQuery::with()|with()]], możesz przyłączyć jedną lub wiele relacji na raz, dodać do nich dodatkowe warunki, 
przyłączyć zagnieżdżone relacje i korzystać z zarówno [[yii\db\ActiveQuery::with()|with()]] jak i [[yii\db\ActiveQuery::joinWith()|joinWith()]]. Przykładowo:

```php
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->andWhere(['>', 'subtotal', 100]);
    },
])->with('country')
    ->all();
```

Czasem, przyłączając dwie tabele, musisz sprecyzować dodatkowe warunki dla części `ON` kwerendy JOIN.
Można to zrobić wywołując metodę [[yii\db\ActiveQuery::onCondition()|onCondition()]] w poniższy sposób:

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

Powyższa kwerenda pobiera *wszystkich* klientów i dla każdego z nich pobiera wszystkie aktywne zamówienia.
Zwróć uwagę na to, że ten przykład różni się od poprzedniego, gdzie pobierani byli tylko klienci posiadający przynajmniej jedno aktywne zamówienie.

> Info: Jeśli [[yii\db\ActiveQuery|ActiveQuery]] zawiera warunek podany za pomocą [[yii\db\ActiveQuery::onCondition()|onCondition()]],
> będzie on umieszczony w części instrukcji `ON` tylko jeśli kwerenda zawiera JOIN. W przeciwnym wypadku warunek ten będzie automatycznie 
> dodany do części `WHERE`. Może zatem składać się z warunków opierających się tylko na kolumnach powiązanej tabeli.


#### Aliasy dołączanych tabeli <span id="relation-table-aliases"></span>

Jak już wspomniano wcześniej, używając JOIN w kwerendzie, musimy ujednoznacznić nazwy kolumn. Z tego powodu często stosuje się aliasy dla tabel. 
Alias dla kwerendy relacyjnej można ustawić, modyfikując ją w następujący sposób:

```php
$query->joinWith([
    'orders' => function ($q) {
        $q->from(['o' => Order::tableName()]);
    },
])
```

Powyższy sposób wygląda jednak na bardzo skomplikowany i wymaga ręcznej modyfikacji w kodzie nazwy tabeli obiektu relacji lub wywołania `Order::tableName()`.
Od wersji 2.0.7, Yii udostępnia do tego celu skróconą metodę. Możliwe jest zdefiniowanie i używanie aliasu dla tabeli relacji w poniższy sposób:

```php
// dołącz relację 'orders' i posortuj wyniki po 'orders.id'
$query->joinWith(['orders o'])->orderBy('o.id');
```

Powyższy kod działa dla prostych relacji. Jeśli jednak potrzebujesz aliasu dla tabeli dołączonej w zagnieżdżonej relacji,
np. `$query->joinWith(['orders.product'])`, musisz rozwinąć wywołanie `joinWith` jak w poniższym przykładzie:

```php
$query->joinWith(['orders o' => function($q) {
        $q->joinWith('product p');
    }])
    ->where('o.amount > 100');
```

### Odwrócone relacje <span id="inverse-relations"></span>

Deklaracje relacji są zazwyczaj obustronne dla dwóch klas Active Record. Przykładowo `Customer` jest powiązany z `Order` poprzez relację `orders`, 
a `Order` jest powiązany jednocześnie z `Customer` za pomocą relacji `customer`.

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

Rozważmy teraz poniższy kod:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// SELECT * FROM `customer` WHERE `id` = 123
$customer2 = $order->customer;

// zwraca "różne"
echo $customer2 === $customer ? 'takie same' : 'różne';
```

Wydawałoby się, że `$customer` i `$customer2` powinny być identyczne, ale jednak nie są! W rzeczywistości zawierają takie same dane klienta, ale 
są różnymi obiektami. Wywołując `$order->customer` wykonywana jest dodatkowa kwerenda SQL do wypełnienia nowego obiektu `$customer2`.

Aby uniknąć nadmiarowego wykonywania ostatniej kwerendy SQL w powyższym przykładzie, powinniśmy wskazać Yii, że `customer` jest *odwróconą relacją* 
`orders` wywołując metodę [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] jak pokazano to poniżej:

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

Z tą dodatkową instrukcją w deklaracji relacji uzyskamy:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// kwerenda SQL nie jest wykonywana
$customer2 = $order->customer;

// wyświetla "takie same"
echo $customer2 === $customer ? 'takie same' : 'różne';
```

> Note: Odwrócone relacje nie mogą być definiowane dla relacji zawierających [tabelę węzła](#junction-table), dlatego też definiując relację z użyciem 
> [[yii\db\ActiveQuery::via()|via()]] lub [[yii\db\ActiveQuery::viaTable()|viaTable()]] nie powinno się już wywoływać 
> [[yii\db\ActiveQuery::inverseOf()|inverseOf()]].


## Zapisywanie relacji <span id="saving-relations"></span>

Podczas pracy z danymi relacyjnymi często konieczne jest ustalenie związku pomiędzy różnymi danymi lub też usunięcie istniejącego połączenia. 
Takie akcje wymagają ustalenia właściwych wartości dla kolumn definiujących relacje. Korzystając z Active Record można użyć następujących instrukcji:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

// ustawianie wartości dla atrybutu definiującego relację "customer" dla Order
$order->customer_id = $customer->id;
$order->save();
```

Active Record zawiera metodę [[yii\db\ActiveRecord::link()|link()]], która pozwala na uzyskanie powyższego w efektywniejszy sposób:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

$order->link('customer', $customer);
```

Metoda [[yii\db\ActiveRecord::link()|link()]] wymaga podania konkretnej nazwy relacji i docelowej instancji Active Record, z którą powinna być nawiązany 
związek. Mechanizm ten zmodyfikuje wartości atrybutów łączących obie instancje Active Record i zapisze je w bazie danych. W powyższym przykładzie 
atrybut `customer_id` instancji `Order` otrzyma wartość atrybutu `id` instancji `Customer`, a następnie zostanie zapisany w bazie danych.

> Note: Nie możesz łączyć w ten sposób dwóch świeżo utworzonych instancji Active Record.

Zaleta używania [[yii\db\ActiveRecord::link()|link()]] jest jeszcze bardziej widoczna, jeśli relacja jest zdefiniowana poprzez 
[tabelę węzła](#junction-table). Przykładowo możesz użyć następującego kodu, aby połączyć instancję `Order` z instancją `Item`:

```php
$order->link('items', $item);
```

Powyższy przykład automatycznie doda nowy wiersz w tabeli węzła `order_item`, aby połączyć zamówienie z produktem.

> Info: Metoda [[yii\db\ActiveRecord::link()|link()]] NIE wykona automatycznie żadnego procesu walidacji danych podczas zapisywania instancji Active Record. 
> Na Tobie spoczywa obowiązek walidacji wszystkich danych przed wywołaniem tej metody.

Odwrotną operacją do [[yii\db\ActiveRecord::link()|link()]] jest [[yii\db\ActiveRecord::unlink()|unlink()]], która usuwa istniejący związek pomiędzy 
dwoma instancjami Active Record. Przykładowo:

```php
$customer = Customer::find()->with('orders')->where(['id' => 123])->one();
$customer->unlink('orders', $customer->orders[0]);
```

Domyślnie metoda [[yii\db\ActiveRecord::unlink()|unlink()]] ustawia wartość klucza obcego (lub wielu kluczy obcych), który definiuje istniejącą relację, na `null`. 
Można jednak zamiast tego wybrać opcję usuwania wiersza tabeli, który zawiera klucz obcy, ustawiając w metodzie parametr `$delete` na `true`.
 
Jeśli w relacji użyty jest węzeł, wywołanie [[yii\db\ActiveRecord::unlink()|unlink()]] spowoduje wyczyszczenie kluczy obcych w tabeli węzła lub też 
usunięcie odpowiadających im wierszy, jeśli `$delete` jest ustawione na `true`.


## Relacje międzybazowe <span id="cross-database-relations"></span> 

Active Record pozwala na deklarowanie relacji pomiędzy klasami Active Record zasilanymi przez różne bazy danych.
Bazy danych mogę być różnych typów (np. MySQL i PostgreSQL lub MS SQL i MongoDB) i mogą pracować na różnych serwerach. 
Do wykonania relacyjnych zapytań używa się takich samych procedur, jak w przypadku relacji w obrębie jednej bazy danych. Przykład:

```php
// Customer jest powiązany z tabelą "customer" w relacyjnej bazie danych (np. MySQL)
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // klient posiada wiele komentarzy
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
    }
}

// Comment jest powiązany z kolekcją "comment" w bazie danych MongoDB
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // komentarz jest przypisany do jednego klienta
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}

$customers = Customer::find()->with('comments')->all();
```

Możesz używać większości funkcjonalności dostępnych dla relacyjnych kwerend opisanych w tym rozdziale. 
 
> Note: Użycie [[yii\db\ActiveQuery::joinWith()|joinWith()]] jest ograniczone do baz danych pozwalających na międzybazowe kwerendy JOIN, dlatego też 
> nie możesz użyć tej metody w powyższym przykładzie, ponieważ MongoDB nie wspiera instrukcji JOIN.


## Niestandardowe klasy kwerend <span id="customizing-query-classes"></span>

Domyślnie wszystkie kwerendy Active Record używają klasy [[yii\db\ActiveQuery|ActiveQuery]]. Aby użyć niestandardowej klasy kwerend razem z klasą Active Record, 
należy nadpisać metodę [[yii\db\ActiveRecord::find()|find()]], aby zwracała instancję żądanej klasy kwerend. Przykład:
 
```php
// plik Comment.php
namespace app\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}
```

Od tego momentu, za każdym razem, gdy wykonywana będzie kwerenda (np. `find()`, `findOne()`) lub pobierana relacja  (np. `hasOne()`) klasy `Comment`, 
praca będzie odbywać się na instancji `CommentQuery` zamiast `ActiveQuery`.

Teraz należy zdefiniować klasę `CommentQuery`, którą można dopasować do własnych kreatywnych potrzeb, dzięki czemu budowanie zapytań bazodanowych będzie o wiele bardziej ułatwione. Dla przykładu,

```php
// plik CommentQuery.php
namespace app\models;

use yii\db\ActiveQuery;

class CommentQuery extends ActiveQuery
{
    // dodatkowe warunki relacyjnej kwerendy dołączone jako domyślne (ten krok można pominąć)
    public function init()
    {
        $this->andOnCondition(['deleted' => false]);
        parent::init();
    }

    // ... dodaj zmodyfikowane metody kwerend w tym miejscu ...

    public function active($state = true)
    {
        return $this->andOnCondition(['active' => $state]);
    }
}
```

> Note: Zwykle, zamiast wywoływać metodę [[yii\db\ActiveQuery::onCondition()|onCondition()]], powinno się używać metody 
> [[yii\db\ActiveQuery::andOnCondition()|andOnCondition()]] lub [[yii\db\ActiveQuery::orOnCondition()|orOnCondition()]], aby dołączać kolejne warunki zapytania w 
> konstruktorze kwerend, dzięki czemu istniejące warunki nie zostaną nadpisane.

Powyższy przykład pozwala na użycie następującego kodu:
 
```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

> Tip: Dla dużych projektów rekomendowane jest, aby używać własnych, odpowiednio dopasowanych do potrzeb, klas kwerend, dzięki czemu klasy Active Record 
> pozostają przejrzyste.

Możesz także użyć nowych metod budowania kwerend przy definiowaniu relacji z `Comment` lub wykonywaniu relacyjnych kwerend:

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::className(), ['customer_id' => 'id'])->active();
    }
}

$customers = Customer::find()->joinWith('activeComments')->all();

// lub alternatywnie
class Customer extends \yii\db\ActiveRecord
{
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
    }
}

$customers = Customer::find()->joinWith([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

> Info: W Yii 1.1 do tego celu służy mechanizm *podzbiorów (scope)*, nie jest on jednak bezpośrednio wspierany w Yii 2.0, a zamiast tego 
> powinno się używać dopasowanych do własnych potrzeb klas kwerend.


## Pobieranie dodatkowych pól

W momencie, gdy instancja Active Record pobiera dane z wyniku kwerendy, wartości kolumn przypisywane są do odpowiadających im atrybutów.

Możliwe jest pobranie dodatkowych kolumn lub wartości za pomocą kwerendy i przypisanie ich w Active Record.
Przykładowo załóżmy, że mamy tabelę `room`, która zawiera informacje o pokojach dostępnych w hotelu. Każdy pokój przechowuje informacje na temat swojej 
wielkości za pomocą pól `length`, `width` i `height`.
Teraz wyobraźmy sobie, że potrzebujemy pobrać listę wszystkich pokojów posortowaną po ich kubaturze w malejącej kolejności.
Nie możemy obliczyć kubatury korzystając z PHP, ponieważ zależy nam na szybkim posortowaniu rekordów i dodatkowo chcemy wyświetlić pole `volume` na liście.
Aby osiągnąć ten cel, musimy zadeklarować dodatkowe pole w klasie `Room` rozszerzającej Active Record, które przechowa wartość `volume`:

```php
class Room extends \yii\db\ActiveRecord
{
    public $volume;

    // ...
}
```

Następnie należy skonstruować kwerendę, która obliczy kubaturę i wykona sortowanie:

```php
$rooms = Room::find()
    ->select([
        '{{room}}.*', // pobierz wszystkie kolumny
        '([[length]] * [[width]] * [[height]]) AS volume', // oblicz kubaturę
    ])
    ->orderBy('volume DESC') // posortuj
    ->all();

foreach ($rooms as $room) {
    echo $room->volume; // zawiera wartość obliczoną przez SQL
}
```

Możliwość pobrania dodatkowych pól jest szczególnie pomocna przy kwerendach agregujących.
Załóżmy, że potrzebujesz wyświetlić listę klientów wraz z liczbą zamówień, których dokonali.
Najpierw musisz zadeklarować klasę `Customer` wraz z relacją `orders` i dodatkowym polem przechowującym liczbę zamówień:

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

Teraz już możesz skonstruować kwerendę, która przyłączy zamówienia i policzy ich liczbę:

```php
$customers = Customer::find()
    ->select([
        '{{customer}}.*', // pobierz wszystkie kolumny klienta
        'COUNT({{order}}.id) AS ordersCount' // oblicz ilość zamówień
    ])
    ->joinWith('orders') // przyłącz tabelę węzła
    ->groupBy('{{customer}}.id') // pogrupuj wyniki dla funkcji agregacyjnej
    ->all();
```

Wadą tej metody jest to, że jeśli informacja nie może zostać pobrana za pomocą kwerendy SQL, musi ona być obliczona oddzielnie. 
Zatem po pobraniu konkretnego wiersza tabeli za pomocą regularnej kwerendy bez dodatkowej instrukcji select, niemożliwym będzie 
zwrócenie wartości dla dodatkowych pól. Tak samo stanie się w przypadku świeżo zapisanych rekordów.

```php
$room = new Room();
$room->length = 100;
$room->width = 50;
$room->height = 2;

$room->volume; // ta wartość będzie wynosić `null` ponieważ nie została jeszcze zadeklarowana
```

Używając magicznych metod [[yii\db\BaseActiveRecord::__get()|__get()]] i [[yii\db\BaseActiveRecord::__set()|__set()]], możemy emulować 
zachowania właściwości:

```php
class Room extends \yii\db\ActiveRecord
{
    private $_volume;
    
    public function setVolume($volume)
    {
        $this->_volume = (float) $volume;
    }
    
    public function getVolume()
    {
        if (empty($this->length) || empty($this->width) || empty($this->height)) {
            return null;
        }
        
        if ($this->_volume === null) {
            $this->setVolume(
                $this->length * $this->width * $this->height
            );
        }
        
        return $this->_volume;
    }

    // ...
}
```

Kiedy kwerenda nie zapewni wartości kubatury, model będzie w stanie automatycznie ją obliczyć, używając swoich atrybutów.

Możesz obliczyć sumaryczne pola rónież korzystając ze zdefiniowanych relacji:

```php
class Customer extends \yii\db\ActiveRecord
{
    private $_ordersCount;
    
    public function setOrdersCount($count)
    {
        $this->_ordersCount = (int) $count;
    }
    
    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; // dzięki temu unikamy wywołania kwerendy szukającej głównych kluczy o wartości null
        }
        
        if ($this->_ordersCount === null) {
            $this->setOrdersCount($this->getOrders()->count()); // oblicz sumę na żądanie z relacji
        }

        return $this->_ordersCount;
    }

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}
```

Dla powyższego kodu, kiedy 'ordersCount' występuje w instrukcji 'select' - `Customer::ordersCount` zostanie wypełnione 
rezultatem kwerendy, w pozostałych przypadkach zostanie obliczone na żądanie używając relacji `Customer::orders`.

Takie podejście może być równie dobrze użyte do stworzenia skrótów dla niektórych danych relacji, zwłąszcza tych służących do obliczania sumarycznego.
Przykładowo:

```php
class Customer extends \yii\db\ActiveRecord
{
    /**
     * Deklaracja wirtualnej właściwości tylko do odczytu dla danych sumarycznych.
     */
    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; // to pozwala na uniknięcie uruchamiania wyszukującej kwerendy dla pustych kluczy głównych
        }
        
        return empty($this->ordersAggregation) ? 0 : $this->ordersAggregation[0]['counted'];
    }

    /**
     * Deklaracja zwykłej relacji 'orders'.
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    /**
     * Deklaracja nowej relacji bazującej na 'orders', ale zapewniającej pobranie danych sumarycznych.
     */
    public function getOrdersAggregation()
    {
        return $this->getOrders()
            ->select(['customer_id', 'counted' => 'count(*)'])
            ->groupBy('customer_id')
            ->asArray(true);
    }

    // ...
}

foreach (Customer::find()->with('ordersAggregation')->all() as $customer) {
    echo $customer->ordersCount; // wyświetla dane sumaryczne z relacji bez dodatkowej kwerendy dzięki gorliwemu pobieraniu
}

$customer = Customer::findOne($pk);
$customer->ordersCount; // wyświetla dane sumaryczne z relacji pobranej leniwie
```
