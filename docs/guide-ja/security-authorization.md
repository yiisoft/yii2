権限付与
========

権限付与は、ユーザが何かをするのに十分な許可を有しているか否かを確認するプロセスです。
Yii は二つの権限付与の方法を提供しています。すなわち、アクセス制御フィルタ (ACF) と、ロール・ベース・アクセス制御 (RBAC) です。


## アクセス制御フィルタ (ACF) <span id="access-control-filter"></span>

アクセス制御フィルタ (ACF) は、[[yii\filters\AccessControl]] として実装される単純な権限付与の方法であり、
何らかの単純なアクセス制御だけを必要とするアプリケーションで使うのに最も適したものです。
その名前が示すように、ACF は、コントローラまたはモジュールで使用することが出来るアクション [フィルタ](structure-filters.md) です。
ACF は、ユーザがアクションの実行をリクエストしたときに、一連の [[yii\filters\AccessControl::rules|アクセス規則]] をチェックして、
現在のユーザがそのアクションにアクセスする許可を持つかどうかを決定します。

下記のコードは、`site` コントローラで ACF を使う方法を示すものです。

```php
use yii\web\Controller;
use yii\filters\AccessControl;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['login', 'logout', 'signup'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'signup'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    // ...
}
```

上記のコードにおいて、ACF は `site` コントローラにビヘイビアとしてアタッチされています。これがアクション・フィルタを使用する典型的な方法です。
`only` オプションは、ACF が `login`、`logout`、`signup` のアクションにのみ適用されるべきであることを指定しています。
`site` コントローラの他の全てのアクションには ACF の影響は及びません。
`rules` オプションは [[yii\filters\AccessRule|アクセス規則]] を指定するものであり、以下のように読むことが出来ます。

- 全てのゲスト・ユーザ (まだ認証されていないユーザ) に、`login` と `singup` のアクションにアクセスすることを許可します。
  `roles` オプションに疑問符 `?` が含まれていますが、これは「ゲスト」を表す特殊なトークンです。
- 認証されたユーザに、`logout` アクションにアクセスすることを許可します。
  `@` という文字はもう一つの特殊なトークンで、「認証されたユーザ」を表すものです。

ACF による権限付与のプロセスにおいては、現在の実行コンテキストに合致する規則が見つかるまで、
アクセス規則が上から下へと一つずつ調べられます。
そして、合致したアクセス規則の `allow` の値が、ユーザが権限を有するか否かを決定するのに使われます。
合致する規則が一つもなかった場合は、ユーザが権限をもたないことを意味し、ACF はアクションの継続を中止します。

ユーザが現在のアクションにアクセスする権限を持っていないと判定した場合は、デフォルトでは、ACF は以下の手段を取ります。

* ユーザがゲストである場合は、[[yii\web\User::loginRequired()]] を呼び出して、ユーザのブラウザをログイン・ページにリダイレクトします。
* ユーザが既に認証されている場合は、[[yii\web\ForbiddenHttpException]] を投げます。

この動作は、次のように、[[yii\filters\AccessControl::denyCallback]] プロパティを構成することによって、カスタマイズすることが出来ます。

```php
[
    'class' => AccessControl::class,
    ...
    'denyCallback' => function ($rule, $action) {
        throw new \Exception('このページにアクセスする権限がありません。');
    }
]
```

[[yii\filters\AccessRule|アクセス規則]] は多くのオプションをサポートしています。以下はサポートされているオプションの要約です。
[[yii\filters\AccessRule]] を拡張して、あなた自身のカスタマイズしたアクセス規則のクラスを作ることも出来ます。

 * [[yii\filters\AccessRule::allow|allow]]: これが「許可」の規則であるか、「禁止」の規則であるかを指定します。

 * [[yii\filters\AccessRule::actions|actions]]: どのアクションにこの規則が適用されるかを指定します。
   これはアクション ID の配列でなければなりません。比較は大文字と小文字を区別します。
   このオプションが空であるか指定されていない場合は、規則が全てのアクションに適用されることを意味します。

 * [[yii\filters\AccessRule::controllers|controllers]]: どのコントローラにこの規則が適用されるかを指定します。これはコントローラ ID の配列でなければなりません。
   コントローラがモジュールに属する場合は、モジュール ID をコントローラ ID の前に付けます。比較は大文字と小文字を区別します。
   このオプションが空であるか指定されていない場合は、規則が全てのコントローラに適用されることを意味します。

 * [[yii\filters\AccessRule::roles|roles]]: どのユーザ・ロールにこの規則が適用されるかを指定します。
   二つの特別なロールが認識されます。これらは、[[yii\web\User::isGuest]] によって判断されます。

    - `?`: ゲスト・ユーザ (まだ認証されていないユーザ) を意味します。
    - `@`: 認証されたユーザを意味します。

   その他のロール名を使うと、[[yii\web\User::can()]] の呼び出しが惹起されますが、そのためには、RBAC (次のセクションで説明します) を有効にする必要があります。
   このオプションが空であるか指定されていない場合は、規則が全てのロールに適用されることを意味します。

 * [[yii\filters\AccessRule::roleParams|roleParams]]: [[yii\web\User::can()]] に渡されるパラメータを指定します。
   パラメータがどのように使われるかは、RBAC 規則を説明する後のセクションを参照して下さい。このオプションが空であるか設定されていない場合は、パラメータは渡されません。

 * [[yii\filters\AccessRule::ips|ips]]: どの [[yii\web\Request::userIP|クライアントの IP アドレス]] にこの規則が適用されるかを指定します。
   IP アドレスは、最後にワイルドカード `*` を含むことが出来て、同じプレフィクスを持つ IP アドレスに合致させることが出来ます。
   例えば、'192.168.*' は、'192.168.' のセグメントに属する全ての IP アドレスに合致します。
   このオプションが空であるか指定されていない場合は、規則が全ての IP アドレスに適用されることを意味します。

 * [[yii\filters\AccessRule::verbs|verbs]]: どのリクエスト・メソッド (HTTP 動詞、例えば `GET` や `POST`) にこの規則が適用されるかを指定します。
   比較は大文字と小文字を区別しません。

 * [[yii\filters\AccessRule::matchCallback|matchCallback]]: この規則が適用されるべきか否かを決定するために呼び出されるべき
   PHP コーラブルを指定します。

 * [[yii\filters\AccessRule::denyCallback|denyCallback]]: この規則がアクセスを禁止する場合に呼び出されるべき
   PHP コーラブルを指定します。

下記は、`matchCallback` オプションを利用する方法を示す例です。
このオプションによって、任意のアクセス制御ロジックを書くことが可能になります。

```php
use yii\filters\AccessControl;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['special-callback'],
                'rules' => [
                    [
                        'actions' => ['special-callback'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return date('d-m') === '31-10';
                        }
                    ],
                ],
            ],
        ];
    }

    // matchCallback が呼ばれる。このページは毎年10月31日だけアクセス出来ます。
    public function actionSpecialCallback()
    {
        return $this->render('happy-halloween');
    }
}
```


## ロール・ベース・アクセス制御 (RBAC) <span id="rbac"></span>

ロール・ベース・アクセス制御 (RBAC) は、単純でありながら強力な集中型のアクセス制御を提供します。
RBAC と他のもっと伝統的なアクセス制御スキーマとの比較に関する詳細については、
[Wiki 記事](https://ja.wikipedia.org/wiki/%E3%83%AD%E3%83%BC%E3%83%AB%E3%83%99%E3%83%BC%E3%82%B9%E3%82%A2%E3%82%AF%E3%82%BB%E3%82%B9%E5%88%B6%E5%BE%A1) を参照してください。

Yii は、[NIST RBAC モデル](https://csrc.nist.gov/CSRC/media/Publications/conference-paper/1992/10/13/role-based-access-controls/documents/ferraiolo-kuhn-92.pdf) に従って、一般的階層型 RBAC を実装しています。
RBAC の機能は、[[yii\rbac\ManagerInterface|authManager]] [アプリケーション・コンポーネント](structure-application-components.md) を通じて提供されます。

RBAC を使用することには、二つの作業が含まれます。
最初の作業は、RBAC 権限付与データを作り上げることであり、第二の作業は、権限付与データを使って必要とされる場所でアクセス・チェックを実行することです。

説明を容易にするために、まず、いくつかの基本的な RBAC の概念を導入します。


### 基本的な概念 <span id="basic-concepts"></span>

ロール (役割) は、*許可* (例えば、記事を作成する、記事を更新するなど) のコレクションです。
一つのロールを一人または複数のユーザに割り当てることが出来ます。
ユーザが特定の許可を有しているか否かをチェックするためには、その許可を含むロールがユーザに割り当てられているか否かをチェックすればよいのです。

各ロールまたは許可に関連付けられた *規則* が存在し得ます。
規則とは、アクセス・チェックの際に、対応するロールや許可が現在のユーザに適用されるか否かを決定するために実行されるコード断片のことです。
例えば、「記事更新」の許可は、現在のユーザが記事の作成者であるかどうかをチェックする規則を持つことが出来ます。
そして、アクセス・チェックのときに、ユーザが記事の作成者でない場合は、彼/彼女は「記事更新」の許可を持っていないと見なすことが出来ます。

ロールおよび許可は、ともに、階層的に構成することが出来ます。具体的に言えば、一つのロールは他のロールと許可を含むことが出来、
許可は他の許可を含むことが出来ます。Yii は、一般的な *半順序* 階層を実装していますが、これはその特殊形として *木* 階層を含むものです。
ロールは許可を含むことが出来ますが、許可はロールを含むことが出来ません。


### RBAC を構成する <span id="configuring-rbac"></span>

権限付与データを定義してアクセス・チェックを実行する前に、
[[yii\base\Application::authManager|authManager]] アプリケーション・コンポーネントを構成する必要があります。
Yii は二種類の権限付与マネージャを提供しています。すなわち、[[yii\rbac\PhpManager]] と [[yii\rbac\DbManager]] です。
前者は権限付与データを保存するのに PHP スクリプト・ファイルを使いますが、後者は権限付与データをデータベースに保存します。
あなたのアプリケーションが非常に動的なロールと許可の管理を必要とするのでなければ、前者を使うことを考慮するのが良いでしょう。


#### `PhpManager` を使用する <span id="using-php-manager"></span>

次のコードは、アプリケーションの構成情報で [[yii\rbac\PhpManager]] クラスを使って `authManager` を構成する方法を示すものです。

```php
return [
    // ...
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        // ...
    ],
];
```

これで `authManager` は `\Yii::$app->authManager` によってアクセスすることが出来るようになります。

デフォルトでは、[[yii\rbac\PhpManager]] は RBAC データを `@app/rbac/` ディレクトリの下のファイルに保存します。
権限の階層をオンラインで変更する必要がある場合は、必ず、ウェブ・サーバのプロセスがこのディレクトリとその中の全てのファイルに対する書き込み権限を有するようにしてください。


#### `DbManager` を使用する <span id="using-db-manager"></span>

次のコードは、アプリケーションの構成情報で [[yii\rbac\DbManager]] クラスを使って `authManager` を構成する方法を示すものです。

```php
return [
    // ...
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // RBAC アイテムの階層をキャッシュしたい場合はコメントを外す
            // 'cache' => 'cache',
        ],
        // ...
    ],
];
```
> Note: yii2-basic-app テンプレートを使おうとする場合は、`config/web.php` に加えて、
  `config/console.php` 構成ファイルにおいても `authManager` を宣言する必要があります。
> yii2-advanced-app の場合は、`authManager` は `common/config/main.php` で一度だけ宣言されなければなりません。

`DbManager` は四つのデータベース・テーブルを使ってデータを保存します。

- [[yii\rbac\DbManager::$itemTable|itemTable]]: 権限アイテムを保存するためのテーブル。デフォルトは "auth_item"。
- [[yii\rbac\DbManager::$itemChildTable|itemChildTable]]: 権限アイテムの階層を保存するためのテーブル。デフォルトは "auth_item_child"。
- [[yii\rbac\DbManager::$assignmentTable|assignmentTable]]: 権限アイテムの割り当てを保存するためのテーブル。デフォルトは "auth_assignment"。
- [[yii\rbac\DbManager::$ruleTable|ruleTable]]: 規則を保存するためのテーブル。デフォルトは "auth_rule"。

先に進む前にこれらのテーブルをデータベースに作成する必要があります。そのためには、`@yii/rbac/migrations` に保存されているマイグレーションを使うことが出来ます。

`yii migrate --migrationPath=@yii/rbac/migrations`

異なる名前空間のマイグレーションを扱う方法の詳細については
[分離されたマイグレーション](db-migrations.md#separated-migrations) のセクションを参照して下さい。

これで `authManager` は `\Yii::$app->authManager` によってアクセスすることが出来るようになります。


### 権限付与データを構築する <span id="generating-rbac-data"></span>

権限付与データを構築する作業は、つまるところ、以下のタスクに他なりません。

- ロールと許可を定義する
- ロールと許可の関係を定義する
- 規則を定義する
- 規則をロールと許可に結び付ける
- ロールをユーザに割り当てる

権限付与に要求される柔軟性の程度によって、上記のタスクのやりかたも異なってきます。
許可の階層構造が開発者によってのみ変更されることを意図する場合は、
マイグレーションまたはコンソールコマンドを使うことが出来ます。
マイグレーションを使う場合の利点は、他のマイグレーションと一緒に実行できることです。
コンソール・コマンドを使う場合の利点は、階層構造の全体が、複数のマイグレーションに分散することなく、コード中に見やすい形で保たれることです。

どちらの方法でも、結局は次のような RBAC 階層を得ることになります。

![単純な RBAC 階層](images/rbac-hierarchy-1.png "単純な RBAC 階層")

許可の階層構造が動的に形成される必要がある場合は、UI またはコンソール・コマンドが必要になります。
階層構造そのものを構築するために使用される API には違いはありません。

#### マイグレーションを使う

[マイグレーション](db-migrations.md) を使って、
`authManager` が提供する API によって階層を初期化したり変更したりすることが出来ます。

`./yii migrate/create init_rbac` を使って新しいマイグレーションを作成し、階層の作成を実装します。

```php
<?php
use yii\db\Migration;

class m170124_084304_init_rbac extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;

        // "createPost" という許可を追加する
        $createPost = $auth->createPermission('createPost');
        $createPost->description = '記事を投稿';
        $auth->add($createPost);

        // "updatePost" という許可を追加する
        $updatePost = $auth->createPermission('updatePost');
        $updatePost->description = '記事を更新';
        $auth->add($updatePost);

        // "author" ロールを追加し、このロールに "createPost" の許可を付与する
        $author = $auth->createRole('author');
        $auth->add($author);
        $auth->addChild($author, $createPost);

        // "admin" ロールを追加し、このロールに "updatePost" の許可を付与する
        // 同時に、"author" ロールが持つ許可も付与する
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $updatePost);
        $auth->addChild($admin, $author);

        // ロールをユーザに割り当てる。1 と 2 は IdentityInterface::getId() によって返される ID
        // IdentityInterface::getId() は、通常は User モデルの中で実装される
        $auth->assign($author, 2);
        $auth->assign($admin, 1);
    }
    
    public function down()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();
    }
}
```

> どのユーザにどのロールを割り当てるかをハードコードしたくない場合は、マイグレーションに `->assign()` の呼び出しを書かないで下さい。
  その代りに、ロールの割り当てを管理する UI またはコンソール・コマンドを作成して下さい。

マイグレーションは `yii migrate` を使って適用することが出来ます。

### コンソール・コマンドを使う

許可の階層が全く変化せず、決った数のユーザしか存在しない場合は、
`authManager` が提供する API によって権限付与データを一回だけ初期設定する [コンソール・コマンド](tutorial-console.md#create-command)
を作ることが出来ます。

```php
<?php
namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        // "createPost" という許可を追加する
        $createPost = $auth->createPermission('createPost');
        $createPost->description = '記事を投稿';
        $auth->add($createPost);

        // "updatePost" という許可を追加
        $updatePost = $auth->createPermission('updatePost');
        $updatePost->description = '記事を更新';
        $auth->add($updatePost);

        // "author" というロールを追加し、このロールに "createPost" の許可を付与する
        $author = $auth->createRole('author');
        $auth->add($author);
        $auth->addChild($author, $createPost);

        // "admin" というロールを追加し、このロールに "updatePost" 許可を付与する
        // 同時に、"author" ロールが持つ許可も付与する
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $updatePost);
        $auth->addChild($admin, $author);

        // ロールをユーザに割り当てる。1 と 2 は IdentityInterface::getId() によって返される ID
        // IdentityInterface::getId() は、通常は User モデルの中で実装される
        $auth->assign($author, 2);
        $auth->assign($admin, 1);
    }
}
```

> Note: アドバンスト・テンプレートを使おうとするときは、`RbacController` を `console/controllers`
  ディレクトリの中に置いて、名前空間を `console\controllers` に変更する必要があります。

上記のコマンドは、コンソールから次のようにして実行することが出来ます。

```
yii rbac/init
```

> どのユーザにどのロールを割り当てるかをハードコードしたくない場合は、  コマンドに `->assign()` の呼び出しを書かないで下さい。
  その代りに、ロールの割り当てを管理する UI またはコンソール・コマンドを作成して下さい。

## ロールをユーザに割り当てる

投稿者 (author) は記事を投稿することが出来、管理者 (admin) は記事を更新することに加えて投稿者が出来る全てのことが出来ます。

あなたのアプリケーションがユーザ自身によるユーザ登録を許している場合は、新しく登録されたユーザに一度はロールを割り当てる必要があります。
例えば、アドバンスト・プロジェクト・テンプレートにおいては、登録したユーザの全てを「投稿者」にするために、
`frontend\models\SignupForm::signup()` を次のように修正しなければなりません。

```php
public function signup()
{
    if ($this->validate()) {
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->save(false);

        // 次の三行が追加されたものです
        $auth = Yii::$app->authManager;
        $authorRole = $auth->getRole('author');
        $auth->assign($authorRole, $user->getId());

        return $user;
    }

    return null;
}
```

動的に更新される権限付与データを持つ複雑なアクセス制御を必要とするアプリケーションについては、
`authManager` が提供する API を使って、特別なユーザ・インタフェイス (つまり、管理パネル) を開発する必要があるでしょう。


### 規則を使う <span id="using-rules"></span>

既に述べたように、規則がロールと許可に制約を追加します。規則は [[yii\rbac\Rule]] を拡張したクラスであり、
[[yii\rbac\Rule::execute()|execute()]] メソッドを実装しなければなりません。前に作った権限階層においては、投稿者は自分自身の記事を編集することが出来ませんでした。
これを修正しましょう。最初に、ユーザが記事の投稿者であることを確認する規則が必要です。

```php
namespace app\rbac;

use yii\rbac\Rule;
use app\models\Post;

/**
 * authorID がパラメータで渡されたユーザと一致するかチェックする
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';

    /**
     * @param string|int $user ユーザ ID
     * @param Item $item この規則が関連付けられているロールまたは許可
     * @param array $params ManagerInterface::checkAccess() に渡されたパラメータ
     * @return bool 関連付けられたロールまたは許可を認めるか否かを示す値
     */
    public function execute($user, $item, $params)
    {
        return isset($params['post']) ? $params['post']->createdBy == $user : false;
    }
}
```

上の規則は、`post` が `$user` によって作成されたかどうかをチェックします。
次に、前に使ったコマンドの中で、`updateOwnPost` という特別な許可を作成します。

```php
$auth = Yii::$app->authManager;

// 規則を追加する
$rule = new \app\rbac\AuthorRule;
$auth->add($rule);

// "updateOwnPost" という許可を作成し、それに規則を関連付ける
$updateOwnPost = $auth->createPermission('updateOwnPost');
$updateOwnPost->description = '自分の記事を更新';
$updateOwnPost->ruleName = $rule->name;
$auth->add($updateOwnPost);

// "updateOwnPost" は "updatePost" から使われる
$auth->addChild($updateOwnPost, $updatePost);

// "author" に自分の記事を更新することを許可する
$auth->addChild($author, $updateOwnPost);
```

これで、次のような権限階層になります。

![規則を持つ RBAC 階層](images/rbac-hierarchy-2.png "規則を持つ RBAC 階層")


### アクセス・チェック <span id="access-check"></span>

権限付与データが準備できてしまえば、アクセス・チェックは [[yii\rbac\ManagerInterface::checkAccess()]] メソッドを呼ぶだけの簡単な仕事です。
たいていのアクセス・チェックは現在のユーザに関するものですから、Yii は、便利なように、[[yii\web\User::can()]] というショートカット・メソッドを提供しています。
これは、次のようにして使うことが出来ます。

```php
if (\Yii::$app->user->can('createPost')) {
    // 記事を作成する
}
```

現在のユーザが `ID=1` である Jane であるとすると、`createPost` からスタートして `Jane` まで到達しようと試みます。

![アクセス・チェック](images/rbac-access-check-1.png "アクセス・チェック")

ユーザが記事を更新することが出来るかどうかをチェックするためには、前に説明した `AuthorRule` によって要求される追加のパラメータを渡す必要があります。

```php
if (\Yii::$app->user->can('updatePost', ['post' => $post])) {
    // 記事を更新する
}
```

現在のユーザが John であるとすると、次の経路をたどります。


![アクセス・チェック](images/rbac-access-check-2.png "アクセス・チェック")

`updatePost` からスタートして、`updateOwnPost` を通過します。通過するためには、`AuthorRule` が `execute` メソッドで `true` を返さなければなりません。
`execute` メソッドは `can` メソッドの呼び出しから `$params` を受け取りますので、その値は `['post' => $post]` です。
すべて OK であれば、John に割り当てられている `author` に到達します。

Jane の場合は、彼女が管理者であるため、少し簡単になります。

![アクセス・チェック](images/rbac-access-check-3.png "アクセス・チェック")

コントローラ内で権限付与を実装するのには、いくつかの方法があります。
追加と削除に対するアクセス権を分離する細分化された許可が必要な場合は、それぞれのアクションに対してアクセス権をチェックする必要があります。
各アクション・メソッドの中で上記の条件を使用するか、または [[yii\filters\AccessControl]] を使います。

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'roles' => ['managePost'],
                ],
                [
                    'allow' => true,
                    'actions' => ['view'],
                    'roles' => ['viewPost'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => ['createPost'],
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],
                    'roles' => ['updatePost'],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'roles' => ['deletePost'],
                ],
            ],
        ],
    ];
}
```

全ての CRUD 操作がまとめて管理される場合は、`managePost` のような単一の許可を使い、
[[yii\web\Controller::beforeAction()]] の中でそれをチェックするのが良いアイデアです。

上記の例では、アクションにアクセスするために必要と指定されたロールについて、パラメータは渡されていません。
しかし、`updatePost` 許可の場合は、それが正しく動作するためには `post` パラメータを渡す必要があります。
アクセス規則の中で [[yii\filters\AccessRule::roleParams|roleParams]] を指定することによって、
[[yii\web\User::can()]] にパラメータを渡すことが出来ます。

```php
[
    'allow' => true,
    'actions' => ['update'],
    'roles' => ['updatePost'],
    'roleParams' => function() {
        return ['post' => Post::findOne(['id' => Yii::$app->request->get('id')])];
    },
],
```

上記の例では、[[yii\filters\AccessRule::roleParams|roleParams]] はアクセス規則がチェックされるときに評価されるクロージャになっています。
従って、モデルは必要になったときだけロードされます。
ロール・パラメータの作成が簡単な操作である場合は、次のように、単に配列を指定しても構いません。

```php
[
    'allow' => true,
    'actions' => ['update'],
    'roles' => ['updatePost'],
    'roleParams' => ['postId' => Yii::$app->request->get('id')],
],
```

### デフォルト・ロールを使う <span id="using-default-roles"></span>

デフォルト・ロールというのは、*全て* のユーザに *黙示的* に割り当てられるロールです。
[[yii\rbac\ManagerInterface::assign()]] を呼び出す必要はなく、権限付与データはその割り当て情報を含みません。

デフォルト・ロールは、通常、そのロールが当該ユーザに適用されるかどうかを決定する規則と関連付けられます。

デフォルト・ロールは、たいていは、何らかのロールの割り当てを既に持っているアプリケーションにおいて使われます。
例えば、アプリケーションによっては、ユーザのテーブルに "group" というカラムを持って、個々のユーザが属する特権グループを表している場合があります。
それぞれの特権グループを RBAC ロールに対応付けることが出来るのであれば、デフォルト・ロールの機能を使って、それぞれのユーザに RBAC ロールを自動的に割り当てることが出来ます。
どのようにすればこれが出来るのか、例を使って説明しましょう。

ユーザのテーブルに `group` というカラムがあって、1 は管理者グループ、2 は投稿者グループを示していると仮定しましょう。
これら二つのグループの権限を表すために、それぞれ、`admin` と `author` という RBAC ロールを作ることにします。
このとき、次のように RBAC データをセットアップすることが出来ます。


```php
namespace app\rbac;

use Yii;
use yii\rbac\Rule;

/**
 * ユーザのグループが合致するかどうかをチェックする
 */
class UserGroupRule extends Rule
{
    public $name = 'userGroup';

    public function execute($user, $item, $params)
    {
        if (!Yii::$app->user->isGuest) {
            $group = Yii::$app->user->identity->group;
            if ($item->name === 'admin') {
                return $group == 1;
            } elseif ($item->name === 'author') {
                return $group == 1 || $group == 2;
            }
        }
        return false;
    }
}
```

次に、[前のセクション](#generating-rbac-data) で説明したように、あなた独自のコマンド/マイグレーションを作成します。

```php
$auth = Yii::$app->authManager;

$rule = new \app\rbac\UserGroupRule;
$auth->add($rule);

$author = $auth->createRole('author');
$author->ruleName = $rule->name;
$auth->add($author);
// ... $author の子として許可を追加 ...

$admin = $auth->createRole('admin');
$admin->ruleName = $rule->name;
$auth->add($admin);
$auth->addChild($admin, $author);
// ... $admin の子として許可を追加 ...
```

上記において、"author" が "admin" の子として追加されているため、規則クラスの `execute()` メソッドを実装する時には、
この階層関係にも配慮しなければならないことに注意してください。
このために、ロール名が "author" である場合には、`execute()` メソッドは、ユーザのグループが 1 または 2 である
(ユーザが "admin" グループまたは "author" グループに属している) ときに true を返しています。

次に、`authManager` の構成情報で、この二つのロールを [[yii\rbac\BaseManager::$defaultRoles]] としてリストします。

```php
return [
    // ...
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['admin', 'author'],
        ],
        // ...
    ],
];
```

このようにすると、アクセス・チェックを実行すると、`admin` と `author` の両方のロールは、それらと関連付けられた規則を評価することによってチェックされるようになります。
規則が true を返せば、そのロールが現在のユーザに適用されることになります。
上述の規則の実装に基づいて言えば、ユーザの `group` の値が 1 であれば `admin` ロールがユーザに適用され、
`group` の値が 2 であれば `author` ロールが適用されるということを意味します。
