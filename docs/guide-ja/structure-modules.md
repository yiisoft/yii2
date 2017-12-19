モジュール
==========

モジュールは、[モデル](structure-models.md)、[ビュー](structure-views.md)、[コントローラ](structure-controllers.md)、およびその他の支援コンポーネントから構成される自己充足的なソフトウェアのユニットです。
モジュールが [アプリケーション](structure-applications.md) にインストールされている場合、エンドユーザはモジュールのコントローラにアクセスする事が出来ます。
これらのことを理由として、モジュールは小さなアプリケーションと見なされることがよくあります。
しかし、モジュールは単独では配備できず、アプリケーションの中に存在しなければならないという点で [アプリケーション](structure-applications.md) とは異なります。


## モジュールを作成する <span id="creating-modules"></span>

モジュールは、モジュールの [[yii\base\Module::basePath|ベースパス]] と呼ばれるディレクトリとして編成されます。
このディレクトリの中に、ちょうどアプリケーションの場合と同じように、`controllers`、`models`、`views` のようなサブディレクトリが存在して、コントローラ、モデル、ビュー、その他のコードを収納しています。
次の例は、モジュール内の中身を示すものです。

```
forum/
    Module.php                   モジュールクラスファイル
    controllers/                 コントローラクラスファイルを含む
        DefaultController.php    デフォルトのコントローラクラスファイル
    models/                      モデルクラスファイルを含む
    views/                       コントローラのビューとレイアウトのファイルを含む
        layouts/                 レイアウトのビューファイルを含む
        default/                 DefaultController のためのビューファイルを含む
            index.php            index ビューファイル
```


### モジュールクラス <span id="module-classes"></span>

全てのモジュールは [[yii\base\Module]] から拡張したユニークなモジュールクラスを持たなければなりません。
モジュールクラスは、モジュールの [[yii\base\Module::basePath|ベースパス]] 直下に配置されて [オートロード可能](concept-autoloading.md) になっていなければなりません。
モジュールがアクセスされたとき、対応するモジュールクラスの単一のインスタンスが作成されます。
[アプリケーションのインスタンス](structure-applications.md) と同じように、モジュールのインスタンスは、モジュール内のコードがデータとコンポーネントを共有するために使用されます。

次のコードは、モジュールクラスがどのようなものかを示す例です。

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->params['foo'] = 'bar';
        // ... 他の初期化コード ...
    }
}
```

`init` メソッドがモジュールのプロパティを初期化するためのコードをたくさん含む場合は、それを [構成情報](concept-configurations.md) の形で保存し、`init()` の中で次のコードを使って読み出すことも可能です。

```php
public function init()
{
    parent::init();
    // config.php からロードした構成情報でモジュールを初期化する
    \Yii::configure($this, require __DIR__ . '/config.php');
}
```

ここで、構成情報ファイル `config.php` は、[アプリケーションの構成情報](structure-applications.md#application-configurations) の場合と同じように、次のような内容を含むことが出来ます。

```php
<?php
return [
    'components' => [
        // コンポーネントの構成情報のリスト
    ],
    'params' => [
        // パラメータのリスト
    ],
];
```


### モジュール内のコントローラ <span id="controllers-in-modules"></span>

モジュールの中でコントローラを作成するときは、コントローラクラスをモジュールクラスの名前空間の `controllers` サブ名前空間に置くことが規約です。
このことは、同時に、コントローラのクラスファイルをモジュールの [[yii\base\Module::basePath|ベースパス]] 内の `controllers` ディレクトリに置くべきことをも意味します。
例えば、前の項で示された `forum` モジュールの中で `post` コントローラを作成するためには、次のようにしてコントローラを宣言しなければなりません。

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

コントローラクラスの名前空間は、[[yii\base\Module::controllerNamespace]] プロパティを構成してカスタマイズすることが出来ます。
いくつかのコントローラがこの名前空間の外にある場合でも、[[yii\base\Module::controllerMap]] プロパティを構成することによって、それらをアクセス可能にすることが出来ます。
これは、[アプリケーションでのコントローラマップ](structure-applications.md#controller-map) の場合と同様です。


### モジュール内のビュー <span id="views-in-modules"></span>

モジュール内のビューは、モジュールの [[yii\base\Module::basePath|ベースパス]] 内の `views` ディレクトリに置かれなくてはなりません。
モジュール内のコントローラによってレンダリングされるビューは、ディレクトリ `views/ControllerID` の下に置きます。
ここで、`ControllerID` は [コントローラ ID](structure-controllers.md#routes) を指します。
例えば、コントローラクラスが `PostController` である場合、ディレクトリはモジュールの [[yii\base\Module::basePath|ベースパス]] の中の `views/post` となります。

モジュールは、そのモジュールのコントローラによってレンダリングされるビューに適用される [レイアウト](structure-views.md#layouts) を指定することが出来ます。
レイアウトは、デフォルトでは `views/layouts` ディレクトリに置かれなければならず、また、[[yii\base\Module::layout]] プロパティがレイアウトの名前を指すように構成しなければなりません。
`layout` プロパティを構成しない場合は、アプリケーションのレイアウトが代りに使用されます。


### モジュール内のコンソールコマンド <span id="console-commands-in-modules"></span>

[コンソール](tutorial-console.md) モードで使用する事が出来るコマンドをmodeコマンドをモジュール内で宣言することも可能です。

あなたのコマンドがコマンドラインユーティリティから見えるようにするためには、Yii がコンソールモードで実行されたときに
[[yii\base\Module::controllerNamespace]] を変更して、コマンドの名前空間を指し示すようにする必要があります。

それを達成する一つの方法は、モジュールの `init()` メソッドの中で Yii アプリケーションのインスタンスの型を調べるという方法です。

```php
public function init()
{
    parent::init();
    if (Yii::$app instanceof \yii\console\Application) {
        $this->controllerNamespace = 'app\modules\forum\commands';
    }
}
```

このようにすれば、コマンドラインから次のルートを使ってあなたのコマンドを使用する事が出来るようになります。

```
yii <module_id>/<command>/<sub_command>
```


## モジュールを使う <span id="using-modules"></span>

アプリケーションの中でモジュールを使うためには、アプリケーションの [[yii\base\Application::modules|modules]] プロパティのリストにそのモジュールを載せてアプリケーションを構成するだけで大丈夫です。
次のコードは、[アプリケーションの構成情報](structure-applications.md#application-configurations) の中で `forum` モジュールを使うようにするものです。

```php
[
    'modules' => [
        'forum' => [
            'class' => 'app\modules\forum\Module',
            // ... モジュールのその他の構成情報 ...
        ],
    ],
]
```

[[yii\base\Application::modules|modules]] プロパティは、モジュールの構成情報の配列を取ります。
各配列のキーは、アプリケーションの全てのモジュールの中でそのモジュールを特定するためのユニークな *モジュール ID* を表します。
そして、対応する配列の値は、そのモジュールを作成するための [構成情報](concept-configurations.md) です。


### ルート <span id="routes"></span>

アプリケーションの中のコントローラをアクセスするのと同じように、[ルート](structure-controllers.md#routes) がモジュールの中のコントローラを指し示すために使われます。
モジュール内のコントローラのルートは、モジュール ID で始まり、[コントローラ ID](structure-controllers.md#controller-ids)、[アクション ID](structure-controllers.md#action-ids) と続くものでなければなりません。
例えば、アプリケーションが `forum` という名前のモジュールを使用している場合、`forum/post/index` というルートは、`forum` モジュール内の `post` コントローラの `index` アクションを表します。
ルートがモジュール ID だけを含む場合は、[[yii\base\Module::defaultRoute]] プロパティ (デフォルト値は `default` です) が、どのコントローラ/アクションが使用されるべきかを決定します。
これは、`forum` というルートは `forum` モジュール内の `default` コントローラを表すという意味です。


### モジュールにアクセスする <span id="accessing-modules"></span>

モジュール内において、モジュール ID や、モジュールのパラメータ、モジュールのコンポーネントなどにアクセスするために、[モジュールクラス](#module-classes) のインスタンスを取得する必要があることがよくあります。
次の文を使ってそうすることが出来ます。

```php
$module = MyModuleClass::getInstance();
```

ここで `MyModuleClass` は、当該モジュールクラスの名前を指すものです。
`getInstance()` メソッドは、現在リクエストされているモジュールクラスのインスタンスを返します。
モジュールがリクエストされていない場合は、このメソッドは `null` を返します。
モジュールクラスの新しいインスタンスを手動で作成しようとしてはいけないことに注意してください。
手動で作成したインスタンスは、リクエストに対するレスポンスとして Yii によって作成されたインスタンスとは別のものになります。

> Info: モジュールを開発するとき、モジュールが固定の ID を使うと仮定してはいけません。
  なぜなら、モジュールは、アプリケーションや他のモジュールの中で使うときに、任意の ID と結び付けることが出来るからです。
  モジュール ID を取得するためには、上記の方法を使って最初にモジュールのインスタンスを取得し、そして `$module->id` によって ID を取得しなければなりません。

モジュールのインスタンスにアクセスするためには、次の二つの方法を使うことも出来ます。

```php
// ID が "forum" である子モジュールを取得する
$module = \Yii::$app->getModule('forum');

// 現在リクエストされているコントローラが属するモジュールを取得する
$module = \Yii::$app->controller->module;
```

最初の方法は、モジュール ID を知っている時しか役に立ちません。一方、第二の方法は、リクエストされているコントローラについて知っている場合に使うのに最適な方法です。

いったんモジュールのインスタンスをとらえれば、モジュールに登録されたパラメータやコンポーネントにアクセスすることが可能になります。
例えば、

```php
$maxPostCount = $module->params['maxPostCount'];
```


### モジュールをブートストラップする <span id="bootstrapping-modules"></span>

いくつかのモジュールは、全てのリクエストで毎回走らせる必要があります。[[yii\debug\Module|デバッグ]] モジュールがその一例です。
そうするためには、そのようなモジュールをアプリケーションの [[yii\base\Application::bootstrap|bootstrap]] プロパティのリストに挙げます。

例えば、次のアプリケーションの構成情報は、`debug` モジュールが常にロードされることを保証するものです。

```php
[
    'bootstrap' => [
        'debug',
    ],

    'modules' => [
        'debug' => 'yii\debug\Module',
    ],
]
```


## 入れ子のモジュール <span id="nested-modules"></span>

モジュールはレベルの制限無く入れ子にすることが出来ます。
つまり、モジュールは別のモジュールを含むことが出来、その含まれたモジュールもさらに別のモジュールを含むことが出来ます。
含む側を *親モジュール*、含まれる側を *子モジュール* と呼びます。
子モジュールは、親モジュールの [[yii\base\Module::modules|modules]] プロパティの中で宣言されなければなりません。
例えば、

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                // ここはもっと短い名前空間の使用を考慮すべきです
                'class' => 'app\modules\forum\modules\admin\Module',
            ],
        ];
    }
}
```

入れ子にされたモジュールの中にあるコントローラのルートは、全ての祖先のモジュールの ID を含まなければなりません。
例えば、`forum/admin/dashboard/index` というルートは、`forum` モジュールの子モジュールである `admin` モジュールの `dashboard` コントローラの `index` アクションを表します。

> Info: [[yii\base\Module::getModule()|getModule()]] メソッドは、親モジュールに直接属する子モジュールだけを返します。
[[yii\base\Application::loadedModules]] プロパティがロードされた全てのモジュールのリストを保持しています。
このリストには、直接の子と孫以下の両方のモジュールが含まれ、クラス名によってインデックスされています。


## ベストプラクティス <span id="best-practices"></span>

モジュールは、それぞれ密接に関係する一連の機能を含む数個のグループに分割できるような、規模の大きなアプリケーションに最も適しています。
そのような機能グループをそれぞれモジュールとして、特定の個人やチームによって開発することが出来ます。

モジュールは、また、機能グループレベルでコードを再利用するための良い方法でもあります。
ある種のよく使われる機能、例えばユーザ管理やコメント管理などは、全て、将来のプロジェクトで容易に再利用できるように、モジュールの形式で開発することが出来ます。
