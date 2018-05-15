コントローラ
============

リソース・クラスを作成して、リソース・データをどのようにフォーマットすべきかを指定したら、
次は、RESTful API を通じてエンド・ユーザにリソースを公開するコントローラ・アクションを作成します。

Yii は、RESTful アクションを作成する仕事を簡単にするための二つの基底コントローラ・クラスを提供しています。
すなわち、[[yii\rest\Controller]] と [[yii\rest\ActiveController]] です。
二つのコントローラの違いは、後者は [アクティブ・レコード](db-active-record.md) として表現されるリソースの扱いに特化した一連のアクションをデフォルトで提供する、という点にあります。
従って、あなたが [アクティブ・レコード](db-active-record.md) を使っていて、提供される組み込みのアクションに満足できるのであれば、
コントローラ・クラスを [[yii\rest\ActiveController]] から拡張することを検討すると良いでしょう。
そうすれば、最小限のコードで強力な RESTful API を作成することが出来ます。

[[yii\rest\Controller]] と [[yii\rest\ActiveController]] は、ともに、下記の機能を提供します。
これらのいくつかについては、後続のセクションで詳細に説明します。

* HTTP メソッドのバリデーション
* [コンテント・ネゴシエーションとデータの書式設定](rest-response-formatting.md)
* [認証](rest-authentication.md)
* [レート制限](rest-rate-limiting.md)

[[yii\rest\ActiveController]] は次の機能を追加で提供します。

* 普通は必要とされる一連のアクション: `index`、`view`、`create`、`update`、`delete`、`options`
* リクエストされたアクションとリソースに対するユーザへの権限付与


## コントローラ・クラスを作成する <span id="creating-controller"></span>

新しいコントローラ・クラスを作成する場合、コントローラ・クラスの命名規約は、
リソースの型の名前を単数形で使う、というものです。
例えば、ユーザの情報を提供するコントローラは `UserController` と名付けることが出来ます。

新しいアクションを作成する仕方はウェブ・アプリケーションの場合とほぼ同じです。
唯一の違いは、`render()` メソッドを呼んでビューを使って結果を表示する代りに、RESTful アクションの場合はデータを直接に返す、という点です。
[[yii\rest\Controller::serializer|シリアライザ]] と [[yii\web\Response|レスポンス・オブジェクト]] が、
元のデータからリクエストされた形式への変換を処理します。
例えば、

```php
public function actionView($id)
{
    return User::findOne($id);
}
```


## フィルタ <span id="filters"></span>

[[yii\rest\Controller]] によって提供される RESTful API 機能のほとんどは [フィルタ](structure-filters.md) の形で実装されています。
具体的に言うと、次のフィルタがリストされた順に従って実行されます。

* [[yii\filters\ContentNegotiator|contentNegotiator]]: コンテント・ネゴシエーションをサポート。
  [レスポンス形式の設定](rest-response-formatting.md) のセクションで説明します。
* [[yii\filters\VerbFilter|verbFilter]]: HTTP メソッドのバリデーションをサポート。
* [[yii\filters\auth\AuthMethod|authenticator]]: ユーザ認証をサポート。
  [認証](rest-authentication.md) のセクションで説明します。
* [[yii\filters\RateLimiter|rateLimiter]]: レート制限をサポート。
  [レート制限](rest-rate-limiting.md) のセクションで説明します。

これらの名前付きのフィルタは、[[yii\rest\Controller::behaviors()|behaviors()]] メソッドで宣言されます。
このメソッドをオーバーライドして、個々のフィルタを構成したり、どれかを無効にしたり、あなた自身のフィルタを追加したりすることが出来ます。
例えば、HTTP 基本認証だけを使いたい場合は、次のようなコードを書くことが出来ます。

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::class,
    ];
    return $behaviors;
}
```

### CORS <span id="cors"></span>

コントローラに [CORS (クロス・オリジン・リソース共有)](structure-filters.md#cors) フィルタを追加するのは、上記の他のフィルタを追加するのより、若干複雑になります。
と言うのは、CORS フィルタは認証メソッドより前に適用されなければならないため、他のフィルタとは少し異なるアプローチが必要だからです。
また、ブラウザが認証クレデンシャルを送信する必要なく、リクエストが出来るかどうかを前もって安全に判断できるように、
[CORS プリフライト・リクエスト](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS#Preflighted_requests)
の認証を無効にする必要もあります。
下記のコードは、[[yii\rest\ActiveController]] を拡張した既存のコントローラに
[[yii\filters\Cors]] フィルタを追加するのに必要なコードを示しています。

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();

    // 認証フィルタを削除する
    $auth = $behaviors['authenticator'];
    unset($behaviors['authenticator']);
    
    // CORS フィルタを追加する
    $behaviors['corsFilter'] = [
        'class' => \yii\filters\Cors::class,
    ];
    
    // 認証フィルタを再度追加する
    $behaviors['authenticator'] = $auth;
    // CORS プリフライト・リクエスト (HTTP OPTIONS メソッド) の認証を回避する
    $behaviors['authenticator']['except'] = ['options'];

    return $behaviors;
}
```


## `ActiveController` を拡張する <span id="extending-active-controller"></span>

コントローラを [[yii\rest\ActiveController]] から拡張する場合は、このコントローラを通じて提供しようとしているリソース・クラスの名前を
[[yii\rest\ActiveController::modelClass|modelClass]] プロパティにセットしなければなりません。
リソース・クラスは [[yii\db\ActiveRecord]] から拡張しなければなりません。


### アクションをカスタマイズする <span id="customizing-actions"></span>

デフォルトでは、[[yii\rest\ActiveController]] は次のアクションを提供します。

* [[yii\rest\IndexAction|index]]: リソースをページごとにリストする。
* [[yii\rest\ViewAction|view]]: 指定されたリソースの詳細を返す。
* [[yii\rest\CreateAction|create]]: 新しいリソースを作成する。
* [[yii\rest\UpdateAction|update]]: 既存のリソースを更新する。
* [[yii\rest\DeleteAction|delete]]: 指定されたりソースを削除する。
* [[yii\rest\OptionsAction|options]]: サポートされている HTTP メソッドを返す。

これらのアクションは全て [[yii\rest\ActiveController::actions()|actions()]] メソッドによって宣言されます。
`actions()` メソッドをオーバーライドすることによって、これらのアクションを構成したり、そのいくつかを無効化したりすることが出来ます。例えば、

```php
public function actions()
{
    $actions = parent::actions();

    // "delete" と "create" のアクションを無効にする
    unset($actions['delete'], $actions['create']);

    // データ・プロバイダの準備を "prepareDataProvider()" メソッドでカスタマイズする
    $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    return $actions;
}

public function prepareDataProvider()
{
    // "index" アクションのためにデータ・プロバイダを準備して返す
}
```

どういう構成オプションが利用できるかを学ぶためには、個々のアクション・クラスのリファレンスを参照してください。


### アクセス・チェックを実行する <span id="performing-access-check"></span>

RESTful API によってリソースを公開するときには、たいてい、
現在のユーザがリクエストしているリソースにアクセスしたり操作したりする許可を持っているか否かをチェックする必要があります。
これは、[[yii\rest\ActiveController]] を使う場合は、[[yii\rest\ActiveController::checkAccess()|checkAccess()]] メソッドを次のようにオーバーライドすることによって出来ます。

```php
/**
 * 現在のユーザの特権をチェックする。
 *
 * 現在のユーザが指定されたデータ・モデルに対して指定されたアクションを実行する特権を
 * 有するか否かをチェックするためには、このメソッドをオーバーライドしなければなりません。
 * ユーザが権限をもたない場合は、[[ForbiddenHttpException]] が投げられなければなりません。
 *
 * @param string $action 実行されるアクションの ID。
 * @param \yii\base\Model $model アクセスされるモデル。null の場合は、アクセスされる特定のモデルが無いことを意味する。
 * @param array $params 追加のパラメータ
 * @throws ForbiddenHttpException ユーザが権限をもたない場合
 */
public function checkAccess($action, $model = null, $params = [])
{
    // ユーザが $action と $model に対する権限を持つかどうかをチェック
    // アクセスを拒否すべきときは ForbiddenHttpException を投げる
    if ($action === 'update' || $action === 'delete') {
        if ($model->author_id !== \Yii::$app->user->id)
            throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
    }
}
```

`checkAccess()` メソッドは [[yii\rest\ActiveController]] のデフォルトのアクションから呼ばれます。
新しいアクションを作成して、それに対してもアクセス・チェックをしたい場合は、新しいアクションの中からこのメソッドを明示的に呼び出さなければなりません。

> Tip: [ロール・ベース・アクセス制御 (RBAC) コンポーネント](security-authorization.md) を使って `checkAccess()` を実装することも可能です。
