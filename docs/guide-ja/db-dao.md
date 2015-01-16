データベースアクセスオブジェクト
================================

> Note|注意: この節はまだ執筆中です。

Yii は、PHP の [PDO](http://www.php.net/manual/ja/book.pdo.php) の上に構築されたデータベースアクセスレイヤを含んでいます。
データベースアクセスオブジェクト (DAO) のインタフェイスは、統一された API を提供し、さまざまなデータベース製品間に存在する不統一のいくらかを解決します。
アクティブレコードは、モデルを通じてのデータベースとの相互作用を提供し、クエリビルダは、動的なクエリの作成を支援します。
一方、DAO はデータベースに対して直接に SQL を実行する単純で効率的な方法を提供します。
実行すべきクエリが高価なものである場合、かつ/または、アプリケーションモデル (および対応するビジネスロジック) が必要でない場合に、あなたは DAO を使いたいと思うでしょう。

Yii はデフォルトで下記の DBMS をサポートしています。

- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/)
- [CUBRID](http://www.cubrid.org/): バージョン 9.3 以上。(cubrid PDO 拡張の [バグ](http://jira.cubrid.org/browse/APIS-658)
  のために、値を引用符で囲む機能が動作しません。そのため、サーバだけでなくクライアントも CUBRID 9.3 が必要になります)
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): バージョン 2008 以上。


構成
----

データベースとの相互作用を開始するためには (DAO を使うにしろ使わないにしろ)、アプリケーションのデータベース接続コンポーネントを構成する必要があります。
データソース名 (DSN) が、どのデータベース製品のどの特定のデータベースにアプリケーションが接続すべきかを構成します。

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase', // MySQL, MariaDB
            //'dsn' => 'sqlite:/path/to/database/file', // SQLite
            //'dsn' => 'pgsql:host=localhost;port=5432;dbname=mydatabase', // PostgreSQL
            //'dsn' => 'cubrid:dbname=demodb;host=localhost;port=33000', // CUBRID
            //'dsn' => 'sqlsrv:Server=localhost;Database=mydatabase', // MS SQL Server, sqlsrv ドライバ
            //'dsn' => 'dblib:host=localhost;dbname=mydatabase', // MS SQL Server, dblib ドライバ
            //'dsn' => 'mssql:host=localhost;dbname=mydatabase', // MS SQL Server, mssql ドライバ
            //'dsn' => 'oci:dbname=//localhost:1521/mydatabase', // Oracle
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

DSN 文字列のフォーマットに関する詳細は、[PHP マニュアル](http://www.php.net/manual/ja/function.PDO-construct.php) を参照してください。
このクラスで構成可能なプロパティの全てのリストについては、[[yii\db\Connection]] を参照してください。

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

主たる `db` 接続には、`\Yii::$app->db` という式によってアクセスすることが出来ます。
一つのアプリケーションで複数の DB 接続を構成することも出来ます。
アプリケーションの構成情報において、それらに別々の ID を割り当てるだけのことです。

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase', 
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'secondDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:/path/to/database/file', 
        ],
    ],
    // ...
];
```

これで、必要に応じて両方のデータベース接続を同時に使用することが出来ます。

```php
$primaryConnection = \Yii::$app->db;
$secondaryConnection = \Yii::$app->secondDb;
```

DB 接続を [アプリケーションコンポーネント](structure-application-components.md) として定義したくない場合は、インスタンスを直接に作成することも出来ます。

```php
$connection = new \yii\db\Connection([
    'dsn' => $dsn,
    'username' => $username,
    'password' => $password,
]);
$connection->open();
```

> Tip|ヒント: 接続を確立した直後に SQL クエリを実行する必要がある場合 (例えば、タイムゾーンや文字セットを設定するなどの場合) は、アプリケーションの構成情報ファイルに次のように追記することが出来ます。
>
```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            // ...
            'on afterOpen' => function($event) {
                $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
            }
        ],
    ],
    // ...
];
```

基本的な SQL クエリを実行する
-----------------------------

いったんデータベース接続のインスタンスを得てしまえば、[[yii\db\Command]] を使って SQL クエリを実行することが出来るようになります。

### SELECT クエリを実行する

実行されるクエリが行のセットを返す場合は、`queryAll` を使います。

```php
$command = $connection->createCommand('SELECT * FROM post');
$posts = $command->queryAll();
```

実行されるクエリが一つの行だけを返す場合は、`queryOne` を使います。

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=1');
$post = $command->queryOne();
```

クエリが複数行ではあっても一つのカラムだけを返す場合は、`queryColumn` を使います。

```php
$command = $connection->createCommand('SELECT title FROM post');
$titles = $command->queryColumn();
```

クエリがスカラ値だけを返す場合は、`queryScalar` を使います。

```php
$command = $connection->createCommand('SELECT COUNT(*) FROM post');
$postCount = $command->queryScalar();
```

### 値を返さないクエリを実行する

実行されるクエリがデータを一つも返さない場合、例えば、INSERT、UPDATE、DELETE などの場合は、コマンドの `execute` メソッドを使うことが出来ます。

```php
$command = $connection->createCommand('UPDATE post SET status=1 WHERE id=1');
$command->execute();
```

あるいはまた、専用の `insert`、`update`、`delete` のメソッドを使うことも出来ます。
これらのメソッドは、クエリの中で使用されるテーブルとカラムの名前を引用符で適切に囲んでくれますので、あなたは単に必要な値を提供するだけで済みます。

[[Ought to put a link to the reference docs here.]]

```php
// INSERT
$connection->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// INSERT 複数行を一度に
$connection->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();

// UPDATE
$connection->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE
$connection->createCommand()->delete('user', 'status = 0')->execute();
```

テーブルとカラムの名前を引用符で囲む <a name="quoting-table-and-column-names"></a>
------------------------------------

テーブルとカラムの名前をクエリの中で安全に使えるようにするために、Yii にそれらの名前を引用符で適切に囲ませることが出来ます。

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

上記のコードにおいて、`[[$column]]` は引用符で適切に囲まれたカラム名に変換され、`{{table}}` は引用符で適切に囲まれたテーブル名に変換されます。

この構文には、テーブル名に限定された特別な変種があります。
それは、`{{%Y}}` が、提供された値にアプリケーションのテーブル接頭辞を (テーブル接頭辞がセットされている場合は) 自動的に追加する、というものです。

```php
$sql = "SELECT COUNT([[$column]]) FROM {{%table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

上記のコードは、テーブル接頭辞をそのように構成している場合は、`tbl_table` からセレクトする結果になります。

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

もう一つの選択肢は、[[yii\db\Connection::quoteTableName()]] と [[yii\db\Connection::quoteColumnName()]] を使って、手作業でテーブル名とカラム名を引用符で囲むことです。

```php
$column = $connection->quoteColumnName($column);
$table = $connection->quoteTableName($table);
$sql = "SELECT COUNT($column) FROM $table";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

プリペアドステートメントを使用する
----------------------------------

クエリのパラメータを安全にクエリに渡すために、プリペアドステートメントを利用すべきです。
最初に、クエリの中に (`:placeholder` という構文を使って) 名前付きのプレースホルダを作ります。
次に、プレースホルダに値をバインドしてクエリを実行します。

```php
$command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
$command->bindValue(':id', $_GET['id']);
$post = $command->queryOne();
```

プリペアドステートメントを使うもう一つの目的は (セキュリティの向上のほかに)、一度だけ準備したクエリを複数回実行することが出来るという点にあります。

```php
$command = $connection->createCommand('DELETE FROM post WHERE id=:id');
$command->bindParam(':id', $id);

$id = 1;
$command->execute();

$id = 2;
$command->execute();
```

クエリの実行の前にプレースホルダを変数にバインドすること、そして、後に続く各回の実行の前に変数の値を変更すること (たいていはループで行います) に着目してください。
このやり方でクエリを実行すると、各クエリを一つずつ実行するのに比べて、はるかに効率が良くなることがあります。

トランザクションを実行する
--------------------------

一続きになった複数の関連するクエリを実行するときは、データの整合性を保護するために、一連のクエリをトランザクションで囲む必要がある場合があります。
トランザクションを使うと、全て成功するか、さもなくば、いかなる結果ももたらさない、という動作をする一連のクエリを書くことが出来ます。
Yii はトランザクションを扱うシンプルなインタフェイスを提供して、単純な場合だけでなく、分離レベルを定義する必要があるような高度な用法にも対応しています。

次のコードが示しているシンプルなパターンは、クエリにトランザクションを使用する全てのコードが従うべきものです。

```php
$transaction = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();
    $connection->createCommand($sql2)->execute();
    // ... その他の SQL 文を実行 ...
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

最初の行で、データベース接続オブジェクトの [[yii\db\Connection::beginTransaction()|beginTransaction()]] メソッドを使って、新しいトランザクションを開始しています。
トランザクション自体は、`$transaction` に保存された [[yii\db\Transaction]] オブジェクトとして表現されています。
エラー処理を可能にするために、全てのクエリを try-catch ブロックで囲みます。
成功した場合には [[yii\db\Transaction::commit()|commit()]] を呼んでトランザクションをコミットし、エラーが発生した場合には [[yii\db\Transaction::rollBack()|rollBack()]] を呼びます。
`rollBack` は、トランザクションの内側で実行された全てのクエリの結果を取り消します。
`throw $e` は、私たち自身ではエラーを処理することが出来ない場合に、例外を再スローするために用いられます。
これによって、他のコード、または Yii のエラーハンドラにエラー処理を委譲しています。

必要であれば、複数のトランザクションを入れ子にすることも可能です。

```php
// 外側のトランザクション
$transaction1 = $connection->beginTransaction();
try {
    $connection->createCommand($sql1)->execute();

    // 内側のトランザクション
    $transaction2 = $connection->beginTransaction();
    try {
        $connection->createCommand($sql2)->execute();
        $transaction2->commit();
    } catch (Exception $e) {
        $transaction2->rollBack();
    }

    $transaction1->commit();
} catch (Exception $e) {
    $transaction1->rollBack();
}
```

これが期待通りの動作をするためには、あなたの DBMS が SAVEPOINT をサポートしていなければならないことに注意してください。
上記のコードはどのような DBMS でも動きますが、トランザクションの安全性が保証されるのは、背後の DBMS がそれをサポートしている場合だけです。

Yii は、また、トランザクションの [分離レベル] の設定もサポートしています。
トランザクションを開始したとき、トランザクションはデータベースによって設定されたデフォルトの分離レベルで実行されます。
トランザクションを開始するときに、分離レベルを明示的に指定することが出来ます。

```php
$transaction = $connection->beginTransaction(\yii\db\Transaction::REPEATABLE_READ);
```

Yii は、最もよく使われる分離レベルのために、四つの定数を提供しています。

- [[\yii\db\Transaction::READ_UNCOMMITTED]] - 最も弱いレベル。ダーティーリード、非再現リード、ファントムが発生しうる。
- [[\yii\db\Transaction::READ_COMMITTED]] - ダーティーリードを回避。
- [[\yii\db\Transaction::REPEATABLE_READ]] - ダーティーリードと非再現リードを回避。
- [[\yii\db\Transaction::SERIALIZABLE]] - 最も強いレベル。上記の問題を全て回避。

上記の定数を使うことも出来ますが、あなたの DBMS で `SET TRANSACTION ISOLATION LEVEL` に続けて書くことが出来る、文法として有効な文字列を使うことも出来ます。
たとえば、postgres であれば、`SERIALIZABLE READ ONLY DEFERRABLE` を使っても構いません。

DBMS によっては、接続全体に対してのみ分離レベルの設定を許容しているものがあることに注意してください。
そのような DBMS の場合、いったん分離レベルを指定すると、後続のトランザクションは、指定しなくても、同じ分離レベルで実行されることになります。
従って、この機能を使用するときは、相反する設定を避けるために、全てのトランザクションについて分離レベルを明示的に指定しなければなりません。
このチュートリアルを書いている時点では、これに該当する DBMS は MSSQL と SQLite です。

> Note|注意: SQLite は、二つの分離レベルしかサポートしていません。すなわち、`READ UNCOMMITTED` と `SERIALIZABLE` しか使えません。
他のレベルを使おうとすると、例外が投げられます。

> Note|注意: PostgreSQL は、トランザクションを開始する前に分離レベルを指定することを許容していません。
すなわち、トランザクションを開始するときに、分離レベルを直接に指定することは出来ません。
この場合、トランザクションを開始した後に [[yii\db\Transaction::setIsolationLevel()]] を呼び出す必要があります。

[分離レベル]: http://ja.wikipedia.org/wiki/%E3%83%88%E3%83%A9%E3%83%B3%E3%82%B6%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3%E5%88%86%E9%9B%A2%E3%83%AC%E3%83%99%E3%83%AB


レプリケーションと読み書きの分離
--------------------------------

多くの DBMS は、データベースの可用性とサーバのレスポンスタイムを向上させるために、[データベースレプリケーション](http://ja.wikipedia.org/wiki/%E3%83%AC%E3%83%97%E3%83%AA%E3%82%B1%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3#.E3.83.87.E3.83.BC.E3.82.BF.E3.83.99.E3.83.BC.E3.82.B9) をサポートしています。
データベースレプリケーションによって、データはいわゆる *マスタサーバ* から *スレーブサーバ* に複製されます。
データの書き込みと更新はすべてマスタサーバ上で実行されなければなりませんが、データの読み出しはスレーブサーバ上でも可能です。

データベースレプリケーションを活用して読み書きの分離を達成するために、[[yii\db\Connection]] コンポーネントを下記のように構成することが出来ます。

```php
[
    'class' => 'yii\db\Connection',

    // マスタの構成
    'dsn' => 'dsn for master server',
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
        ['dsn' => 'スレーブサーバ 1 の DSN'],
        ['dsn' => 'スレーブサーバ 2 の DSN'],
        ['dsn' => 'スレーブサーバ 3 の DSN'],
        ['dsn' => 'スレーブサーバ 4 の DSN'],
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
$rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// マスタに対してクエリを実行する
$db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Info|情報: [[yii\db\Command::execute()]] を呼ぶことで実行されるクエリは、書き込みのクエリと見なされ、[[yii\db\Command]] の "query" メソッドのうちの一つによって実行されるその他すべてのクエリは、読み出しクエリと見なされます。
  現在アクティブなスレーブ接続は `$db->slave` によって取得することが出来ます。

`Connection` コンポーネントは、スレーブ間のロードバランス調整とフェイルオーバーをサポートしています。
読み出しクエリを最初に実行するときに、`Connection` コンポーネントはランダムにスレーブを選んで接続を試みます。
そのスレーブが「死んでいる」ことが分かったときは、他のスレーブを試します。
スレーブが一つも使用できないときは、マスタに接続します。
[[yii\db\Connection::serverStatusCache|サーバステータスキャッシュ]] を構成することによって、「死んでいる」サーバを記憶し、[[yii\db\Connection::serverRetryInterval|一定期間]] はそのサーバへの接続を再試行しないようにすることが出来ます。

> Info|情報: 上記の構成では、すべてのスレーブに対して 10 秒の接続タイムアウトが指定されています。
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
        ['dsn' => 'マスタサーバ 1 の DSN'],
        ['dsn' => 'マスタサーバ 2 の DSN'],
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
        ['dsn' => 'スレーブサーバ 1 の DSN'],
        ['dsn' => 'スレーブサーバ 2 の DSN'],
        ['dsn' => 'スレーブサーバ 3 の DSN'],
        ['dsn' => 'スレーブサーバ 4 の DSN'],
    ],
]
```

上記の構成は、二つのマスタと四つのスレーブを指定しています。
`Connection` コンポーネントは、スレーブ間での場合と同じように、マスタ間でのロードバランス調整とフェイルオーバーをサポートしています。
一つ違うのは、マスタが一つも利用できないときは例外が投げられる、という点です。

> Note|注意: [[yii\db\Connection::masters|masters]] プロパティを使って一つまたは複数のマスタを構成する場合は、データベース接続を定義する `Connection` オブジェクト自体のその他のプロパティ (例えば、`dsn`、`username`、`password`) は全て無視されます。

既定では、トランザクションはマスタ接続を使用します。そして、トランザクション内では、全ての DB 操作はマスタ接続を使用します。
例えば、

```php
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
}
```

スレーブ接続を使ってトランザクションを開始したいときは、次のように、明示的にそうする必要があります。

```php
$transaction = $db->slave->beginTransaction();
```

時として、読み出しクエリの実行にマスタ接続を使うことを強制したい場合があります。
これは、`useMaster()` メソッドを使うによって達成できます。

```php
$rows = $db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```

直接に `$db->enableSlaves` を false に設定して、全てのクエリをマスタ接続に向けることも出来ます。


データベーススキーマを扱う
--------------------------

### スキーマ情報を取得する

[[yii\db\Schema]] のインスタンスを次のようにして取得することが出来ます。

```php
$schema = $connection->getSchema();
```

このオブジェクトが、データベースについてのいろいろなスキーマ情報を読み取ることを可能にする一連のメソッドを持っています。

```php
$tables = $schema->getTableNames();
```

完全なリファレンスとしては、[[yii\db\Schema]] を参照してください。

### スキーマを修正する

基本的な SQL クエリに加えて、[[yii\db\Command]] はデータベーススキーマの修正を可能にする一連のメソッドを持っています。

- createTable, renameTable, dropTable, truncateTable
- addColumn, renameColumn, dropColumn, alterColumn
- addPrimaryKey, dropPrimaryKey
- addForeignKey, dropForeignKey
- createIndex, dropIndex

これらは、次のようにして使用することが出来ます。

```php
// TABLE を作成する
$connection->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

完全なリファレンスとしては、[[yii\db\Command]] を参照してください。
