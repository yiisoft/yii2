ページネーション
================

一つのページに表示するにはデータの数が多すぎるという場合に、データを複数のページに分割して、それぞれのページでは一部分だけを表示する、
という戦略がよく使われます。この戦略が *ページネーション* として知られるものです。

Yii は [[yii\data\Pagination]] オブジェクトを使って、ページネーションのスキームに関する情報を表します。具体的に言えば、

* [[yii\data\Pagination::$totalCount|totalCount]] データ・アイテムの総数を指定します。
  通常、データ・アイテムの総数は、一つのページを表示することが可能なデータ・アイテムの数より、ずっと大きなものになることに注意してください。
* [[yii\data\Pagination::$pageSize|pageSize]] 各ページが含むアイテムの数を指定します。
  デフォルト値は 20 です。
* [[yii\data\Pagination::$page|page]] 現在のページ番号 (0 から始まる) を示します。デフォルト値は 0 であり、最初のページを意味します。

これらの情報を全て定義した [[yii\data\Pagination]] オブジェクトを使って、データの一部分を取得して表示することが出来ます。
例えば、データ・プロバイダからデータを取得する場合であれば、ページネーションによって提供される値によって、それに対応する
`OFFSET` と `LIMIT` の句を DB クエリに指定することが出来ます。下記に例を挙げます。

```php
use yii\data\Pagination;

// status = 1 である全ての記事を取得する DB クエリを構築する
$query = Article::find()->where(['status' => 1]);

// 記事の総数を取得する (ただし、記事のデータはまだ取得しない)
$count = $query->count();

// 記事の総数を使ってページネーション・オブジェクトを作成する
$pagination = new Pagination(['totalCount' => $count]);

// ページネーションを使ってクエリの OFFSET と LIMIT を修正して記事を取得する
$articles = $query->offset($pagination->offset)
    ->limit($pagination->limit)
    ->all();
```

上記の例で返される記事のページ番号はどうなるでしょう? それは `page` という名前のクエリ・パラメータがリクエストに含まれるかどうかによって決ります。
デフォルトでは、ページネーション・オブジェクトは [[yii\data\Pagination::$page|page]] に `page` パラメータの値をセットしようと試みます。
そして、このパラメータが提供されていない場合には、デフォルト値である 0 が使用されます。

ページネーションをサポートする UI 要素の構築を容易にするために、Yii はページ・ボタンのリストを表示する [[yii\widgets\LinkPager]] ウィジェットを提供しています。
これは、ユーザがページ・ボタンをクリックして、どのページを表示すべきかを指示することが出来るものです。
このウィジェットは、ページネーション・オブジェクトを受け取って、現在のページ番号が何であるかを知り、何個のページ・ボタンを表示すべきかを知ります。
例えば、

```php
use yii\widgets\LinkPager;

echo LinkPager::widget([
    'pagination' => $pagination,
]);
```

UI 要素を手動で構築したい場合は、[[yii\data\Pagination::createUrl()]] を使って、いろんなページに跳ぶ URL を作成することが出来ます。
このメソッドは page パラメータを要求し、その page パラメータを含む正しくフォーマットされた URL を作成します。
例えば、

```php
// 作成される URL が使用すべきルートを指定する
// 指定しない場合は、現在リクエストされているルートが使用される
$pagination->route = 'article/index';

// /index.php?r=article%2Findex&page=100 を表示
echo $pagination->createUrl(100);

// /index.php?r=article%2Findex&page=101 を表示
echo $pagination->createUrl(101);
```

> Tip: `page` クエリ・パラメータの名前をカスタマイズするためには、ページネーション・オブジェクトを作成する際に
[[yii\data\Pagination::pageParam|pageParam]] プロパティを構成します。
