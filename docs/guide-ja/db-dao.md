データベース・アクセス・オブジェクト
====================================

[PDO](http://www.php.net/manual/ja/book.pdo.php) の上に構築された Yii DAO (データベース・アクセス・オブジェクト) は、
リレーショナル・データベースにアクセスするためのオブジェクト指向 API を提供するものです。
これは、データベースにアクセスする他のもっと高度な方法、例えば [クエリ・ビルダ](db-query-builder.md) や [アクティブ・レコード](db-active-record.md) の基礎でもあります。

Yii DAO を使うときは、主として素の SQL と PHP 配列を扱う必要があります。
結果として、Yii DAO はデータベースにアクセスする方法としては最も効率的なものになります。
しかし、SQL の構文はデータベースによってさまざまに異なる場合がありますので、Yii DAO を使用するということは、特定のデータベースに依存しないアプリケーションを作るためには追加の労力が必要になる、ということをも同時に意味します。

Yii 2.0 では、DAO は下記の DBMS のサポートを内蔵しています。

- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/): バージョン 8.4 以上。
- [CUBRID](http://www.cubrid.org/): バージョン 9.3 以上。
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): バージョン 2008 以上。

> Info: Yii 3 以降では、CUBRID、Oracle および MSSQL に対する DAO サポートは、フレームワーク内蔵のコア・コンポーネント
  としては提供されていません。それらは、独立した [エクステンション](structure-extensions.md) としてインストールされる
  必要があります。[yiisoft/yii2-oracle](https://www.yiiframework.com/extension/yiisoft/yii2-oracle) および
  [yiisoft/yii2-mssql](https://www.yiiframework.com/extension/yiisoft/yii2-mssql) が
  [公式エクステンション](https://www.yiiframework.com/extensions/official) として提供されています。

> Note: PHP 7 用の pdo_oci の新しいバージョンは、現在、ソース・コードとしてのみ存在します。
  [コミュニティによる説明](https://github.com/yiisoft/yii2/issues/10975#issuecomment-248479268) に従ってコンパイルするか、
  または、[PDO エミュレーション・レイヤ](https://github.com/taq/pdooci) を使って下さい。

## DB 接続を作成する <span id="creating-db-connections"></span>

データベースにアクセスするためには、まずは、[[yii\db\Connection]] のインスタンスを作成して、データベースに接続する必要があります。

```php
$db = new yii\db\Connection([
    'dsn' => 'mysql:host=localhost;dbname=example',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```

DB 接続は、たいていは、さまざまな場所でアクセスする必要がありますので、次のように、
[アプリケーション・コンポーネント](structure-application-components.md) の形式で構成するのが通例です。

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=example',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

こうすると `Yii::$app->db` という式で DB 接続にアクセスすることが出来るようになります。

> Tip: あなたのアプリケーションが複数のデータベースにアクセスする必要がある場合は、複数の DB アプリケーション・コンポーネントを構成することが出来ます。

DB 接続を構成するときは、つねに [[yii\db\Connection::dsn|dsn]] プロパティによってデータ・ソース名 (DSN) を指定しなければなりません。
DSN の形式はデータベースによってさまざまに異なります。
詳細は [PHP マニュアル](http://www.php.net/manual/ja/function.PDO-construct.php) を参照して下さい。下記にいくつかの例を挙げます。

* MySQL, MariaDB: `mysql:host=localhost;dbname=mydatabase`
* SQLite: `sqlite:/path/to/database/file`
* PostgreSQL: `pgsql:host=localhost;port=5432;dbname=mydatabase`
* CUBRID: `cubrid:dbname=demodb;host=localhost;port=33000`
* MS SQL Server (sqlsrv ドライバ経由): `sqlsrv:Server=localhost;Database=mydatabase`
* MS SQL Server (dblib ドライバ経由): `dblib:host=localhost;dbname=mydatabase`
* MS SQL Server (mssql ドライバ経由): `mssql:host=localhost;dbname=mydatabase`
* Oracle: `oci:dbname=//localhost:1521/mydatabase`

ODBC 経由でデータベースに接続しようとする場合は、[[yii\db\Connection::driverName]] プロパティを構成して、Yii に実際のデータベースのタイプを知らせなければならないことに注意してください。
例えば、

```php
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

[[yii\db\Connection::dsn|dsn]] プロパティに加えて、たいていは [[yii\db\Connection::username|username]] と [[yii\db\Connection::password|password]] も構成しなければなりません。
構成可能なプロパティの全てのリストは [[yii\db\Connection]] を参照して下さい。

> Info: DB 接続のインスタンスを作成するとき、実際のデータベース接続は、最初の SQL を実行するか、
  [[yii\db\Connection::open()|open()]] メソッドを明示的に呼ぶかするまでは確立されません。

> Tip: 時として、何らかの環境変数を初期化するために、データベース接続を確立した直後に何かクエリを実行したい場合があるでしょう
> (例えば、タイムゾーンや文字セットを設定するなどです)。
> そうするために、データベース接続の [[yii\db\Connection::EVENT_AFTER_OPEN|afterOpen]] イベントに対するイベント・ハンドラを登録することが出来ます。
> 以下のように、アプリケーションの構成情報に直接にハンドラを登録することが出来ます。
> 
> ```php
> 'db' => [
>     // ...
>     'on afterOpen' => function($event) {
>         // $event->sender は DB 接続を指す
>         $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
>     }
> ]
> ```


## SQL クエリを実行する <span id="executing-sql-queries"></span>

いったんデータベース接続のインスタンスを得てしまえば、次の手順に従って SQL クエリを実行することが出来ます。

1. 素の SQL クエリで [[yii\db\Command]] を作成する。
2. パラメータをバインドする (オプション)。
3. [[yii\db\Command]] の SQL 実行メソッドの一つを呼ぶ。

次に、データベースからデータを読み出すさまざまな方法を例示します。

```php
// 行のセットを返す。各行は、カラム名と値の連想配列。
// クエリが結果を返さなかった場合は空の配列が返される。
$posts = Yii::$app->db->createCommand('SELECT * FROM post')
            ->queryAll();

// 一つの行 (最初の行) を返す。
// クエリの結果が無かった場合は false が返される。
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=1')
           ->queryOne();

// 一つのカラム (最初のカラム) を返す。
// クエリが結果を返さなかった場合は空の配列が返される。
$titles = Yii::$app->db->createCommand('SELECT title FROM post')
             ->queryColumn();

// スカラ値を返す。
// クエリの結果が無かった場合は false が返される。
$count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM post')
             ->queryScalar();
```

> Note: 精度を保つために、対応するデータベース・カラムの型が数値である場合でも、
  データベースから取得されたデータは、全て文字列として表現されます。


### パラメータをバインドする <span id="binding-parameters"></span>

パラメータを持つ SQL から DB コマンドを作成するときは、SQL インジェクション攻撃を防止するために、
ほとんど全ての場合においてパラメータをバインドする手法を用いるべきです。例えば、

```php
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValue(':id', $_GET['id'])
           ->bindValue(':status', 1)
           ->queryOne();
```

SQL 文において、一つまたは複数のパラメータ・プレースホルダ (例えば、上記のサンプルにおける `:id`) を埋め込むことが出来ます。
パラメータ・プレースホルダは、コロンから始まる文字列でなければなりません。
そして、次に掲げるパラメータをバインドするメソッドの一つを使って、パラメータの値をバインドします。

* [[yii\db\Command::bindValue()|bindValue()]]: 一つのパラメータの値をバインドします。
* [[yii\db\Command::bindValues()|bindValues()]]: 一回の呼び出しで複数のパラメータの値をバインドします。
* [[yii\db\Command::bindParam()|bindParam()]]: [[yii\db\Command::bindValue()|bindValue()]] と似ていますが、
  パラメータを参照渡しでバインドすることもサポートしています。

次の例はパラメータをバインドする方法の選択肢を示すものです。

```php
$params = [':id' => $_GET['id'], ':status' => 1];

$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValues($params)
           ->queryOne();
           
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status', $params)
           ->queryOne();
```

パラメータ・バインディングは [プリペアド・ステートメント](http://php.net/manual/ja/mysqli.quickstart.prepared-statements.php) によって実装されています。
パラメータ・バインディングには、SQL インジェクション攻撃を防止する以外にも、SQL 文を一度だけ準備して異なるパラメータで複数回実行することにより、
パフォーマンスを向上させる効果もあります。例えば、

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id');

$post1 = $command->bindValue(':id', 1)->queryOne();
$post2 = $command->bindValue(':id', 2)->queryOne();
// ...
```

[[yii\db\Command::bindParam()|bindParam()]] はパラメータを参照渡しでバインドすることをサポートしていますので、
上記のコードは次のように書くことも出来ます。

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id')
              ->bindParam(':id', $id);

$id = 1;
$post1 = $command->queryOne();

$id = 2;
$post2 = $command->queryOne();
```

クエリの実行の前にプレースホルダを変数 `$id` にバインドし、そして、後に続く各回の実行の前にその変数の値を変更していること
(これは、たいてい、ループで行います) に着目してください。
このやり方でクエリを実行すると、パラメータの値が違うごとに新しいクエリを実行するのに比べて、
はるかに効率を良くすることが出来ます。

> Info: パラメータ・バインディングは、素の SQL を含む文字列に値を挿入しなければならない場所でのみ使用されます。
> [クエリ・ビルダ](db-query-builder.md) や [アクティブ・レコード](db-active-record.md) のような高レベルの抽象的レイヤーでは、
> 多くの場所で SQL に変換される値の配列を指定する場合がよくあります。
> これらの場所では Yii によってパラメータ・バインディングが内部的に実行されますので、パラメータを手動で指定する必要はありません。


### SELECT しないクエリを実行する <span id="non-select-queries"></span>

今までのセクションで紹介した `queryXyz()` メソッドは、すべて、データベースからデータを取得する SELECT クエリを扱うものでした。
データを返さないクエリのためには、代りに [[yii\db\Command::execute()]] メソッドを呼ばなければなりません。例えば、

```php
Yii::$app->db->createCommand('UPDATE post SET status=1 WHERE id=1')
   ->execute();
```

[[yii\db\Command::execute()]] メソッドは SQL の実行によって影響を受けた行の数を返します。

INSERT、UPDATE および DELETE クエリのためには、素の SQL を書く代りに、それぞれ、
[[yii\db\Command::insert()|insert()]]、[[yii\db\Command::update()|update()]]、[[yii\db\Command::delete()|delete()]] を呼んで、対応する SQL を構築することが出来ます。
これらのメソッドは、テーブルとカラムの名前を適切に引用符で囲み、パラメータの値をバインドします。例えば、

```php
// INSERT (テーブル名, カラムの値)
Yii::$app->db->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// UPDATE (テーブル名, カラムの値, 条件)
Yii::$app->db->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE (テーブル名, 条件)
Yii::$app->db->createCommand()->delete('user', 'status = 0')->execute();
```

[[yii\db\Command::batchInsert()|batchInsert()]] を呼んで複数の行を一気に挿入することも出来ます。
この方法は、一度に一行を挿入するよりはるかに効率的です。

```php
// テーブル名, カラム名, カラムの値
Yii::$app->db->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();
```

もう一つの有用なメソッドは [[yii\db\Command::upsert()|upsert()]] です。upsert は、(ユニーク制約に合致する)行がまだ存在しない場合はデータベース・テーブルに行を挿入し、
既に行が存在している場合は行を更新する、アトミックな操作です。

```php
Yii::$app->db->createCommand()->upsert('pages', [
    'name' => 'フロント・ページ',
    'url' => 'http://example.com/', // url はユニーク
    'visits' => 0,
], [
    'visits' => new \yii\db\Expression('visits + 1'),
], $params)->execute();
```

上記のコードは、新しいページのレコードを挿入するか、または、既存のレコードの訪問者カウンタをインクリメントします。

上述のメソッド群はクエリを生成するだけであり、実際にそれを実行するためには、常に [[yii\db\Command::execute()|execute()]]
を呼び出す必要があることに注意してください。


## テーブルとカラムの名前を引用符で囲む <span id="quoting-table-and-column-names"></span>

特定のデータベースに依存しないコードを書くときには、テーブルとカラムの名前を適切に引用符で囲むことが、たいてい、頭痛の種になります。
データベースによって名前を引用符で囲む規則がさまざまに異なるからです。
この問題を克服するために、次のように、Yii によって導入された引用符の構文を使用することが出来ます。

* `[[カラム名]]`: 引用符で囲むべきカラム名は、二重角括弧で包む。
* `{{テーブル名}}`: 引用符で囲むべきテーブル名は、二重波括弧で包む。

Yii DAO は、このような構文を、DBMS 固有の文法に従って、
適切な引用符で囲まれたカラム名とテーブル名に自動的に変換します。
例えば、

```php
// MySQL では SELECT COUNT(`id`) FROM `employee` という SQL が実行される
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{employee}}")
            ->queryScalar();
```


### テーブル接頭辞を使う <span id="using-table-prefix"></span>

あなたの DB テーブル名のほとんどが何か共通の接頭辞を持っている場合は、
Yii DAO によって提供されているテーブル接頭辞の機能を使うことが出来ます。

最初に、アプリケーションの構成情報で、[[yii\db\Connection::tablePrefix]] プロパティによって、テーブル接頭辞を指定します。

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            // ...
            'tablePrefix' => 'tbl_',
        ],
    ],
];
```

そして、あなたのコードの中で、そのテーブル接頭辞を名前に持つテーブルを参照しなければならないときには、いつでも `{{%テーブル名}}` という構文を使います。
パーセント記号は DB 接続を構成したときに指定したテーブル接頭辞に自動的に置き換えられます。
例えば、

```php
// MySQL では SELECT COUNT(`id`) FROM `tbl_employee` という SQL が実行される
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{%employee}}")
            ->queryScalar();
```


## トランザクションを実行する <span id="performing-transactions"></span>

一続きになった複数の関連するクエリを実行するときは、データの整合性と一貫性を保証するために、
一連のクエリをトランザクションで囲む必要がある場合があります。
一連のクエリのどの一つが失敗した場合でも、データベースは、何一つクエリが実行されなかったかのような状態へとロールバックされます。

次のコードはトランザクションの典型的な使用方法を示すものです。

```php
Yii::$app->db->transaction(function($db) {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... その他の SQL 文を実行 ...
});
```

上記のコードは、次のものと等価です。こちらの方が、エラー処理のコードをより細かく制御することが出来ます。

```php
$db = Yii::$app->db;
$transaction = $db->beginTransaction();
try {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... その他の SQL 文を実行 ...

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```

[[yii\db\Connection::beginTransaction()|beginTransaction()]] メソッドを呼ぶことによって、新しいトランザクションが開始されます。
トランザクションは、変数 `$transaction` に保存された [[yii\db\Transaction]] オブジェクトとして表現されます。
そして、実行されるクエリが `try...catch...` ブロックで囲まれます。
全てのクエリの実行が成功した場合には、トランザクションをコミットするために [[yii\db\Transaction::commit()|commit()]] が呼ばれます。
そうでなく、例外がトリガされてキャッチされた場合は、[[yii\db\Transaction::rollBack()|rollBack()]]
が呼ばれて、トランザクションの中で失敗したクエリに先行するクエリによって行なわれた変更が、ロールバックされます。
そして、`throw $e` が、まるでそれをキャッチしなかったかのように、例外を再スローしますので、通常のエラー処理プロセスがその例外の面倒を見ることになります。

> Note: 上記のコードでは、PHP 5.x と PHP 7.x との互換性のために、二つのcatch ブロックを持っています。
> `\Exception` は PHP 7.0 以降では、[`\Throwable` インタフェイス](http://php.net/manual/ja/class.throwable.php) を実装しています。
> 従って、あなたのアプリケーションが PHP 7.0 以上しか使わない場合は、`\Exception` の部分を省略することが出来ます。


### 分離レベルを指定する <span id="specifying-isolation-levels"></span>

Yii は、トランザクションの [分離レベル] の設定もサポートしています。デフォルトでは、新しいトランザクションを開始したときは、
データベースシステムによって設定された分離レベルを使用します。デフォルトの分離レベルは、次のようにしてオーバーライドすることが出来ます。

```php
$isolationLevel = \yii\db\Transaction::REPEATABLE_READ;

Yii::$app->db->transaction(function ($db) {
    ....
}, $isolationLevel);
 
// あるいは

$transaction = Yii::$app->db->beginTransaction($isolationLevel);
```

Yii は、最もよく使われる分離レベルのために、四つの定数を提供しています。

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - 最も弱いレベル。ダーティー・リード、非再現リード、ファントムが発生しうる。
- [[\yii\db\Transaction::READ_COMMITTED]] - ダーティー・リードを回避。
- [[\yii\db\Transaction::REPEATABLE_READ]] - ダーティー・リードと非再現リードを回避。
- [[\yii\db\Transaction::SERIALIZABLE]] - 最も強いレベル。上記の問題を全て回避。

分離レベルを指定するためには、上記の定数を使う以外に、あなたが使っている DBMS によってサポートされている有効な構文の文字列を使うことも出来ます。
例えば、PostreSQL では、`"SERIALIZABLE READ ONLY DEFERRABLE"` を使うことが出来ます。

DBMS によっては、接続全体に対してのみ分離レベルの設定を許容しているものがあることに注意してください。
その場合、すべての後続のトランザクションは、指定しなくても、それと同じ分離レベルで実行されます。
従って、この機能を使用するときは、矛盾する設定を避けるために、全てのトランザクションについて分離レベルを明示的に指定しなければなりません。
このチュートリアルを書いている時点では、この制約の影響を受ける DBMS は MSSQL と SQLite だけです。

> Note: SQLite は、二つの分離レベルしかサポートしていません。すなわち、`READ UNCOMMITTED` と `SERIALIZABLE` しか使えません。
他のレベルを使おうとすると、例外が投げられます。

> Note: PostgreSQL は、トランザクションを開始する前に分離レベルを指定することを許容していません。
すなわち、トランザクションを開始するときに、分離レベルを直接に指定することは出来ません。
この場合、トランザクションを開始した後に [[yii\db\Transaction::setIsolationLevel()]] を呼び出す必要があります。

[分離レベル]: http://ja.wikipedia.org/wiki/%E3%83%88%E3%83%A9%E3%83%B3%E3%82%B6%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3%E5%88%86%E9%9B%A2%E3%83%AC%E3%83%99%E3%83%AB


### トランザクションを入れ子にする <span id="nesting-transactions"></span>

あなたの DBMS が Savepoint をサポートしている場合は、次のように、複数のトランザクションを入れ子にすることが出来ます。

```php
Yii::$app->db->transaction(function ($db) {
    // 外側のトランザクション
    
    $db->transaction(function ($db) {
        // 内側のトランザクション
    });
});
```

あるいは、

```php
$db = Yii::$app->db;
$outerTransaction = $db->beginTransaction();
try {
    $db->createCommand($sql1)->execute();

    $innerTransaction = $db->beginTransaction();
    try {
        $db->createCommand($sql2)->execute();
        $innerTransaction->commit();
    } catch (\Exception $e) {
        $innerTransaction->rollBack();
        throw $e;
    } catch(\Throwable $e) {
        $transaction->rollBack();
        throw $e;
    }

    $outerTransaction->commit();
} catch (\Exception $e) {
    $outerTransaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```


## レプリケーションと読み書きの分離 <span id="read-write-splitting"></span>

多くの DBMS は、データベースの可用性とサーバのレスポンス・タイムを向上させるために、
[データベース・レプリケーション](http://ja.wikipedia.org/wiki/%E3%83%AC%E3%83%97%E3%83%AA%E3%82%B1%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3#.E3.83.87.E3.83.BC.E3.82.BF.E3.83.99.E3.83.BC.E3.82.B9) をサポートしています。
データベース・レプリケーションによって、データはいわゆる *マスタ・サーバ* から *スレーブ・サーバ* に複製されます。
データの書き込みと更新はすべてマスタ・サーバ上で実行されなければなりませんが、データの読み出しはスレーブ・サーバ上でも可能です。

データベース・レプリケーションを活用して読み書きの分離を達成するために、
[[yii\db\Connection]] コンポーネントを下記のように構成することが出来ます。

```php
[
    'class' => 'yii\db\Connection',

    // マスタの構成
    'dsn' => 'マスタ・サーバの DSN',
    'username' => 'master',
    'password' => '',

    // スレーブの共通の構成
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // 短かめの接続タイムアウトを使う
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // スレーブの構成のリスト
    'slaves' => [
        ['dsn' => 'スレーブ・サーバ 1 の DSN'],
        ['dsn' => 'スレーブ・サーバ 2 の DSN'],
        ['dsn' => 'スレーブ・サーバ 3 の DSN'],
        ['dsn' => 'スレーブ・サーバ 4 の DSN'],
    ],
]
```

上記の構成は、一つのマスタと複数のスレーブを指定するものです。
読み出しのクエリを実行するためには、スレーブの一つが接続されて使用され、書き込みのクエリを実行するためには、マスタが使われます。
そのような読み書きの分離が、この構成によって、自動的に達成されます。例えば、

```php
// 上記の構成を使って Connection のインスタンスを作成する
$db = Yii::createObject($config);

// スレーブの一つに対してクエリを実行する
$rows = Yii::$app->db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// マスタに対してクエリを実行する
Yii::$app->db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Info: [[yii\db\Command::execute()]] を呼ぶことで実行されるクエリは、書き込みのクエリと見なされ、
  [[yii\db\Command]] の "query" メソッドのうちの一つによって実行されるその他すべてのクエリは、読み出しクエリと見なされます。
  現在アクティブなスレーブ接続は `Yii::$app->db->slave` によって取得することが出来ます。

`Connection` コンポーネントは、スレーブ間のロード・バランス調整とフェイルオーバーをサポートしています。
読み出しクエリを最初に実行するときに、`Connection` コンポーネントはランダムにスレーブを選んで接続を試みま・す。
そのスレーブが「死んでいる」ことが分かったときは、他のスレーブを試します。
スレーブが一つも使用できないときは、マスタに接続します。
[[yii\db\Connection::serverStatusCache|サーバ・ステータスキャッシュ]] を構成することによって、「死んでいる」サーバを記憶し、
[[yii\db\Connection::serverRetryInterval|一定期間]] はそのサーバへの接続を再試行しないようにすることが出来ます。

> Info: 上記の構成では、すべてのスレーブに対して 10 秒の接続タイムアウトが指定されています。
  これは、10 秒以内に接続できなければ、そのスレーブは「死んでいる」と見なされることを意味します。
  このパラメータは、実際の環境に基づいて調整することが出来ます。


複数のマスタと複数のスレーブという構成にすることも可能です。例えば、


```php
[
    'class' => 'yii\db\Connection',

    // マスタの共通の構成
    'masterConfig' => [
        'username' => 'master',
        'password' => '',
        'attributes' => [
            // 短かめの接続タイムアウトを使う
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // マスタの構成のリスト
    'masters' => [
        ['dsn' => 'マスタ・サーバ 1 の DSN'],
        ['dsn' => 'マスタ・サーバ 2 の DSN'],
    ],

    // スレーブの共通の構成
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // 短かめの接続タイムアウトを使う
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // スレーブの構成のリスト
    'slaves' => [
        ['dsn' => 'スレーブ・サーバ 1 の DSN'],
        ['dsn' => 'スレーブ・サーバ 2 の DSN'],
        ['dsn' => 'スレーブ・サーバ 3 の DSN'],
        ['dsn' => 'スレーブ・サーバ 4 の DSN'],
    ],
]
```

上記の構成は、二つのマスタと四つのスレーブを指定しています。
`Connection` コンポーネントは、スレーブ間での場合と同じように、マスタ間でのロード・バランス調整とフェイルオーバーをサポートしています。
一つ違うのは、マスタが一つも利用できないときは例外が投げられる、という点です。

> Note: [[yii\db\Connection::masters|masters]] プロパティを使って一つまたは複数のマスタを構成する場合は、
  データベース接続を定義する `Connection` オブジェクト自体の他のプロパティ
  (例えば、`dsn`、`username`、`password`) は全て無視されます。


デフォルトでは、トランザクションはマスタ接続を使用します。
そして、トランザクション内では、全ての DB 操作はマスタ接続を使用します。例えば、

```php
$db = Yii::$app->db;
// トランザクションはマスタ接続で開始される
$transaction = $db->beginTransaction();

try {
    // クエリは両方ともマスタに対して実行される
    $rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
    $db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```

スレーブ接続を使ってトランザクションを開始したいときは、次のように、明示的にそうする必要があります。

```php
$transaction = Yii::$app->db->slave->beginTransaction();
```

時として、読み出しクエリの実行にマスタ接続を使うことを強制したい場合があります。
これは、`useMaster()` メソッドを使うことによって達成できます。

```php
$rows = Yii::$app->db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```

直接に `Yii::$app->db->enableSlaves` を `false` に設定して、全てのクエリをマスタ接続に向けることも出来ます。


## データベース・スキーマを扱う <span id="database-schema"></span>

Yii DAO は、新しいテーブルを作ったり、テーブルからカラムを削除したりなど、データベース・スキーマを操作することを可能にする一揃いのメソッドを提供しています。
以下がそのソッドのリストです。

* [[yii\db\Command::createTable()|createTable()]]: テーブルを作成する
* [[yii\db\Command::renameTable()|renameTable()]]: テーブルの名前を変更する
* [[yii\db\Command::dropTable()|dropTable()]]: テーブルを削除する
* [[yii\db\Command::truncateTable()|truncateTable()]]: テーブルの全ての行を削除する
* [[yii\db\Command::addColumn()|addColumn()]]: カラムを追加する
* [[yii\db\Command::renameColumn()|renameColumn()]]: カラムの名前を変更する
* [[yii\db\Command::dropColumn()|dropColumn()]]: カラムを削除する
* [[yii\db\Command::alterColumn()|alterColumn()]]: カラムを変更する
* [[yii\db\Command::addPrimaryKey()|addPrimaryKey()]]: プライマリ・キーを追加する
* [[yii\db\Command::dropPrimaryKey()|dropPrimaryKey()]]: プライマリ・キーを削除する
* [[yii\db\Command::addForeignKey()|addForeignKey()]]: 外部キーを追加する
* [[yii\db\Command::dropForeignKey()|dropForeignKey()]]: 外部キーを削除する
* [[yii\db\Command::createIndex()|createIndex()]]: インデックスを作成する
* [[yii\db\Command::dropIndex()|dropIndex()]]: インデックスを削除する

これらのメソッドは次のようにして使うことが出来ます。

```php
// CREATE TABLE
Yii::$app->db->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

上記の配列は、生成されるカラムの名前と型を記述しています。
Yii はカラムの型のために一連の抽象データ型を提供しているため、データベースの違いを意識せずにスキーマを定義することが可能です。
これらの抽象データ型は、テーブルが作成されるデータベースによって異なる DBMS 固有の型定義に変換されます。
詳しい情報は [[yii\db\Command::createTable()|createTable()]] メソッドの API ドキュメントを参照してください。

データベースのスキーマを変更するだけでなく、テーブルに関する定義情報を DB 接続の [[yii\db\Connection::getTableSchema()|getTableSchema()]] メソッドによって取得することも出来ます。
例えば、

```php
$table = Yii::$app->db->getTableSchema('post');
```

このメソッドは、テーブルのカラム、プライマリ・キー、外部キーなどの情報を含む [[yii\db\TableSchema]] オブジェクトを返します。
これらの情報は、主として [クエリ・ビルダ](db-query-builder.md) や [アクティブ・レコード](db-active-record.md) によって利用されて、
特定のデータベースに依存しないコードを書くことを助けてくれています。
