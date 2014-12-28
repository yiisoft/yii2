コントローラ
============

リソースクラスを作成して、リソースデータをどのようにフォーマットすべきかを指定したら、次は、RESTful API を通じてエンドユーザにリソースを公開するコントローラアクションを作成します。

Yii は、RESTful アクションを作成する仕事を簡単にするための二つの基底コントローラクラスを提供しています。
すなわち、[[yii\rest\Controller]] と [[yii\rest\ActiveController]] です。
二つのコントローラの違いは、後者は [アクティブレコード](db-active-record.md) として表現されるリソースの扱いに特化した一連のアクションをデフォルトで提供する、という点にあります。
従って、あなたが [アクティブレコード](db-active-record.md) を使っていて、提供される組み込みのアクションに満足できるのであれば、コントローラクラスを [[yii\rest\ActiveController]] から拡張することを検討すると良いでしょう。
そうすれば、最小限のコードで強力な RESTful API を作成することが出来ます。

[[yii\rest\Controller]] と [[yii\rest\ActiveController]] は、ともに、下記の機能を提供します。
これらのいくつかについては、後続の節で詳細に説明します。

* HTTP メソッドのバリデーション
* [コンテントネゴシエーションとデータの書式設定](rest-response-formatting.md)
* [認証](rest-authentication.md)
* [転送レート制限](rest-rate-limiting.md)

[[yii\rest\ActiveController]] は次の機能を追加で提供します。

* 普通は必要とされる一連のアクション: `index`、`view`、`create`、`update`、`delete`、`options`
* リクエストされたアクションとリソースに関するユーザへの権限付与


## コントローラクラスを作成する <a name="creating-controller"></a>

新しいコントローラクラスを作成する場合、コントローラクラスの命名規約は、リソースの型の名前を単数形で使う、というものです。
例えば、ユーザの情報を提供するコントローラは `UserController` と名付けることが出来ます。

新しいアクションを作成する仕方はウェブアプリケーションの場合とほぼ同じです。
唯一の違いは、`render()` メソッドを呼んでビューを使って結果を表示する代りに、RESTful アクションの場合はデータを直接に返す、という点です。
[[yii\rest\Controller::serializer|シリアライザ]] と [[yii\web\Response|レスポンスオブジェクト]] が、下のデータからリクエストされた形式への変換を処理します。
例えば、

```php
public function actionView($id)
{
    return User::findOne($id);
}
```


## フィルタ <a name="filters"></a>

[[yii\rest\Controller]] によって提供される RESTful API 機能のほとんどは [フィルタ](structure-filters.md) の形で実装されています。
具体的に言うと、次のフィルタがリストされた順に従って実行されます。

* [[yii\filters\ContentNegotiator|contentNegotiator]]: コンテントネゴシエーションをサポート。
  [レスポンスの書式設定](rest-response-formatting.md) の節で説明します。
* [[yii\filters\VerbFilter|verbFilter]]: HTTP メソッドのバリデーションをサポート。
* [[yii\filters\AuthMethod|authenticator]]: ユーザ認証をサポート。
  [認証](rest-authentication.md) の節で説明します。
* [[yii\filters\RateLimiter|rateLimiter]]: 転送レート制限をサポート。
  [転送レート制限](rest-rate-limiting.md) の節で説明します。

これらの名前付きのフィルタは、[[yii\rest\Controller::behaviors()|behaviors()]] メソッドで宣言されます。
このメソッドをオーバーライドして、個々のフィルタを構成したり、どれかを無効にしたり、あなた自身のフィルタを追加したりすることが出来ます。
例えば、HTTP 基本認証だけを使いたい場合は、次のようなコードを書くことが出来ます。

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::className(),
    ];
    return $behaviors;
}
```


## `ActiveController` を拡張する <a name="extending-active-controller"></a>

If your controller class extends from [[yii\rest\ActiveController]], you should set
its [[yii\rest\ActiveController::modelClass||modelClass]] property to be the name of the resource class
that you plan to serve through this controller. The class must extend from [[yii\db\ActiveRecord]].


### Customizing Actions <a name="customizing-actions"></a>

By default, [[yii\rest\ActiveController]] provides the following actions:

* [[yii\rest\IndexAction|index]]: list resources page by page;
* [[yii\rest\ViewAction|view]]: return the details of a specified resource;
* [[yii\rest\CreateAction|create]]: create a new resource;
* [[yii\rest\UpdateAction|update]]: update an existing resource;
* [[yii\rest\DeleteAction|delete]]: delete the specified resource;
* [[yii\rest\OptionsAction|options]]: return the supported HTTP methods.

All these actions are declared through the [[yii\rest\ActiveController::actions()|actions()]] method.
You may configure these actions or disable some of them by overriding the `actions()` method, like shown the following,

```php
public function actions()
{
    $actions = parent::actions();

    // disable the "delete" and "create" actions
    unset($actions['delete'], $actions['create']);

    // customize the data provider preparation with the "prepareDataProvider()" method
    $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    return $actions;
}

public function prepareDataProvider()
{
    // prepare and return a data provider for the "index" action
}
```

Please refer to the class references for individual action classes to learn what configuration options are available.


### Performing Access Check <a name="performing-access-check"></a>

When exposing resources through RESTful APIs, you often need to check if the current user has the permission
to access and manipulate the requested resource(s). With [[yii\rest\ActiveController]], this can be done
by overriding the [[yii\rest\ActiveController::checkAccess()|checkAccess()]] method like the following,

```php
/**
 * Checks the privilege of the current user.
 *
 * This method should be overridden to check whether the current user has the privilege
 * to run the specified action against the specified data model.
 * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
 *
 * @param string $action the ID of the action to be executed
 * @param \yii\base\Model $model the model to be accessed. If null, it means no specific model is being accessed.
 * @param array $params additional parameters
 * @throws ForbiddenHttpException if the user does not have access
 */
public function checkAccess($action, $model = null, $params = [])
{
    // check if the user can access $action and $model
    // throw ForbiddenHttpException if access should be denied
}
```

The `checkAccess()` method will be called by the default actions of [[yii\rest\ActiveController]]. If you create
new actions and also want to perform access check, you should call this method explicitly in the new actions.

> Tip: You may implement `checkAccess()` by using the [Role-Based Access Control (RBAC) component](security-authorization.md).
