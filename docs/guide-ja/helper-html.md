Html ヘルパ
===========

全てのウェブ・アプリケーションは大量の HTML マークアップを生成します。
マークアップが静的な場合は、[PHP と HTML を一つのファイルに混ぜる](http://php.net/manual/ja/language.basic-syntax.phpmode.php) ことによって効率よく生成することが可能ですが、マークアップを動的にするとなると、何らかの助けが無ければ、処理がトリッキーになってきます。
Yii はそのような手助けを Html ヘルパの形式で提供します。
これは、よく使われる HTML タグとそのオプションやコンテントを処理するための一連のスタティック・メソッドを提供するものです。

> Note: あなたのマークアップがおおむね静的なものである場合は、HTML を直接に使用する方が適切です。
> 何でもかんでも Html ヘルパの呼び出しでラップする必要はありません。


## 基礎 <span id="basics"></span>

動的な HTML を文字列の連結によって構築していると、あっという間に乱雑なコードになります。
そのため、Yii はタグのオプションを操作し、それらのオプションに基づいてタグを構築する一連のメソッドを提供します。


### タグを生成する <span id="generating-tags"></span>

タグを生成するコードは次のようなものです。

```php
<?= Html::tag('p', Html::encode($user->name), ['class' => 'username']) ?>
```

最初の引数はタグの名前です。二番目の引数は、開始タグと終了タグの間に囲まれることになるコンテントです。
`Html::encode` を使っていることに注目してください。
これは、必要な場合には HTML を使うことが出来るように、コンテントが自動的にはエンコードされないからです。
三番目の引数は HTML のオプション、言い換えると、タグの属性です。この配列で、キーは `class`、`href`、`target` などの属性の名前であり、値は属性の値です。

上記のコードは次の HTML を生成します。

```html
<p class="username">samdark</p>
```

開始タグまたは終了タグだけが必要な場合は、`Html::beginTag()` または `Html::endTag()` のメソッドを使うことが出来ます。

オプションは多くの Html ヘルパのメソッドとさまざまなウィジェットで使用されます。
その全ての場合において、いくつか追加の処理がなされることを知っておいてください。

- 値が `null` である場合は、対応する属性はレンダリングされません。
- 値が真偽値である属性は、[真偽値属性 (boolean attributes)](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes) 
  として扱われます。
- 属性の値は [[yii\helpers\Html::encode()|Html::encode()]] を使って HTML エンコードされます。
- 属性の値が配列である場合は、次のように処理されます。
 
   * 属性が [[yii\helpers\Html::$dataAttributes]] にリストされているデータ属性である場合、例えば `data` や `ng` である場合は、
    値の配列にある要素の一つ一つについて、属性のリストがレンダリングされます。
     例えば、`'data' => ['id' => 1, 'name' => 'yii']` は `data-id="1" data-name="yii"` を生成します。
     また、`'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` は 
     `data-params='{"id":1,"name":"yii"}' data-status="ok"` を生成します。
     後者の例において、下位の配列に対して JSON 形式が使用されていることに注意してください。
   * 属性がデータ属性でない場合は、値は JSON エンコードされます。
     例えば、`['params' => ['id' => 1, 'name' => 'yii']` は `params='{"id":1,"name":"yii"}'` を生成します。


### CSS のクラスとスタイルを形成する <span id="forming-css"></span>

HTML タグのオプションを構築する場合、たいていは、デフォルトの値から始めて必要な修正をする、という方法をとります。
CSS クラスを追加または削除するために、次のコードを使用することが出来ます。

```php
$options = ['class' => 'btn btn-default'];

if ($type === 'success') {
    Html::removeCssClass($options, 'btn-default');
    Html::addCssClass($options, 'btn-success');
}

echo Html::tag('div', 'Pwede na', $options);

// $type が 'success' の場合、次のようにレンダリングされる
// <div class="btn btn-success">Pwede na</div>
```

配列形式を使って複数の CSS クラスを指定することも出来ます。

```php
$options = ['class' => ['btn', 'btn-default']];

echo Html::tag('div', 'Save', $options);
// '<div class="btn btn-default">Save</div>' をレンダリングする
```

クラスを追加・削除する際にも配列形式を使うことが出来ます。

```php
$options = ['class' => 'btn'];

if ($type === 'success') {
    Html::addCssClass($options, ['btn-success', 'btn-lg']);
}

echo Html::tag('div', 'Save', $options);
// '<div class="btn btn-success btn-lg">Save</div>' をレンダリングする
```

`Html::addCssClass()` はクラスの重複を防止しますので、同じクラスが二度出現するかも知れないと心配する必要はありません。

```php
$options = ['class' => 'btn btn-default'];

Html::addCssClass($options, 'btn-default'); // クラス 'btn-default' は既に存在する

echo Html::tag('div', 'Save', $options);
// '<div class="btn btn-default">Save</div>' をレンダリングする
```

CSS のクラスオプションを配列形式で指定する場合には、名前付きのキーを使ってクラスの論理的な目的を示すことが出来ます。
この場合、`Html::addCssClass()` で同じキーを持つクラスを指定しても無視されます。

```php
$options = [
    'class' => [
        'btn',
        'theme' => 'btn-default',
    ]
];

Html::addCssClass($options, ['theme' => 'btn-success']); // 'theme' キーは既に使用されている

echo Html::tag('div', 'Save', $options);
// '<div class="btn btn-default">Save</div>' をレンダリングする
```

CSS のスタイルも `style` 属性を使って、同じように設定することが出来ます。

```php
$options = ['style' => ['width' => '100px', 'height' => '100px']];

// style="width: 100px; height: 200px; position: absolute;" となる
Html::addCssStyle($options, 'height: 200px; position: absolute;');

// style="position: absolute;" となる
Html::removeCssStyle($options, ['width', 'height']);
```

[[yii\helpers\Html::addCssStyle()|addCssStyle()]] を使うときには、CSS プロパティの名前と値に対応する「キー-値」ペアの配列か、
または、`width: 100px; height: 200px;` のような文字列を指定することが出来ます。
この二つの形式は、[[yii\helpers\Html::cssStyleFromArray()|cssStyleFromArray()]] と [[yii\helpers\Html::cssStyleToArray()|cssStyleToArray()]] を使って、双方向に変換することが出来ます。
[[yii\helpers\Html::removeCssStyle()|removeCssStyle()]] メソッドは、削除すべきプロパティの配列を受け取ります。
プロパティが一つだけである場合は、文字列で指定することも出来ます。


### コンテントをエンコードおよびデコードする <span id="encoding-and-decoding-content"></span>

コンテントが適切かつ安全に HTML として表示されるためには、コンテント内の特殊文字がエンコードされなければなりません。
特殊文字のエンコードとデコードは、PHP では [htmlspecialchars](http://www.php.net/manual/ja/function.htmlspecialchars.php) と
[htmlspecialchars_decode](http://www.php.net/manual/ja/function.htmlspecialchars-decode.php) によって行われます。
これらのメソッドを直接使用する場合の問題は、文字エンコーディングと追加のフラグを毎回指定しなければならないことです。
フラグは毎回同じものであり、文字エンコーディングはセキュリティ問題を防止するためにアプリケーションのそれと一致すべきものですから、
Yii は二つのコンパクトかつ使いやすいメソッドを用意しました。

```php
$userName = Html::encode($user->name);
echo $userName;

$decodedUserName = Html::decode($userName);
```


## フォーム <span id="forms"></span>

フォームのマークアップを扱う仕事は、極めて面倒くさく、エラーを生じがちなものです。
このため、フォームのマークアップの仕事を助けるための一群のメソッドがあります。

> Note: モデルを扱っており、検証が必要である場合は、[[yii\widgets\ActiveForm|ActiveForm]] を使うことを検討してください。


### フォームを作成する <span id="creating-forms"></span>

フォームを開始するためには、次のように [[yii\helpers\Html::beginForm()|beginForm()]] メソッドを使うことが出来ます。

```php
<?= Html::beginForm(['order/update', 'id' => $id], 'post', ['enctype' => 'multipart/form-data']) ?>
```

最初の引数は、フォームが送信されることになる URL です。これは [[yii\helpers\Url::to()|Url::to()]] によって受け入れられる Yii のルートおよびパラメータの形式で指定することが出来ます。
第二の引数は使われるメソッドです。`post` がデフォルトです。第三の引数はフォームタグのオプションの配列です。
上記の場合では、POST リクエストにおけるフォーム・データのエンコーディング方法を `multipart/form-data` に変更しています。
これはファイルをアップロードするために必要とされます。

フォーム・タグを閉じるのは簡単です。

```php
<?= Html::endForm() ?>
```


### ボタン <span id="buttons"></span>

ボタンを生成するためには、次のコードを使うことが出来ます。

```php
<?= Html::button('押してね !', ['class' => 'teaser']) ?>
<?= Html::submitButton('送信', ['class' => 'submit']) ?>
<?= Html::resetButton('リセット', ['class' => 'reset']) ?>
```

最初の引数は、三つのメソッドのどれでも、ボタンのタイトルであり、第二の引数はオプションです。
タイトルはエンコードされませんので、エンド・ユーザからデータを取得する場合は [[yii\helpers\Html::encode()|Html::encode()]] を使ってエンコードしてください。


### インプット・フィールド <span id="input-fields"></span>

インプットのメソッドには二つのグループがあります。
一つは `active` から始まるものでアクティブ・インプットと呼ばれます。もう一方は `active` から始まらないものです。
アクティブ・インプットは、データを指定されたモデルと属性から取得しますが、通常のインプットでは、データは直接に指定されます。

最も汎用的なメソッドは以下のものです。

```php
タイプ、インプットの名前、値、オプション
<?= Html::input('text', 'username', $user->name, ['class' => $username]) ?>

タイプ、モデル、モデルの属性名、オプション
<?= Html::activeInput('text', $user, 'name', ['class' => $username]) ?>
```

インプットのタイプが前もって判っている場合は、ショートカットメソッドを使う方が便利です。

- [[yii\helpers\Html::buttonInput()]]
- [[yii\helpers\Html::submitInput()]]
- [[yii\helpers\Html::resetInput()]]
- [[yii\helpers\Html::textInput()]], [[yii\helpers\Html::activeTextInput()]]
- [[yii\helpers\Html::hiddenInput()]], [[yii\helpers\Html::activeHiddenInput()]]
- [[yii\helpers\Html::passwordInput()]] / [[yii\helpers\Html::activePasswordInput()]]
- [[yii\helpers\Html::fileInput()]], [[yii\helpers\Html::activeFileInput()]]
- [[yii\helpers\Html::textarea()]], [[yii\helpers\Html::activeTextarea()]]

ラジオとチェックボックスは、メソッドのシグニチャの面で少し異なっています。

```php
<?= Html::radio('agree', true, ['label' => '同意します']) ?>
<?= Html::activeRadio($model, 'agree', ['class' => 'agreement']) ?>

<?= Html::checkbox('agree', true, ['label' => '同意します']) ?>
<?= Html::activeCheckbox($model, 'agree', ['class' => 'agreement']) ?>
```

ドロップダウン・リストとリスト・ボックスは、次のようにしてレンダリングすることが出来ます。

```php
<?= Html::dropDownList('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeDropDownList($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>

<?= Html::listBox('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeListBox($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>
```

最初の引数はインプットの名前、第二の引数は現在選択されている値です。そして第三の引数は「キー-値」のペアであり、配列のキーはリストの値、配列の値はリストのラベルです。

複数の選択肢を選択できるようにしたい場合は、チェックボックス・リストが最適です。

```php
<?= Html::checkboxList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeCheckboxList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```

そうでない場合は、ラジオ・リストを使います。

```php
<?= Html::radioList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeRadioList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```


### ラベルとエラー <span id="labels-and-errors"></span>

インプットと同じように、ラベルを生成するメソッドが二つあります。モデルからデータを取るアクティブなラベルと、データを直接受け入れるアクティブでないラベルです。

```php
<?= Html::label('ユーザ名', 'username', ['class' => 'label username']) ?>
<?= Html::activeLabel($user, 'username', ['class' => 'label username']) ?>
```

一つまたは複数のモデルから取得したエラーを要約として表示するためには、次のコードを使うことが出来ます。

```php
<?= Html::errorSummary($posts, ['class' => 'errors']) ?>
```

個別のエラーを表示するためには、次のようにします。

```php
<?= Html::error($post, 'title', ['class' => 'error']) ?>
```


### インプットの名前と値 <span id="input-names-and-values"></span>

モデルに基づいてインプット・フィールドの名前、ID、値を取得するメソッドがあります。
これらは主として内部的に使用されるものですが、場合によっては重宝します。

```php
// Post[title]
echo Html::getInputName($post, 'title');

// post-title
echo Html::getInputId($post, 'title');

// '私の最初の投稿'
echo Html::getAttributeValue($post, 'title');

// $post->authors[0]
echo Html::getAttributeValue($post, '[0]authors[0]');
```

上記において、最初の引数はモデルであり、第二の引数は属性を示す式です。これは最も単純な形式においては属性名ですが、配列の添字を前 および/または 後に付けた属性名とすることも出来ます。配列の添字は主として表形式データ入力のために使用されます。

- `[0]content` は、表形式データ入力で使われます。表形式入力の最初のモデルの "content" 属性を表します。
- `dates[0]` は、"dates" 属性の最初の配列要素を表します。
- `[0]dates[0]` は、表形式入力の最初のモデルの "dates" 属性の最初の配列要素を表します。

前後の添字なしに属性名を取得するためには、次のコードを使うことが出来ます。

```php
// dates
echo Html::getAttributeName('dates[0]');
```


## スタイルとスクリプト <span id="styles-and-scripts"></span>

埋め込みのスタイルとスクリプトをラップするタグを生成するメソッドが二つあります。

```php
<?= Html::style('.danger { color: #f00; }') ?>

これは次の HTML を生成します。

<style>.danger { color: #f00; }</style>


<?= Html::script('alert("こんにちは!");', ['defer' => true]) ?>

これは次の HTML を生成します。

<script defer>alert("こんにちは!");</script>
```

CSS ファイルの外部スタイルをリンクしたい場合は、次のようにします。

```php
<?= Html::cssFile('@web/css/ie5.css', ['condition' => 'IE 5']) ?>

これは次の HTML を生成します。

<!--[if IE 5]>
    <link href="http://example.com/css/ie5.css" />
<![endif]-->
```

最初の引数は URL であり、第二の引数はオプションの配列です。通常のオプションに加えて、次のものを指定することが出来ます。

- `condition` - 指定された条件を使って `<link` を条件付きコメントで囲みます。
  条件付きコメントなんて、使う必要が無くなっちゃえば良いのにね ;)
- `noscript` - `true` に設定すると `<link` を `<noscript>` タグで囲むことができます。
  この場合、JavaScript がブラウザでサポートされていないか、ユーザが JavaScript を無効にしたときだけ、CSS がインクルードされます。

JavaScript ファイルをリンクするためには、次のようにします。

```php
<?= Html::jsFile('@web/js/main.js') ?>
```

CSS と同じように、最初の引数はインクルードされるファイルへのリンクを指定するものです。オプションを第二の引数として渡すことが出来ます。
オプションに置いて、`cssFile` のオプションと同じように、`condition` を指定することが出来ます。


## ハイパーリンク <span id="hyperlinks"></span>

ハイパーリンクを手軽に生成できるメソッドがあります。

```php
<?= Html::a('プロファイル', ['user/view', 'id' => $id], ['class' => 'profile-link']) ?>
```

最初の引数はタイトルです。これはエンコードされませんので、エンド・ユーザから取得したデータを使う場合は、`Html::encode()` でエンコードする必要があります。
第二の引数が、`<a` タグの `href` に入ることになるものです。
どのような値が受け入れられるかについて、詳細は [Url::to()](helper-url.md) を参照してください。
第三の引数は、タグのプロパティの配列です。

`mailto` リンクを生成する必要があるときは、次のコードを使うことが出来ます。

```php
<?= Html::mailto('連絡先', 'admin@example.com') ?>
```


## 画像 <span id="images"></span>

イメージタグを生成するためには次のようにします。

```php
<?= Html::img('@web/images/logo.png', ['alt' => '私のロゴ']) ?>

これは次の HTML を生成します。

<img src="http://example.com/images/logo.png" alt="私のロゴ" />
```

最初の引数は、[エイリアス](concept-aliases.md) 以外にも、ルートとパラメータ、または URL を受け入れることが出来ます。[Url::to()](helper-url.md) と同様です。


## リスト <span id="lists"></span>

順序なしリストは、次のようにして生成することが出来ます。

```php
<?= Html::ul($posts, ['item' => function($item, $index) {
    return Html::tag(
        'li',
        $this->render('post', ['item' => $item]),
        ['class' => 'post']
    );
}]) ?>
```

順序付きリストを生成するためには、代りに `Html:ol()` を使ってください。
