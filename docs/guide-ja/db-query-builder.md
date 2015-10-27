クエリビルダ
============

[データベースアクセスオブジェクト](db-dao.md) の上に構築されているクエリビルダは、SQL 文をプログラム的に、かつ、DBMS の違いを意識せずに作成することを可能にしてくれます。
クエリビルダを使うと、生の SQL 文を書くことに比べて、より読みやすい SQL 関連のコードを書き、より安全な SQL 文を生成することが容易になります。

通常、クエリビルダの使用は、二つのステップから成ります。

1. SELECT SQL 文のさまざまな部分 (例えば、`SELECT`、`FROM`) を表現する [[yii\db\Query]] オブジェクトを構築する。
2. [[yii\db\Query]] のクエリメソッド (例えば `all()`) を実行して、データベースからデータを取得する。

次のコードは、クエリビルダを使用する典型的な方法を示すものです。

```php
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->all();
```

上記のコードは、次の SQL 文を生成して実行します。
ここでは、`:last_name` パラメータは `'Smith'` という文字列にバインドされています。

```sql
SELECT `id`, `email` 
FROM `user`
WHERE `last_name` = :last_name
LIMIT 10
```

> Info|情報: 通常は、[[yii\db\QueryBuilder]] ではなく、主として [[yii\db\Query]] を使用します。
  前者は、クエリメソッドの一つを呼ぶときに、後者によって黙示的に起動されます。
  [[yii\db\QueryBuilder]] は、DBMS に依存しない [[yii\db\Query]] オブジェクトから、DBMS に依存する SQL 文を生成する (例えば、テーブルやカラムの名前を DBMS ごとに違う方法で引用符で囲む) 役割を負っているクラスです。

## クエリを構築する <span id="building-queries"></span>

[[yii\db\Query]] オブジェクトを構築するために、さまざまなクエリ構築メソッドを呼んで、SQL 文のさまざまな部分を定義します。
これらのメソッドの名前は、SQL 文の対応する部分に使われる SQL キーワードに似たものになっています。
例えば、SQL 文の `FROM` の部分を定義するためには、`from()` メソッドを呼び出します。
クエリ構築メソッドは、すべて、クエリオブジェクトそのものを返しますので、複数の呼び出しをチェーンしてまとめることが出来ます。

以下で、それぞれのクエリ構築メソッドの使用方法を説明しましょう。


### [[yii\db\Query::select()|select()]] <span id="select"></span>

[[yii\db\Query::select()|select()]] メソッドは、SQL 文の `SELECT` 句を定義します。
選択されるカラムは、次のように、配列または文字列として指定することが出来ます。
選択されるカラムの名前は、クエリオブジェクトから SQL 文が生成されるときに、自動的に引用符で囲まれます。

```php
$query->select(['id', 'email']);

// 次と等価

$query->select('id, email');
```

選択されるカラム名は、生の SQL 文を書くときにするように、テーブル接頭辞 および/または カラムのエイリアスを含むことが出来ます。
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
カンマを含む DB 式をセレクトする場合は、自動的に引用符で囲む機能が誤動作しないように、配列形式を使わなければなりません。
例えば、

```php
$query->select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']); 
```

バージョン 2.0.1 以降では、サブクエリもセレクトすることが出来ます。
各サブクエリは、[[yii\db\Query]] オブジェクトの形で指定しなければなりません。
例えば、

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


### [[yii\db\Query::from()|from()]] <span id="from"></span>

[[yii\db\Query::from()|from()]] メソッドは、SQL 文の `FROM` 句を定義します。例えば、

```php
// SELECT * FROM `user`
$query->from('user');
```

セレクトの対象になる (一つまたは複数の) テーブルは、文字列または配列として指定することが出来ます。
テーブル名は、生の SQL 文を書くときにするように、スキーマ接頭辞 および/または テーブルエイリアスを含むことが出来ます。例えば、

```php
$query->from(['public.user u', 'public.post p']);

// 次と等価

$query->from('public.user u, public.post p');
```

配列形式を使う場合は、次のように、配列のキーを使ってテーブルエイリアスを指定することも出来ます。

```php
$query->from(['u' => 'public.user', 'p' => 'public.post']);
```

テーブル名の他に、[[yii\db\Query]] オブジェクトの形で指定することによって、サブクエリをセレクトの対象とすることも出来ます。
例えば、

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');

// SELECT * FROM (SELECT `id` FROM `user` WHERE status=1) u 
$query->from(['u' => $subQuery]);
```


### [[yii\db\Query::where()|where()]] <span id="where"></span>

[[yii\db\Query::where()|where()]] メソッドは、SQL 文の `WHERE` 句を定義します。
`WHERE` の条件を指定するために、次の三つの形式から一つを選んで使うことが出来ます。

- 文字列形式、例えば、`'status=1'`
- ハッシュ形式、例えば、`['status' => 1, 'type' => 2]`
- 演算子形式、例えば、`['like', 'name', 'test']`


#### 文字列形式 <span id="string-format"></span>

文字列形式は、非常に単純な条件を定義する場合に最適です。
これは、生の SQL を書いている場合と同じように動作します。
例えば、

```php
$query->where('status=1');

// あるいは、パラメータバインディングを使って、動的にパラメータをバインドする
$query->where('status=:status', [':status' => $status]);
```

次のように、条件式に変数を直接に埋め込んではいけません。
特に、変数の値がユーザの入力に由来する場合、あなたのアプリケーションを SQL インジェクション攻撃にさらすことになりますので、してはいけません。

```php
// 危険! $status が整数であることが絶対に確実でなければ、してはいけない。
$query->where("status=$status");
```

パラメータバインディングを使う場合は、[[yii\db\Query::params()|params()]] または [[yii\db\Query::addParams()|addParams()]] を使って、パラメータの指定を分離することが出来ます。

```php
$query->where('status=:status')
    ->addParams([':status' => $status]);
```


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

ご覧のように、クエリビルダは頭が良いので、null や配列である値も、適切に処理します。

次のように、サブクエリをハッシュ形式で使うことも出来ます。

```php
$userQuery = (new Query())->select('id')->from('user');

// ...WHERE `id` IN (SELECT `id` FROM `user`)
$query->where(['id' => $userQuery]);
```

#### 演算子形式 <span id="operator-format"></span>

演算子形式を使うと、任意の条件をプログラム的な方法で指定することが出来ます。
これは次の形式を取るものです。

```php
[演算子, オペランド1, オペランド2, ...]
```

ここで、各オペランドは、文字列形式、ハッシュ形式、あるいは、再帰的に演算子形式として指定することが出来ます。
そして、演算子には、次のどれか一つを使うことが出来ます。

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

- `>`、`<=`、その他、二つのオペランドを取る有効な DB 演算子全て: 最初のオペランドはカラム名、第二のオペランドは値でなければなりません。
  例えば、`['>', 'age', 10]` は `age>10` を生成します。


#### 条件を追加する <span id="appending-conditions"></span>

[[yii\db\Query::andWhere()|andWhere()]] または [[yii\db\Query::orWhere()|orWhere()]] を使って、既存の条件に別の条件を追加することが出来ます。
これらのメソッドを複数回呼んで、複数の条件を別々に追加することが出来ます。
例えば、

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

#### フィルタ条件 <span id="filter-conditions"></span>

ユーザの入力に基づいて `WHERE` の条件を構築する場合、普通は、空の入力値は無視したいものです。
例えば、ユーザ名とメールアドレスによる検索が可能な検索フォームにおいては、ユーザが username/email のインプットフィールドに何も入力しなかった場合は、username/email の条件を無視したいでしょう。
[[yii\db\Query::filterWhere()|filterWhere()]] メソッドを使うことによって、この目的を達することが出来ます。

```php
// $username と $email はユーザの入力による
$query->filterWhere([
    'username' => $username,
    'email' => $email,
]);
```

[[yii\db\Query::filterWhere()|filterWhere()]] と [[yii\db\Query::where()|where()]] の唯一の違いは、前者は [ハッシュ形式](#hash-format) の条件において提供された空の値を無視する、という点です。
従って、`$email` が空で `$sername` がそうではない場合は、上記のコードは、結果として `...WHERE username=:username` という SQL になります。

> Info|情報: 値が空であると見なされるのは、null、空の配列、空の文字列、または空白のみを含む文字列である場合です。

[[yii\db\Query::andWhere()|andWhere()]] または [[yii\db\Query::orWhere()|orWhere()]] と同じように、[[yii\db\Query::andFilterWhere()|andFilterWhere()]] または [[yii\db\Query::orFilterWhere()|orFilterWhere()]] を使って、既存の条件に別のフィルタ条件を追加することも出来ます。

### [[yii\db\Query::orderBy()|orderBy()]] <span id="order-by"></span>

[[yii\db\Query::orderBy()|orderBy()]] メソッドは SQL 文の `ORDER BY` 句を指定します。例えば、


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

> Note|注意: `ORDER BY` が何らかの DB 式を含む場合は、配列形式を使わなければなりません。

[[yii\db\Query::addOrderBy()|addOrderBy()]] を呼んで、`ORDER BY' 句にカラムを追加することが出来ます。
例えば、

```php
$query->orderBy('id ASC')
    ->addOrderBy('name DESC');
```

### [[yii\db\Query::groupBy()|groupBy()]] <span id="group-by"></span>

[[yii\db\Query::groupBy()|groupBy()]] メソッドは SQL 文の `GROUP BY` 句を指定します。
例えば、

```php
// ... GROUP BY `id`, `status`
$query->groupBy(['id', 'status']);
```

`GROUP BY` が単純なカラム名だけを含む場合は、生の SQL 文を書くときにするように、文字列を使って指定することが出来ます。
例えば、

```php
$query->groupBy('id, status');
```

> Note|注意: `GROUP BY` が何らかの DB 式を含む場合は、配列形式を使わなければなりません。
 
[[yii\db\Query::addGroupBy()|addGroupBy()]] を呼んで、`GROUP BY` 句にカラムを追加することが出来ます。
例えば、

```php
$query->groupBy(['id', 'status'])
    ->addGroupBy('age');
```


### [[yii\db\Query::having()|having()]] <span id="having"></span>

[[yii\db\Query::having()|having()]] メソッドは SQL 文の `HAVING` 句を指定します。
このメソッドが取る条件は、[where()](#where) と同じ方法で指定することが出来ます。
例えば、

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

[[yii\db\Query::limit()|limit()]] と [[yii\db\Query::offset()|offset()]] のメソッドは、SQL 文の `LIMIT` と `OFFSET` 句を指定します。
例えば、
 
```php
// ... LIMIT 10 OFFSET 20
$query->limit(10)->offset(20);
```

無効な上限やオフセット (例えば、負の数) を指定した場合は、無視されます。

> Info|情報: `LIMIT` と `OFFSET` をサポートしていない DBMS (例えば MSSQL) に対しては、クエリビルダが `LIMIT`/`OFFSET` の振る舞いをエミュレートする SQL 文を生成します。


### [[yii\db\Query::join()|join()]] <span id="join"></span>

[[yii\db\Query::join()|join()]] メソッドは SQL 文の `JOIN` 句を指定します。例えば、
 
```php
// ... LEFT JOIN `post` ON `post`.`user_id` = `user`.`id`
$query->join('LEFT JOIN', 'post', 'post.user_id = user.id');
```

[[yii\db\Query::join()|join()]] メソッドは、四つのパラメータを取ります。
 
- `$type`: 結合のタイプ、例えば、`'INNER JOIN'`、`'LEFT JOIN'`。
- `$table`: 結合されるテーブルの名前。
- `$on`: オプション。結合条件、すなわち、`ON` 句。
   条件の指定方法の詳細については、[where()](#where) を参照してください。
- `$params`: オプション。結合条件にバインドされるパラメータ。

`INNER JOIN`、`LEFT JOIN` および `RIGHT JOIN` を指定するためには、それぞれ、次のショートカットメソッドを使うことが出来ます。

- [[yii\db\Query::innerJoin()|innerJoin()]]
- [[yii\db\Query::leftJoin()|leftJoin()]]
- [[yii\db\Query::rightJoin()|rightJoin()]]

例えば、

```php
$query->leftJoin('post', 'post.user_id = user.id');
```

複数のテーブルを結合するためには、テーブルごとに一回ずつ、上記の結合メソッドを複数回呼び出します。

テーブルを結合することに加えて、サブクエリを結合することも出来ます。
そうするためには、結合されるべきサブクエリを [[yii\db\Query]] オブジェクトとして指定します。
例えば、

```php
$subQuery = (new \yii\db\Query())->from('post');
$query->leftJoin(['u' => $subQuery], 'u.id = author_id');
```

この場合、サブクエリを配列に入れて、配列のキーを使ってエイリアスを指定しなければなりません。


### [[yii\db\Query::union()|union()]] <span id="union"></span>

[[yii\db\Query::union()|union()]] メソッドは SQL 文の `UNION` 句を指定します。例えば、

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


## クエリメソッド <span id="query-methods"></span>

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

上記のメソッドの全ては、オプションで、DB クエリの実行に使用されるべき [[yii\db\Connection|DB 接続]] を表す `$db` パラメータを取ることが出来ます。
このパラメータを省略した場合は、DB 接続として `db` [アプリケーションコンポーネント](structure-application-components.md) が使用されます。
次に `count()` クエリメソッドを使う例をもう一つ挙げます。

```php
// 実行される SQL: SELECT COUNT(*) FROM `user` WHERE `last_name`=:last_name
$count = (new \yii\db\Query())
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->count();
```

あなたが [[yii\db\Query]] のクエリメソッドを呼び出すと、実際には、内部的に次の仕事がなされます。

* [[yii\db\QueryBuilder]] を呼んで、[[yii\db\Query]] の現在の構成に基づいた SQL 文を生成する。
* 生成された SQL 文で [[yii\db\Command]] オブジェクトを作成する。
* [[yii\db\Command]] のクエリメソッド (例えば `queryAll()`) を呼んで、SQL 文を実行し、データを取得する。

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
この目的は、[[yii\db\Query::all()|all()]] の前に [[yii\db\Query::indexBy()|indexBy()]] を呼ぶことによって達成することが出来ます。
例えば、

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

この無名関数は、現在の行データを含む `$row` というパラメータを取り、現在の行のインデックス値として使われるスカラ値を返さなくてはなりません。


## バッチクエリ <span id="batch-query"></span>

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
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users は "username" カラムでインデックスされている
}

foreach ($query->each() as $username => $user) {
}
```
