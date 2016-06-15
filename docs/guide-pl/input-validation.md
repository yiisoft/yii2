Walidacja danych wejściowych
================

Jedna z głównych zasad mówi, że nigdy nie powinno ufać się danym otrzymanym od użytkowników oraz zawsze walidować je przez użyciem.

Mamy [model](structure-models.md) wypełniony danymi od użytkownika. Możemy go zwalidować przez wywołanie metody [[yii\base\Model::validate()|validate()]].
Metoda zwróci wartość `boolean` wskazującą, czy walidacja się powiodła, czy nie. Jeśli nie, możesz pobrać informacje o błędach za pomocą właściwości [[yii\base\Model::errors|errors]].
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

Aby metoda [[yii\base\Model::validate()|validate()]] naprawdę zadziałała, powinieneś zadeklarować zasady walidacji dla atrybutów, które mają jej podlegać.
Powinno zostać to zrobione przez nadpisanie metody [[yii\base\Model::rules()|rules()]]. Poniższy przykład pokazuje jak zostały zadeklarowane zasady walidacji dla modelu `ContactForm`:

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

Metoda [[yii\base\Model::rules()|rules()]] powinna zwracać tablicę zasad, gdzie każda zasada jest tablicą w następującym formacie:

```php
[
    // wymagane, określa atrybut który powinien zostać zwalidowany przez tę zasadę.
    // Dla pojedyńczego atrybutu możemy użyć bezpośrednio jego nazwy, bez osadzania go w tablicy
    ['attribute1', 'attribute2', ...],

    // wymagane, określa typ tej zasady walidacji
    // Może to być nazwa klasy, alias walidatora lub nazwa metody walidacji
    'validator',

    // opcjonalny, określa, w którym scenariuszu/scenariuszach ta zasada powinna zostać użyta
    // w przypadku nie podania żadnego argumentu zasada zostanie zaaplikowana do wszystkich scenariuszy
    // Możesz również skonfigurować opcję "except" jeśli chcesz użyć tej zasady dla wszystkich scenariuszy. oprócz tych wymienionych przez Ciebie
    'on' => ['scenario1', 'scenario2', ...],

    // opcjonalny, określa dodatkowe konfiguracje do obiektu walidatora
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

Dla każdej z zasad musisz określić co najmniej jeden atrybut, którego ma ona dotyczyć, oraz należy określić typ tej zasady.
Możesz określić typ zasady jako jedną z następujących form:

* alias walidatora podstawowego, np. `required`, `in`, `date` itd. Zajrzyj do sekcji [Podstawowe walidatory](tutorial-core-validators.md) aby uzyskać pełną listę walidatorów podstawowych.
* nazwa metody walidacji w klasie modelu, lub funkcja anonimowa. Po więcej szczegółów zajrzyj do sekcji [Inline Validators](#inline-validators).
* pełna nazwa klasy walidatora. Po więcej szczegółów zajrzyj do sekcji [Standalone Validators](#standalone-validators).

Zasada może zostać użyta do walidacji jednego lub wielu atrybutów, a atrybut może być walidowany przez jedną, lub wiele zasad.
Zasada może zostać użyta dla konkretnych [scenariuszy](structure-models.md#scenarios) przez określenie opcji `on`.
Jeśli nie określisz opcji `on` oznacza to, że zasada zostanie użyta w każdym scenariuszu.

Kiedy zostaje wywołana metoda [[yii\base\Model::validate()|validate()]] zostają wykonane następujące kroki w celu wykonania walidacji:

1. Określenie które atrybuty powinny zostać zwalidowane przez pobranie listy atrybutów z [[yii\base\Model::scenarios()|scenarios()]] używając aktualnego 
   [[yii\base\Model::scenario|scenario]]. Wybrane atrybuty nazywane są *atrybutami aktywnymi*.
2. Określenie które zasady walidacji powinny zostać użyte przez pobranie listy zasad z [[yii\base\Model::rules()|rules()]] używając aktualnego [[yii\base\Model::scenario|scenario]]. 
   Wybrane zasady nazywane są *zasadami aktywnymi*.
3. Użycie każdej aktywnej zasady do walidacji każdego aktywnego atrybutu, który jest powiązany do konkretnej zasady. Zasady walidacji są wykonywane w kolejności w jakiej zostały zapisane.

Odnosząc się do powyższych kroków walidacji, atrybut zostanie zwalidowany wtedy i tylko wtedy, gdy jest on aktywnym atrybutem zadeklarowanym w 
[[yii\base\Model::scenarios()|scenarios()]] oraz jest powiązany z jedną lub wieloma aktywnymi zasadami zadeklarowanymi w [[yii\base\Model::rules()|rules()]].


### Dostosowywanie wiadomości błedów <span id="customizing-error-messages"></span>

Większość walidatorów posiada domyślne wiadomości błędów, które zostaną dodane do poddanego walidacji modelu, kiedy któryś z atrybutów nie przejdzie walidacji.
Dla przykładu, walidator [[yii\validators\RequiredValidator|required]] doda wiadomość "Username cannot be blank." do modelu, jeśli atrybut `username` nie przejdzie walidacji tej zasady.

Możesz dostosować wiadomość błędu danej zasady przez określenie właściwości `message` przy deklaracji zasady.
Dla przykładu,

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Proszę wybrać login.'],
    ];
}
```

Niektóre walidatory mogą wspierać dodatkowe wiadomości błedów, aby bardziej precyzyjnie określić problemy przy walidacji.
Dla przykładu, walidator [[yii\validators\NumberValidator|number]] dodaje [[yii\validators\NumberValidator::tooBig|tooBig]] oraz [[yii\validators\NumberValidator::tooSmall|tooSmall]] 
do opisania sytuacji, kiedy liczba jest za duża lub za mała podczas walidacji. Możesz skonfigurować te wiadomości tak, jak pozostałe właściwości walidatorów w zasadzie walidacji.


### Zdarzenia walidacji <span id="validation-events"></span>

Podczas wywołania metody [[yii\base\Model::validate()|validate()]] zostaną wywołane dwie metody, które możesz nadpisać, aby dostosować proces walidacji:

* [[yii\base\Model::beforeValidate()|beforeValidate()]]: domyślna implementacja wywoła zdarzenie [[yii\base\Model::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]]. Możesz nadpisać tę 
  metodę lub odnieść się do zdarzenia, aby wykonać dodatkowe operacje przed walidacją. Metoda powinna zwracać wartość `boolean` wskazującą, czy walidacja powinna zostać wykonana, czy nie.
* [[yii\base\Model::afterValidate()|afterValidate()]]: domyślna implementacja wywoła zdarzenie [[yii\base\Model::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]]. Możesz nadpisać tę metodę 
  lub odnieść się do zdarzenia, aby wykonać dodatkowe operacje po zakończonej walidacji.


### Walidacja warunkowa <span id="conditional-validation"></span>

Aby zwalidować atrybuty tylko wtedy, gdy zostaną spełnione pewne założenia, np. walidacja jednego atrybutu zależy od wartości drugiego atrybutu, możesz użyć właściwości
 [[yii\validators\Validator::when|when]] aby zdefiniować taki warunek. Dla przykładu,

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
 * @return boolean wartość zwrotna; czy reguła powinna zostać zastosowana
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
Aby osiągnąć ten cel, możesz użyć zasad walidacji.

Poniższy przykład pokazuje, jak wyciąć znaki spacji z pola oraz zmienić puste pole na wartość `NULL` przy użyciu podstawowych walidatorów [trim](tutorial-core-validators.md#trim) oraz 
[default](tutorial-core-validators.md#default):

```php
[
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
]
```

Możesz użyć również bardziej ogólnego walidatora [filter](tutorial-core-validators.md#filter), aby przeprowadzić bardziej złożone filtrowanie.

Jak pewnie zauważyłeś, te zasady walidacji tak naprawdę nie walidują danych. Zamiast tego przetwarzają wartości, a następnie przypisują je do atrybutów, które zostały poddane walidacji.


### Obsługa pustych danych wejściowych <span id="handling-empty-inputs"></span>

Kiedy dane wejściowe są wysłane przez formularz HTML, często zachodzi potrzeba przypisania im domyślnych wartości jeśli są puste.
Możesz to osiągnąć przez użycie walidatora [default](tutorial-core-validators.md#default). Dla przykładu,

```php
[
    // ustawia atrybuty "username" oraz "email" jako `NULL` jeśli są puste
    [['username', 'email'], 'default'],

    // ustawia atrybut "level" równy "1" jeśli jest pusty
    ['level', 'default', 'value' => 1],
]
```

Domyślnie pole uważane jest za puste, jeśli jego wartość to pusty string, pusta tablica lub `NULL`.
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

Jeśli potrzebujesz wykonać tylko jeden typ walidacji (np. walidacja adresu email), możesz wywołać metodę [[yii\validators\Validator::validate()|validate()]] wybranego walidatora, tak 
jak poniżej:

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo 'Email is valid.';
} else {
    echo $error;
}
```

> Note: Nie każdy walidator wspiera tego typu walidację. Dla przykładu, podstawowy walidator [unique](tutorial-core-validators.md#unique) został zaprojektowany do pracy wyłącznie z 
> modelami.

Jeśli potrzebujesz przeprowadzić wielokrotne walidacje, możesz użyć [[yii\base\DynamicModel|DynamicModel]], który wspiera deklarację atrybutów oraz zasad walidacji "w locie".
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

Metoda [[yii\base\DynamicModel::validateData()|validateData()]] tworzy instancję `DynamicModel`, definiuje atrybuty używając przekazanych danych (`name` oraz `email` w tym przykładzie),
a następnie wywołuje metodę [[yii\base\Model::validate()|validate()]] z podanymi zasadami walidacji.

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

Po walidacji możesz sprawdzić, czy przebiegła ona poprawnie lub nie, przez wywołanie metody [[yii\base\DynamicModel::hasErrors()|hasErrors()]], 
a następnie pobrać błędy walidacji z właściwości [[yii\base\DynamicModel::errors|errors]], tak jak w normalnym modelu.
Możesz również uzyskać dostęp do dynamicznych atrybutów tej instancji, np. `$model->name` and `$model->email`.


## Tworzenie walidatorów <span id="creating-validators"></span>

Oprócz używania [podstawowych walidatorów](tutorial-core-validators.md) dołączonych do wydania Yii, możesz dodatkowo utworzyć własne: wbudowane lub niezależne.

### Walidatory wbudowane <span id="inline-validators"></span>

Wbudowany walidator jest zdefiniowaną w modelu metodą lub funkcją anonimową. Jej definicja jest następująca:

```php
/**
 * @param string $attribute atrybut podlegający walidacji
 * @param mixed $params wartość parametru podanego w zasadzie walidacji 
 */
function ($attribute, $params)
```

Jeśli atrybut nie przejdzie walidacji, metoda/funkcja powinna wywołać metodę [[yii\base\Model::addError()|addError()]] do zapisania wiadomości błędu w modelu,
aby mogła ona zostać później pobrana i zaprezentowana użytkownikowi.

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
            ['token', function ($attribute, $params) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'Token musi zawierać litery lub cyfry.');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'Wybrany kraj musi być jednym z: "USA", "Web".');
        }
    }
}
```

> Note: Domyślnie wbudowane walidatory nie zostaną zastosowane, jeśli ich powiązane atrybuty otrzymają puste wartości lub wcześniej nie przeszły którejś z zasad walidacji.
> Jeśli chcesz się upewnić, że zasada zawsze zostanie zastosowana, możesz skonfigurować właściwość [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] i/lub 
> [[yii\validators\Validator::skipOnError|skipOnError]] przypisując jej wartość `false` w deklaracji zasady walidacji. Dla przykładu:
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### Walidatory niezależne <span id="standalone-validators"></span>

Walidator niezależy jest klasą rozszerzającą [[yii\validators\Validator|Validator]] lub klasy po nim dziedziczące. 
Możesz zaimplementować jego logikę walidacji przez nadpisanie metody [[yii\validators\Validator::validateAttribute()|validateAttribute()]].
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

Poniżej znajduje się przykład, jak mógłbyś użyć powyższej klasy walidatora w swoim modelu.

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


## Walidacja po stronie klienta <span id="client-side-validation"></span>

Walidacja po stronie klienta bazująca na kodzie JavaScript jest wskazana, kiedy użytkownicy dostarczają dane przez formularz HTML, 
ponieważ pozwala na szybszą walidację błędów, a tym samym zapewnia lepszą ich obsługę dla użytkownika. Możesz użyć lub zaimplementować walidator, który wspiera walidację po stronie 
klienta *dodatkowo do* walidacji po stronie serwera.

> Info: Walidacja po stronie klienta nie jest wymagana. Głównym jej celem jest poprawa jakości korzystania z formularzy przez użytkowników.
> Podobnie do danych wejściowych pochodzących od użytkowników, nigdy nie powinieneś ufać walidacji po stronie klienta. Z tego powodu,
> powinieneś zawsze przeprowadzać walidację po stronie serwera wywołując metodę [[yii\base\Model::validate()|validate()]], tak jak zostało to opisane w poprzednich sekcjach.

### Używanie walidacji po stronie klienta <span id="using-client-side-validation"></span>

Wiele [podstawowych walidatorów](tutorial-core-validators.md) domyślnie wspiera walidację po stronie klienta. Wszystko, co musisz zrobić, to użyć widżetu 
[[yii\widgets\ActiveForm|ActiveForm]] do zbudowania formularza HTML. Dla przykładu, model `LoginForm` poniżej deklaruje dwie zasady: jedną, używającą podstawowego walidatora 
[required](tutorial-core-validators.md#required), który wspiera walidację po stronie klienta i serwera, oraz drugą, w której użyto walidatora wbudowanego `validatePassword`, który 
wspiera tylko walidację po stronie klienta.

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

Jeśli chcesz wyłączyć kompletnie walidację po stronie klienta, możesz ustawić właściwość [[yii\widgets\ActiveForm::enableClientValidation|enableClientValidation]] na `false`.
Możesz również wyłączyć ten rodzaj walidacji dla konkretnego pola, przez ustawienie jego właściwości 
[[yii\widgets\ActiveField::enableClientValidation|enableClientValidation]] na `false`. Jeśli właściwość `enableClientValidation` zostanie skonfigurowana na poziomie pola formularza i w 
samym formularzu jednocześnie, pierwszeństwo będzie miała opcja określona w formularzu.


### Implementacja walidacji po stronie klienta <span id="implementing-client-side-validation"></span>

Aby utworzyć walidator wspierający walidację po stronie klienta, powinieneś zaimplementować metodę [[yii\validators\Validator::clientValidateAttribute()|clientValidateAttribute()]],
która zwraca kod JavaScript, odpowiedzialny za przeprowadzenie walidacji. W kodzie JavaScript możesz użyć następujących predefiniowanych zmiennych:

- `attribute`: nazwa atrybutu podlegającego walidacji.
- `value`: wartość atrybutu podlegająca walidacji.
- `messages`: tablica używana do przechowywania wiadomości błędów dla danego atrybutu. 
- `deferred`: tablica, do której można dodać zakolejkowane obiekty (wyjaśnione w późniejszej podsekcji).

W poniższym przykładzie, tworzymy walidator `StatusValidator`, który sprawdza, czy wartość danego atrybutu jest wartością znajdującą się w zbiorze statusów w bazie danych.
Walidator wspiera obydwa typy walidacji: po stronie klienta oraz serwerową.

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

> Tip: Powyższy kod został podany głównie do zademonstrowania jak wspierać walidację po stronie klienta.
> W praktyce można użyć podstawowego walidatora [in](tutorial-core-validators.md#in), aby osiągnąć ten sam cel.
> Możesz napisać taką zasadę walidacji następująco:
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

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

W powyższym kodzie, zmienna `deferred` jest dostarczona przez Yii, która jest tablicą zakolejkowanych obiektów.
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

> Note: Metoda `resolve()` musi być wywołana po zwalidowaniu atrybutu. W przeciwnym razie walidacja formularza nie zostanie ukończona.

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

Aby uaktywnić walidację AJAX dla pojedyńczego pola formularza, ustaw właściwość [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]] na `true` oraz określ unikalne ID 
formularza: 

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


Musisz również przygotować serwer, aby mógł obsłużyć AJAXowe zapytanie o walidację. Możesz to osiągnąć przez następujący skrawek kodu w akcji kontrolera:

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

Powyższy kod sprawdzi, czy zapytanie zostało wysłane przy użyciu AJAXa. Jeśli tak, w odpowiedzi zwróci wynik walidacji w formacie JSON.

> Info: Możesz również użyć [walidacji kolejkowej](#deferred-validation) do wykonania walidacji AJAX, jednakże walidacja AJAXowa opisana w tej sekcji jest bardziej systematyczna i 
> wymaga mniej wysiłku przy kodowaniu.

Kiedy zarówno `enableClientValidation`, jak i `enableAjaxValidation` ustawione są na true, walidacja za pomocą AJAX zostanie uruchomiona dopiero po udanej 
walidacji po stronie klienta.
