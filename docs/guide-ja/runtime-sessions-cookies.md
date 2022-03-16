セッションとクッキー
====================

セッションとクッキーは、データが複数回のユーザ・リクエストにまたがって持続することを可能にします。
素の PHP では、それぞれ、グローバル変数 `$_SESSION` と `$_COOKIE` によってアクセスすることが出来ます。
Yii はセッションとクッキーをオブジェクトとしてカプセル化し、オブジェクト指向の流儀でアクセスできるようにするとともに、有用な機能強化を追加しています。


## セッション <span id="sessions"></span>

[リクエスト](runtime-requests.md) や [レスポンス](runtime-responses.md) と同じように、
デフォルトでは [[yii\web\Session]] のインスタンスである `session` [アプリケーション・コンポーネント] によって、
セッションにアクセスすることが出来ます。


### セッションのオープンとクローズ <span id="opening-closing-sessions"></span>

セッションのオープンとクローズは、次のようにして出来ます。

```php
$session = Yii::$app->session;

// セッションが既に開かれているかチェックする
if ($session->isActive) ...

// セッションを開く
$session->open();

// セッションを閉じる
$session->close();

// セッションに登録されている全てのデータを破壊する
$session->destroy();
```

エラーを発生させずに [[yii\web\Session::open()|open()]] と [[yii\web\Session::close()|close()]] を複数回呼び出すことが出来ます。
内部的には、これらのメソッドは、セッションが既に開かれているかどうかを最初にチェックします。


### セッション・データにアクセスする <span id="access-session-data"></span>

セッションに保存されているデータにアクセスするためには、次のようにすることが出来ます。

```php
$session = Yii::$app->session;

// セッション変数を取得する。次の三つの用法は等価。
$language = $session->get('language');
$language = $session['language'];
$language = isset($_SESSION['language']) ? $_SESSION['language'] : null;

// セッション変数を設定する。次の三つの用法は等価。
$session->set('language', 'en-US');
$session['language'] = 'en-US';
$_SESSION['language'] = 'en-US';

// セッション変数を削除する。次の三つの用法は等価。
$session->remove('language');
unset($session['language']);
unset($_SESSION['language']);

// セッション変数が存在するかどうかをチェックする。次の三つの用法は等価。
if ($session->has('language')) ...
if (isset($session['language'])) ...
if (isset($_SESSION['language'])) ...

// 全てのセッション変数をたどる。次の二つの用法は等価。
foreach ($session as $name => $value) ...
foreach ($_SESSION as $name => $value) ...
```

> Info: `session` コンポーネントによってセッション・データにアクセスする場合は、まだ開かれていないときは、自動的にセッションが開かれます。
  これに対して `$_SESSION` によってセッション・データにアクセスする場合は、
  `session_start()` を明示的に呼び出すことが必要になります。

配列であるセッション・データを扱う場合、`session` コンポーネントには、配列の要素を直接修正することができない、という制約があります。
例えば、

```php
$session = Yii::$app->session;

// 次のコードは動かない
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// 次のコードは動く
$session['captcha'] = [
    'number' => 5,
    'lifetime' => 3600,
];

// 次のコードも動く
echo $session['captcha']['lifetime'];
```

次の回避策のどれかを使ってこの問題を解決することが出来ます。

```php
$session = Yii::$app->session;

// $_SESSION を直接使う (既に Yii::$app->session->open() が呼び出されていることを確認)
$_SESSION['captcha']['number'] = 5;
$_SESSION['captcha']['lifetime'] = 3600;

// 配列全体を取得し、修正して、保存しなおす
$captcha = $session['captcha'];
$captcha['number'] = 5;
$captcha['lifetime'] = 3600;
$session['captcha'] = $captcha;

// 配列の代わりに ArrayObject を使う
$session['captcha'] = new \ArrayObject;
...
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// 共通の接頭辞を持つキーを使って配列データを保存する
$session['captcha.number'] = 5;
$session['captcha.lifetime'] = 3600;
```

パフォーマンスとコードの可読性を高めるためには、最後の回避策を推奨します。
すなわち、配列を一つのセッション変数として保存する代りに、配列の個々の要素を、他の配列要素と共通の接頭辞を持つ、
個別のセッション変数として保存することです。


### カスタム・セッション・ストレージ <span id="custom-session-storage"></span>

デフォルトの [[yii\web\Session]] クラスはセッション・データをサーバ上のファイルとして保存します。
Yii は、また、さまざまなセッション・ストレージを実装する下記のクラスをも提供しています。

* [[yii\web\DbSession]]: セッション・データをデータベース・テーブルを使って保存する。
* [[yii\web\CacheSession]]: セッション・データを、構成された [キャッシュ・コンポーネント](caching-data.md#cache-components) の力を借りて、キャッシュを使って保存する。
* [[yii\redis\Session]]: セッション・データを [redis](https://redis.io/) をストレージ媒体として使って保存する。
* [[yii\mongodb\Session]]: セッション・データを [MongoDB](https://www.mongodb.com/) に保存する。

これらのセッション・クラスは全て一連の同じ API メソッドをサポートします。
その結果として、セッションを使用するアプリケーション・コードを修正することなしに、セッション・ストレージ・クラスを切り替えることが出来ます。

> Note: カスタム・セッション・ストレージを使っているときに `$_SESSION` を通じてセッション・データにアクセスしたい場合は、
  セッションが [[yii\web\Session::open()]] によって既に開始されていることを確認しなければなりません。
  これは、カスタム・セッション・ストレージのハンドラは、この `open()` メソッドの中で登録されるからです。

> Note: カスタム・セッション・ストレージを使うときは、セッションのガーベッジ・コレクタを明示的に構成する必要がある場合があります。
  PHP のインストレーションによっては(例えば Debian では)、ガーベッジ・コレクタの蓋然性は 0 とされ、セッション・ファイルはクロン・ジョブでオフラインで消去することになっています。
  このプロセスはあなたのカスタム・セッション・ストレージには当てはまりませんので、
  [[yii\web\Session::$GCProbability]] に 0 でない値を設定して構成しなければなりません。
  
これらのコンポーネント・クラスの構成方法と使用方法については、それらの API ドキュメントを参照してください。
下記の例は、アプリケーションの構成情報において、データベース・テーブルをセッション・ストレージとして使うために [[yii\web\DbSession]]
を構成する方法を示すものです。

```php
return [
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',
            // 'db' => 'mydb',  // DB 接続のアプリケーション・コンポーネント ID。デフォルトは 'db'。
            // 'sessionTable' => 'my_session', // セッション・テーブル名。デフォルトは 'session'。
        ],
    ],
];
```

セッション・データを保存するために、次のようなデータベース・テーブルを作成することも必要です。

```sql
CREATE TABLE session
(
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```

ここで 'BLOB' はあなたが選んだ DBMS の BLOB 型を指します。下記は人気のあるいくつかの DBMS で使用できる BLOB 型です。

- MySQL: LONGBLOB
- PostgreSQL: BYTEA
- MSSQL: BLOB

> Note: php.ini の `session.hash_function` の設定によっては、`id` カラムの長さを修正する必要があるかも知れません。
  例えば、`session.hash_function=sha256` である場合は
  40 の代りに 64 の長さを使わなければなりません。

別の方法として、次のマイグレーションを使ってこれを達成することも出来ます。

```php
<?php

use yii\db\Migration;

class m170529_050554_create_table_session extends Migration
{
    public function up()
    {
        $this->createTable('{{%session}}', [
            'id' => $this->char(64)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->binary()
        ]);
        $this->addPrimaryKey('pk-id', '{{%session}}', 'id');
    }

    public function down()
    {
        $this->dropTable('{{%session}}');
    }
}
```

### フラッシュ・データ <span id="flash-data"></span>

フラッシュ・データは特殊な種類のセッション・データで、あるリクエストの中で設定されると、
次のリクエストの間においてのみ読み出すことが出来て、その後は自動的に削除されるものです。
フラッシュ・データが最もよく使われるのは、エンド・ユーザに一度だけ表示されるべきメッセージ、
例えば、ユーザのフォーム送信が成功した後に表示される確認メッセージなどを実装するときです。

`session` アプリケーション・コンポーネントによって、フラッシュ・データを設定し、アクセスすることが出来ます。例えば、

```php
$session = Yii::$app->session;

// リクエスト #1
// "postDeleted" という名前のフラッシュ・メッセージを設定する
$session->setFlash('postDeleted', '投稿の削除に成功しました。');

// リクエスト #2
// "postDeleted" という名前のフラッシュ・メッセージを表示する
echo $session->getFlash('postDeleted');

// リクエスト #3
// フラッシュ・メッセージは自動的に削除されるので、$result は false になる
$result = $session->hasFlash('postDeleted');
```

通常のセッション・データと同様に、任意のデータをフラッシュ・データとして保存することが出来ます。

[[yii\web\Session::setFlash()]] を呼び出すと、同じ名前の既存のフラッシュ・データは上書きされます。
同じ名前の既存のメッセージに新しいフラッシュ・データを追加するためには、代りに [[yii\web\Session::addFlash()]] を使うことが出来ます。
例えば、

```php
$session = Yii::$app->session;

// リクエスト #1
// "alerts" という名前の下にフラッシュ・メッセージを追加する
$session->addFlash('alerts', '投稿の削除に成功しました。');
$session->addFlash('alerts', '友達の追加に成功しました。');
$session->addFlash('alerts', 'あなたのレベルが上りました。');

// リクエスト #2
// $alerts は "alerts" という名前の下にあるフラッシュ・メッセージの配列となる
$alerts = $session->getFlash('alerts');
```

> Note: 同じ名前のフラッシュ・データに対して、[[yii\web\Session::setFlash()]] と [[yii\web\Session::addFlash()]] を一緒に使わないようにしてください。
  これは、後者のメソッドが、同じ名前のフラッシュ・データを追加できるように、
  フラッシュ・データを自動的に配列に変換するからです。
  その結果、[[yii\web\Session::getFlash()]] を呼び出したとき、この二つのメソッドを呼び出した順序に従って、
  あるときは配列を受け取り、あるときは文字列を受け取るということになります。

> Tip: フラッシュ・メッセージを表示するためには、[[yii\bootstrap\Alert|bootstrap Alert]] ウィジェットを次のように使用することが出来ます。
>
> ```php
> echo Alert::widget([
>    'options' => ['class' => 'alert-info'],
>    'body' => Yii::$app->session->getFlash('postDeleted'),
> ]);
> ```


## クッキー <span id="cookies"></span>

Yii は個々のクッキーを [[yii\web\Cookie]] のオブジェクトとして表します。
[[yii\web\Request]] と [[yii\web\Response]] は、ともに、`cookies` という名前のプロパティによって、クッキーのコレクションを保持します。
前者のクッキー・コレクションはリクエストの中で送信されてきたクッキーを表し、
一方、後者のクッキー・コレクションは、これからユーザに送信されるクッキーを表します。

アプリケーションで、リクエストとレスポンスを直接に操作する部分は、コントローラです。
従って、クッキーの読み出しと送信はコントローラで実行されるべきです。

### クッキーを読み出す <span id="reading-cookies"></span>

現在のリクエストに含まれるクッキーは、下記のコードを使って取得することが出来ます。

```php
// "request" コンポーネントからクッキー・コレクション (yii\web\CookieCollection) を取得する。
$cookies = Yii::$app->request->cookies;

// "language" というクッキーの値を取得する。クッキーが存在しない場合は、デフォルト値として "en" を返す。
$language = $cookies->getValue('language', 'en');

// "language" というクッキーの値を取得する別の方法。
if (($cookie = $cookies->get('language')) !== null) {
    $language = $cookie->value;
}

// $cookies を配列のように使うことも出来る。
if (isset($cookies['language'])) {
    $language = $cookies['language']->value;
}

// "language" というクッキーが在るかどうかチェックする。
if ($cookies->has('language')) ...
if (isset($cookies['language'])) ...
```


### クッキーを送信する <span id="sending-cookies"></span>

下記のコードを使って、クッキーをエンド・ユーザに送信することが出来ます。

```php
// "response" コンポーネントからクッキー・コレクション (yii\web\CookieCollection) を取得する。
$cookies = Yii::$app->response->cookies;

// 送信されるレスポンスに新しいクッキーを追加する。
$cookies->add(new \yii\web\Cookie([
    'name' => 'language',
    'value' => 'zh-CN',
]));

// クッキーを削除する。
$cookies->remove('language');
// 次のようにしても同じ。
unset($cookies['language']);
```

[[yii\web\Cookie]] クラスは、上記の例で示されている [[yii\web\Cookie::name|name]] と [[yii\web\Cookie::value|value]]
のプロパティ以外にも、[[yii\web\Cookie::domain|domain]] や [[yii\web\Cookie::expire|expire]] など、
他のプロパティを定義して、利用可能なクッキー情報の全てを完全に表しています。
クッキーを準備するときに必要に応じてこれらのプロパティを構成してから、レスポンスのクッキー・コレクションに追加することが出来ます。

### クッキー検証 <span id="cookie-validation"></span>

最後の二つの項で示されているように、`request` と `response` のコンポーネントを通じてクッキーを読んだり送信したりする場合には、
クッキーがクライアント・サイドで修正されるのを防止するクッキー検証という追加のセキュリティを享受することが出来ます。
これは、個々のクッキーにハッシュ文字列をサインとして追加することによって達成されます。
アプリケーションは、サインを見て、クッキーがクライアント・サイドで修正されたかどうかを知ることが出来ます。
もし、修正されていれば、そのクッキーは `request` コンポーネントの [[yii\web\Request::cookies|クッキー・コレクション]] からはアクセスすることが出来なくなります。

> Note: クッキー検証は値が修正されたクッキーの読み込みを防止するだけです。
  検証に失敗した場合でも、`$_COOKIE` を通じてそのクッキーにアクセスすることは引き続いて可能です。
  これは、サードパーティのライブラリが、クッキー検証を含まない独自の方法でクッキーを操作することが出来るようにするするためです。

クッキー検証はデフォルトで有効になっています。
[[yii\web\Request::enableCookieValidation]] プロパティを `false` に設定することによって無効にすることが出来ますが、無効にしないことを強く推奨します。

> Note: `$_COOKIE` と `setcookie()` によって直接に 読み出し/送信 されるクッキーは検証されません。

クッキー検証を使用する場合は、前述のハッシュ文字列を生成するために使用される [[yii\web\Request::cookieValidationKey]] を指定しなければなりません。
アプリケーションの構成情報で `request` コンポーネントを構成することによって、そうすることが出来ます。

```php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'ここに秘密のキーを書く',
        ],
    ],
];
```

> Info: [[yii\web\Request::cookieValidationKey|cookieValidationKey]] は、あなたのアプリケーションにとって、決定的に重要なものです。
  これは信頼する人にだけ教えるべきものです。バージョン・コントロール・システムに保存してはいけません。

## セキュリティの設定

[[yii\web\Cookie]] と [[yii\web\Session]] の両者は下記のセキュリティ・フラグをサポートしています。

### httpOnly

セキュリティを向上させるために、[[yii\web\Cookie::httpOnly]] および [[yii\web\Session::cookieParams]] の 'httponly' パラメータの
デフォルト値は `true` に設定されています。
これによって、クライアント・サイド・スクリプトが保護されたクッキーにアクセスする危険が軽減されます (ブラウザがサポートしていれば)。
詳細については、[httpOnly の wiki 記事](https://owasp.org/www-community/HttpOnly) を読んでください。

### secure

secure フラグの目的は、クッキーが平文で送信されることを防止することです。ブラウザが secure フラグをサポートしている場合、
リクエストが secure な接続 (TLS) によって送信される場合にのみクッキーがリクエストに含まれます。
詳細については [Secure フラグの wiki 記事](https://owasp.org/www-community/controls/SecureCookieAttribute) を参照して下さい。

### sameSite

Yii 2.0.21 以降、[[yii\web\Cookie::sameSite]] 設定がサポートされています。これは PHP バージョン 7.3.0 以降を必要とします。
`sameSite` 設定の目的は CSRF (Cross-Site Request Forgery) 攻撃を防止することです。
ブラウザが `sameSite` 設定をサポートしている場合、指定されたポリシー ('Lax' または 'Strict') に従うクッキーだけが送信されます。
詳細については [SameSite の wiki 記事](https://owasp.org/www-community/SameSite) を参照して下さい。
更なるセキュリティ強化のために、`sameSite` がサポートされていない PHP のバージョンで使われた場合には例外が投げられます。
この機能を PHP のバージョンに関わりなく使用する場合は、最初にバージョンをチェックして下さい。例えば、
```php
[
    'sameSite' => PHP_VERSION_ID >= 70300 ? yii\web\Cookie::SAME_SITE_LAX : null,
]
```
> Note: 今はまだ `sameSite` 設定をサポートしていないブラウザもありますので、
  [追加の CSRF 保護](security-best-practices.md#avoiding-csrf) を行うことを強く推奨します。

## セッションに関する php.ini の設定

[PHP マニュアル](https://www.php.net/manual/ja/session.security.ini.php) で示されているように、`php.ini` にはセッションのセキュリティに関する重要な設定があります。
推奨される設定を必ず適用して下さい。特に、PHP インストールのデフォルトでは有効にされていない
`session.use_strict_mode` を有効にして下さい。
この設定は [[yii\web\Session::useStrictMode]] を使って設定することも出来ます。
