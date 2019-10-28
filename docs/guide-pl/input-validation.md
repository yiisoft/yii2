Walidacja danych wejściowych
============================

Jedna z głównych zasad mówi, że nigdy nie należy ufać danym otrzymanym od użytkownika oraz że zawsze należy walidować je przed użyciem.

Rozważmy [model](structure-models.md) wypełniony danymi pobranymi od użytkownika. Możemy zweryfikować je poprzez wywołanie metody [[yii\base\Model::validate()|validate()]].
Metoda zwróci wartość `boolean` wskazującą, czy walidacja się powiodła, czy też nie. Jeśli nie, można pobrać informacje o błędach za pomocą właściwości
[[yii\base\Model::errors|errors]].
Dla przykładu,

```php
$model = new \app\models\ContactForm();

// uzupełniamy model danymi od użytkownika
$model->load(\Yii::$app->request->post());
// ten zapis jest tożsamy z poniższą metodą
// $model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // akcja w przypadku poprawnej walidacji
} else {
    // akcja w przypadku niepoprawnej walidacji. Zmienna $errors jest tablicą zawierającą wiadomości błędów
    $errors = $model->errors;
}
```


## Deklaracja zasad <span id="declaring-rules"></span>

Aby metoda [[yii\base\Model::validate()|validate()]] naprawdę zadziałała, należy zdefiniować zasady walidacji dla atrybutów, które mają jej podlegać.
Powinno zostać to zrobione przez nadpisanie metody [[yii\base\Model::rules()|rules()]]. Poniższy przykład pokazuje jak zostały zadeklarowane zasady walidacji dla modelu
`ContactForm`:

```php
public function rules()
{
    return [
        // atrybuty name, email, subject oraz body są wymagane
        [['name', 'email', 'subject', 'body'], 'required'],

        // atrybut email powinien być poprawnym adresem email
        ['email', 'email'],
    ];
}
```

Metoda [[yii\base\Model::rules()|rules()]] powinna zwracać tablicę zasad, gdzie każda zasada jest również tablicą o następującym formacie:

```php
[
    // wymagane, określa atrybut który powinien zostać zwalidowany przez tę zasadę.
    // Dla pojedyńczego atrybutu możemy użyć bezpośrednio jego nazwy, bez osadzania go w tablicy
    ['attribute1', 'attribute2', ...],

    // wymagane, określa rodzaj walidacji
    // Może to być nazwa klasy, alias walidatora lub nazwa metody walidacji
    'validator',

    // opcjonalne, określa, w którym scenariuszu/scenariuszach ta zasada powinna zostać użyta
    // w przypadku nie podania żadnego argumentu zasada zostanie zaaplikowana do wszystkich scenariuszy
    // Możesz również skonfigurować opcję "except", jeśli chcesz użyć tej zasady dla wszystkich scenariuszy, z wyjątkiem wymienionych
    'on' => ['scenario1', 'scenario2', ...],

    // opcjonalne, określa dodatkową konfigurację dla obiektu walidatora
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

Dla każdej z zasad musisz określić co najmniej jeden atrybut, którego ma ona dotyczyć, oraz określić rodzaj zasady jako
jedną z następujących form:

* alias walidatora podstawowego, np. `required`, `in`, `date` itd. Zajrzyj do sekcji [Podstawowe walidatory](tutorial-core-validators.md),
  aby uzyskać pełną listę walidatorów podstawowych.
* nazwa metody walidacji w klasie modelu lub funkcja anonimowa. Po więcej szczegółów zajrzyj do sekcji [Walidatory wbudowane](#inline-validators).
* pełna nazwa klasy walidatora. Po więcej szczegółów zajrzyj do sekcji [Walidatory niezależne](#standalone-validators).

Zasada może zostać użyta do walidacji jednego lub wielu atrybutów, a atrybut może być walidowany przez jedną lub wiele zasad.
Zasada może zostać użyta dla konkretnych [scenariuszy](structure-models.md#scenarios) przez dodanie opcji `on`.
Jeśli nie dodasz opcji `on` oznacza to, że zasada zostanie użyta w każdym scenariuszu.

Wywołanie metody [[yii\base\Model::validate()|validate()]] powoduje podjęcie następujących kroków w celu wykonania walidacji:

1. Określenie, które atrybuty powinny zostać zweryfikowane poprzez pobranie ich listy z metody [[yii\base\Model::scenarios()|scenarios()]], używając aktualnego
   [[yii\base\Model::scenario|scenariusza]]. Wybrane atrybuty nazywane są *atrybutami aktywnymi*.
2. Określenie, które zasady walidacji powinny zostać użyte przez pobranie ich listy z metody [[yii\base\Model::rules()|rules()]], używając aktualnego
   [[yii\base\Model::scenario|scenariusza]]. Wybrane zasady nazywane są *zasadami aktywnymi*.
3. Użycie każdej aktywnej zasady do walidacji każdego aktywnego atrybutu, który jest powiązany z konkretną zasadą. Zasady walidacji są wykonywane w kolejności,
   w jakiej zostały zapisane.

Odnosząc się do powyższych kroków, atrybut zostanie zwalidowany wtedy i tylko wtedy, gdy jest on aktywnym atrybutem zadeklarowanym w
[[yii\base\Model::scenarios()|scenarios()]] oraz jest powiązany z jedną lub wieloma aktywnymi zasadami zadeklarowanymi w [[yii\base\Model::rules()|rules()]].

> Note: Czasem użyteczne jest nadanie nazwy zasadzie np.
>
> ```php
> public function rules()
> {
>     return [
>         // ...
>         'password' => [['password'], 'string', 'max' => 60],
>     ];
> }
> ```
>
> W modelu potomnym można to wykorzystać:
>
> ```php
> public function rules()
> {
>     $rules = parent::rules();
>     unset($rules['password']);
>     return $rules;
> }


### Dostosowywanie wiadomości błedów <span id="customizing-error-messages"></span>

Większość walidatorów posiada domyślne wiadomości błędów, które zostają dodane do poddanego walidacji modelu, kiedy któryś z atrybutów nie przejdzie pomyślnie walidacji.
Dla przykładu, walidator [[yii\validators\RequiredValidator|required]] dodaje komunikat "Username cannot be blank.", kiedy atrybut `username` nie przejdzie walidacji tej zasady.

Możesz dostosować wiadomość błędu zasady przez określenie właściwości `message` podczas jej deklaracji.
Dla przykładu,

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Proszę wybrać login.'],
    ];
}
```

Niektóre walidatory mogą wspierać dodatkowe wiadomości błedów, aby bardziej precyzyjnie określić problemy powstałe przy walidacji.
Dla przykładu, walidator [[yii\validators\NumberValidator|number]] dodaje [[yii\validators\NumberValidator::tooBig|tooBig]] oraz
[[yii\validators\NumberValidator::tooSmall|tooSmall]] do opisania sytuacji, kiedy poddawana walidacji liczba jest za duża lub za mała.
Możesz skonfigurować te wiadomości tak, jak pozostałe właściwości walidatorów podczas deklaracji zasady.


### Zdarzenia walidacji <span id="validation-events"></span>

Podczas wywołania metody [[yii\base\Model::validate()|validate()]] zostaną wywołane dwie metody, które możesz nadpisać, aby dostosować proces walidacji:

* [[yii\base\Model::beforeValidate()|beforeValidate()]]: domyślna implementacja wywoła zdarzenie [[yii\base\Model::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]].
  Możesz nadpisać tę metodę lub odnieść się do zdarzenia, aby wykonać dodatkowe operacje przed walidacją.
  Metoda powinna zwracać wartość `boolean` wskazującą, czy walidacja powinna zostać przeprowadzona, czy też nie.
* [[yii\base\Model::afterValidate()|afterValidate()]]: domyślna implementacja wywoła zdarzenie [[yii\base\Model::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]].
  Możesz nadpisać tę metodę lub odnieść się do zdarzenia, aby wykonać dodatkowe operacje po zakończonej walidacji.


### Walidacja warunkowa <span id="conditional-validation"></span>

Aby zwalidować atrybuty tylko wtedy, gdy zostaną spełnione pewne założenia, np. walidacja jednego atrybutu zależy od wartości drugiego atrybutu, możesz użyć właściwości
 [[yii\validators\Validator::when|when]], aby zdefiniować taki warunek. Dla przykładu,

```php
[
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }],
]
```

Właściwość [[yii\validators\Validator::when|when]] pobiera możliwą do wywołania funkcję PHP z następującą definicją:

```php
/**
 * @param Model $model model, który podlega walidacji
 * @param string $attribute atrybut, który podlega walidacji
 * @return bool wartość zwrotna; czy reguła powinna zostać zastosowana
 */
function ($model, $attribute)
```

Jeśli potrzebujesz również wsparcia walidacji warunkowej po stronie użytkownika, powinieneś skonfigurować właściwość [[yii\validators\Validator::whenClient|whenClient]],
która przyjmuje wartość `string` reprezentującą funkcję JavaScript, zwracającą wartość `boolean`, która będzie określała, czy zasada powinna zostać zastosowana, czy nie.
Dla przykładu,

```php
[
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').val() == 'USA';
    }"],
]
```


### Filtrowanie danych <span id="data-filtering"></span>

Dane od użytkownika często muszą zostać przefiltrowane. Dla przykładu, możesz chcieć wyciąć znaki spacji na początku i na końcu pola `username`.
Aby osiągnąć ten cel, możesz również użyć zasad walidacji.

Poniższy przykład pokazuje, jak wyciąć znaki spacji z pola oraz zmienić puste pole na wartość `null` przy użyciu podstawowych walidatorów
[trim](tutorial-core-validators.md#trim) oraz [default](tutorial-core-validators.md#default):

```php
[
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
]
```

Możesz użyć również bardziej ogólnego walidatora [filter](tutorial-core-validators.md#filter), aby przeprowadzić złożone filtrowanie.

Jak pewnie zauważyłeś, te zasady walidacji tak naprawdę nie walidują danych. Zamiast tego przetwarzają wartości, a następnie przypisują je do atrybutów,
które zostały poddane walidacji.


### Obsługa pustych danych wejściowych <span id="handling-empty-inputs"></span>

Kiedy dane wejściowe są wysłane przez formularz HTML, często zachodzi potrzeba przypisania im domyślnych wartości, jeśli są puste.
Możesz to osiągnąć przez użycie walidatora [default](tutorial-core-validators.md#default). Dla przykładu,

```php
[
    // ustawia atrybuty "username" oraz "email" jako `null` jeśli są puste
    [['username', 'email'], 'default'],

    // ustawia atrybut "level" równy "1", jeśli jest pusty
    ['level', 'default', 'value' => 1],
]
```

Domyślnie pole uważane jest za puste, jeśli jego wartość to pusty łańcuch znaków, pusta tablica lub `null`.
Możesz dostosować domyślną logikę wykrywania pustych pól przez skonfigurowanie parametru [[yii\validators\Validator::isEmpty|isEmpty]], przekazując mu funkcję PHP.
Dla przykładu,

```php
[
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }],
]
```

> Note: Większość walidatorów nie obsługuje pustych pól, jeśli ich właściwość [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] przyjmuje domyślnie wartość `true`.
> Zostaną one po prostu pominięte podczas walidacji, jeśli ich powiązany atrybut otrzyma wartość uznawaną za pustą.
> Wśród [podstawowych walidatorów](tutorial-core-validators.md), tylko walidatory `captcha`, `default`, `filter`, `required` oraz `trim` obsługują puste pola.

## Walidacja "Ad Hoc" <span id="ad-hoc-validation"></span>

Czasami potrzebna będzie walidacja *ad hoc* dla wartości które nie są powiązane z żadnym modelem.

Jeśli potrzebujesz wykonać tylko jeden typ walidacji (np. walidację adresu email), możesz wywołać metodę
[[yii\validators\Validator::validate()|validate()]] wybranego walidatora, tak jak poniżej:

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo 'Email is valid.';
} else {
    echo $error;
}
```

> Note: Nie każdy walidator wspiera tego typu walidację. Dla przykładu, podstawowy walidator [unique](tutorial-core-validators.md#unique)
> został zaprojektowany do pracy wyłącznie z modelami.

Jeśli potrzebujesz przeprowadzić wielokrotne walidacje, możesz użyć modelu [[yii\base\DynamicModel|DynamicModel]],
który wspiera deklarację atrybutów oraz zasad walidacji "w locie".
Dla przykładu,

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
        [['name', 'email'], 'string', 'max' => 128],
        ['email', 'email'],
    ]);

    if ($model->hasErrors()) {
        // validation fails
    } else {
        // validation succeeds
    }
}
```

Metoda [[yii\base\DynamicModel::validateData()|validateData()]] tworzy instancję `DynamicModel`, definiuje atrybuty używając przekazanych danych
(`name` oraz `email` w tym przykładzie), a następnie wywołuje metodę [[yii\base\Model::validate()|validate()]] z podanymi zasadami walidacji.

Alternatywnie, możesz użyć bardziej "klasycznego" zapisu to przeprowadzenia tego typu walidacji:

```php
public function actionSearch($name, $email)
{
    $model = new DynamicModel(compact('name', 'email'));
    $model->addRule(['name', 'email'], 'string', ['max' => 128])
        ->addRule('email', 'email')
        ->validate();

    if ($model->hasErrors()) {
        // validation fails
    } else {
        // validation succeeds
    }
}
```

Po walidacji możesz sprawdzić, czy przebiegła ona poprawnie, poprzez wywołanie metody [[yii\base\DynamicModel::hasErrors()|hasErrors()]],
a następnie pobrać błędy walidacji z właściwości [[yii\base\DynamicModel::errors|errors]], tak jak w przypadku zwykłego modelu.
Możesz również uzyskać dostęp do dynamicznych atrybutów tej instancji, np. `$model->name` i `$model->email`.


## Tworzenie walidatorów <span id="creating-validators"></span>

Oprócz używania [podstawowych walidatorów](tutorial-core-validators.md) dołączonych do wydania Yii, możesz dodatkowo utworzyć własne; wbudowane lub niezależne.

### Walidatory wbudowane <span id="inline-validators"></span>

Wbudowany walidator jest zdefiniowaną w modelu metodą lub funkcją anonimową. Jej definicja jest następująca:

```php
/**
 * @param string $attribute atrybut podlegający walidacji
 * @param mixed $params wartość parametru podanego w zasadzie walidacji
 * @param \yii\validators\InlineValidator $validator powiązana instancja InlineValidator
 * Ten parametr jest dostępny od wersji 2.0.11.
 */
function ($attribute, $params, $validator)
```

Jeśli atrybut nie przejdzie walidacji, metoda/funkcja powinna wywołać metodę [[yii\base\Model::addError()|addError()]] do zapisania wiadomości o błędzie w modelu,
która może zostać później pobrana i zaprezentowana użytkownikowi.

Poniżej znajduje się kilka przykładów:

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // Wbudowany walidator zdefiniowany jako metoda validateCountry() w modelu
            ['country', 'validateCountry'],

            // Wbudowany walidator zdefiniowany jako funkcja anonimowa
            ['token', function ($attribute, $params, $validator) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'Token musi zawierać litery lub cyfry.');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params, $validator)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'Wybrany kraj musi być jednym z: "USA", "Web".');
        }
    }
}
```

> Note: Począwszy od wersji 2.0.11 możesz użyć [[yii\validators\InlineValidator::addError()]], aby dodać błędy bezpośrednio. W tym sposobie treść błędu 
> może być sformatowana bezpośrednio za pomocą [[yii\i18n\I18N::format()]]. Użyj `{attribute}` i `{value}` w treści błędu, aby odwołać się odpowiednio 
> do etykiety atrybutu (bez konieczności pobierania jej ręcznie) i wartości atrybutu:
>
> ```php
> $validator->addError($this, $attribute, 'Wartość "{value}" nie jest poprawna dla {attribute}.');
> ```

> Note: Domyślnie wbudowane walidatory nie zostaną zastosowane, jeśli ich powiązane atrybuty otrzymają puste wartości lub wcześniej nie przeszły którejś z zasad walidacji.
> Jeśli chcesz się upewnić, że zasada zawsze zostanie zastosowana, możesz skonfigurować właściwość [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] i/lub
> [[yii\validators\Validator::skipOnError|skipOnError]], przypisując jej wartość `false` w deklaracji zasady walidacji. Dla przykładu:
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### Walidatory niezależne <span id="standalone-validators"></span>

Walidator niezależy jest klasą rozszerzającą [[yii\validators\Validator|Validator]] lub klasy po nim dziedziczące.
Możesz zaimplementować jego logikę walidacji poprzez nadpisanie metody [[yii\validators\Validator::validateAttribute()|validateAttribute()]].
Jeśli atrybut nie przejdzie walidacji, wywołaj metodę [[yii\base\Model::addError()|addError()]] do zapisania wiadomości błędu w modelu, tak jak w
[walidatorach wbudowanych](#inline-validators).


Dla przykładu, poprzedni wbudowany walidator mógłby zostać przeniesiony do nowej klasy `components/validators/CountryValidator`.

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Web'])) {
            $this->addError($model, $attribute, 'Wybrany kraj musi być jednym z: "USA", "Web".');
        }
    }
}
```

Jeśli chcesz, aby walidator wspierał walidację wartości bez modelu, powinieneś nadpisać metodę [[yii\validators\Validator::validate()|validate()]].
Możesz nadpisać także [[yii\validators\Validator::validateValue()|validateValue()]] zamiast `validateAttribute()` oraz `validate()`,
ponieważ domyślnie te dwie metody są implementowane użyciem metody `validateValue()`.

Poniżej znajduje się przykład użycia powyższej klasy walidatora w modelu.

```php
namespace app\models;

use Yii;
use yii\base\Model;
use app\components\validators\CountryValidator;

class EntryForm extends Model
{
    public $name;
    public $email;
    public $country;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['country', CountryValidator::className()],
            ['email', 'email'],
        ];
    }
}
```

## Walidacja wielu atrybutów na raz <span id="multiple-attributes-validation"></span>

Zdarza się, że walidatory sprawdzają wiele atrybutów jednocześnie. Rozważmy następujący formularz:

```php
class MigrationForm extends \yii\base\Model
{
    /**
     * Kwota minimalnych funduszy dla jednej dorosłej osoby
     */
    const MIN_ADULT_FUNDS = 3000;
    /**
     * Kwota minimalnych funduszy dla jednego dziecka
     */
    const MIN_CHILD_FUNDS = 1500;

    public $personalSalary;
    public $spouseSalary;
    public $childrenCount;
    public $description;

    public function rules()
    {
        return [
            [['personalSalary', 'description'], 'required'],
            [['personalSalary', 'spouseSalary'], 'integer', 'min' => self::MIN_ADULT_FUNDS],
            ['childrenCount', 'integer', 'min' => 0, 'max' => 5],
            [['spouseSalary', 'childrenCount'], 'default', 'value' => 0],
            ['description', 'string'],
        ];
    }
}
```

### Tworzenie walidatora <span id="multiple-attributes-validator"></span>

Powiedzmy, że chcemy sprawdzić, czy dochód rodziny jest wystarczający do utrzymania dzieci. W tym celu możemy utworzyć wbudowany walidator
`validateChildrenFunds`, który będzie uruchamiany tylko jeśli `childrenCount` będzie większe niż 0.

Zwróć uwagę na to, że nie możemy użyć wszystkich walidowanych atrybutów (`['personalSalary', 'spouseSalary', 'childrenCount']`) przy dołączaniu walidatora.
Wynika to z tego, że ten sam walidator będzie uruchomiony dla każdego z atrybutów oddzielnie (łącznie 3 razy), a musimy użyć go tylko raz dla całego zestawu atrybutów.

Możesz użyć dowolnego z tych atrybutów zamiast podanego poniżej (lub też tego, który uważasz za najbardziej tu odpowiedni):

```php
['childrenCount', 'validateChildrenFunds', 'when' => function ($model) {
    return $model->childrenCount > 0;
}],
```

Implementacja `validateChildrenFunds` może wyglądać następująco:

```php
public function validateChildrenFunds($attribute, $params)
{
    $totalSalary = $this->personalSalary + $this->spouseSalary;
    // Podwój minimalny fundusz dorosłych, jeśli ustalono zarobki współmałżonka
    $minAdultFunds = $this->spouseSalary ? self::MIN_ADULT_FUNDS * 2 : self::MIN_ADULT_FUNDS;
    $childFunds = $totalSalary - $minAdultFunds;
    if ($childFunds / $this->childrenCount < self::MIN_CHILD_FUNDS) {
        $this->addError('childrenCount', 'Twoje zarobki nie są wystarczające, aby utrzymać dzieci.');
    }
}
```

Możesz zignorować parametr `$attribute`, ponieważ walidacja nie jest powiązana bezpośrednio tylko z jednym atrybutem.


### Dodawanie informacji o błędach <span id="multiple-attributes-errors"></span>

Dodawanie błędów walidacji w przypadku wielu atrybutów może różnić się w zależności od ustalonej metodyki pracy z formularzami:

- Można wybrać najbardziej w naszej opinii pole i dodać błąd do jego atrybutu:

```php
$this->addError('childrenCount', 'Twoje zarobki nie są wystarczające dla potrzeb dzieci.');
```

- Można wybrać wiele ważnych odpowiednich atrybutów lub też wszystkie i dodać ten sam błąd do każdego z nich. Możemy przechować 
treść w oddzielnej zmiennej przed przekazaniem jej do `addError`, aby nie powtarzać się w kodzie (zasada DRY - Don't Repeat Yourself).

```php
$message = 'Twoje zarobki nie są wystarczające dla potrzeb dzieci.';
$this->addError('personalSalary', $message);
$this->addError('wifeSalary', $message);
$this->addError('childrenCount', $message);
```

Lub też użyć pętli:

```php
$attributes = ['personalSalary, 'wifeSalary', 'childrenCount'];
foreach ($attributes as $attribute) {
    $this->addError($attribute, 'Twoje zarobki nie są wystarczające dla potrzeb dzieci.');
}
```

- Można też dodać ogólny błąd (niepowiązany z żadnym szczególnym atrybutem). Do tego celu możemy wykorzystać nazwę nieistniejącego atrybutu, 
na przykład `*`, ponieważ to, czy atrybut istnieje, nie jest sprawdzane w tym kroku.

```php
$this->addError('*', 'Twoje zarobki nie są wystarczające dla potrzeb dzieci.');
```

W rezultacie takiej operacji nie zobaczymy błędu zaraz obok pól formularza. Aby go wyświetlić, możemy dodać do widoku podsumowanie błędów formularza:

```php
<?= $form->errorSummary($model) ?>
```

> Note: Tworzenie walidatora operującego na wielu atrybutach jednocześnie jest dobrze opisane w [książce kucharskiej społeczności Yii](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-validator-multiple-attributes.md).


## Walidacja po stronie klienta <span id="client-side-validation"></span>

Walidacja po stronie klienta, bazująca na kodzie JavaScript jest wskazana, kiedy użytkownicy dostarczają dane przez formularz HTML,
ponieważ pozwala na szybszą walidację błędów, a tym samym zapewnia lepszą ich obsługę dla użytkownika. Możesz użyć lub zaimplementować walidator,
który wspiera walidację po stronie klienta jako *dodatek* do walidacji po stronie serwera.

> Info: Walidacja po stronie klienta nie jest wymagana. Głównym jej celem jest poprawa jakości korzystania z formularzy dla użytkowników.
> Podobnie jak w przypadku danych wejściowych pochodzących od użytkowników, nigdy nie powinieneś ufać walidacji przeprowadanej po stronie klienta.
> Z tego powodu należy zawsze przeprowadzać główną walidację po stronie serwera wywołując metodę [[yii\base\Model::validate()|validate()]],
> tak jak zostało to opisane w poprzednich sekcjach.

### Używanie walidacji po stronie klienta <span id="using-client-side-validation"></span>

Wiele [podstawowych walidatorów](tutorial-core-validators.md) domyślnie wspiera walidację po stronie klienta. Wszystko, co musisz zrobić, to użyć widżetu
[[yii\widgets\ActiveForm|ActiveForm]] do zbudowania formularza HTML. Dla przykładu, model `LoginForm` poniżej deklaruje dwie zasady: jedną, używającą podstawowego walidatora
[required](tutorial-core-validators.md#required), który wspiera walidację po stronie klienta i serwera, oraz drugą, w której użyto walidatora wbudowanego `validatePassword`,
który wspiera tylko walidację po stronie serwera.

```php
namespace app\models;

use yii\base\Model;
use app\models\User;

class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // atrybuty username oraz password są wymagane
            [['username', 'password'], 'required'],

            // atrybut password jest walidowany przez validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Nieprawidłowa nazwa użytkownika lub hasło.');
        }
    }
}
```

Formularz HTML zbudowany przez następujący kod zawiera dwa pola: `username` oraz `password`.
Jeśli wyślesz formularz bez wpisywania jakichkolwiek danych, otrzymasz komunikaty błędów o ich braku, bez konieczności przeprowadzania komunikacji z serwerem.

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

"Za kulisami", widżet [[yii\widgets\ActiveForm|ActiveForm]] odczyta wszystkie zasady walidacji zadeklarowane w modelu i wygeneruje odpowiedni kod JavaScript
dla walidatorów wspierających walidację po stronie klienta. Kiedy użytkownik zmieni wartość w polu lub spróbuje wysłać formularz, zostanie wywołana walidacja po stronie klienta.

Jeśli chcesz wyłączyć całkowicie walidację po stronie klienta, możesz ustawić właściwość [[yii\widgets\ActiveForm::enableClientValidation|enableClientValidation]] na `false`.
Możesz również wyłączyć ten rodzaj walidacji dla konkretnego pola, przez ustawienie jego właściwości
[[yii\widgets\ActiveField::enableClientValidation|enableClientValidation]] na `false`. Jeśli właściwość `enableClientValidation` zostanie skonfigurowana na poziomie pola
formularza i w samym formularzu jednocześnie, pierwszeństwo będzie miała opcja określona w formularzu.

> Info: Od wersji 2.0.11 wszystkie walidatory rozszerzające klasę [[yii\validators\Validator]] używają opcji klienta przekazywanych 
> z oddzielnej metody - [[yii\validators\Validator::getClientOptions()]]. Możesz jej użyć:
>
> - jeśli chcesz zaimplementować swoją własną walidację po stronie klienta, ale pozostawić synchronizację z opcjami walidatora po stronie serwera;
> - do rozszerzenia lub zmodyfikowania dla uzyskania specjalnych korzyści:
>
> ```php
> public function getClientOptions($model, $attribute)
> {
>     $options = parent::getClientOptions($model, $attribute);
>     // Zmodyfikuj $options w tym miejscu
>
>     return $options;
> }
> ```


### Implementacja walidacji po stronie klienta <span id="implementing-client-side-validation"></span>

Aby utworzyć walidator wspierający walidację po stronie klienta, powinieneś zaimplementować metodę
[[yii\validators\Validator::clientValidateAttribute()|clientValidateAttribute()]], która zwraca kod JavaScript, odpowiedzialny za przeprowadzenie walidacji.
W kodzie JavaScript możesz użyć następujących predefiniowanych zmiennych:

- `attribute`: nazwa atrybutu podlegającego walidacji.
- `value`: wartość atrybutu podlegająca walidacji.
- `messages`: tablica używana do przechowywania wiadomości błędów dla danego atrybutu.
- `deferred`: tablica, do której można dodać zakolejkowane obiekty (wyjaśnione w późniejszej podsekcji).

W poniższym przykładzie, tworzymy walidator `StatusValidator`, który sprawdza, czy wartość danego atrybutu jest wartością znajdującą się na liście statusów w bazie danych.
Walidator wspiera obydwa typy walidacji; po stronie klienta oraz serwerową.

```php
namespace app\components;

use yii\validators\Validator;
use app\models\Status;

class StatusValidator extends Validator
{
    public function init()
    {
        parent::init();
        $this->message = 'Niepoprawna wartość pola status.';
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!Status::find()->where(['id' => $value])->exists()) {
            $model->addError($attribute, $this->message);
        }
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $statuses = json_encode(Status::find()->select('id')->asArray()->column());
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
if ($.inArray(value, $statuses) === -1) {
    messages.push($message);
}
JS;
    }
}
```

> Tip: Powyższy kod został podany głównie do zademonstrowania, jak wspierać walidację po stronie klienta.
> W praktyce można użyć podstawowego walidatora [in](tutorial-core-validators.md#in), aby osiągnąć ten sam cel.
> Możesz napisać taką zasadę walidacji następująco:
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

> Tip: Jeśli musisz dodać ręcznie walidację po stronie klienta np. podczas dynamicznego dodawania pól formularza lub przeprowadzania specjalnej logiki w obrębie interfejsu
> użytkownika, zapoznaj się z rozdziałem [Praca z ActiveForm za pomocą JavaScript](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-activeform-js.md)
> w Yii 2.0 Cookbook.

### Kolejkowa walidacja <span id="deferred-validation"></span>

Jeśli potrzebujesz przeprowadzić asynchroniczną walidację po stronie klienta, możesz utworzyć [obiekt kolejkujący](http://api.jquery.com/category/deferred-object/).
Dla przykładu, aby przeprowadzić niestandardową walidację AJAX, możesz użyć następującego kodu:

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.push($.get("/check", {value: value}).done(function(data) {
            if ('' !== data) {
                messages.push(data);
            }
        }));
JS;
}
```

W powyższym kodzie, zmienna `deferred` jest dostarczoną przez Yii tablicą zakolejkowanych obiektów.
Metoda jQuery `$.get()` tworzy obiekt kolejkowy, który jest dodawany do tablicy `deferred`.

Możesz także utworzyć osobny obiekt kolejkowania i wywołać jego metodę `resolve()` po otrzymaniu asynchronicznej informacji zwrotnej.
Poniższy przykład pokazuje, jak zwalidować wymiary przesłanego obrazka po stronie klienta.

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        var def = $.Deferred();
        var img = new Image();
        img.onload = function() {
            if (this.width > 150) {
                messages.push('Image too wide!!');
            }
            def.resolve();
        }
        var reader = new FileReader();
        reader.onloadend = function() {
            img.src = reader.result;
        }
        reader.readAsDataURL(file);

        deferred.push(def);
JS;
}
```

> Note: Metoda `resolve()` musi być wywołana po walidacji atrybutu. W przeciwnym razie główna walidacja formularza nie zostanie ukończona.

Dla uproszczenia, tablica `deferred` jest wyposażona w skrótową metodę `add()`, która automatycznie tworzy obiekt kolejkowy i dodaje go do tej tablicy.
Używając tej metody, możesz uprościć powyższy przykład:

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.add(function(def) {
            var img = new Image();
            img.onload = function() {
                if (this.width > 150) {
                    messages.push('Image too wide!!');
                }
                def.resolve();
            }
            var reader = new FileReader();
            reader.onloadend = function() {
                img.src = reader.result;
            }
            reader.readAsDataURL(file);
        });
JS;
}
```


### Walidacja przy użyciu AJAX <span id="ajax-validation"></span>

Niektóre walidacje mogą zostać wykonane tylko po stronie serwera, ponieważ tylko serwer posiada niezbędne informacje do ich przeprowadzenia.
Dla przykładu, aby sprawdzić, czy login został już zajęty, musimy sprawdzić tabelę użytkowników w bazie danych.
W tym właśnie przypadku możesz użyć walidacji AJAX. Wywoła ona żądanie AJAX w tle, aby spradzić to pole.

Aby uaktywnić walidację AJAX dla pojedyńczego pola formularza, ustaw właściwość [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]] na `true`
oraz zdefiniuj unikalne `id` formularza:

```php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'registration-form',
]);

echo $form->field($model, 'username', ['enableAjaxValidation' => true]);

// ...

ActiveForm::end();
```

Aby uaktywnić walidację AJAX dla całego formularza, ustaw właściwość [[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]] na `true` na poziomie formularza:

```php
$form = ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]);
```

> Note: Jeśli właściwość [[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]] zostanie skonfigurowana na poziomie pola formularza i jednocześnie w samym formularzu,
> pierwszeństwo będzie miała opcja określona w formularzu.


Musisz również przygotować serwer na obsłużenie AJAXowego zapytanie o walidację. Możesz to osiągnąć przez następujący fragment kodu w akcji kontrolera:

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

Powyższy kod sprawdzi, czy zapytanie zostało wysłane przy użyciu AJAXa. Jeśli tak, w odpowiedzi zwróci wynik walidacji w formacie JSON.

> Info: Możesz również użyć [walidacji kolejkowej](#deferred-validation) do wykonania walidacji AJAX,
> jednakże walidacja AJAXowa opisana w tej sekcji jest bardziej systematyczna i wymaga mniej wysiłku przy kodowaniu.

Kiedy zarówno `enableClientValidation`, jak i `enableAjaxValidation` ustawione są na `true`, walidacja za pomocą AJAX zostanie uruchomiona dopiero po udanej
walidacji po stronie klienta.
