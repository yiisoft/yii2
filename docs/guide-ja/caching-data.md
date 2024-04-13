データ・キャッシュ
==================

データ・キャッシュは PHP の変数をキャッシュに格納し、あとでキャッシュからそれらを読み込みます。
これは、[クエリ・キャッシュ](#query-caching) や [ページ・キャッシュ](caching-page.md) など、
より高度なキャッシュ機能の基礎でもあります。

以下のコードが、データ・キャッシュの典型的な利用パターンです。ここで、`$cache` は [キャッシュ・コンポーネント](#cache-components)
を指しています。

```php
// キャッシュから $data を取得しようと試みる
$data = $cache->get($key);

if ($data === false) {
    // キャッシュの中に $data が見つからない場合は一から作る
    $data = $this->calculateSomething();

    // $data をキャッシュに格納して、次回はそれを取得できるようにする
    $cache->set($key, $data);
}

// この時点で $data は利用可能になっている
```

バージョン 2.0.11 以降は、[キャッシュ・コンポーネント](#cache-components) が提供する [[yii\caching\Cache::getOrSet()|getOrSet()]] メソッドを使って、
データを取得、計算、保存するためのコードを単純化することが出来ます。
次に示すコードは、上述の例と全く同じことをするものです。

```php
$data = $cache->getOrSet($key, function () {
    return $this->calculateSomething();
});
```

キャッシュが `$key` と関連づけられたデータを保持している場合は、キャッシュされている値が返されます。
そうでない場合は、渡された無名関数が実行されて値が計算され、その値がキャッシュされるとともに返されます。

無名関数が外部のスコープの何らかのデータを必要とする場合は、それを `use` 文を使って渡すことが出来ます。
例えば、

```php
$user_id = 42;
$data = $cache->getOrSet($key, function () use ($user_id) {
    return $this->calculateSomething($user_id);
});
```

> Note: [[yii\caching\Cache::getOrSet()|getOrSet()]] メソッドは、有効期限と依存もサポートしています。
  詳しくは [キャッシュの有効期限](#cache-expiration) と [キャッシュの依存](#cache-dependencies) を参照してください。
  

## キャッシュ・コンポーネント <span id="cache-components"></span>

データ・キャッシュはメモリ、ファイル、データベースなどさまざまなキャッシュ・ストレージを表す、
いわゆる *キャッシュ・コンポーネント* に依存しています。

キャッシュ・コンポーネントは通常グローバルに設定しアクセスできるように
[アプリケーション・コンポーネント](structure-application-components.md) として登録されます。
以下のコードは、二台のキャッシュ・サーバを用いる [memcached](https://memcached.org/) を使うように
`cache` アプリケーション・コンポーネントを構成する方法を示すものです。

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

こうすると、上記のキャッシュ・コンポーネントに `Yii::$app->cache` という式でアクセスできるようになります。

すべてのキャッシュ・コンポーネントは同じ API をサポートしているので、アプリケーションの構成情報で設定しなおせば、
キャッシュを使っているコードに変更を加えることなく、異なるキャッシュ・コンポーネントに入れ替えることができます。
例えば上記の構成を [[yii\caching\ApcCache|APC キャッシュ]] を使うように変更する場合は以下のようにします:


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Tip: キャッシュ・コンポーネントは複数登録することができます。`cache` という名前のコンポーネントが、
  キャッシュに依存する多数のクラスによってデフォルトで使用されます (例えば [[yii\web\UrlManager]] など) 。


### サポートされているキャッシュ・ストレージ <span id="supported-cache-storage"></span>

Yii はさまざまなキャッシュ・ストレージをサポートしています。以下がその概要です:

* [[yii\caching\ApcCache]]: PHP の [APC](https://www.php.net/manual/ja/book.apc.php) 拡張モジュールを使用します。
  集中型の重厚なアプリケーションのキャッシュを扱うときには最速の一つとして考えることができます
  (例えば、サーバが一台で、専用のロード・バランサを持っていない、などの場合)。
* [[yii\caching\DbCache]]: キャッシュされたデータを格納するためにデータベースのテーブルを使用します。
  このキャッシュを使用するには [[yii\caching\DbCache::cacheTable]] で指定したテーブルを作成する必要があります。
* [[yii\caching\ArrayCache]]: 配列に値を保存することによって、現在のリクエストのためだけのキャッシュを提供します。
  ArrayCache のパフォーマンスを高めるために、[[yii\caching\ArrayCache::$serializer]] を `false` に設定して、
  保存するデータのシリアライズを無効にすることが出来ます。   .
* [[yii\caching\DummyCache]]: 実際にはキャッシュを行わない、キャッシュのプレースホルダとして働きます。
  このコンポーネントの目的は、キャッシュの可用性をチェックする必要があるコードを簡略化することです。
  たとえば、開発中やサーバに実際のキャッシュ・サポートがない場合でも、
  このキャッシュを使用するようにキャッシュ・コンポーネントを構成することができます。
  そして、実際のキャッシュ・サポートが有効になったときに、対応するキャッシュ・コンポーネントに切替えて使用します。
  どちらの場合も、`Yii::$app->cache` が `null` かも知れないと心配せずに、
  データを取得するために同じコード `Yii::$app->cache->get($key)` を使用できます。
* [[yii\caching\FileCache]]: キャッシュされたデータを保存するために通常のファイルを使用します。
  これはページ・コンテントなど大きなかたまりのデータに特に適しています。
* [[yii\caching\MemCache]]: PHP の [Memcache](https://www.php.net/manual/ja/book.memcache.php) と
  [Memcached](https://www.php.net/manual/ja/book.memcached.php) 拡張モジュールを使用します。
  分散型のアプリケーションでキャッシュを扱うときには最速の一つとして考えることができます
  (例えば、複数台のサーバで、ロード・バランサがある、などの場合) 。
* [[yii\redis\Cache]]: [Redis](https://redis.io/) の key-value ストアに基づいてキャッシュ・コンポーネントを実装しています。
  (Redis の バージョン 2.6.12 以降が必要とされます) 。
* [[yii\caching\WinCache]]: PHP の [WinCache](https://iis.net/downloads/microsoft/wincache-extension) エクステンションを使用します。
  ([参照リンク](https://www.php.net/manual/ja/book.wincache.php))
* [[yii\caching\XCache]] _(非推奨)_: PHP の [XCache](https://en.wikipedia.org/wiki/List_of_PHP_accelerators#XCache) 拡張モジュールを使用します。
* [[yii\caching\ZendDataCache]] _(非推奨)_:
  キャッシュ・メディアとして [Zend Data Cache](https://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm)
  を使用します。


> Tip: 同じアプリケーション内で異なるキャッシュを使用することもできます。
  一般的なやり方として、小さくとも常に使用されるデータ (例えば、統計データ) を格納する場合はメモリ・ベースのキャッシュ・ストレージを使用し、
  大きくて使用頻度の低いデータ (例えば、ページ・コンテント) を格納する場合はファイル・ベース、またはデータベースのキャッシュ・ストレージを使用します  。


## キャッシュ API <span id="cache-apis"></span>

すべてのキャッシュ・コンポーネントが同じ基底クラス [[yii\caching\Cache]] を持っているので、以下の API をサポートしています。

* [[yii\caching\Cache::get()|get()]]: 指定されたキーを用いてキャッシュからデータを取得します。
  データが見つからないか、もしくは有効期限が切れたり無効になったりしている場合は false を返します。
* [[yii\caching\Cache::set()|set()]]: キーによって識別されるデータをキャッシュに格納します。
* [[yii\caching\Cache::add()|add()]]: キーがキャッシュ内で見つからない場合に、キーによって識別されるデータをキャッシュに格納します。
* [[yii\caching\Cache::getOrSet()|getOrSet()]]: 指定されたキーを用いてキャッシュからデータを取得します。
  取得できなかった場合は、渡されたコールバック関数を実行し、関数の返り値をそのキーでキャッシュに保存し、そしてその値を返します。
* [[yii\caching\Cache::multiGet()|multiGet()]]: 指定されたキーを用いてキャッシュから複数のデータを取得します。
* [[yii\caching\Cache::multiSet()|multiSet()]]: キャッシュに複数のデータを格納します。各データはキーによって識別されます。
* [[yii\caching\Cache::multiAdd()|multiAdd()]]: キャッシュに複数のデータを格納します。
  各データはキーによって識別されます。もしキャッシュ内にキーがすでに存在する場合はスキップされます。
* [[yii\caching\Cache::exists()|exists()]]: 指定されたキーがキャッシュ内で見つかったかどうかを示す値を返します。
* [[yii\caching\Cache::delete()|delete()]]: キャッシュからキーによって識別されるデータを削除します。
* [[yii\caching\Cache::flush()|flush()]]: キャッシュからすべてのデータを削除します。

> Note: boolean 型の `false` を直接にキャッシュしてはいけません。
  なぜなら、[[yii\caching\Cache::get()|get()]] メソッドは、データがキャッシュ内に見つからないことを示すために戻り値として `false` を使用しているからです。
  代りに、配列内に `false` を置いてキャッシュすることによって、この問題を回避して下さい。

キャッシュされたデータを取得する際に発生するオーバーヘッドを減らすために、MemCache, APC などのいくつかのキャッシュ・ストレージは、
バッチ・モードで複数のキャッシュされた値を取得することをサポートしています。
[[yii\caching\Cache::multiGet()|multiGet()]] や [[yii\caching\Cache::multiAdd()|multiAdd()]] などの API はこの機能を十分に引き出すために提供されています。
基礎となるキャッシュ・ストレージがこの機能をサポートしていない場合には、シミュレートされます。

[[yii\caching\Cache]] は `ArrayAccess` インタフェイスを継承しているので、キャッシュ・コンポーネントは配列のように扱うことができます。
以下はいくつかの例です:

```php
$cache['var1'] = $value1;  // $cache->set('var1', $value1); と同等
$value2 = $cache['var2'];  // $value2 = $cache->get('var2'); と同等
```


### キャッシュのキー <span id="cache-keys"></span>

キャッシュに格納される各データは、一意のキーによって識別されます。
キャッシュ内にデータを格納するときはキーを指定する必要があります。
また、あとでキャッシュからデータを取得するときは、それに対応するキーを提供しなければなりません。

キャッシュのキーとしては、文字列または任意の値を使用することができます。
キーが文字列でない場合は、自動的に文字列にシリアライズされます。

キャッシュのキーを定義する一般的なやり方として、全ての決定要素を配列の形で含めるという方方があります。
例えば [[yii\db\Schema]] はデータベース・テーブルのスキーマ情報を以下のキーを使用してキャッシュしています。

```php
[
    __CLASS__,              // スキーマ・クラス名
    $this->db->dsn,         // データベース接続のデータ・ソース名
    $this->db->username,    // データベース接続のログイン・ユーザ
    $name,                  // テーブル名
];
```

見ての通り、キーは一意にデータベースのテーブルを指定するために必要なすべての情報を含んでいます。

> Note: [[yii\caching\Cache::multiSet()|multiSet()]] または [[yii\caching\Cache::multiAdd()|multiAdd()]] によってキャッシュに保存される値が持つことが出来るのは、
文字列または整数のキーだけです。それらより複雑なキーを設定する必要がある場合は、
[[yii\caching\Cache::set()|set()]] または [[yii\caching\Cache::add()|add()]] によって、値を個別に保存してください。

同じキャッシュ・ストレージが異なるアプリケーションによって使用されているときは、
キャッシュのキーの競合を避けるために、各アプリケーションではユニークなキーの接頭辞を指定する必要があります。
これは [[yii\caching\Cache::keyPrefix]] プロパティを設定することで出来ます。例えば、アプリケーション構成情報で以下のように書くことができます:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'myapp',       // ユニークなキャッシュのキーの接頭辞
    ],
],
```

相互運用性を確保するために、英数字のみを使用する必要があります。


### キャッシュの有効期限 <span id="cache-expiration"></span>

キャッシュに格納されたデータは、何らかのキャッシュ・ポリシー (例えば、キャッシュ・スペースがいっぱいになったときは最も古いデータが削除される、など)
の執行によって除去されない限り、永遠に残り続けます。
この動作を変えるために [[yii\caching\Cache::set()|set()]] を呼んでデータ・アイテムを保存するときに、有効期限パラメータを指定することができます。
このパラメータは、データ・アイテムが何秒間有効なものとしてキャッシュ内に残ることが出来るかを示します。
[[yii\caching\Cache::get()|get()]] でデータ・アイテムを取得する際に有効期限が切れていた場合は、
キャッシュ内にデータが見つからなかったことを示す `false` が返されます。例えば、

```php
// 最大で 45 秒間キャッシュ内にデータを保持する
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data は有効期限が切れているか、またはキャッシュ内に見つからない
}
```

バージョン 2.0.11 以降は、デフォルトの無限の有効期限に替えて特定の有効期限を指定したい場合には、
キャッシュ・コンポーネントの構成で [[yii\caching\Cache::$defaultDuration|defaultDuration]] の値を指定することが出来ます。
これによって、特定の `duration` パラメータを毎回 [[yii\caching\Cache::set()|set()]] に渡さなくてもよくなります。


### キャッシュの依存 <span id="cache-dependencies"></span>

有効期限の設定に加えて、キャッシュされたデータは、いわゆる *キャッシュの依存* (キャッシュが依存している事物) の変化によって無効にすることもできます。
例えば [[yii\caching\FileDependency]] は、キャッシュがファイルの更新時刻に依存していることを表しています。
この依存が変化したときは、対応するファイルが更新されたことを意味します。
その結果、キャッシュ内で見つかった古いファイルのコンテントは、無効とされるべきであり
[[yii\caching\Cache::get()|get()]] は `false` を返さなければなりません。

キャッシュの依存は [[yii\caching\Dependency]] の子孫クラスのオブジェクトとして表現されます。
[[yii\caching\Cache::set()|set()]] でキャッシュにデータ・アイテムを格納する際に、
関連するキャッシュの依存のオブジェクトを一緒に渡すことができます。例えば、

```php
// example.txt ファイルの更新日時への依存を作成します。
$dependency = new \yii\caching\FileDependency(['fileName' => 'example.txt']);

// データは 30 秒で期限切れになります。
// さらに、example.txt が変更された場合、有効期限内でも無効になります。
$cache->set($key, $data, 30, $dependency);

// キャッシュはデータの有効期限が切れているかをチェックします。
// 同時に、関連付けられた依存が変更されているかもチェックします。
// これらの条件のいずれかが満たされている場合は false を返します。
$data = $cache->get($key);
```

以下は利用可能なキャッシュの依存の概要です:

- [[yii\caching\ChainedDependency]]: チェーン上のいずれかの依存が変更された場合に、依存が変更されます。
- [[yii\caching\DbDependency]]: 指定された SQL 文のクエリ結果が変更された場合、依存が変更されます。
- [[yii\caching\ExpressionDependency]]: 指定された PHP の式の結果が変更された場合、依存が変更されます。
- [[yii\caching\CallbackDependency]]: 指定されたPHPコールバックの結果が変更された場合、依存関係は変更されます。
- [[yii\caching\FileDependency]]: ファイルの最終更新日時が変更された場合、依存が変更されます。
- [[yii\caching\TagDependency]]: キャッシュされるデータ・アイテムに一つまたは複数のタグを関連付けます。
  [[yii\caching\TagDependency::invalidate()]] を呼び出すことによって、指定されたタグ (複数可) を持つキャッシュされたデータ・アイテムを無効にすることができます。

> Note: 依存を有するキャッシュについて [[yii\caching\Cache::exists()|exists()]] メソッドを使用することは避けてください。
  このメソッドは、キャッシュされたデータに関連づけられた依存がある場合でも、依存が変化したかどうかをチェックしません。
  つまり、[[yii\caching\Cache::exists()|exists()]] が `true` を返しているのに、 [[yii\caching\Cache::get()|get()]] が `false` を返すという場合があり得ます。


## クエリ・キャッシュ <span id="query-caching"></span>

クエリ・キャッシュは、データ・キャッシュ上に構築された特別なキャッシュ機能で、
データベースのクエリ結果をキャッシュするために提供されています。

クエリ・キャッシュは [[yii\db\Connection|データベース接続]] と有効な `cache` [アプリケーション・コンポーネント](#cache-components) を必要とします。
`$db` を [[yii\db\Connection]] のインスタンスと仮定した場合、クエリ・キャッシュの基本的な使い方は以下のようになります:

```php
$result = $db->cache(function ($db) {

    // クエリ・キャッシュが有効で、かつクエリ結果がキャッシュ内にある場合、
    // SQL クエリ結果がキャッシュから提供されます
    return $db->createCommand('SELECT * FROM customer WHERE id=1')->queryOne();

});
```

クエリ・キャッシュは [DAO](db-dao.md) だけではなく [アクティブ・レコード](db-active-record.md) でも使用することができます。

```php
$result = Customer::getDb()->cache(function ($db) {
    return Customer::find()->where(['id' => 1])->one();
});
```

> Info: いくつかの DBMS (例えば [MySQL](https://dev.mysql.com/doc/refman/5.1/ja/query-cache.html))
  もデータベース・サーバ・サイドのクエリ・キャッシュをサポートしています。
  どちらのクエリ・キャッシュ・メカニズムを選んでも構いません。
  前述した Yii のクエリ・キャッシュにはキャッシュの依存を柔軟に指定できるという利点があり、潜在的にはより効率的です。

2.0.14 以降は、下記のショートカットを使用することが出来ます。

```php
(new Query())->cache(7200)->all();
// および
User::find()->cache(7200)->all();
```


### 構成 <span id="query-caching-configs"></span>

クエリ・キャッシュには [[yii\db\Connection]] を通して設定可能な三つのグローバルなオプションがあります:

* [[yii\db\Connection::enableQueryCache|enableQueryCache]]: クエリ・キャッシュを可能にするかどうか。デフォルトは `true` です。
  実効的にクエリ・キャッシュをオンにするには [[yii\db\Connection::queryCache|queryCache]]
  によって指定される有効なキャッシュを持っている必要があることに注意してください。
* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: これはクエリ結果がキャッシュ内に有効な状態として
  持続できる秒数を表します。
  クエリ・キャッシュを永遠にキャッシュに残したい場合は 0 を指定することができます。
  このプロパティは [[yii\db\Connection::cache()]] が持続時間を指定せず呼び出されたときに使用されるデフォルト値です。
* [[yii\db\Connection::queryCache|queryCache]]: これはキャッシュ・アプリケーション・コンポーネントの ID を表します。
デフォルトは `'cache'` です。有効なキャッシュ・コンポーネントが存在する場合にのみ、クエリ・キャッシュが使用可能になります。


### 使い方 <span id="query-caching-usages"></span>

クエリ・キャッシュを使用する必要がある複数の SQL クエリを持っている場合は [[yii\db\Connection::cache()]]
を使用することができます。使い方は以下のとおりです。

```php
$duration = 60;     // クエリ結果を 60 秒間 キャッシュ
$dependency = ...;  // 依存のオプション

$result = $db->cache(function ($db) {

    // ... ここで SQL クエリを実行します ...

    return $result;

}, $duration, $dependency);
```

無名関数内の任意の SQL クエリは、指定した依存とともに指定された期間キャッシュされます。
もしキャッシュ内に有効なクエリ結果が見つかった場合は、クエリはスキップされ、代りに結果がキャッシュから提供されます。
`$duration` の指定がない場合 [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]
で指定されている値が代りに使用されます。

場合によっては `cache()` 内でいくつかの特定のクエリに対してクエリ・キャッシュを無効にしたいことが有るでしょう。
そのときは [[yii\db\Connection::noCache()]] を使用します。

```php
$result = $db->cache(function ($db) {

    // クエリ・キャッシュを使用する SQL クエリ

    $db->noCache(function ($db) {

        // クエリ・キャッシュを使用しない SQL クエリ

    });

    // ...

    return $result;
});
```

単一のクエリのためだけにクエリ・キャッシュを使用したい場合は、コマンドを構築するときに [[yii\db\Command::cache()]]
を呼び出すことができます。例えば、

```php
// クエリ・キャッシュを使い、期間を 60 秒にセットする
$customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->cache(60)->queryOne();
```

また、一つのコマンドに対してクエリ・キャッシュを無効にするために [[yii\db\Command::noCache()]] を使用することもできます。例えば、

```php
$result = $db->cache(function ($db) {

    // クエリ・キャッシュを使用する SQL クエリ

    // このコマンドにはクエリ・キャッシュを使用しない
    $customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->noCache()->queryOne();

    // ...

    return $result;
});
```


### 制約 <span id="query-caching-limitations"></span>

リソース・ハンドラを返すようなクエリにはクエリ・キャッシュは働きません。
例えば、いくつかの DBMS において BLOB 型のカラムを用いる場合、
クエリ結果はカラム・データに対するリソース・ハンドラを返します。

いくつかのキャッシュ・ストレージはサイズに制約があります。
例えば Memcache では、各エントリのサイズは 1MB が上限値です。
そのためクエリ結果のサイズがこの制約を越える場合、キャッシュは失敗します。


## キャッシュのフラッシュ <span id="cache-flushing">

保存されている全てのキャッシュ・データを無効化する必要がある場合は、[[yii\caching\Cache::flush()]] を呼ぶことが出来ます。

コンソールから `yii cache/flush` を呼ぶことによっても、キャッシュをフラッシュすることが出来ます。
 - `yii cache`: アプリケーションで利用可能なキャッシュのリストを表示します。
 - `yii cache/flush cache1 cache2`: キャッシュ・コンポーネント `cache1` と `cache2` をフラッシュします
  (複数のコンポーネント名をスペースで区切って渡すことが出来ます)
 - `yii cache/flush-all`: アプリケーションの全てのキャッシュ・コンポーネントをフラッシュします。
- `yii cache/flush-schema db`: 指定された DB 接続に対する DB スキーマ・キャッシュをクリアします。

> Info: デフォルトでは、コンソール・アプリケーションは独立した構成情報ファイルを使用します。
正しい結果を得るためには、ウェブとコンソールのアプリケーション構成で同じキャッシュ・コンポーネントを使用していることを確認してください。
