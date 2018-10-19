モデル
======

モデルは [MVC](http://ja.wikipedia.org/wiki/Model_View_Controller) アーキテクチャの一部を成すものです。
これは、ビジネスのデータ、規則、ロジックを表現するオブジェクトです。

モデル・クラスは、[[yii\base\Model]] またはその子クラスを拡張することによって作成することが出来ます。
基底クラス [[yii\base\Model]] は、次のような数多くの有用な機能をサポートしています。

* [属性](#attributes): ビジネス・データを表現します。通常のオブジェクト・プロパティや配列要素のようにして
  アクセスすることが出来ます。
* [属性のラベル](#attribute-labels): 属性の表示ラベルを指定します。
* [一括代入](#massive-assignment): 一回のステップで複数の属性にデータを投入することをサポートしています。
* [検証規則](#validation-rules): 宣言された検証規則に基いて入力されたデータの有効性を保証します。
* [データのエクスポート](#data-exporting): カスタマイズ可能な形式でモデル・データを配列にエクスポートすることが出来ます。

`Model` クラスは、[アクティブ・レコード](db-active-record.md) のような、更に高度なモデルの基底クラスでもあります。
それらの高度なモデルについての詳細は、関連するドキュメントを参照してください。

> Info: あなたのモデル・クラスの基底クラスとして [[yii\base\Model]] を使うことが要求されている訳ではありません。
  しかしながら、Yii のコンポーネントの多くが [[yii\base\Model]] をサポートするように作られていますので、通常は [[yii\base\Model]] がモデルの基底クラスとして推奨されます。


## 属性 <span id="attributes"></span>

モデルはビジネス・データを *属性* の形式で表現します。全ての属性はそれぞれパブリックにアクセス可能なモデルのプロパティと同様なものです。
[[yii\base\Model::attributes()]] メソッドが、モデルがどのような属性を持つかを指定します。

属性に対しては、通常のオブジェクト・プロパティにアクセスするのと同じようにして、アクセスすることが出来ます。

```php
$model = new \app\models\ContactForm;

// "name" は ContactForm の属性
$model->name = 'example';
echo $model->name;
```

また、配列の要素にアクセスするようして、属性にアクセスすることも出来ます。
これは、[[yii\base\Model]] が [ArrayAccess インタフェイス](http://php.net/manual/ja/class.arrayaccess.php) と
[Traversable インタフェイス](http://jp2.php.net/manual/ja/class.traversable.php) をサポートしている恩恵です。

```php
$model = new \app\models\ContactForm;

// 配列要素のように属性にアクセスする
$model['name'] = 'example';
echo $model['name'];

// モデルは foreach で中身をたどることが出来る
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```


### 属性を定義する <span id="defining-attributes"></span>

あなたのモデルが [[yii\base\Model]] を直接に拡張するものである場合、デフォルトでは、全ての *static でない public な* メンバ変数は属性となります。
例えば、次に示す `ContactForm` モデルは四つの属性、すなわち、`name`、`email`、`subject`、そして、`body` を持ちます。
この `ContactForm` モデルは、HTML フォームから受け取る入力データを表現するために使われます。

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
}
```


[[yii\base\Model::attributes()]] をオーバーライドして、属性を異なる方法で定義することが出来ます。
このメソッドはモデルが持つ属性の名前を返さなくてはなりません。
例えば、[[yii\db\ActiveRecord]] は、関連付けられたデータベース・テーブルのコラム名を属性の名前として返すことによって、属性を定義しています。
ただし、これと同時に、定義された属性に対して通常のオブジェクト・プロパティと同じようにアクセスすることが出来るように、
`__get()` や `__set()` などのマジック・メソッドをオーバーライドする必要があるかもしれないことに注意してください。


### 属性のラベル <span id="attribute-labels"></span>

属性の値を表示したり、入力してもらったりするときに、属性と関連付けられたラベルを表示する必要があることがよくあります。
例えば、`firstName` という名前の属性を考えたとき、入力フォームやエラー・メッセージのような箇所でエンド・ユーザに表示するときは、
もっとユーザ・フレンドリーな `First Name` というラベルを表示したいと思うでしょう。

[[yii\base\Model::getAttributeLabel()]] を呼ぶことによって属性のラベルを得ることが出来ます。例えば、

```php
$model = new \app\models\ContactForm;

// "Name" を表示する
echo $model->getAttributeLabel('name');
```

デフォルトでは、属性のラベルは属性の名前から自動的に生成されます。
ラベルの生成は [[yii\base\Model::generateAttributeLabel()]] というメソッドによって行われます。
このメソッドは、キャメルケースの変数名を複数の単語に分割し、各単語の最初の文字を大文字にします。
例えば、`username` は `Username` となり、`firstName` は `First Name` となります。

自動的に生成されるラベルを使用したくない場合は、[[yii\base\Model::attributeLabels()]] をオーバーライドして、
属性のラベルを明示的に宣言することが出来ます。例えば、

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;

    public function attributeLabels()
    {
        return [
            'name' => 'Your name',
            'email' => 'Your email address',
            'subject' => 'Subject',
            'body' => 'Content',
        ];
    }
}
```

複数の言語をサポートするアプリケーションでは、属性のラベルを翻訳したいと思うでしょう。
これも、以下のように、[[yii\base\Model::attributeLabels()|attributeLabels()]] の中で行うことが出来ます。

```php
public function attributeLabels()
{
    return [
        'name' => \Yii::t('app', 'Your name'),
        'email' => \Yii::t('app', 'Your email address'),
        'subject' => \Yii::t('app', 'Subject'),
        'body' => \Yii::t('app', 'Content'),
    ];
}
```

条件に従って属性のラベルを定義することも出来ます。
例えば、モデルが使用される [シナリオ](#scenarios) に基づいて、同じ属性に対して違うラベルを返すことことが出来ます。

> Info: 厳密に言えば、属性のラベルは [ビュー](structure-views.md) の一部を成すものです。
  しかし、たいていの場合、モデルの中でラベルを宣言する方が便利が良く、結果としてクリーンで再利用可能なコードになります。


## シナリオ <span id="scenarios"></span>

モデルはさまざまに異なる *シナリオ* で使用されます。
例えば、`User` モデルはユーザ・ログインの入力を収集するために使われますが、同時に、ユーザ登録の目的でも使われます。
異なるシナリオの下では、モデルが使用するビジネス・ルールとロジックも異なるものになり得ます。
例えば、`email` 属性はユーザ登録の際には必須とされるかも知れませんが、ログインの際にはそうではないでしょう。

モデルは [[yii\base\Model::scenario]] プロパティを使って、自身が使われているシナリオを追跡します。
デフォルトでは、モデルは `default` という一つのシナリオだけをサポートします。
次のコードは、モデルのシナリオを設定する二つの方法を示すものです。

```php
// シナリオをプロパティとして設定する
$model = new User;
$model->scenario = User::SCENARIO_LOGIN;

// シナリオを設定情報で設定する
$model = new User(['scenario' => User::SCENARIO_LOGIN]);
```

デフォルトでは、モデルによってサポートされるシナリオは、モデルで宣言されている [検証規則](#validation-rules) によって決定されます。
しかし、次のように、[[yii\base\Model::scenarios()]] メソッドをオーバーライドして、
この振る舞いをカスタマイズすることが出来ます。

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        return [
            self::SCENARIO_LOGIN => ['username', 'password'],
            self::SCENARIO_REGISTER => ['username', 'email', 'password'],
        ];
    }
}
```

> Info: 上記の例と後続の例では、モデル・クラスは [[yii\db\ActiveRecord]] を拡張するものとなっています。
  というのは、複数のシナリオを使用することは、通常は、[アクティブ・レコード](db-active-record.md) クラスで発生するからです。

`seanarios()` メソッドは、キーがシナリオの名前であり、値が対応する *アクティブな属性* である配列を返します。
アクティブな属性とは、[一括代入](#massive-assignment) することが出来て、[検証](#validation-rules) の対象になる属性です。
上記の例では、`login` シナリオにおいては `username` と `password` の属性がアクティブであり、
一方、`register` シナリオにおいては、`username` と `password` に加えて `email` もアクティブです。

`scenarios()` のデフォルトの実装は、検証規則の宣言メソッドである [[yii\base\Model::rules()]] に現れる全てのシナリオを返すものです。
`scenarios()` をオーバーライドするときに、デフォルトのシナリオに加えて新しいシナリオを導入したい場合は、
次のようなコードを書きます。

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password'];
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password'];
        return $scenarios;
    }
}
```

シナリオの機能は、主として、[検証](#validation-rules) と [属性の一括代入](#massive-assignment) によって使用されます。
しかし、他の目的に使うことも可能です。例えば、現在のシナリオに基づいて異なる [属性のラベル](#attribute-labels)
を宣言することも出来ます。


## 検証規則 <span id="validation-rules"></span>

モデルのデータをエンド・ユーザから受け取ったときは、データを検証して、
それが一定の規則 (*検証規則*、あるいは、いわゆる *ビジネス・ルール*) を満たしていることを確認しなければなりません。
`ContactForm` モデルを例に挙げるなら、全ての属性が空ではなく、`email` 属性が有効なメール・アドレスを含んでいることを確認したいでしょう。
いずれかの属性の値が対応するビジネス・ルールを満たしていないときは、
ユーザがエラーを訂正するのを助ける適切なエラー・メッセージが表示されなければなりません。

受信したデータを検証するために、[[yii\base\Model::validate()]] を呼ぶことが出来ます。
このメソッドは、[[yii\base\Model::rules()]] で宣言された検証規則を使って、該当するすべての属性を検証します。
エラーが見つからなければ、メソッドは `true` を返します。
そうでなければ、[[yii\base\Model::errors]] にエラーを保存して、`false` を返します。例えば、

```php
$model = new \app\models\ContactForm;

// モデルの属性にユーザの入力を代入する
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // すべての入力値は有効である
} else {
    // 検証が失敗: $errors はエラー・メッセージを含む配列
    $errors = $model->errors;
}
```


モデルに関連付けられた検証規則を宣言するためには、[[yii\base\Model::rules()]] メソッドをオーバーライドして、
モデルの属性が満たすべき規則を返すようにします。
次の例は、`ContactForm` モデルのために宣言された検証規則を示します。

```php
public function rules()
{
    return [
        // name、email、subject、body の属性が必須
        [['name', 'email', 'subject', 'body'], 'required'],

        // email 属性は、有効なメール・アドレスでなければならない
        ['email', 'email'],
    ];
}
```

一つの規則は、一つまたは複数の属性を検証するために使うことが出来ます。
また、一つの属性は、一つまたは複数の規則によって検証することが出来ます。
検証規則をどのように宣言するかについての詳細は [入力を検証する](input-validation.md) のセクションを参照してください。

時として、特定の [シナリオ](#scenarios) にのみ適用される規則が必要になるでしょう。
そのためには、下記のように、規則に `on` プロパティを指定することが出来ます。

```php
public function rules()
{
    return [
        // "register" シナリオでは、username、email、password のすべてが必須
        [['username', 'email', 'password'], 'required', 'on' => self::SCENARIO_REGISTER],

        // "login" シナリオでは、username と password が必須
        [['username', 'password'], 'required', 'on' => self::SCENARIO_LOGIN],
    ];
}
```

`on` プロパティを指定しない場合は、その規則は全てのシナリオに適用されることになります。
現在の [[yii\base\Model::scenario|シナリオ]] に適用可能な規則は *アクティブな規則* と呼ばれます。

属性が検証されるのは、それが `scenarios()` の中でアクティブな属性であると宣言されており、かつ、
その属性が `rules()` の中で宣言されている一つまたは複数のアクティブな規則と結び付けられている場合であり、また、そのような場合だけです。


## 一括代入 <span id="massive-assignment"></span>

一括代入は、一行のコードを書くだけで、ユーザの入力した複数のデータをモデルに投入できる便利な方法です。
一括代入は、入力されたデータを [[yii\base\Model::$attributes]] プロパティに直接に代入することによって、モデルの属性にデータを投入します。
次の二つのコード断片は等価であり、どちらもエンド・ユーザから送信されたフォームのデータを
`ContactForm` モデルの属性に割り当てようとするものです。
明らかに、一括代入を使う前者の方が、後者よりも明快で間違いも起こりにくいでしょう。

```php
$model = new \app\models\ContactForm;
$model->attributes = \Yii::$app->request->post('ContactForm');
```

```php
$model = new \app\models\ContactForm;
$data = \Yii::$app->request->post('ContactForm', []);
$model->name = isset($data['name']) ? $data['name'] : null;
$model->email = isset($data['email']) ? $data['email'] : null;
$model->subject = isset($data['subject']) ? $data['subject'] : null;
$model->body = isset($data['body']) ? $data['body'] : null;
```


### 安全な属性 <span id="safe-attributes"></span>

一括代入は、いわゆる *安全な属性*、すなわち、[[yii\base\Model::scenarios()]] においてモデルの現在の
[[yii\base\Model::scenario|シナリオ]] のためにリストされている属性に対してのみ適用されます。
例えば、`User` モデルが次のようなシナリオ宣言を持っている場合において、現在のシナリオが `login` であるときは、
`username` と `password` のみが一括代入が可能です。
その他の属性はいっさい触れられません。

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password'],
        self::SCENARIO_REGISTER => ['username', 'email', 'password'],
    ];
}
```

> Info: 一括代入が安全な属性に対してのみ適用されるのは、エンド・ユーザの入力データがどの属性を修正することが出来るか、
ということを制御する必要があるからです。
  例えば、`User` モデルに、ユーザに割り当てられた権限を決定する `permission` という属性がある場合、
この属性を修正できるのは、管理者がバックエンドのインタフェイスを通じてする時だけに制限したいでしょう。

[[yii\base\Model::scenarios()]] のデフォルトの実装は [[yii\base\Model::rules()]] に現われる全てのシナリオと属性を返すものです。
従って、このメソッドをオーバーライドしない場合は、アクティブな検証規則のどれかに出現する限り、
その属性は安全である、ということになります。

このため、実際に検証することなく属性を安全であると宣言できるように、
`safe` というエイリアスを与えられた特別なバリデータが提供されています。
例えば、次の規則は `title` と `description` の両方が安全な属性であると宣言しています。

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### 安全でない属性 <span id="unsafe-attributes"></span>

上記で説明したように、[[yii\base\Model::scenarios()]] メソッドは二つの目的を持っています。
すなわち、どの属性が検証されるべきかを決めることと、どの属性が安全であるかを決めることです。
めったにない場合として、属性を検証する必要はあるが、安全であるという印は付けたくない、ということがあります。
そういう時は、下の例の `secret` 属性のように、名前の前に感嘆符 `!` を付けて `scenarios()` の中で宣言することが出来ます。

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password', '!secret'],
    ];
}
```

このモデルが `login` シナリオにある場合、三つの属性は全て検証されます。
しかし、`username` と `password` の属性だけが一括代入が可能です。
`secret` 属性に入力値を割り当てるためには、下記のように明示的に代入を実行する必要があります。

```php
$model->secret = $secret;
```

同じ事が `rules()` メソッドの中でも出来ます。

```php
public function rules()
{
    return [
        [['username', 'password', '!secret'], 'required', 'on' => 'login']
    ];
}
```

この場合、`username`、`password` そして `secret` の属性が必須項目とされますが、`secret` は明示的に代入される必要があります。


## データのエクスポート <span id="data-exporting"></span>

モデルを他の形式にエクスポートする必要が生じることはよくあります。例えば、モデルのコレクションを JSON や Excel 形式に変換したい場合があるでしょう。
このエクスポートのプロセスは二つの独立したステップに分割することが出来ます。

- モデルが配列に変換され、
- 配列が目的の形式に変換される。

あなたは最初のステップだけに注力することが出来ます。
と言うのは、第二のステップは汎用的なデータ・フォーマッタ、例えば [[yii\web\JsonResponseFormatter]] によって達成できるからです。

モデルを配列に変換する最も簡単な方法は、[[yii\base\Model::$attributes]] プロパティを使うことです。
例えば、

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

デフォルトでは、[[yii\base\Model::$attributes]] プロパティは [[yii\base\Model::attributes()]]
で宣言されている *全て* の属性の値を返します。

モデルを配列に変換するためのもっと柔軟で強力な方法は、[[yii\base\Model::toArray()]] メソッドを使うことです。
このメソッドのデフォルトの動作は [[yii\base\Model::$attributes]] のそれと同じものです。
しかしながら、このメソッドを使うと、どのデータ項目 (*フィールド* と呼ばれます) を結果の配列に入れるか、そして、その項目にどのような書式を適用するかを選ぶことが出来ます。
実際、[レスポンス形式の設定](rest-response-formatting.md) で説明されているように、RESTful ウェブサービスの開発においては、
これがモデルをエクスポートするデフォルトの方法となっています。


### フィールド <span id="fields"></span>

フィールドとは、単に、モデルの [[yii\base\Model::toArray()]] メソッドを呼ぶことによって取得される配列に含まれる、
名前付きの要素のことです。

デフォルトでは、フィールドの名前は属性の名前と等しいものになります。
しかし、このデフォルトの動作は、[[yii\base\Model::fields()|fields()]] および/または [[yii\base\Model::extraFields()|extraFields()]] メソッドをオーバーライドして、変更することが出来ます。
どちらのメソッドも、フィールド定義のリストを返すものです。
`fields()` によって定義されるフィールドは、デフォルト・フィールドです。すなわち、`toArray()` はデフォルトでこれらのフィールドを返す、ということを意味します。
`extraFields()` メソッドは、`$expand` パラメータによって指定する限りにおいて `toArray()` によって返される、追加のフィールドを定義するものです。
例として、次のコードは、`fields()` で定義された全てのフィールドと、(`extraFields()` で定義されていれば)
`prettyName` と `fullAddress` フィールドを返すものです。

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

`fields()` をオーバーライドして、フィールドを追加、削除、リネーム、再定義することが出来ます。
`fields()` の返り値は配列でなければなりません。配列のキーはフィールド名であり、配列の値は対応するフィールド定義です。
フィールドの定義には、プロパティ/属性 の名前か、または、対応するフィールドの値を返す無名関数を使うことが出来ます。
フィールド名がそれを定義する属性名と同一であるという特殊な場合においては、配列のキーを省略することが出来ます。
例えば、

```php
// 明示的に全てのフィールドをリストする方法。(API の後方互換性を保つために) DB テーブルや
// モデル属性の変更がフィールドの変更を引き起こさないことを保証したい場合に適している。
public function fields()
{
    return [
        // フィールド名が属性名と同じ
        'id',

        // フィールド名は "email"、対応する属性名は "email_address"
        'email' => 'email_address',

        // フィールド名は "name"、その値は PHP コールバックで定義
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// いくつかのフィールドを除外する方法。親の実装を継承しつつ、公開すべきでないフィールドは
// 除外したいときに適している。
public function fields()
{
    $fields = parent::fields();

    // 公開すべきでない情報を含むフィールドを削除する
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: デフォルトではモデルの全ての属性がエクスポートされる配列に含まれるため、データを精査して、
> 公開すべきでない情報が含まれていないことを確認しなければなりません。
> そういう情報がある場合は、`fields()` をオーバーライドして、除外しなければなりません。
> 上記の例では、`auth_key`、`password_hash` および `password_reset_token` を除外しています。


## ベスト・プラクティス <span id="best-practices"></span>

モデルは、ビジネスのデータ、規則、ロジックを表わす中心的なオブジェクトです。
モデルは、たいてい、さまざまな場所で再利用される必要があります。
良く設計されたアプリケーションでは、通常、モデルは [コントローラ](structure-controllers.md) よりもはるかに太ったものになります。

要約すると、モデルは、

* ビジネス・データを表現する属性を含むことが出来ます。
* データの有効性と整合性を保証する検証規則を含むことが出来ます。
* ビジネス・ロジックを実装するメソッドを含むことが出来ます。
* リクエスト、セッション、または他の環境データに直接アクセスするべきではありません。
  これらのデータは、[コントローラ](structure-controllers.md) によってモデルに注入されるべきです。
* HTML を埋め込むなどの表示用のコードは避けるべきです -  表示は [ビュー](structure-views.md) で行う方が良いです。
* あまりに多くの [シナリオ](#scenarios) を一つのモデルで持つことは避けましょう。

大規模で複雑なシステムを開発するときには、たいてい、上記の最後にあげた推奨事項を考慮するのが良いでしょう。
そういうシステムでは、モデルは数多くの場所で使用され、それに従って、数多くの規則セットやビジネス・ロジックを含むため、
非常に太ったものになり得ます。
コードの一ヶ所に触れるだけで数ヶ所の違った場所に影響が及ぶため、ついには、モデルのコードの保守が悪夢になってしまうこともよくあります。
モデルのコードの保守性を高めるためには、以下の戦略をとることが出来ます。

* 異なる [アプリケーション](structure-applications.md) または [モジュール](structure-modules.md)
  によって共有される一連の基底モデル・クラスを定義します。
  これらのモデル・クラスは、すべてで共通に使用される最小限の規則セットとロジックのみを含むべきです。
* モデルを使用するそれぞれの [アプリケーション](structure-applications.md) または [モジュール](structure-modules.md) において、
  対応する基底モデル・クラスから拡張した具体的なモデル・クラスを定義します。
  この具体的なモデル・クラスが、そのアプリケーションやモジュールに固有の規則やロジックを含むべきです。

例えば、[アドバンスト・プロジェクト・テンプレート](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/README.md) の中で、基底モデル・クラス `common\models\Post` を定義することが出来ます。
次に、フロントエンド・アプリケーションにおいては、`common\models\Post` から拡張した具体的なモデル・クラス
`frontend\models\Post` を定義して使います。
また、バックエンド・アプリケーションにおいても、同様に、`backend\models\Post` を定義します。
この戦略を取ると、`frontend\models\Post` の中のコードはフロントエンド・アプリケーション固有のものであると保証することが出来ます。
そして、フロントエンドのコードにどのような変更を加えても、バックエンド・アプリケーションを壊すかもしれないと心配する必要がなくなります。
