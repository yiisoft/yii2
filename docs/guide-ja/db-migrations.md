データベース・マイグレーション
============================

データベース駆動型のアプリケーションを開発し保守する途上で、ソース・コードが進化するのと同じように、
使用されるデータベースの構造も進化していきます。
例えば、アプリケーションの開発中に、新しいテーブルが必要であることが分ったり、アプリケーションを配備した後に、
クエリのパフォーマンスを向上させるためにインデックスを作成すべきことが発見されたりします。
データベースの構造の変更が何らかのソース・コードの変更を要求する場合はよくありますから、
Yii はいわゆる *データベース・マイグレーション* 機能を提供して、ソース・コードとともにバージョン管理される
*データベース・マイグレーション* の形式でデータベースの変更を追跡できるようにしています。

下記の一連のステップは、開発中にチームによってデータベース・マイグレーションがどのように使用されるかを示す例です。

1. Tim が新しいマイグレーション (例えば、新しいテーブルを作成したり、カラムの定義を変更したりなど) を作る。
2. Tim が新しいマイグレーションをソース・コントロール・システム (例えば Git や Mercurial) にコミットする。
3. Doug がソース・コントロール・システムから自分のレポジトリを更新して新しいマイグレーションを受け取る。
4. Doug がマイグレーションを彼のローカルの開発用データベースに適用して、自分のデータベースの同期を取り、
   Tim が行った変更を反映する。

そして、次の一連のステップは、本番環境でデータベース・マイグレーションとともに新しいリリースを配備する方法を示すものです。

1. Scott は新しいデータベース・マイグレーションをいくつか含むプロジェクトのレポジトリにリリース・タグを作成する。
2. Scott は本番サーバでソース・コードをリリース・タグまで更新する。
3. Scott は本番のデータベースに対して累積したデータベース・マイグレーションを全て適用する。

Yii は一連のマイグレーション・コマンドライン・ツールを提供して、以下の機能をサポートします。

* 新しいマイグレーションの作成
* マイグレーションの適用
* マイグレーションの取消
* マイグレーションの再適用
* マイグレーションの履歴と状態の表示

これらのツールは、全て、`yii migrate` コマンドからアクセスすることが出来ます。
このセクションでは、これらのツールを使用して、さまざまなタスクをどうやって達成するかを詳細に説明します。
各ツールの使用方法は、ヘルプコマンド `yii help migrate` によっても知ることが出来ます。

> Tip: マイグレーションはデータベース・スキーマに影響を及ぼすだけでなく、既存のデータを新しいスキーマに合うように修正したり、RBAC 階層を作成したり、
キャッシュをクリーンアップしたりするために使うことも出来ます。

> Note: マイグレーションを使ってデータを操作する際に、作成済みの[アクティブ・レコード](db-active-record.md)・クラスを使えば
> 便利かも知れないと気が付くことがあるでしょう。なぜなら、ロジックのいくつかは既にアクティブ・レコードで実装済みなのですから。
> しかしながら、永久に不変であり続けることを本質とするマイグレーションにおいて書かれるコードとは対照的に、アプリケーションの
> ロジックは変化にさらされるものであることに留意しなければなりません。つまり、マイグレーション・コードにアクティブ・レコードを使った場合、
> アクティブ・レコードのレイヤにおけるロジックの変更が既存のマイグレーションを偶発的に破壊する可能性があります。この理由により、
> マイグレーション・コードは、アクティブ・レコード・クラスなど、他のアプリケーション・ロジックから独立を保つべきです。


## マイグレーションを作成する <span id="creating-migrations"></span>

新しいマイグレーションを作成するためには、次のコマンドを実行します。

```
yii migrate/create <name>
```

要求される `name` パラメータには、マイグレーションの非常に短い説明を指定します。
例えば、マイグレーションが *news* という名前のテーブルを作成するものである場合は、
`create_news_table` という名前を使って、次のようにコマンドを実行すれば良いでしょう。

```
yii migrate/create create_news_table
```

> Note: この `name` 引数は、生成されるマイグレーション・クラス名の一部として使用されますので、
  アルファベット、数字、および/または、アンダースコアだけを含むものでなければなりません。

上記のコマンドは、`m150101_185401_create_news_table.php` という名前の新しい PHP クラス・ファイルを
`@app/migrations` ディレクトリに作成します。このファイルは次のようなコードを含み、主として、
スケルトン・コードを持った `m150101_185401_create_news_table` というマイグレーション・クラスを宣言するためのものす。

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {

    }

    public function down()
    {
        echo "m101129_185401_create_news_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
```

各データベース・マイグレーションは [[yii\db\Migration]] から拡張した PHP クラスとして定義されます。
マイグレーション・クラスの名前は、`m<YYMMDD_HHMMSS>_<Name>` という形式で自動的に生成されます。ここで、

* `<YYMMDD_HHMMSS>` は、マイグレーション作成コマンドが実行された UTC 日時を表し、
* `<Name>` は、あなたがコマンドに与えた `name` 引数と同じ値になります。

マイグレーション・クラスにおいて、あなたがなすべき事は、データベースの構造に変更を加える `up()` メソッドにコードを書くことです。
また、`up()` によって加えられた変更を取り消すための `down()` メソッドにも、コードを書きたいと思うかもしれません。
`up()` メソッドは、このマイグレーションによってデータベースをアップグレードする際に呼び出され、`down()` メソッドはデータベースをダウングレードする際に呼び出されます。
下記のコードは、新しい `news` テーブルを作成するマイグレーション・クラスをどのようにして実装するかを示すものです。

```php
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'content' => Schema::TYPE_TEXT,
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }
}
```

> Info: 全てのマイグレーションが取り消し可能な訳ではありません。
  例えば、`up()` メソッドがテーブルからある行を削除するものである場合、`down()` メソッドでその行を回復することは出来ません。
  また、データベース・マイグレーションを取り消すことはあまり一般的ではありませんので、場合によっては、面倒くさいというだけの理由で `down()` を実装しないこともあるでしょう。
  そういう場合は、マイグレーションが取り消し不可能であることを示すために、`down()` メソッドで false を返さなければなりません。

基底のマイグレーション・クラス [[yii\db\Migration]] は、[[yii\db\Migration::db|db]] プロパティによって、
データベース接続にアクセスすることを可能にしています。このデータベース接続によって、[データベース・スキーマを扱う](db-dao.md#database-schema)
で説明されているメソッドを使い、データベース・スキーマを操作することが出来ます。

テーブルやカラムを作成するときは、物理的な型を使うのでなく、*抽象型* を使って、
あなたのマイグレーションが特定の DBMS に依存しないようにします。
[[yii\db\Schema]] クラスが、サポートされている抽象型を表す一連の定数を定義しています。
これらの定数は `TYPE_<Name>` という形式の名前を持っています。
例えば、`TYPE_PK` は、オート・インクリメントのプライマリ・キー型であり、`TYPE_STRING` は文字列型です。
これらの抽象型は、マイグレーションが特定のデータベースに適用されるときに、対応する物理型に翻訳されます。
MySQL の場合は、`TYPE_PK` は `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY` に変換され、`TYPE_STRING` は `varchar(255)` となります。

抽象型を使用するときに、付随的な制約を追加することが出来ます。
上記の例では、`Schema::TYPE_STRING` に ` NOT NULL` を追加して、このカラムが null を許容しないことを指定しています。

> Info: 抽象型と物理型の対応関係は、それぞれの `QueryBuilder` の具象クラスの [[yii\db\QueryBuilder::$typeMap|$typeMap]]
  プロパティによって定義されています。

バージョン 2.0.6 以降は、カラムのスキーマを定義するための更に便利な方法を提供するスキーマビルダが新たに導入されています。
したがって、上記のマイグレーションは次のように書くことが出来ます。

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }
}
```

カラムの型を定義するために利用できる全てのメソッドのリストは、[[yii\db\SchemaBuilderTrait]] の API ドキュメントで参照することが出来ます。

> Info: 生成されるファイルのパーミッションと所有者は現在の環境によって決定されます。
  これが原因でファイルにアクセス出来ない場合が生じ得ます。例えば、docker コンテナ内で作成されたマイグレーションのファイルをホストで編集するとそうなる場合があります。
  このような場合には MigrateController の `newFileMode` および/または `newFileOwnership` を変更することが出来ます。
  例えば、アプリケーション設定で次のように設定します。
  ```php
  <?php
  return [
      'controllerMap' => [
          'migrate' => [
              'class' => 'yii\console\controllers\MigrateController',
              'newFileOwnership' => '1000:1000', # Default WSL user id
              'newFileMode' => 0660,
          ],
      ],
  ];
  ```

## マイグレーションを生成する <span id="generating-migrations"></span>

バージョン 2.0.7 以降では、マイグレーション・コンソールがマイグレーションを生成する便利な方法を提供しています。

マイグレーションの名前が特別な形式である場合は、生成されるマイグレーション・ファイルに追加のコードが書き込まれます。
例えば、`create_xxx_table` や `drop_xxx_table` であれば、テーブルの作成や削除をするコードが追加されます。
以下で、この機能の全ての変種を説明します。

### テーブルの作成

```
yii migrate/create create_post_table
``` 

上記のコマンドは、次のコードを生成します。

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

テーブルのフィールドも直接に生成したい場合は、`--fields` オプションでフィールドを指定します。
 
```
yii migrate/create create_post_table --fields="title:string,body:text"
``` 

これは、次のコードを生成します。

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}

```

さらに多くのフィールド・パラメータを指定することも出来ます。

```
yii migrate/create create_post_table --fields="title:string(12):notNull:unique,body:text"
``` 

これは、次のコードを生成します。

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

> Note: プライマリ・キーが自動的に追加されて、デフォルトでは `id` と名付けられます。
> 別の名前を使いたい場合は、`--fields="name:primaryKey"` のように、明示的に指定してください。

#### 外部キー

バージョン 2.0.8 からは、`foreignKey` キーワードを使って外部キーを生成することができます。

```
yii migrate/create create_post_table --fields="author_id:integer:notNull:foreignKey(user),category_id:integer:defaultValue(1):foreignKey,title:string,body:text"
```

これは、次のコードを生成します。

```php
/**
 * Handles the creation for table `post`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `category`
 */
class m160328_040430_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->defaultValue(1),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);

        // creates index for column `author_id`
        $this->createIndex(
            'idx-post-author_id',
            'post',
            'author_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-post-author_id',
            'post',
            'author_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-post-category_id',
            'post',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-post-category_id',
            'post',
            'category_id',
            'category',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-post-author_id',
            'post'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-post-author_id',
            'post'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-post-category_id',
            'post'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-post-category_id',
            'post'
        );

        $this->dropTable('post');
    }
}
```

カラムの記述における `foreignKey` キーワードの位置によって、生成されるコードが変ることはありません。
つまり、

- `author_id:integer:notNull:foreignKey(user)`
- `author_id:integer:foreignKey(user):notNull`
- `author_id:foreignKey(user):integer:notNull`

これらはすべて同じコードを生成します。

`foreignKey` キーワードは括弧の中にパラメータを取ることが出来て、
これが生成される外部キーの関連テーブルの名前になります。
パラメータが渡されなかった場合は、テーブル名はカラム名から推測されます。

上記の例で `author_id:integer:notNull:foreignKey(user)` は、
`user` テーブルへの外部キーを持つ `author_id` という名前のカラムを生成します。
一方、`category_id:integer:defaultValue(1):foreignKey` は、
`category` テーブルへの外部キーを持つ `category_id` というカラムを生成します。

2.0.11 以降では、`foreignKey` キーワードは空白で区切られた第二のパラメータを取ることが出来ます。
これは、生成される外部キーに関連づけられるカラム名を表します。
第二のパラメータが渡されなかった場合は、カラム名はテーブル・スキーマから取得されます。
スキーマが存在しない場合や、プライマリ・キーが設定されていなかったり、複合キーであったりする場合は、デフォルト名として `id` が使用されます。

### テーブルを削除する

```
yii migrate/create drop_post_table --fields="title:string(12):notNull:unique,body:text"
``` 

これは、次のコードを生成します。

```php
class m150811_220037_drop_post_table extends Migration
{
    public function up()
    {
        $this->dropTable('post');
    }

    public function down()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }
}
```

### カラムを追加する

マイグレーションの名前が `add_xxx_column_to_yyy_table` の形式である場合、ファイルの内容は、
必要となる `addColumn` と `dropColumn` を含むことになります。

カラムを追加するためには、次のようにします。

```
yii migrate/create add_position_column_to_post_table --fields="position:integer"
```

これが次のコードを生成します。

```php
class m150811_220037_add_position_column_to_post_table extends Migration
{
    public function up()
    {
        $this->addColumn('post', 'position', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('post', 'position');
    }
}
```

次のようにして複数のカラムを指定することも出来ます。

```
yii migrate/create add_xxx_column_yyy_column_to_zzz_table --fields="xxx:integer,yyy:text"
```

### カラムを削除する

マイグレーションの名前が `drop_xxx_column_from_yyy_table` の形式である場合、
ファイルの内容は、必要となる `addColumn` と `dropColumn` を含むことになります。

```
yii migrate/create drop_position_column_from_post_table --fields="position:integer"
```

これは、次のコードを生成します。

```php
class m150811_220037_drop_position_column_from_post_table extends Migration
{
    public function up()
    {
        $this->dropColumn('post', 'position');
    }

    public function down()
    {
        $this->addColumn('post', 'position', $this->integer());
    }
}
```

### 中間テーブルを追加する

マイグレーションの名前が `create_junction_table_for_xxx_and_yyy_tables` の形式である場合は、
中間テーブルを作成するのに必要となるコードが生成されます。

```
yii migrate/create create_junction_table_for_post_and_tag_tables --fields="created_at:dateTime"
```

これは、次のコードを生成します。

```php
/**
 * Handles the creation for table `post_tag`.
 * Has foreign keys to the tables:
 *
 * - `post`
 * - `tag`
 */
class m160328_041642_create_junction_table_for_post_and_tag_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post_tag', [
            'post_id' => $this->integer(),
            'tag_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'PRIMARY KEY(post_id, tag_id)',
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_tag-post_id',
            'post_tag',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_tag-post_id',
            'post_tag',
            'post_id',
            'post',
            'id',
            'CASCADE'
        );

        // creates index for column `tag_id`
        $this->createIndex(
            'idx-post_tag-tag_id',
            'post_tag',
            'tag_id'
        );

        // add foreign key for table `tag`
        $this->addForeignKey(
            'fk-post_tag-tag_id',
            'post_tag',
            'tag_id',
            'tag',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // drops foreign key for table `post`
        $this->dropForeignKey(
            'fk-post_tag-post_id',
            'post_tag'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_tag-post_id',
            'post_tag'
        );

        // drops foreign key for table `tag`
        $this->dropForeignKey(
            'fk-post_tag-tag_id',
            'post_tag'
        );

        // drops index for column `tag_id`
        $this->dropIndex(
            'idx-post_tag-tag_id',
            'post_tag'
        );

        $this->dropTable('post_tag');
    }
}
```

2.0.11 以降では、中間テーブルの外部キーのカラム名はテーブル・スキーマから取得されます。
スキーマでテーブルが定義されていない場合や、プライマリ・キーが設定されていなかったり複合キーであったりする場合は、デフォルト名 `id` が使われます。

### トランザクションを使うマイグレーション <span id="transactional-migrations"></span>

複雑な一連の DB マイグレーションを実行するときは、通常、データベースの一貫性と整合性を保つために、
各マイグレーションが全体として成功または失敗することを保証する必要があります。
この目的を達成するために、各マイグレーションの DB 操作を [トランザクション](db-dao.md#performing-transactions) で囲むことが推奨されます。

トランザクションを使うマイグレーションを実装するためのもっと簡単な方法は、マイグレーションのコードを `safeUp()` と `safeDown()` のメソッドに入れることです。
この二つのメソッドが `up()` および `down()` と違う点は、これらが暗黙のうちにトランザクションに囲まれていることです。
結果として、これらのメソッドの中で何か操作が失敗した場合は、先行する全ての操作が自動的にロールバックされます。

次の例では、`news` テーブルを作成するだけでなく、このテーブルに初期値となる行を挿入しています。

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
        ]);

        $this->insert('news', [
            'title' => 'test 1',
            'content' => 'content 1',
        ]);
    }

    public function safeDown()
    {
        $this->delete('news', ['id' => 1]);
        $this->dropTable('news');
    }
}
```

通常、`safeUp()` で複数の DB 操作を実行する場合は、`safeDown()` では実行の順序を逆にしなければならないことに注意してください。
上記の例では、`safeUp()` では、最初にテーブルを作って、次に行を挿入し、
`safeDown()` では、先に行を削除して、次にテーブルを削除しています。

> Note: 全ての DBMS がトランザクションをサポートしている訳ではありません。また、トランザクションに入れることが出来ない DB クエリもあります。
  いくつかの例を [暗黙のコミット](https://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html) で見ることが出来ます。
  その場合には、代りに、`up()` と `down()` を実装しなければなりません。


### データベース・アクセス・メソッド <span id="db-accessing-methods"></span>

基底のマイグレーション・クラス [[yii\db\Migration]] は、データベースにアクセスして操作するための一連のメソッドを提供しています。
あなたは、これらのメソッドが、[[yii\db\Command]] クラスによって提供される [DAO メソッド](db-dao.md) と同じような名前を付けられていることに気付くでしょう。
例えば、[[yii\db\Migration::createTable()]] メソッドは、[[yii\db\Command::createTable()]] と全く同じように、
新しいテーブルを作成します。

[[yii\db\Migration]] によって提供されているメソッドを使うことの利点は、[[yii\db\Command]]
インスタンスを明示的に作成する必要がないこと、そして、各メソッドを実行すると、
どのようなデータベース操作がどれだけの時間をかけて実行されたかを教えてくれる有益なメッセージが自動的に表示されることです。

以下がそういうデータベース・アクセス・メソッドの一覧です。

* [[yii\db\Migration::execute()|execute()]]: SQL 文を実行
* [[yii\db\Migration::insert()|insert()]]: 一行を挿入
* [[yii\db\Migration::batchInsert()|batchInsert()]]: 複数行を挿入
* [[yii\db\Migration::update()|update()]]: 行を更新
* [[yii\db\Migration::upsert()|upsert()]]: 一行を挿入または既に存在していれば更新 (2.0.14 以降)
* [[yii\db\Migration::delete()|delete()]]: 行を削除
* [[yii\db\Migration::createTable()|createTable()]]: テーブルを作成
* [[yii\db\Migration::renameTable()|renameTable()]]: テーブルの名前を変更
* [[yii\db\Migration::dropTable()|dropTable()]]: テーブルを削除
* [[yii\db\Migration::truncateTable()|truncateTable()]]: テーブル中の全ての行を削除
* [[yii\db\Migration::addColumn()|addColumn()]]: カラムを追加
* [[yii\db\Migration::renameColumn()|renameColumn()]]: カラムの名前を変更
* [[yii\db\Migration::dropColumn()|dropColumn()]]: カラムを削除
* [[yii\db\Migration::alterColumn()|alterColumn()]]: カラムの定義を変更
* [[yii\db\Migration::addPrimaryKey()|addPrimaryKey()]]: プライマリ・キーを追加
* [[yii\db\Migration::dropPrimaryKey()|dropPrimaryKey()]]: プライマリ・キーを削除
* [[yii\db\Migration::addForeignKey()|addForeignKey()]]: 外部キーを追加
* [[yii\db\Migration::dropForeignKey()|dropForeignKey()]]: 外部キーを削除
* [[yii\db\Migration::createIndex()|createIndex()]]: インデックスを作成
* [[yii\db\Migration::dropIndex()|dropIndex()]]: インデックスを削除
* [[yii\db\Migration::addCommentOnColumn()|addCommentOnColumn()]]: カラムにコメントを追加
* [[yii\db\Migration::dropCommentFromColumn()|dropCommentFromColumn()]]: カラムからコメントを削除
* [[yii\db\Migration::addCommentOnTable()|addCommentOnTable()]]: テーブルにコメントを追加
* [[yii\db\Migration::dropCommentFromTable()|dropCommentFromTable()]]: テーブルからコメントを削除

> Info: [[yii\db\Migration]] は、データベース・クエリ・メソッドを提供しません。
> これは、通常、データベースからのデータ取得については、メッセージを追加して表示する必要がないからです。
> 更にまた、複雑なクエリを構築して実行するためには、強力な [クエリ・ビルダ](db-query-builder.md) を使うことが出来るからです。
> マイグレーションの中でクエリ・ビルダを使うと、次のようなコードになります。
>
> ```php
> // 全ユーザについて、status フィールドを更新する
> foreach((new Query)->from('users')->each() as $user) {
>     $this->update('users', ['status' => 1], ['id' => $user['id']]);
> }
> ```

## マイグレーションを適用する <span id="applying-migrations"></span>

データベースを最新の構造にアップグレードするためには、利用できる全ての新しいマイグレーションを適用するために、次のコマンドを使わなければなりません。

```
yii migrate
```

コマンドを実行すると、まだ適用されていない全てのマイグレーションが一覧表示されます。
リストされたマイグレーションを適用することをあなたが確認すると、タイムスタンプの値の順に、一つずつ、
すべての新しいマイグレーション・クラスの `up()` または `safeUp()` メソッドが実行されます。
マイグレーションのどれかが失敗した場合は、コマンドは残りのマイグレーションを適用せずに終了します。

> Tip: あなたのサーバでコマンドラインを使用できない場合は
> [web shell](https://github.com/samdark/yii2-webshell) エクステンションを使ってみてください。

適用が成功したマイグレーションの一つ一つについて、`migration` という名前のデータベース・テーブルに行が挿入されて、
マイグレーションの成功が記録されます。この記録によって、マイグレーション・ツールは、どのマイグレーションが適用され、
どのマイグレーションが適用されていないかを特定することが出来ます。

> Info: マイグレーション・ツールは、コマンドの [[yii\console\controllers\MigrateController::db|db]] オプションで指定されたデータベースに
  `migration` テーブルを自動的に作成します。デフォルトでは、このデータベースは
  `db` [アプリケーション・コンポーネント](structure-application-components.md) によって指定されます。

時として、利用できる全てのマイグレーションではなく、一つまたは数個の新しいマイグレーションだけを適用したい場合があります。
コマンドを実行するときに、適用したいマイグレーションの数を指定することによって、そうすることが出来ます。
例えば、次のコマンドは、次の三個の利用できるマイグレーションを適用しようとするものです。

```
yii migrate 3
```

また、そのマイグレーションまでをデータベースに適用するという、特定のマイグレーションを明示的に指定することも出来ます。
そのためには、`migrate/to` コマンドを、次のどれかの形式で使います。

```
yii migrate/to 150101_185401                      # タイムスタンプを使ってマイグレーションを指定
yii migrate/to "2015-01-01 18:54:01"              # strtotime() によって解釈できる文字列を使用
yii migrate/to m150101_185401_create_news_table   # フルネームを使用
yii migrate/to 1392853618                         # UNIX タイムスタンプを使用
```

指定されたマイグレーションよりも古いものが適用されずに残っている場合は、指定されたものが適用される前に、
すべて適用されます。

指定されたマイグレーションが既に適用済みである場合、それより新しいものが適用されていれば、すべて取り消されます。


## マイグレーションを取り消す <span id="reverting-migrations"></span>

適用済みのマイグレーションを一個または複数個取り消したい場合は、下記のコマンドを使うことが出来ます。

```
yii migrate/down     # 最近に適用されたマイグレーション一個を取り消す
yii migrate/down 3   # 最近に適用されたマイグレーション三個を取り消す
```

> Note: 全てのマイグレーションが取り消せるとは限りません。
  そのようなマイグレーションを取り消そうとするとエラーとなり、取り消しのプロセス全体が終了させられます。


## マイグレーションを再適用する <span id="redoing-migrations"></span>

マイグレーションの再適用とは、指定されたマイグレーションを最初に取り消してから、再度適用することを意味します。
これは次のコマンドによって実行することが出来ます。

```
yii migrate/redo        # 最後に適用された一個のマイグレーションを再適用する
yii migrate/redo 3      # 最後に適用された三個のマイグレーションを再適用する
```

> Note: マイグレーションが取り消し不可能な場合は、それを再適用することは出来ません。

## マイグレーションをリフレッシュする <span id="refreshing-migrations"></span>

Yii 2.0.13 以降、データベースから全てのテーブルと外部キーを削除して、全てのマイグレーションを最初から再適用することが出来ます。

```
yii migrate/fresh       # データベースを削除し、全てのマイグレーションを最初から適用する
```

## マイグレーションをリスト表示する <span id="listing-migrations"></span>

どのマイグレーションが適用済みであり、どのマイグレーションが未適用であるかをリスト表示するために、次のコマンドを使うことが出来ます。

```
yii migrate/history     # 最後に適用された 10 個のマイグレーションを表示
yii migrate/history 5   # 最後に適用された 5 個のマイグレーションを表示
yii migrate/history all # 適用された全てのマイグレーションを表示

yii migrate/new         # 適用可能な最初の 10 個のマイグレーションを表示
yii migrate/new 5       # 適用可能な最初の 5 個のマイグレーションを表示
yii migrate/new all     # 適用可能な全てのマイグレーションを表示
```


## マイグレーション履歴を修正する <span id="modifying-migration-history"></span>

時として、実際にマイグレーションを適用したり取り消したりするのではなく、
データベースが特定のマイグレーションまでアップグレードされたとマークしたいだけ、という場合があります。
このようなことがよく起るのは、データベースを手作業で特定の状態に変更した後に、その変更のための一つまたは複数のマイグレーションを記録はするが再度適用はしたくない、という場合です。
次のコマンドでこの目的を達することが出来ます。

```
yii migrate/mark 150101_185401                      # タイムスタンプを使ってマイグレーションを指定
yii migrate/mark "2015-01-01 18:54:01"              # strtotime() によって解釈できる文字列を使用
yii migrate/mark m150101_185401_create_news_table   # フルネームを使用
yii migrate/mark 1392853618                         # UNIX タイムスタンプを使用
```

このコマンドは、一定の行を追加または削除して、`migration` テーブルを修正し、データベースが指定されたものまでマイグレーションが適用済みであることを示します。
このコマンドによってマイグレーションが適用されたり取り消されたりはしません。


## マイグレーションをカスタマイズする <span id="customizing-migrations"></span>

マイグレーションコマンドをカスタマイズする方法がいくつかあります。


### コマンドライン・オプションを使う <span id="using-command-line-options"></span>

マイグレーション・コマンドには、その動作をカスタマイズするために使うことが出来るコマンドライン・オプションがいくつかあります。

* `interactive`: 真偽値 (デフォルト値は true)。マイグレーションを対話モードで実行するかどうかを指定します。
  true である場合は、コマンドが何らかの操作を実行する前に、ユーザは確認を求められます。
  コマンドがバックグラウンドのプロセスで使用される場合は、このオプションを false にセットします。

* `migrationPath`: 文字列 (デフォルト値は `@app/migrations`)。
  全てのマイグレーション・クラス・ファイルを保存しているディレクトリを指定します。
  この値は、ディレクトリ・パスか、パス・[エイリアス](concept-aliases.md) として指定することが出来ます。
  ディレクトリが存在する必要があり、そうでなければコマンドがエラーを発生させることに注意してください。

* `migrationTable`: 文字列 (デフォルト値は `migration`)。マイグレーション履歴の情報を保存するためのデータベース・テーブル名を指定します。
  テーブルが存在しない場合は、コマンドによって自動的に作成されます。
  `version varchar(255) primary key, apply_time integer` という構造のテーブルを手作業で作成しても構いません。

* `db`: 文字列 (デフォルト値は `db`)。データベース [アプリケーション・コンポーネント](structure-application-components.md) の ID を指定します。
  このコマンドによってマイグレーションを適用されるデータベースを表します。

* `templateFile`: 文字列 (デフォルト値は `@yii/views/migration.php`)。
  スケルトンのマイグレーション・クラス・ファイルを生成するために使用されるテンプレート・ファイルのパスを指定します。
  この値は、ファイル・パスか、パス [エイリアス](concept-aliases.md) として指定することが出来ます。
  テンプレート・ファイルは PHP スクリプトであり、その中で、マイグレーション・クラスの名前を取得するための `$className` という事前定義された変数を使うことが出来ます。

* `generatorTemplateFiles`: 配列 (デフォルト値は `[
        'create_table' => '@yii/views/createTableMigration.php',
        'drop_table' => '@yii/views/dropTableMigration.php',
        'add_column' => '@yii/views/addColumnMigration.php',
        'drop_column' => '@yii/views/dropColumnMigration.php',
        'create_junction' => '@yii/views/createTableMigration.php'
  ]`)。マイグレーション・コードを生成するためのテンプレート・ファイルを指定します。
  詳細は "[マイグレーションを生成する](#generating-migrations)" を参照してください。
  
* `fields`: マイグレーション・コードを生成するためのカラム定義文字列の配列。
  デフォルト値は `[]`。個々の定義の書式は `COLUMN_NAME:COLUMN_TYPE:COLUMN_DECORATOR` です。
  例えば、`--fields=name:string(12):notNull` は、サイズが 12 の null でない文字列カラムを作成します。

次の例は、これらのオプションの使い方を示すものです。

例えば、`forum` モジュールにマイグレーションを適用しようとしており、
そのマイグレーション・ファイルがモジュールの `migrations` ディレクトリに配置されている場合、
次のコマンドを使うことが出来ます。

```
# forum モジュールのマイグレーションを非対話的に適用する
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```


### コマンドをグローバルに構成する <span id="configuring-command-globally"></span>

マイグレーション・コマンドを実行するたびに同じオプションの値を入力する代りに、次のように、
アプリケーションの構成情報でコマンドを一度だけ構成して済ませることが出来ます。

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationTable' => 'backend_migration',
        ],
    ],
];
```

上記のように構成しておくと、`migrate` コマンドを実行するたびに、
`backend_migration` テーブルがマイグレーション履歴を記録するために使われるようになります。
もう、`migrationTable` のコマンドライン・オプションを使ってテーブルを指定する必要はなくなります。


### 名前空間を持つマイグレーション <span id="namespaced-migrations"></span>

2.0.10 以降では、マイグレーションのクラスに名前空間を適用することが出来ます。
マイグレーションの名前空間のリストをを [[yii\console\controllers\MigrateController::migrationNamespaces|migrationNamespaces]] によって指定することが出来ます。
マイグレーションのクラスに名前空間を使うと、マイグレーションのソースについて、複数の配置場所を使用することが出来ます。例えば、

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => null, // app\migrations が下記にあげられている場合に、名前空間に属さないマイグレーションを無効化する
            'migrationNamespaces' => [
                'app\migrations', // アプリケーション全体のための共通のマイグレーション
                'module\migrations', // プロジェクトの特定のモジュールのためのマイグレーション
                'some\extension\migrations', // 特定のエクステンションのためのマイグレーション
            ],
        ],
    ],
];
```

> Note: 異なる名前空間に属するマイグレーションを適用しても、**単一の** マイグレーション履歴が生成されます。
  つまり、特定の名前空間に属するマイグレーションだけを適用したり元に戻したりすることは出来ません。

名前空間を持つマイグレーションを操作するときは、新規作成時も、元に戻すときも、マイグレーション名の前にフルパスの名前空間を指定しなければなりません。
バック・スラッシュ (`\`) のシンボルは、通常、シェルでは特殊文字として扱われますので、シェルのエラーや誤った動作を防止するために、
適切にエスケープしなければならないことに注意して下さい。例えば、

```
yii migrate/create app\\migrations\\createUserTable
```

> Note: [[yii\console\controllers\MigrateController::migrationPath|migrationPath]] によって指定されたマイグレーションは、
  名前空間を持つことが出来ません。  名前空間を持つマイグレーションは [[yii\console\controllers\MigrateController::migrationNamespaces]]
  プロパティを通じてのみ適用可能です。

バージョン 2.0.12 以降は [[yii\console\controllers\MigrateController::migrationPath|migrationPath]] プロパティは
名前空間を持たないマイグレーションを含む複数のディレクトリを指定した配列を受け入れるようになりました。
この機能追加は、主として、いろんな場所にあるマイグレーションを使っている既存のプロジェクトによって使われることを意図しています。
これらのマイグレーションは、主として、他の開発者による Yii エクステンションなど、外部ソースに由来するものであり、
新しい手法を使い始めようとしても、名前空間を使うように変更することが簡単には出来ないものだからです。

#### 名前空間を持つマイグレーションを生成する

名前空間を持つマイグレーションは "CamelCase" の命名規則 `M<YYMMDDHHMMSS><Name>` (例えば `M190720100234CreateUserTable`) を持ちます。
このようなマイグレーションを生成するときは、テーブル名が "CamenCase" 形式から "アンダースコア" 形式に変換されることを
覚えておいて下さい。例えば、

```
yii migrate/create app\\migrations\\DropGreenHotelTable
```

上記のコマンドは、名前空間 `app\migrations` の中で、`green_hotel` というテーブルを削除するマイグレーションを生成します。そして、

```
yii migrate/create app\\migrations\\CreateBANANATable
```

というコマンドは、名前空間 `app\migrations` の中で `b_a_n_a_n_a` というテーブルを作成するマイグレーションを生成します。

テーブル名が大文字と小文字を含む（例えば `studentsExam`) ときは、名前の先頭にアンダースコアを付ける必要があります。

```
yii migrate/create app\\migrations\\Create_studentsExamTable
```

このコマンドは、名前空間 `app\migrations` の中で `studentsExam` というテーブルを作成するマイグレーションを生成します。

### 分離されたマイグレーション <span id="separated-migrations"></span>

プロジェクトのマイグレーション全体に単一のマイグレーション履歴を使用することが望ましくない場合もあります。
例えば、完全に独立した機能性とそれ自身のためのマイグレーションを持つような 'blog' エクステンションをインストールする場合には、
メインのプロジェクトの機能専用のマイグレーションに影響を与えたくないでしょう。

これらをお互いに完全に分離して適用かつ追跡したい場合は、別々の名前空間とマイグレーション履歴テーブルを使う
複数のマイグレーションコマンドを構成することが出来ます。

```php
return [
    'controllerMap' => [
        // アプリケーション全体のための共通のマイグレーション
        'migrate-app' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => ['app\migrations'],
            'migrationTable' => 'migration_app',
            'migrationPath' => null,
        ],
        // 特定のモジュールのためのマイグレーション
        'migrate-module' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => ['module\migrations'],
            'migrationTable' => 'migration_module',
            'migrationPath' => null,
        ],
        // 特定のエクステンションのためのマイグレーション
        'migrate-rbac' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => '@yii/rbac/migrations',
            'migrationTable' => 'migration_rbac',
        ],
    ],
];
```

データベースを同期するためには、一つではなく複数のコマンドを実行しなければならなくなることに注意してください。

```
yii migrate-app
yii migrate-module
yii migrate-rbac
```


## 複数のデータベースにマイグレーションを適用する <span id="migrating-multiple-databases"></span>

デフォルトでは、マイグレーションは `db` [アプリケーション・コンポーネント](structure-application-components.md) によって指定された同じデータベースに対して適用されます。
マイグレーションを別のデータベースに適用したい場合は、次のように、`db` コマンドライン・オプションを指定することが出来ます。

```
yii migrate --db=db2
```

上記のコマンドはマイグレーションを `db2` データベースに適用します。

場合によっては、*いくつかの* マイグレーションはあるデータベースに適用し、*別のいくつかの* マイグレーションはもう一つのデータベースに適用したい、ということがあります。
この目的を達するためには、マイグレーション・クラスを実装する時に、そのマイグレーションが使用する DB コンポーネントの ID を明示的に指定しなければなりません。
例えば、次のようにします。

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function init()
    {
        $this->db = 'db2';
        parent::init();
    }
}
```

上記のマイグレーションは、`db` コマンドライン・オプションで別のデータベースを指定した場合でも、`db2` に対して適用されます。
ただし、マイグレーション履歴は、`db` コマンドライン・オプションで指定されたデータベースに記録されることに注意してください。

同じデータベースを使う複数のマイグレーションがある場合は、上記の `init()` コードを持つ基底のマイグレーション・クラスを作成することを推奨します。
そうすれば、個々のマイグレーション・クラスは、その基底クラスから拡張することが出来ます。

> Tip: 異なるデータベースを操作するためには、[[yii\db\Migration::db|db]] プロパティを設定する以外にも、
  マイグレーション・クラスの中で新しいデータベース接続を作成するという方法があります。
  そうすれば、そのデータベース接続で [DAO メソッド](db-dao.md) を使って、違うデータベースを操作することが出来ます。

複数のデータベースに対してマイグレーションを適用するために採用できるもう一つの戦略としては、異なるデータベースに対するマイグレーションは異なるマイグレーションパスに保持する、というものがあります。
そうすれば、次のように、異なるデータベースのマイグレーションを別々のコマンドで適用することが出来ます。

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

最初のコマンドは `@app/migrations/db1` にあるマイグレーションを `db1` データベースに適用し、
第二のコマンドは `@app/migrations/db2` にあるマイグレーションを `db2` データベースに適用する、という具合です。
