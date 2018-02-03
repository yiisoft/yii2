Проверка входящих данных
================

Как правило, вы никогда не должны доверять данным, полученным от пользователей и всегда проверять их прежде, чем работать с ними и добавлять в базу данных.

Учитывая [модель](structure-models.md) данных которые должен заполнить пользователь, можно проверить эти данные на валидность воспользовавшись методом [[yii\base\Model::validate()]]. Метод возвращает логическое значение с результатом валидации ложь/истина. Если данные не валидны, ошибку можно получить воспользовавшись методом  [[yii\base\Model::errors]]. Рассмотрим пример:

```php
$model = new \app\models\ContactForm;

// заполняем модель пользовательскими данными
$model->load(\Yii::$app->request->post());
// аналогично следующей строке:
// $model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // все данные корректны
} else {
    // данные не корректны: $errors - массив содержащий сообщения об ошибках
    $errors = $model->errors;
}
```


## Правила проверки <span id="declaring-rules"></span>

Для того, чтобы  `validate()` действительно работал, нужно объявить правила проверки атрибутов.
Правила для проверки нужно указать в методе [[yii\base\Model::rules()]]. В следующем примере показано, как
правила для проверки модели `ContactForm`, нужно объявлять:

```php
public function rules()
{
    return [
        // атрибут required указывает, что name, email, subject, body обязательны для заполнения
        [['name', 'email', 'subject', 'body'], 'required'],

        // атрибут email указывает, что в переменной email должен быть корректный адрес электронной почты
        ['email', 'email'],
    ];
}
```

Метод [[yii\base\Model::rules()|rules()]] должен возвращать массив правил, каждое из которых является массивом в следующем формате:

```php
[
    // обязательный, указывает, какие атрибуты должны быть проверены по этому правилу.
    // Для одного атрибута, вы можете использовать имя атрибута не создавая массив
    ['attribute1', 'attribute2', ...],

    // обязательный, указывает тип правила.
    // Это может быть имя класса, псевдоним валидатора, или метод для проверки
    'validator',

    // необязательный, указывает, в каком случае(ях) это правило должно применяться
    // если не указан, это означает, что правило применяется ко всем сценариям
    // Вы также можете настроить "except" этот вариант применяет правило ко всем
    // сценариям кроме перечисленных
    'on' => ['scenario1', 'scenario2', ...],

    // необязательный, задает дополнительные конфигурации для объекта validator
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

Для каждого правила необходимо указать, по крайней мере, какие атрибуты относится к этому правилу и тип правила.
Вы можете указать тип правила в одном из следующих форматов:

* Псевдонимы основного валидатора, например `required`, `in`, `date` и другие. Пожалуйста, обратитесь к списку
  [Основных валидаторов](tutorial-core-validators.md) за более подробной информацией.
* Название метода проверки в модели класса, или анонимную функцию. Пожалуйста, обратитесь к разделу
  [Встроенных валидаторов](#inline-validators) за более подробной информацией.
* Полное имя класса валидатора. Пожалуйста, обратитесь к разделу [Автономных валидаторов](#standalone-validators)
  за более подробной информацией.

Правило может использоваться для проверки одного или нескольких атрибутов. Атрибут может быть проверен одним или несколькими правилами.
Правило может быть применено только к определенным [сценариям](structure-models.md#scenarios) указав свойство `on`.
Если вы не укажите свойство `on`, это означает, что правило будет применяться ко всем сценариям.

Когда вызывается  метод `validate()` для проверки, он выполняет следующие действия:

1. Определяет, какие атрибуты должны проверяться путем получения списка атрибутов от [[yii\base\Model::scenarios()]]
   используя текущий [[yii\base\Model::scenario|scenario]]. Эти атрибуты называются - *активными атрибутами*.
2. Определяет, какие правила проверки должны использоваться, получив список правил от [[yii\base\Model::rules()]]
   используя текущий [[yii\base\Model::scenario|scenario]]. Эти правила называются - *активными правилами*.
3. Каждое активное правило проверяет каждый активный атрибут, который ассоциируется с правилом.
   Правила проверки выполняются в том порядке, как они перечислены.

Согласно вышеизложенным пунктам, атрибут будет проверяться, если и только если он является
активным атрибутом, объявленным в `scenarios()` и связан с одним или несколькими активными правилами,
объявленными в `rules()`.

> Note: Правилам валидации полезно давать имена. Например:
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
> В случае наследования предыдущей модели, именованные правила можно модифицировать или удалить:
>
> ```php
> public function rules()
> {
>     $rules = parent::rules();
>     unset($rules['password']);
>     return $rules;
> }

### Настройка сообщений об ошибках <span id="customizing-error-messages"></span>

Большинство валидаторов имеют сообщения об ошибках по умолчанию, которые будут добавлены к модели когда его атрибуты не проходят проверку.
Например, [[yii\validators\RequiredValidator|required]] валидатор добавил к модели сообщение об ошибке "Имя пользователя не может быть пустым." когда атрибут `username` не удовлетворил правилу этого валидатора.

Вы можете настроить сообщение об ошибке для каждого правила, указав свойство `message` при объявлении правила, следующим образом:

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Please choose a username.'],
    ];
}
```

Некоторые валидаторы могут поддерживать дополнительные сообщения об ошибках, чтобы более точно описать причину ошибки.
Например, [[yii\validators\NumberValidator|number]] валидатор поддерживает
[[yii\validators\NumberValidator::tooBig|tooBig]] и [[yii\validators\NumberValidator::tooSmall|tooSmall]]
для описания ошибки валидации, когда проверяемое значение является слишком большим и слишком маленьким, соответственно.
Вы можете настроить эти сообщения об ошибках, как в настройках валидаторов, так и непосредственно в правилах проверки.


### События валидации <span id="validation-events"></span>

Когда вызывается метод [[yii\base\Model::validate()]] он инициализирует вызов двух методов,
которые можно переопределить, чтобы настроить процесс проверки:

* [[yii\base\Model::beforeValidate()]]: выполнение по умолчанию вызовет [[yii\base\Model::EVENT_BEFORE_VALIDATE]]
  событие. Вы можете переопределить этот метод, или обрабатывать это событие, чтобы сделать некоторую предобработку данных (например, форматирование входных данных), метод вызывается до начала валидации. Этот метод должен возвращать логическое значение, указывающее, следует ли продолжать проверку или нет.
* [[yii\base\Model::afterValidate()]]: выполнение по умолчанию вызовет  [[yii\base\Model::EVENT_AFTER_VALIDATE]]
  событие. Вы можете либо переопределить этот метод или обрабатывать это событие, чтобы сделать некоторую  постобработку данных(Например, отформатировать данные удобным для дальнейшей обработки образом), метод вызывается после валидации.

### Условные валидации <span id="conditional-validation"></span>

Для проверки атрибутов только при выполнении определенных условий, например если один атрибут зависит от значения другого атрибута можно использовать [[yii\validators\Validator::when|when]] свойство, чтобы определить такие условия. Например:

```php
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }],
```

Это свойство [[yii\validators\Validator::when|when]] принимает PHP callable функцию с следующим описанием:

```php
/**
 * @param Model $model модель используемая для проверки
 * @param string $attribute атрибут для проверки
 * @return bool следует ли применять правило
 */
function ($model, $attribute)
```

Если вам нужна поддержка условной проверки на стороне клиента, вы должны настроить свойство метода
[[yii\validators\Validator::whenClient|whenClient]] которое принимает строку, представляющую JavaScript
функцию, возвращаемое значение определяет, следует ли применять правило или нет. Например:

```php
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').val() == 'USA';
    }"]
```


### Фильтрация данных <span id="data-filtering"></span>

Пользователь часто вводит данные которые нужно предварительно отфильтровать или предварительно обработать(очистить).
Например, вы хотите обрезать пробелы вокруг `username`. Вы можете использовать правила валидации для
достижения этой цели.

В следующих примерах показано, как обрезать пробелы в входных данных и превратить пустые входные данные в `NULL`
с помощью [trim](tutorial-core-validators.md#trim) и указать значения по умолчанию с помощью свойства
[default](tutorial-core-validators.md#default) основного валидатора:

```php
return [
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
];
```

Вы также можете использовать более сложные фильтрации данных с помощью анонимной функции
 подробнее об этом [filter](tutorial-core-validators.md#filter).

Как видите, эти правила валидации на самом деле не проверяют входные данные. Вместо этого,
они будут обрабатывать значения и обратно возвращать результат работы. Фильтры по сути выполняют предобработку входящих данных.


### Обработка пустых входных данных <span id="handling-empty-inputs"></span>

Если входные данные представлены из HTML-формы, часто нужно присвоить некоторые значения
по умолчанию для входных данных, если они не заполнены. Вы можете сделать это с помощью
валидатора [default](tutorial-core-validators.md#default). Например:

```php
return [
    // установим "username" и "email" как NULL, если они пустые
    [['username', 'email'], 'default'],

    // установим "level" как 1 если он пустой
    ['level', 'default', 'value' => 1],
];
```

По умолчанию входные данные считаются пустыми, если их значением является пустая строка, пустой массив или `null`.
Вы можете настроить значение по умолчанию с помощью свойства [[yii\validators\Validator::isEmpty]]
используя анонимную функцию. Например:

```php
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }]
```

> Note: большинство валидаторов не обрабатывает пустые входные данные, если их
  [[yii\base\Validator::skipOnEmpty]] свойство принимает значение по умолчанию `true`.
  Они просто будут пропущены во время проверки, если связанные с ними атрибуты являются пустыми.
  Среди [основных валидаторов](tutorial-core-validators.md), только `captcha`, `default`, `filter`,
  `required`, и `trim` будут обрабатывать пустые входные данные.


## Специальная валидация <span id="ad-hoc-validation"></span>

Иногда вам нужно сделать специальную валидацию для значений, которые не связаны с какой-либо модели.

Если необходимо выполнить только один тип проверки (например, проверка адреса электронной почты),
вы можете вызвать метод [[yii\validators\Validator::validate()|validate()]] нужного валидатора.
Например:

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo 'Email is valid.';
} else {
    echo $error;
}
```

> Note: Не все валидаторы поддерживают такой тип проверки. Примером может служить
[unique](tutorial-core-validators.md#unique) валидатор, который предназначен для работы с моделью.

Если необходимо выполнить несколько проверок в отношении нескольких значений,
вы можете использовать [[yii\base\DynamicModel]], который поддерживает объявление, как
атрибутов так и правил "на лету". Его использование выглядит следующим образом:

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
        [['name', 'email'], 'string', 'max' => 128],
        ['email', 'email'],
    ]);

    if ($model->hasErrors()) {
        // валидация завершилась с ошибкой
    } else {
        // Валидация успешно выполнена
    }
}
```

Метод [[yii\base\DynamicModel::validateData()]] создает экземпляр `DynamicModel`, определяет
атрибуты, используя приведенные данные (`name` и `email` в этом примере), и затем вызывает
[[yii\base\Model::validate()]]
с данными правилами.

Кроме того, вы можете использовать следующий "классический" синтаксис для выполнения специальной проверки данных:

```php
public function actionSearch($name, $email)
{
    $model = new DynamicModel(compact('name', 'email'));
    $model->addRule(['name', 'email'], 'string', ['max' => 128])
        ->addRule('email', 'email')
        ->validate();

    if ($model->hasErrors()) {
        // валидация завершилась с ошибкой
    } else {
        // Валидация успешно выполнена
    }
}
```
После валидации, вы можете проверить успешность выполнения вызвав
метод [[yii\base\DynamicModel::hasErrors()|hasErrors()]] и затем получить ошибки проверки вызвав
метод [[yii\base\DynamicModel::errors|errors]] как это делают нормальные модели.
Вы можете также получить доступ к динамическим атрибутам, определенным через экземпляр модели, например,
`$model->name` и `$model->email`.


## Создание Валидаторов <span id="creating-validators"></span>

Кроме того, используя [основные валидаторы](tutorial-core-validators.md), включенные в релизы Yii, вы также можете
создавать свои собственные валидаторы. Вы можете создавать встроенные валидаторы или автономные валидаторы.


### Встроенные Валидаторы <span id="inline-validators"></span>

Встроенный валидатор наследует методы модели или использует анонимную функцию.
Описание метода/функции:

```php
/**
 * @param string $attribute атрибут проверяемый в настоящее время
 * @param array $params дополнительные пары имя-значение, заданное в правиле
 */
function ($attribute, $params)
```

Если атрибут не прошел проверку, метод/функция должна вызвать [[yii\base\Model::addError()]],
чтобы сохранить сообщение об ошибке в модели, для того чтобы позже можно было получить сообщение об ошибке для
представления конечным пользователям.

Ниже приведены некоторые примеры:

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // встроенный валидатор определяется как модель метода validateCountry()
            ['country', 'validateCountry'],

            // встроенный валидатор определяется как анонимная функция
            ['token', function ($attribute, $params) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'Токен должен содержать буквы или цифры.');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Indonesia'])) {
            $this->addError($attribute, 'Страна должна быть либо "USA" или "Indonesia".');
        }
    }
}
```

> Note: по умолчанию, встроенные валидаторы не будут применяться, если связанные с ними атрибуты
получат пустые входные данные, или если они уже не смогли пройти некоторые правила валидации.
Если вы хотите, чтобы, это правило применялось всегда, вы можете настроить свойства
[[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] и/или [[yii\validators\Validator::skipOnError|skipOnError]]
свойства `false` в правиле объявления. Например:
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### Автономные валидаторы <span id="standalone-validators"></span>

Автономный валидатор - это класс, расширяющий [[yii\validators\Validator]] или его дочерних класс.
Вы можете реализовать свою логику проверки путем переопределения метода
[[yii\validators\Validator::validateAttribute()]]. Если атрибут не прошел проверку, вызвать
[[yii\base\Model::addError()]],
чтобы сохранить сообщение об ошибке в модели, как это делают [встроенные валидаторы](#inline-validators).

Валидация может быть помещена в отдельный класс [[components/validators/CountryValidator]]. В этом случае можно использовать метод  [[yii\validators\Validator::addError()]] для того, чтобы добавить своё сообщение об ошибке в модель:

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Indonesia'])) {
            $this->addError($model, $attribute, 'Страна должна быть либо "{country1}" либо "{country2}".', ['country1' => 'USA', 'country2' => 'Indonesia']);
        }
    }
}
```

Если вы хотите, чтобы ваш валидатор поддерживал проверку значений, без модели, также необходимо переопределить
[[yii\validators\Validator::validate()]]. Вы можете также
переопределить [[yii\validators\Validator::validateValue()]]
вместо `validateAttribute()` и `validate()`, потому что по умолчанию последние два метода
реализуются путем вызова `validateValue()`.


## Валидация на стороне клиента <span id="client-side-validation"></span>

Проверка на стороне клиента на основе JavaScript целесообразна, когда конечные пользователи вводят
входные данные через HTML-формы, так как эта проверка позволяет пользователям узнать, ошибки ввода
быстрее, и таким образом улучшает ваш пользовательский интерфейс. Вы можете использовать или
реализовать валидатор, который поддерживает валидацию на стороне клиента *в дополнение* к проверке на стороне сервера.

> Info: Проверка на стороне клиента желательна, но необязательна. Её основная цель заключается в
предоставлении пользователям более удобного интерфейса. Так как входные данные, поступают от конечных
пользователей, вы никогда не должны доверять верификации на стороне клиента. По этой причине, вы всегда
должны выполнять верификацию на стороне сервера путем вызова [[yii\base\Model::validate()]],
как описано в предыдущих пунктах.


### Использование валидации на стороне клиента <span id="using-client-side-validation"></span>

Многие [основные валидаторы](tutorial-core-validators.md) поддерживают проверку на стороне клиента out-of-the-box.
Все, что вам нужно сделать, это просто использовать [[yii\widgets\ActiveForm]] для построения HTML-форм.

Например, `LoginForm` ниже объявляет два правила: один использует [required](tutorial-core-validators.md#required)
основные валидаторы, который поддерживается на стороне клиента и сервера; другой использует `validatePassword`
встроенный валидатор, который поддерживается только на стороне сервера.

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
            // username и password обязательны для заполнения
            [['username', 'password'], 'required'],

            // проверке пароля с помощью validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Неправильное имя пользователя или пароль.');
        }
    }
}
```

HTML-форма построена с помощью следующего кода, содержит поля для ввода `username` и `password`.
Если вы отправите форму, не вводя ничего, вы получите сообщения об ошибках, требующих ввести данные.
Сообщения появятся сразу, без обращения к серверу.

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

Класс [[yii\widgets\ActiveForm]] будет читать правила проверки заявленные в модели и генерировать
соответствующий код JavaScript для валидаторов, которые поддерживают проверку на стороне клиента.
Когда пользователь изменяет значение поля ввода или отправляет форму, JavaScript на стороне клиента
будет срабатывать и проверять введенные данные.

Если вы хотите отключить проверку на стороне клиента полностью, вы можете настроить свойство
[[yii\widgets\ActiveForm::enableClientValidation]] установив значение `false`. Вы также можете отключить
проверку на стороне клиента отдельных полей ввода, настроив их с помощью свойства
[[yii\widgets\ActiveField::enableClientValidation]] установив значение `false`.


### Реализация проверки на стороне клиента <span id="implementing-client-side-validation"></span>

Чтобы создать валидатор, который поддерживает проверку на стороне клиента, вы должны реализовать метод
[[yii\validators\Validator::clientValidateAttribute()]] возвращающий фрагмент кода JavaScript,
который выполняет проверку на стороне клиента. В JavaScript-коде, вы можете использовать следующие предопределенные переменные:

- `attribute`: имя атрибута для проверки.
- `value`: проверяемое значение.
- `messages`: массив, используемый для хранения сообщений об ошибках, проверки значения атрибута.
- `deferred`: массив, который содержит отложенные объекты (описано в следующем подразделе).

 В следующем примере мы создаем `StatusValidator` который проверяет значение поля на соответствие допустимым статусам.
 Валидатор поддерживает оба способа проверки и на стороне сервера и на стороне клиента.

```php
namespace app\components;

use yii\validators\Validator;
use app\models\Status;

class StatusValidator extends Validator
{
    public function init()
    {
        parent::init();
        $this->message = 'Invalid status input.';
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

> Tip: приведенный выше код даётся, в основном, чтобы продемонстрировать, как осуществляется
> поддержка проверки на стороне клиента. На практике вы можете использовать
> [in](tutorial-core-validators.md#in) основные валидаторы для достижения той же цели.
> Вы можете написать проверку, как правило, например:
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

### Отложенная валидация <span id="deferred-validation"></span>

Если Вам необходимо выполнить асинхронную проверку на стороне клиента, вы можете создавать
[Deferred objects](http://api.jquery.com/category/deferred-object/). Например, чтобы выполнить
пользовательские AJAX проверки, вы можете использовать следующий код:

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

В примере выше переменная `deferred` предусмотренная Yii, которая является массивом Отложенных объектов.
`$.get()` метод jQuery создает Отложенный объект, который помещается в массив `deferred`.

Также можно явно создать Отложенный объект и вызвать его методом `resolve()`, тогда выполняется асинхронный
вызов к серверу. В следующем примере показано, как проверить размеры загружаемого файла изображения
на стороне клиента.

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        var def = $.Deferred();
        var img = new Image();
        img.onload = function() {
            if (this.width > 150) {
                messages.push('Изображение слишком широкое!');
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

> Note: метод `resolve()` должен быть вызван после того, как атрибут был проверен.
В противном случае основная проверка формы не будет завершена.

Для простоты работы с массивом `deferred`, существует упрощенный метод `add()`, который автоматически создает Отложенный объект и добавляет его в `deferred` массив. Используя этот метод, вы можете упростить пример выше, следующим образом:

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.add(function(def) {
            var img = new Image();
            img.onload = function() {
                if (this.width > 150) {
                    messages.push('Изображение слишком широкое!');
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


### AJAX валидация <span id="ajax-validation"></span>

Некоторые проверки можно сделать только на стороне сервера, потому что только сервер имеет необходимую информацию.
Например, чтобы проверить логин пользователя на уникальность, необходимо проверить логин в
базе данных на стороне сервера. Вы можете использовать проверку на основе AJAX в этом случае.
Это вызовет AJAX-запрос в фоновом режиме, чтобы проверить логин пользователя, сохраняя при этом валидацию
на стороне клиента. Выполняя её перед запросом к серверу.

Чтобы включить AJAX-валидацию для одного поля, Вы должны свойство [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]] выбрать как `true` и указать уникальный `id` формы:

```php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'registration-form',
]);

echo $form->field($model, 'username', ['enableAjaxValidation' => true]);

// ...

ActiveForm::end();
```

Чтобы включить AJAX-валидацию для всей формы, Вы должны свойство
[[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]] выбрать как `true` для формы:

```php
$form = yii\widgets\ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]);
```

> Note: В случае, если свойство `enableAjaxValidation` указано и у поля и у формы, первый вариант будет иметь приоритет.

Также необходимо подготовить сервер для обработки AJAX-запросов валидации. Это может быть достигнуто
с помощью следующего фрагмента кода, в контроллере действий:

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

Приведенный выше код будет проверять, является ли текущий запрос AJAX. Если да,
он будет отвечать на этот запрос, предварительно выполнив проверку и возвратит ошибки в
случае их появления в формате JSON.

> Info: Вы также можете использовать [Deferred Validation](#deferred-validation) AJAX валидации.
Однако, AJAX-функция проверки, описанная здесь более интегрированная и требует меньше усилий к написанию кода.
