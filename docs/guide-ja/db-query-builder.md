クエリビルダとクエリ
====================

> Note|注意: この節はまだ執筆中です。

[データベースの基礎](db-dao.md) の節で説明したように、Yii は基本的なデータベースアクセスレイヤを提供します。
このデータベースアクセスレイヤは、データベースと相互作用するための低レベルな方法を提供するものです。
それが有用な状況もありますが、生の SQL を書くことは面倒くさく、間違いを生じやすいものでもあります。
これに取って代る方法の一つがクエリビルダを使用することです。
クエリビルダは、実行すべきクエリを生成するためのオブジェクト指向の手法です。

クエリビルダの典型的な使用例は以下のようなものです。

```php
$rows = (new \yii\db\Query())
    ->select('id, name')
    ->from('user')
    ->limit(10)
    ->all();

// これは下記のコードと等価

$query = (new \yii\db\Query())
    ->select('id, name')
    ->from('user')
    ->limit(10);

// コマンドを作成。$command->sql で実際の SQL を取得できる
$command = $query->createCommand();

// コマンドを実行
$rows = $command->queryAll();
```

クエリメソッド
--------------

ご覧のように、[[yii\db\Query]] が、あなたが扱わねばならない主役のオブジェクトです。
舞台裏では、`Query` は、実際には、さまざまなクエリ情報を表現する役目を負っているに過ぎません。
実際のクエリ構築のロジックは、`createCommand()` コマンドを呼んだときに、[[yii\db\QueryBuilder]] によって実行され、クエリの実行は [[yii\db\Command]] によって実行されます。

便宜上の理由から、[[yii\db\Query]] が、よく使われる一連のクエリメソッド (クエリを構築し、実行して、結果を返すメソッド) を提供しています。
例えば、

- [[yii\db\Query::all()|all()]]: クエリを構築し、実行して、全ての結果を配列として返します。
- [[yii\db\Query::one()|one()]]: 結果の最初の行を返します。
- [[yii\db\Query::column()|column()]]: 結果の最初のカラムを返します。
- [[yii\db\Query::scalar()|scalar()]]: 結果の最初の行の最初のカラムを返します。
- [[yii\db\Query::exists()|exists()]]: 何らかのクエリ結果が有るかどうかを返します。
- [[yii\db\Query::count()|count()]]: `COUNT` クエリの結果を返します。
  他の似たようなメソッドに、`sum($q)`、`average($q)`、`max($q)`、`min($q)` があり、いわゆる統計データクエリをサポートしています。
  これらのメソッドでは `$q` パラメータは必須であり、カラム名または式を取ります。


クエリを構築する
----------------

以下に、SQL 文の中のさまざまな句を組み立てる方法を説明します。
話を単純にするために、`$query` という変数を使って [[yii\db\Query]] オブジェクトを表すものとします。


### `SELECT`

基本的な `SELECT` クエリを組み立てるためには、どのテーブルからどのカラムをセレクトするかを指定する必要があります。

```php
$query->select('id, name')
    ->from('user');
```

セレクトのオプションは、上記のように、カンマで区切られた文字列で指定することも出来ますが、配列によって指定することも出来ます。
配列を使う構文は、セレクトを動的に組み立てる場合に、特に有用です。

```php
$query->select(['id', 'name'])
    ->from('user');
```

> Info|情報: `SELECT` 句が SQL 式を含む場合は、常に配列形式を使うべきです。
> これは、`CONCAT(first_name, last_name) AS full_name` のように、SQL 式がカンマを含みうるからです。
> そういう式を他のカラムと一緒に文字列の中に含めると、式がカンマによっていくつかの部分に分離されるおそれがあります。
> それはあなたの意図するところではないでしょう。

カラムを指定するときは、例えば `user.id` や `user.id AS user_id` などのように、テーブル接頭辞やカラムエイリアスを含めることが出来ます。
カラムを指定するのに配列を使っている場合は、例えば `['user_id' => 'user.id', 'user_name' => 'user.name']` のように、配列のキーを使ってカラムエイリアスを指定することも出来ます。

バージョン 2.0.1 以降では、サブクエリをカラムとしてセレクトすることも出来ます。例えば、
 
```php
$subQuery = (new Query)->select('COUNT(*)')->from('user');
$query = (new Query)->select(['id', 'count' => $subQuery])->from('post');
// $query は次の SQL を表現する
// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
```

重複行を除外して取得したい場合は、次のように、`distinct()` を呼ぶことが出来ます。

```php
$query->select('user_id')->distinct()->from('post');
```

### `FROM`

どのテーブルからデータを取得するかを指定するために `from()` を呼びます。

```php
$query->select('*')->from('user');
```

カンマ区切りの文字列または配列を使って、複数のテーブルを指定することが出来ます。
テーブル名は、スキーマ接頭辞 (例えば `'public.user'`)、 および/または、テーブルエイリアス (例えば、`'user u'`) を含んでも構いません。
テーブル名が何らかの括弧を含んでいる場合 (すなわち、テーブルがサブクエリまたは DB 式で与えられていることを意味します) を除いて、メソッドが自動的にテーブル名を引用符で囲みます。
例えば、

```php
$query->select('u.*, p.*')->from(['user u', 'post p']);
```

テーブルが配列として指定されている場合は、配列のキーをテーブルエイリアスとして使うことも出来ます。
(テーブルにエイリアスが必要でない場合は、文字列のキーを使わないでください。)
例えば、

```php
$query->select('u.*, p.*')->from(['u' => 'user', 'p' => 'post']);
```

`Query` オブジェクトを使ってサブクエリを指定することが出来ます。
この場合、対応する配列のキーがサブクエリのエイリアスとして使われます。

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');
$query->select('*')->from(['u' => $subQuery]);
```


### `WHERE`

通常、データは何らかの基準に基づいて選択されます。
クエリビルダはその基準を指定するための有用なメソッドをいくつか持っていますが、その中で最も強力なものが `where` です。
これは多様な方法で使うことが出来ます。

条件を適用するもっとも簡単な方法は文字列を使うことです。

```php
$query->where('status=:status', [':status' => $status]);
```

文字列を使うときは、文字列の結合によってクエリを作るのではなく、必ずクエリパラメータをバインドするようにしてください。
上記の手法は使っても安全ですが、下記の手法は安全ではありません。

```php
$query->where("status=$status"); // 危険!
```

`status` の値をただちにバインドするのでなく、`params` または `addParams` を使ってそうすることも出来ます。

```php
$query->where('status=:status');
$query->addParams([':status' => $status]);
```

*ハッシュ形式* を使って、複数の条件を同時に `where` にセットすることが出来ます。

```php
$query->where([
    'status' => 10,
    'type' => 2,
    'id' => [4, 8, 15, 16, 23, 42],
]);
```

上記のコードは次の SQL を生成します。

```sql
WHERE (`status` = 10) AND (`type` = 2) AND (`id` IN (4, 8, 15, 16, 23, 42))
```

NULL はデータベースでは特別な値です。クエリビルダはこれを賢く処理します。例えば、

```php
$query->where(['status' => null]);
```

これは次の WHERE 句になります。

```sql
WHERE (`status` IS NULL)
```

`IS NOT NULL` が必要なときは次のように書くことが出来ます。

```php
$query->where(['not', ['col' => null]]);
```

次のように `Query` オブジェクトを使ってサブクエリを作ることも出来ます。

```php
$userQuery = (new Query)->select('id')->from('user');
$query->where(['id' => $userQuery]);
```

これは次の SQL を生成します。

```sql
WHERE `id` IN (SELECT `id` FROM `user`)
```

このメソッドを使うもう一つの方法は、`[演算子, オペランド1, オペランド2, ...]` という形式の引数を使う方法です。

演算子には、次のどれか一つを使うことが出来ます ([[yii\db\QueryInterface::where()]] も参照してください)。

- `and`: 二つのオペランドが `AND` を使って結合されます。例えば、`['and', 'id=1', 'id=2']` は `id=1 AND id=2` を生成します。
  オペランドが配列である場合は、ここで説明されている規則に従って文字列に変換されます。
  例えば、`['and', 'type=1', ['or', 'id=1', 'id=2']]` は `type=1 AND (id=1 OR id=2)` を生成します。
  このメソッドは、文字列を引用符で囲ったりエスケープしたりしません。

- `or`: 二つのオペランドが `OR` を使って結合されること以外は `and` 演算子と同じです。

- `between`: オペランド 1 はカラム名、オペランド 2 と 3 はカラムの値が属すべき範囲の開始値と終了値としなければなりません。
  例えば、`['between', 'id', 1, 10]` は `id BETWEEN 1 AND 10` を生成します。

- `not between`: 生成される条件において `BETWEEN` が `NOT BETWEEN` に置き換えられる以外は、`between` と同じです。

- `in`: オペランド 1 はカラム名または DB 式でなければなりません。
  オペランド 2 は、配列または `Query` オブジェクトのどちらかを取ることが出来ます。
  オペランド 2 が配列である場合は、その配列は、カラムまたは DB 式が該当すべき値域を表すものとされます。
  オペランド 2 が `Query` オブジェクトである場合は、サブクエリが生成されて、カラムまたは DB 式の値域として使われます。
  例えば、`['in', 'id', [1, 2, 3]]` は `id IN (1, 2, 3)` を生成します。
  このメソッドは、カラム名を適切に引用符で囲み、値域の値をエスケープします。
  `in` 演算子はまた複合カラムをもサポートしています。
  その場合、オペランド 1 はカラム名の配列とし、オペランド 2 は配列の配列、または、複合カラムの値域を表す `Query` オブジェクトでなければなりません。

- `not in`: 生成される条件において `IN` が `NOT IN` に置き換えられる以外は、`in` と同じです。

- `like`: オペランド 1 はカラム名または DB 式、オペランド 2 はカラムまたは DB 式がマッチすべき値を示す文字列または配列でなければなりません。
  例えば、`['like', 'name', 'tester']` は `name LIKE '%tester%'` を生成します。
  値域が配列として与えられた場合は、複数の `LIKE` 述語が生成されて 'AND' によって結合されます。
  例えば、`['like', 'name', ['test', 'sample']]` は `name LIKE '%test%' AND name LIKE '%sample%'` を生成します。
  さらに、オプションである三番目のオペランドによって、値の中の特殊文字をエスケープする方法を指定することも出来ます。
  このオペランド 3 は、特殊文字とそのエスケープ結果のマッピングを示す配列でなければなりません。
  このオペランドが提供されない場合は、デフォルトのエスケープマッピングが使用されます。
  `false` または空の配列を使って、値が既にエスケープ済みであり、それ以上エスケープを適用すべきでないことを示すことが出来ます。
  エスケープマッピングを使用する場合 (または第三のオペランドが与えられない場合) は、値が自動的に一組のパーセント記号によって囲まれることに注意してください。

  > Note|注意: PostgreSQL を使っている場合は、`like` の代りに、大文字と小文字を区別しない比較のための [`ilike`](http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE) を使うことも出来ます。

- `or like`: オペランド 2 が配列である場合に `LIKE` 述語が `OR` によって結合される以外は、`like` 演算子と同じです。

- `not like`: 生成される条件において `LIKE` が `NOT LIKE` に置き換えられる以外は、`like` 演算子と同じです。

- `or not like`: `NOT LIKE` 述語が `OR` によって結合される以外は、`not like` 演算子と同じです。

- `exists`: 要求される一つだけのオペランドは、サブクエリを表す [[yii\db\Query]] のインスタンスでなければなりません。
  これは `EXISTS (sub-query)` という式を構築します。

- `not exists`: `exists` 演算子と同じで、`NOT EXISTS (sub-query)` という式を構築します。

これらに加えて、どのようなものでも演算子として指定することが出来ます。

```php
$query->select('id')
    ->from('user')
    ->where(['>=', 'id', 10]);
```

これは次の結果になります。

```sql
SELECT id FROM user WHERE id >= 10;
```

条件の一部を動的に構築しようとする場合は、`andWhere()` と `orWhere()` を使うのが非常に便利です。

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);
if (!empty($search)) {
    $query->andWhere(['like', 'title', $search]);
}
```

`$search` が空でない場合は次の SQL が生成されます。

```sql
WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```

#### フィルタの条件を構築する

ユーザの入力に基づいてフィルタの条件を構築する場合、普通は、「空の入力値」は特別扱いして、フィルタではそれを無視したいものです。
例えば、ユーザ名とメールアドレスの入力欄を持つ HTML フォームがあるとします。
ユーザがユーザ名の入力欄のみに何かを入力した場合は、入力されたユーザ名だけを検索条件とするクエリを作成したいでしょう。
この目的を達するために `filterWhere()` メソッドを使うことが出来ます。

```php
// $username と $email はユーザの入力による
$query->filterWhere([
    'username' => $username,
    'email' => $email,
]);
```

`filterWhere()` メソッドは `where()` と非常によく似ています。
主な相違点は、`filterWhere()` は与えられた条件から空の値を削除する、ということです。
従って、`$email` が「空」である場合は、結果として生成されるクエリは `...WHERE username=:username` となります。
そして、`$username` と `$email` が両方とも「空」である場合は、クエリは `WHERE` の部分を持ちません。

値が *空* であるのは、null、空文字列、空白文字だけの文字列、または、空配列である場合です。

フィルタの条件を追加するために、`andFilterWhere()` と `orFilterWhere()` を使うことも出来ます。


### `ORDER BY`

結果を並び替えるために `orderBy` と `addOrderBy` を使うことが出来ます。

```php
$query->orderBy([
    'id' => SORT_ASC,
    'name' => SORT_DESC,
]);
```

ここでは `id` の昇順、`name` の降順で並べ替えています。

### `GROUP BY` と `HAVING`

生成される SQL に `GROUP BY` を追加するためには、次のようにすることが出来ます。

```php
$query->groupBy('id, status');
```

`groupBy` を使った後に別のフィールドを追加したい場合は、

```php
$query->addGroupBy(['created_at', 'updated_at']);
```

`HAVING` 条件を追加したい場合は、それに対応する `having` メソッドおよび `andHaving` と `orHaving` を使うことが出来ます。
これらのメソッドのパラメータは、`where` メソッドグループのそれと同様です。

```php
$query->having(['status' => $status]);
```

### `LIMIT` と `OFFSET`

結果を 10 行に限定したいときは、`limit` を使うことが出来ます。

```php
$query->limit(10);
```

最初の 100 行をスキップしたい時は、こうします。

```php
$query->offset(100);
```

### `JOIN`

適切な結合メソッドを使って、クエリビルダで `JOIN` 句を生成することが出来ます。

- `innerJoin()`
- `leftJoin()`
- `rightJoin()`

次の左外部結合では、二つの関連テーブルから一つのクエリでデータを取得しています。

```php
$query->select(['user.name AS author', 'post.title as title'])
    ->from('user')
    ->leftJoin('post', 'post.user_id = user.id');
```

このコードにおいて、`leftJoin()` メソッドの最初のパラメータは、結合するテーブルを指定するものです。
第二のパラメータは、結合の条件を定義しています。

データベース製品がその他の結合タイプをサポートしている場合は、汎用の `join` メソッドによってそれを使うことが出来ます。

```php
$query->join('FULL OUTER JOIN', 'post', 'post.user_id = user.id');
```

最初のパラメータが実行する結合タイプです。第二は結合するテーブル、第三は結合の条件です。

`FROM` と同様に、サブクエリを結合することも出来ます。
そのためには、一つの要素を持つ配列としてサブクエリを指定します。
配列の値はサブクエリを表す `Query` オブジェクトとし、配列のキーはサブクエリのエイリアスとしなければなりません。
例えば、

```php
$query->leftJoin(['u' => $subQuery], 'u.id=author_id');
```


### `UNION`

SQL における `UNION` は、一つのクエリの結果を別のクエリの結果に追加するものです。
両方のクエリによって返されるカラムが一致していなければなりません。
Yii においてこれを構築するためには、最初に二つのクエリオブジェクトを作成し、次に `union` メソッドを使って連結します。

```php
$query = new Query();
$query->select("id, category_id as type, name")->from('post')->limit(10);

$anotherQuery = new Query();
$anotherQuery->select('id, type, name')->from('user')->limit(10);

$query->union($anotherQuery);
```


バッチクエリ
------------

大量のデータを扱う場合は、[[yii\db\Query::all()]] のようなメソッドは適していません。
なぜなら、それらのメソッドは、全てのデータをメモリ上に読み込むことを必要とするためです。
必要なメモリ量を低く抑えるために、Yii はいわゆるバッチクエリのサポートを提供しています。
バッチクエリはデータカーソルを利用して、バッチモードでデータを取得します。

バッチクエリは次のようにして使うことが出来ます。

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->orderBy('id');

foreach ($query->batch() as $users) {
    // $users は user テーブルから取得した 100 以下の行の配列
}

// または、一行ずつ反復したい場合は
foreach ($query->each() as $user) {
    // $user は user テーブルから取得した一つの行を表す
}
```

[[yii\db\Query::batch()]] メソッドと [[yii\db\Query::each()]] メソッドは [[yii\db\BatchQueryResult]] オブジェクトを返します。
このオブジェクトは `Iterator` インタフェイスを実装しており、従って、`foreach` 構文の中で使うことが出来ます。
初回の反復の際に、データベースに対する SQL クエリが作成されます。データは、その後、反復のたびにバッチモードで取得されます。
デフォルトでは、バッチサイズは 100 であり、各バッチにおいて 100 行のデータが取得されます。
`batch()` または `each()` メソッドに最初のパラメータを渡すことによって、バッチサイズを変更することが出来ます。

[[yii\db\Query::all()]] とは対照的に、バッチクエリは一度に 100 行のデータしかメモリに読み込みません。
データを処理した後、すぐにデータを破棄するようにすれば、バッチクエリの助けを借りてメモリ消費量を削減することが出来ます。

[[yii\db\Query::indexBy()]] によってクエリ結果をあるカラムでインデックスするように指定している場合でも、バッチクエリは正しいインデックスを保持します。
例えば、

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users は "username" カラムでインデックスされている
}

foreach ($query->each() as $username => $user) {
}
```
