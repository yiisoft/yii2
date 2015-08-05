数据小部件
============

Yii提供了一套 [widgets](structure-widgets.md) 小部件，这些小部件可以用于显示数据。
[DetailView](#detail-view) 小部件能够用于显示一条单记录数据，
[ListView](#list-view) 和 [GridView](#grid-view) 小部件能够用于显示一个拥有分页、排序和过滤功能的一个列表或者表格。



DetailView <a name="detail-view"></a>
----------

[[yii\widgets\DetailView|DetailView]] 小部件显示的是单一 [[yii\widgets\DetailView::$model|model]] 数据的详情。

它非常适合用常规格式显示一个模型（例如在一个表格的一行中显示模型的每个属性）。
这里说的模型可以是 [[\yii\base\Model]] 或者其子类的一个实例，例如子类 [active record](db-active-record.md)，也可以是一个关联数组。

DetailView使用 [[yii\widgets\DetailView::$attributes|$attributes]] 属性来决定显示模型哪些属性以及如何格式化。
可用的格式化选项，见 [formatter section](output-formatting.md) 章节。

一个典型的DetailView的使用方法如下：
 
```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',               // title attribute (in plain text)
        'description:html',    // description attribute formatted as HTML
        [                      // the owner name of the model
            'label' => 'Owner',
            'value' => $model->owner->name,
        ],
        'created_at:datetime', // creation date formatted as datetime
    ],
]);
```

ListView <a name="list-view"></a>
--------

[[yii\widgets\ListView|ListView]] 小部件用于显示 [data provider](output-data-providers.md) 提供的数据。
每个数据模型用指定 [[yii\widgets\ListView::$itemView|view file]] 文件来渲染。
因为它提通过开箱即用式的分页、排序以及过滤这样一些特性，所以它可以很方便地同时为最终用户显示信息和创建数据管理界面。


一个典型的用法如下例所示：

```php
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
]);
```

`_post` 视图文件可能包含如下代码：


```php
<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>
<div class="post">
    <h2><?= Html::encode($model->title) ?></h2>
    
    <?= HtmlPurifier::process($model->text) ?>    
</div>
```

在上面的视图文件中，当前的数据模型 `$model` 是可用的。另外，下面的这些变量也是可用的：

- `$key`：混合类型，键的值是与数据项相关联的。
- `$index`：整型，是由数据提供者返回的数组中基于0的数据项的索引。
- `$widget`：类型是ListView，是小部件的实例。

假如你需要传递附加的数据到每一个视图中，你可以像下面这样用 [[yii\widgets\ListView::$viewParams|$viewParams]] 属性传递键值对：


```php
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
    'viewParams' => [
        'fullView' => true,
        'context' => 'main-page',
        // ...
    ],
]);
```

在视图中，这些附加数据也是可以作为变量来使用的。


GridView <a name="grid-view"></a>
--------

数据网格或者网格视图小部件是Yii中最强大的部件之一。如果你需要快速建立系统的管理部分，这将非常有用。
数据从 [data provider](output-data-providers.md) 数据提供者中取出来并且使用 [[yii\grid\GridView::columns|columns]] 在一个表格表单中渲染每一行的数据。


表中的每一行代表一个数据项的数据，并且一列通常表示该项的属性（某些列可以对应于属性或静态文本的复杂表达式）。


使用GridView的最少代码如下：

```php
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

上面的代码首先创建了一个数据提供者，然后使用GridView显示每一行的每个属性，每一行的数据是从数据提供者取来的。
展现出来的表相当完美地经过排序以及分页功能的包装。


### Grid columns

表格的列是通过 [[yii\grid\Column]] 类来配置的，这个类是在网格视图配置项中的 [[yii\grid\GridView::columns|columns]] 属性配置的。
根据不同列的类型和设置能够以不同地方式展现数据。对于默认的类 [[yii\grid\DataColumn]]，能够展现一个模型的属性，并且可以被排序和过滤。



```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        // Simple columns defined by the data contained in $dataProvider.
        // Data from the model's column will be used.
        'id',
        'username',
        // More complex one.
        [
            'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
            'value' => function ($data) {
                return $data->name; // $data['name'] for array data, e.g. using SqlDataProvider.
            },
        ],
    ],
]);
```

大家注意啊，假如说配置中的 [[yii\grid\GridView::columns|columns]] 部分没有被指定，那么Yii会试图展示所有可能的来自数据提供者模型的列。



### Column classes

通过使用不同列对应的类，可以自定义网格列：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- here
            // you may configure additional properties here
        ],
```

接下来我们将回顾一下Yii提供的针对列的类，此外你还可以创建你自己的对应列的类。

每个列类是从 [[yii\grid\Column]] 扩展而来，以便于当配置网格列的时候，你可以设置一写公共的选项。


- [[yii\grid\Column::header|header]] 允许为头部的一行设置内容。
- [[yii\grid\Column::footer|footer]] 允许为尾部的一行设置内容。
- [[yii\grid\Column::visible|visible]] 定义某个列是否应该可见
- [[yii\grid\Column::content|content]] 允许你通过一个有效的PHP回调来为一行返回数据，格式如下：

  ```php
  function ($model, $key, $index, $column) {
      return 'a string';
  }
  ```
  
你可以通过数组来指定各种容器式的HTML选项：

- [[yii\grid\Column::headerOptions|headerOptions]]
- [[yii\grid\Column::footerOptions|footerOptions]]
- [[yii\grid\Column::filterOptions|filterOptions]]
- [[yii\grid\Column::contentOptions|contentOptions]]


#### Data column <span id="data-column"></span>

[[yii\grid\DataColumn|Data column]] 用于显示和排序数据。这是默认的列的类型，所以使用它来指定类的时候可以省略。


数据列主要通过 [[yii\grid\DataColumn::format|format]] 属性来设置的。它的值对应于 `formatter` [application component](structure-application-components.md) 应用组件里面的一些方法，
默认是使用 [[\yii\i18n\Formatter|Formatter]] 应用组件：

```php
echo GridView::widget([
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'text'
        ],
        [
            'attribute' => 'birthday',
            'format' => ['date', 'php:Y-m-d']
        ],
    ],
]); 
```

在上面的代码中，`text` 对应于 [[\yii\i18n\Formatter::asText()]]。列的值作为第一个参数传递。在第二列的定义中，`date` 对应于 [[\yii\i18n\Formatter::asDate()]]。
当'php:Y-m-d'作为第二个参数值的时候，该列的值也是通过第一个参数来传递的。


对于一系列可用的格式化组件可以参考 [section about Data Formatting](output-formatting.md)。

对于配置数据列，还有一个快捷的格式化方法，具体已经在 [[yii\grid\GridView::columns|columns]] API文档中有详细描述。



#### Action column

[[yii\grid\ActionColumn|Action column]] 用于显示一些动作按钮诸如每一行的更新、删除操作。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\ActionColumn',
            // you may configure additional properties here
        ],
```

你可以配置的可用属性如下：

- [[yii\grid\ActionColumn::controller|controller]] 是一个应该执行一些动作的控制器的ID。
  如果没有设置，它将使用当前正在执行的控制器。
- [[yii\grid\ActionColumn::template|template]] 定义了用于在动作列中构建了每个单元格中的模板。
  在大括号内的令牌被当做是控制器的动作ID (在动作列的上下文中也称作*button names*)。
  它们将会被对应的按钮渲染回调做替换，这些回调是在 [[yii\grid\ActionColumn::$buttons|buttons]] 中指定的。
  例如，令牌 `{view}` 将被 `buttons['view']` 回调的结果所替换。
  假如说没有回调，令牌将被一个空字符串替换。默认的令牌有 `{view} {update} {delete}`。
- [[yii\grid\ActionColumn::buttons|buttons]] 是一个按钮渲染回调数组。这些数组的键是按钮的名字（没有花括号），并且值是对应的按钮渲染回调函数。
  这些回调函数的使用应该像下面这种结构：

  ```php
  function ($url, $model, $key) {
      // return the button HTML code
  }
  ```

  在上面的代码中，`$url` 是列为按钮创建的URL，`$model`是正在为当前行渲染的模型对象，并且 `$key` 是在数据提供者数组中模型的键。


- [[yii\grid\ActionColumn::urlCreator|urlCreator]] 是使用指定的模型信息来创建一个按钮URL的回调函数。
  回调的签名应该和 [[yii\grid\ActionColumn::createUrl()]] 是一样的。
  假如这个属性没有设置，按钮的URLs将使用 [[yii\grid\ActionColumn::createUrl()]] 来创建。


#### Checkbox column

[[yii\grid\CheckboxColumn|Checkbox column]] 展现一列的复选框。

为了添加复选框的一列到网格视图中，将它添加到 [[yii\grid\GridView::$columns|columns]] 中的配置如下：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        // ...
        [
            'class' => 'yii\grid\CheckboxColumn',
            // you may configure additional properties here
        ],
    ],
```

用户可能通过点击一些复选框来选择网格中的一些行。被选择的行可能通过调用下面的JavaScript代码来获得：


```javascript
var keys = $('#grid').yiiGridView('getSelectedRows');
// keys is an array consisting of the keys associated with the selected rows
```

#### Serial column

[[yii\grid\SerialColumn|Serial column]] 渲染以 `1` 开头并且靠前的行数。

使用方法和下面的例子一样简单：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'], // <-- here
        // ...
```


### Sorting data

> 注意：这部分正在开发中。
>
> - https://github.com/yiisoft/yii2/issues/1576

### Filtering data

用于过滤数据的网格视图需要一个有输入表单，过滤表单形式的 [model](structure-models.md) 模型，并且能够调整数据提供者查询方面的搜索条件。
通常做法是使用 [active records](db-active-record.md) 活动记录来创建一个能够提供所需功能的搜索模型类（可以使用 [Gii](start-gii.md) 来生成）。
这个类为搜索定义了验证规则并且提供了一个将会返回数据提供者的 `search()` 方法。



为了给 `Post` 模型增加搜索能力，我们可以像下面的例子一样创建 `PostSearch` 模型：

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{
    public function rules()
    {
        // only fields in rules() are searchable
        return [
            [['id'], 'integer'],
            [['title', 'creation_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Post::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', $this->title])
              ->andFilterWhere(['like', 'creation_date', $this->creation_date]);

        return $dataProvider;
    }
}

```

你可以在控制器中使用这个方法为网格视图获取数据提供者：

```php
$searchModel = new PostSearch();
$dataProvider = $searchModel->search(Yii::$app->request->get());

return $this->render('myview', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);
```

然后你在视图中分配 `$dataProvider` 和 `$searchModel` 这两个变量到网格视图小部件中：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        // ...
    ],
]);
```


### Working with model relations

当我们在一个网格视图中展现活动数据的时候，你可能会遇到这种情况，就是显示关联的列的值，诸如：提交作者的名字而非仅仅是他的 `id`。
当 `Post` 模型有一个关联的属性名叫 `author` 并且作者模型有一个属性叫 `name`，那么你可以通过在 [[yii\grid\GridView::$columns]] 中定义属性名为 `author.name` 来解决上面的情况。
网格视图将显示作者名，但是默认是不启用排序和过滤的。
你不得不调整 `PostSearch` 模型，在最后一节中已经介绍了如何添加此功能。



为了使关联列能够排序，你不得不连表，并且添加排序规则到数据提供者的排序组件中：


```php
$query = Post::find();
$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);

// join with relation `author` that is a relation to the table `users`
// and set the table alias to be `author`
$query->joinWith(['author' => function($query) { $query->from(['author' => 'users']); }]);
// enable sorting for the related column
$dataProvider->sort->attributes['author.name'] = [
    'asc' => ['author.name' => SORT_ASC],
    'desc' => ['author.name' => SORT_DESC],
];

// ...
```

过滤也需要像上面一样调用joinWith方法。你也需要在属性中定义可查询的列和规则，就像下面这样：

```php
public function attributes()
{
    // add related fields to searchable attributes
    return array_merge(parent::attributes(), ['author.name']);
}

public function rules()
{
    return [
        [['id'], 'integer'],
        [['title', 'creation_date', 'author.name'], 'safe'],
    ];
}
```

然后在 `search()` 方法中，你仅需要添加另外一个过滤条件：

```php
$query->andFilterWhere(['LIKE', 'author.name', $this->getAttribute('author.name')]);
```

> 信息：在上面的代码中，我们使用相同的字符串作为关联名称和表的别名；
> 然而，当你的表别名和关联名称不相同的时候，你将不得不注意在哪使用你的别名，在哪使用你的关联名称。
> 一个简单的规则是在每个地方使用别名来构建数据库查询和所有其他地方定义（诸如：`attributes()` 和 `rules()`）的关联名称。
> 
>
> 例如，假如你使用别名 `au` 作为作者关系表，那么联查语句就像下面这样：
>
> ```php
> $query->joinWith(['author' => function($query) { $query->from(['au' => 'users']); }]);
> ```
> 当别名已经在关系定义中定义了，也可以直接调用 `$query->joinWith(['author']);`。
>
> 在筛选条件中，别名必须使用，但属性名称保持不变：
>
> ```php
> $query->andFilterWhere(['LIKE', 'au.name', $this->getAttribute('author.name')]);
> ```
> 
> 排序定义也同样如此：
>
> ```php
> $dataProvider->sort->attributes['author.name'] = [
>      'asc' => ['au.name' => SORT_ASC],
>      'desc' => ['au.name' => SORT_DESC],
> ];
> ```
>
> 同样，当指定使用 [[yii\data\Sort::defaultOrder|defaultOrder]] 来排序的时候，你需要使用关联名称替代别名：
>
> 
> ```php
> $dataProvider->sort->defaultOrder = ['author.name' => SORT_ASC];
> ```

> 信息：更多关于 `joinWith` 和在后台执行查询的相关信息，
> 可以查看 [active record docs on joining with relations](db-active-record.md#joining-with-relations)。

#### Using SQL views for filtering, sorting and displaying data

还有另外一种方法可以更快、更有用 - SQL 视图。例如，假如我们需要展示网格视图附带着用户和他们的简介，我们可以这样做：


```sql
CREATE OR REPLACE VIEW vw_user_info AS
    SELECT user.*, user_profile.lastname, user_profile.firstname
    FROM user, user_profile
    WHERE user.id = user_profile.user_id
```

然后你需要创建活动记录代表这个视图：

```php

namespace app\models\views\grid;

use yii\db\ActiveRecord;

class UserView extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vw_user_info';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // define here your rules
        ];
    }

    /**
     * @inheritdoc
     */
    public static function attributeLabels()
    {
        return [
            // define here your attribute labels
        ];
    }


}
```

之后你可以使用这个UserView活动记录和搜索模型，没有附加的排序和过滤属性的规范。
所有属性将很好地工作。请注意，这种方法有利有弊：

- 你不需要指定不同排序和过滤条件。一切都很完美地运转；
- 它可以更快，因为数据的大小，SQL查询的执行（对于每个关联数据你都不需要额外的查询）都得到优化；
- 因为在SQL视图中这仅仅是一个简单的映射UI，所以在你的实体中，它可能缺乏一些领域的逻辑性，所以，假如你有一些方法诸如 `isActive`、`isDeleted` 或者其他一些影响到UI的方法，
  你也需要在这个类中复制他们。


### Multiple GridViews on one page

你可以在一个单独页面中使用多个网格视图，但是一些额外的配置还是有必要的，为的就是它们相互之间互不干扰。
当使用多个网格视图实例的时候，你必须要为生成的排序和分页链接配置不同的参数名，以便于每个网格视图有它们各自独立的排序和分页。
你可以通过设置 [[yii\data\Sort::sortParam|sortParam]] 和 [[yii\data\Pagination::pageParam|pageParam]]，对应于数据提供者的
[[yii\data\BaseDataProvider::$sort|sort]] 和 [[yii\data\BaseDataProvider::$pagination|pagination]] 实例。




假如我们想要列出 `Post` 和 `User模型，这两个模型已经在 `$userProvider` 和 `$postProvider` 这两个数据提供者中准备好，
具体做法如下：

```php
use yii\grid\GridView;

$userProvider->pagination->pageParam = 'user-page';
$userProvider->sort->sortParam = 'user-sort';

$postProvider->pagination->pageParam = 'post-page';
$postProvider->sort->sortParam = 'post-sort';

echo '<h1>Users</h1>';
echo GridView::widget([
    'dataProvider' => $userProvider,
]);

echo '<h1>Posts</h1>';
echo GridView::widget([
    'dataProvider' => $postProvider,
]);
```

### Using GridView with Pjax

> 注意: 这部分正在开发中。

待定
