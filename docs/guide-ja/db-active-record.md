アクティブレコード
==================

> Note|注意: この節はまだ執筆中です。

[アクティブレコード](http://ja.wikipedia.org/wiki/Active_Record) は、データベースに保存されているデータにアクセスするために、オブジェクト指向のインタフェイスを提供するものです。
アクティブレコードクラスはデータベーステーブルと関連付けられて、アクティブレコードのインスタンスがそのテーブルの行に対応し、アクティブレコードのインスタンスの属性がその行のカラムの値を表現します。
生の SQL 文を書く代りに、アクティブレコードを使って、オブジェクト指向の流儀でデータベーステーブルのデータを操作することが出来ます。

例えば、`Customer` が `customer` テーブルに関連付けられたアクティブレコードクラスであり、`name` が `customer` テーブルのカラムであると仮定しましょう。
`customer` テーブルに新しい行を挿入するために次のコードを書くことが出来ます。

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

上記のコードは、次のように生の SQL 文を使うのと等価なものですが、生の SQL 文の方は、直感的でなく、間違いも生じやすく、また、DBMS の違いによる互換性の問題も生じ得ます。

```php
$db->createCommand('INSERT INTO customer (name) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

下記が、現在 Yii のアクティブレコードによってサポートされているデータベースのリストです。

* MySQL 4.1 以降: [[yii\db\ActiveRecord]] による。
* PostgreSQL 7.3 以降: [[yii\db\ActiveRecord]] による。
* SQLite 2 および 3: [[yii\db\ActiveRecord]] による。
* Microsoft SQL Server 2010 以降: [[yii\db\ActiveRecord]] による。
* Oracle: [[yii\db\ActiveRecord]] による。
* CUBRID 9.3 以降: [[yii\db\ActiveRecord]] による。(cubrid PDO 拡張の [バグ](http://jira.cubrid.org/browse/APIS-658)
  のために、値を引用符で囲む機能が動作しません。そのため、サーバだけでなくクライアントも CUBRID 9.3 が必要になります)
* Sphnix: [[yii\sphinx\ActiveRecord]] による。`yii2-sphinx` エクステンションが必要。
* ElasticSearch: [[yii\elasticsearch\ActiveRecord]] による。`yii2-elasticsearch` エクステンションが必要。
* Redis 2.6.12 以降: [[yii\redis\ActiveRecord]] による。`yii2-redis` エクステンションが必要。
* MongoDB 1.3.0 以降: [[yii\mongodb\ActiveRecord]] による。`yii2-mongodb` エクステンションが必要。

ご覧のように、Yii はリレーショナルデータベースだけでなく NoSQL データベースに対してもアクティブレコードのサポートを提供しています。
このチュートリアルでは、主としてリレーショナルデータベースのためのアクティブレコードの使用方法を説明します。
しかし、ここで説明するほとんどの内容は NoSQL データベースのためのアクティブレコードにも適用することが出来るものです。


アクティブレコードクラスを宣言する
----------------------------------

アクティブレコードクラスを宣言するためには、[[yii\db\ActiveRecord]] を拡張して、クラスと関連付けられるデータベーステーブルの名前を返す `tableName` メソッドを実装する必要があります。

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DELETED = 'deleted';
    
    /**
     * @return string アクティブレコードクラスと関連付けられるデータベーステーブルの名前
     */
    public static function tableName()
    {
        return 'customer';
    }
}
```


カラムのデータにアクセスする
----------------------------

アクティブレコードは、対応するデータベーステーブルの行の各カラムをアクティブレコードオブジェクトの属性に割り付けます。
属性は通常のオブジェクトのパブリックなプロパティと同様の振る舞いをします。
属性の名前は対応するから無名と同じであり、大文字と小文字を区別します。

カラムの値を読み出すために、次の構文を使用することが出来ます。

```php
// "id" と "email" は、$customer アクティブレコードオブジェクトと関連付けられたテーブルのカラム名
$id = $customer->id;
$email = $customer->email;
```

カラムの値を変更するためには、関連付けられたプロパティに新しい値を代入して、オブジェクトを保存します。

```php
$customer->email = 'jane@example.com';
$customer->save();
```


データベースに接続する
----------------------

アクティブレコードは、データベースとの間でデータを交換するために [[yii\db\Connection|DB 接続]] を使用します。
既定では、アクティブレコードは `db` [アプリケーションコンポーネント](structure-application-components.md) を接続として使用します。
[データベースの基礎](db-dao.md) で説明したように、次のようにして、アプリケーションの構成情報ファイルの中で `db` コンポーネントを構成することが出来ます。

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

アプリケーションの中で複数のデータベースを使っており、アクティブレコードクラスのために異なる DB 接続を使いたい場合は、[[yii\db\ActiveRecord::getDb()|getDb()]] メソッドをオーバーライドすることが出来ます。

```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        return \Yii::$app->db2;  // "db2" アプリケーションコンポーネントを使用
    }
}
```


データベースにデータを問い合わせる
----------------------------------

アクティブレコードは、DB クエリを構築してアクティブレコードインスタンスにデータを投入するために、二つの入力メソッドを提供しています。

 - [[yii\db\ActiveRecord::find()]]
 - [[yii\db\ActiveRecord::findBySql()]]

この二つのメソッドは [[yii\db\ActiveQuery]] のインスタンスを返します。
 [[yii\db\ActiveQuery]] は [[yii\db\Query]] を拡張したものであり、従って、[[yii\db\Query]] と同じ一連の柔軟かつ強力な DB クエリ構築メソッド、例えば、`where()`、`join()`、`orderBy()` 等を提供します。
下記の例は、いくつかの可能性を示すものです。

```php
// *アクティブ* な顧客を全て読み出して、その ID によって並べ替える
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// ID が 1 である一人の顧客を返す
$customer = Customer::find()
    ->where(['id' => 1])
    ->one();

// *アクティブ* な顧客の数を返す
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// 結果を顧客 ID によってインデックスする
$customers = Customer::find()->indexBy('id')->all();
// $customers 配列は顧客 ID によってインデックスされる

// 生の SQL 文を使って顧客を読み出す
$sql = 'SELECT * FROM customer';
$customers = Customer::findBySql($sql)->all();
```

> Tip|ヒント: 上記のコードでは、`Customer::STATUS_ACTIVE` は `Customer` で定義されている定数です。
  コードの中で、ハードコードされた文字列や数字ではなく、意味が分かる名前の定数を使用することは良い慣行です。


プライマリキーの値または一連のカラムの値に合致するアクティブレコードのインスタンスを返すためのショートカットメソッドが二つ提供されています。
すなわち、`findOne()` と `findAll()` です。
前者は合致する最初のインスタンスを返し、後者は合致する全てのインスタンスを返します。
例えば、

```php
// ID が 1 である顧客を一人返す
$customer = Customer::findOne(1);

// ID が 1 である *アクティブ* な顧客を一人返す
$customer = Customer::findOne([
    'id' => 1,
    'status' => Customer::STATUS_ACTIVE,
]);

// ID が 1、2、または 3 である顧客を全て返す
$customers = Customer::findAll([1, 2, 3]);

// 状態が「削除済み」である顧客を全て返す
$customer = Customer::findAll([
    'status' => Customer::STATUS_DELETED,
]);
```

> Note: デフォルトでは、`findOne()` も `one()` も、クエリに `LIMIT 1` を追加しません。
  クエリが一つだけまたは少数の行のデータしか返さないことが分かっている場合 (例えば、プライマリキーか何かでクエリをする場合) は、これで十分であり、また、この方が望ましいでしょう。
  しかし、クエリが多数の行のデータを返す可能性がある場合は、パフォーマンスを向上させるために `limit(1)` を呼ぶべきです。
  例えば、`Customer::find()->where(['status' => Customer::STATUS_ACTIVE])->limit(1)->one()` のように。


### データを配列に読み出す

大量のデータを処理する場合には、メモリ使用量を節約するために、データベースから取得したデータを配列に保持したいこともあるでしょう。
これは、`asArray()` を呼ぶことによって実現できます。

```php
// 顧客を `Customer` オブジェクトでなく配列の形式で返す
$customers = Customer::find()
    ->asArray()
    ->all();
// $customers の各要素は、「名前-値」のペアの配列
```

このメソッドはメモリを節約してパフォーマンスを向上させますが、低い抽象レイヤに向って一歩を踏み出すものであり、アクティブレコードのレイヤが持ついくつかの機能を失うことになるという点に注意してください。
`asArray` を使ってデータを読み出すことは、[クエリビルダ](db-dao.md) を使って普通のクエリを実行するのと、ほとんど同じことです。
`asArray` を使うと、結果は、型変換の実行を伴わない単純な配列になります。
その結果、アクティブレコードオブジェクトでアクセスする場合には整数になるフィールドが、文字列の値を含むことがあり得ます。

### データをバッチモードで読み出す

[クエリビルダ](db-query-builder.md) において、大量のデータをデータベースから検索する場合に、メモリ使用量を最小化するために *バッチクエリ* を使うことが出来るということを説明しました。
おなじテクニックをアクティブレコードでも使うことが出来ます。
例えば、

```php
// 一度に 10 人の顧客を読み出す
foreach (Customer::find()->batch(10) as $customers) {
    // $customers は 10 以下の Customer オブジェクトの配列
}
// 一度に 10 人の顧客を読み出して、一人ずつ反復する
foreach (Customer::find()->each(10) as $customer) {
    // $customer は Customer オブジェクト
}
// いーがーローディングをするバッチクエリ
foreach (Customer::find()->with('orders')->each() as $customer) {
}
```


データベースのデータを操作する
------------------------------

アクティブレコードは、一つのアクティブレコードインスタンスに関連付けられたテーブルの一行を挿入、更新または削除するために、次のメソッドを提供しています。

- [[yii\db\ActiveRecord::save()|save()]]
- [[yii\db\ActiveRecord::insert()|insert()]]
- [[yii\db\ActiveRecord::update()|update()]]
- [[yii\db\ActiveRecord::delete()|delete()]]

アクティブレコードは、アクティブレコードクラスと関連付けられたテーブル全体に適用する、次の静的なメソッドを提供しています。
これらのメソッドはテーブル全体に影響を与えますので、使用するときはこの上なく注意深くしなければなりません。
例えば、`deleteAll()` はテーブルの全ての行を削除します。

- [[yii\db\ActiveRecord::updateCounters()|updateCounters()]]
- [[yii\db\ActiveRecord::updateAll()|updateAll()]]
- [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]]
- [[yii\db\ActiveRecord::deleteAll()|deleteAll()]]


次の例は、これらのメソッドの使用方法を示すものです。

```php
// 新しい customer のレコードを挿入する
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();  // $customer->insert() と等値

// 既存の customer のレコードを更新する
$customer = Customer::findOne($id);
$customer->email = 'james@example.com';
$customer->save();  // $customer->update() と等値

// 既存の customer のレコードを削除する
$customer = Customer::findOne($id);
$customer->delete();

// いくつかの customer のレコードを削除する
Customer::deleteAll('age > :age AND gender = :gender', [':age' => 20, ':gender' => 'M']);

// すべてのレコードの年齢に 1 を追加する
Customer::updateAllCounters(['age' => 1]);
```

> Info|情報: `save()` メソッドは、アクティブレコードインスタンスが新しいものであるか否かに従って、`insert()` または `update()` を呼びます
   (内部的には、[[yii\db\ActiveRecord::isNewRecord]] の値をチェックして判断します)。
  アクティブレコードのインスタンスが `new` 演算子によって作成された場合は、`save()` を呼ぶと、テーブルに新しい行が挿入されます。
  データベースから読み出されたアクティブレコードに対して `save()` を呼ぶと、テーブルの中の対応する行が更新されます。


### データの入力と検証

アクティブレコードは [[yii\base\Model]] を拡張したものですので、[モデル](structure-models.md) で説明したのと同じデータ入力と検証の機能をサポートしています。
例えば、[[yii\base\Model::rules()|rules()]] メソッドをオーバーライドして検証規則を宣言することが出来ます。
アクティブレコードインスタンスにユーザの入力データを一括代入することも出来ます。
また、[[yii\base\Model::validate()|validate()]] を呼んで、データ検証を実行させることも出来ます。

`save()`、`insert()` または `update()` を呼ぶと、これらのメソッドが自動的に [[yii\base\Model::validate()|validate()]] を呼びます。
検証が失敗すると、対応するデータ保存操作はキャンセルされます。

次の例は、アクティブレコードを使ってユーザ入力を収集/検証してデータベースに保存する方法を示すものです。

```php
// 新しいレコードを作成する
$model = new Customer;
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    // ユーザ入力が収集、検証されて、保存された
}

// プライマリキーが $id であるレコードを更新する
$model = Customer::findOne($id);
if ($model === null) {
    throw new NotFoundHttpException;
}
if ($model->load(Yii::$app->request->post()) && $model->save()) {
    // ユーザ入力が収集、検証されて、保存された
}
```


### デフォルト値を読み出す

テーブルのカラムの定義は、デフォルト値を含むことが出来ます。
アクティブレコードのためのウェブフォームに、このデフォルト値を事前に代入しておきたい場合があるでしょう。
そうするためには、フォームを表示する前に、[[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]] を呼びます。

```php
$customer = new Customer();
$customer->loadDefaultValues();
// ... $customer の HTML フォームを表示する ...
```

属性に対して何かの初期値を自分自身で設定したい場合は、アクティブレコードクラスの `init()` メソッドをオーバーライドして、そこで値を設定することが出来ます。
例えば、`status` 属性のデフォルト値を設定したい場合は、

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_ACTIVE;
}
```

アクティブレコードのライフサイクル
----------------------------------

アクティブレコードがデータベースのデータの操作に使われるときのライフサイクルを理解しておくことは重要なことです。
そのライフサイクルは、概して、対応するイベントと関連付けられており、それらのイベントに対して干渉したり反応したりするコードを注入できるようになっています。
これらのイベントは特にアクティブレコードの [ビヘイビア](concept-behaviors.md) を開発するときに役に立ちます。

アクティブレコードの新しいインスタンスを作成する場合は、次のライフサイクルを経ます。

1. コンストラクタ
2. [[yii\db\ActiveRecord::init()|init()]]: [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] イベントをトリガ

[[yii\db\ActiveRecord::find()|find()]] メソッドによってデータを検索する場合は、新しくデータを投入されるアクティブレコードの全てが、それぞれ、次のライフサイクルを経ます。

1. コンストラクタ
2. [[yii\db\ActiveRecord::init()|init()]]: [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]] イベントをトリガ
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]] イベントをトリガ

[[yii\db\ActiveRecord::save()|save()]] を呼んで、アクティブレコードを挿入または更新する場合は、次のライフサイクルを経ます。

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] イベントをトリガ
2. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]] イベントをトリガ
3. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] または [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]] イベントをトリガ
4. 実際のデータ挿入または更新を実行
5. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] または [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]] イベントをトリガ

最後に、[[yii\db\ActiveRecord::delete()|delete()]] を呼んで、アクティブレコードを削除する場合は、次のライフサイクルを経ます。

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]] イベントをトリガ
2. 実際のデータ削除を実行
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]] イベントをトリガ


リレーショナルデータを扱う
--------------------------

テーブルのリレーショナルデータもアクティブレコードを使ってクエリすることが出来ます
(すなわち、テーブル A のデータを選択すると、テーブル B の関連付けられたデータも一緒に取り込むことが出来ます)。
アクティブレコードのおかげで、返されるリレーショナルデータは、プライマリテーブルと関連付けられたアクティブレコードオブジェクトのプロパティのようにアクセスすることが出来ます。

例えば、適切なリレーションが宣言されていれば、`$customer->orders` にアクセスすることによって、指定された顧客が発行した注文を表す `Order` オブジェクトの配列を取得することが出来ます。

リレーションを宣言するためには、[[yii\db\ActiveQuery]] オブジェクトを返すゲッターメソッドを定義します。そして、その [[yii\db\ActiveQuery]] オブジェクトは、リレーションのコンテキストに関する情報を持ち、従って関連するレコードだけをクエリするものとします。
例えば、

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        // Customer は Order.customer_id -> id によって、複数の Order を持つ
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends \yii\db\ActiveRecord
{
    public function getCustomer()
    {
        // Order は Customer.id -> customer_id によって、一つの Customer を持つ
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

上記の例で使用されている [[yii\db\ActiveRecord::hasMany()]] と [[yii\db\ActiveRecord::hasOne()]] のメソッドは、リレーショナルデータベースにおける多対一と一対一の関係を表現するために使われます。
例えば、顧客 (customer) は複数の注文 (order) を持ち、注文 (order) は一つの顧客 (customer)を持つ、という関係です。
これらのメソッドはともに二つのパラメータを取り、[[yii\db\ActiveQuery]] オブジェクトを返します。

 - `$class`: 関連するモデルのクラス名。これは完全修飾のクラス名でなければなりません。
 - `$link`: 二つのテーブルに属するカラム間の関係。これは配列として与えられなければなりません。
   配列のキーは、`$class` と関連付けられるテーブルにあるカラムの名前であり、配列の値はリレーションを宣言しているクラスのテーブルにあるカラムの名前です。
   リレーションをテーブルの外部キーに基づいて定義するのが望ましい慣行です。

リレーションを宣言した後は、リレーショナルデータを取得することは、対応するゲッターメソッドで定義されているコンポーネントのプロパティを取得するのと同じように、とても簡単なことになります。

```php
// 顧客の注文を取得する
$customer = Customer::findOne(1);
$orders = $customer->orders;  // $orders は Order オブジェクトの配列
```

舞台裏では、上記のコードは、各行について一つずつ、次の二つの SQL クエリを実行します。

```sql
SELECT * FROM customer WHERE id=1;
SELECT * FROM order WHERE customer_id=1;
```

> Tip|情報: `$customer->orders` という式に再びアクセスした場合は、第二の SQL クエリはもう実行されません。
  第二の SQL クエリは、この式が最初にアクセスされた時だけ実行されます。
  二度目以降のアクセスでは、内部的にキャッシュされている以前に読み出した結果が返されるだけです。
  リレーショナルデータを再クエリしたい場合は、単純に、まず既存の式を未設定状態に戻して (`unset($customer->orders);`) から、再度、`$customer->orders` にアクセスします。

場合によっては、リレーショナルクエリにパラメータを渡したいことがあります。
例えば、顧客の注文を全て返す代りに、小計が指定した金額を超える大きな注文だけを返したいことがあるでしょう。
そうするためには、次のようなゲッターメソッドで `bigOrders` リレーションを宣言します。

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getBigOrders($threshold = 100)
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])
            ->where('subtotal > :threshold', [':threshold' => $threshold])
            ->orderBy('id');
    }
}
```

`hasMany()` が 返す [[yii\db\ActiveQuery]] は、[[yii\db\ActiveQuery]] のメソッドを呼ぶことでクエリをカスタマイズ出来るものであることを覚えておいてください。

上記の宣言によって、`$customer->bigOrders` にアクセスした場合は、小計が 100 以上である注文だけが返されることになります。
異なる閾値を指定するためには、次のコードを使用します。

```php
$orders = $customer->getBigOrders(200)->all();
```

> Note|注意: リレーションメソッドは [[yii\db\ActiveQuery]] のインスタンスを返します。
リレーションを属性 (すなわち、クラスのプロパティ) としてアクセスした場合は、返り値はリレーションのクエリ結果となります。
クエリ結果は、リレーションが複数のレコードを返すものか否かに応じて、[[yii\db\ActiveRecord]] の一つのインスタンス、またはその配列、または null となります。
例えば、`$customer->getOrders()` は `ActiveQuery` のインスタンスを返し、`$customer->orders` は `Order` オブジェクトの配列 (またはクエリ結果が無い場合は空の配列) を返します。


連結テーブルを使うリレーション
------------------------------

場合によっては、二つのテーブルが [連結テーブル][] と呼ばれる中間的なテーブルによって関連付けられていることがあります。
そのようなリレーションを宣言するために、[[yii\db\ActiveQuery::via()|via()]] または [[yii\db\ActiveQuery::viaTable()|viaTable()]] メソッドを呼んで、[[yii\db\ActiveQuery]] オブジェクトをカスタマイズすることが出来ます。

例えば、テーブル `order` とテーブル `item` が連結テーブル `order_item` によって関連付けられている場合、`Order` クラスにおいて `items` リレーションを次のように宣言することが出来ます。

```php
class Order extends \yii\db\ActiveRecord
{
    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaTable('order_item', ['order_id' => 'id']);
    }
}
```

[[yii\db\ActiveQuery::via()|via()]] メソッドは、最初のパラメータとして、結合テーブルの名前ではなく、アクティブレコードクラスで宣言されているリレーションの名前を取ること以外は、[[yii\db\ActiveQuery::viaTable()|viaTable()]] と同じです。
例えば、上記の `items` リレーションは次のように宣言しても等値です。

```php
class Order extends \yii\db\ActiveRecord
{
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems');
    }
}
```

[連結テーブル]: https://en.wikipedia.org/wiki/Junction_table "Junction table on Wikipedia"


Lazy and Eager Loading
----------------------

As described earlier, when you access the related objects for the first time, ActiveRecord will perform a DB query
to retrieve the corresponding data and populate it into the related objects. No query will be performed
if you access the same related objects again. We call this *lazy loading*. For example,

```php
// SQL executed: SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// SQL executed: SELECT * FROM order WHERE customer_id=1
$orders = $customer->orders;
// no SQL executed
$orders2 = $customer->orders;
```

Lazy loading is very convenient to use. However, it may suffer from a performance issue in the following scenario:

```php
// SQL executed: SELECT * FROM customer LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SQL executed: SELECT * FROM order WHERE customer_id=...
    $orders = $customer->orders;
    // ...handle $orders...
}
```

How many SQL queries will be performed in the above code, assuming there are more than 100 customers in
the database? 101! The first SQL query brings back 100 customers. Then for each customer, a SQL query
is performed to bring back the orders of that customer.

To solve the above performance problem, you can use the so-called *eager loading* approach by calling [[yii\db\ActiveQuery::with()]]:

```php
// SQL executed: SELECT * FROM customer LIMIT 100;
//               SELECT * FROM orders WHERE customer_id IN (1,2,...)
$customers = Customer::find()->limit(100)
    ->with('orders')->all();

foreach ($customers as $customer) {
    // no SQL executed
    $orders = $customer->orders;
    // ...handle $orders...
}
```

As you can see, only two SQL queries are needed for the same task!

> Info: In general, if you are eager loading `N` relations among which `M` relations are defined with `via()` or `viaTable()`,
> a total number of `1+M+N` SQL queries will be performed: one query to bring back the rows for the primary table, one for
> each of the `M` junction tables corresponding to the `via()` or `viaTable()` calls, and one for each of the `N` related tables.

> Note: When you are customizing `select()` with eager loading, make sure you include the columns that link
> the related models. Otherwise, the related models will not be loaded. For example,

```php
$orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
// $orders[0]->customer is always null. To fix the problem, you should do the following:
$orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
```

Sometimes, you may want to customize the relational queries on the fly. This can be
done for both lazy loading and eager loading. For example,

```php
$customer = Customer::findOne(1);
// lazy loading: SELECT * FROM order WHERE customer_id=1 AND subtotal>100
$orders = $customer->getOrders()->where('subtotal>100')->all();

// eager loading: SELECT * FROM customer LIMIT 100
//                SELECT * FROM order WHERE customer_id IN (1,2,...) AND subtotal>100
$customers = Customer::find()->limit(100)->with([
    'orders' => function($query) {
        $query->andWhere('subtotal>100');
    },
])->all();
```


Inverse Relations
-----------------

Relations can often be defined in pairs. For example, `Customer` may have a relation named `orders` while `Order` may have a relation
named `customer`:

```php
class Customer extends ActiveRecord
{
    ....
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    ....
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

If we perform the following query, we would find that the `customer` of an order is not the same customer object
that finds those orders, and accessing `customer->orders` will trigger one SQL execution while accessing
the `customer` of an order will trigger another SQL execution:

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// echoes "not equal"
// SELECT * FROM order WHERE customer_id=1
// SELECT * FROM customer WHERE id=1
if ($customer->orders[0]->customer === $customer) {
    echo 'equal';
} else {
    echo 'not equal';
}
```

To avoid the redundant execution of the last SQL statement, we could declare the inverse relations for the `customer`
and the `orders` relations by calling the [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] method, like the following:

```php
class Customer extends ActiveRecord
{
    ....
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

Now if we execute the same query as shown above, we would get:

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// echoes "equal"
// SELECT * FROM order WHERE customer_id=1
if ($customer->orders[0]->customer === $customer) {
    echo 'equal';
} else {
    echo 'not equal';
}
```

In the above, we have shown how to use inverse relations in lazy loading. Inverse relations also apply in
eager loading:

```php
// SELECT * FROM customer
// SELECT * FROM order WHERE customer_id IN (1, 2, ...)
$customers = Customer::find()->with('orders')->all();
// echoes "equal"
if ($customers[0]->orders[0]->customer === $customers[0]) {
    echo 'equal';
} else {
    echo 'not equal';
}
```

> Note: Inverse relation cannot be defined with a relation that involves pivoting tables.
> That is, if your relation is defined with [[yii\db\ActiveQuery::via()|via()]] or [[yii\db\ActiveQuery::viaTable()|viaTable()]],
> you cannot call [[yii\db\ActiveQuery::inverseOf()]] further.


Joining with Relations
----------------------

When working with relational databases, a common task is to join multiple tables and apply various
query conditions and parameters to the JOIN SQL statement. Instead of calling [[yii\db\ActiveQuery::join()]]
explicitly to build up the JOIN query, you may reuse the existing relation definitions and call
[[yii\db\ActiveQuery::joinWith()]] to achieve this goal. For example,

```php
// find all orders and sort the orders by the customer id and the order id. also eager loading "customer"
$orders = Order::find()->joinWith('customer')->orderBy('customer.id, order.id')->all();
// find all orders that contain books, and eager loading "books"
$orders = Order::find()->innerJoinWith('books')->all();
```

In the above, the method [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] is a shortcut to [[yii\db\ActiveQuery::joinWith()|joinWith()]]
with the join type set as `INNER JOIN`.

You may join with one or multiple relations; you may apply query conditions to the relations on-the-fly;
and you may also join with sub-relations. For example,

```php
// join with multiple relations
// find the orders that contain books and were placed by customers who registered within the past 24 hours
$orders = Order::find()->innerJoinWith([
    'books',
    'customer' => function ($query) {
        $query->where('customer.created_at > ' . (time() - 24 * 3600));
    }
])->all();
// join with sub-relations: join with books and books' authors
$orders = Order::find()->joinWith('books.author')->all();
```

Behind the scenes, Yii will first execute a JOIN SQL statement to bring back the primary models
satisfying the conditions applied to the JOIN SQL. It will then execute a query for each relation
and populate the corresponding related records.

The difference between [[yii\db\ActiveQuery::joinWith()|joinWith()]] and [[yii\db\ActiveQuery::with()|with()]] is that
the former joins the tables for the primary model class and the related model classes to retrieve
the primary models, while the latter just queries against the table for the primary model class to
retrieve the primary models.

Because of this difference, you may apply query conditions that are only available to a JOIN SQL statement.
For example, you may filter the primary models by the conditions on the related models, like the example
above. You may also sort the primary models using columns from the related tables.

When using [[yii\db\ActiveQuery::joinWith()|joinWith()]], you are responsible to disambiguate column names.
In the above examples, we use `item.id` and `order.id` to disambiguate the `id` column references
because both of the order table and the item table contain a column named `id`.

By default, when you join with a relation, the relation will also be eagerly loaded. You may change this behavior
by passing the `$eagerLoading` parameter which specifies whether to eager load the specified relations.

And also by default, [[yii\db\ActiveQuery::joinWith()|joinWith()]] uses `LEFT JOIN` to join the related tables.
You may pass it with the `$joinType` parameter to customize the join type. As a shortcut to the `INNER JOIN` type,
you may use [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]].

Below are some more examples,

```php
// find all orders that contain books, but do not eager load "books".
$orders = Order::find()->innerJoinWith('books', false)->all();
// which is equivalent to the above
$orders = Order::find()->joinWith('books', false, 'INNER JOIN')->all();
```

Sometimes when joining two tables, you may need to specify some extra condition in the ON part of the JOIN query.
This can be done by calling the [[yii\db\ActiveQuery::onCondition()]] method like the following:

```php
class User extends ActiveRecord
{
    public function getBooks()
    {
        return $this->hasMany(Item::className(), ['owner_id' => 'id'])->onCondition(['category_id' => 1]);
    }
}
```

In the above, the [[yii\db\ActiveRecord::hasMany()|hasMany()]] method returns an [[yii\db\ActiveQuery]] instance,
upon which [[yii\db\ActiveQuery::onCondition()|onCondition()]] is called
to specify that only items whose `category_id` is 1 should be returned.

When you perform a query using [[yii\db\ActiveQuery::joinWith()|joinWith()]], the ON condition will be put in the ON part
of the corresponding JOIN query. For example,

```php
// SELECT user.* FROM user LEFT JOIN item ON item.owner_id=user.id AND category_id=1
// SELECT * FROM item WHERE owner_id IN (...) AND category_id=1
$users = User::find()->joinWith('books')->all();
```

Note that if you use eager loading via [[yii\db\ActiveQuery::with()]] or lazy loading, the on-condition will be put
in the WHERE part of the corresponding SQL statement, because there is no JOIN query involved. For example,

```php
// SELECT * FROM user WHERE id=10
$user = User::findOne(10);
// SELECT * FROM item WHERE owner_id=10 AND category_id=1
$books = $user->books;
```


Working with Relationships
--------------------------

ActiveRecord provides the following two methods for establishing and breaking a
relationship between two ActiveRecord objects:

- [[yii\db\ActiveRecord::link()|link()]]
- [[yii\db\ActiveRecord::unlink()|unlink()]]

For example, given a customer and a new order, we can use the following code to make the
order owned by the customer:

```php
$customer = Customer::findOne(1);
$order = new Order();
$order->subtotal = 100;
$customer->link('orders', $order);
```

The [[yii\db\ActiveRecord::link()|link()]] call above will set the `customer_id` of the order to be the primary key
value of `$customer` and then call [[yii\db\ActiveRecord::save()|save()]] to save the order into the database.


Cross-DBMS Relations
--------------------

ActiveRecord allows you to establish relationships between entities from different DBMS. For example: between a relational database table and MongoDB collection. Such a relation does not require any special code:

```php
// Relational database Active Record
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // Customer, stored in relational database, has many Comments, stored in MongoDB collection:
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
    }
}

// MongoDb Active Record
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // Comment, stored in MongoDB collection, has one Customer, stored in relational database:
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

All Active Record features like eager and lazy loading, establishing and breaking a relationship and so on, are
available for cross-DBMS relations.

> Note: do not forget Active Record solutions for different DBMS may have specific methods and features, which may not be
  applied for cross-DBMS relations. For example: usage of [[yii\db\ActiveQuery::joinWith()]] will obviously not work with
  relation to the MongoDB collection.


Scopes
------

When you call [[yii\db\ActiveRecord::find()|find()]] or [[yii\db\ActiveRecord::findBySql()|findBySql()]], it returns an
[[yii\db\ActiveQuery|ActiveQuery]] instance.
You may call additional query methods, such as [[yii\db\ActiveQuery::where()|where()]], [[yii\db\ActiveQuery::orderBy()|orderBy()]],
to further specify the query conditions.

It is possible that you may want to call the same set of query methods in different places. If this is the case,
you should consider defining the so-called *scopes*. A scope is essentially a method defined in a custom query class that calls a set of query methods to modify the query object. You can then use a scope instead of calling a normal query method.

Two steps are required to define a scope. First, create a custom query class for your model and define the needed scope
methods in this class. For example, create a `CommentQuery` class for the `Comment` model and define the `active()`
scope method like the following:

```php
namespace app\models;

use yii\db\ActiveQuery;

class CommentQuery extends ActiveQuery
{
    public function active($state = true)
    {
        $this->andWhere(['active' => $state]);
        return $this;
    }
}
```

Important points are:

1. Class should extend from `yii\db\ActiveQuery` (or another `ActiveQuery` such as `yii\mongodb\ActiveQuery`).
2. A method should be `public` and should return `$this` in order to allow method chaining. It may accept parameters.
3. Check [[yii\db\ActiveQuery]] methods that are very useful for modifying query conditions.

Second, override [[yii\db\ActiveRecord::find()]] to use the custom query class instead of the regular [[yii\db\ActiveQuery|ActiveQuery]].
For the example above, you need to write the following code:

```php
namespace app\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    /**
     * @inheritdoc
     * @return CommentQuery
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}
```

That's it. Now you can use your custom scope methods:

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

You can also use scopes when defining relations. For example,

```php
class Post extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::className(), ['post_id' => 'id'])->active();

    }
}
```

Or use the scopes on-the-fly when performing a relational query:

```php
$posts = Post::find()->with([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

### Default Scope

If you used Yii 1.1 before, you may know a concept called *default scope*. A default scope is a scope that
applies to ALL queries. You can define a default scope easily by overriding [[yii\db\ActiveRecord::find()]]. For example,

```php
public static function find()
{
    return parent::find()->where(['deleted' => false]);
}
```

Note that all your queries should then not use [[yii\db\ActiveQuery::where()|where()]] but
[[yii\db\ActiveQuery::andWhere()|andWhere()]] and [[yii\db\ActiveQuery::orWhere()|orWhere()]]
to not override the default condition.


Transactional operations
---------------------

There are two ways of dealing with transactions while working with Active Record. First way is doing everything manually
as described in the "transactions" section of "[Database basics](db-dao.md)". Another way is to implement the
`transactions` method where you can specify which operations are to be wrapped into transactions on a per model scenario:

```php
class Post extends \yii\db\ActiveRecord
{
    public function transactions()
    {
        return [
            'admin' => self::OP_INSERT,
            'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            // the above is equivalent to the following:
            // 'api' => self::OP_ALL,
        ];
    }
}
```

In the above `admin` and `api` are model scenarios and the constants starting with `OP_` are operations that should
be wrapped in transactions for these scenarios. Supported operations are `OP_INSERT`, `OP_UPDATE` and `OP_DELETE`.
`OP_ALL` stands for all three.

Such automatic transactions are especially useful if you're doing additional database changes in `beforeSave`,
`afterSave`, `beforeDelete`, `afterDelete` and want to be sure that both succeeded before they are saved.

Optimistic Locks
--------------

Optimistic locking allows multiple users to access the same record for edits and avoids
potential conflicts. For example, when a user attempts to save the record upon some staled data
(because another user has modified the data), a [[\yii\db\StaleObjectException]] exception will be thrown,
and the update or deletion is skipped.

Optimistic locking is only supported by `update()` and `delete()` methods and isn't used by default.

To use Optimistic locking:

1. Create a column to store the version number of each row. The column type should be `BIGINT DEFAULT 0`.
   Override the `optimisticLock()` method to return the name of this column.
2. In the Web form that collects the user input, add a hidden field that stores
   the lock version of the recording being updated.
3. In the controller action that does the data updating, try to catch the [[\yii\db\StaleObjectException]]
   and implement necessary business logic (e.g. merging the changes, prompting stated data)
   to resolve the conflict.

Dirty Attributes
--------------

An attribute is considered dirty if its value was modified after the model was loaded from database or since the most recent data save. When saving record data by calling `save()`, `update()`, `insert()` etc. only dirty attributes are saved into the database. If there are no dirty attributes then there is nothing to be saved so no query will be issued at all.

See also
--------

- [Model](structure-models.md)
- [[yii\db\ActiveRecord]]
