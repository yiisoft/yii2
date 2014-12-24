データキャッシュ
============

データキャッシュは PHP の変数をキャッシュに格納し、あとでキャッシュからそれらを読み込みます。
[クエリキャッシュ](#query-caching) や [ページキャッシュ](caching-page.md) などのより高度なキャッシュ機能の基礎でもあります。

以下はデータキャッシュの典型的な利用パターンを示したコードです。`$cache` は [キャッシュコンポーネント](#cache-components) を指します:

```php
// キャッシュから $data を取得しようと試みる
$data = $cache->get($key);

if ($data === false) {

    // キャッシュの中に $data が見つからない場合は一から作る

    // 次回はそれを取得できるように $data をキャッシュに格納する
    $cache->set($key, $data);
}

// $data はここで利用できる
```


## キャッシュコンポーネント <a name="cache-components"></a>

データキャッシュはメモリ、ファイル、データベースなどさまざまなキャッシュストレージを表す、いわゆるキャッシュコンポーネントに依存しています。

キャッシュコンポーネントは通常グローバルに設定しアクセスできるように [アプリケーションコンポーネント](structure-application-components.md) として登録されています。以下のコードは [Memcached](http://memcached.org/) を使い 2 つのキャッシュサーバを用いて、`cache` コンポーネントをどのように設定するかを示したものです:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\MemCache',
        'servers' => [
            [
                'host' => 'server1',
                'port' => 11211,
                'weight' => 100,
            ],
            [
                'host' => 'server2',
                'port' => 11211,
                'weight' => 50,
            ],
        ],
    ],
],
```

上記のキャッシュコンポーネントには `Yii::$app->cache` でアクセスできます。

すべてのキャッシュコンポーネントは同じ API がセットされているので、アプリケーションのコンフィグレーション側で設定しなおせば、キャッシュを使っているコードに変更を加えなくても、異なるキャッシュコンポーネントに入れ替えることができます。例えば上記の設定を [[yii\caching\ApcCache|APC キャッシュ]] に変更する場合は以下のようにします:


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> ヒント: キャッシュコンポーネントは複数登録することができます。`cache` という名前のコンポーネントはキャッシュに依存したクラスによってデフォルトで使用されています (例えば [[yii\web\UrlManager]] など) 。


### サポートされているキャッシュストレージ <a name="supported-cache-storage"></a>

Yii はさまざまなキャッシュストレージをサポートしています。以下は概要です:

* [[yii\caching\ApcCache]]: PHP の [APC](http://php.net/manual/ja/book.apc.php) 拡張モジュールを使用します。集中型の分厚いアプリケーションのキャッシュを扱うときには最速の一つとして考えることができます (例えば、サーバが 1 台であったり、専用のロードバランサを持っていない、など) 。
* [[yii\caching\DbCache]]: キャッシュされたデータを格納するためにデータベースのテーブルを使用します。このキャッシュを使用するには [[yii\caching\DbCache::cacheTable]] で指定したテーブルを作成する必要があります。
* [[yii\caching\DummyCache]]: 実際にはキャッシュを行わない、キャッシュの代替を提供します。このコンポーネントの目的は、キャッシュが利用できることをチェックするためのコードを簡略化することです。たとえば、開発中やサーバに実際のキャッシュサポートがない場合に、このキャッシュコンポーネントを使用することができます。そして、実際のキャッシュサポートが有効になったときに、対応するキャッシュコンポーネントに切替えて使用します。 どちらの場合も、`Yii::$app->cache` が null かも知れないと心配せずに、データを取得するために同じコード `Yii::$app->cache->get($key)` を使用できます。
* [[yii\caching\FileCache]]: キャッシュされたデータを保存するために標準ファイルを使用します。これはページコンテンツなど大きなかたまりのデータに特に適しています。
* [[yii\caching\MemCache]]: PHP の [Memcache](http://php.net/manual/ja/book.memcache.php) と [Memcached](http://php.net/manual/ja/book.memcached.php) 拡張モジュールを使用します。分散型のアプリケーションでキャッシュを扱うときには最速の一つとして考えることができます (例えば、複数台のサーバ構成であったり、ロードバランサなど) 。
* [[yii\redis\Cache]]: [Redis](http://redis.io/) の key-value ストアに基づいてキャッシュコンポーネントを実装しています。(Redis の バージョン 2.6.12 以降が必要です) 。
* [[yii\caching\WinCache]]: PHP の [WinCache](http://iis.net/downloads/microsoft/wincache-extension) ([関連リンク](http://php.net/manual/ja/book.wincache.php)) 拡張モジュールを使用します。
* [[yii\caching\XCache]]: PHP の [XCache](http://xcache.lighttpd.net/) 拡張モジュールを使用します。
* [[yii\caching\ZendDataCache]]: キャッシュメディアして [Zend Data Cache](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm) を使用します。

> ヒント: 同じアプリケーション内で異なるキャッシュを使用することもできます。一般的なやり方として、小さくとも常に使用されるデータ (例えば、統計データなど) を格納する場合はメモリベースのキャッシュストレージを使用し、大きくて使用頻度の低いデータを格納する場合はファイルベース、またはデータベースのキャッシュストレージを使用します (例えば、ページコンテンツなど) 。


## キャッシュ API <a name="cache-apis"></a>

すべてのキャッシュコンポーネントが同じ基底クラス [[yii\caching\Cache]] を持っているので、以下の API をサポートしています。

* [[yii\caching\Cache::get()|get()]]: 指定されたキーを用いてキャッシュからデータを取得します。キャッシュが見つからないか、もしくは有効期限が切れていたり無効になっている場合は false を返します。
* [[yii\caching\Cache::set()|set()]]: キーによって識別されたデータをキャッシュに格納します。
* [[yii\caching\Cache::add()|add()]]: キーがキャッシュ内で見つからない場合に、キーによって識別されたデータをキャッシュに格納します。
* [[yii\caching\Cache::mget()|mget()]]: 指定されたキーを用いてキャッシュから複数のデータを取得します。
* [[yii\caching\Cache::mset()|mset()]]: キャッシュに複数のデータを格納します。各データはキーによって識別されます。
* [[yii\caching\Cache::madd()|madd()]]: キャッシュに複数のデータを格納します。各データはキーによって識別されます。もしキャッシュ内にキーがすでに存在する場合はスキップされます。
* [[yii\caching\Cache::exists()|exists()]]: 指定されたキーがキャッシュ内で見つかったかどうかを示す値を返します。
* [[yii\caching\Cache::delete()|delete()]]: キャッシュからキーによって識別されるデータを削除します。
* [[yii\caching\Cache::flush()|flush()]]: キャッシュからすべてのデータを削除します。

> 注意: [[yii\caching\Cache::get()|get()]] メソッドは、データがキャッシュ内に見つからないことを示すために戻り値として false を使用しているので、直接 boolean 型の `false` をキャッシュしないでください。代わり、配列内に `false` を置いてキャッシュすることによって、この問題を回避できます。

キャッシュされたデータを取得する際に発生するオーバーヘッドを減すために、MemCache, APC などのいくつかのキャッシュストレージはバッチモードで複数のキャッシュされた値の取得をサポートしています。[[yii\caching\Cache::mget()|mget()]] や [[yii\caching\Cache::madd()|madd()]] などの API はこの機能を十分に引き出すために提供されています。基礎となるキャッシュストレージがこの機能をサポートしていない場合には、シミュレートされます。

[[yii\caching\Cache]] は `ArrayAccess` インターフェイスを継承しているので、キャッシュコンポーネントは配列のように扱うことができます。以下はいくつかの例です:

```php
$cache['var1'] = $value1;  // $cache->set('var1', $value1); と同等
$value2 = $cache['var2'];  // $value2 = $cache->get('var2'); と同等
```


### キャッシュのキーについて <a name="cache-keys"></a>

キャッシュに格納される各データは、一意のキーによって識別されるため、キャッシュ内にデータを格納するときはキーを指定する必要があります。あとでキャッシュからデータを取得するときは、それに対応するキーを用意する、といった感じです。

文字列またはキャッシュのキーとして、任意の値を使用することができます。キーが文字列でない場合は、自動的に文字列にシリアライズされます。

キャッシュのキーを定義する一般的なやり方として、配列に決定要素を単位としてすべて含めることです。
例えば [[yii\db\Schema]] はデータベースのテーブルに関するキャッシュスキーマ情報に以下のキーを使用しています:

```php
[
    __CLASS__,              // クラス名
    $this->db->dsn,         // データベース接続のデータソース名
    $this->db->username,    // データベース接続のログインユーザ
    $name,                  // テーブル名
];
```

見ての通り、キーは一意にデータベースのテーブルを指定するために必要なすべての情報が含まれています。

同じキャッシュストレージが異なるアプリケーションによって使用されているときは、キャッシュのキーの競合を避けるために、各アプリケーションではユニークなキーの接頭辞を指定する必要があります。これは [[yii\caching\Cache::keyPrefix]] プロパティを設定することでできます。例えば、アプリケーションのコンフィギュレーションで以下のように書くことができます:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'myapp',       // ユニークなキャッシュのキーの接頭辞
    ],
],
```

相互運用性を確保するために、英数字のみを使用する必要があります。


### キャッシュの有効期限 <a name="cache-expiration"></a>

キャッシュに格納されたデータは、いくつかのキャッシュポリシー (例えば、キャッシュスペースがいっぱいになったときは最も古いデータが削除される、など) の実施で除去されない限り、永遠に残り続けます。この動作を変えるために [[yii\caching\Cache::set()|set()]] で有効期限パラメータを指定することができます。パラメータはキャッシュ内に何秒間有効であるかを示します。[[yii\caching\Cache::get()|get()]] でデータを取得する際に有効期限が切れていた場合は、キャッシュ内にデータが見つからなかったことを示す false が返されます。例えば、

```php
// 最大で 45 秒間キャッシュ内にデータを保持する
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data は有効期限が切れているか、またはキャッシュ内に見つからない
}
```


### キャッシュの依存関係 <a name="cache-dependencies"></a>


有効期限の設定に加えて、キャッシュされたデータにはいわゆる *キャッシュの依存関係* の変化によって無効にすることもできます。例えば [[yii\caching\FileDependency]] はファイルの更新時刻の依存関係を表しています。依存関係が変更されたときに、対応するファイルが更新されることを意味しています。その結果、キャッシュ内で見つかった古いファイルのコンテンツは、無効とされるべきであり [[yii\caching\Cache::get()|get()]] は false を返します。

キャッシュの依存関係は [[yii\caching\Dependency]] 子孫クラスのオブジェクトとして表現されます。[[yii\caching\Cache::set()|set()]] でキャッシュにデータを格納する際に、関連するキャッシュの依存関係を知らせることができます。例えば、

```php
// example.txt ファイルの変更時間への依存関係を作成
$dependency = new \yii\caching\FileDependency(['fileName' => 'example.txt']);

// データは 30 秒で期限切れになります
// さらに、依存関係にあるファイルが変更された場合、有効期限内でも無効になります
$cache->set($key, $data, 30, $dependency);

// データが有効期限切れの場合はキャッシュがチェックされます
// 関連する依存関係が変更された場合にもチェックします
// これらの条件のいずれかが満たされている場合は false を返します
$data = $cache->get($key);
```

以下は利用可能なキャッシュの依存関係の概要です:

- [[yii\caching\ChainedDependency]]: チェーン上のいずれかの依存関係が変更された場合、依存関係が変更されます。
- [[yii\caching\DbDependency]]: 指定された SQL 文のクエリ結果が変更された場合、依存関係が変更されます。
- [[yii\caching\ExpressionDependency]]: 指定されたPHPの式の結果が変更された場合、依存関係が変更されます。
- [[yii\caching\FileDependency]]: ファイルの最終更新時刻が変更された場合、依存関係が変更されます。
- [[yii\caching\TagDependency]]: 一つまたは複数のタグを持つキャッシュされたデータを関連付けます。[[yii\caching\TagDependency::invalidate()]] を呼び出すことによって指定されたタグ(複数可)と、キャッシュされたデータを無効にすることができます。


## クエリキャッシュ <a name="query-caching"></a>

クエリキャッシュは、データキャッシュ上に構築された特別なキャッシュ機能で、データベースのクエリ結果をキャッシュするために提供されています。

クエリキャッシュは [[yii\db\Connection|データベース接続]] と有効な `cache` コンポーネントを必要とします。
`$db` を [[yii\db\Connection]] のインスタンスと仮定した場合、クエリキャッシュの基本的な使い方は以下のようになります:

```php
$result = $db->cache(function ($db) {

    // クエリキャッシュが有効で、かつクエリ結果がキャッシュ内にある場合、
    // SQL クエリ結果がキャッシュから提供されます
    return $db->createCommand('SELECT * FROM customer WHERE id=1')->queryOne();

});
```
クエリキャッシュは [DAO](db-dao.md) だけではなく [アクティブレコード](db-active-record.md) でも使用することができます。

> 情報: いくつかの DBMS (例えば [MySQL](http://dev.mysql.com/doc/refman/5.1/ja/query-cache.html)) でもデータベースのサーバサイドのクエリキャッシュをサポートしています。どちらのクエリキャッシュメカニズムも選べますが、前述した Yii のクエリキャッシュを使用することによって、キャッシュの依存関係を柔軟に指定できたり、潜在的にもより効率的でしょう。


### 設定 <a name="query-caching-configs"></a>

クエリキャッシュは [[yii\db\Connection]] を通して 3 つのグローバルな設定可能オプションがあります:

* [[yii\db\Connection::enableQueryCache|enableQueryCache]]: クエリキャッシュを可能にするかどうか。デフォルトは true。実効的にクエリキャッシュをオンにするには [[yii\db\Connection::queryCache|queryCache]] によって指定し、さらに有効なキャッシュを持っている必要があることに注意してください。
* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: これはクエリ結果がキャッシュ内に有効な状態として維持できる秒数を表します。クエリキャッシュを永遠にキャッシュに残したい場合は 0 を指定することができます。このプロパティは [[yii\db\Connection::cache()]] の持続時間を指定せず呼び出されたときに使用されるデフォルト値です。
* [[yii\db\Connection::queryCache|queryCache]]: これはキャッシュコンポーネントの ID を表します。デフォルトは `'cache'`。有効なキャッシュコンポーネントが存在する場合にのみ、クエリキャッシュが使用可能になります。


### 使い方 <a name="query-caching-usages"></a>

クエリキャッシュを使用する必要がある複数の SQL クエリを持っている場合は [[yii\db\Connection::cache()]] を使用することができます。使い方としては以下のように、

```php
$duration = 60;     // クエリ結果を 60 秒間 キャッシュ
$dependency = ...;  // 依存関係のオプション

$result = $db->cache(function ($db) {

    // ... ここで SQL クエリを実行します ...

    return $result;

}, $duration, $dependency);
```

無名関数内の任意の SQL クエリは、指定した依存関係とともに指定された期間キャッシュされます。もしキャッシュ内に有効なクエリ結果が見つかった場合、クエリはスキップされ、その結果、代わりにキャッシュから提供されます。`$duration` の指定がない場合 [[yii\db\Connection::queryCacheDuration|queryCacheDuration]] で指定されている値が使用されます。

また、`cache()` 内でいくつかの特定のクエリに対してクエリキャッシュを無効にすることもできます。この場合 [[yii\db\Connection::noCache()]] を使用します。


```php
$result = $db->cache(function ($db) {

    // クエリキャッシュを使用する SQL クエリ

    $db->noCache(function ($db) {

        // クエリキャッシュを使用しない SQL クエリ

    });

    // ...

    return $result;
});
```

単一のクエリのためにクエリキャッシュを使用する場合は、コマンドを構築するときに [[yii\db\Command::cache()]] を呼び出すことができます。例えば、

```php
// クエリキャッシュを使い、期間を 60 秒にセットする
$customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->cache(60)->queryOne();
```

また、ひとつのコマンドでクエリキャッシュを無効にするために [[yii\db\Command::noCache()]] を使用することもできます。例えば、

```php
$result = $db->cache(function ($db) {

    // クエリキャッシュを使用する SQL クエリ

    // このコマンドはクエリキャッシュを使用しない
    $customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->noCache()->queryOne();

    // ...

    return $result;
});
```


### 制約 <a name="query-caching-limitations"></a>

リソースハンドルを返すようなクエリにはクエリキャッシュは働きません。例えばいくつかの DBMS において BLOB 型のカラムを用いる場合、クエリ結果はカラムデータについてリソースハンドルを返します。

いくつかのキャッシュストレージはサイズに制約があります。例えば Memcache では、各エントリのサイズは 1MB が上限値です。そのためクエリ結果のサイズがこの制約を越える場合、キャッシュは失敗します。
