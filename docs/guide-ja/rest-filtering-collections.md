コレクションのフィルタリング
============================

バージョン 2.0.13 以降、リソースのコレクションは [[yii\data\DataFilter]] コンポーネントを使ってフィルタにかけることが出来ます。
このコンポーネントは、リクエスト経由で渡されるフィルタ条件の構築を可能にし、そして、拡張バージョンの [[yii\data\ActiveDataFilter]] の助力によって、
[[yii\db\QueryInterface::where()]] にとって適切な形式でフィルタ条件を使う事を可能にします。


## データ・プロバイダをフィルタリングのために構成する <span id="configuring-data-provider-for-filtering"></span>

[コレクション](rest-resources.md#collections) のセクションで言及されているように、 
[データ・プロバイダ](output-data-providers#data-providers) を使うと、並べ替えてページ付けしたリソースのリストを出力することが出来ます。
また、データ・プロバイダを使って、そのリストをフィルタにかけることも出来ます。

```php
$filter = new ActiveDataFilter([
    'searchModel' => 'app\models\PostSearch',
]);

$filterCondition = null;
// どのようなソースからでもフィルタをロードすることが出来ます。例えば、
// リクエスト・ボディの JSON からロードしたい場合は、
// 下記のように Yii::$app->request->getBodyParams() を使います。
if ($filter->load(Yii::$app->request->get())) { 
    $filterCondition = $filter->build();
    if ($filterCondition === false) {
        // シリアライザがフィルタの抽出でエラーを出すかもしれない
        return $filter;
    }
}

$query = Post::find();
if ($filterCondition !== null) {
    $query->andWhere($filterCondition);
}

return new ActiveDataProvider([
    'query' => $query,
]);
```

`PostSearch` モデルが、どのプロパティと値がフィルタリングのために許容されるかを定義する役目を担います。

```php
use yii\base\Model;

class PostSearch extends Model 
{
    public $id;
    public $title;
    
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['title', 'string', 'min' => 2, 'max' => 200],            
        ];
    }
}
```

そこで特別なビジネス・ロジックが必要でない場合には、検索ルールのためのスタンドアロンなモデルを準備する代わりに、
[[yii\base\DynamicModel]] を使うことが出来ます。

```php
$filter = new ActiveDataFilter([
    'searchModel' => (new DynamicModel(['id', 'title']))
        ->addRule(['id'], 'integer')
        ->addRule(['title'], 'string', ['min' => 2, 'max' => 200]),
]);
```

`searchModel` を定義することは、エンド・ユーザに許容するフィルタ条件を制御するために欠かすことが出来ません。


## リクエストのフィルタリング <span id="filtering-request"></span>

通常、エンド・ユーザは許容された一つ以上のメソッド（これらはAPIドキュメントに明示的に記述されるべきものです）を使ってフィルタリング条件をリクエストで提供するものと期待されます。
例えば、フィルタリングが JSON を使って POST メソッドで操作される場合は、
下記と似たようなものになります。

```json
{
    "filter": {
        "id": {"in": [2, 5, 9]},
        "title": {"like": "cheese"}
    }
}
```

上記の条件は、次のように解釈されます :
- `id` は、2, 5, または 9 でなければならず、**かつD**
- `title` は `cheese` という語を含まなければならない。

同一の条件が GET クエリの一部として送信される場合は、次のようになります :

```
?filter[id][in][]=2&filter[id][in][]=5&filter[id][in][]=9&filter[title][like]=cheese
```

デフォルトの `filter` キー・ワードは、[[yii\data\DataFilter::$filterAttributeName]] を設定して変更することが出来ます。


## フィルタ制御キーワード <span id="filter-control-keywords"></span>

許容されているフィルタ制御キーワードは下記の通りです :

| キーワード     |     意味      |
|:--------------:|:-------------:|
|     `and`      |     `AND`     |
|      `or`      |     `OR`      |
|     `not`      |     `NOT`     |
|      `lt`      |      `<`      |
|      `gt`      |      `>`      |
|     `lte`      |     `<=`      |
|     `gte`      |     `>=`      |
|      `eq`      |      `=`      |
|     `neq`      |     `!=`      |
|      `in`      |     `IN`      |
|     `nin`      |   `NOT IN`    |
|     `like`     |    `LIKE`     |

オプションの [[yii\data\DataFilter::$filterControls]] を拡張して、上記のリストを拡張することが出来ます。
例えば、下記のように、同一のフィルタ構築キーにいくつかのキーワードを与えて、複数のエイリアスを作成することが出来ます :

```php
[
    'eq' => '=',
    '=' => '=',
    '==' => '=',
    '===' => '=',
    // ...
]
```

未定義のキーワードは、すべて、フィルタ制御とは認識されず、属性名として扱われることに注意して下さい。
制御キーワードと属性名の衝突は避けなければなりません。
（例えば、制御キーワードとしての 'like' と属性名としての 'like' が存在する場合、そのような属性に対して条件を指定することは不可能です。）

> Note: フィルタ制御を指定する時に、あなたのAPIが使用する実際のデータ交換形式に留意しましょう。
  すべての指定された制御キーワードがその形式にとって妥当なものであることを確認して下さい。
  例えば、XML ではタグ名は Letter クラスの文字でしか開始出来ませんから、`>`, `=`, `$gt` 等は XML スキーマに違反することになります。

> Note: 新しいフィルタ制御キーワードを追加する時は、演算子の結合規則および所期の動作に基づいて、期待されるクエリ結果を得るためには
  [[yii\data\DataFilter::$conditionValidators]] および/または [[yii\data\DataFilter::$operatorTypes]] をも
  更新する必要があるかどうか、必ず確認して下さい。


## Null 値の扱い <span id="handling-the-null-values"></span>

JSON の式野中では `null` を使う事は容易ですが、文字通りの 'null' を文字列としての "null" と混乱させずに GET クエリを使ってを送信することは不可能です。
バージョン 2.0.40 以降では、[[yii\data\DataFilter::$nullValue]] オプションを使って、文字通りの `null` に置換される単語(デフォルトでは、"NULL")を構成することが出来ます。


## 属性のエイリアス <span id="aliasing-attributes"></span>

属性を別の名前で呼びたい場合や、結合された DB テーブルでフィルタをかけたい場合に、
[[yii\data\DataFilter::$attributeMap]] を使ってエイリアスのマップを設定することが出来ます。

```php
[
    'carPart' => 'car_part', // car_part 属性でフィルタするために carPart が使われる
    'authorName' => '{{author}}.[[name]]', // 結合された author テーブルの name 属性でフィルタするために authorName が使われる
]
```

## `ActiveController` のためにフィルタを構成する <span id="configuring-filters-for-activecontroller"></span>

[[yii\rest\ActiveController]] には一般的な一揃いの REST アクションが失踪されていますが、
[[yii\rest\IndexAction::$dataFilter]] プロパティによってフィルタを使うことも簡単に出来ます。
可能な方法のうちの一つは [[yii\rest\ActiveController::actions()]] を使ってそうすることです :

```php
public function actions()
{
    $actions = parent::actions();
    
    $actions['index']['dataFilter'] = [
        'class' => \yii\data\ActiveDataFilter::class,
        'attributeMap' => [
            'clockIn' => 'clock_in',
        ],
        'searchModel' => (new DynamicModel(['id', 'clockIn']))->addRule(['id', 'clockIn'], 'integer', ['min' => 1]),
    ];
    
    return $actions;
}
```

これで(`index` アクションによってアクセス可能な)コレクションを `id` と `clockIn` プロパティによってフィルタすることが出来ます。
