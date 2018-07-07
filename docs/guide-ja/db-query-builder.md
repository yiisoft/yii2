クエリ・ビルダ
==============

[データベース・アクセス・オブジェクト](db-dao.md) の上に構築されているクエリ・ビルダは、SQL クエリをプログラム的に、
かつ、DBMS の違いを意識せずに作成することを可能にしてくれます。
クエリ・ビルダを使うと、生の SQL 文を書くことに比べて、より読みやすい SQL 関連のコードを書き、より安全な SQL 文を生成することが容易になります。

通常、クエリ・ビルダの使用は、二つのステップから成ります。

1. SELECT SQL 文のさまざまな部分 (例えば、`SELECT`、`FROM`) を表現する [[yii\db\Query]] オブジェクトを構築する。
2. [[yii\db\Query]] のクエリ・メソッド (例えば `all()`) を実行して、データベースからデータを取得する。

次のコードは、クエリ・ビルダを使用する典型的な方法を示すものです。

```php
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->all();
```

上記のコードは、次の SQL クエリを生成して実行します。
ここでは、`:last_name` パラメータは `'Smith'` という文字列にバインドされています。

```sql
SELECT `id`, `email` 
FROM `user`
WHERE `last_name` = :last_name
LIMIT 10
```

> Info: 通常は、[[yii\db\QueryBuilder]] ではなく、主として [[yii\db\Query]] を使用します。
  前者は、クエリ・メソッドの一つを呼ぶときに、後者によって黙示的に起動されます。
  [[yii\db\QueryBuilder]] は、DBMS に依存しない [[yii\db\Query]] オブジェクトから、DBMS に依存する SQL 文を生成する
  (例えば、テーブルやカラムの名前を DBMS ごとに違う方法で引用符で囲む) 役割を負っているクラスです。


## クエリを構築する <span id="building-queries"></span>

[[yii\db\Query]] オブジェクトを構築するために、さまざまなクエリ構築メソッドを呼んで、SQL クエリのさまざまな部分を定義します。
これらのメソッドの名前は、SQL 文の対応する部分に使われる SQL キーワードに似たものになっています。
例えば、SQL クエリの `FROM` の部分を定義するためには、`from()` メソッドを呼び出します。
クエリ構築メソッドは、すべて、クエリ・オブジェクトそのものを返しますので、複数の呼び出しをチェーンしてまとめることが出来ます。

以下で、それぞれのクエリ構築メソッドの使用方法を説明しましょう。


### [[yii\db\Query::select()|select()]] <span id="select"></span>

[[yii\db\Query::select()|select()]] メソッドは、SQL 文の `SELECT` 句を定義します。
選択されるカラムは、次のように、配列または文字列として指定することが出来ます。
選択されるカラムの名前は、クエリ・オブジェクトから SQL 文が生成されるときに、自動的に引用符で囲まれます。

```php
$query->select(['id', 'email']);

// 次と等価

$query->select('id, email');
```

選択されるカラム名は、生の SQL クエリを書くときにするように、テーブル接頭辞 および/または カラムのエイリアスを含むことが出来ます。
例えば、

```php
$query->select(['user.id AS user_id', 'email']);

// 次と等価

$query->select('user.id AS user_id, email');
```

カラムを指定するのに配列形式を使っている場合は、配列のキーを使ってカラムのエイリアスを指定することも出来ます。
例えば、上記のコードは次のように書くことが出来ます。

```php
$query->select(['user_id' => 'user.id', 'email']);
```

クエリを構築するときに [[yii\db\Query::select()|select()]] メソッドを呼ばなかった場合は、`*` がセレクトされます。
すなわち、*全て* のカラムが選択されることになります。

カラム名に加えて、DB 式をセレクトすることも出来ます。
カンマを含む DB 式をセレクトする場合は、自動的に引用符で囲む機能が誤動作しないように、配列形式を使わなければなりません。例えば、

```php
$query->select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']); 
```

生の SQL が使われる場所ではどこでもそうですが、セレクトに DB 式を書く場合には、テーブルやカラムの名前を表すために
[特定のデータベースに依存しない引用符の構文](db-dao.md#quoting-table-and-column-names) を使うことが出来ます。

バージョン 2.0.1 以降では、サブ・クエリもセレクトすることが出来ます。
各サブ・クエリは、[[yii\db\Query]] オブジェクトの形で指定しなければなりません。例えば、

```php
$subQuery = (new Query())->select('COUNT(*)')->from('user');

// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
$query = (new Query())->select(['id', 'count' => $subQuery])->from('post');
```

重複行を除外してセレクトするためには、次のように、[[yii\db\Query::distinct()|distinct()]] を呼ぶことが出来ます。

```php
// SELECT DISTINCT `user_id` ...
$query->select('user_id')->distinct();
```

追加のカラムをセレクトするためには [[yii\db\Query::addSelect()|addSelect()]] を呼ぶことが出来ます。例えば、

```php
$query->select(['id', 'username'])
    ->addSelect(['email']);
```


### [[yii\db\Query::from()|from()]] <span id="from"></span>

[[yii\db\Query::from()|from()]] メソッドは、SQL 文の `FROM` 句を定義します。例えば、

```php
// SELECT * FROM `user`
$query->from('user');
```

セレクトの対象になる (一つまたは複数の) テーブルは、文字列または配列として指定することが出来ます。
テーブル名は、生の SQL 文を書くときにするように、スキーマ接頭辞 および/または テーブル・エイリアスを含むことが出来ます。例えば、

```php
$query->from(['public.user u', 'public.post p']);

// 次と等価

$query->from('public.user u, public.post p');
```

配列形式を使う場合は、次のように、配列のキーを使ってテーブル・エイリアスを指定することも出来ます。

```php
$query->from(['u' => 'public.user', 'p' => 'public.post']);
```

テーブル名の他に、[[yii\db\Query]] オブジェクトの形で指定することによって、サブ・クエリをセレクトの対象とすることも出来ます。
例えば、

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');

// SELECT * FROM (SELECT `id` FROM `user` WHERE status=1) u 
$query->from(['u' => $subQuery]);
```

#### プレフィックス
また、デフォルトの [[yii\db\Connection::$tablePrefix|tablePrefix]] を適用することも出来ます。
実装の仕方は ["データベース・アクセス・オブジェクト" ガイドの "テーブル名を引用符で囲む" のセクション](guide-db-dao.html#quoting-table-and-column-names) にあります。

### [[yii\db\Query::where()|where()]] <span id="where"></span>

[[yii\db\Query::where()|where()]] メソッドは、SQL クエリの `WHERE` 句を定義します。
`WHERE` の条件を指定するために、次の4つの形式から一つを選んで使うことが出来ます。

- 文字列形式、例えば、`'status=1'`
- ハッシュ形式、例えば、`['status' => 1, 'type' => 2]`
- 演算子形式、例えば、`['like', 'name', 'test']`
- オブジェクト形式、例えば、`new LikeCondition('name', 'LIKE', 'test')`

#### 文字列形式 <span id="string-format"></span>

文字列形式は、非常に単純な条件を定義する場合や、DBMS の組み込み関数を使う必要がある場合に最適です。
これは、生の SQL を書いている場合と同じように動作します。例えば、

```php
$query->where('status=1');

// あるいは、パラメータ・バインディングを使って、動的にパラメータをバインドする
$query->where('status=:status', [':status' => $status]);

// date フィールドに対して MySQL の YEAR() 関数を使う生の SQL
$query->where('YEAR(somedate) = 2015');
```

次のように、条件式に変数を直接に埋め込んではいけません。
特に、変数の値がユーザの入力に由来する場合、あなたのアプリケーションを SQL インジェクション攻撃にさらすことになりますので、してはいけません。

```php
// 危険! $status が整数であることが絶対に確実でなければ、してはいけない。
$query->where("status=$status");
```

`パラメータ・バインディング` を使う場合は、[[yii\db\Query::params()|params()]] または [[yii\db\Query::addParams()|addParams()]]
を使って、パラメータの指定を分離することが出来ます。

```php
$query->where('status=:status')
    ->addParams([':status' => $status]);
```

生の SQL が使われる場所ではどこでもそうですが、文字列形式で条件を書く場合には、テーブルやカラムの名前を表すために
[特定のデータベースに依存しない引用符の構文](db-dao.md#quoting-table-and-column-names) を使うことが出来ます。

#### ハッシュ形式 <span id="hash-format"></span>

値が等しいことを要求する単純な条件をいくつか `AND` で結合する場合は、ハッシュ形式を使うのが最適です。
個々の条件を表す配列の各要素は、キーをカラム名、値をそのカラムが持つべき値とします。
例えば、

```php
// ...WHERE (`status` = 10) AND (`type` IS NULL) AND (`id` IN (4, 8, 15))
$query->where([
    'status' => 10,
    'type' => null,
    'id' => [4, 8, 15],
]);
```

ご覧のように、クエリ・ビルダは頭が良いので、null や配列である値も、適切に処理します。

次のように、サブ・クエリをハッシュ形式で使うことも出来ます。

```php
$userQuery = (new Query())->select('id')->from('user');

// ...WHERE `id` IN (SELECT `id` FROM `user`)
$query->where(['id' => $userQuery]);
```

ハッシュ形式を使う場合、Yii は内部的にパラメータ・バインディングを使用します。
従って、[文字列形式](#string-format) とは対照的に、ここでは手動でパラメータを追加する必要はありません。ただし、Yii はカラム名を決してエスケープしないことに注意して下さい。
従って、ユーザから取得した変数を何ら追加のチェックをすることなくカラム名として渡すと、SQL インジェクション攻撃に対して脆弱になります。
アプリケーションを安全に保つためには、カラム名として変数を使わないこと、または、変数をホワイト・リストによってフィルターすることが必要です。
カラム名をユーザから取得する必要がある場合は、ガイドの [データをフィルタリングする](output-data-widgets.md#filtering-data) という記事を読んで下さい。
例えば、次のコードは脆弱です。

```php
// 脆弱なコード:
$column = $request->get('column');
$value = $request->get('value');
$query->where([$column => $value]);
// $value は安全です。しかし、$column の名前はエンコードされません。
```

#### 演算子形式 <span id="operator-format"></span>

演算子形式を使うと、任意の条件をプログラム的な方法で指定することが出来ます。これは次の形式を取るものです。

```php
[演算子, オペランド1, オペランド2, ...]
```

ここで、各オペランドは、文字列形式、ハッシュ形式、あるいは、再帰的に演算子形式として指定することが出来ます。
そして、演算子には、次のどれか一つを使うことが出来ます。

- `and`: 複数のオペランドが `AND` を使って結合されます。
  例えば、`['and', 'id=1', 'id=2']` は `id=1 AND id=2` を生成します。
  オペランドが配列である場合は、ここで説明されている規則に従って文字列に変換されます。
  例えば、`['and', 'type=1', ['or', 'id=1', 'id=2']]` は `type=1 AND (id=1 OR id=2)` を生成します。
  このメソッドは、文字列を引用符で囲ったりエスケープしたりしません。

- `or`: オペランドが `OR` を使って結合されること以外は `and` 演算子と同じです。

- `not`: オペランド1 だけを受け取って `NOT()` で包みます。例えば、`['not', 'id=1']` は `NOT (id=1)` を生成します。オペランド1 は、それ自体も複数の式を表す配列であっても構いません。例えば、`['not', ['status' => 'draft', 'name' => 'example']]` は `NOT ((status='draft') AND (name='example'))` を生成します。

- `between`: オペランド 1 はカラム名、オペランド 2 と 3 はカラムの値が属すべき範囲の開始値と終了値としなければなりません。
  例えば、`['between', 'id', 1, 10]` は `id BETWEEN 1 AND 10` を生成します。
  値が二つのカラムの値の間にあるという条件 (例えば、`11 BETWEEN min_id AND max_id`) を構築する必要がある場合は、
  [[yii\db\conditions\BetweenColumnsCondition|BetweenColumnsCondition]] を使用しなければなりません。
  条件定義のオブジェクト形式について更に学習するためには [条件 – オブジェクト形式](#object-format)
  のセクションを参照して下さい。

- `not between`: 生成される条件において `BETWEEN` が `NOT BETWEEN` に置き換えられる以外は、
  `between` と同じです。

- `in`: オペランド 1 はカラム名または DB 式でなければなりません。
  オペランド 2 は、配列または `Query` オブジェクトのどちらかを取ることが出来ます。
  オペランド 2 が配列である場合は、その配列は、カラムまたは DB 式が該当すべき値域を表すものとされます。
  オペランド 2 が `Query` オブジェクトである場合は、サブ・クエリが生成されて、カラムまたは DB 式の値域として使われます。
  例えば、`['in', 'id', [1, 2, 3]]` は `id IN (1, 2, 3)` を生成します。
  このメソッドは、カラム名を適切に引用符で囲み、値域の値をエスケープします。
  `in` 演算子はまた複合カラムをもサポートしています。
  その場合、オペランド 1 はカラム名の配列とし、オペランド 2 は配列の配列、または、複合カラムの値域を表す `Query` オブジェクトでなければなりません。

- `not in`: 生成される条件において `IN` が `NOT IN` に置き換えられる以外は、`in` と同じです。

- `like`: オペランド 1 はカラム名または DB 式、オペランド 2 はそのカラムまたは DB 式がマッチすべき値を示す
  文字列または配列でなければなりません。
  例えば、`['like', 'name', 'tester']` は `name LIKE '%tester%'` を生成します。
  値域が配列として与えられた場合は、複数の `LIKE` 述語が生成されて 'AND' によって結合されます。
  例えば、`['like', 'name', ['test', 'sample']]` は
  `name LIKE '%test%' AND name LIKE '%sample%'` を生成します。
  さらに、オプションである三番目のオペランドによって、値の中の特殊文字をエスケープする方法を指定することも出来ます。
  このオペランド 3 は、特殊文字とそのエスケープ結果のマッピングを示す配列でなければなりません。
  このオペランドが提供されない場合は、デフォルトのエスケープ・マッピングが使用されます。
  `false` または空の配列を使って、値が既にエスケープ済みであり、それ以上エスケープを適用すべきでないことを示すことが出来ます。
  エスケープマッピングを使用する場合 (または第三のオペランドが与えられない場合) は、
  値が自動的に一対のパーセント記号によって囲まれることに注意してください。

  > Note: PostgreSQL を使っている場合は、`like` の代りに、大文字と小文字を区別しない比較のための
  > [`ilike`](http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE) を使うことも出来ます。

- `or like`: オペランド 2 が配列である場合に `LIKE` 述語が `OR` によって結合される以外は、
  `like` 演算子と同じです。

- `not like`: 生成される条件において `LIKE` が `NOT LIKE` に置き換えられる以外は、
  `like` 演算子と同じです。

- `or not like`: `NOT LIKE` 述語が `OR` によって結合される以外は、
  `not like` 演算子と同じです。

- `exists`: 要求される一つだけのオペランドは、サブ・クエリを表す [[yii\db\Query]] のインスタンスでなければなりません。
  これは `EXISTS (sub-query)` という式を構築します。

- `not exists`: `exists` 演算子と同じで、`NOT EXISTS (sub-query)` という式を構築します。

- `>`、`<=`、その他、二つのオペランドを取る有効な DB 演算子全て: 最初のオペランドはカラム名、第二のオペランドは値でなければなりません。
  例えば、`['>', 'age', 10]` は `age>10` を生成します。

演算子形式を使う場合、Yii は値に対して内部的にパラメータ・バインディングを使用します。
従って、[文字列形式](#string-format) とは対照的に、ここでは手動でパラメータを追加する必要はありません。
ただし、Yii はカラム名を決してエスケープしないことに注意して下さい。従って、変数をカラム名として渡すと、アプリケーションは SQL インジェクション攻撃に対して脆弱になります。
アプリケーションを安全に保つためには、カラム名として変数を使わないこと、または、変数をホワイト・リストによってフィルターすることが必要です。
カラム名をユーザから取得する必要がある場合は、ガイドの [データをフィルタリングする](output-data-widgets.md#filtering-data) という記事を読んで下さい。
例えば、次のコードは脆弱です。

```php
// 脆弱なコード:
$column = $request->get('column');
$value = $request->get('value);
$query->where([$column => $value]);
// $value は安全です。しかし、$column の名前はエンコードされません。
```

#### オブジェクト形式 <span id="object-format"></span>

オブジェクト形式は 2.0.14 から利用可能な、条件を定義するための最も強力でもあり、最も複雑でもある方法です。
クエリ・ビルダの上にあなた自身の抽象レイヤを構築したいときや、または独自の複雑な条件を実装したいときは、
この形式を採用する必要があります。

条件クラスのインスタンスはイミュータブルです。
条件クラスのインスタンスは条件データを保持し、条件ビルダにゲッターを提供することを唯一の目的とします。
そして、条件ビルダが、条件クラスのインスタンスに保存されたデータを SQL の式に変換するロジックを持つクラスです。

内部的には、上述の三つの形式は、生の SQL を構築するに先立って、暗黙のうちにオブジェクト形式に変換されます。
従って、複数の形式を単一の条件に結合することが可能です。

```php
$query->andWhere(new OrCondition([
    new InCondition('type', 'in', $types),
    ['like', 'name', '%good%'],
    'disabled=false'
]))
```

演算子形式からオブジェクト形式への変換は、
演算子の名前とそれを表すクラス名を対応づける [[yii\db\QueryBuilder::conditionClasses|QueryBuilder::conditionClasses]]
プロパティに従って行われます。

- `AND`, `OR` -> `yii\db\conditions\ConjunctionCondition`
- `NOT` -> `yii\db\conditions\NotCondition`
- `IN`, `NOT IN` -> `yii\db\conditions\InCondition`
- `BETWEEN`, `NOT BETWEEN` -> `yii\db\conditions\BetweenCondition`

等々。

オブジェクト形式を使うことによって、あなた独自の条件を作成したり、デフォルトの条件が作成される方法を変更したりすることが可能になります。
詳細は [特製の条件や式を追加する](#adding-custom-conditions-and-expressions) のセクションを参照して下さい。


#### 条件を追加する <span id="appending-conditions"></span>

[[yii\db\Query::andWhere()|andWhere()]] または [[yii\db\Query::orWhere()|orWhere()]] を使って、既存の条件に別の条件を追加することが出来ます。
これらのメソッドを複数回呼んで、複数の条件を別々に追加することが出来ます。
例えば、

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);

if (!empty($search)) {
    $query->andWhere(['like', 'title', $search]);
}
```

`$search` が空でない場合は次の `WHERE` 条件 が生成されます。

```sql
WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```


#### フィルタ条件 <span id="filter-conditions"></span>

ユーザの入力に基づいて `WHERE` の条件を構築する場合、普通は、空の入力値は無視したいものです。
例えば、ユーザ名とメール・アドレスによる検索が可能な検索フォームにおいては、
ユーザが username/email のインプット・フィールドに何も入力しなかった場合は、username/email の条件を無視したいでしょう。
[[yii\db\Query::filterWhere()|filterWhere()]] メソッドを使うことによって、この目的を達することが出来ます。

```php
// $username と $email はユーザの入力による
$query->filterWhere([
    'username' => $username,
    'email' => $email,
]);
```

[[yii\db\Query::filterWhere()|filterWhere()]] と [[yii\db\Query::where()|where()]] の唯一の違いは、
前者は [ハッシュ形式](#hash-format) の条件において提供された空の値を無視する、という点です。
従って、`$email` が空で `$sername` がそうではない場合は、上記のコードは、結果として `WHERE username=:username` という SQL 条件になります。

> Info: 値が空であると見なされるのは、`null`、空の配列、空の文字列、または空白のみを含む文字列である場合です。

[[yii\db\Query::andWhere()|andWhere()]] または [[yii\db\Query::orWhere()|orWhere()]] と同じように、
[[yii\db\Query::andFilterWhere()|andFilterWhere()]] または [[yii\db\Query::orFilterWhere()|orFilterWhere()]] を使って、
既存の条件に別のフィルタ条件を追加することも出来ます。

さらに加えて、値の方に含まれている比較演算子を適切に判断してくれる
[[yii\db\Query::andFilterCompare()]] があります。

```php
$query->andFilterCompare('name', 'John Doe');
$query->andFilterCompare('rating', '>9');
$query->andFilterCompare('value', '<=100');
```

演算子を明示的に指定することも可能です。

```php
$query->andFilterCompare('name', 'Doe', 'like');
```

Yii 2.0.11 以降には、`HAVING` の条件のためにも、同様のメソッドがあります。

- [[yii\db\Query::filterHaving()|filterHaving()]]
- [[yii\db\Query::andFilterHaving()|andFilterHaving()]]
- [[yii\db\Query::orFilterHaving()|orFilterHaving()]]

### [[yii\db\Query::orderBy()|orderBy()]] <span id="order-by"></span>

[[yii\db\Query::orderBy()|orderBy()]] メソッドは SQL クエリの `ORDER BY` 句を指定します。例えば、

```php
// ... ORDER BY `id` ASC, `name` DESC
$query->orderBy([
    'id' => SORT_ASC,
    'name' => SORT_DESC,
]);
```

上記のコードにおいて、配列のキーはカラム名であり、配列の値は並べ替えの方向です。
PHP の定数 `SORT_ASC` は昇順、`SORT_DESC` は降順を指定するものです。

`ORDER BY` が単純なカラム名だけを含む場合は、生の SQL 文を書くときにするように、文字列を使って指定することが出来ます。
例えば、

```php
$query->orderBy('id ASC, name DESC');
```

> Note: `ORDER BY` が何らかの DB 式を含む場合は、配列形式を使わなければなりません。

[[yii\db\Query::addOrderBy()|addOrderBy()]] を呼んで、`ORDER BY' 句にカラムを追加することが出来ます。
例えば、

```php
$query->orderBy('id ASC')
    ->addOrderBy('name DESC');
```


### [[yii\db\Query::groupBy()|groupBy()]] <span id="group-by"></span>

[[yii\db\Query::groupBy()|groupBy()]] メソッドは SQL クエリの `GROUP BY` 句を指定します。例えば、

```php
// ... GROUP BY `id`, `status`
$query->groupBy(['id', 'status']);
```

`GROUP BY` が単純なカラム名だけを含む場合は、生の SQL 文を書くときにするように、文字列を使って指定することが出来ます。
例えば、

```php
$query->groupBy('id, status');
```

> Note: `GROUP BY` が何らかの DB 式を含む場合は、配列形式を使わなければなりません。
 
[[yii\db\Query::addGroupBy()|addGroupBy()]] を呼んで、`GROUP BY` 句にカラムを追加することが出来ます。
例えば、

```php
$query->groupBy(['id', 'status'])
    ->addGroupBy('age');
```


### [[yii\db\Query::having()|having()]] <span id="having"></span>

[[yii\db\Query::having()|having()]] メソッドは SQL クエリの `HAVING` 句を指定します。
このメソッドが取る条件は、[where()](#where) と同じ方法で指定することが出来ます。例えば、

```php
// ... HAVING `status` = 1
$query->having(['status' => 1]);
```

条件を指定する方法の詳細については、[where()](#where) のドキュメントを参照してください。

[[yii\db\Query::andHaving()|andHaving()]] または [[yii\db\Query::orHaving()|orHaving()]] を呼んで、`HAVING` 句に条件を追加することが出来ます。
例えば、

```php
// ... HAVING (`status` = 1) AND (`age` > 30)
$query->having(['status' => 1])
    ->andHaving(['>', 'age', 30]);
```


### [[yii\db\Query::limit()|limit()]] と [[yii\db\Query::offset()|offset()]] <span id="limit-offset"></span>

[[yii\db\Query::limit()|limit()]] と [[yii\db\Query::offset()|offset()]] のメソッドは、SQL クエリの `LIMIT` 句と `OFFSET` 句を指定します。
例えば、
 
```php
// ... LIMIT 10 OFFSET 20
$query->limit(10)->offset(20);
```

無効な上限やオフセット (例えば、負の数) を指定した場合は、無視されます。

> Info: `LIMIT` と `OFFSET` をサポートしていない DBMS (例えば MSSQL) に対しては、
クエリ・ビルダが `LIMIT`/`OFFSET` の振る舞いをエミュレートする SQL 文を生成します。


### [[yii\db\Query::join()|join()]] <span id="join"></span>

[[yii\db\Query::join()|join()]] メソッドは SQL クエリの `JOIN` 句を指定します。例えば、
 
```php
// ... LEFT JOIN `post` ON `post`.`user_id` = `user`.`id`
$query->join('LEFT JOIN', 'post', 'post.user_id = user.id');
```

[[yii\db\Query::join()|join()]] メソッドは、四つのパラメータを取ります。
 
- `$type`: 結合のタイプ、例えば、`'INNER JOIN'`、`'LEFT JOIN'`。
- `$table`: 結合されるテーブルの名前。
- `$on`: オプション。結合条件、すなわち、`ON` 句。
   条件の指定方法の詳細については、[where()](#where) を参照してください。
   カラムに基づく条件を指定する場合は、配列記法は**使えない**ことに注意してください。
   例えば、`['user.id' => 'comment.userId']` は、user の id が `'comment.userId'` という文字列でなければならない、という条件に帰結します。
   配列記法ではなく文字列記法を使って、`'user.id = comment.userId'` という条件を指定しなければなりません。
- `$params`: オプション。結合条件にバインドされるパラメータ。

`INNER JOIN`、`LEFT JOIN` および `RIGHT JOIN` を指定するためには、それぞれ、次のショートカット・メソッドを使うことが出来ます。

- [[yii\db\Query::innerJoin()|innerJoin()]]
- [[yii\db\Query::leftJoin()|leftJoin()]]
- [[yii\db\Query::rightJoin()|rightJoin()]]

例えば、

```php
$query->leftJoin('post', 'post.user_id = user.id');
```

複数のテーブルを結合するためには、テーブルごとに一回ずつ、上記の結合メソッドを複数回呼び出します。

テーブルを結合することに加えて、サブ・クエリを結合することも出来ます。
そうするためには、結合されるべきサブ・クエリを [[yii\db\Query]] オブジェクトとして指定します。例えば、

```php
$subQuery = (new \yii\db\Query())->from('post');
$query->leftJoin(['u' => $subQuery], 'u.id = author_id');
```

この場合、サブ・クエリを配列に入れて、配列のキーを使ってエイリアスを指定しなければなりません。


### [[yii\db\Query::union()|union()]] <span id="union"></span>

[[yii\db\Query::union()|union()]] メソッドは SQL クエリの `UNION` 句を指定します。例えば、

```php
$query1 = (new \yii\db\Query())
    ->select("id, category_id AS type, name")
    ->from('post')
    ->limit(10);

$query2 = (new \yii\db\Query())
    ->select('id, type, name')
    ->from('user')
    ->limit(10);

$query1->union($query2);
```

[[yii\db\Query::union()|union()]] を複数回呼んで、`UNION` 句をさらに追加することが出来ます。


## クエリ・メソッド <span id="query-methods"></span>

[[yii\db\Query]] は、さまざまな目的のクエリのために、一揃いのメソッドを提供しています。

- [[yii\db\Query::all()|all()]]: 各行を「名前-値」のペアの連想配列とする、結果の行の配列を返す。
- [[yii\db\Query::one()|one()]]: 結果の最初の行を返す。
- [[yii\db\Query::column()|column()]]: 結果の最初のカラムを返す。
- [[yii\db\Query::scalar()|scalar()]]: 結果の最初の行の最初のカラムにあるスカラ値を返す。
- [[yii\db\Query::exists()|exists()]]: クエリが結果を含むか否かを示す値を返す。
- [[yii\db\Query::count()|count()]]: `COUNT` クエリの結果を返す。
- その他の集計クエリ、すなわち、[[yii\db\Query::sum()|sum($q)]], [[yii\db\Query::average()|average($q)]],
  [[yii\db\Query::max()|max($q)]], [[yii\db\Query::min()|min($q)]].
  これらのメソッドでは、`$q` パラメータは必須であり、カラム名または DB 式とすることが出来る。

例えば、

```php
// SELECT `id`, `email` FROM `user`
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->all();
    
// SELECT * FROM `user` WHERE `username` LIKE `%test%`
$row = (new \yii\db\Query())
    ->from('user')
    ->where(['like', 'username', 'test'])
    ->one();
```

> Note: [[yii\db\Query::one()|one()]] メソッドはクエリ結果の最初の行だけを返します。このメソッドは `LIMIT 1`
  を生成された SQL 文に追加しません。このことは、クエリが一つまたは少数の行しか返さないことが判っている場合
  (例えば、何らかのプライマリ・キーでクエリを発行する場合) は問題になりませんし、むしろ好ましいことです。
  しかし、クエリ結果が多数のデータ行になる可能性がある場合は、パフォーマンスを向上させるために、明示的に `limit(1)` を呼ぶべきです。例えば、
  `(new \yii\db\Query())->from('user')->limit(1)->one()`

上記のメソッドの全ては、オプションで、DB クエリの実行に使用されるべき [[yii\db\Connection|DB 接続]] を表す `$db` パラメータを取ることが出来ます。
このパラメータを省略した場合は、DB 接続として `db` [アプリケーション・コンポーネント](structure-application-components.md) が使用されます。
次に [[yii\db\Query::count()|count()]] クエリ・メソッドを使う例をもう一つ挙げます。

```php
// 実行される SQL: SELECT COUNT(*) FROM `user` WHERE `last_name`=:last_name
$count = (new \yii\db\Query())
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->count();
```

あなたが [[yii\db\Query]] のクエリ・メソッドを呼び出すと、実際には、内部的に次の仕事がなされます。

* [[yii\db\QueryBuilder]] を呼んで、[[yii\db\Query]] の現在の構成に基づいた SQL 文を生成する。
* 生成された SQL 文で [[yii\db\Command]] オブジェクトを作成する。
* [[yii\db\Command]] のクエリ・メソッド (例えば [[yii\db\Command::queryAll()|queryAll()]]) を呼んで、SQL 文を実行し、データを取得する。

場合によっては、[[yii\db\Query]] オブジェクトから構築された SQL 文を調べたり使ったりしたいことがあるでしょう。
次のコードを使って、その目的を達することが出来ます。

```php
$command = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->createCommand();
    
// SQL 文を表示する
echo $command->sql;
// バインドされるパラメータを表示する
print_r($command->params);

// クエリ結果の全ての行を返す
$rows = $command->queryAll();
```


## クエリ結果をインデックスする <span id="indexing-query-results"></span>

[[yii\db\Query::all()|all()]] を呼ぶと、結果の行は連続した整数でインデックスされた配列で返されます。
場合によっては、違う方法でインデックスしたいことがあるでしょう。
例えば、特定のカラムの値や、何らかの式の値によってインデックスするなどです。
この目的は、[[yii\db\Query::all()|all()]] の前に [[yii\db\Query::indexBy()|indexBy()]] を呼ぶことによって達成することが出来ます。例えば、

```php
// [100 => ['id' => 100, 'username' => '...', ...], 101 => [...], 103 => [...], ...] を返す
$query = (new \yii\db\Query())
    ->from('user')
    ->limit(10)
    ->indexBy('id')
    ->all();
```

式の値によってインデックスするためには、[[yii\db\Query::indexBy()|indexBy()]] メソッドに無名関数を渡します。

```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy(function ($row) {
        return $row['id'] . $row['username'];
    })->all();
```

この無名関数は、現在の行データを含む `$row` というパラメータを取り、
現在の行のインデックス値として使われるスカラ値を返さなくてはなりません。

> Note: [[yii\db\Query::groupBy()|groupBy()]] や [[yii\db\Query::orderBy()|orderBy()]]
> のようなクエリ・メソッドが SQL に変換されてクエリの一部となるのとは対照的に、このメソッドはデータベースからデータが取得された後で動作します。
> このことは、クエリの SELECT に含まれるカラム名だけを使うことが出来る、ということを意味します。
> また、テーブル・プレフィックスを付けてカラムを選択した場合、例えば `customer.id` を選択した場合は、
> リザルトセットのカラム名は `id` しか含みませんので、テーブルプレフィックス無しで `->indexBy('id')` と呼ぶ必要があります。


## バッチ・クエリ <span id="batch-query"></span>

大量のデータを扱う場合は、[[yii\db\Query::all()]] のようなメソッドは適していません。
なぜなら、それらのメソッドは、クエリの結果全てをクライアントのメモリに読み込むことを必要とするためです。
この問題を解決するために、Yii はバッチ・クエリのサポートを提供しています。クエリ結果はサーバに保持し、
クライアントはカーソルを利用して1回に1バッチずつ結果セットを反復取得するのです。

> Warning: MySQL のバッチ・クエリの実装には既知の制約と回避策があります。下記を参照して下さい。

バッチ・クエリは次のようにして使うことが出来ます。

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->orderBy('id');

foreach ($query->batch() as $users) {
    // $users は user テーブルから取得した 100 以下の行の配列
}

// または、一行ずつ反復する場合は
foreach ($query->each() as $user) {
    // データはサーバから 100 行のバッチで取得される
    // しかし $user は user テーブルの一つの行を表す
}
```

[[yii\db\Query::batch()]] メソッドと [[yii\db\Query::each()]] メソッドは [[yii\db\BatchQueryResult]]
オブジェクトを返します。このオブジェクトは `Iterator` インタフェイスを実装しており、従って、`foreach` 構文の中で使うことが出来ます。
初回の反復の際に、データベースに対する SQL クエリが作成されます。データは、その後、反復のたびにバッチ・モードで取得されます。
デフォルトでは、バッチ・サイズは 100 であり、各バッチにおいて 100 行のデータが取得されます。
バッチ・サイズは、`batch()` または `each()` メソッドに最初のパラメータを渡すことによって変更することが出来ます。

[[yii\db\Query::all()]] とは対照的に、バッチ・クエリは一度に 100 行のデータしかメモリに読み込みません。

[[yii\db\Query::indexBy()]] によって、いずれかのカラムでクエリ結果をインデックスするように指定している場合でも、
バッチ・クエリは正しいインデックスを保持します。

例えば、

```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users は "username" カラムでインデックスされている
}

foreach ($query->each() as $username => $user) {
    // ...
}
```

#### MySQL におけるバッチ・クエリの制約 <span id="batch-query-mysql"></span>

MySQL のバッチ・クエリの実装は PDO ドライバのライブラリに依存しています。デフォルトでは、MySQL のクエリは
[`バッファ・モード`](http://php.net/manual/ja/mysqlinfo.concepts.buffering.php) で実行されます。
このことが、カーソルを使ってデータを取得する目的を挫折させます。というのは、バッファ・モードでは、
ドライバによって結果セット全体がクライアントのメモリに読み込まれることを防止できないからです。

> Note: `libmysqlclient` が使われている場合 (PHP5 ではそれが普通ですが) は、
  結果セットに使用されたメモリは PHP のメモリ使用量としてカウントされません。そのため、一見、バッチ・クエリが正しく動作するように見えますが、
  実際には、データ・セット全体がクライアントのメモリに読み込まれて、クライアントのメモリを使い果たす可能性があります。

バッファ・モードを無効化してクライアントのメモリ要求量を削減するためには、PDO 接続のプロパティ
`PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` を `false` に設定しなければなりません。しかし、そうすると、
データ・セット全体を取得するまでは、同じ接続を通じては別のクエリを実行できなくなります。これによって
`ActiveRecord` が必要に応じてテーブル・スキーマを取得するためのクエリを実行できなくなる可能性があります。
これが問題にならない場合 (テーブル・スキーマが既にキャッシュされている場合) は、元の接続を非バッファ・モードに切り替えて、
バッチ・クエリを実行した後に元に戻すということが可能です。

```php
Yii::$app->db->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

// バッチ・クエリを実行

Yii::$app->db->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
```

> Note: MyISAM の場合は、バッチ・クエリが継続している間、テーブルがロックされて、
  他の接続からの書き込みアクセスが遅延または拒絶されることがあります。非バッファ・モードのクエリを使う場合は、
  カーソルを開いている時間を可能な限り短くするように努めて下さい。

スキーマがキャッシュされていない場合、またはバッチ・クエリを処理している間に他のクエリを走らせる必要がある場合は、
独立した非バッファ・モードのデータベース接続を作成することが出来ます。

```php
$unbufferedDb = new \yii\db\Connection([
    'dsn' => Yii::$app->db->dsn,
    'username' => Yii::$app->db->username,
    'password' => Yii::$app->db->password,
    'charset' => Yii::$app->db->charset,
]);
$unbufferedDb->open();
$unbufferedDb->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
```

`$unbufferedDb` が `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` が `false` であること以外は、
元のバッファ・モードの `$db` と同じ PDO 属性を持つことを保証したい場合は、
[`$db` のディープ・コピー](https://github.com/yiisoft/yii2/issues/8420#issuecomment-301423833)
をしてから、手動で false に設定することを考慮して下さい。

そして、クエリは普通に作成します。新しい接続を使ってバッチ・クエリを走らせ、結果をバッチで取得、
または一つずつ取得します。

```php
// データを 1000 のバッチで取得
foreach ($query->batch(1000, $unbufferedDb) as $users) {
    // ...
}


// データは 1000 のバッチでサーバから取得されるが、一つずつ反復処理される
foreach ($query->each(1000, $unbufferedDb) as $user) {
    // ...
}
```

結果セットが全て取得されて接続が必要なくなったら、接続を閉じることが出来ます。

```php
$unbufferedDb->close();
```

> Note: 非バッファ・モードのクエリは PHP 側でのメモリ消費は少なくなりますが、MySQL サーバの負荷を増加させ得ます。
特別に巨大なデータに対するアプリの動作については、あなた自身のコードを設計することが推奨されます。
例えば、[整数のキーで範囲を分割して、非バッファ・モードのクエリでループする](https://github.com/yiisoft/yii2/issues/8420#issuecomment-296109257) など。

### 特製の条件や式を追加する <span id="adding-custom-conditions-and-expressions"></span>

[条件 – オブジェクト形式](#object-format) のセクションで触れたように、特製の条件クラスを作成することが可能です。
例として、特定のカラムが一定の値より小さいことをチェックする条件を作ってみましょう。
演算子形式を使えば、それは次のようになるでしょう。

```php
[
    'and',
    '>', 'posts', $minLimit,
    '>', 'comments', $minLimit,
    '>', 'reactions', $minLimit,
    '>', 'subscriptions', $minLimit
]
```

このような条件を一度に適用できたら良いですね。一つのクエリの中で複数回使われる場合には、最適化の効果が大きいでしょう。
特製の条件オブジェクトを作って、それを実証しましょう。

Yii には、条件を表現するクラスを特徴付ける [[yii\db\conditions\ConditionInterface|ConditionInterface]] があります。
このインタフェイスは、配列形式から条件を作ることを可能にするための `fromArrayDefinition()` メソッドを実装することを要求します。
あなたがそれを必要としない場合は、例外を投げるだけのメソッドとして実装しても構いません。

特製の条件クラスを作るのですから、私たちの仕事に最適な API を構築すれば良いのです。

```php
namespace app\db\conditions;

class AllGreaterCondition implements \yii\db\conditions\ConditionInterface
{
    private $columns;
    private $value;

    /**
     * @param string[] $columns $value よりも大きくなければならないカラムの配列
     * @param mixed $value 各カラムと比較する値
     */
    public function __construct(array $columns, $value)
    {
        $this->columns = $columns;
        $this->value = $value;
    }
    
    public static function fromArrayDefinition($operator, $operands)
    {
        throw new InvalidArgumentException('未実装、あとでやる');
    }
    
    public function getColumns() { return $this->columns; }
    public function getValue() { return $this->vaule; }
}
```

これで条件オブジェクトを作ることが出来ます。

```php
$conditon = new AllGreaterCondition(['col1', 'col2'], 42);
```

しかし `QueryBuilder` は、このオブジェクトから SQL 条件式を作る方法を知りません。
次に、この条件に対する式ビルダを作成する必要があります。
式ビルダは `build()` メソッドを提供する [[yii\db\ExpressionBuilderInterface]] を実装しなければいけません。

```php
namespace app\db\conditions;

class AllGreaterConditionBuilder implements \yii\db\ExpressionBuilderInterface
{
    use \yii\db\Condition\ExpressionBuilderTrait; // コンストラクタと `queryBuilder` プロパティを含む。

    /**
     * @param AllGreaterCondition $condition ビルドすべき条件
     * @param array $params バインディング・パラメータ
     */ 
    public function build(ConditionInterface $condition, &$params)
    {
        $value = $condition->getValue();
        
        $conditions = [];
        foreach ($condition->getColumns() as $column) {
            $conditions[] = new SimpleCondition($column, '>', $value);
        }

        return $this->queryBuider->buildCondition(new AndCondition($conditions), $params);
    }
}
```

後は、単に [[yii\db\QueryBuilder|QueryBuilder]] に私たちの新しい条件について知らせるだけです – 
条件のマッピングを `expressionBuilders` 配列に追加します。次のように、アプリケーション構成で直接に追加することが出来ます。

```php
'db' => [
    'class' => 'yii\db\mysql\Connection',
    // ...
    'queryBuilder' => [
        'expressionBuilders' => [
            'app\db\conditions\AllGreaterCondition' => 'app\db\conditions\AllGreaterConditionBuilder',
        ],
    ],
],
```

これで、私たちの新しい条件を `where()` で使用することが出来るようになりました。

```php
$query->andWhere(new AllGreaterCondition(['posts', 'comments', 'reactions', 'subscriptions'], $minValue));
```

演算子形式を使って私たちの特製の条件を作成することが出来るようにしたい場合は、
演算子を [[yii\db\QueryBuilder::conditionClasses|QueryBuilder::conditionClasses]] の中で宣言しなければなりません。

```php
'db' => [
    'class' => 'yii\db\mysql\Connection',
    // ...
    'queryBuilder' => [
        'expressionBuilders' => [
            'app\db\conditions\AllGreaterCondition' => 'app\db\conditions\AllGreaterConditionBuilder',
        ],
        'conditionClasses' => [
            'ALL>' => 'app\db\conditions\AllGreaterCondition',
        ],
    ],
],
```

そして、`app\db\conditions\AllGreaterCondition` の中で `AllGreaterCondition::fromArrayDefinition()`
メソッドの本当の実装を作成します。

```php
namespace app\db\conditions;

class AllGreaterCondition implements \yii\db\conditions\ConditionInterface
{
    // ... 上記の実装を参照
     
    public static function fromArrayDefinition($operator, $operands)
    {
        return new static($operands[0], $operands[1]);
    }
}
```

これ以降は、私たちの特製の条件をより短い演算子形式を使って作成することが出来ます。

```php
$query->andWhere(['ALL>', ['posts', 'comments', 'reactions', 'subscriptions'], $minValue]);
```

お気付きのことと思いますが、ここには二つの概念があります。Expression(式)と Condition(条件)です。
[[yii\db\ExpressionInterface]] は、それを構築するために [[yii\db\ExpressionBuilderInterface]]
を実装した式ビルダクラスを必要とするオブジェクトを特徴付けるインタフェイスです。
また [[yii\db\condition\ConditionInterface]] は、[[yii\db\ExpressionInterface|ExpressionInterface]] を拡張して、
上述されたように配列形式の定義から作成できるオブジェクトに対して使用されるべきものですが、同様にビルダを必要とするものです。

要約すると、

- Expression(式) – データセットのためのデータ転送オブジェクトであり、最終的に何らかの SQL 文にコンパイルされる。
  (演算子、文字列、配列、JSON、等)
- Condition(条件) – Expression(式) のスーパーセットで、一つの SQL 条件にコンパイルすることが可能な複数の式
  (またはスカラ値)の集合。

[[yii\db\ExpressionInterface|ExpressionInterface]] を実装する独自のクラスを作成して、
データを SQL 文に変換することの複雑さを隠蔽することが出来ます。
[次の記事](db-active-record.md) では、式について、さらに多くの例を学習します。
