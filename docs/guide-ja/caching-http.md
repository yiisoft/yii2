HTTP キャッシュ
============

前の節で説明したサーバーサイドのキャッシュに加えて、ウェブアプリケーションは、同じページコンテンツを生成し送信する時間を節約するために、クライアントサイドでもキャッシュを利用することができます。

クライアントサイドのキャッシュを使用するには、レンダリング結果をキャッシュできるように、コントローラアクションのフィルタとして [[yii\filters\HttpCache]] を設定します。[[yii\filters\HttpCache]] は `GET` と `HEAD` リクエストに対してのみ動作し、また、それらのリクエストは 3 種類のキャッシュ関連の HTTP ヘッダを扱うことができます:

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## `Last-Modified` ヘッダ <span id="last-modified"></span>

`Last-Modified` ヘッダは、クライアントがそれをキャッシュする時から、ページが変更されたかどうかを示すために、タイムスタンプを使用しています。

`Last-Modified` ヘッダの送信を有効にするには [[yii\filters\HttpCache::lastModified]] プロパティを、ページの変更時間に関する UNIX タイムスタンプを返す PHP の callable 型で、以下のようなシグネチャで構成していきます。

```php
/**
 * @param Action $action 現在扱っているアクションオブジェクト
 * @param array $params "params" プロパティの値
 * @return integer ページの更新時刻を表す UNIX タイムスタンプ
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

上記のコードは `index` アクションでのみ HTTP キャッシュを有効にしている状態です。投稿の最終更新時刻に基づいて `Last-Modified` を生成する必要があります。ブラウザが初めて `index` ページにアクセスすると、ページはサーバ上で生成されブラウザに送信されます。もしブラウザが再度同じページにアクセスし、その期間中に投稿に変更がない場合は、ブラウザはクライアントサイドにキャッシュしたものを使用するので、サーバはページを再生成することはありません。その結果、サーバサイドのレンダリング処理とページコンテンツの送信は両方ともスキップされます。


## `ETag` ヘッダ <span id="etag"></span>

"Entity Tag" (略して `ETag`) ヘッダはページコンテンツを表すためにハッシュを使用します。ページが変更された場合ハッシュも同様に変更されます。サーバサイドで生成されたハッシュとクライアントサイドで保持しているハッシュを比較することによって、ページが変更されたかどうか、また再送信するべきかどうかを決定します。

`ETag` ヘッダの送信を有効にするには [[yii\filters\HttpCache::etagSeed]] プロパティを設定します。プロパティは ETag のハッシュを生成するためのシードを返す PHP の callable 型で、以下のようなシグネチャで構成していきます。

```php
/**
 * @param Action $action 現在扱っているアクションオブジェクト
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

上記のコードは `view` アクションでのみ HTTP キャッシュを有効にしている状態です。リクエストされた投稿のタイトルとコンテンツに基づいて HTTP の `Etag` ヘッダを生成しています。ブラウザが初めて `view` ページにアクセスするときに、ページがサーバ上で生成されブラウザに送信されます。ブラウザが再度同じページにアクセスし、投稿のタイトルやコンテンツに変更がない場合には、サーバはページを再生成せず、ブラウザはクライアントサイトにキャッシュしたものを使用します。その結果、サーバサイドのレンダリング処理とページコンテンツ送信の両方ともスキップされます。

ETag は `Last-Modified` ヘッダよりも複雑かつ、より正確なキャッシング方式を可能にします。例えば、サイトが別のテーマに切り替わった場合には ETag を無効化する、といったことができます。

ETag はリクエスト毎に再評価する必要があるため、負荷の高いもの生成すると `HttpCache` の本来の目的を損なって不必要なオーバーヘッドが生じる場合があるので、ページのコンテンツが変更されたときにキャッシュを無効化するための式は単純なものを指定するようにして下さい。

> 注意: [RFC 7232](http://tools.ietf.org/html/rfc7232#section-2.4) に準拠して `Etag` と `Last-Modified` ヘッダの両方を設定した場合、`HttpCache` はその両方とも送信します。また、もし `If-None-Match` ヘッダと `If-Modified-Since` ヘッダの両方を送信した場合は前者のみが尊重されます。

## `Cache-Control` ヘッダ <span id="cache-control"></span>

`Cache-Control` ヘッダはページのための一般的なキャッシュポリシーを指定します。ヘッダ値に [[yii\filters\HttpCache::cacheControlHeader]] プロパティを設定することで、それを送ることができます。デフォルトでは、以下のヘッダーが送信されます:

```
Cache-Control: public, max-age=3600
```

## セッションキャッシュリミッタ<span id="session-cache-limiter"></span>

ページでセッションを使用している場合、PHP はいくつかのキャッシュ関連の HTTP ヘッダ(PHP の設定ファイル内で指定されている session.cache_limiter など)を自動的に送信します。これらのヘッダは `HttpCache` で妨害したり、必要なキャッシュを無効にしたりできます。この動作を変更したい場合は [[yii\filters\HttpCache::sessionCacheLimiter]] プロパティを設定します。プロパティには `public`、`private`、`private_no_expire`、そして `nocache` などの文字列の値を使用することができます。これらの値についての説明は [session_cache_limiter()](http://www.php.net/manual/ja/function.session-cache-limiter.php) を参照してください。


## SEO への影響 <span id="seo-implications"></span>

検索エンジンのボットはキャッシュヘッダを尊重する傾向があります。 クローラの中には、一定期間内に処理するドメインごとのページ数に制限を持っているものもあるため、キャッシュヘッダを導入して、処理の必要があるページ数を減らしてやると、サイトのインデックスの作成を促進できるかも知れません。
