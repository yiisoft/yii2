Url ヘルパ
==========

Url ヘルパは URL を管理するための一連のスタティックメソッドを提供します。


## よく使う URL を取得する <span id="getting-common-urls"></span>

よく使う URL を取得するために使うことが出来るメソッドが二つあります。
すなわち、ホーム URL と、現在のリクエストのベース URL を取得するメソッドです。
ホーム URL を取得するためには、次のようにします。

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

パラメータが渡されない場合は、生成される URL は相対 URL になります。
パラメータとして `true` を渡せば、現在のスキーマの絶対 URL を取得することが出来ます。
または、スキーマ (`http`, `https`) を明示的に指定しても構いません。

現在のリクエストのベース URL を取得するためには、次のようにします。
 
```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

このメソッドの唯一のパラメータは、`Url::home()` の場合と全く同じ動作をします。


## URL を生成する <span id="creating-urls"></span>

与えられたルートへの URL を生成するためには、`Url::toRoute()` メソッドを使います。
このメソッドは、[[\yii\web\UrlManager]] を使って URL を生成します。

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```

ルートは、文字列として指定することが出来ます (例えば、`site/index`)。
または、生成される URL に追加のクエリパラメータを指定したい場合は、配列を使うことも出来ます。
配列の形式は、以下のようにしなければなりません。

```php
// /index.php?r=site/index&param1=value1&param2=value2 を生成
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

アンカーの付いた URL を生成したい場合は、`#` パラメータを持つ配列を使うことが出来ます。例えば、

```php
// /index.php?r=site/index&param1=value1#name を生成
['site/index', 'param1' => 'value1', '#' => 'name']
```

ルートは、絶対ルートか相対ルートかのどちらかです。
絶対ルートは先頭にスラッシュを持ち (例えば `/site/index`)、相対ルートは持ちません (例えば `site/index` または `index`)。
相対ルートは次の規則に従って絶対ルートに変換されます。

- ルートが空文字列である場合は、現在の [[yii\web\Controller::route|ルート]] が使用されます。
- ルートがスラッシュを全く含まない場合は (例えば `index`)、カレントコントローラのアクション ID であると見なされて、カレントコントローラの [[\yii\web\Controller::uniqueId|uniqueId]] が前置されます。
- ルートが先頭にスラッシュを含まない場合は (例えば `site/index`)、カレントモジュールに対する相対ルートと見なされて、カレントモジュールの [[\yii\base\Module::uniqueId|uniqueId]] が前置されます。

バージョン 2.0.2 以降では、[エイリアス](concept-aliases.md) の形式でルートを指定することが出来ます。
その場合は、エイリアスが最初に実際のルートに変換され、そのルートが上記の規則に従って絶対ルートに変換されます。

以下に、このメソッドの使用例をいくつか挙げます。

```php
// /index.php?r=site/index
echo Url::toRoute('site/index');

// /index.php?r=site/index&src=ref1#name
echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     エイリアス "@postEdit" は "post/edit" と定義されていると仮定
echo Url::toRoute(['@postEdit', 'id' => 100]);

// http://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', true);

// https://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', 'https');
```

もうひとつ、[[toRoute()]] と非常によく似た `Url::to()` というメソッドがあります。
唯一の違いは、このメソッドはルートを配列として指定することを要求する、という点です。
文字列が与えられた場合は、URL として扱われます。

最初の引数は、次のいずれかを取り得ます。

- 配列: URL を生成するために [[toRoute()]] が呼び出されます。例えば、`['site/index']`、`['post/index', 'page' => 2]`。
  ルートの指定方法の詳細については [[toRoute()]] を参照してください。
- `@` で始まる文字列: これはエイリアスとして扱われ、エイリアスに対応する文字列が返されます。
- 空文字列: 現在リクエストされている URL が返されます。
- 通常の文字列: その通りのものとして扱われます。

`$scheme` (文字列または `true`) が指定された場合は、ホスト情報 ([[\yii\web\UrlManager::hostInfo]] から取得されます) を伴う絶対 URL が返されます。
`$url` が既に絶対 URL であった場合には、そのスキームが指定されたものに置き換えられます。

下記にいくつかの用例を挙げます。

```php
// /index.php?r=site/index
echo Url::to(['site/index']);

// /index.php?r=site/index&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     エイリアス "@postEdit" が "post/edit" と定義されていると仮定
echo Url::to(['@postEdit', 'id' => 100]);

// 現在リクエストされている URL
echo Url::to();

// /images/logo.gif
echo Url::to('@web/images/logo.gif');

// images/logo.gif
echo Url::to('images/logo.gif');

// http://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', true);

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', 'https');
```

バージョン 2.0.3 以降では、[[yii\helpers\Url::current()]] を使って、現在リクエストされているルートと GET パラメータに基づいて URL を生成することが出来ます。
`$params` パラメータを渡して、GET パラメータの中のいくつかを修正したり削除したり、または新しい GET パラメータを追加したりすることが出来ます。
例えば、

```php
// $_GET が ['id' => 123, 'src' => 'google'] であり、現在のルートが "post/view" であると仮定

// /index.php?r=post/view&id=123&src=google
echo Url::current();

// /index.php?r=post/view&id=123
echo Url::current(['src' => null]);
// /index.php?r=post/view&id=100&src=google
echo Url::current(['id' => 100]);
```


## URL を記憶する <span id="remember-urls"></span>

URL を記憶して、後に続く一連のリクエストの一つを処理するときに、記憶した URL を使わなければならないという場合があります。
これは、次のようにして達成することが出来ます。
 
```php
// 現在の URL を記憶する
Url::remember();

// 指定された URL を記憶する。引数の形式は Url::to() を参照。
Url::remember(['product/view', 'id' => 42]);

// 指定された名前で URL を記憶する。
Url::remember(['product/view', 'id' => 42], 'product');
```

次のリクエストで、記憶された URL を次のようにして取得することが出来ます。

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```

## 相対 URL かどうかチェックする <span id="checking-relative-urls"></span>

URL が相対 URL であること、すなわち、URL がホスト情報の部分を持っていないことを確かめるために、次のコードを使うことが出来ます。

```php
$isRelative = Url::isRelative('test/it');
```
