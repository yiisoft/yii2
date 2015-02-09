テンプレートエンジンを使う
==========================

> Note|注意: この節はまだ執筆中です。

デフォルトでは、Yii は PHP をテンプレート言語として使いますが、[Twig](http://twig.sensiolabs.org/) や [Smarty](http://www.smarty.net/) などの他のレンダリングエンジンをサポートするように Yii を構成することが出来ます。

`view` コンポーネントがビューのレンダリングに責任を持っています。
このコンポーネントのビヘイビアを構成することによって、カスタムテンプレートエンジンを追加することが出来ます。

```php
[
    'components' => [
        'view' => [
            'class' => 'yii\web\View',
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'cachePath' => '@runtime/Twig/cache',
                    // twig のオプションの配列
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => ['html' => '\yii\helpers\Html'],
                    'uses' => ['yii\bootstrap'],
                ],
                // ...
            ],
        ],
    ],
]
```

上記のコードにおいては、Smarty と Twig の両者がビューファイルによって使用可能なものとして構成されています。
しかし、これらのエクステンションをプロジェクトで使うためには、`composer.json` ファイルも修正して、これらのエクステンションを含める必要があります。

```
"yiisoft/yii2-smarty": "*",
"yiisoft/yii2-twig": "*",
```

上のコードを `composer.json` の `require` セクションに追加します。
変更をファイルに保存した後、コマンドラインで `composer update --prefer-dist` を実行することによってエクステンションをインストールすることが出来ます。

Twig
----

Twig を使うためには、`.twig` という拡張子を持つファイルにテンプレートを作成しなければなりません
(別のファイル拡張子を使っても構いませんが、それに対応してコンポーネントの構成を変更しなければなりません)。
通常のビューファイルと違って、Twig を使うときは、コントローラで `$this->render()` を呼ぶときに拡張子を含めなければなりません。

```php
return $this->render('renderer.twig', ['username' => 'Alex']);
```

### テンプレートの構文

Twig の基礎を学ぶための最善のリソースは、[twig.sensiolabs.org](http://twig.sensiolabs.org/documentation) にある公式ドキュメントです。
それに追加して、下記に説明する Yii 固有の拡張構文があります。

#### メソッドとファンクションの呼び出し

結果が必要な場合は、次の構文を使ってメソッドや関数を呼び出すことが出来ます。

```
{% set result = my_function({'a' : 'b'}) %}
{% set result = myObject.my_function({'a' : 'b'}) %}
```

結果を変数に代入する代りに echo したい場合は、こうします。

```
{{ my_function({'a' : 'b'}) }}
{{ myObject.my_function({'a' : 'b'}) }}
```

結果を必要としない場合は、`void` ラッパーを使うべきです。

```
{{ void(my_function({'a' : 'b'})) }}
{{ void(myObject.my_function({'a' : 'b'})) }}
```

#### オブジェクトのプロパティを設定する

`set` と呼ばれる特別な関数を使って、オブジェクトのプロパティを設定することが出来ます。
例えば、テンプレート中の下記のコードはページタイトルを変更します。

```
{{ set(this, 'title', 'New title') }}
```

#### 名前空間とクラスをインポートする

追加のクラスと名前空間をテンプレートの中でインポートすることが出来ます。

```
名前空間のインポート:
{{ use('/app/widgets') }}

クラスのインポート:
{{ use('/yii/widgets/ActiveForm') }}

エイリアス化してクラスをインポート:
{{ use({'alias' : '/app/widgets/MyWidget'}) }}
```

#### 他のテンプレートを参照する

`include` と `extends` 文によるテンプレートの参照には二つの方法があります。

```
{% include "comment.twig" %}
{% extends "post.twig" %}

{% include "@app/views/snippets/avatar.twig" %}
{% extends "@app/views/layouts/2columns.twig" %}
```

最初の場合では、現在のテンプレートのパスからの相対的なパスでビューを探します。
`comment.twig` と `post.twig` は、現在レンダリングされているテンプレートと同じディレクトリで探されます。

第二の場合では、パスエイリアスを使います。
`@app` のような全ての Yii のエイリアスがデフォルトで利用できます。

#### ウィジェット

このエクステンションは、ウィジェットを簡単に使えるように、ウィジェットの構文を関数呼び出しに変換します。

```
{{ use('yii/bootstrap') }}
{{ nav_bar_begin({
    'brandLabel': 'My Company',
}) }}
    {{ nav_widget({
        'options': {
            'class': 'navbar-nav navbar-right',
        },
        'items': [{
            'label': 'Home',
            'url': '/site/index',
        }]
    }) }}
{{ nav_bar_end() }}
```

上記のテンプレートでは、`nav_bar_begin`、`nav_bar_end` また `nav_widget` は、二つの部分から構成されます。
最初の部分は、小文字とアンダースコアに変換されたウィジェットの名前です。
`NavBar` は `nav_bar`、`Nav` は `nav` に変換されます。
第二の部分の `_begin`、`_end` および `_widget` は、ウィジェットのメソッド `::begin()`、`::end()` および `::widget()` と同じものです。

もっと汎用的な `Widget::end()` を実行する `widget_end()` も使うことが出来ます。

#### アセット

アセットは次の方法で登録することが出来ます。

```
{{ use('yii/web/JqueryAsset') }}
{{ register_jquery_asset() }}
```

上記のコードで、`register` は、アセットを扱うことを指定し、`jquery_asset` は、既に `use` でインポート済みの `JqueryAsset` クラスに翻訳されます。

#### フォーム

フォームは次のようにして構築することが出来ます。

```
{{ use('yii/widgets/ActiveForm') }}
{% set form = active_form_begin({
    'id' : 'login-form',
    'options' : {'class' : 'form-horizontal'},
}) %}
    {{ form.field(model, 'username') | raw }}
    {{ form.field(model, 'password').passwordInput() | raw }}

    <div class="form-group">
        <input type="submit" value="ログイン" class="btn btn-primary" />
    </div>
{{ active_form_end() }}
```


#### URL

URL を構築するのに使える二つの関数があります。

```php
<a href="{{ path('blog/view', {'alias' : post.alias}) }}">{{ post.title }}</a>
<a href="{{ url('blog/view', {'alias' : post.alias}) }}">{{ post.title }}</a>
```

`path` は相対的な URL を生成し、`url` は絶対的な URL を生成します。
内部的には、両者とも、[[\yii\helpers\Url]] を使っています。

#### 追加の変数

Twig のテンプレート内では、次の変数が常に定義されています。

- `app` - `\Yii::$app` オブジェクト
- `this` - 現在の `View` オブジェクト

### 追加の構成

Yii Twig エクステンションは、あなた自身の構文を定義して、通常のヘルパクラスをテンプレートに導入することを可能にしています。
構成のオプションを見ていきましょう。

#### グローバル

アプリケーション構成の `globals` 変数によって、グローバルなヘルパや変数を追加することが出来ます。
Yii のヘルパとあなた自身の変数を定義することが出来ます。

```php
'globals' => [
    'html' => '\yii\helpers\Html',
    'name' => 'Carsten',
    'GridView' => '\yii\grid\GridView',
],
```

いったん構成してしまえば、テンプレートの中で以下のようにグローバルを使用することが出来ます。

```
Hello, {{name}}! {{ html.a('ログインしてください', 'site/login') | raw }}.

{{ GridView.widget({'dataProvider' : provider}) | raw }}
```

#### 関数

追加の関数を次のようにして定義することが出来ます。

```php
'functions' => [
    'rot13' => 'str_rot13',
    'truncate' => '\yii\helpers\StringHelper::truncate',
],
```

テンプレートでは、次のようにして使うことが出来ます。

```
`{{ rot13('test') }}`
`{{ truncate(post.text, 100) }}`
```

#### フィルタ

追加のフィルタをアプリケーション構成の `filters` オプションによって追加することが出来ます。

```php
'filters' => [
    'jsonEncode' => '\yii\helpers\Json::encode',
],
```

テンプレートの中では、次の構文を使ってフィルタを適用することが出来ます。

```
{{ model|jsonEncode }}
```


Smarty
------

Smarty を使うためには、`.tpl` という拡張子を持つファイルにテンプレートを作成しなければなりません
(別のファイル拡張子を使っても構いませんが、それに対応してコンポーネントの構成を変更しなければなりません)。
通常のビューファイルと違って、Smarty を使うときは、コントローラで `$this->render()` を呼ぶときに拡張子を含めなければなりません。

```php
return $this->render('renderer.tpl', ['username' => 'Alex']);
```

### テンプレートの構文

Smarty のテンプレートの構文を学ぶための最善のリソースは、[www.smarty.net](http://www.smarty.net/docs/ja/) にある公式ドキュメントです。
それに追加して、下記に説明する Yii 固有の拡張構文があります。

#### オブジェクトのプロパティを設定する

`set` と呼ばれる特別な関数を使って、ビューとコントローラの一般的なプロパティを設定することが出来ます。
現在サポートされているプロパティは、`title`、`theme` および `layout` です。

```
{set title="My Page"}
{set theme="frontend"}
{set layout="main.tpl"}
```

タイトルについては、専用のブロックもあります。

```
{title}My Page{/title}
```

#### メタタグを設定する

メタタグは次のようにして設定することが出来ます。

```
{meta keywords="Yii,PHP,Smarty,framework"}
```

`description` のためには専用のブロックもあります。

```
{description}Smarty エクステンションについて説明するページです{/description}
```

#### オブジェクトのメソッドを呼び出す

場合によっては、オブジェクトのメソッドを呼び出す必要があるでしょう。

#### スタティックなクラスをインポートし、ウィジェットを関数およびブロックとして使用する

追加のスタティックなクラスをテンプレートの中でインポートすることが出来ます。

```
{use class="yii\helpers\Html"}
{Html::mailto('eugenia@example.com')}
```

必要であれば、カスタムエイリアスを設定することも出来ます。

```
{use class="yii\helpers\Html" as="Markup"}
{Markup::mailto('eugenia@example.com')}
```

このエクステンションは、ウィジェットを簡単に使えるように、ウィジェットの構文を関数呼び出しまたはブロックに変換します。
通常のウィジェットについては、次のように関数を使うことが出来ます。

```
{use class='@yii\grid\GridView' type='function'}
{GridView dataProvider=$provider}
```

ActiveForm のように `begin` および `end` メソッドを持つウィジェットについては、ブロックを使うほうが良いでしょう。

```
{use class='yii\widgets\ActiveForm' type='block'}
{ActiveForm assign='form' id='login-form' action='/form-handler' options=['class' => 'form-horizontal']}
    {$form->field($model, 'firstName')}
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <input type="submit" value="ログイン" class="btn btn-primary" />
        </div>
    </div>
{/ActiveForm}
```

特定のウィジェットを多用する場合は、それをアプリケーション構成の中で宣言して、テンプレートから `{use class` の呼び出しを削除するのが良いアイデアです。

```php
'components' => [
    'view' => [
        // ...
        'renderers' => [
            'tpl' => [
                'class' => 'yii\smarty\ViewRenderer',
                'widgets' => [
                    'blocks' => [
                        'ActiveForm' => '\yii\widgets\ActiveForm',
                    ],
                ],
            ],
        ],
    ],
],
```

#### 他のテンプレートを参照する

`include` と `extends` 文によるテンプレートの参照には、主として二つの方法があります。


```
{include 'comment.tpl'}
{extends 'post.tpl'}

{include '@app/views/snippets/avatar.tpl'}
{extends '@app/views/layouts/2columns.tpl'}
```

最初の場合では、現在のテンプレートのパスからの相対的なパスでビューを探します。
`comment.tpl` と `post.tpl` は、現在レンダリングされているテンプレートと同じディレクトリで探されます。

第二の場合では、パスエイリアスを使います。
`@app` のような全ての Yii のエイリアスがデフォルトで利用できます。

#### CSS、JavaScript およびアセットバンドル

JavaScript と CSS のファイルを登録するためには、次の構文を使うことが出来ます。

```
{registerJsFile url='http://maps.google.com/maps/api/js?sensor=false' position='POS_END'}
{registerCssFile url='@assets/css/normalizer.css'}
```

JavaScript と CSS をテンプレートに直接書きたい場合は、便利なブロックがあります。
```
{registerJs key='show' position='POS_LOAD'}
    $("span.show").replaceWith('<div class="show">');
{/registerJs}

{registerCss}
div.header {
    background-color: #3366bd;
    color: white;
}
{/registerCss}
```

アセットバンドルは次のようにして登録することが出来ます。

```
{use class="yii\web\JqueryAsset"}
{JqueryAsset::register($this)|void}
```

ここではメソッド呼び出しの結果が必要ないので `void` 修飾子を使っています。

#### URL

URL を構築するのに使える二つの関数があります。

```php
<a href="{path route='blog/view' alias=$post.alias}">{$post.title}</a>
<a href="{url route='blog/view' alias=$post.alias}">{$post.title}</a>
```

`path` は相対的な URL を生成し、`url` は絶対的な URL を生成します。
内部的には、両者とも、[[\yii\helpers\Url]] を使っています。

#### 追加の変数

Smarty のテンプレート内では、次の変数が常に定義されています。

- `app` - `\Yii::$app` オブジェクト
- `this` - 現在の `View` オブジェクト

#### 構成情報のパラメータにアクセスする

アプリケーションにおいて `Yii::$app->params->something` によって取得できるパラメータは、次のようにして使用することが出来ます。

```
`{#something#}`
```
