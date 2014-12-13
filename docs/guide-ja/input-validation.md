入力を検証する
==============

大まかに言うなら、エンドユーザから受信したデータは決して信用せず、利用する前に検証しなければならない、ということです。

[モデル](structure-models.md) にユーザの入力が投入されたら、モデルの [[yii\base\Model::validate()]] メソッドを呼んで入力を検証することが出来ます。
このメソッドは検証が成功したか否かを示す真偽値を返します。
検証が失敗した場合は、[[yii\base\Model::errors]] プロパティからエラーメッセージを取得することが出来ます。
例えば、

```php
$model = new \app\models\ContactForm;

// モデルの属性にユーザ入力を投入する
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // 全ての入力が有効
} else {
    // 検証が失敗。$errors はエラーメッセージを含む配列
    $errors = $model->errors;
}
```

## 規則を宣言する <a name="declaring-rules"></a>

`validate()` を現実に動作させるためには、検証する予定の属性に対して検証規則を宣言しなければなりません。
規則は [[yii\base\Model::rules()]] メソッドをオーバーライドすることで宣言します。
次の例は、`ContactForm` モデルに対して検証規則を宣言する方法を示すものです。

```php
public function rules()
{
    return [
        // 名前、メールアドレス、主題、本文が必須項目
        [['name', 'email', 'subject', 'body'], 'required'],

        // email 属性は有効なメールアドレスでなければならない
        ['email', 'email'],
    ];
}
```

[[yii\base\Model::rules()|rules()]] メソッドは配列を返すべきものですが、配列の各要素は次の形式の配列でなければなりません。

```php
[
    // 必須。この規則によって検証されるべき属性を指定する。
    // 属性が一つだけの場合は、配列の中に入れずに、属性の名前を直接に書いてもよい。
    ['属性1', '属性2', ...],

    // 必須。この規則のタイプを指定する。
    // クラス名、バリデータのエイリアス、または、検証メソッドの名前。
    'バリデータ',

    // オプション。この規則が適用されるべき一つまたは複数のシナリオを指定する。
    // 指定しない場合は、この規則が全てのシナリオに適用されることを意味する。
    // "except" オプションを構成して、列挙したシナリオを除く全てのシナリオに
    // この規則が適用されるべきことを指定することも出来る。
    'on' => ['シナリオ1', 'シナリオ2', ...],

    // オプション。バリデータオブジェクトに対する追加の構成情報を指定する。
    'プロパティ1' => '値1', 'プロパティ2' => '値2', ...
]
```

各規則について、最低限、規則がどの属性に適用されるか、そして、規則がどのタイプであるかを指定しなければなりません。
規則のタイプは、次に挙げる形式のどれか一つを選ぶことが出来ます。

* コアバリデータのエイリアス。例えば、`required`、`in`、`date`、等々。
  コアバリデータの完全なリストは [コアバリデータ](tutorial-core-validators.md) を参照してください。
* モデルクラス内の検証メソッドの名前、または無名関数。詳細は、[インラインバリデータ](#inline-validators) の項を参照してください。
* 完全修飾のバリデータクラス名。詳細は [スタンドアロンバリデータ](#standalone-validators) の項を参照してください。

一つの規則は、一つまたは複数の属性を検証するために使用することが出来ます。
そして、一つの属性は、一つまたは複数の規則によって検証され得ます。
`on` オプションを指定することで、規則を特定の [シナリオ](structure-models.md#scenarios) においてのみ適用することが出来ます。
`on` オプションを指定しない場合は、規則が全てのシナリオに適用されることになります。

`validate()` メソッドが呼ばれると、次のステップを踏んで検証が実行されます。

1. 現在の [[yii\base\Model::scenario|シナリオ]] を使って [[yii\base\Model::scenarios()]] から属性のリストを取得し、どの属性が検証されるべきかを決定します。
   検証されるべき属性が *アクティブな属性* と呼ばれます。
2. 現在の [[yii\base\Model::scenario|シナリオ]] を使って [[yii\base\Model::rules()]] から規則のリストを取得し、どの検証規則が使用されるべきかを決定します。
   使用されるべき規則が *アクティブな規則* と呼ばれます。
3. 全てのアクティブな規則を一つずつ使って、その規則に関連付けられた全てのアクティブな属性を一つずつ検証します。
   検証規則はリストに挙げられている順に評価されます。

属性は、上記の検証ステップに従って、`scenarios()` でアクティブな属性であると宣言されており、かつ、`rules()` で宣言された一つまたは複数のアクティブな規則と関連付けられている場合に、また、その場合に限って、検証されます。


### エラーメッセージをカスタマイズする <a name="customizing-error-messages"></a>

たいていのバリデータはデフォルトのエラーメッセージを持っていて、属性の検証が失敗した場合にそれを検証対象のモデルに追加します。
例えば、[[yii\validators\RequiredValidator|required]] バリデータは、このバリデータを使って `username` 属性を検証したとき、規則に合致しない場合は「ユーザ名は空ではいけません。」というエラーメッセージをモデルに追加します。

規則のエラーメッセージは、次に示すように、規則を宣言するときに `message` プロパティを指定することによってカスタマイズすることが出来ます。

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'ユーザ名を選んでください。'],
    ];
}
```

バリデータの中には、検証を失敗させたさまざまな原因をより詳しく説明するための追加のエラーメッセージをサポートしているものがあります。
例えば、[[yii\validators\NumberValidator|number]] バリデータは、検証される値が大きすぎたり小さすぎたりしたときに検証の失敗を説明するために、それぞれ、[[yii\validators\NumberValidator::tooBig|tooBig]] および [[yii\validators\NumberValidator::tooSmall|tooSmall]] のメッセージをサポートしています。
これらのエラーメッセージも、バリデータの他のプロパティと同様、検証規則の中で構成することが出来ます。


### 検証のイベント <a name="validation-events"></a>

[[yii\base\Model::validate()]] は、呼び出されると、検証プロセスをカスタマイズするためにオーバーライドできる二つのメソッドを呼び出します。

* [[yii\base\Model::beforeValidate()]]: 既定の実装は [[yii\base\Model::EVENT_BEFORE_VALIDATE]] イベントをトリガするものです。
  このメソッドをオーバーライドするか、または、イベントに反応して、検証が実行される前に、何らかの前処理 (例えば入力されたデータの正規化) をすることが出来ます。
  このメソッドは、検証を続行すべきか否かを示す真偽値を返さなくてはなりません。
* [[yii\base\Model::afterValidate()]]: 既定の実装は [[yii\base\Model::EVENT_AFTER_VALIDATE]] イベントをトリガするものです。
  このメソッドをオーバーライドするか、または、イベントに反応して、検証が完了した後に、何らかの後処理をすることが出来ます。


### 条件付きの検証 <a name="conditional-validation"></a>

特定の条件が満たされる場合に限って属性を検証したい場合、例えば、ある属性の検証が他の属性の値に依存する場合には、[[yii\validators\Validator::when|when]] プロパティを使って、そのような条件を定義することが出来ます。
例えば、

```php
[
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }],
]
```

[[yii\validators\Validator::when|when]] プロパティは、次のシグニチャを持つ PHP コーラブルを値として取ります。

```php
/**
 * @param Model $model 検証されるモデル
 * @param string $attribute 検証される属性
 * @return boolean 規則が適用されるか否か
 */
function ($model, $attribute)
```

クライアント側でも条件付きの検証をサポートする必要がある場合は、[[yii\validators\Validator::whenClient|whenClient]] プロパティを構成しなければなりません。
このプロパティは、規則を適用すべきか否かを返す JavaScript 関数を表す文字列を値として取ります。
例えば、

```php
[
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').val() == 'USA';
    }"],
]
```


### データのフィルタリング <a name="data-filtering"></a>

ユーザ入力をフィルタまたは前処理する必要があることがよくあります。
例えば、`username` の入力値の前後にある空白を除去したいというような場合です。
この目的を達するために検証規則を使うことが出来ます。

次の例では、入力値の前後にある空白を除去して、空の入力値を null に変換することを、[trim](tutorial-core-validators.md#trim) および [default](tutorial-core-validators.md#default) のコアバリデータで行っています。

```php
[
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
]
```

もっと汎用的な [filter](tutorial-core-validators.md#filter) バリデータを使って、もっと複雑なデータフィルタリングをすることも出来ます。
お判りのように、これらの検証規則は実際には入力を検証しません。そうではなくて、検証される属性の値を処理して書き戻すのです。


### 空の入力値を扱う <a name="handling-empty-inputs"></a>

HTML フォームから入力データが送信されたとき、入力値が空である場合には何らかのデフォルト値を割り当てなければならないことがよくあります。
[default](tutorial-core-validators.md#default) バリデータを使ってそうすることが出来ます。
例えば、

```php
[
    // 空の時は "username" と "email" を null にする
    [['username', 'email'], 'default'],

    // 空の時は "level" を 1 にする
    ['level', 'default', 'value' => 1],
]
```

既定では、入力値が空であると見なされるのは、それが、空文字列であるか、空配列であるか、null であるときです。
空を検知するこのデフォルトのロジックは、[[yii\validators\Validator::isEmpty]] プロパティを PHP コーラブルで構成することによって、カスタマイズすることが出来ます。
例えば、

```php
[
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }],
]
```

> Note|注意: たいていのバリデータは、[[yii\base\Validator::skipOnEmpty]] プロパティがデフォルト値 `true` を取っている場合は、空の入力値を処理しません。
  そのようなバリデータは、関連付けられた属性が空の入力値を受け取ったときは、検証の過程ではスキップされるだけになります。
  [コアバリデータ](tutorial-core-validators.md) の中では、`captcha`、`default`、`filter`、`required`、そして `trim` だけが空の入力値を処理します。


## 臨時の検証 <a name="ad-hoc-validation"></a>

時として、何らかのモデルに結び付けられていない値に対する *臨時の検証* を実行しなければならない場合があります。

実行する必要がある検証が一種類 (例えば、メールアドレスの検証) だけである場合は、使いたいバリデータの [[yii\validators\Validator::validate()|validate()]] メソッドを次のように呼び出すことが出来ます。

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo 'メールアドレスは有効。';
} else {
    echo $error;
}
```

> Note|注意: 全てのバリデータがこの種の検証をサポートしている訳ではありません。
  その一例が [unique](tutorial-core-validators.md#unique) コアバリデータであり、これはモデルとともに使用されることだけを念頭にして設計されています。

いくつかの値に対して複数の検証を実行する必要がある場合は、属性と規則の両方をその場で宣言することが出来る [[yii\base\DynamicModel]] を使うことが出来ます。
これは、次のような使い方をします。

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
        [['name', 'email'], 'string', 'max' => 128],
        ['email', 'email'],
    ]);

    if ($model->hasErrors()) {
        // 検証が失敗
    } else {
        // 検証が成功
    }
}
```

[[yii\base\DynamicModel::validateData()]] メソッドは `DynamicModel` のインスタンスを作成し、与えられた値 (この例では `name` と `email`) を使って属性を定義し、そして、与えられた規則で [[yii\base\Model::validate()]] を呼び出します。

別の選択肢として、次のように、もっと「クラシック」な構文を使って、臨時のデータ検証を実行することも出来ます。

```php
public function actionSearch($name, $email)
{
    $model = new DynamicModel(compact('name', 'email'));
    $model->addRule(['name', 'email'], 'string', ['max' => 128])
        ->addRule('email', 'email')
        ->validate();

    if ($model->hasErrors()) {
        // 検証が失敗
    } else {
        // 検証が成功
    }
}
```

検証を実行した後は、通常のモデルで行うのと同様に、検証が成功したか否かを [[yii\base\DynamicModel::hasErrors()|hasErrors()]] メソッドを呼んでチェックして、[[yii\base\DynamicModel::errors|errors]] プロパティから検証エラーを取得することが出来ます。
また、このモデルのインスタンスによって定義された動的な属性に対しても、例えば `$model->name` や `$model->email` のようにして、アクセスすることが出来ます。


## バリデータを作成する <a name="creating-validators"></a>

Besides using the [core validators](tutorial-core-validators.md) included in the Yii releases, you may also
create your own validators. You may create inline validators or standalone validators.


### Inline Validators <a name="inline-validators"></a>

An inline validator is one defined in terms of a model method or an anonymous function. The signature of
the method/function is:

```php
/**
 * @param string $attribute the attribute currently being validated
 * @param array $params the additional name-value pairs given in the rule
 */
function ($attribute, $params)
```

If an attribute fails the validation, the method/function should call [[yii\base\Model::addError()]] to save
the error message in the model so that it can be retrieved back later to present to end users.

Below are some examples:

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // an inline validator defined as the model method validateCountry()
            ['country', 'validateCountry'],

            // an inline validator defined as an anonymous function
            ['token', function ($attribute, $params) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'The token must contain letters or digits.');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```

> Note: By default, inline validators will not be applied if their associated attributes receive empty inputs
  or if they have already failed some validation rules. If you want to make sure a rule is always applied,
  you may configure the [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] and/or [[yii\validators\Validator::skipOnError|skipOnError]]
  properties to be false in the rule declarations. For example:
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### Standalone Validators <a name="standalone-validators"></a>

A standalone validator is a class extending [[yii\validators\Validator]] or its child class. You may implement
its validation logic by overriding the [[yii\validators\Validator::validateAttribute()]] method. If an attribute
fails the validation, call [[yii\base\Model::addError()]] to save the error message in the model, like you do
with [inline validators](#inline-validators). For example,

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Web'])) {
            $this->addError($model, $attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```

If you want your validator to support validating a value without a model, you should also override
[[yii\validators\Validator::validate()]]. You may also override [[yii\validators\Validator::validateValue()]]
instead of `validateAttribute()` and `validate()` because by default the latter two methods are implemented
by calling `validateValue()`.


## Client-Side Validation <a name="client-side-validation"></a>

Client-side validation based on JavaScript is desirable when end users provide inputs via HTML forms, because
it allows users to find out input errors faster and thus provides a better user experience. You may use or implement
a validator that supports client-side validation *in addition to* server-side validation.

> Info: While client-side validation is desirable, it is not a must. Its main purpose is to provide users with a better
  experience. Similar to input data coming from end users, you should never trust client-side validation. For this reason,
  you should always perform server-side validation by calling [[yii\base\Model::validate()]], as
  described in the previous subsections.


### Using Client-Side Validation <a name="using-client-side-validation"></a>

Many [core validators](tutorial-core-validators.md) support client-side validation out-of-the-box. All you need to do
is just use [[yii\widgets\ActiveForm]] to build your HTML forms. For example, `LoginForm` below declares two
rules: one uses the [required](tutorial-core-validators.md#required) core validator which is supported on both
client and server sides; the other uses the `validatePassword` inline validator which is only supported on the server
side.

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
            // username and password are both required
            [['username', 'password'], 'required'],

            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }
}
```

The HTML form built by the following code contains two input fields `username` and `password`.
If you submit the form without entering anything, you will find the error messages requiring you
to enter something appear right away without any communication with the server.

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

Behind the scene, [[yii\widgets\ActiveForm]] will read the validation rules declared in the model
and generate appropriate JavaScript code for validators that support client-side validation. When a user
changes the value of an input field or submit the form, the client-side validation JavaScript will be triggered.

If you want to turn off client-side validation completely, you may configure the
[[yii\widgets\ActiveForm::enableClientValidation]] property to be false. You may also turn off client-side
validation of individual input fields by configuring their [[yii\widgets\ActiveField::enableClientValidation]]
property to be false.


### Implementing Client-Side Validation <a name="implementing-client-side-validation"></a>

To create a validator that supports client-side validation, you should implement the
[[yii\validators\Validator::clientValidateAttribute()]] method which returns a piece of JavaScript code
that performs the validation on the client side. Within the JavaScript code, you may use the following
predefined variables:

- `attribute`: the name of the attribute being validated.
- `value`: the value being validated.
- `messages`: an array used to hold the validation error messages for the attribute.
- `deferred`: an array which deferred objects can be pushed into (explained in the next subsection).

In the following example, we create a `StatusValidator` which validates if an input is a valid status input
against the existing status data. The validator supports both server side and client side validation.

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
if (!$.inArray(value, $statuses)) {
    messages.push($message);
}
JS;
    }
}
```

> Tip: The above code is given mainly to demonstrate how to support client-side validation. In practice,
> you may use the [in](tutorial-core-validators.md#in) core validator to achieve the same goal. You may
> write the validation rule like the following:
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

### Deferred Validation <a name="deferred-validation"></a>

If you need to perform asynchronous client-side validation, you can create [Deferred objects](http://api.jquery.com/category/deferred-object/).
For example, to perform a custom AJAX validation, you can use the following code:

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

In the above, the `deferred` variable is provided by Yii, which is an array of Deferred objects. The `$.get()`
jQuery method creates a Deferred object which is pushed to the `deferred` array.

You can also explicitly create a Deferred object and call its `resolve()` method when the asynchronous callback
is hit. The following example shows how to validate the dimensions of an uploaded image file on the client side.

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

> Note: The `resolve()` method must be called after the attribute has been validated. Otherwise the main form
  validation will not complete.

For simplicity, the `deferred` array is equipped with a shortcut method `add()` which automatically creates a Deferred
object and adds it to the `deferred` array. Using this method, you can simplify the above example as follows,

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


### AJAX Validation <a name="ajax-validation"></a>

Some validations can only be done on the server side, because only the server has the necessary information.
For example, to validate if a username is unique or not, it is necessary to check the user table on the server side.
You can use AJAX-based validation in this case. It will trigger an AJAX request in the background to validate the
input while keeping the same user experience as the regular client-side validation.

To enable AJAX validation for the whole form, you have to set the
[[yii\widgets\ActiveForm::enableAjaxValidation]] property to be `true` and specify `id` to be a unique form identifier:

```php
<?php $form = yii\widgets\ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]); ?>
```

You may also turn AJAX validation on or off for individual input fields by configuring their
[[yii\widgets\ActiveField::enableAjaxValidation]] property.

You also need to prepare the server so that it can handle the AJAX validation requests.
This can be achieved by a code snippet like the following in the controller actions:

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

The above code will check whether the current request is an AJAX. If yes, it will respond to
this request by running the validation and returning the errors in JSON format.

> Info: You can also use [Deferred Validation](#deferred-validation) to perform AJAX validation.
  However, the AJAX validation feature described here is more systematic and requires less coding effort.
