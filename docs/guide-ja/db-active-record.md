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
* Microsoft SQL Server 2008 以降: [[yii\db\ActiveRecord]] による。
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

> Note|注意: 自明なことですが、カラム名が直接にアクティブレコードクラスの属性名になりますので、データベースの命名スキーマでアンダースコアを使用している場合はアンダースコアを持つ属性名になります。
> 例えば、`user_name` というカラムは、アクティブレコードのオブジェクトでは `$user->user_name` としてアクセスされることになります。
> コードスタイルが気になるのであれば、データベースの命名スキーマも camelCase を使用しなければなりません。
> しかしながら、camelCase の使用は要求されてはいません。Yii は他のどのような命名スタイルでも十分に動作します。


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
  コードの中で、ハードコードされた文字列や数字ではなく、意味が分かる名前の定数を使用することは良いプラクティスです。


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
   リレーションをテーブルの外部キーに基づいて定義するのが望ましいプラクティスです。

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


中間テーブルを使うリレーション
------------------------------

場合によっては、二つのテーブルが [中間テーブル][] と呼ばれる中間的なテーブルによって関連付けられていることがあります。
そのようなリレーションを宣言するために、[[yii\db\ActiveQuery::via()|via()]] または [[yii\db\ActiveQuery::viaTable()|viaTable()]] メソッドを呼んで、[[yii\db\ActiveQuery]] オブジェクトをカスタマイズすることが出来ます。

例えば、テーブル `order` とテーブル `item` が中間テーブル `order_item` によって関連付けられている場合、`Order` クラスにおいて `items` リレーションを次のように宣言することが出来ます。

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

[中間テーブル]: https://en.wikipedia.org/wiki/Junction_table "Junction table on Wikipedia"


レイジーローディングとイーガーローディング
------------------------------------------

前に述べたように、関連オブジェクトに最初にアクセスしたときに、アクティブレコードは DB クエリを実行して関連データを読み出し、それを関連オブジェクトに投入します。
同じ関連オブジェクトに再度アクセスしても、クエリは実行されません。
これを *レイジーローディング* と呼びます。
例えば、

```php
// 実行される SQL: SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// 実行される SQL: SELECT * FROM order WHERE customer_id=1
$orders = $customer->orders;
// SQL は実行されない
$orders2 = $customer->orders;
```

レイジーローディングは非常に使い勝手が良いものです。しかし、次のシナリオでは、パフォーマンスの問題を生じ得ます。

```php
// 実行される SQL: SELECT * FROM customer WHERE id=1
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // 実行される SQL: SELECT * FROM order WHERE customer_id=...
    $orders = $customer->orders;
    // ... $orders を処理 ...
}
```

データベースに 100 人以上の顧客が登録されていると仮定した場合、上記のコードで何個の SQL クエリが実行されるでしようか?
101 です。最初の SQL クエリが 100 人の顧客を返します。
次に、100 人の顧客全てについて、それぞれ、顧客の注文を返すための SQL クエリが実行されます。

上記のパフォーマンスの問題を解決するためには、[[yii\db\ActiveQuery::with()]] を呼んでいわゆる *イーガーローディング* を使うことが出来ます。

```php
// 実行される SQL: SELECT * FROM customer LIMIT 100;
//                 SELECT * FROM orders WHERE customer_id IN (1,2,...)
$customers = Customer::find()->limit(100)
    ->with('orders')->all();

foreach ($customers as $customer) {
    // SQL は実行されない
    $orders = $customer->orders;
    // ... $orders を処理 ...
}
```

ご覧のように、同じ仕事をするのに必要な SQL クエリがたった二つになります。

> Info|情報: 一般化して言うと、`N` 個のリレーションのうち `M` 個のリレーションが `via()` または `viaTable()` によって定義されている場合、この `N` 個のリレーションをイーガーロードしようとすると、合計で `1+M+N` 個の SQL クエリが実行されます。
> 主たるテーブルの行を返すために一つ、`via()` または `viaTable()` の呼び出しに対応する `M` 個の中間テーブルのそれぞれに対して一つずつ、そして、`N` 個の関連テーブルのそれぞれに対して一つずつ、という訳です。

> Note|注意: イーガーローディングで `select()` をカスタマイズしようとする場合は、関連モデルにリンクするカラムを必ず含めてください。
> そうしないと、関連モデルは読み出されません。例えば、

```php
$orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
// $orders[0]->customer は常に null になる。この問題を解決するためには、次のようにしなければならない。
$orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
```

場合によっては、リレーショナルクエリをその場でカスタマイズしたいことがあるでしょう。
これは、レイジーローディングでもイーガーローディングでも、可能です。例えば、

```php
$customer = Customer::findOne(1);
// レイジーローディング: SELECT * FROM order WHERE customer_id=1 AND subtotal>100
$orders = $customer->getOrders()->where('subtotal>100')->all();

// イーガーローディング: SELECT * FROM customer LIMIT 100
//                       SELECT * FROM order WHERE customer_id IN (1,2,...) AND subtotal>100
$customers = Customer::find()->limit(100)->with([
    'orders' => function($query) {
        $query->andWhere('subtotal>100');
    },
])->all();
```


逆リレーション
--------------

リレーションは、たいていの場合、ペアで定義することが出来ます。
例えば、`Customer` が `orders` という名前のリレーションを持ち、`Order` が `customer` という名前のリレーションを持つ、ということがあります。

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

次に例示するクエリを実行すると、注文 (order) のリレーションとして取得した顧客 (customer) が、最初にその注文をリレーションとして取得した顧客とは別の Customer オブジェクトになってしまうことに気付くでしょう。
また、`customer->orders` にアクセスすると一個の SQL が実行され、`order->customer` にアクセスするともう一つ別の SQL が実行されるということにも気付くでしょう。

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// "等しくない" がエコーされる
// SELECT * FROM order WHERE customer_id=1
// SELECT * FROM customer WHERE id=1
if ($customer->orders[0]->customer === $customer) {
    echo '等しい';
} else {
    echo '等しくない';
}
```

冗長な最後の SQL 文の実行を避けるためには、次のように、[[yii\db\ActiveQuery::inverseOf()|inverseOf()]] メソッドを呼んで、`customer` と `oerders` のリレーションに対して逆リレーションを宣言することが出来ます。

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

こうすると、上記と同じクエリを実行したときに、次の結果を得ることが出来ます。

```php
// SELECT * FROM customer WHERE id=1
$customer = Customer::findOne(1);
// "等しい" がエコーされる
// SELECT * FROM order WHERE customer_id=1
if ($customer->orders[0]->customer === $customer) {
    echo '等しい';
} else {
    echo '等しくない';
}
```

上記では、レイジーローディングにおいて逆リレーションを使う方法を示しました。
逆リレーションはイーガーローディングにも適用されます。

```php
// SELECT * FROM customer
// SELECT * FROM order WHERE customer_id IN (1, 2, ...)
$customers = Customer::find()->with('orders')->all();
// "等しい" がエコーされる
if ($customers[0]->orders[0]->customer === $customers[0]) {
    echo '等しい';
} else {
    echo '等しくない';
}
```

> Note|注意: 逆リレーションはピボットテーブルを含むリレーションに対しては定義することが出来ません。
> つまり、リレーションが [[yii\db\ActiveQuery::via()|via()]] または [[yii\db\ActiveQuery::viaTable()|viaTable()]] によって定義されている場合は、[[yii\db\ActiveQuery::inverseOf()]] を追加で呼ぶことは出来ません。


リレーションを使ってテーブルを結合する <a name="joining-with-relations">
--------------------------------------

リレーショナルデータベースを扱う場合、複数のテーブルを結合して、JOIN SQL 文にさまざまなクエリ条件とパラメータを指定することは、ごく当り前の仕事です。
その目的を達するために、[[yii\db\ActiveQuery::join()]] を明示的に呼んで JOIN クエリを構築する代りに、既存のリレーション定義を再利用して [[yii\db\ActiveQuery::joinWith()]] を呼ぶことが出来ます。
例えば、

```php
// 全ての注文を検索して、注文を顧客 ID と注文 ID でソートする。同時に "customer" をイーガーロードする。
$orders = Order::find()->joinWith('customer')->orderBy('customer.id, order.id')->all();
// 書籍を含む全ての注文を検索し、"books" をイーガーロードする。
$orders = Order::find()->innerJoinWith('books')->all();
```

上記において、[[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] メソッドは、結合タイプを `INNER JOIN` とする [[yii\db\ActiveQuery::joinWith()|joinWith()]] へのショートカットです。

一個または複数のリレーションを結合することが出来ます。リレーションにクエリ条件をその場で適用することも出来ます。
また、サブリレーションを結合することも出来ます。例えば、

```php
// 複数のリレーションを結合
// 書籍を含む注文で、過去 24 時間以内に登録した顧客によって発行された注文を検索する
$orders = Order::find()->innerJoinWith([
    'books',
    'customer' => function ($query) {
        $query->where('customer.created_at > ' . (time() - 24 * 3600));
    }
])->all();
// サブリレーションとの結合: 書籍および書籍の著者を結合
$orders = Order::find()->joinWith('books.author')->all();
```

舞台裏では、Yii は最初に JOIN SQL 文を実行して、その JOIN SQL に適用された条件を満たす主たるモデルを取得します。
そして、次にリレーションごとのクエリを実行して、対応する関連レコードを投入します。

[[yii\db\ActiveQuery::joinWith()|joinWith()]] と [[yii\db\ActiveQuery::with()|with()]] の違いは、前者が主たるモデルクラスのテーブルと関連モデルクラスのテーブルを結合して主たるモデルを読み出すのに対して、後者は主たるモデルクラスのテーブルに対してだけクエリを実行して主たるモデルを読み出す、という点にあります。

この違いによって、[[yii\db\ActiveQuery::joinWith()|joinWith()]] では、JOIN SQL 文だけに指定できるクエリ条件を適用することが出来ます。
例えば、上記の例のように、関連モデルに対する条件によって主たるモデルをフィルタすることが出来ます。
主たるモデルを関連テーブルのカラムを使って並び替えることも出来ます。

[[yii\db\ActiveQuery::joinWith()|joinWith()]] を使うときは、カラム名の曖昧さを解決することについて、あなたが責任を負わなければなりません。
上記の例では、order テーブルと item テーブルがともに `id` という名前のカラムを持っているため、`item.id` と `order.id` を使って、`id` カラムの参照の曖昧さを解決しています。

既定では、リレーションを結合すると、リレーションがイーガーロードされることにもなります。
この既定の動作は、指定されたリレーションをイーガーロードするかどうかを規定する `$eagerLoading` パラメータを渡して、変更することが出来ます。

また、既定では、[[yii\db\ActiveQuery::joinWith()|joinWith()]] は関連テーブルを結合するのに `LEFT JOIN` を使います。
結合タイプをカスタマイズするために `$joinType` パラメータを渡すことが出来ます。
`INNER JOIN` タイプのためのショートカットとして、[[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]] を使うことが出来ます。

下記に、いくつかの例を追加します。

```php
// 書籍を含む注文を全て検索するが、"books" はイーガーロードしない。
$orders = Order::find()->innerJoinWith('books', false)->all();
// これも上と等値
$orders = Order::find()->joinWith('books', false, 'INNER JOIN')->all();
```

二つのテーブルを結合するとき、場合によっては、JOIN クエリの ON の部分で何らかの追加条件を指定する必要があります。
これは、次のように、[[yii\db\ActiveQuery::onCondition()]] メソッドを呼んで実現することが出来ます。

```php
class User extends ActiveRecord
{
    public function getBooks()
    {
        return $this->hasMany(Item::className(), ['owner_id' => 'id'])->onCondition(['category_id' => 1]);
    }
}
```

上記においては、[[yii\db\ActiveRecord::hasMany()|hasMany()]] メソッドが [[yii\db\ActiveQuery]] のインスタンスを返しています。
そして、それに対して [[yii\db\ActiveQuery::onCondition()|onCondition()]] が呼ばれて、`category_id` が 1 である品目だけが返されるべきことを指定しています。

[[yii\db\ActiveQuery::joinWith()|joinWith()]] を使ってクエリを実行すると、指定された ON 条件が対応する JOIN クエリの ON の部分に挿入されます。
例えば、

```php
// SELECT user.* FROM user LEFT JOIN item ON item.owner_id=user.id AND category_id=1
// SELECT * FROM item WHERE owner_id IN (...) AND category_id=1
$users = User::find()->joinWith('books')->all();
```

[[yii\db\ActiveQuery::with()]] を使ってイーガーロードする場合や、レイジーロードする場合には、JOIN クエリは使われないため、ON 条件が対応する SQL 文の WHERE の部分に挿入されることに注意してください。
例えば、

```php
// SELECT * FROM user WHERE id=10
$user = User::findOne(10);
// SELECT * FROM item WHERE owner_id=10 AND category_id=1
$books = $user->books;
```


関連付けを扱う
--------------

アクティブレコードは、二つのアクティブレコードオブジェクト間の関連付けを確立および破棄するために、次の二つのメソッドを提供しています。

- [[yii\db\ActiveRecord::link()|link()]]
- [[yii\db\ActiveRecord::unlink()|unlink()]]

例えば、顧客と新しい注文があると仮定したとき、次のコードを使って、その注文をその顧客のものとすることが出来ます。

```php
$customer = Customer::findOne(1);
$order = new Order();
$order->subtotal = 100;
$customer->link('orders', $order);
```

上記の [[yii\db\ActiveRecord::link()|link()]] の呼び出しは、注文の `customer_id` に `$customer` のプライマリキーの値を設定し、[[yii\db\ActiveRecord::save()|save()]] を呼んで注文をデータベースに保存します。


DBMS 間のリレーション
---------------------

アクティブレコードは、異なる DBMS に属するエンティティ間、例えば、リレーショナルデータベースのテーブルと MongoDB のコレクションの間に、リレーションを確立することを可能にしています。
そのようなリレーションでも、何も特別なコードは必要ありません。

```php
// リレーショナルデータベースのアクティブレコード
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // リレーショナルデータベースに保存されている Customer は、MongoDB コレクションに保存されている複数の Comment を持つ
        return $this->hasMany(Comment::className(), ['customer_id' => 'id']);
    }
}

// MongoDb のアクティブレコード
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // MongoDB コレクションに保存されている Comment は、リレーショナルデータベースに保存されている一つの Customer を持つ
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }
}
```

アクティブレコードの全ての機能、例えば、イーガーローディングやレイジーローディング、関連付けの確立や破棄などが、DBMS 間のリレーションでも利用可能です。

> Note|注意: DBMS ごとのアクティブレコードの実装には、DBMS 固有のメソッドや機能が含まれる場合があり、そういうものは DBMS 間のリレーションには適用できないということを忘れないでください。
  例えば、[[yii\db\ActiveQuery::joinWith()]] の使用が MongoDB コレクションに対するリレーションでは動作しないことは明白です。


スコープ
--------

[[yii\db\ActiveRecord::find()|find()]] または [[yii\db\ActiveRecord::findBySql()|findBySql()]] を呼ぶと、[[yii\db\ActiveQuery|ActiveQuery]] のインスタンスが返されます。
そして、追加のクエリメソッド、例えば、[[yii\db\ActiveQuery::where()|where()]] や [[yii\db\ActiveQuery::orderBy()|orderBy()]] を呼んで、クエリ条件をさらに指定することが出来ます。

別々の場所で同じ一連のクエリメソッドを呼びたいということがあり得ます。
そのような場合には、いわゆる *スコープ* を定義することを検討すべきです。
スコープは、本質的には、カスタムクエリクラスの中で定義されたメソッドであり、クエリオブジェクトを修正する一連のメソッドを呼ぶものです。
スコープを定義しておくと、通常のクエリメソッドを呼ぶ代りに、スコープを使うことが出来るようになります。

スコープを定義するためには二つのステップが必要です。
最初に、モデルのためのカスタムクエリクラスを作成して、このクラスの中に必要なスコープメソッドを定義します。
例えば、`Comment` モデルのために `CommentQuery` クラスを作成して、次のように、`active()` というスコープメソッドを定義します。

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

重要な点は、以下の通りです。

1. クラスは `yii\db\ActiveQuery` (または、`yii\mongodb\ActiveQuery` などの、その他の `ActiveQuery`) を拡張したものにしなければなりません。
2. メソッドは `public` で、メソッドチェーンが出来るように `$this` を返さなければなりません。メソッドはパラメータを取ることが出来ます。
3. クエリ条件を修正する方法については、[[yii\db\ActiveQuery]] のメソッド群を参照するのが非常に役に立ちます。

次に、[[yii\db\ActiveRecord::find()]] をオーバーライドして、通常の [[yii\db\ActiveQuery|ActiveQuery]] の代りに、カスタムクエリクラスを使うようにします。
上記の例のためには、次のコードを書く必要があります。

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

以上です。これで、カスタムスコープメソッドを使用することが出来ます。

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

リレーションを定義するときにもスコープを使用することが出来ます。例えば、

```php
class Post extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::className(), ['post_id' => 'id'])->active();

    }
}
```

または、リレーショナルクエリを実行するときに、その場でスコープを使うことも出来ます。

```php
$posts = Post::find()->with([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

### デフォルトスコープ

あなたが Yii 1.1 を前に使ったことがあれば、*デフォルトスコープ* と呼ばれる概念を知っているかも知れません。
デフォルトスコープは、全てのクエリに適用されるスコープです。
デフォルトスコープは、[[yii\db\ActiveRecord::find()]] をオーバライドすることによって、簡単に定義することが出来ます。
例えば、

```php
public static function find()
{
    return parent::find()->where(['deleted' => false]);
}
```

ただし、すべてのクエリにおいて、デフォルトの条件を上書きしないために、[[yii\db\ActiveQuery::where()|where()]] を使わず、[[yii\db\ActiveQuery::andWhere()|andWhere()]] または [[yii\db\ActiveQuery::orWhere()|orWhere()]] を使うべきであることに注意してください。


トランザクション操作
--------------------

アクティブレコードを扱う際には、二つの方法でトランザクション操作を処理することができます。
最初の方法は、"[データベースの基礎](db-dao.md)" の「トランザクション」の項で説明したように、全てを手作業でやる方法です。
もう一つの方法として、`transactions` メソッドを実装して、モデルのシナリオごとに、どの操作をトランザクションで囲むかを指定することが出来ます。

```php
class Post extends \yii\db\ActiveRecord
{
    public function transactions()
    {
        return [
            'admin' => self::OP_INSERT,
            'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            // 上は次と等値
            // 'api' => self::OP_ALL,
        ];
    }
}
```

上記において、`admin` と `api` はモデルのシナリオであり、`OP_` で始まる定数は、これらのシナリオについてトランザクションで囲まれるべき操作を示しています。
サポートされている操作は、`OP_INSERT`、`OP_UPDATE`、そして、`OP_DELETE` です。
`OP_ALL` は三つ全てを示します。

このような自動的なトランザクションは、`beforeSave`、`afterSave`、`beforeDelete`、`afterDelete` によってデータベースに追加の変更を加えており、本体の変更と追加の変更の両方が成功した場合にだけデータベースにコミットしたい、というときに取り分けて有用です。

楽観的ロック
------------

楽観的ロックは、複数のユーザが編集のために同一のレコードにアクセスすることを許容しつつ、発生しうる衝突を回避するものです。
例えば、ユーザが (別のユーザが先にデータを修正したために) 陳腐化したデータに対してレコードの保存を試みた場合は、[[\yii\db\StaleObjectException]] 例外が投げられて、更新または削除はスキップされます。

楽観的ロックは、`update()` と `delete()` メソッドだけでサポートされ、既定では使用されません。

楽観的ロックを使用するためには、

1. 各行のバージョン番号を保存するカラムを作成します。カラムのタイプは `BIGINT DEFAULT 0` でなければなりません。
   `optimisticLock()` メソッドをオーバーライドして、このカラムの名前を返すようにします。
2. ユーザ入力を収集するウェブフォームに、更新されるレコードのロックバージョンを保持する隠しフィールドを追加します。
3. データ更新を行うコントローラアクションにおいて、[[\yii\db\StaleObjectException]] 例外を捕捉して、衝突を解決するために必要なビジネスロジック (例えば、変更をマージしたり、データの陳腐化を知らせたり) を実装します。

ダーティな属性
--------------

属性は、データベースからロードされた後、または最後のデータ保存の後に値が変更されると、ダーティであると見なされます。
そして、`save()`、`update()`、`insert()` などを呼んでレコードデータを保存するときは、ダーティな属性だけがデータベースに保存されます。
ダーティな属性が無い場合は、保存すべきものは無いことになり、クエリは何も発行されません。

参照
----

以下も参照してください。

- [モデル](structure-models.md)
- [[yii\db\ActiveRecord]]
