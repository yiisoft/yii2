フィルタ
========

フィルタは、[コントローラ・アクション](structure-controllers.md#actions) の前 および/または 後に走るオブジェクトです。
例えば、アクセス・コントロール・フィルタはアクションの前に走って、アクションが特定のエンド・ユーザだけにアクセスを許可するものであることを保証します。
また、コンテント圧縮フィルタはアクションの後に走って、レスポンスのコンテントをエンド・ユーザに送出する前に圧縮します。

一つのフィルタは、前フィルタ (アクションの *前* に適用されるフィルタのロジック) および/または
後フィルタ (アクションの *後* に適用されるロジック) から構成することが出来ます。


## フィルタを使用する <span id="using-filters"></span>

フィルタは、本質的には特別な種類の [ビヘイビア](concept-behaviors.md) です。
したがって、フィルタを使うことは [ビヘイビアを使う](concept-behaviors.md#attaching-behaviors) ことと同じです。
下記のように、[[yii\base\Controller::behaviors()|behaviors()]] メソッドをオーバーライドすることによって、コントローラの中でフィルタを宣言することが出来ます。

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index', 'view'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

デフォルトでは、コントローラ・クラスの中で宣言されたフィルタは、そのコントローラの *全て* のアクションに適用されます。
しかし、[[yii\base\ActionFilter::only|only]] プロパティを構成することによって、
フィルタがどのアクションに適用されるべきかを明示的に指定することも出来ます。
上記の例では、 `HttpCache` フィルタは、`index` と `view` のアクションに対してのみ適用されています。
また、[[yii\base\ActionFilter::except|except]] プロパティを構成して、いくつかのアクションをフィルタされないように除外することも可能です。

コントローラのほかに、[モジュール](structure-modules.md) または [アプリケーション](structure-applications.md)
でもフィルタを宣言することが出来ます。
そのようにした場合、[[yii\base\ActionFilter::only|only]] と [[yii\base\ActionFilter::except|except]] のプロパティを上で説明したように構成しない限り、
そのフィルタは、モジュールまたはアプリケーションに属する *全て* のコントローラ・アクションに適用されます。

> Note: モジュールやアプリケーションでフィルタを宣言する場合、[[yii\base\ActionFilter::only|only]] と [[yii\base\ActionFilter::except|except]] のプロパティでは、
  アクション ID ではなく、[ルート](structure-controllers.md#routes) を使わなければなりません。
  なぜなら、モジュールやアプリケーションのスコープでは、アクション ID だけでは完全にアクションを指定することが出来ないからです。

一つのアクションに複数のフィルタが構成されている場合、フィルタは下記で説明されている規則に従って適用されます。

* 前フィルタ
    - アプリケーションで宣言されたフィルタを `behaviors()` にリストされた順に適用する。
    - モジュールで宣言されたフィルタを `behaviors()` にリストされた順に適用する。
    - コントローラで宣言されたフィルタを `behaviors()` にリストされた順に適用する。
    - フィルタのどれかがアクションをキャンセルすると、
      そのフィルタの後のフィルタ (前フィルタと後フィルタの両方) は適用されない。
* 前フィルタを通過したら、アクションを走らせる。
* 後フィルタ
    - コントローラで宣言されたフィルタを `behaviors()` にリストされた逆順で適用する。
    - モジュールで宣言されたフィルタを `behaviors()` にリストされた逆順で適用する。
    - アプリケーションで宣言されたフィルタを `behaviors()` にリストされた逆順で適用する。


## フィルタを作成する <span id="creating-filters"></span>

新しいアクション・フィルタを作成するためには、[[yii\base\ActionFilter]] を拡張して、
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] および/または [[yii\base\ActionFilter::afterAction()|afterAction()]] メソッドをオーバーライドします。
前者はアクションが走る前に実行され、後者は走った後に実行されます。
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] の返り値が、アクションが実行されるべきか否かを決定します。
返り値が `false` である場合、このフィルタの後に続くフィルタはスキップされ、アクションは実行を中止されます。

次の例は、アクションの実行時間をログに記録するフィルタを示すものです。

```php
namespace app\components;

use Yii;
use yii\base\ActionFilter;

class ActionTimeFilter extends ActionFilter
{
    private $_startTime;

    public function beforeAction($action)
    {
        $this->_startTime = microtime(true);
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        $time = microtime(true) - $this->_startTime;
        Yii::debug("アクション '{$action->uniqueId}' は $time 秒を消費。");
        return parent::afterAction($action, $result);
    }
}
```


## コアのフィルタ <span id="core-filters"></span>

Yii はよく使われる一連のフィルタを提供しており、それらは、主として `yii\filters` 名前空間の下にあります。
以下では、それらのフィルタを簡単に紹介します。


### [[yii\filters\AccessControl|AccessControl]] <span id="access-control"></span>

AccessControl は、一組の [[yii\filters\AccessControl::rules|規則]] に基づいて、シンプルなアクセス・コントロールを提供するものです。
具体的に言うと、アクションが実行される前に、AccessControl はリストされた規則を調べて、
現在のコンテキスト変数 (例えば、ユーザの IP アドレスや、ユーザのログイン状態など) に最初に合致するものを見つけます。
そして、合致した規則によって、リクエストされたアクションの実行を許可するか拒否するかを決定します。
合致する規則がなかった場合は、アクセスは拒否されます。

次の例は、認証されたユーザに対しては `create` と `update` のアクションへのアクセスを許可し、
その他のすべてのユーザにはこれら二つのアクションに対するアクセスを拒否する仕方を示すものです。

```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'only' => ['create', 'update'],
            'rules' => [
                // 認証されたユーザに許可する
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // その他はすべてデフォルトにより拒否される
            ],
        ],
    ];
}
```

アクセス・コントロール一般についての詳細は [権限](security-authorization.md) のセクションを参照してください。


### 認証メソッド・フィルタ <span id="auth-method-filters"></span>

認証メソッド・フィルタは、[HTTP Basic 認証](https://ja.wikipedia.org/wiki/Basic%E8%AA%8D%E8%A8%BC)、
[OAuth 2](https://oauth.net/2/) などの様々なメソッドを使ってユーザを認証するために使われるものです。
これらのフィルタ・クラスはすべて `yii\filters\auth` 名前空間の下にあります。

次の例は、[[yii\filters\auth\HttpBasicAuth]] の使い方を示すもので、HTTP Basic 認証に基づくアクセス・トークンを使ってユーザを認証しています。
これを動作させるためには、あなたの [[yii\web\User::identityClass|ユーザ・アイデンティティ・クラス]]
が [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]
メソッドを実装していなければならないことに注意してください。

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    return [
        'basicAuth' => [
            'class' => HttpBasicAuth::class,
        ],
    ];
}
```

認証メソッド・フィルタは RESTful API を実装するときに使われるのが通例です。
詳細については、RESTful の [認証](rest-authentication.md) のセクションを参照してください。


### [[yii\filters\ContentNegotiator|ContentNegotiator]] <span id="content-negotiator"></span>

ContentNegotiator は、レスポンス形式のネゴシエーションとアプリケーション言語のネゴシエーションをサポートします。
このフィルタは `GET` パラメータと `Accept` HTTP ヘッダを調べることによって、レスポンス形式 および/または 言語を決定しようとします。

次の例では、ContentNegotiator はレスポンス形式として JSON と XML をサポートし、
(合衆国の)英語とドイツ語を言語としてサポートするように構成されています。

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

public function behaviors()
{
    return [
        [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ];
}
```

レスポンス形式と言語は [アプリケーションのライフサイクル](structure-applications.md#application-lifecycle)
のもっと早い段階で決定される必要があることがよくあります。
このため、ContentNegotiator はフィルタの他に、[ブートストラップ・コンポーネント](structure-applications.md#bootstrap) としても使うことができるように設計されています。
例えば、次のように、ContentNegotiator を [アプリケーションの構成情報](structure-applications.md#application-configurations)
の中で構成することが出来ます。

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

[
    'bootstrap' => [
        [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ],
];
```

> Info: 望ましいコンテント・タイプと言語がリクエストから決定できない場合は、
  [[formats]] および [[languages]] に挙げられている最初の形式と言語が使用されます。



### [[yii\filters\HttpCache|HttpCache]] <span id="http-cache"></span>

HttpCache は `Last-Modified` および `Etag` の HTTP ヘッダを利用して、クライアント・サイドのキャッシュを実装するものです。
例えば、

```php
use yii\filters\HttpCache;

public function behaviors()
{
    return [
        [
            'class' => HttpCache::class,
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

HttpCache に関する詳細は [HTTP キャッシュ](caching-http.md) のセクションを参照してください。


### [[yii\filters\PageCache|PageCache]] <span id="page-cache"></span>

PageCache はサーバ・サイドにおけるページ全体のキャッシュを実装するものです。
次の例では、PageCache が `index` アクションに適用されて、最大 60 秒間、または、`post` テーブルのエントリ数が変化するまでの間、ページ全体をキャッシュしています。
さらに、このページ・キャッシュは、選択されたアプリケーションの言語に従って、違うバージョンのページを保存するようにしています。

```php
use yii\filters\PageCache;
use yii\caching\DbDependency;

public function behaviors()
{
    return [
        'pageCache' => [
            'class' => PageCache::class,
            'only' => ['index'],
            'duration' => 60,
            'dependency' => [
                'class' => DbDependency::class,
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
            'variations' => [
                \Yii::$app->language,
            ]
        ],
    ];
}
```

PageCache の使用に関する詳細は [ページ・キャッシュ](caching-page.md) のセクションを参照してください。


### [[yii\filters\RateLimiter|RateLimiter]] <span id="rate-limiter"></span>

RateLimiter は [リーキー・バケット・アルゴリズム](https://ja.wikipedia.org/wiki/%E3%83%AA%E3%83%BC%E3%82%AD%E3%83%BC%E3%83%90%E3%82%B1%E3%83%83%E3%83%88) に基づいてレート制限のアルゴリズムを実装するものです。
主として RESTful API を実装するときに使用されます。
このフィルタの使用に関する詳細は [レート制限](rest-rate-limiting.md) のセクションを参照してください。


### [[yii\filters\VerbFilter|VerbFilter]] <span id="verb-filter"></span>

VerbFilter は、HTTP リクエスト・メソッド (HTTP 動詞) がリクエストされたアクションによって許可されているかどうかをチェックするものです。
許可されていない場合は、HTTP 405 例外を投げます。
次の例では、VerbFilter が宣言されて、CRUD アクションに対して許可されるメソッドの典型的なセットを指定しています。

```php
use yii\filters\VerbFilter;

public function behaviors()
{
    return [
        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['get', 'post'],
                'update' => ['get', 'put', 'post'],
                'delete' => ['post', 'delete'],
            ],
        ],
    ];
}
```

### [[yii\filters\Cors|Cors]] <span id="cors"></span>

クロス・オリジン・リソース共有 [CORS](https://developer.mozilla.org/ja/docs/Web/HTTP/CORS) とは、ウェブ・ページにおいて、さまざまなリソース (例えば、フォントや JavaScript など) を、それを生成するドメイン以外のドメインからリクエストすることを可能にするメカニズムです。
特に言えば、JavaScript の AJAX 呼出しが使用することが出来る XMLHttpRequest メカニズムです。
このような「クロス・ドメイン｣のリクエストは、このメカニズムに拠らなければ、
同一生成元のセキュリティ・ポリシーによって、ウェブ・ブラウザから禁止されるはずのものです。
CORS は、ブラウザとサーバが交信して、クロス・ドメインのリクエストを許可するか否かを決定する方法を定義するものです。

[[yii\filters\Cors|Cors フィルタ]] は、CORS ヘッダが常に送信されることを保証するために、
Authentication / Authorization のフィルタよりも前に定義されなければなりません。

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
        ],
    ], parent::behaviors());
}
```

あなたの API の [[yii\rest\ActiveController]] クラスに CORS フィルタを追加したい場合は、
[REST コントローラ](rest-controllers.md#cors) のセクションも参照して下さい。

Cors のフィルタリングは [[yii\filters\Cors::$cors|$cors]] プロパティを使ってチューニングすることが出来ます。

* `cors['Origin']`: 許可される生成元を定義するのに使われる配列。`['*']` (すべて) または `['https://www.myserver.net'、'https://www.myotherserver.com']` などが設定可能。デフォルトは `['*']`。
* `cors['Access-Control-Request-Method']`: 許可される HTTP 動詞の配列。たとえば、`['GET', 'OPTIONS', 'HEAD']`。デフォルトは `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`。
* `cors['Access-Control-Request-Headers']`: 許可されるヘッダの配列。全てのヘッダを意味する `['*']` または特定のヘッダを示す `['X-Request-With']` が設定可能。デフォルトは `['*']`。
* `cors['Access-Control-Allow-Credentials']`: 現在のリクエストをクレデンシャルを使ってすることが出来るかどうかを定義。`true`、`false` または `null` (設定なし) が設定可能。デフォルトは `null`。
* `cors['Access-Control-Max-Age']`: プリフライト・リクエストの寿命を定義。デフォルトは `86400`。

次の例は、生成元 `https://www.myserver.net` に対する `GET`、`HEAD` および `OPTIONS` のメソッドによる CORS を許可するものです。

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
        ],
    ], parent::behaviors());
}
```

デフォルトのパラメータをアクション単位でオーバーライドして CORS ヘッダをチューニングすることも可能です。
例えば、`login` アクションに `Access-Control-Allow-Credentials` を追加することは、次のようにすれば出来ます。

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
            'actions' => [
                'login' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ]
        ],
    ], parent::behaviors());
}
```
