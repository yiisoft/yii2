エイリアス
==========

ファイル・パスや URL を表すのにエイリアスを使用すると、あなたはプロジェクト内で絶対パスや URL をハードコードする必要がなくなります。
エイリアスは、通常のファイル・パスや URL と区別するために、 `@` 文字で始まる必要があります。
先頭に `@` を付けずに定義されたエイリアスは、`@` 文字が先頭に追加されます。

Yii はすでに利用可能な多くの事前定義エイリアスを持っています。
たとえば、 `@yii` というエイリアスは Yii フレームワークのインストール・パスを表し、`@web` は現在実行中のウェブ・アプリケーションのベース URL を表します。

エイリアスを定義する <span id="defining-aliases"></span>
--------------------

[[Yii::setAlias()]] を呼び出すことにより、ファイル・パスまたは URL のエイリアスを定義することができます。

```php
// ファイル・パスのエイリアス
Yii::setAlias('@foo', '/path/to/foo');

// URL のエイリアス
Yii::setAlias('@bar', 'http://www.example.com');

// \foo\Bar クラスを保持する具体的なファイルのエイリアス
Yii::setAlias('@foo/Bar.php', '/definitely/not/foo/Bar.php');
```

> Note: エイリアスされているファイル・パスや URL は、必ずしも実在するファイルまたはリソースを参照しない場合があります。

定義済みのエイリアスがあれば、スラッシュ `/` に続けて 1 つ以上のパス・セグメントを追加することで（[[Yii::setAlias()]]
の呼び出しを必要とせずに) 新しいエイリアスを導出することができます。 [[Yii::setAlias()]] を通じて定義されたエイリアスは
*ルート・エイリアス* となり、それから派生したエイリアスは *派生エイリアス* になります。たとえば、 `@foo` がルート・エイリアスなら、
`@foo/bar/file.php` は派生エイリアスです。

エイリアスを、他のエイリアス (ルートまたは派生のいずれか) を使用して定義することができます:

```php
Yii::setAlias('@foobar', '@foo/bar');
```

ルート・エイリアスは通常、 [ブートストラップ](runtime-bootstrapping.md) 段階で定義されます。
たとえば、[エントリ・スクリプト](structure-entry-scripts.md) で [[Yii::setAlias()]] を呼び出すことができます。
便利なように、 [アプリケーション](structure-applications.md) は、`aliases` という名前の書き込み可能なプロパティを提供しており、
それをアプリケーションの [構成情報](concept-configurations.md) で設定することが可能です。

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/path/to/foo',
        '@bar' => 'http://www.example.com',
    ],
];
```


エイリアスを解決する <span id="resolving-aliases"></span>
--------------------

[[Yii::getAlias()]] を呼び出して、ルート・エイリアスが表すファイル・パスまたは URL を解決することができます。
同メソッドで、派生エイリアスを対応するファイル・パスまたは URL に解決することもできます。

```php
echo Yii::getAlias('@foo');               // /path/to/foo を表示
echo Yii::getAlias('@bar');               // http://www.example.com を表示
echo Yii::getAlias('@foo/bar/file.php');  // /path/to/foo/bar/file.php を表示
```

派生エイリアスによって表されるパスや URL は、
派生エイリアス内のルート・エイリアス部分を対応するパスや URL で置換して決定されます。

> Note: [[Yii::getAlias()]] メソッドは、 結果のパスや URL が実在するファイルやリソースを参照しているかをチェックしません。


ルート・エイリアス名にはスラッシュ `/` 文字を含むことができます。 [[Yii::getAlias()]] メソッドは、
エイリアスのどの部分がルート・エイリアスであるかを賢く判別し、
正確に対応するファイル・パスや URL を決定します:

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
Yii::getAlias('@foo/test/file.php');  // /path/to/foo/test/file.php を表示
Yii::getAlias('@foo/bar/file.php');   // /path2/bar/file.php を表示
```

もし `@foo/bar` がルート・エイリアスとして定義されていなければ、最後のステートメントは `/path/to/foo/bar/file.php` を表示します。


エイリアスを使用する <span id="using-aliases"></span>
--------------------

Yii では、多くの場所で、パスや URL に変換する [[Yii::getAlias()]] を呼び出す必要なく、
エイリアスが認識されます。
たとえば、 [[yii\caching\FileCache::cachePath]] は、ファイル・パスとファイル・パスを表すエイリアスの両方を受け入れることが出来ます。
これは、接頭辞 `@` によって、ファイル・パスとエイリアスを区別することが出来るためです。

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

プロパティやメソッドのパラメータがエイリアスをサポートしているかどうかは、API ドキュメントに注意を払ってください。


事前定義されたエイリアス <span id="predefined-aliases"></span>
------------------------

Yii では、一般的に使用されるファイルのパスと URL を簡単に参照できるよう、エイリアスのセットが事前に定義されています:

- `@yii`, `BaseYii.php` ファイルがあるディレクトリ (フレームワーク・ディレクトリとも呼ばれます)
- `@app`, 現在実行中のアプリケーションの [[yii\base\Application::basePath|ベース・パス]]
- `@runtime`, 現在実行中のアプリケーションの [[yii\base\Application::runtimePath|ランタイム・パス]] 。デフォルトは `@app/runtime` 。
- `@webroot`, 現在実行中のウェブ・アプリケーションのウェブ・ルート・ディレクトリ。
  エントリス・クリプトを含むディレクトリによって決定されます。
- `@web`, 現在実行中のウェブ・アプリケーションのベース URL。これは、 [[yii\web\Request::baseUrl]] と同じ値を持ちます。
- `@vendor`, [[yii\base\Application::vendorPath|Composer のベンダー・ディレクトリ]] 。デフォルトは `@app/vendor` 。
- `@bower`, [bower パッケージ](http://bower.io/) が含まれるルート・ディレクトリ。デフォルトは `@vendor/bower` 。
- `@npm`, [npm パッケージ](https://www.npmjs.org/) が含まれるルート・ディレクトリ。デフォルトは `@vendor/npm` 。

`@yii` エイリアスは [エントリ・スクリプト](structure-entry-scripts.md) に `Yii.php` ファイルを読み込んだ時点で定義されます。
エイリアスの残りの部分は、アプリケーションのコンストラクタ内で、アプリケーションの [構成情報](concept-configurations.md)
を適用するときに定義されます。


エクステンションのエイリアス <span id="extension-aliases"></span>
----------------------------

Composer でインストールされる [エクステンション](structure-extensions.md) のそれぞれに対してエイリアスが自動的に定義されます。
各エイリアスは、その `composer.json` ファイルで宣言されたエクステンションのルート名前空間にちなんで名付けられ、
パッケージのルート・ディレクトリを表します。たとえば、あなたが `yiisoft/yii2-jui` エクステンションをインストールしたとすると、
自動的に `@yii/jui` というエイリアスが [ブートストラップ](runtime-bootstrapping.md) 段階で定義されます。これは次のものと等価です。

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```
