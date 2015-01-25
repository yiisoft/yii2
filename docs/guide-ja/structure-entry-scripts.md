エントリスクリプト
==================

エントリスクリプトは、アプリケーションのブートストラップ過程のチェーンにおける最初の環です。
アプリケーションは (ウェブアプリケーションであれ、コンソールアプリケーションであれ）単一のエントリスクリプトを持ちます。
エンドユーザはエントリスクリプトに対してリクエストを発行し、エントリスクリプトはアプリケーションのインスタンスを作成して、それにリクエストを送付します。

ウェブアプリケーションのエントリスクリプトは、エンドユーザからアクセス出来るように、ウェブからのアクセスが可能なディレクトリの下に保管されなければなりません。
たいていは `index.php` と名付けられますが、ウェブサーバが見つけることが出来る限り、どのような名前を使っても構いません。

コンソールアプリケーションのエントリスクリプトは、通常は、アプリケーションの [ベースパス](structure-applications.md) の下に保管され、`yii` と名付けられます (`.php` の拡張子を伴います) 。
これは、ユーザが `./yii <route> [引数] [オプション]` というコマンドによってコンソールアプリケーションを走らせることが出来るようにするためのスクリプトであり、実行可能なパーミッションを与えられるべきものです。

エントリスクリプトは主として次の仕事をします。

* グローバルな定数を定義する;
* [Composer のオートローダ](http://getcomposer.org/doc/01-basic-usage.md#autoloading) を登録する。
* [[Yii]] クラスファイルをインクルードする。
* アプリケーションの構成情報を読み出す。
* [アプリケーション](structure-applications.md) のインスタンスを生成して構成する。
* [[yii\base\Application::run()]] を呼んで、受け取ったリクエストを処理する。


## ウェブアプリケーション<span id="web-applications"></span>

次に示すのが、[ベーシックウェブアプリケーションテンプレート](start-installation.md) のエントリスクリプトです。

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// Composer のオートローダを登録
require(__DIR__ . '/../vendor/autoload.php');

// Yii クラスファイルをインクルード
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// アプリケーションの構成情報を読み出す
$config = require(__DIR__ . '/../config/web.php');

// アプリケーションを作成し、構成して、走らせる
(new yii\web\Application($config))->run();
```


## コンソールアプリケーション<span id="console-applications"></span>

同様に、下記がコンソールアプリケーションのエントリスクリプトです:le application:

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

// fcgi が既定では STDIN と STDOUT を定義していないので
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

// Composer のオートローダを登録
require(__DIR__ . '/vendor/autoload.php');

// Yii クラスファイルをインクルード
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

// アプリケーションの構成情報を読み出す
$config = require(__DIR__ . '/config/console.php');

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```


## 定数を定義する<span id="defining-constants"></span>

グローバルな定数を定義するには、エントリスクリプトが最善の場所です。
Yii は下記の三つの定数をサポートしています:

* `YII_DEBUG`: アプリケーションがデバッグモードで走るかどうかを規定します。
  デバッグモードにおいては、アプリケーションはより多くのログ情報を保持し、例外が投げられたときに、より詳細なエラーのコールスタックを表示します。
  この理由により、デバッグモードは主として開発時に使用されるべきものとなります。
  `YII_DEBUG` の既定値は false です。
* `YII_ENV`: どういう環境でアプリケーションが走るかを規定します。
  詳細については、[構成情報](concept-configurations.md#environment-constants) の節で説明されます。
  `YII_ENV` の既定値は `'prod'` です。これはアプリケーションが本番環境で走ることを意味します。
* `YII_ENABLE_ERROR_HANDLER`: Yii によって提供されるエラーハンドラを有効にするかどうかを規定します。
  この定数の既定値は true です。

定数を定義するときには、しばしば次のようなコードを用います。

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

これは下記のコードと同じ意味のものです。

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

明らかに前者の方が簡潔で理解しやすいでしょう。

PHP ファイルがインクルードされる時に定数の効力が生じるようにするために、定数の定義はエントリスクリプトの冒頭でなされるべきです。
