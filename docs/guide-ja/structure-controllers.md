コントローラ
============

コントローラは [MVC](https://ja.wikipedia.org/wiki/Model_View_Controller) アーキテクチャの一部を成すものです。
それは [[yii\base\Controller]] を拡張したクラスのオブジェクトであり、リクエストの処理とレスポンスの生成について責任を負います。
具体的には、コントローラは、[アプリケーション](structure-applications.md) から制御を引き継いだ後、
入ってきたリクエストのデータを分析し、それを [モデル](structure-models.md) に引き渡して、
モデルが生成した結果を [ビュー](structure-views.md) に投入し、最終的に外に出て行くレスポンスを生成します。


## アクション <span id="actions"></span>

コントローラは、エンド・ユーザがアドレスを指定して実行をリクエストできる最も基本的なユニットである *アクション* から構成されます。
コントローラは一つまたは複数のアクションを持つことが出来ます。

次の例は、`view` と `create` という二つのアクションを持つ `post` コントローラを示すものです。

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Post;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
}
```

`view` アクション (`actionView()` メソッドで定義されます) において、コードは最初に、リクエストされたモデルの ID に従って [モデル](structure-models.md) を読み出します。
モデルの読み出しが成功したときは、`view` という名前の [ビュー](structure-views.md) を使ってモデルを表示します。
失敗したときは例外を投げます。

`create` アクション (`actionCreate()` メソッドで定義されます) においても、コードは似たようなものです。
最初に、リクエスト・データを使って [モデル](structure-models.md) の新しいインスタンスにデータを投入することを試み、そして、モデルを保存することを試みます。
両方が成功したときは、新しく作成されたモデルの ID を使って `view` アクションにブラウザをリダイレクトします。
どちらかが失敗したときは、ユーザが必要なデータを入力できるようにするための `create` ビューを表示します。


## ルート <span id="routes"></span>

エンド・ユーザは、いわゆる *ルート* によって、アクションを指定します。ルートは、次の部分からなる文字列です。

* モジュール ID: この部分は、コントローラがアプリケーションではない [モジュール](structure-modules.md) に属する場合にのみ存在します。
* [コントローラ ID]((#controller-ids): 同じアプリケーション (または、コントローラがモジュールに属する場合は、同じモジュール)
  に属する全てのコントローラの中から、コントローラを一意に特定する文字列。
* [アクション ID](#action-ids): 同じコントローラに属する全てのアクションの中から、アクションを一意に特定する文字列。

ルートは次の形式を取ります。

```
ControllerID/ActionID
```

または、コントローラがモジュールに属する場合は、次の形式を取ります。

```php
ModuleID/ControllerID/ActionID
```

ですから、ユーザが `https://hostname/index.php?r=site/index` という URL でリクエストをした場合は、
`site` コントローラの中の `index` アクションが実行されます。
ルートがどのようにしてアクションとして解決されるかについての詳細は、[ルーティングと URL 生成](runtime-routing.md) のセクションを参照してください。


## コントローラを作成する <span id="creating-controllers"></span>

[[yii\web\Application|ウェブ・アプリケーション]] では、コントローラは [[yii\web\Controller]] またはその子クラスから派生させなければなりません。
同様に、[[yii\console\Application|コンソール・アプリケーション]] では、コントローラは [[yii\console\Controller]] またはその子クラスから派生させなければなりません。
次のコードは `site` コントローラを定義するものです。

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### コントローラ ID <span id="controller-ids"></span>

通常、コントローラは特定のタイプのリソースに関するリクエストを処理するように設計されます。
この理由により、たいていは、処理するリソースのタイプを示す名詞をコントローラの ID として使います。
例えば、記事データを処理するコントローラの ID としては、`article` を使うことが出来ます。

デフォルトでは、コントローラ ID は、小文字の英字、数字、アンダースコア、ダッシュ、および、フォワード・スラッシュのみを含むべきものです。
例えば、`article` と `post-comment` はともに有効なコントローラ ID ですが、
`article?`、`PostComment`、`admin\post` はそうではありません。

コントローラ ID は、サブ・ディレクトリの接頭辞を含んでも構いません。
例えば、`admin/article` は、[[yii\base\Application::controllerNamespace|コントローラ名前空間]] の下の `admin` サブ・ディレクトリにある `article` コントローラを表します。
サブ・ディレクトリの接頭辞として有効な文字は、小文字または大文字の英字、数字、アンダースコア、そして、フォワード・スラッシュです。
フォワード・スラッシュは、複数レベルのサブ・ディレクトリの区切り文字として使われます (例えば、`panels/admin`)。


### コントローラ・クラスの命名規則 <span id="controller-class-naming"></span>

コントローラ・クラスの名前は下記の手順に従ってコントローラ ID から導出することが出来ます。

1. ハイフンで区切られた各単語の最初の文字を大文字に変える。
   コントローラ ID がスラッシュを含む場合、この規則は ID の最後のスラッシュの後ろの部分にのみ適用されることに注意。
2. ハイフンを削除し、フォワード・スラッシュを全てバックワード・スラッシュに置き換える。
3. 接尾辞 `Controller` を追加する。
4. [[yii\base\Application::controllerNamespace|コントローラ名前空間]] を頭に付ける。

以下は、[[yii\base\Application::controllerNamespace|コントローラ名前空間]] がデフォルト値 `app\controllers`
を取っていると仮定したときの、いくつかの例です。

* `article` は `app\controllers\ArticleController` になる。
* `post-comment` は `app\controllers\PostCommentController` になる。
* `admin/post-comment` は `app\controllers\admin\PostCommentController` になる。
* `adminPanels/post-comment` は `app\controllers\adminPanels\PostCommentController` になる。

コントローラ・クラスは [オートロード可能](concept-autoloading.md) でなければなりません。
この理由により、上記の例の `aritcle` コントローラ・クラスは [エイリアス](concept-aliases.md) が
`@app/controllers/ArticleController.php` であるファイルに保存されるべきものとなります。
一方、`admin/post-comment` コントローラは `@app/controllers/admin/PostCommentController.php` というエイリアスのファイルに保存されるべきものとなります。

> Info: 最後の例である `admin/post-comment` は、どうすれば [[yii\base\Application::controllerNamespace|コントローラ名前空間]]
のサブ・ディレクトリにコントローラを置くことが出来るかを示しています。
  この方法は、コントローラをいくつかのカテゴリに分けて編成したい、けれども [モジュール](structure-modules.md) は使いたくない、という場合に役立ちます。


### コントローラ・マップ <span id="controller-map"></span>

[[yii\base\Application::controllerMap|コントローラ・マップ]] を構成すると、上で述べたコントローラ ID
とクラス名の制約を乗り越えることが出来ます。
これは、主として、クラス名に対する制御が及ばないサード・パーティのコントローラを使おうとする場合に有用です。

[[yii\base\Application::controllerMap|コントローラ・マップ]] は [アプリケーションの構成情報](structure-applications.md#application-configurations)
の中で、次のように構成することが出来ます。

```php
[
    'controllerMap' => [
        // クラス名を使って "account" コントローラを宣言する
        'account' => 'app\controllers\UserController',

        // 構成情報配列を使って "article" コントローラを宣言する
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]
```


### デフォルト・コントローラ <span id="default-controller"></span>

全てのアプリケーションは、それぞれ、[[yii\base\Application::defaultRoute]] プロパティによって指定されるデフォルト・コントローラを持ちます。
リクエストが [ルート](#routes) を指定していない場合、このプロパティによって指定されたルートが使われます。
[[yii\web\Application|ウェブ・アプリケーション]] では、この値は `'site'` であり、一方、[[yii\console\Application|コンソール・アプリケーション]] では、`help` です。
従って、URL が `https://hostname/index.php` である場合は、`site` コントローラがリクエストを処理することになります。

次のように [アプリケーションの構成情報](structure-applications.md#application-configurations) を構成して、デフォルト・コントローラを変更することが出来ます。

```php
[
    'defaultRoute' => 'main',
]
```


## アクションを作成する <span id="creating-actions"></span>

アクションは、コントローラ・クラスの中にいわゆる *アクション・メソッド* を定義するだけで簡単に作成することが出来ます。
アクション・メソッドとは、`action` という語で始まる名前を持つ *public* メソッドのことです。
アクション・メソッドの返り値がエンド・ユーザに送信されるレスポンス・データを表します。次のコードは、`index` と `hello-world` という二つのアクションを定義するものです。

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHelloWorld()
    {
        return 'Hello World';
    }
}
```


### アクション ID <span id="action-ids"></span>

アクションは、たいてい、あるリソースについて特定の操作を実行するように設計されます。
この理由により、アクション ID は、通常、`view`、`update` などのような動詞になります。

デフォルトでは、アクション ID は、小文字の英字、数字、アンダースコア、そして、ハイフンのみを含むべきものです。
アクション ID の中のハイフンは単語を分けるために使われます。
例えば、`view`、`update2`、`comment-post` は全て有効なアクション ID ですが、`view?`、`Update` はそうではありません。

アクションは二つの方法、すなわち、インライン・アクションまたはスタンドアロン・アクションとして作成することが出来ます。
インライン・アクションはコントローラ・クラスのメソッドとして定義されるものであり、
一方、スタンドアロン・アクションは [[yii\base\Action]] またはその子クラスを拡張するクラスです。
インライン・アクションは作成するのにより少ない労力を要するため、通常は、アクションを再利用する意図がない場合に推奨されます。
もう一方のスタンドアロン・アクションは、主として、さまざまなコントローラの中で使われることや、[エクステンション](structure-extensions.md) として再配布されることを目的として作成されます。


### インライン・アクション <span id="inline-actions"></span>

インライン・アクションは、たった今説明したように、アクション・メソッドの形で定義されるアクションを指します。

アクション・メソッドの名前は、次の手順に従って、アクション ID から導出されます。

1. アクション ID に含まれる各単語の最初の文字を大文字に変換する。
2. ハイフンを削除する。
3. 接頭辞 `action` を付ける。

例えば、`index` は `actionIndex` となり、`hello-world` は `actionHelloWorld` となります。

> Note: アクション・メソッドの名前は、*大文字と小文字を区別* します。
  `ActionIndex` という名前のメソッドがあっても、それはアクション・メソッドとは見なされず、結果として、`index` アクションに対するリクエストは例外に帰結します。
  アクション・メソッドが public でなければならない事にも注意してください。
  private や protected なメソッドがインライン・アクションを定義することはありません。


インライン・アクションは作成するのにほとんど労力を要さないため、たいていのアクションはインライン・アクションとして定義されます。
しかし、同じアクションを別の場所で再利用する計画を持っていたり、また、アクションを再配布したいと思っていたりする場合は、
アクションを *スタンドアロン・アクション* として定義することを検討すべきです。


### スタンドアロン・アクション <span id="standalone-actions"></span>

スタンドアロン・アクションは、[[yii\base\Action]] またはその子クラスを拡張するアクション・クラスの形で定義されるものです。
例えば、Yii のリリースに [[yii\web\ViewAction]] と [[yii\web\ErrorAction]] が含まれていますが、
これらは両方ともスタンドアロン・アクションです。

スタンドアロン・アクションを使用するためには、下記のように、コントローラの [[yii\base\Controller::actions()]] メソッドをオーバーライドして、
*アクション・マップ* の中でスタンドアロン・アクションを宣言しなければなりません。

```php
public function actions()
{
    return [
        // クラス名を使って "error" アクションを宣言する
        'error' => 'yii\web\ErrorAction',

        // 構成情報配列を使って "view" アクションを宣言する
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

ご覧のように、`actions()` メソッドは、キーがアクション ID であり、値が対応するアクションのクラス名または
[構成情報](concept-configurations.md) である配列を返さなければなりません。
インライン・アクションと違って、スタンドアロン・アクションのアクション ID は、`actions()` メソッドにおいて宣言される限りにおいて、任意の文字を含むことが出来ます。

スタンドアロン・アクション・クラスを作成するためには、[[yii\base\Action]] またはその子クラスを拡張して、`run()` という名前の public メソッドを実装しなければなりません。
`run()` メソッドの役割はアクション・メソッドの役割と同じです。例えば、

```php
<?php
namespace app\components;

use yii\base\Action;

class HelloWorldAction extends Action
{
    public function run()
    {
        return "Hello World";
    }
}
```


### アクションの結果 <span id="action-results"></span>

アクション・メソッド、または、スタンドアロン・アクションの `run()` メソッドの返り値は、重要な意味を持ちます。
それは、対応するアクションの結果を表すものです。

返り値は、エンド・ユーザにレスポンスとして送信される [レスポンス](runtime-responses.md) オブジェクトとすることが出来ます。

* [[yii\web\Application|ウェブ・アプリケーション]] では、返り値を [[yii\web\Response::data]] に割り当てられる任意のデータとすることも出来ます。
  このデータは、後に、レスポンス・ボディを表す文字列へと変換されます。
* [[yii\console\Application|コンソール・アプリケーション]] では、返り値をコマンド実行の
  [[yii\console\Response::exitStatus|終了ステータス]] を示す整数とすることも出来ます。

これまでに示した例においては、アクションの結果はすべて文字列であり、エンド・ユーザに送信されるレスポンス・ボディとして扱われるものでした。
次の例では、アクションがレスポンス・オブジェクトを返すことによって、ユーザのブラウザを
新しい URL にリダイレクトすることが出来る様子が示されています
([[yii\web\Controller::redirect()|redirect()]] メソッドの返り値はレスポンス・オブジェクトです)。

```php
public function actionForward()
{
    // ユーザのブラウザを https://example.com にリダイレクトする
    return $this->redirect('https://example.com');
}
```


### アクション・パラメータ <span id="action-parameters"></span>

インライン・アクションのアクション・メソッドと、スタンドアロン・アクションの `run()` メソッドは、*アクション・パラメータ* と呼ばれるパラメータを取ることが出来ます。
パラメータの値はリクエストから取得されます。
[[yii\web\Application|ウェブ・アプリケーション]] では、各アクション・パラメータの値は `$_GET` からパラメータ名をキーとして読み出されます。
[[yii\console\Application|コンソール・アプリケーション]] では、アクション・パラメータはコマンドライン引数に対応します。

次の例では、`view` アクション (インライン・アクションです) は、二つのパラメータ、すなわち、`$id` と `$version` を宣言しています。

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public function actionView($id, $version = null)
    {
        // ...
    }
}
```

アクション・パラメータには、次のように、さまざまなリクエストに応じて異なる値が投入されます。

* `https://hostname/index.php?r=post/view&id=123`: `$id` パラメータには `'123'` という値が入れられます。
  一方、`version` というクエリ・パラメータは無いので、`$version` は `null` のままになります。
* `https://hostname/index.php?r=post/view&id=123&version=2`: `$id` および `$version` パラメータに、
  それぞれ、`'123'` と `'2'` が入ります。
* `https://hostname/index.php?r=post/view`: 必須の `$id` パラメータがリクエストで提供されていないため、
  [[yii\web\BadRequestHttpException]] 例外が投げられます。
* `https://hostname/index.php?r=post/view&id[]=123`: `$id` パラメータが予期しない配列値 `['123']` を受け取ろうとするため、
  [[yii\web\BadRequestHttpException]] 例外が投げられます。

アクション・パラメータに配列値を受け取らせたい場合は、次のように、パラメータに `array` の型ヒントを付けなければなりません。

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

このようにすると、リクエストが `https://hostname/index.php?r=post/view&id[]=123` である場合は、`$id` パラメータは `['123']` という値を受け取ります。
リクエストが `https://hostname/index.php?r=post/view&id=123` である場合も、スカラ値 `'123'` が自動的に配列に変換されるため、
`$id` パラメータは引き続き同じ配列値を受け取ります。

上記の例は主としてウェブ・アプリケーションでのアクション・パラメータの動作を示すものです。
コンソール・アプリケーションについては、[コンソール・コマンド](tutorial-console.md) のセクションで詳細を参照してください。


### デフォルト・アクション <span id="default-action"></span>

すべてのコントローラは、それぞれ、[[yii\base\Controller::defaultAction]] によって指定されるデフォルト・アクションを持ちます。
[ルート](#routes) がコントローラ ID のみを含む場合は、
指定されたコントローラのデフォルト・アクションがリクエストされたことを意味します。

デフォルトでは、デフォルト・アクションは `index` と設定されます。
このデフォルト値を変更したい場合は、以下のように、コントローラ・クラスでこのプロパティをオーバーライドするだけです。

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'home';

    public function actionHome()
    {
        return $this->render('home');
    }
}
```


## コントローラのライフサイクル <span id="controller-lifecycle"></span>

リクエストを処理するときに、[アプリケーション](structure-applications.md) はリクエストされた [ルート](#routes)
に基いてコントローラを作成します。
そして、次に、コントローラはリクエストに応じるために以下のライフサイクルを経過します。

1. コントローラが作成され構成された後、[[yii\base\Controller::init()]] メソッドが呼ばれる。
2. コントローラは、リクエストされたアクション ID に基いて、アクション・オブジェクトを作成する。
   * アクション ID が指定されていないときは、[[yii\base\Controller::defaultAction|デフォルト・アクション ID]] が使われる。
   * アクション ID が [[yii\base\Controller::actions()|アクション・マップ]] の中に見つかった場合は、
     スタンドアロン・アクションが作成される。
   * アクション ID に合致するアクション・メソッドが見つかった場合は、インライン・アクションが作成される。
   * 上記以外の場合は、[[yii\base\InvalidRouteException]] 例外が投げられる。
3. コントローラは、アプリケーション、(コントローラがモジュールに属する場合は) モジュール、
  そしてコントローラの `beforeAction()` メソッドをこの順で呼び出す。
   * どれか一つの呼び出しが `false` を返した場合は、残りのまだ呼ばれていない `beforeAction()` メソッドはスキップされ、
     アクションの実行はキャンセルされる。
   * デフォルトでは、それぞれの `beforeAction()` メソッドは、ハンドラをアタッチすることが可能な `beforeAction` イベントをトリガする。
4. コントローラがアクションを実行する。
   * アクション・パラメータが解析されて、リクエスト・データからデータが投入される。
5. コントローラは、コントローラ、(コントローラがモジュールに属する場合は) モジュール、
     そしてアプリケーションの `afterAction()` メソッドをこの順で呼び出す。
   * デフォルトでは、それぞれの `afterAction()` メソッドは、ハンドラをアタッチすることが可能な `afterAction` イベントをトリガする。
6. アプリケーションはアクションの結果を受け取り、それを [レスポンス](runtime-responses.md) に割り当てる。


## ベスト・プラクティス <span id="best-practices"></span>

良く設計されたアプリケーションでは、コントローラはたいてい非常に軽いものになり、
それぞれのアクションは数行のコードしか含まないものになります。
あなたのコントローラが少々複雑になっている場合、そのことは、通常、コントローラをリファクタして、コードの一部を他のクラスに移動すべきことを示すものです。

いくつかのベスト・プラクティスを特に挙げるなら、コントローラは、

* [リクエスト](runtime-requests.md) データにアクセスすることが出来ます。
* リクエスト・データを使って [モデル](structure-models.md) や他のサービス・コンポーネントのメソッドを呼ぶことが出来ます。
* [ビュー](structure-views.md) を使ってレスポンスを構成することが出来ます。
* リクエストされたデータの処理をするべきではありません - データは [モデルのレイヤ](structure-models.md) において処理されるべきです。
* HTML を埋め込むなどの表示に関わるコードは避けるべきです - 表示は [ビュー](structure-views.md) で行う方が良いです。
