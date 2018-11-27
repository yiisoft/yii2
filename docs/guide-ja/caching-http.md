HTTP キャッシュ
===============

これまでのセクションで説明したサーバ・サイドのキャッシュに加えて、ウェブ・アプリケーションは、
同じページ・コンテントを生成し送信する時間を節約するために、クライアント・サイドでもキャッシュを利用することができます。

クライアント・サイドのキャッシュを使用するには、レンダリング結果をキャッシュできるように、
コントローラ・アクションのフィルタとして [[yii\filters\HttpCache]] を設定します。
[[yii\filters\HttpCache]] は `GET` と `HEAD` リクエストに対してのみ動作し、それらのリクエストに対する 3 種類のキャッシュ関連の HTTP ヘッダを扱うことができます:

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## `Last-Modified` ヘッダ <span id="last-modified"></span>

`Last-Modified` ヘッダは、クライアントがキャッシュした時からページが変更されたかどうかを示すために、タイムスタンプを使用しています。

`Last-Modified` ヘッダの送信を有効にするには [[yii\filters\HttpCache::lastModified]] プロパティを構成します。
このプロパティは、ページの更新時刻に関する UNIX タイムスタンプを返す PHP のコーラブルでなければなりません。
この PHP コーラブルのシグニチャは以下のとおりです。

```php
/**
 * @param Action $action 現在扱っているアクション・オブジェクト
 * @param array $params "params" プロパティの値
 * @return int ページの更新時刻を表す UNIX タイムスタンプ
 */
function ($action, $params)
```

以下は `Last-Modified` ヘッダを使用する例です:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('post')->max('updated_at');
            },
        ],
    ];
}
```

上記のコードは `index` アクションでのみ HTTP キャッシュを有効にすべきことを記述しています。
`Last-Modified` は、投稿の最終更新時刻に基づいて生成される必要があります。
ブラウザが初めて `index` ページにアクセスしたときは、ページはサーバ上で生成されブラウザに送信されます。
もしブラウザが再度同じページにアクセスし、その期間中に投稿に変更がない場合は、サーバはページを再生成せず、
ブラウザはクライアント・サイドにキャッシュしたものを使用します。
その結果、サーバ・サイドのレンダリング処理とページ・コンテントの送信は両方ともスキップされます。


## `ETag` ヘッダ <span id="etag"></span>

"Entity Tag" (略して `ETag`) ヘッダはページ・コンテントを表すためのハッシュです。
ページが変更された場合ハッシュも同様に変更されます。
サーバ・サイドで生成されたハッシュとクライアント・サイドで保持しているハッシュを比較することによって、ページが変更されたかどうか、そして再送信するべきかどうかを決定します。

`ETag` ヘッダの送信を有効にするには [[yii\filters\HttpCache::etagSeed]] プロパティを設定します。
プロパティは ETag のハッシュを生成するためのシードを返す PHP のコーラブルで、
以下のようなシグネチャを持たなければなりません。

```php
/**
 * @param Action $action 現在扱っているアクション・オブジェクト
 * @param array $params "params" プロパティの値
 * @return string ETag のハッシュを生成するためのシードとして使用する文字列
 */
function ($action, $params)
```

以下は `ETag` ヘッダを使用している例です:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['view'],
            'etagSeed' => function ($action, $params) {
                $post = $this->findModel(\Yii::$app->request->get('id'));
                return serialize([$post->title, $post->content]);
            },
        ],
    ];
}
```

上記のコードは `view` アクションでのみ HTTP キャッシュを有効にすべきことを記述しています。
`Etag` HTTP ヘッダは、リクエストされた投稿のタイトルとコンテントに基づいて生成されなければなりません。
ブラウザが初めて `view` ページにアクセスしたときは、ページがサーバ上で生成されブラウザに送信されます。
ブラウザが再度同じページにアクセスし、投稿のタイトルやコンテントに変更がない場合には、
サーバはページを再生成せず、ブラウザはクライアント・サイドにキャッシュしたものを使用します。
その結果、サーバ・サイドのレンダリング処理とページ・コンテント送信は両方ともスキップされます。

ETag は `Last-Modified` ヘッダよりも複雑 かつ/または より正確なキャッシング方式を可能にします。
例えば、サイトが別のテーマに切り替わった場合には ETag を無効化する、といったことができます。

ETag はリクエスト毎に再評価する必要があるため、負荷の高い生成方法を使うと `HttpCache`
の本来の目的を損なって不必要なオーバーヘッドが生じる場合があります。
ページのコンテントが変更されたときにキャッシュを無効化するための式は単純なものを指定するようにして下さい。

> Note: [RFC 7232](http://tools.ietf.org/html/rfc7232#section-2.4) に準拠して
  `Etag` と `Last-Modified` ヘッダの両方を設定した場合、
  `HttpCache` はその両方とも送信します。  また、もし `If-None-Match` ヘッダと
  `If-Modified-Since` ヘッダの両方を送信した場合は前者のみが尊重されます。


## `Cache-Control` ヘッダ <span id="cache-control"></span>

`Cache-Control` ヘッダはページのための一般的なキャッシュ・ポリシーを指定します。
[[yii\filters\HttpCache::cacheControlHeader]] プロパティにヘッダの値を設定することで、それを送ることができます。
デフォルトでは、以下のヘッダが送信されます:

```
Cache-Control: public, max-age=3600
```

## セッション・キャッシュ・リミッタ<span id="session-cache-limiter"></span>

ページでセッションを使用している場合、PHP はいくつかのキャッシュ関連の HTTP ヘッダ
(PHP の INI 設定ファイル内で指定されている session.cache_limiter など) を自動的に送信します。
これらのヘッダが `HttpCache` が実現しようとしているキャッシュ機能を妨害したり無効にしたりすることがあります。
この問題を防止するために、`HttpCache` はこれらのヘッダの送信をデフォルトで自動的に無効化します。
この動作を変更したい場合は [[yii\filters\HttpCache::sessionCacheLimiter]] プロパティを設定します。
このプロパティには `public`、`private`、`private_no_expire`、そして `nocache` などの文字列の値を使用することができます。
これらの値についての説明は [session_cache_limiter()](http://www.php.net/manual/ja/function.session-cache-limiter.php)
を参照してください。


## SEO への影響 <span id="seo-implications"></span>

検索エンジンのボットはキャッシュ・ヘッダを尊重する傾向があります。
クローラの中には、一定期間内に処理するドメインごとのページ数に制限を持っているものもあるため、
キャッシュ・ヘッダを導入して、処理の必要があるページ数を減らしてやると、サイトのインデックスの作成を促進できるかも知れません。

