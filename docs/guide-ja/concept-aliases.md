エイリアス
=======

ファイルパスや URL を表すのにエイリアスを使用すると、あなたはプロジェクト内で絶対パスや URL をハードコードする必要がなくなります。エイリアスは、通常のファイルパスや URL と区別するために、 `@` 文字で始まる必要があります。Yii はすでに利用可能な多くの事前定義エイリアスを持っています。
たとえば、 `@yii` というエイリアスは Yii フレームワークのインストールパスを表し、 `@web` は現在実行中の Web アプリケーションのベース URL を表します。


エイリアスの定義 <span id="defining-aliases"></span>
----------------

[[Yii::setAlias()]] を呼び出すことにより、ファイルパスまたは URL のエイリアスを定義することができます。

```php
// ファイルパスのエイリアス
Yii::setAlias('@foo', '/path/to/foo');

// URL のエイリアス
Yii::setAlias('@bar', 'http://www.example.com');
```

> 補足: エイリアスされているファイルパスや URL は、必ずしも実在するファイルまたはリソースを参照しない場合があります。

定義済みのエイリアスがあれば、スラッシュ `/` に続けて 1 つ以上のパスセグメントを追加することで（[[Yii::setAlias()]]
の呼び出しを必要とせずに) 新しいエイリアスを導出することができます。 [[Yii::setAlias()]] を通じて定義されたエイリアスは
*ルートエイリアス* となり、それから派生したエイリアスは *派生エイリアス* になります。たとえば、 `@foo` がルートエイリアスなら、
`@foo/bar/file.php` は派生エイリアスです。

エイリアスを、他のエイリアス (ルートまたは派生のいずれか) を使用して定義することができます:

```php
Yii::setAlias('@foobar', '@foo/bar');
```

ルートエイリアスは通常、 [ブートストラップ](runtime-bootstrapping.md) 段階で定義されます。
たとえば、[エントリスクリプト](structure-entry-scripts.md) で [[Yii::setAlias()]] を呼び出すことができます。
便宜上、 [アプリケーション](structure-applications.md) は、`aliases` という名前の書き込み可能なプロパティを提供しており、
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


エイリアスの解決 <span id="resolving-aliases"></span>
-----------------

[[Yii::getAlias()]] を呼び出して、ルートエイリアスが表すファイルパスまたは URL を解決することができます。
同メソッドで、派生エイリアスを対応するファイルパスまたは URL に解決することもできます。

```php
echo Yii::getAlias('@foo');               // /path/to/foo を表示
echo Yii::getAlias('@bar');               // http://www.example.com を表示
echo Yii::getAlias('@foo/bar/file.php');  // /path/to/foo/bar/file.php を表示
```

派生エイリアスによって表されるパスや URL は、派生エイリアス内のルートエイリアス部分を、対応するパス/URL
で置換して決定されます。

> 補足: [[Yii::getAlias()]] メソッドは、 結果のパスや URL が実在するファイルやリソースを参照しているかをチェックしません。

ルートエイリアス名にはスラッシュ `/` 文字を含むことができます。 [[Yii::getAlias()]] メソッドは、
エイリアスのどの部分がルートエイリアスであるかを賢く判別し、正確に対応するファイルパスや URL を決定します:

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
Yii::getAlias('@foo/test/file.php');  // /path/to/foo/test/file.php を表示
Yii::getAlias('@foo/bar/file.php');   // /path2/bar/file.php を表示
```

もし `@foo/bar` がルートエイリアスとして定義されていなければ、最後のステートメントは `/path/to/foo/bar/file.php` を表示します。


エイリアスの使用 <span id="using-aliases"></span>
-------------

エイリアスは、それをパスや URL に変換するための [[Yii::getAlias()​]] の呼び出しがなくても、Yii の多くの場所でみられます。
たとえば、 [[yii\caching\FileCache::cachePath]] は、ファイルパスとファイルパスを表すエイリアスの両方を受け入れることができ、
接頭辞 `@` によって、エイリアスとファイルパスを区別することができます。

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

プロパティやメソッドのパラメータがエイリアスをサポートしているかどうかは、API ドキュメントに注意を払ってください。


事前定義されたエイリアス <span id="predefined-aliases"></span>
------------------

Yii では、一般的に使用されるファイルのパスと URL を簡単に参照できるよう、エイリアスのセットが事前に定義されています:

- `@yii`, `BaseYii.php` ファイルがあるディレクトリ (フレームワークディレクトリとも呼ばれます)
- `@app`, 現在実行中のアプリケーションの [[yii\base\Application::basePath|ベースパス]]
- `@runtime`, 現在実行中のアプリケーションの [[yii\base\Application::runtimePath|ランタイムパス]] 。デフォルトは `@app/runtime` 。
- `@webroot`, 現在実行中の Web アプリケーションの Web ルートディレクトリ。エントリスクリプトを含むディレクトリをもとに決定されます。
- `@web`, 現在実行中の Web アプリケーションのベース URL。これは、 [[yii\web\Request::baseUrl]] と同じ値を持ちます。
- `@vendor`, [[yii\base\Application::vendorPath|Composerのベンダーディレクトリ]] 。デフォルトは `@app/vendor` 。
- `@bower`, [bower パッケージ](http://bower.io/) が含まれるルートディレクトリ。デフォルトは `@vendor/bower` 。
- `@npm`, [npm パッケージ](https://www.npmjs.org/) が含まれるルートディレクトリ。デフォルトは `@vendor/npm` 。

`@yii` エイリアスは [エントリスクリプト](structure-entry-scripts.md) に `Yii.php` ファイルを読み込んだ時点で定義されます。
エイリアスの残りの部分は、アプリケーションのコンストラクタ内で、アプリケーションの [構成情報](concept-configurations.md) を適用するときに定義されます。

エクステンションのエイリアス <span id="extension-aliases"></span>
-----------------

Composer でインストールされる各 [エクステンション](structure-extensions.md) ごとに、エイリアスが自動的に定義されます。
各エイリアスは、その `composer.json` ファイルで宣言された、エクステンションのルート名前空間にちなんで名付けられており、
それらは、パ​​ッケージのルートディレクトリを表します。たとえば、あなたが `yiisoft/yii2-jui` エクステンションをインストールしたとすると、
自動的に `@yii/jui` というエイリアスができ、 [ブートストラップ](runtime-bootstrapping.md) 段階で、次のと同等のものとして定義されます:

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```

