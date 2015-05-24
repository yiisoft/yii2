入力を検証する
==============

経験則として言えることは、エンドユーザから受信したデータは決して信用せず、利用する前に検証しなければならない、ということです。

[モデル](structure-models.md) にユーザの入力が投入されたら、モデルの [[yii\base\Model::validate()]] メソッドを呼んで入力を検証することが出来ます。
このメソッドは検証が成功したか否かを示す真偽値を返します。
検証が失敗した場合は、[[yii\base\Model::errors]] プロパティからエラーメッセージを取得することが出来ます。
例えば、

```php
$model = new \app\models\ContactForm();

// モデルの属性にユーザ入力を投入する
$model->load(\Yii::$app->request->post());
// これは次と等価
// $model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // 全ての入力が有効
} else {
    // 検証が失敗。$errors はエラーメッセージを含む配列
    $errors = $model->errors;
}
```

## 規則を宣言する <span id="declaring-rules"></span>

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

[[yii\base\Model::rules()|rules()]] メソッドは規則の配列を返すべきものですが、その配列の各要素は次の形式の配列でなければなりません。

```php
[
    // 必須。この規則によって検証されるべき属性を指定する。
    // 属性が一つだけの場合は、配列の中に入れずに、属性の名前を直接に書いてもよい。
    ['属性1', '属性2', ...],

    // 必須。この規則のタイプを指定する。
    // クラス名、バリデータのエイリアス、または、バリデーションメソッドの名前。
    'バリデータ',

    // オプション。この規則が適用されるべき一つまたは複数のシナリオを指定する。
    // 指定しない場合は、この規則が全てのシナリオに適用されることを意味する。
    // "except" オプションを構成して、列挙したシナリオを除く全てのシナリオに
    // この規則が適用されるべきことを指定してもよい。
    'on' => ['シナリオ1', 'シナリオ2', ...],

    // オプション。バリデータオブジェクトに対する追加の構成情報を指定する。
    'プロパティ1' => '値1', 'プロパティ2' => '値2', ...
]
```

各規則について、最低限、規則がどの属性に適用されるか、そして、規則がどのタイプであるかを指定しなければなりません。
規則のタイプは、次に挙げる形式のどれか一つを選ぶことが出来ます。

* コアバリデータのエイリアス。例えば、`required`、`in`、`date`、等々。
  コアバリデータの完全なリストは [コアバリデータ](tutorial-core-validators.md) を参照してください。
* モデルクラス内のバリデーションメソッドの名前、または無名関数。詳細は、[インラインバリデータ](#inline-validators) の項を参照してください。
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

属性は、上記の検証のステップに従って、`scenarios()` でアクティブな属性であると宣言されており、かつ、`rules()` で宣言された一つまたは複数のアクティブな規則と関連付けられている場合に、また、その場合に限って、検証されます。


### エラーメッセージをカスタマイズする <span id="customizing-error-messages"></span>

たいていのバリデータはデフォルトのエラーメッセージを持っていて、属性の検証が失敗した場合にそれを検証の対象であるモデルに追加します。
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
例えば、[[yii\validators\NumberValidator|number]] バリデータは、検証される値が大きすぎたり小さすぎたりしたときに、検証の失敗を説明するために、それぞれ、[[yii\validators\NumberValidator::tooBig|tooBig]] および [[yii\validators\NumberValidator::tooSmall|tooSmall]] のメッセージをサポートしています。
これらのエラーメッセージも、バリデータの他のプロパティと同様、検証規則の中で構成することが出来ます。


### 検証のイベント <span id="validation-events"></span>

[[yii\base\Model::validate()]] は、呼び出されると、検証のプロセスをカスタマイズするためにオーバーライドできる二つのメソッドを呼び出します。

* [[yii\base\Model::beforeValidate()]]: デフォルトの実装は [[yii\base\Model::EVENT_BEFORE_VALIDATE]] イベントをトリガするものです。
  このメソッドをオーバーライドするか、または、イベントに反応して、検証が実行される前に、何らかの前処理 (例えば入力されたデータの正規化) をすることが出来ます。
  このメソッドは、検証を続行すべきか否かを示す真偽値を返さなくてはなりません。
* [[yii\base\Model::afterValidate()]]: デフォルトの実装は [[yii\base\Model::EVENT_AFTER_VALIDATE]] イベントをトリガするものです。
  このメソッドをオーバーライドするか、または、イベントに反応して、検証が完了した後に、何らかの後処理をすることが出来ます。


### 条件付きの検証 <span id="conditional-validation"></span>

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


### データのフィルタリング <span id="data-filtering"></span>

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

お分かりのように、これらの検証規則は実際には入力を検証しません。そうではなくて、検証される属性の値を処理して書き戻すのです。


### 空の入力値を扱う <span id="handling-empty-inputs"></span>

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

デフォルトでは、入力値が空であると見なされるのは、それが、空文字列であるか、空配列であるか、null であるときです。
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


## その場限りの検証 <span id="ad-hoc-validation"></span>

時として、何らかのモデルに結び付けられていない値に対する *その場限りの検証* を実行しなければならない場合があります。

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
  その一例が [unique](tutorial-core-validators.md#unique) コアバリデータであり、これはモデルとともに使用されることだけを前提にして設計されています。

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

別の選択肢として、次のように、もっと「クラシック」な構文を使って、その場限りのデータ検証を実行することも出来ます。

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


## バリデータを作成する <span id="creating-validators"></span>

Yii のリリースに含まれている [コアバリデータ](tutorial-core-validators.md) を使う以外に、あなた自身のバリデータを作成することも出来ます。
インラインバリデータとスタンドアロンバリデータを作ることが出来ます。


### インラインバリデータ <span id="inline-validators"></span>

インラインバリデータは、モデルのメソッドまたは無名関数として定義されるバリデータです。
メソッド/関数 のシグニチャは、

```php
/**
 * @param string $attribute 現在検証されている属性
 * @param mixed $params 規則に与えられる "params" の値
 */
function ($attribute, $params)
```

属性が検証に失敗した場合は、メソッド/関数 は [[yii\base\Model::addError()]] を呼んでエラーメッセージをモデルに保存し、後で読み出してエンドユーザに表示することが出来るようにしなければなりません。

下記にいくつかの例を示します。

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // モデルメソッド validateCountry() として定義されるインラインバリデータ
            ['country', 'validateCountry'],

            // 無名関数として定義されるインラインバリデータ
            ['token', function ($attribute, $params) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'トークンは英数字で構成しなければなりません。');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, '国は "USA" または "Web" でなければなりません。');
        }
    }
}
```

> Note|注意: デフォルトでは、インラインバリデータは、関連付けられている属性が空の入力値を受け取ったり、既に何らかの検証規則に失敗したりしている場合には、適用されません。
> 規則が常に適用されることを保証したい場合は、規則の宣言において [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] および/または [[yii\validators\Validator::skipOnError|skipOnError]] のプロパティを false に設定することが出来ます。
> 例えば、
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### スタンドアロンバリデータ <span id="standalone-validators"></span>

スタンドアロンバリデータは、[[yii\validators\Validator]] またはその子クラスを拡張するクラスです。
[[yii\validators\Validator::validateAttribute()]] メソッドをオーバーライドすることによって、その検証ロジックを実装することが出来ます。
[インラインバリデータ](#inline-validators) でするのと同じように、属性が検証に失敗した場合は、[[yii\base\Model::addError()]] を呼んでエラーメッセージをモデルに保存します。
例えば、

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Web'])) {
            $this->addError($model, $attribute, '国は "USA" または "Web" でなければなりません。');
        }
    }
}
```


あなたのバリデータで、モデルを使わない値の検証をサポートしたい場合は、[[yii\validators\Validator::validate()]] もオーバーライドしなければなりません。
または、`validateAttribute()` と `validate()` の代りに、[[yii\validators\Validator::validateValue()]] をオーバーライドしても構いません。
と言うのは、前の二つは、デフォルトでは、`validateValue()` を呼び出すことによって実装されているからです。


## クライアント側での検証 <span id="client-side-validation"></span>

エンドユーザが HTML フォームで値を入力する際には、JavaScript に基づくクライアント側での検証を提供することが望まれます。
というのは、クライアント側での検証は、ユーザが入力のエラーを早く見つけることが出来るようにすることによって、より良いユーザ体験を提供するものだからです。
あなたも、サーバ側での検証 *に加えて* クライアント側での検証をサポートするバリデータを使用したり実装したりすることが出来ます。

> Info|情報: クライアント側での検証は望ましいものですが、不可欠なものではありません。
  その主たる目的は、ユーザにより良い体験を提供することにあります。
  エンドユーザから来る入力値と同じように、クライアント側での検証を決して信用してはいけません。
  この理由により、これまでの項で説明したように、常に [[yii\base\Model::validate()]] を呼び出してサーバ側での検証を実行しなければなりません。


### クライアント側での検証を使う <span id="using-client-side-validation"></span>

多くの [コアバリデータ](tutorial-core-validators.md) は、そのままで、クライアント側での検証をサポートしています。
あなたがする必要があるのは、[[yii\widgets\ActiveForm]] を使って HTML フォームを作るということだけです。
例えば、下の `LoginForm` は二つの規則を宣言しています。
一つは、[required](tutorial-core-validators.md#required) コアバリデータを使っていますが、これはクライアント側とサーバ側の両方でサポートされています。
もう一つは `validatePassword` インラインバリデータを使っていますが、こちらはサーバ側でのみサポートされています。

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
            // username と password はともに必須
            [['username', 'password'], 'required'],

            // password は validatePassword() によって検証される
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'ユーザ名またはパスワードが違います。');
        }
    }
}
```

次のコードによって構築される HTML フォームは、`username` と `password` の二つの入力フィールドを含みます。
何も入力せずにこのフォームを送信すると、何かを入力するように要求するエラーメッセージが、サーバと少しも交信することなく、ただちに表示されることに気付くでしょう。

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('ログイン') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

舞台裏では、[[yii\widgets\ActiveForm]] がモデルで宣言されている検証規則を読んで、クライアント側の検証をサポートするバリデータのために、適切な JavaScript コードを生成します。
ユーザが入力フィールドの値を変更したりフォームを送信したりすると、クライアント側の検証の JavaScript が起動されます。

クライアント側の検証を完全に無効にしたい場合は、[[yii\widgets\ActiveForm::enableClientValidation]] プロパティを false に設定することが出来ます。
また、個々の入力フィールドごとにクライアント側の検証を無効にしたい場合には、入力フィールドの [[yii\widgets\ActiveField::enableClientValidation]] プロパティを false に設定することが出来ます。
`eanbleClientValidation` が入力フィールドのレベルとフォームのレベルの両方で構成されている場合は前者が優先されます。


### クライアント側の検証を実装する <span id="implementing-client-side-validation"></span>

クライアント側の検証をサポートするバリデータを作成するためには、クライアント側での検証を実行する JavaScript コードを返す [[yii\validators\Validator::clientValidateAttribute()]] メソッドを実装しなければなりません。
その JavaScript の中では、次の事前定義された変数を使用することが出来ます。

- `attribute`: 検証される属性の名前。
- `value`: 検証される値。
- `messages`: 属性に対する検証のエラーメッセージを保持するために使用される配列。
- `deferred`: Deferred オブジェクトをプッシュして入れることが出来る配列 (次の項で説明します)。

次の例では、入力された値が既存のステータスのデータに含まれる有効なステータス値であるかどうかを検証する `StatusValidator` を作成します。
このバリデータは、サーバ側とクライアント側の両方の検証をサポートします。

```php
namespace app\components;

use yii\validators\Validator;
use app\models\Status;

class StatusValidator extends Validator
{
    public function init()
    {
        parent::init();
        $this->message = '無効なステータスが入力されました。';
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

> Tip|ヒント: 上記のコード例の主たる目的は、クライアント側の検証をサポートする方法を説明することにあります。
> 実際の仕事では、[in](tutorial-core-validators.md#in) コアバリデータを使って、同じ目的を達することが出来ます。
> 次のように検証規則を書けばよいのです。
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

### Deferred 検証 <span id="deferred-validation"></span>

非同期のクライアント側の検証をサポートする必要がある場合は、[Defered オブジェクト](http://api.jquery.com/category/deferred-object/) を作成することが出来ます。
例えば、AJAX によるカスタム検証を実行するために、次のコードを使うことが出来ます。

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

上のコードにおいて、`deferred` は Yii が提供する変数で、Deferred オブジェクトの配列です。
jQuery の `$.get()` メソッドによって作成された Deferred オブジェクトが `deferred` 配列にプッシュされています。

Deferred オブジェクトを明示的に作成して、非同期のコールバックが呼ばれたときに、Deferred オブジェクトの `resolve()` メソッドを呼ぶことも出来ます。
次の例は、アップロードされる画像ファイルの大きさをクライアント側で検証する方法を示すものです。

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        var def = $.Deferred();
        var img = new Image();
        img.onload = function() {
            if (this.width > 150) {
                messages.push('画像の幅が大きすぎます。');
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

> Note|注意: 属性が検証された後に、`resolve()` メソッドを呼び出さなければなりません。
  そうしないと、主たるフォームの検証が完了しません。

簡潔に記述できるように、`deferred` 配列はショートカットメソッド `add()` を装備しており、このメソッドを使うと、自動的に Deferred オブジェクトを作成して `deferred` 配列に追加することが出来ます。
このメソッドを使えば、上記の例は次のように簡潔に記すことが出来ます。

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.add(function(def) {
            var img = new Image();
            img.onload = function() {
                if (this.width > 150) {
	                messages.push('画像の幅が大きすぎます。');
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


### AJAX 検証 <span id="ajax-validation"></span>

場合によっては、サーバだけが必要な情報を持っているために、サーバ側でしか検証が実行できないことがあります。
例えば、ユーザ名がユニークであるか否かを検証するためには、サーバ側で user テーブルを調べることが必要になります。
このような場合には、AJAX ベースの検証を使うことが出来ます。
AJAX 検証は、通常のクライアント側での検証と同じユーザ体験を保ちながら、入力値を検証するためにバックグラウンドで AJAX リクエストを発行します。

単一のインプットフィールドに対して AJAX 検証を有効にするためには、そのフィールドの [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]] プロパティを true に設定し、フォームに一意の `id` を指定します。

```php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'registration-form',
]);

echo $form->field($model, 'username', ['enableAjaxValidation' => true]);

// ...

ActiveForm::end();
```

フォーム全体に対して AJAX 検証を有効にするためには、フォームのレベルで [[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]] を true に設定します。

```php
$form = ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]);
```

> Note|注意: `enableAjaxValidation` プロパティがインプットフィールドのレベルとフォームのレベルの両方で構成された場合は、前者が優先されます。

また、サーバ側では、AJAX 検証のリクエストを処理できるように準備しておく必要があります。
これは、コントローラのアクションにおいて、次のようなコード断片を使用することで達成できます。

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

上記のコードは、現在のリクエストが AJAX であるかどうかをチェックします。
もし AJAX であるなら、リクエストに応えて検証を実行し、エラーを JSON 形式で返します。

> Info|情報: AJAX 検証を実行するためには、[Deferred 検証](#deferred-validation) を使うことも出来ます。
  しかし、ここで説明された AJAX 検証の機能の方がより体系化されており、コーディングの労力も少なくて済みます。
