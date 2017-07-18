フォームを作成する
==================

アクティブレコードに基づくフォーム : ActiveForm
-----------------------------------------------
Yii においてフォームを使用するときは、主として [[yii\widgets\ActiveForm]] による方法を使います。
フォームがモデルに基づくものである場合はこの方法を選ぶべきです。
これに加えて、[[yii\helpers\Html]] にはいくつかの有用なメソッドがあり、どんなフォームでも、ボタンやヘルプテキストを追加するのには、通常、それらのメソッドを使います。

フォームは、クライアント側で表示されるものですが、たいていの場合、対応する [モデル](structure-models.md) を持ち、それを使ってサーバ側でフォームの入力を検証します
(入力の検証の詳細については、[入力を検証する](input-validation.md) の節を参照してください)。
モデルに基づくフォームを作成する場合、最初のステップは、モデルそのものを定義することです。
モデルは、データベースの何らかのデータを表現するために [アクティブレコード](db-active-record.md) から派生させたクラスか、あるいは、任意の入力、例えばログインフォームの入力を保持するための ([[yii\base\Model]] から派生させた) 汎用的な Model クラスか、どちらかにすることが出来ます。
以下の例においては、ログインフォームのために汎用的なモデルを使う方法を示します。

```php
<?php

class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // 検証規則をここで定義
        ];
    }

```

コントローラにおいて、このモデルのインスタンスをビューに渡し、ビューでは [[yii\widgets\ActiveForm|ActiveForm]] ウィジェットがフォームを表示するのに使われます。

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('ログイン', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
```

### `begin()` と `end()` で囲む <span id="wrapping-with-begin-and-end"></span>
上記のコードでは、[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] がフォームのインスタンスを作成するとともに、フォームの開始をマークしています。
[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] と [[yii\widgets\ActiveForm::end()|ActiveForm::end()]] の間に置かれた全てのコンテントが HTML の `<form>` タグによって囲まれます。
どのウィジェットでも同じですが、ウィジェットをどのように構成すべきかに関するオプションを指定するために、`begin` メソッドに配列を渡すことが出来ます。
この例では、追加の CSS クラスと要素を特定するための ID が渡されて、`<form>` の開始タグに適用されています。
利用できるオプションの全ては [[yii\widgets\ActiveForm]] の API ドキュメントに記されていますので参照してください。

### ActiveField <span id="activefield"></span>.
フォームの中では、フォームの要素を作成するために、ActiveForm ウィジェットの [[yii\widgets\ActiveForm::field()|ActiveForm::field()]] メソッドが呼ばれています。
このメソッドは、フォームの要素だけでなく、そのラベルも作成し、適用できる JavaScript の検証メソッドがあれば、それも追加します。
[[yii\widgets\ActiveForm::field()|ActiveForm::field()]] メソッドは、[[yii\widgets\ActiveField]] のインスタンスを返します。
このメソッドの呼び出し結果を直接にエコーすると、結果は通常の (text の) インプットになります。
このメソッドの呼び出しに追加の [[yii\widgets\ActiveField|ActiveField]] のメソッドをチェーンして、出力結果をカスタマイズすることが出来ます。

```php
// パスワードのインプット
<?= $form->field($model, 'password')->passwordInput() ?>
// ヒントとカスタマイズしたラベルを追加
<?= $form->field($model, 'username')->textInput()->hint('お名前を入力してください')->label('お名前') ?>
// HTML5 のメールインプット要素を作成
<?= $form->field($model, 'email')->input('email') ?>
```

これで、フォームのフィールドによって定義された [[yii\widgets\ActiveField::$template|テンプレート]] に従って、`<label>`、`<input>` など、全てのタグが生成されます。
インプットフィールドの名前は、モデルの [[yii\base\Model::formName()|フォーム名]] と属性から自動的に決定されます。
例えば、上記の例における `username` 属性のインプットフィールドの名前は `LoginForm[username]` となります。
この命名規則の結果として、ログインフォームの全ての属性が配列として、サーバ側においては `$_POST['LoginForm']` に格納されて利用できることになります。

> Tip: 一つのフォームに一つのモデルだけがある場合、インプットの名前を単純化したいときは、
> モデルの [[yii\base\Model::formName()|formName()]] メソッドをオーバーライドして空文字列を返すようにして、配列の部分をスキップすることが出来ます。
> この方法を使えば、[GridView](output-data-widgets.md#grid-view) で使われるフィルターモデルで、もっと見栄えの良い URL を生成させることが出来ます。

モデルの属性を指定するために、もっと洗練された方法を使うことも出来ます。
例えば、複数のファイルをアップロードしたり、複数の項目を選択したりする場合に、属性の名前に `[]` を付けて、属性が配列の値を取り得ることを指定することが出来ます。

```php
// 複数のファイルのアップロードを許可する
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// 複数の項目をチェックすることを許可する
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

送信ボタンなどのフォーム要素に名前をつけるときには注意が必要です。
[jQuery ドキュメント](https://api.jquery.com/submit/) によれば、衝突を生じさせ得る予約された名前がいくつかあります。

> フォームおよびフォームの子要素は、フォームのプロパティと衝突するインプット名や id、たとえば `submit`、`length`、`method` などを使ってはなりません。
> 名前の衝突は訳の分らない失敗を生じさせることがあります。
> 命名規則の完全なリストを知り、この問題についてあなたのマークアップをチェックするためには、[DOMLint](http://kangax.github.io/domlint/) を参照してください。

フォームに HTML タグを追加するためには、素の HTML を使うか、または、上記の例の [[yii\helpers\Html::submitButton()|Html::submitButton()]] のように、[[yii\helpers\Html|Html]] ヘルパクラスのメソッドを使うことが出来ます。

> Tip: あなたのアプリケーションで Twitter Bootstrap CSS を使っている場合は、[[yii\widgets\ActiveForm]] の代りに [[yii\bootstrap\ActiveForm]] を使うのが良いでしょう。
> 後者は前者の拡張であり、bootstrap CSS フレームワークで使用するための追加のスタイルをサポートしています。

> Tip: 必須フィールドをアスタリスク付きのスタイルにするために、次の CSS を使うことが出来ます。
>
> ```css
> div.required label.control-label:after {
>     content: " *";
>     color: red;
> }
> ```

リストを作る <span id="creating-activeform-lists"></span>
--------------------------

三種類のリストがあります:
* ドロップダウンリスト
* ラジオリスト
* チェックボックスリスト

リストを作るためには、項目の配列を準備しなければなりません。これは、手作業でやることも出来ます。

```php
$items = [
    1 => '項目 1', 
    2 => '項目 2'
]
```

または、DB から取得することも出来ます。

```php
$items = Category::find()
        ->select(['id', 'label'])
        ->indexBy('id')
        ->column();
```

このような `$items` が、いろんなリストウィジェットによって処理されるべきものとなります。
フォームのフィールドの値(および現在アクティブな項目)は、`$model` の属性の現在の値に従って自動的に設定されます。

#### ドロップダウンリストを作る <span id="creating-activeform-dropdownlist"></span>

ActiveField の [[\yii\widgets\ActiveField::dropDownList()]] メソッドを使って、ドロップダウンリストを作ることが出来ます。

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->dropdownList([
        1 => '項目 1', 
        2 => '項目 2'
    ],
    ['prompt'=>'カテゴリーを選択してください']
);
```

#### ラジオリストを作る <span id="creating-activeform-radioList"></span>

ActiveField の [[\yii\widgets\ActiveField::radioList()]] メソッドを使ってラジオリストを作ることが出来ます。

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->radioList([
    1 => 'ラジオ 1', 
    2 => 'ラジオ 2'
]);
```

#### チェックボックスリストを作る <span id="creating-activeform-checkboxList"></span>

ActiveField の [[\yii\widgets\ActiveField::checkboxList()]] メソッドを使ってチェックボックスリストを作ることが出来ます。

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->checkboxList([
    1 => 'チェックボックス 1', 
    2 => 'チェックボックス 2'
]);
```


Pjax を使う <span id="working-with-pjax"></span>
-----------

[[yii\widgets\Pjax|Pjax]] ウィジェットを使うと、ページ全体をリロードせずに、ページの一部分だけを更新することが出来ます。
これを使うと、送信後にフォームだけを更新して、その中身を入れ替えることが出来ます。

[[yii\widgets\Pjax::$formSelector|$formSelector]] を構成すると、どのフォームの送信が pjax を起動するかを指定することが出来ます。
それが指定されていない場合は、Pjax に囲まれたコンテントの中にあって `data-pjax` 属性を持つすべてのフォームが pjax リクエストを起動することになります。

```php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Pjax::begin([
    // Pjax のオプション
]);
    $form = ActiveForm::begin([
        'options' => ['data' => ['pjax' => true]],
        // ActiveForm の追加のオプション
    ]);

        // ActiveForm のコンテント

    ActiveForm::end();
Pjax::end();
```
> Tip: [[yii\widgets\Pjax|Pjax]] ウィジェット内部のリンクに注意してください。
> と言うのは、リンクに対するレスポンスもウィジェット内部でレンダリングされるからです。
> これを防ぐためには、`data-pjax="0"` という HTML 属性を使用します。

#### 送信ボタンの値とファイルのアップロード

`jQuery.serializeArray()` については、
[ファイル](https://github.com/jquery/jquery/issues/2321) および
[送信ボタンの値](https://github.com/jquery/jquery/issues/2321)
を扱うときに問題があることが知られています。
この問題は解決される見込みがなく、関数自体も HTML5 で導入された `FormData` クラスによって置き換えられるべきものとして、廃止予定となっています。

このことは、すなわち、ajax または [[yii\widgets\Pjax|Pjax]] ウィジェットを使う場合、ファイルと送信ボタンの値に対する唯一の公式なサポートは、
`FormData` クラスに対する [ブラウザのサポート](https://developer.mozilla.org/en-US/docs/Web/API/FormData#Browser_compatibility) に依存しているということを意味します。


さらに読むべき文書 <span id="further-reading"></span>
------------------

次の節 [入力を検証する](input-validation.md) は、送信されたフォームデータのサーバ側でのバリデーションと、ajax バリデーションおよびクライアント側バリデーションを扱います。

フォームのもっと複雑な使用方法については、以下の節を読んで下さい。

- [表形式インプットのデータ収集](input-tabular-input.md) - 同じ種類の複数のモデルのデータを収集する。
- [複数のモデルのデータを取得する](input-multiple-models.md) - 同じフォームの中で複数の異なるモデルを扱う。
- [ファイルをアップロードする](input-file-upload.md) - フォームを使ってファイルをアップロードする方法。
