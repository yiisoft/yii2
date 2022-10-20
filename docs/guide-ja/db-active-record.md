アクティブ・レコード
====================

[アクティブ・レコード](https://ja.wikipedia.org/wiki/Active_Record) は、データベースに保存されているデータにアクセスするために、
オブジェクト指向のインタフェイスを提供するものです。
アクティブ・レコード・クラスはデータベース・テーブルと関連付けられます。
アクティブ・レコードのインスタンスはそのテーブルの行に対応し、アクティブ・レコードのインスタンスの *属性* がその行にある特定のカラムの値を表現します。
生の SQL 文を書く代りに、アクティブ・レコードの属性にアクセスしたり、アクティブ・レコードのメソッドを呼んだりして、
データベース・テーブルに保存さているデータにアクセスしたり、データを操作したりします。

例えば、`Customer` が `customer` テーブルに関連付けられたアクティブ・レコード・クラスであり、
`name` が `customer` テーブルのカラムであると仮定しましょう。
`customer` テーブルに新しい行を挿入するために次のコードを書くことが出来ます。

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

上記のコードは、MySQL では、次のような生の SQL 文を使うのと等価なものです。
しかし、生の SQL 文の方は、直感的でなく、間違いも生じやすく、また、別の種類のデータベースを使う場合には、互換性の問題も生じ得ます。

```php
$db->createCommand('INSERT INTO `customer` (`name`) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

Yii は次のリレーショナル・データベースに対して、アクティブ・レコードのサポートを提供しています。

* MySQL 4.1 以降: [[yii\db\ActiveRecord]] による。
* PostgreSQL 7.3 以降: [[yii\db\ActiveRecord]] による。
* SQLite 2 および 3: [[yii\db\ActiveRecord]] による。
* Microsoft SQL Server 2008 以降: [[yii\db\ActiveRecord]] による。
* Oracle: [[yii\db\ActiveRecord]] による。
* CUBRID 9.3 以降: [[yii\db\ActiveRecord]] による。(cubrid PDO 拡張の [バグ](http://jira.cubrid.org/browse/APIS-658)
  のために、値を引用符で囲む機能が動作しません。そのため、サーバだけでなくクライアントも CUBRID 9.3 が必要になります)
* Sphinx: [[yii\sphinx\ActiveRecord]] による。`yii2-sphinx` エクステンションが必要。
* ElasticSearch: [[yii\elasticsearch\ActiveRecord]] による。`yii2-elasticsearch` エクステンションが必要。

これらに加えて、Yii は次の NoSQL データベースに対しても、アクティブ・レコードの使用をサポートしています。

* Redis 2.6.12 以降: [[yii\redis\ActiveRecord]] による。`yii2-redis` エクステンションが必要。
* MongoDB 1.3.0 以降: [[yii\mongodb\ActiveRecord]] による。`yii2-mongodb` エクステンションが必要。

このチュートリアルでは、主としてリレーショナル・データベースのためのアクティブ・レコードの使用方法を説明します。
しかし、ここで説明するほとんどの内容は NoSQL データベースのためのアクティブ・レコードにも適用することが出来るものです。


## アクティブ・レコード・クラスを宣言する <span id="declaring-ar-classes"></span>

まずは、[[yii\db\ActiveRecord]] を拡張してアクティブ・レコード・クラスを宣言するところから始めましょう。

### テーブル名を設定する

デフォルトでは、すべてのアクティブ・レコード・クラスはデータベース・テーブルと関連付けられます。
[[yii\db\ActiveRecord::tableName()|tableName()]] メソッドが、クラス名を [[yii\helpers\Inflector::camel2id()]] によって変換して、テーブル名を返します。
テーブル名がこの規約に従っていない場合は、このメソッドをオーバライドすることが出来ます。

同時に、デフォルトの [[yii\db\Connection::$tablePrefix|tablePrefix]] を適用することも可能です。
例えば、[[yii\db\Connection::$tablePrefix|tablePrefix]] が `tbl_` である場合は、`Customer` は `tbl_customer` になり、`OrderItem` は`tbl_order_item` になります。

テーブル名が `{{%TableName}}` という形式で与えられた場合は、パーセント記号 `%` がテーブルプレフィックスに置き換えられます。
例えば、`{{%post}}` は `{{tbl_post}}` となります。
テーブル名を囲む二重波括弧は、[テーブル名を囲む引用符号](db-dao.md#quoting-table-and-column-names) となります。

次の例では、`customer` というデータベース・テーブルのための `Customer` という名前のアクティブ・レコード・クラスを宣言しています。

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @return string このアクティブ・レコード・クラスと関連付けられるテーブルの名前
     */
    public static function tableName()
    {
        return '{{customer}}';
    }
}
```

### アクティブ・レコードは「モデル」と呼ばれる

アクティブ・レコードのインスタンスは [モデル](structure-models.md) であると見なされます。
この理由により、私たちは通常 `app\models` 名前空間 (あるいはモデル・クラスを保管するための他の名前空間) の下にアクティブ・レコード・クラスを置きます。

[[yii\db\ActiveRecord]] は [[yii\base\Model]] から拡張していますので、属性、検証規則、データのシリアル化など、
[モデル](structure-models.md) が持つ *全ての* 機能を継承しています。


## データベースに接続する <span id="db-connection"></span>

デフォルトでは、アクティブ・レコードは、`db` [アプリケーション・コンポーネント](structure-application-components.md) を
[[yii\db\Connection|DB 接続]] として使用して、データベースのデータにアクセスしたり操作したりします。
[データベース・アクセス・オブジェクト](db-dao.md) で説明したように、次のようにして、アプリケーションの構成情報ファイルの中で
`db` コンポーネントを構成することが出来ます。

```php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=testdb',
            'username' => 'demo',
            'password' => 'demo',
        ],
    ],
];
```

`db` コンポーネントとは異なるデータベース接続を使いたい場合は、[[yii\db\ActiveRecord::getDb()|getDb()]]
メソッドをオーバーライドしなければなりません。

```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        // "db2" アプリケーション・コンポーネントを使用
        return \Yii::$app->db2;
    }
}
```


## データをクエリする <span id="querying-data"></span>

アクティブ・レコード・クラスを宣言した後、それを使って対応するデータベース・テーブルからデータをクエリすることが出来ます。
このプロセスは通常次の三つのステップを踏みます。

1. [[yii\db\ActiveRecord::find()]] メソッドを呼んで、新しいクエリ・オブジェクトを作成する。
2. [クエリ構築メソッド](db-query-builder.md#building-queries) を呼んで、クエリ・オブジェクトを構築する。
3. [クエリ・メソッド](db-query-builder.md#query-methods) を呼んで、アクティブ・レコードのインスタンスの形でデータを取得する。

ご覧のように、このプロセスは [クエリ・ビルダ](db-query-builder.md) による手続きと非常によく似ています。
唯一の違いは、`new` 演算子を使ってクエリ・オブジェクトを生成する代りに、[[yii\db\ActiveQuery]] クラスであるクエリ・オブジェクトを返す
[[yii\db\ActiveRecord::find()]] を呼ぶ、という点です。

以下の例は、アクティブ・クエリを使ってデータをクエリする方法を示すものです。

```php
// ID が 123 である一人の顧客を返す
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::find()
    ->where(['id' => 123])
    ->one();

// アクティブな全ての顧客を返して、ID によって並べる
// SELECT * FROM `customer` WHERE `status` = 1 ORDER BY `id`
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// アクティブな顧客の数を返す
// SELECT COUNT(*) FROM `customer` WHERE `status` = 1
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// 全ての顧客を顧客IDによってインデックスされた配列として返す
// SELECT * FROM `customer`
$customers = Customer::find()
    ->indexBy('id')
    ->all();
```

上記において、`$customer` は `Customer` オブジェクトであり、`$customers` は `Customer` オブジェクトの配列です。
全てこれらには `customer` テーブルから取得されたデータが投入されます。

> Info: [[yii\db\ActiveQuery]] は [[yii\db\Query]] から拡張しているため、[クエリ・ビルダ](db-query-builder.md)
  のセクションで説明されたクエリ構築メソッドとクエリ・メソッドの *全て* を使うことが出来ます。

プライマリ・キーの値や一群のカラムの値でクエリをすることはよく行われる仕事ですので、Yii はこの目的のために、
二つのショートカット・メソッドを提供しています。

- [[yii\db\ActiveRecord::findOne()]]: クエリ結果の最初の行を一つのアクティブ・レコード・インスタンスに投入して返す。
- [[yii\db\ActiveRecord::findAll()]]: *全ての* クエリ結果をアクティブ・レコード・インスタンスの配列に投入して返す。

どちらのメソッドも、次のパラメータ形式のどれかを取ることが出来ます。

- スカラ値: 値は検索時に求められるプライマリ・キーの値として扱われます。
  Yii は、データベースのスキーマ情報を読んで、どのカラムがプライマリ・キーのカラムであるかを自動的に判断します。
- スカラ値の配列: 配列は検索時に求められるプライマリ・キーの値の配列として扱われます。
- 連想配列: キーはカラム名であり、値は検索時に求められる対応するカラムの値です。
  詳細については、[ハッシュ形式](db-query-builder.md#hash-format) を参照してください。

次のコードは、これらのメソッドの使用方法を示すものです。

```php
// ID が 123 である一人の顧客を返す
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// ID が 100, 101, 123, 124 のどれかである顧客を全て返す
// SELECT * FROM `customer` WHERE `id` IN (100, 101, 123, 124)
$customers = Customer::findAll([100, 101, 123, 124]);

// ID が 123 であるアクティブな顧客を返す
// SELECT * FROM `customer` WHERE `id` = 123 AND `status` = 1
$customer = Customer::findOne([
    'id' => 123,
    'status' => Customer::STATUS_ACTIVE,
]);

// アクティブでない全ての顧客を返す
// SELECT * FROM `customer` WHERE `status` = 0
$customers = Customer::findAll([
    'status' => Customer::STATUS_INACTIVE,
]);
```

> Warning: これらのメソッドにユーザ入力を渡す必要がある場合は、入力値がスカラ値であること、または、
> 入力値が配列形式の条件である場合は配列の構造が外部から変更され得ないことを保証して下さい。
>
> ```php
> // yii\web\Controller が $id はスカラ値であることを保証しています
> public function actionView($id)
> {
>     $model = Post::findOne($id);
>     // ...
> }
>
> // 検索するカラムを明示的に指定する場合。ここでは、どんなスカラ値または配列を渡しても、単一のレコードを発見する結果になります。
> $model = Post::findOne(['id' => Yii::$app->request->get('id')]);
>
> // 次のコードを使用してはいけません! 任意のカラムの値による検索が可能な配列形式の条件を挿入される可能性があります!
> $model = Post::findOne(Yii::$app->request->get('id'));
> ```


> Note: [[yii\db\ActiveRecord::findOne()]] も [[yii\db\ActiveQuery::one()]] も、生成される SQL 文に `LIMIT 1` を追加しません。
  あなたのクエリが多数のデータ行を返すかもしれない場合は、パフォーマンスを向上させるために、`limit(1)` を明示的に呼ぶべきです。
  例えば `Customer::find()->limit(1)->one()` のように。

クエリ構築メソッドを使う以外に、生の SQL を書いてデータをクエリして結果をアクティブ・レコード・オブジェクトに投入することも出来ます。
そうするためには [[yii\db\ActiveRecord::findBySql()]] メソッドを呼ぶことが出来ます。

```php
// アクティブでない全ての顧客を返す
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

[[yii\db\ActiveRecord::findBySql()|findBySql()]] を呼んだ後は、追加でクエリ構築メソッドを呼び出してはいけません。
呼んでも無視されます。


## データにアクセスする <span id="accessing-data"></span>

既に述べたように、データベースから取得されたデータはアクティブ・レコードのインスタンスに投入されます。
そして、クエリ結果の各行がアクティブ・レコードの一つのインスタンスに対応します。
アクティブ・レコード・インスタンスの属性にアクセスすることによって、カラムの値にアクセスすることが出来ます。例えば、

```php
// "id" と "email" は "customer" テーブルのカラム名
$customer = Customer::findOne(123);
$id = $customer->id;
$email = $customer->email;
```

> Note: アクティブ・レコードの属性の名前は、関連付けられたテーブルのカラムの名前に従って、大文字と小文字を区別して名付けられます。
  Yii は、関連付けられたテーブルの全てのカラムに対して、アクティブ・レコードの属性を自動的に定義します。
  これらの属性は、すべて、再宣言してはいけません。

アクティブ・レコードの属性はテーブルのカラムに従って命名されるため、
テーブルのカラム名がアンダースコアで単語を分ける方法で命名されている場合は、
`$customer->first_name` のような属性名を使って PHP コードを書くことになります。
コード・スタイルの一貫性が気になるのであれば、テーブルのカラム名を (例えば camelCase を使う名前に) 変更しなければなりません。


### データ変換 <span id="data-transformation"></span>

入力または表示されるデータの形式が、データベースにデータを保存するときに使われるものと異なる場合がよくあります。
例えば、データベースでは顧客の誕生日を UNIX タイムスタンプで保存している (まあ、あまり良い設計ではありませんが)
けれども、ほとんどの場合において誕生日を `'YYYY/MM/DD'` という形式の文字列として操作したい、というような場合です。
この目的を達するために、次のように、`Customer` アクティブ・レコード・クラスにおいて *データ変換*
メソッドを定義することが出来ます。

```php
class Customer extends ActiveRecord
{
    // ...

    public function getBirthdayText()
    {
        return date('Y/m/d', $this->birthday);
    }
    
    public function setBirthdayText($value)
    {
        $this->birthday = strtotime($value);
    }
}
```

このようにすれば、PHP コードにおいて、`$customer->birthday` にアクセスする代りに、`$customer->birthdayText` にアクセスすれば、
顧客の誕生日を `'YYYY/MM/DD'` の形式で入力および表示することが出来ます。

> Tip: 上記は、一般にデータの変換を達成するための簡単な方法を示すためのものです。
> 日付の値については、Yii は、[DateValidator](tutorial-core-validators.md#date) と DatePicker ウィジェットを使用するという、より良い方法を提供しています。
> DatePicker については、[JUI ウィジェットのセクション](widget-jui#datepicker-date-input) で説明されています。


### データを配列に取得する <span id="data-in-arrays"></span>

データをアクティブ・レコード・オブジェクトの形で取得するのは便利であり柔軟ですが、大きなメモリ使用量を要するために、
大量のデータを取得しなければならない場合は、必ずしも望ましい方法ではありません。
そういう場合は、クエリ・メソッドを実行する前に [[yii\db\ActiveQuery::asArray()|asArray()]] を呼ぶことによって、PHP 配列を使ってデータを取得することが出来ます。

```php
// すべての顧客を返す
// 各顧客は連想配列として返される
$customers = Customer::find()
    ->asArray()
    ->all();
```

> Note: このメソッドはメモリを節約してパフォーマンスを向上させますが、低レベルの DB 抽象レイヤに近いものであり、
  あなたはアクティブ・レコードの機能のほとんどを失うことになります。非常に重要な違いが、カラムの値のデータ型に現れます。
  アクティブ・レコード・インスタンスとしてデータを返す場合、カラムの値は実際のカラムの型に従って自動的に型キャストされます。
  一方、配列としてデータを返す場合は、実際のカラムの型に関係なく、カラムの値は文字列になります。
  なぜなら、何も処理をしない場合の PDO の結果は文字列だからです。


### データをバッチ・モードで取得する <span id="data-in-batches"></span>

[クエリ・ビルダ](db-query-builder.md) において、大量のデータをデータベースから検索する場合に、メモリ使用量を最小化するために
*バッチ・クエリ* を使うことが出来るということを説明しました。おなじテクニックをアクティブ・レコードでも使うことが出来ます。例えば、

```php
// 一度に 10 人の顧客を読み出す
foreach (Customer::find()->batch(10) as $customers) {
    // $customers は 10 以下の Customer オブジェクトの配列
}

// 一度に 10 人の顧客を読み出して、一人ずつ反復する
foreach (Customer::find()->each(10) as $customer) {
    // $customer は Customer オブジェクト
}

// イーガー・ローディングをするバッチ・クエリ
foreach (Customer::find()->with('orders')->each() as $customer) {
    // $customer は 'orders' リレーションを投入された Customer オブジェクト
}
```


## データを保存する <span id="inserting-updating-data"></span>

アクティブ・レコードを使えば、次のステップを踏んで簡単にデータをデータベースに保存することが出来ます。

1. アクティブ・レコードのインスタンスを準備する
2. アクティブ・レコードの属性に新しい値を割り当てる
3. [[yii\db\ActiveRecord::save()]] を呼んでデータをデータベースに保存する

例えば、

```php
// 新しいデータ行を挿入する
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();

// 既存のデータ行を更新する
$customer = Customer::findOne(123);
$customer->email = 'james@newexample.com';
$customer->save();
```

[[yii\db\ActiveRecord::save()|save()]] メソッドは、アクティブ・レコード・インスタンスの状態に従って、データ行を挿入するか、
または、更新することが出来ます。インスタンスが `new` 演算子によって新しく作成されたものである場合は、
[[yii\db\ActiveRecord::save()|save()]] を呼び出すと、新しい行が挿入されます。インスタンスがクエリ・メソッドの結果である場合は、
[[yii\db\ActiveRecord::save()|save()]] を呼び出すと、そのインスタンスと関連付けられた行が更新されます。

アクティブ・レコード・インスタンスの二つの状態は、その [[yii\db\ActiveRecord::isNewRecord|isNewRecord]]
プロパティの値をチェックすることによって区別することが出来ます。
下記のように、このプロパティは [[yii\db\ActiveRecord::save()|save()]] によっても内部的に使用されています。

```php
public function save($runValidation = true, $attributeNames = null)
{
    if ($this->getIsNewRecord()) {
        return $this->insert($runValidation, $attributeNames);
    } else {
        return $this->update($runValidation, $attributeNames) !== false;
    }
}
```

> Tip: [[yii\db\ActiveRecord::insert()|insert()]] または [[yii\db\ActiveRecord::update()|update()]] を直接に呼んで、
  行を挿入または更新することも出来ます。


### データの検証 <span id="data-validation"></span>

[[yii\db\ActiveRecord]] は [[yii\base\Model]] を拡張したものですので、同じ [データ検証](input-validation.md) 機能を共有しています。
[[yii\db\ActiveRecord::rules()|rules()]] メソッドをオーバーライドすることによって検証規則を宣言し、
[[yii\db\ActiveRecord::validate()|validate()]] メソッドを呼ぶことによってテータの検証を実行することが出来ます。

[[yii\db\ActiveRecord::save()|save()]] を呼ぶと、デフォルトでは [[yii\db\ActiveRecord::validate()|validate()]] が自動的に呼ばれます。
検証が通った時だけ、実際にデータが保存されます。
検証が通らなかった時は単に `false` が返され、[[yii\db\ActiveRecord::errors|errors]] プロパティをチェックして検証エラー・メッセージを取得することが出来ます。

> Tip: データが検証を必要としないことが確実である場合 (例えば、データが信頼できるソースに由来するものである場合) は、
  検証をスキップするために `save(false)` を呼ぶことが出来ます。


### 一括代入 <span id="massive-assignment"></span>

通常の [モデル](structure-models.md) と同じように、アクティブ・レコードのインスタンスも  [一括代入機能](structure-models.md#massive-assignment) を享受することが出来ます。
この機能を使うと、下記で示されているように、一つの PHP 文で、アクティブ・レコード・インスタンスの複数の属性に値を割り当てることが出来ます。
ただし、[安全な属性](structure-models.md#safe-attributes) だけが一括代入が可能であることを記憶しておいてください。

```php
$values = [
    'name' => 'James',
    'email' => 'james@example.com',
];

$customer = new Customer();

$customer->attributes = $values;
$customer->save();
```


### カウンタを更新する <span id="updating-counters"></span>

データベース・テーブルのあるカラムの値を増加・減少させるのは、よくある仕事です。私たちはそのようなカラムをカウンタ・カラムと呼んでいます。
[[yii\db\ActiveRecord::updateCounters()|updateCounters()]] を使って一つまたは複数のカウンタ・カラムを更新することが出来ます。
例えば、

```php
$post = Post::findOne(100);

// UPDATE `post` SET `view_count` = `view_count` + 1 WHERE `id` = 100
$post->updateCounters(['view_count' => 1]);
```

> Note: カウンタ・カラムを更新するのに [[yii\db\ActiveRecord::save()]] を使うと、不正確な結果になってしまう場合があります。
  というのは、同じカウンタの値を読み書きする複数のリクエストによって、同一のカウンタが保存される可能性があるからです。


### ダーティな属性 <span id="dirty-attributes"></span>

[[yii\db\ActiveRecord::save()|save()]] を呼んでアクティブ・レコード・インスタンスを保存すると、*ダーティな属性* だけが保存されます。
属性は、DB からロードされた後、または、最後に保存された後にその値が変更されると、*ダーティ* であると見なされます。
ただし、データ検証は、アクティブ・レコード・インスタンスがダーティな属性を持っているかどうかに関係なく実施される
ことに注意してください。

アクティブ・レコードはダーティな属性のリストを自動的に保守します。
そうするために、一つ前のバージョンの属性値を保持して、最新のバージョンと比較します。
[[yii\db\ActiveRecord::getDirtyAttributes()]] を呼ぶと、現在ダーティである属性を取得することが出来ます。
また、[[yii\db\ActiveRecord::markAttributeDirty()]] を呼んで、ある属性をダーティであると明示的にマークすることも出来ます。

最新の修正を受ける前の属性値を知りたい場合は、[[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]]
または [[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]] を呼ぶことが出来ます。

> Note: 新旧の値は `===` 演算子を使って比較されるため、同じ値を持っていても型が違うとダーティであると見なされます。
> このことは、モデルが HTML フォームからユーザの入力を受け取るときにしばしば生じます。
> HTML フォームでは全ての値が文字列として表現されるからです。
> 入力値が正しい型、例えば整数値となることを保証するために、`['attributeName', 'filter', 'filter' => 'intval']` のように
> [検証フィルタ](input-validation.md#data-filtering) を適用することが出来ます。
> このフィルタは、[intval()](https://www.php.net/manual/ja/function.intval.php), [floatval()](https://www.php.net/manual/ja/function.floatval.php),
> [boolval](https://www.php.net/manual/ja/function.boolval.php) など、PHP の全てのタイプキャスト関数で動作します。

### デフォルト属性値 <span id="default-attribute-values"></span>

あなたのテーブルのカラムの中には、データベースでデフォルト値が定義されているものがあるかも知れません。
そして、場合によっては、アクティブ・レコード・インスタンスのウェブ・フォームに、そういうデフォルト値をあらかじめ投入したいことがあるでしょう。
同じデフォルト値を繰り返して書くことを避けるために、[[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]]
を呼んで、DB で定義されたデフォルト値を対応するアクティブ・レコードの属性に投入することが出来ます。

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz には、"xyz" カラムを定義するときに宣言されたデフォルト値が割り当てられる
```


### 属性の型キャスト <span id="attributes-typecasting"></span>

[[yii\db\ActiveRecord]] は、クエリの結果を投入されるときに、[データベース・テーブル・スキーマ](db-dao.md#database-schema)
からの情報を使って、自動的な型キャストを実行します。これによって、整数として宣言されているテーブルカラムから取得されるデータを
アクティブ・レコードのインスタンスでも PHP の integer として投入し、真偽値として宣言されているデータを boolean として投入することが出来るようになっています。
しかしながら、型キャストのメカニズムには、いくつかの制約があります。

* 浮動小数点数値は変換されず、文字列として表されます。そうしないと精度が失われるおそれがあるからです。
* 整数値の変換は、あなたが使っているオペレーティング・システムの整数の大きさに依存します。具体的に言うと、
  'unsigned integer' または 'big integer' として宣言されたカラムの値は、64-bit オペレーティングシステムでのみ PHP の integer に変換されます。
  32-bit オペレーティングシステムでは、文字列として表されます。

属性の型キャストは、アクティブ・レコードのインスタンスにクエリの結果から値を投入するときだけしか実行されないことに注意してください。
HTTP リクエストから値をロードしたり、プロパティにアクセスして直接に値を設定したりするときには、自動的な変換は行われません。
また、アクティブ・レコードのデータ保存のための SQL 文を準備する際にもテーブル・スキーマが使用されて、
値が正しい型でクエリにバインドされることを保証します。
しかし、アクティブ・レコードのインスタンスの属性値は保存の過程において変換されることはありません。

> Tip: アクティブ・レコードの検証や保存の際の属性型キャストを楽にするために
  [[yii\behaviors\AttributeTypecastBehavior]] を使うことが出来ます。

2.0.14 以降、Yii のアクティブ・レコードは、JSON や多次元配列のような複雑な型をサポートしています。

#### MySQL および PostgreSQL における JSON

データが取得された後、JSON カラムの値は標準的な JSON デコード規則に従って、
自動的に JSON からデコードされます。

アクティブ・レコードは、属性値を JSON カラムに保存するために [[yii\db\JsonExpression|JsonExpression]]
オブジェクトを自動的に生成します。このオブジェクトが [クエリ・ビルダ](db-query-builder.md) レベルで JSON 文字列にエンコードされます。

#### PostgreSQL における配列

データが取得された後、配列カラムの値は PgSQL 記法から自動的に [[yii\db\ArrayExpression|ArrayExpression]] オブジェクトにデコードされます。
このオブジェクトは PHP の `ArrayAccess` インタフェイスを実装しているため、これを配列として使うこと事が出来ます。また、`->getValue()` を呼んで配列そのものを取得することも出来ます。

アクティブ・レコードは、属性値を配列カラムに保存するために [[yii\db\ArrayExpression|ArrayExpression]]
オブジェクトを生成します。このオブジェクトが [クエリ・ビルダ](db-query-builder.md) のレベルで配列を表す PgSQL 文字列にエンコードされます。

JSON カラムに対して条件を使用することも出来ます。

```php
$query->andWhere(['=', 'json', new ArrayExpression(['foo' => 'bar'])
```

式を構築するシステムについて更に学習するためには [クエリ・ビルダ – 特製の条件や式を追加する](db-query-builder.md#adding-custom-conditions-and-expressions)
という記事を参照して下さい。

### 複数の行を更新する <span id="updating-multiple-rows"></span>

上述のメソッドは、すべて、個別のアクティブ・レコード・インスタンスに対して作用し、個別のテーブル行を挿入したり更新したりするものです。
複数の行を同時に更新するためには、代りに、スタティックなメソッドである [[yii\db\ActiveRecord::updateAll()|updateAll()]]
を呼ばなければなりません。

```php
// UPDATE `customer` SET `status` = 1 WHERE `email` LIKE `%@example.com`
Customer::updateAll(['status' => Customer::STATUS_ACTIVE], ['like', 'email', '@example.com']);
```

同様に、[[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] を呼んで、
複数の行のカウンタカラムを同時に更新することが出来ます。

```php
// UPDATE `customer` SET `age` = `age` + 1
Customer::updateAllCounters(['age' => 1]);
```


## データを削除する <span id="deleting-data"></span>

一行のデータを削除するためには、最初にその行に対応するアクティブ・レコード・インスタンスを取得して、
次に [[yii\db\ActiveRecord::delete()]] メソッドを呼びます。

```php
$customer = Customer::findOne(123);
$customer->delete();
```

[[yii\db\ActiveRecord::deleteAll()]] を呼んで、複数またはすべてのデータ行を削除することが出来ます。例えば、

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> Note: [[yii\db\ActiveRecord::deleteAll()|deleteAll()]] を呼ぶときは、十分に注意深くしてください。
  なぜなら、条件の指定を間違うと、あなたのテーブルからすべてのデータを完全に消し去ってしまうことになるからです。


## アクティブ・レコードのライフサイクル <span id="ar-life-cycles"></span>

アクティブ・レコードがさまざまな目的で使用される場合のそれぞれのライフサイクルを理解しておくことは重要なことです。
それぞれのライフサイクルにおいては、特定の一続きのメソッドが呼び出されます。
そして、これらのメソッドをオーバーライドして、ライフサイクルをカスタマイズするチャンスを得ることが出来ます。
また、ライフサイクルの中でトリガされる特定のアクティブ・レコード・イベントに反応して、あなたのカスタム・コードを挿入することも出来ます。
これらのイベントが特に役に立つのは、アクティブ・レコードのライフサイクルをカスタマイズする必要のあるアクティブ・レコード・[ビヘイビア](concept-behaviors.md) を開発する際です。

次に、さまざまなアクティブ・レコードのライフサイクルと、
そのライフサイクルに含まれるメソッドやイベントを要約します。


### 新しいインスタンスのライフサイクル <span id="new-instance-life-cycle"></span>

`new` 演算子によって新しいアクティブ・レコード・インスタンスを作成する場合は、次のライフサイクルを経ます。

1. クラスのコンストラクタ。
2. [[yii\db\ActiveRecord::init()|init()]]: [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] イベントをトリガ。


### データをクエリする際のライフサイクル <span id="querying-data-life-cycle"></span>

[クエリ・メソッド](#querying-data) のどれか一つによってデータをクエリする場合は、
新しくデータを投入されるアクティブ・レコードは次のライフサイクルを経ます。

1. クラスのコンストラクタ。
2. [[yii\db\ActiveRecord::init()|init()]]: [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] イベントをトリガ。
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] イベントをトリガ。


### データを保存する際のライフサイクル <span id="saving-data-life-cycle"></span>

[[yii\db\ActiveRecord::save()|save()]] を呼んでアクティブ・レコード・インスタンスを挿入または更新する場合は、
次のライフサイクルを経ます。

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] イベントをトリガ。
   このメソッドが `false` を返すか、[[yii\base\ModelEvent::isValid]] が `false` であった場合、
   残りのステップはスキップされる。
2. データ検証を実行。データ検証が失敗した場合、3 より後のステップはスキップされる。
3. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]]
   イベントをトリガ。
4. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]]
   または [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]] イベントをトリガ。
   このメソッドが `false` を返すか、[[yii\base\ModelEvent::isValid]] が `false` であった場合、
   残りのステップはスキップされる。
5. 実際のデータの挿入または更新を実行。
6. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]
    または [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]]
    イベントをトリガ。
   

### データを削除する際のライフサイクル <span id="deleting-data-life-cycle"></span>

[[yii\db\ActiveRecord::delete()|delete()]] を呼んでアクティブ・レコード・インスタンスを削除する際は、
次のライフサイクルを経ます。

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]]
   イベントをトリガ。このメソッドが `false` を返すか、[[yii\base\ModelEvent::isValid]] が `false` であった場合は、
   残りのステップはスキップされる。
2. 実際のデータの削除を実行。
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]]
   イベントをトリガ。


> Note: 次のメソッドを呼んだ場合は、いずれの場合も、上記のライフサイクルのどれかを開始させることはありません。
> これらのメソッドは、レコード単位ではなく、データベース上で直接に動作するためです。
>
> - [[yii\db\ActiveRecord::updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()]] 

### データをリフレッシュする際のライフサイクル <span id="refreshing-data-life-cycle"></span>

[[yii\db\ActiveRecord::refresh()|refresh()]] を呼んでアクティブ・レコード・インスタンスをリフレッシュする際は、リフレッシュが成功してメソッドが `true` を返すと
[[yii\db\ActiveRecord::EVENT_AFTER_REFRESH|EVENT_AFTER_REFRESH]] イベントがトリガされます。


## トランザクションを扱う <span id="transactional-operations"></span>

アクティブ・レコードを扱う際には、二つの方法で [トランザクション](db-dao.md#performing-transactions) を処理することができます。

最初の方法は、次に示すように、アクティブ・レコードのメソッドの呼び出しを明示的にトランザクションのブロックで囲む方法です。

```php
$customer = Customer::findOne(123);

Customer::getDb()->transaction(function($db) use ($customer) {
    $customer->id = 200;
    $customer->save();
    // ... 他の DB 操作 ...
});

// あるいは、別の方法

$transaction = Customer::getDb()->beginTransaction();
try {
    $customer->id = 200;
    $customer->save();
    // ... 他の DB 操作 ...
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch(\Throwable $e) {
    $transaction->rollBack();
    throw $e;
}
```

> Note: 上記のコードでは、PHP 5.x と PHP 7.x との互換性のために、二つの catch ブロックを持っています。
> `\Exception` は PHP 7.0 以降では、[`\Throwable` インタフェイス](https://www.php.net/manual/ja/class.throwable.php) を実装しています。
> 従って、あなたのアプリケーションが PHP 7.0 以上しか使わない場合は、`\Exception` の部分を省略することが出来ます。

第二の方法は、トランザクションのサポートが必要な DB 操作を [[yii\db\ActiveRecord::transactions()]]
メソッドに列挙するという方法です。

```php
class Post extends \yii\db\ActiveRecord
{
    public function transactions()
    {
        return [
            'admin' => self::OP_INSERT,
            'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            // 上は次と等価
            // 'api' => self::OP_ALL,
        ];
    }
}
```

[[yii\db\ActiveRecord::transactions()]] メソッドが返す配列では、キーは [シナリオ](structure-models.md#scenarios) の名前であり、
値はトランザクションで囲まれるべき操作でなくてはなりません。
いろいろな DB 操作を参照するのには、次の定数を使わなければなりません。

* [[yii\db\ActiveRecord::OP_INSERT|OP_INSERT]]: [[yii\db\ActiveRecord::insert()|insert()]] によって実行される挿入の操作。
* [[yii\db\ActiveRecord::OP_UPDATE|OP_UPDATE]]: [[yii\db\ActiveRecord::update()|update()]] によって実行される更新の操作。
* [[yii\db\ActiveRecord::OP_DELETE|OP_DELETE]]: [[yii\db\ActiveRecord::delete()|delete()]] によって実行される削除の操作。

複数の操作を示すためには、`|` を使って上記の定数を連結してください。
ショートカット定数 [[yii\db\ActiveRecord::OP_ALL|OP_ALL]] を使って、上記の三つの操作すべてを示すことも出来ます。

このメソッドを使って生成されたトランザクションは、[[yii\db\ActiveRecord::beforeSave()|beforeSave()]] を呼ぶ前に開始され、
[[yii\db\ActiveRecord::afterSave()|afterSave()]] を実行した後にコミットされます。

## 楽観的ロック <span id="optimistic-locks"></span>

楽観的ロックは、一つのデータ行が複数のユーザによって更新されるときに発生しうる衝突を回避するための方法です。
例えば、ユーザ A と ユーザ B が 同時に同じ wiki 記事を編集しており、ユーザ A が自分の編集結果を保存した後に、
ユーザ B も自分の編集結果を保存しようとして「保存」ボタンをクリックする場合を考えてください。
ユーザ B は、実際には古くなったバージョンの記事に対する操作をしようとしていますので、彼が記事を保存するのを防止し、
彼に何らかのヒント・メッセージを表示する方法があることが望まれます。

楽観的ロックは、あるカラムを使って各行のバージョン番号を記録するという方法によって、上記の問題を解決します。
古くなったバージョン番号とともに行を保存しようとすると、[[yii\db\StaleObjectException]] 例外が投げられて、
行が保存されるのが防止されます。
楽観的ロックは、 [[yii\db\ActiveRecord::update()]] または [[yii\db\ActiveRecord::delete()]]
メソッドを使って既存の行を更新または削除しようとする場合にだけサポートされます。

楽観的ロックを使用するためには、次のようにします。

1. アクティブ・レコード・クラスと関連付けられている DB テーブルに、各行のバージョン番号を保存するカラムを作成します。
   カラムは長倍精度整数 (big integer) タイプでなければなりません (MySQL では `BIGINT DEFAULT 0` です)。
2. [[yii\db\ActiveRecord::optimisticLock()]] メソッドをオーバーライドして、このカラムの名前を返すようにします。
3. あなたのモデル・クラスの中で [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] を実装し、受信したリクエストからその値を自動的に解析できるようにします。
   [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] が検証を処理すべきですので、バージョンの属性は検証規則から削除します。
4. ユーザ入力を収集するウェブフォームに、更新されるレコードの現在のバージョン番号を保持する隠しフィールドを追加します。
5. アクティブ・レコードを使って行の更新を行うコントローラ・アクションにおいて、[[\yii\db\StaleObjectException]] 例外を捕捉して、
   衝突を解決するために必要なビジネス・ロジック (例えば、変更をマージしたり、データの陳腐化を知らせたり) を実装します。

例えば、バージョン番号のカラムが `version` と名付けられているとすると、
次のようなコードによって楽観的ロックを実装することが出来ます。

```php
// ------ ビューのコード -------

use yii\helpers\Html;

// ... 他の入力フィールド
echo Html::activeHiddenInput($model, 'version');


// ------ コントローラのコード -------

use yii\db\StaleObjectException;

public function actionUpdate($id)
{
    $model = $this->findModel($id);

    try {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    } catch (StaleObjectException $e) {
        // 衝突を解決するロジック
    }
}

// ------ モデルのコード -------

use yii\behaviors\OptimisticLockBehavior;

public function behaviors()
{
    return [
        OptimisticLockBehavior::class,
    ];
}

public function optimisticLock()
{
    return 'version';
}

```
> Note: [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] は、ユーザが正しいバージョン番号を送信したときにだけ
> レコードが保存されるという事を保証します。そして、そのために、[[\yii\web\Request::getBodyParam()|getBodyParam()]] の結果を直接に解析します。
> そこで、あなたのモデル・クラスを拡張して、親モデルで第2段階を行い、ビヘイビアのアタッチ(第3段階)を子モデルで行うようにすると便利でしょう。
> そうすれば、一方を内部使用のためだけのインスタンスとして使うことが出来、他方をエンド・ユーザの入力の受信に責任を持つモデルとしてコントローラと結びつける事が出来ます。
> もう一つのやり方としては、[[\yii\behaviors\OptimisticLockBehavior::$value|value]] プロパティを構成して独自のロジックを実装することも可能です。


## リレーショナル・データを扱う <span id="relational-data"></span>

個々のデータベース・テーブルを扱うだけでなく、アクティブ・レコードは関連したテーブルのデータも一緒に読み出して、
主たるデータを通して簡単にアクセス出来るようにすることが出来ます。
例えば、一人の顧客は一つまたは複数の注文を発することがあり得ますので、顧客のデータは注文のデータと関連を持っていることになります。
このリレーションが適切に宣言されていれば、`$customer->orders` という式を使って顧客の注文情報にアクセスすることが出来ます。
`$customer->orders` は、顧客の注文情報を `Order` アクティブ・レコード・インスタンスの配列として返してくれます。


### リレーションを宣言する <span id="declaring-relations"></span>

アクティブ・レコードを使ってリレーショナル・データを扱うためには、最初に、アクティブ・レコード・クラスの中でリレーションを宣言する必要があります。
これは、以下のように、関心のあるそれぞれのリレーションについて *リレーション・メソッド* を宣言するだけの簡単な作業です。

```php
class Customer extends ActiveRecord
{
    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    // ...

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
```

上記のコードでは、`Customer` クラスのために `orders` リレーションを宣言し、`Order` クラスのために `customer`
リレーションを宣言しています。

各リレーション・メソッドは `getXyz` という名前にしなければなりません。ここで `xyz` (最初の文字は小文字です) が *リレーション名* と呼ばれます。
リレーション名は *大文字と小文字を区別する* ことに注意してください。

リレーションを宣言する際には、次の情報を指定しなければなりません。

- リレーションの多重性: [[yii\db\ActiveRecord::hasMany()|hasMany()]] または [[yii\db\ActiveRecord::hasOne()|hasOne()]]
  のどちらかを呼ぶことによって指定されます。
  上記の例では、リレーションの宣言において、顧客は複数の注文を持ち得るが、一方、注文は一人の顧客しか持たない、ということが容易に読み取れます。
- 関連するアクティブ・レコード・クラスの名前: [[yii\db\ActiveRecord::hasMany()|hasMany()]] または [[yii\db\ActiveRecord::hasOne()|hasOne()]]
  の最初のパラメータとして指定されます。
  クラス名を取得するのに `Xyz::class` を呼ぶのが推奨されるプラクティスです。
  そうすれば、IDE の自動補完のサポートを得ることことが出来るだけでなく、コンパイル段階でエラーを検出することが出来ます。
- 二つの型のデータ間のリンク: 二つの型のデータの関連付けに用いられるカラムを指定します。
  配列の値は主たるデータ (リレーションを宣言しているアクティブ・レコード・クラスによって表されるデータ) のカラムであり、
  配列のキーは関連するデータのカラムです。

  これを記憶するための簡単な規則は、上の例で見るように、関連するアクティブ・レコードを書いた直後に、それに属するカラムを
  続けて書く、ということです。ご覧のように、`customer_id` は `Order` のプロパティであり、
  `id` は`Customer` のプロパティです。
  
> Warning: リレーション名としては `relation` は予約済みで使えません。これを使うと `ArgumentCountError` となります。


### リレーショナル・データにアクセスする <span id="accessing-relational-data"></span>

リレーションを宣言した後は、リレーション名を通じてリレーショナル・データにアクセスすることが出来ます。
これは、リレーション・メソッドによって定義されるオブジェクト・[プロパティ](concept-properties.md) にアクセスするのと同様です。
このため、これを *リレーション・プロパティ* と呼びます。例えば、

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
// $orders is an array of Order objects
$orders = $customer->orders;
```

> Info: `xyz` という名前のリレーションを getter メソッド `getXyz()` によって宣言すると、`xyz` を
  [オブジェクト・プロパティ](concept-properties.md) のようにアクセスすることが出来るようになります。名前は大文字と小文字を区別することに注意してください。

リレーションが [[yii\db\ActiveRecord::hasMany()|hasMany()]] によって宣言されている場合は、
このリレーション・プロパティにアクセスすると、関連付けられたアクティブ・レコード・インスタンスの配列が返されます。
リレーションが [[yii\db\ActiveRecord::hasOne()|hasOne()]] によって宣言されている場合は、
このリレーション・プロパティにアクセスすると、関連付けられたアクティブ・レコード・インスタンスか、関連付けられたデータが見つからないときは `null` が返されます。

リレーション・プロパティに最初にアクセスしたときは、上記の例で示されているように、SQL 文が実行されます。
その同じプロパティに再びアクセスしたときは、SQL 文を再実行することなく、以前の結果が返されます。
SQL 文の再実行を強制するためには、まず、リレーション・プロパティの割り当てを解除 (unset) しなければなりません :
`unset($customer->orders)`。

> Note: リレーション・プロパティの概念は [オブジェクト・プロパティ](concept-properties.md) の機能と同一であるように見えますが、一つ、重要な相違点があります。
> 通常のオブジェクト・プロパティでは、プロパティの値はそれを定義する getter メソッドと同じ型を持ちます。
> しかし、リレーション・プロパティにアクセスすると [[yii\db\ActiveRecord]] のインスタンスまたはその配列が返されるのに対して、
> リレーション・メソッドは [[yii\db\ActiveQuery]] のインスタンスを返します。
> 
> ```php
> $customer->orders; // `Order` オブジェクトの配列
> $customer->getOrders(); // ActiveQuery のインスタンス
> ```
> 
> このことは、次のセクションで説明するように、カスタマイズしたクエリを作成するのに役に立ちます。


### 動的なリレーショナル・クエリ <span id="dynamic-relational-query"></span>

リレーション・メソッドは [[yii\db\ActiveQuery]] のインスタンスを返すため、DB クエリを実行する前に、
クエリ構築メソッドを使ってこのクエリを更に修正することが出来ます。例えば、

```php
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getOrders()
    ->where(['>', 'subtotal', 200])
    ->orderBy('id')
    ->all();
```

リレーション・プロパティにアクセスする場合と違って、リレーション・メソッドによって動的なリレーショナル・クエリを実行する場合は、
同じ動的なリレーショナル・クエリが以前に実行されたことがあっても、毎回、SQL 文が実行されます。

さらに進んで、もっと簡単に動的なリレーショナル・クエリを実行できるように、リレーションの宣言をパラメータ化したい場合もあるでしょう。
例えば、`bigOrders` リレーションを下記のように宣言することが出来ます。

```php
class Customer extends ActiveRecord
{
    public function getBigOrders($threshold = 100)
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])
            ->where('subtotal > :threshold', [':threshold' => $threshold])
            ->orderBy('id');
    }
}
```

これによって、次のようなリレーショナル・クエリを実行することが出来るようになります。

```php
// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getBigOrders(200)->all();

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 100 ORDER BY `id`
$orders = $customer->bigOrders;
```


### 中間テーブルによるリレーション <span id="junction-table"></span>

データベースの設計において、二つの関連するテーブル間の多重性が多対多である場合は、通常、
[中間テーブル](https://ja.wikipedia.org/wiki/%E9%80%A3%E6%83%B3%E3%82%A8%E3%83%B3%E3%83%86%E3%82%A3%E3%83%86%E3%82%A3) が導入されます。
例えば、`order` テーブルと `item` テーブルは、`order_item` と言う名前の中間テーブルによって関連付けることが出来ます。
このようにすれば、一つの注文を複数の商品に対応させ、また、一つの商品を複数の注文に対応させることが出来ます。

このようなリレーションを宣言するときは、[[yii\db\ActiveQuery::via()|via()]] または [[yii\db\ActiveQuery::viaTable()|viaTable()]]
のどちらかを呼んで中間テーブルを指定します。
[[yii\db\ActiveQuery::via()|via()]] と [[yii\db\ActiveQuery::viaTable()|viaTable()]] の違いは、
前者が既存のリレーション名の形式で中間テーブルを指定するのに対して、後者は中間テーブルを直接に指定する、という点です。例えば、

```php
class Order extends ActiveRecord
{
    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->viaTable('order_item', ['order_id' => 'id']);
    }
}
```

あるいは、また、

```php
class Order extends ActiveRecord
{
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems');
    }
}
```

中間テーブルを使って宣言されたリレーションの使い方は、通常のリレーションと同じです。例えば、

```php
// SELECT * FROM `order` WHERE `id` = 100
$order = Order::findOne(100);

// SELECT * FROM `order_item` WHERE `order_id` = 100
// SELECT * FROM `item` WHERE `item_id` IN (...)
// 商品オブジェクトの配列を返す
$items = $order->items;
```


### 複数のテーブルを経由するリレーション定義の連鎖<span id="multi-table-relations"></span>

さらに、[[yii\db\ActiveQuery::via()|via()]] を使ってリレーション定義を連鎖させ、複数のテーブルを経由するリレーションを定義することも可能です。
上記の例で考えましょう。そこには `Customer`(顧客)、`Order`(注文) そして `Item`(品目) というクラスがあります。
`Customer` クラスに、発注された全ての注文によって購入された全ての品目を列挙するリレーションを追加して、
それに `getPurchasedItems()` という名前を付けることが出来ます。リレーション定義の連鎖が次のコード・サンプルで示されています。

```php
class Customer extends ActiveRecord
{
    // ...

    public function getPurchasedItems()
    {
        // 顧客の購入品目、すなわち、`Item` の 'id' カラムが OrderItem の 'item_id' に合致するもの
        return $this->hasMany(Item::class, ['id' => 'item_id'])
                    ->via('orderItems');
    }

    public function getOrderItems()
    {
        // 顧客の OrderItems、すなわち、`Order` の `id` カラムが `OrderItem` の 'order_id' に合致するもの
        return $this->hasMany(OrderItem::class, ['order_id' => 'id'])
                    ->via('orders');
    }

    public function getOrders()
    {
        // 顧客の注文
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```


### レイジー・ローディングとイーガー・ローディング <span id="lazy-eager-loading"></span>

[リレーショナル・データにアクセスする](#accessing-relational-data) において、通常のオブジェクト・プロパティにアクセスするのと同じようにして、
アクティブ・レコード・インスタンスのリレーション・プロパティにアクセスすることが出来ることを説明しました。
SQL 文は、リレーション・プロパティに最初にアクセスするときにだけ実行されます。
このようなリレーショナル・データのアクセス方法を *レイジー・ローディング* と呼びます。例えば、

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$orders = $customer->orders;

// SQL は実行されない
$orders2 = $customer->orders;
```

レイジー・ローディングは非常に使い勝手が良いものです。
しかし、複数のアクティブ・レコード・インスタンスの同じリレーション・プロパティにアクセスする必要がある場合は、パフォーマンスの問題を生じ得ます。
次のコードサンプルを考えて見てください。実行される SQL 文の数はいくらになるでしょう?

```php
// SELECT * FROM `customer` LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SELECT * FROM `order` WHERE `customer_id` = ...
    $orders = $customer->orders;
}
```

上のコードのコメントから判るように、実行される SQL 文は 101 にもなります。
これは、for ループの中で、異なる `Customer` オブジェクトの `orders` リレーションにアクセスするたびに、
SQL 文が一つ実行されることになるからです。

このパフォーマンスの問題を解決するために、次に示すように、いわゆる *イーガー・ローディング* の手法を使うことが出来ます。

```php
// SELECT * FROM `customer` LIMIT 100;
// SELECT * FROM `orders` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->with('orders')
    ->limit(100)
    ->all();

foreach ($customers as $customer) {
    // SQL は実行されない
    $orders = $customer->orders;
}
```

[[yii\db\ActiveQuery::with()]] を呼ぶことによって、最初の 100 人の顧客の注文をたった一つの SQL 文で返すように、アクティブ・レコードに指示をしています。
結果として、実行される SQL 文の数は 101 から 2 に減ります。

イーガー・ローディングは、一つだけでなく、複数のリレーションに対しても使うことが出来ます。さらには、*ネストされたリレーション* でさえ、イーガー・ロードすることが出来ます。
ネストされたリレーションというのは、関連するアクティブ・レコードの中で宣言されているリレーションです。
例えば、`Cutomer` が `orders` リレーションによって `Order` と関連しており、`Order` が `items` リレーションによって `Item` と関連している場合です。
`Customer` に対するクエリを実行するときに、ネストされたリレーションの記法である `orders.items` を使って、`items` をイーガー・ロードすることが出来ます。

次のコードは、[[yii\db\ActiveQuery::with()|with()]] のさまざまな使い方を示すものです。
ここでは、`Customer` クラスは `orders` と `country` という二つのリレーションを持っており、また、`Order` クラスは `items` という一つのリレーションを持っていると仮定しています。

```php
// "orders" と "country" の両方をイーガー・ロードする
$customers = Customer::find()->with('orders', 'country')->all();
// これは下の配列記法と等価
$customers = Customer::find()->with(['orders', 'country'])->all();
// SQL は実行されない
$orders= $customers[0]->orders;
// SQL は実行されない
$country = $customers[0]->country;

// "orders" リレーションと、ネストされた "orders.items" をイーガー・ロード
$customers = Customer::find()->with('orders.items')->all();
// 最初の顧客の、最初の注文の品目にアクセスする
// SQL は実行されない
$items = $customers[0]->orders[0]->items;
```

深くネストされたリレーション、たとえば `a.b.c.c` をイーガー・ロードすることも出来ます。
このとき、すべての親リレーションもイーガー・ロードされます。
つまり、`a.b.c.d` を使って [[yii\db\ActiveQuery::with()|with()]] を呼ぶと、`a`、`a.b`、`a.b.c` そして `a.b.c.d` をイーガー・ロードすることになります。

> Info: 一般化して言うと、`N` 個のリレーションのうち `M` 個のリレーションが [中間テーブル](#junction-table) によって定義されている場合、
  この `N` 個のリレーションをイーガー・ロードしようとすると、合計で `1+M+N` 個の SQL クエリが実行されます。
  ネストされたリレーション `a.b.c.d` は 4 個のリレーションとして数えられることに注意してください。

リレーションをイーガー・ロードするときに、対応するリレーショナル・クエリを無名関数を使ってカスタマイズすることが出来ます。
例えば、

```php
// 顧客を検索し、その国とアクティブな注文を同時に返す
// SELECT * FROM `customer`
// SELECT * FROM `country` WHERE `id` IN (...)
// SELECT * FROM `order` WHERE `customer_id` IN (...) AND `status` = 1
$customers = Customer::find()->with([
    'country',
    'orders' => function ($query) {
        $query->andWhere(['status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

リレーションのためのリレーショナル・クエリをカスタマイズするときは、リレーション名を配列のキーとし、対応する値に無名関数を使わなければなりません。
無名関数が受け取る `$query` パラメータは、リレーションのためのリレーショナル・クエリを実行するのに使用される
[[yii\db\ActiveQuery]] オブジェクトを表します。
上のコード例では、注文の状態に関する条件を追加して、リレーショナル・クエリを修正しています。

> Note: リレーションをイーガー・ロードするときに [[yii\db\Query::select()|select()]] を呼ぶ場合は、
> リレーションの宣言で参照されているカラムが選択されるように注意しなければなりません。
> そうしないと、リレーションのモデルが正しくロードされないことがあります。例えば、
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // この場合、$orders[0]->customer は常に `null` になります。問題を修正するためには、次のようにしなければなりません。
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### リレーションを使ってテーブルを結合する <span id="joining-with-relations"></span>

> Note: この項で説明されていることは、MySQL、PostgreSQL など、
  リレーショナル・データベースに対してのみ適用されます。

ここまで説明してきたリレーショナル・クエリは、主たるデータを検索する際に主テーブルのカラムだけを参照するものでした。
現実には、関連するテーブルのカラムを参照しなければならない場合がよくあります。
例えば、少なくとも一つのアクティブな注文を持つ顧客を取得したい、というような場合です。
この問題を解決するためには、以下のようにして、テーブルを結合するクエリを構築することが出来ます。

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id`
// WHERE `order`.`status` = 1
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->select('customer.*')
    ->leftJoin('order', '`order`.`customer_id` = `customer`.`id`')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->with('orders')
    ->all();
```

> Note: JOIN SQL 文を含むリレーショナル・クエリを構築する場合は、カラム名の曖昧さを解消することが重要です。
  カラム名に対応するテーブル名をプレフィクスするのが慣例です。

しかしながら、もっと良いのは、[[yii\db\ActiveQuery::joinWith()]] を呼んで、既にあるリレーションの宣言を利用するという手法です。

```php
$customers = Customer::find()
    ->joinWith('orders')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->all();
```

どちらの方法でも、実行される SQL 文のセットは同じです。けれども、後者の方がはるかに明快で簡潔です。

デフォルトでは、[[yii\db\ActiveQuery::joinWith()|joinWith()]] は `LEFT JOIN` を使って、関連するテーブルを主テーブルに結合します。
第三のパラメータ `$joinType` によって異なる結合タイプ (例えば `RIGHT JOIN`) を指定することが出来ます。
指定したい結合タイプが `INNER JOIN` である場合は、代りに、[[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] を呼ぶだけで済ませることが出来ます。

デフォルトでは、[[yii\db\ActiveQuery::joinWith()|joinWith()]] を呼ぶと、リレーションのデータが [イーガー・ロード](#lazy-eager-loading) されます。
リレーションのデータを読み取りたくない場合は、第二のパラメータ `$eagerLoading` を `false` に指定することが出来ます。

> Note: たとえイーガー・ローディングを有効にして [[yii\db\ActiveQuery::joinWith()|joinWith()]] や [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] を使う場合でも、
  リレーションのデータを取得するのには `JOIN` クエリの結果は**使われません**。
  その場合でも、やはり、[イーガー・ローディング](#lazy-eager-loading) のセクションで説明したように、結合されたリレーションごとに追加のクエリが実行されます。

[[yii\db\ActiveQuery::with()|with()]] と同じように、一つまたは複数のリレーションを結合したり、
リレーションクエリをその場でカスタマイズしたり、ネストされたリレーションを結合したりすることが出来ます。
また、[[yii\db\ActiveQuery::with()|with()]] と [[yii\db\ActiveQuery::joinWith()|joinWith()]] を混ぜて使用することも出来ます。例えば、

```php
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->andWhere(['>', 'subtotal', 100]);
    },
])->with('country')
    ->all();
```

二つのテーブルを結合するときに、結合クエリの `ON` の部分に追加の条件を指定する必要がある場合があるでしょう。
これは、次のように、[[yii\db\ActiveQuery::onCondition()]] メソッドを呼ぶことによって実現できます。

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id` AND `order`.`status` = 1 
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->onCondition(['order.status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

上記のクエリは *全ての* 顧客を返し、各顧客について全てのアクティブな注文を返します。
これは、少なくとも一つのアクティブな注文を持つ顧客を全て返す、という以前の例とは異なっていることに注意してください。

> Info: [[yii\db\ActiveQuery]] が [[yii\db\ActiveQuery::onCondition()|onCondition()]] によって条件を指定された場合、
  クエリが JOIN 句を含む場合は、条件は `ON` の部分に置かれます。
  クエリが JOIN 句を含まない場合は、条件は自動的に `WHERE` の部分に追加されます。
  このようにして、リレーションのテーブルのカラムを含む条件だけが `ON` の部分に置かれます。

#### リレーションのテーブルのエイリアス <span id="relation-table-aliases"></span>

前に注意したように、クエリに JOIN を使うときは、カラム名の曖昧さを解消する必要があります。そのために、テーブルにエイリアスを定義することがよくあります。
リレーションのテーブルのためにエイリアスを設定することは、リレーショナル・クエリを次のようにカスタマイズすることによっても可能です。

```php
$query->joinWith([
    'orders' => function ($q) {
        $q->from(['o' => Order::tableName()]);
    },
])
```

しかし、これでは非常に複雑ですし、リレーションオブジェクトのテーブル名をハードコーディングしたり、`Order::tableName()` を呼んだりしなければなりません。
バージョン 2.0.7 以降、Yii はこれに対するショートカットを提供しています。今では、次のようにしてリレーションのテーブルのエイリアスを定義して使うことが出来ます。

```php
// orders リレーションを JOIN し、結果を orders.id でソートする
$query->joinWith(['orders o'])->orderBy('o.id');
```

上記の文法が動作するのは単純なリレーションの場合です。ネストされたリレーションを結合する
(例えば、`$query->joinWith(['orders.product'])` ) ときに、中間テーブルのエイリアスが必要になった場合は、
次の例のように、joinWith の呼び出しをネストさせる必要があります。

```php
$query->joinWith(['orders o' => function($q) {
        $q->joinWith('product p');
    }])
    ->where('o.amount > 100');
```

### 逆リレーション <span id="inverse-relations"></span>

リレーションの宣言は、たいていの場合、二つのアクティブ・レコード・クラスの間で相互的なものになります。
例えば、`Customer` は `orders` リレーションによって `Order` に関連付けられ、逆に、`Order` は`customer` リレーションによって `Customer` に関連付けられる、という具合です。

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
```

ここで、次のコード断片について考えて見てください。

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// SELECT * FROM `customer` WHERE `id` = 123
$customer2 = $order->customer;

// "異なる" が表示される
echo $customer2 === $customer ? '同じ' : '異なる';
```

私たちは `$customer` と `$customer2` が同じであると期待しますが、そうではありません。
実際、二つは同じ顧客データを含んでいますが、オブジェクトとしては異なります。
`$order->customer` にアクセスするときに追加の SQL 文が実行されて、新しいオブジェクトである `$customer2` にデータが投入されます。

上記の例において、冗長な最後の SQL 文の実行を避けるためには、下に示すように、
[[yii\db\ActiveQuery::inverseOf()|inverseOf()]]メソッドを呼ぶことによって、`customer` が
`orders` の *逆リレーション* であることを Yii に教えておかなければなりません。

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

このようにリレーションの宣言を修正すると、次の結果を得ることが出来ます。

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// No SQL will be executed
$customer2 = $order->customer;

// "同じ" が表示される
echo $customer2 === $customer ? '同じ' : '異なる';
```

> Note: 逆リレーションは [中間テーブル](#junction-table) を含むリレーションについては宣言することが出来ません。
  つまり、リレーションが [[yii\db\ActiveQuery::via()|via()]] または [[yii\db\ActiveQuery::viaTable()|viaTable()]]
  によって定義されている場合は、[[yii\db\ActiveQuery::inverseOf()|inverseOf()]] を追加で呼んではいけません。


## リレーションを保存する <span id="saving-relations"></span>

リレーショナル・データを扱う時には、たいてい、さまざまなデータ間にリレーションを確立したり、既存のリレーションを破棄したりする必要があります。
そのためには、リレーションを定義するカラムの値を適切に設定することが必要です。
アクティブ・レコードを使う場合は、結局の所、次のようなコードを書くことになるでしょう。

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

// Order において "customer" リレーションを定義する属性の値を設定する
$order->customer_id = $customer->id;
$order->save();
```

アクティブ・レコードは、この仕事をもっと楽に達成することが出来るように、[[yii\db\ActiveRecord::link()|link()]] メソッドを提供しています。

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

$order->link('customer', $customer);
```

[[yii\db\ActiveRecord::link()|link()]] メソッドは、リレーション名と、リレーションを確立する対象の
アクティブ・レコード・インスタンスを指定することを要求します。
このメソッドは、二つのアクティブ・レコード・インスタンスをリンクする属性の値を修正して、それをデータベースに書き込みます。
上記の例では、`Order` インスタンスの `customer_id` 属性を `Customer` インスタンスの `id`
属性の値になるようにセットして、それをデータベースに保存します。

> Note: 二つの新規作成されたアクティブ・レコード・インスタンスをリンクすることは出来ません。

[[yii\db\ActiveRecord::link()|link()]] を使用することの利点は、リレーションが [中間テーブル](#junction-table)
によって定義されている場合に、さらに明白になります。
例えば、一つの `Order` インスタンスと一つの`Item` インスタンスをリンクするのに、次のコードを使うことが出来ます。

```php
$order->link('items', $item);
```

上記のコードによって、`order_item` 中間テーブルに、注文と商品を関連付けるための行が自動的に挿入されます。

> Info: [[yii\db\ActiveRecord::link()|link()]] メソッドは、
  影響を受けるアクティブ・レコード・インスタンスを保存する際に、データ検証を実行しません。
  このメソッドを呼ぶ前にすべての入力値を検証することはあなたの責任です。

[[yii\db\ActiveRecord::link()|link()]] の逆の操作が [[yii\db\ActiveRecord::unlink()|unlink()]] です。
これは、既存の二つのアクティブ・レコード・インスタンスのリレーションを破棄します。例えば、

```php
$customer = Customer::find()->with('orders')->where(['id' => 123])->one();
$customer->unlink('orders', $customer->orders[0]);
```

デフォルトでは、[[yii\db\ActiveRecord::unlink()|unlink()]] メソッドは、
既存のリレーションを指定している外部キーの値を `null` に設定します。
ただし、`$delete` パラメータを `true` にしてメソッドに渡して、その外部キーを含むテーブル行を削除するという方法を選ぶことも出来ます。

リレーションに中間テーブルが含まれている場合は、[[yii\db\ActiveRecord::unlink()|unlink()]] を呼ぶと、
中間テーブルにある外部キーがクリアされるか、または、`$delete` が `true` であるときは、
中間テーブルにある対応する行が削除されるかします。


## DBMS 間のリレーション <span id="cross-database-relations"></span> 

アクティブ・レコードは、異なるデータベースをバックエンドに持つアクティブ・レコードの間でリレーションを宣言することを可能にしています。
データベースは異なるタイプ (例えば、MySQL と PostgreSQL、または、MS SQL と MongoDB) であってもよく、別のサーバで動作していても構いません。
同じ構文を使ってリレーショナル・クエリを実行することが出来ます。例えば、

```php
// Customer はリレーショナル・データベース (例えば MySQL) の "customer" テーブルと関連付けられている
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // Customer は多くの Comment を持つ
        return $this->hasMany(Comment::class, ['customer_id' => 'id']);
    }
}

// Comment は MongoDb データベースの "comment" コレクションと関連付けられている
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // Comment は 一つの Customer を持つ
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}

$customers = Customer::find()->with('comments')->all();
```

このセクションで説明されたリレーショナル・クエリ機能のほとんどを使用することが出来ます。

> Note: [[yii\db\ActiveQuery::joinWith()]] の使用は、データベース間の JOIN クエリをサポートしているデータベースに限定されます。
  この理由により、上記の例では `joinWith` メソッドは使用することが出来ません。MongoDB は JOIN をサポートしていないからです。


## クエリ・クラスをカスタマイズする <span id="customizing-query-classes"></span>

デフォルトでは、全てのアクティブ・レコードのクエリは [[yii\db\ActiveQuery]] によってサポートされます。
カスタマイズされたクエリ・クラスをアクティブ・レコードで使用するためには、[[yii\db\ActiveRecord::find()]]
メソッドをオーバーライドして、カスタマイズされたクエリ・クラスのインスタンスを返すようにしなければなりません。例えば、
 
```php
// file Comment.php
namespace app\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}
```

このようにすると、`Comment` のクエリを実行したり (例えば `find()` や `findOne()` を呼んだり)、`Comment` とのリレーションを定義したり (例えば `hasOne()` を定義したり)
する際には、いつでも、`AcctiveQuery` の代りに `CommentQuery` のインスタンスを使用することになります。

さて、`CommentQuery` クラスを定義しなければならない訳ですが、このクラスをさまざまな創造的方法でカスタマイズして、あなたのクエリ構築作業を楽しいものにすることが出来ます。例えば、

```php
// file CommentQuery.php
namespace app\models;

use yii\db\ActiveQuery;

class CommentQuery extends ActiveQuery
{
    // デフォルトで条件を追加 (省略可)
    public function init()
    {
        $this->andOnCondition(['deleted' => false]);
        parent::init();
    }

    // ... ここにカスタマイズしたクエリ・メソッドを追加 ...

    public function active($state = true)
    {
        return $this->andOnCondition(['active' => $state]);
    }
}
```

> Note: 新しいクエリ構築メソッドを定義するときには、通常は、既存のどの条件も上書きしないように、
  [[yii\db\ActiveQuery::onCondition()|onCondition()]] ではなく、[[yii\db\ActiveQuery::andOnCondition()|andOnCondition()]]
  または [[yii\db\ActiveQuery::orOnCondition()|orOnCondition()]] を呼んで条件を追加しなければなりません。

このようにすると、次のようなクエリ構築のコードを書くことが出来るようになります。

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

> Tip: 大きなプロジェクトでは、アクティブ・レコード・クラスをクリーンに保つことが出来るように、
  クエリ関連のコードのほとんどをカスタマイズされたクエリ・クラスに保持することが推奨されます。

この新しいクエリ構築メソッドは、`Comment` に関するリレーションを定義するときや、リレーショナル・クエリを実行するときにも使用することが出来ます。

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::class, ['customer_id' => 'id'])->active();
    }
}

$customers = Customer::find()->with('activeComments')->all();

// あるいは、また
class Customer extends \yii\db\ActiveRecord
{
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['customer_id' => 'id']);
    }
}

$customers = Customer::find()->with([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

> Info: Yii 1.1 には、*スコープ* と呼ばれる概念がありました。 Yii 2.0 では、スコープはもはや直接にはサポートされません。
  同じ目的を達するためには、カスタマイズされたクエリ・クラスとクエリ・メソッドを使わなければなりません。


## 追加のフィールドを選択する

アクティブ・レコードのインスタンスにクエリ結果からデータが投入されるときは、
受け取ったデータセットのカラムの値が対応する属性に入れられます。

クエリ結果から追加のカラムや値を取得して、アクティブ・レコードの内部に格納することが出来ます。
例えば、ホテルの客室の情報を含む `room` という名前のテーブルがあるとしましょう。
そして、全ての客室のデータは `length` (長さ)、`width` (幅)、`height` (高さ) というフィールドを使って、部屋の幾何学的なサイズに関する情報を格納しているとします。
空いている全ての部屋の一覧を容積の降順で取得する必要がある場合を考えて見てください。
レコードをその値で並べ替える必要があるので、PHP を使って容積を計算することは出来ません。
しかし、同時に、一覧には `volume` (容積) も表示したいでしょう。
目的を達するためには、`Room` アクティブ・レコード・クラスにおいて追加のフィールドを宣言し、`volume` の値を格納する必要があります。

```php
class Room extends \yii\db\ActiveRecord
{
    public $volume;

    // ...
}
```

そして、部屋の容積を計算して並べ替えを実行するクエリを構築しなければなりません。

```php
$rooms = Room::find()
    ->select([
        '{{room}}.*', // 全てのカラムを選択
        '([[length]] * [[width]] * [[height]]) AS volume', // 容積を計算
    ])
    ->orderBy('volume DESC') // 並べ替えを適用
    ->all();

foreach ($rooms as $room) {
    echo $room->volume; // SQL によって計算された値を含んでいる
}
```

追加のフィールドが選択できることは、集計クエリに対して特に有効に機能します。
注文の数とともに顧客の一覧を表示する必要がある場合を想定してください。
まず初めに、`Customer` クラスの中で、`orders` リレーションと、注文数を格納するための追加のフィールドを宣言しなければなりません。

```php
class Customer extends \yii\db\ActiveRecord
{
    public $ordersCount;

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```

そして、order を結合して注文数を計算するクエリを構築することが出来ます。

```php
$customers = Customer::find()
    ->select([
        '{{customer}}.*', // 顧客の全てのフィールドを選択
        'COUNT({{order}}.id) AS ordersCount' // 注文数を計算
    ])
    ->joinWith('orders') // テーブルの結合を保証する
    ->groupBy('{{customer}}.id') // 結果をグループ化して、集計関数の動作を保証する
    ->all();
```

この方法を使うことの短所の一つは、情報が SQL クエリでロードされていない場合には、それを別途計算しなければならない、ということです。
従って、追加のセレクト文を持たない通常のクエリによって特定のレコードを読み出した場合には、追加のフィールドの実際の値を返すことは不可能になります。
同じことが新しく保存されたレコードでも起こります。

```php
$room = new Room();
$room->length = 100;
$room->width = 50;
$room->height = 2;

$room->volume; // まだ指定されていないため、この値は `null` になります。
```

[[yii\db\BaseActiveRecord::__get()|__get()]] と [[yii\db\BaseActiveRecord::__set()|__set()]] のマジック・メソッドを使用すれば、
プロパティの動作をエミュレートすることが出来ます。

```php
class Room extends \yii\db\ActiveRecord
{
    private $_volume;
    
    public function setVolume($volume)
    {
        $this->_volume = (float) $volume;
    }
    
    public function getVolume()
    {
        if (empty($this->length) || empty($this->width) || empty($this->height)) {
            return null;
        }
        
        if ($this->_volume === null) {
            $this->setVolume(
                $this->length * $this->width * $this->height
            );
        }
        
        return $this->_volume;
    }

    // ...
}
```

このようにすると、SELECT クエリによって容積が提供されていない場合に、
モデルの他の属性を使って容積を自動的に計算することが出来ます。

集約フィールドも、同じように、定義されたリレーションを使って計算することが出来ます。

```php
class Customer extends \yii\db\ActiveRecord
{
    private $_ordersCount;

    public function setOrdersCount($count)
    {
        $this->_ordersCount = (int) $count;
    }

    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; // プライマリ・キーが null の場合のリレーショナル・クエリを防止
        }
        
        if ($this->_ordersCount === null) {
            $this->setOrdersCount(count($this->orders)); // 要求に応じてリレーションから集約を計算
        }

        return $this->_ordersCount;
    }

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```

このコードでは、'ordersCount' が 'select' 文に存在する場合は、`Customer::ordersCount` はクエリの結果によって投入されます。
そうでない場合は、要求に応じて、`Customer::orders` リレーションを使って計算されます。

マジック・メソッドによってプロパティをエミュレートする手法は、ある種のリレーショナル・データ、特に集約のショートカットを作成するためにも使用することが出来ます。
例えば、

```php
class Customer extends \yii\db\ActiveRecord
{
    /**
     * 読み出し専用の集約データの仮想プロパティを定義
     */
    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; // プライマリ・キーが null の場合のリレーショナル・クエリを防止
        }
        
        return empty($this->ordersAggregation) ? 0 : $this->ordersAggregation[0]['counted'];
    }

    /**
     * 通常の 'orders' リレーションを宣言
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

    /**
     * 集約データを提供する新しいリレーションを 'orders' を元にして宣言
     */
    public function getOrdersAggregation()
    {
        return $this->getOrders()
            ->select(['customer_id', 'counted' => 'count(*)'])
            ->groupBy('customer_id')
            ->asArray(true);
    }

    // ...
}

foreach (Customer::find()->with('ordersAggregation')->all() as $customer) {
    echo $customer->ordersCount; // イーガー・ローディングにより、追加のクエリなしに、リレーションから集約データを出力
}

$customer = Customer::findOne($pk);
$customer->ordersCount; // レイジーロードされたリレーションから集約データを出力
```
