数据小部件
============

Yii提供了一套数据小部件 [widgets](structure-widgets.md) ，这些小部件可以用于显示数据。
[DetailView](#detail-view) 小部件能够用于显示一条记录数据，
[ListView](#list-view) 和 [GridView](#grid-view) 小部件能够用于显示一个拥有分页、
排序和过滤功能的一个列表或者表格。


DetailView <span id="detail-view"></span>
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
        'title',                                           // title attribute (in plain text)
        'description:html',                                // description attribute formatted as HTML
        [                                                  // the owner name of the model
            'label' => 'Owner',
            'value' => $model->owner->name,            
            'contentOptions' => ['class' => 'bg-red'],     // HTML attributes to customize value tag
            'captionOptions' => ['tooltip' => 'Tooltip'],  // HTML attributes to customize label tag
        ],
        'created_at:datetime',                             // creation date formatted as datetime
    ],
]);
```

请记住，与处理一组模型的 [[yii\widgets\GridView|GridView]] 不同，
[[yii\widgets\DetailView|DetailView]] 只处理一个。
因为 `$model` 是唯一一个用于显示的模型，并且可以作为变量在视图中使用。

但是有些情况下可以使闭包有用。
例如指定了 `visible`，并且你不想让`value` 的结果为 `false`：

```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        [
            'attribute' => 'owner',
            'value' => function ($model) {
                return $model->owner->name;
            },
            'visible' => \Yii::$app->user->can('posts.owner.view'),
        ],
    ],
]);
```

ListView <span id="list-view"></span>
--------

[[yii\widgets\ListView|ListView]] 小部件用于显示数据提供者 [data provider](output-data-providers.md) 提供的数据。
每个数据模型用指定的视图文件 [[yii\widgets\ListView::$itemView|view file]] 来渲染。
因为它提供开箱即用式的（译者注：封装好的）分页、排序以及过滤这样一些特性，
所以它可以很方便地为最终用户显示信息并同时创建数据管理界面。

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

`_post` 视图文件可包含如下代码：


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

- `$key`：混合类型，键的值与数据项相关联。
- `$index`：整型，是由数据提供者返回的数组中以0起始的数据项的索引。
- `$widget`：类型是ListView，是小部件的实例。

假如你需要传递附加数据到每一个视图中，你可以像下面这样用 [[yii\widgets\ListView::$viewParams|$viewParams]] 
属性传递键值对：

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

在视图中，上述这些附加数据也是可以作为变量来使用的。


GridView <span id="grid-view"></span>
--------

数据网格或者说 GridView 小部件是Yii中最强大的部件之一。如果你需要快速建立系统的管理后台，
GridView 非常有用。它从数据提供者 [data provider](output-data-providers.md) 中取得数据并使用 
[[yii\grid\GridView::columns|columns]] 属性的一组列配置，在一个表格中渲染每一行数据。

表中的每一行代表一个数据项的数据，并且一列通常表示该项的属性
（某些列可以对应于属性或静态文本的复杂表达式）。

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
展现出来的表格封装了排序以及分页功能。


### 表格列

表格的列是通过 [[yii\grid\Column]] 类来配置的，这个类是通过 GridView 配置项中的 [[yii\grid\GridView::columns|columns]] 
属性配置的。根据列的类别和设置的不同，各列能够以不同方式展示数据。
默认的列类是 [[yii\grid\DataColumn]]，用于展现模型的某个属性，
并且可以排序和过滤。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        // 数据提供者中所含数据所定义的简单的列
        // 使用的是模型的列的数据
        'id',
        'username',
        // 更复杂的列数据
        [
            'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
            'value' => function ($data) {
                return $data->name; // 如果是数组数据则为 $data['name'] ，例如，使用 SqlDataProvider 的情形。
            },
        ],
    ],
]);
```

请注意，假如配置中没有指定 [[yii\grid\GridView::columns|columns]] 属性，
那么 Yii 会试图显示数据提供者的模型中所有可能的列。


### 列类

通过使用不同类，网格列可以自定义：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- 这里
            // 你还可以在此配置其他属性
        ],
```

除了我们下面将要展开讨论的Yii自带的列类，你还可以创建你自己的列类。

每个列类是从 [[yii\grid\Column]] 扩展而来，
从而在配置网格列的时候，你可以设置一些公共的选项。

- [[yii\grid\Column::header|header]] 允许为头部行设置内容。
- [[yii\grid\Column::footer|footer]] 允许为尾部行设置内容。
- [[yii\grid\Column::visible|visible]] 定义某个列是否可见。
- [[yii\grid\Column::content|content]] 允许你传递一个有效的PHP回调来为一行返回数据，格式如下：

  ```php
  function ($model, $key, $index, $column) {
      return 'a string';
  }
  ```
  
你可以传递数组来指定各种容器式的HTML选项：

- [[yii\grid\Column::headerOptions|headerOptions]]
- [[yii\grid\Column::footerOptions|footerOptions]]
- [[yii\grid\Column::filterOptions|filterOptions]]
- [[yii\grid\Column::contentOptions|contentOptions]]


#### 数据列 <span id="data-column"></span>

[[yii\grid\DataColumn|Data column]] 用于显示和排序数据。这是默认的列的类型，
所以在使用 DataColumn 为列类时，可省略类的指定（译者注：不需要'class'选项的意思）。

数据列的主要配置项是 [[yii\grid\DataColumn::format|format]] 属性。它的值对应于 `formatter` [application component](structure-application-components.md) 应用组件里面的一些方法，
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
        'created_at:datetime', // shortcut format
        [
            'label' => 'Education',
            'attribute' => 'education',
            'filter' => ['0' => 'Elementary', '1' => 'Secondary', '2' => 'Higher'],
            'filterInputOptions' => ['prompt' => 'All educations', 'class' => 'form-control', 'id' => null]
        ],
    ],
]);
```

在上述代码中，`text` 对应于 [[\yii\i18n\Formatter::asText()]]。列的值作为第一个参数传递。
在第二列的定义中，`date` 对应于 [[\yii\i18n\Formatter::asDate()]]。
该列的值再次作为第一个参数传递同时 'php:Y-m-d' 被用作第二个参数。

有关可用格式化程序的列表，请参阅 [关于数据格式的部分](output-formatting.md)。

对于配置数据列，还有一种快捷方式格式，
请参阅 API 文档 [[yii\grid\GridView::columns|columns]]。

使用 [[yii\grid\DataColumn::filter|filter]] 和 [[yii\grid\DataColumn::filterInputOptions|filterInputOptions]]
去控制过滤器输入的 HTML。

默认情况下，列的头部有 [[yii\data\Sort::link]] 来呈现。它还可以使用 [[yii\grid\Column::header]] 来调整。
要更改头部文本，您应该像上面的示例中那样设置 [[yii\grid\DataColumn::$label]]。
默认情况下，标签应该从数据模型中填充。更多细节请参阅 [[yii\grid\DataColumn::getHeaderCellLabel]]。

#### 动作列 

[[yii\grid\ActionColumn|Action column]] 用于显示一些动作按钮，如每一行的更新、删除操作。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\ActionColumn',
            // 您可以在此处配置其他属性
        ],
```

可配置的属性如下：

- [[yii\grid\ActionColumn::controller|controller]] 是应该执行这些动作的控制器ID。
  如果没有设置，它将使用当前控制器。
- [[yii\grid\ActionColumn::template|template]] 定义在动作列中使用的构建每个单元格的模板。
  在大括号内括起来的的令牌被当做是控制器的 action 方法ID (在动作列的上下文中也称作*按钮名称*)。
  它们将会被 [[yii\grid\ActionColumn::$buttons|buttons]] 中指定的对应按钮的关联的渲染回调函数替代。
  例如，令牌 `{view}` 将被 `buttons['view']` 关联的渲染回调函数的返回结果所替换。
  如果没有找到回调函数，令牌将被替换成一个空串。默认的令牌有 `{view} {update} {delete}` 。
- [[yii\grid\ActionColumn::buttons|buttons]] 是一个按钮的渲染回调数数组。数组中的键是按钮的名字（没有花括号），并且值是对应的按钮渲染回调函数。
  这些回调函数须使用下面这种原型：

  ```php
  function ($url, $model, $key) {
      // return the button HTML code
  }
  ```

  在上面的代码中，`$url` 是列为按钮创建的URL，`$model`是当前要渲染的模型对象，
  并且 `$key` 是在数据提供者数组中模型的键。

- [[yii\grid\ActionColumn::urlCreator|urlCreator]] 是使用指定的模型信息来创建一个按钮URL的回调函数。
  该回调的原型和 [[yii\grid\ActionColumn::createUrl()]] 是一样的。
  假如这个属性没有设置，按钮的URL将使用 [[yii\grid\ActionColumn::createUrl()]] 来创建。
- [[yii\grid\ActionColumn::visibleButtons|visibleButtons]] 是控制每个按钮可见性条件的数组。
  数组键是按钮名称 (没有大括号)，值是布尔值 true/false 或匿名函数。
  如果在数组中没有指定按钮名称，将会按照默认的来显示。
  回调必须像如下这样来使用：

  ```php
  function ($model, $key, $index) {
      return $model->status === 'editable';
  }
  ```

  或者你可以传递一个布尔值：

  ```php
  [
      'update' => \Yii::$app->user->can('update')
  ]
  ```

#### 复选框列 

[[yii\grid\CheckboxColumn|Checkbox column]] 显示一个复选框列。

想要添加一个复选框到网格视图中，将它添加到 [[yii\grid\GridView::$columns|columns]] 的配置中，如下所示：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        // ...
        [
            'class' => 'yii\grid\CheckboxColumn',
            // 你可以在这配置更多的属性
        ],
    ],
```

用户可点击复选框来选择网格中的一些行。被选择的行可通过调用下面的
JavaScript代码来获得：

```javascript
var keys = $('#grid').yiiGridView('getSelectedRows');
// keys 为一个由与被选行相关联的键组成的数组
```

#### 序号列 

[[yii\grid\SerialColumn|Serial column]] 渲染行号，以 `1` 起始并自动增长。

使用方法和下面的例子一样简单：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'], // <-- here
        // ...
```


### 数据排序

> Note: 这部分正在开发中。
>
> - https://github.com/yiisoft/yii2/issues/1576

### 数据过滤

为了过滤数据的 GridView 需要一个模型 [model](structure-models.md) 来 
从过滤表单接收数据，以及调整数据提供者的查询对象，以满足搜索条件。
使用活动记录 [active records](db-active-record.md) 时，通常的做法是
创建一个能够提供所需功能的搜索模型类（可以使用 [Gii](start-gii.md) 来生成）。
这个类为搜索定义了验证规则并且提供了一个将会返回数据提供者对象
的 `search()` 方法。

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
        // 只有在 rules() 函数中声明的字段才可以搜索
        return [
            [['id'], 'integer'],
            [['title', 'creation_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // 旁路在父类中实现的 scenarios() 函数
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Post::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // 从参数的数据中加载过滤条件，并验证
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // 增加过滤条件来调整查询对象
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', $this->title])
              ->andFilterWhere(['like', 'creation_date', $this->creation_date]);

        return $dataProvider;
    }
}
```

> Tip: 请参阅 [Query Builder](db-query-builder.md) 尤其是 [Filter Conditions](db-query-builder.md#filter-conditions)
> 去学习如何构建过滤查询。

你可以在控制器中使用如下方法为网格视图获取数据提供者：

```php
$searchModel = new PostSearch();
$dataProvider = $searchModel->search(Yii::$app->request->get());

return $this->render('myview', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);
```

然后你在视图中将 `$dataProvider` 和 `$searchModel` 对象分派给 GridView 小部件：

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        // ...
    ],
]);
```

### 单独过滤表单

大多数时候使用 GridView 标头过滤器就足够了，但是如果你需要一个单独的过滤器表单，你也可以很轻松的去添加。您可以使用以下内容创建部分视图 `_search.php`：


```php
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PostSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="post-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'creation_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::submitButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
```

并将其包含在 `index.php` 视图中，如下所示：

```php
<?= $this->render('_search', ['model' => $searchModel]) ?>
```

> Note: 如果使用 Gii 生成 CRUD 代码， 默认情况下会生成单独的过滤器表单（`_search.php`），
  但是在 `index.php` 视图中已经被注释了。取消注释就可以用了!

当您需要按字段过滤时，单独的过滤器表单很有用，这些字段不会在 GridView 中显示，也不适用于特殊筛选条件（如日期范围）。
对于按日期范围过滤，
我们可以将非 DB 属性 `createdFrom` 和 `createdTo` 添加到搜索模型：

```php
class PostSearch extends Post
{
    /**
     * @var string
     */
    public $createdFrom;

    /**
     * @var string
     */
    public $createdTo;
}
```

在 `search()` 扩展查询条件的方法如下：

```php
$query->andFilterWhere(['>=', 'creation_date', $this->createdFrom])
      ->andFilterWhere(['<=', 'creation_date', $this->createdTo]);
```

并将代表字段添加到过滤器表单：

```php
<?= $form->field($model, 'creationFrom') ?>

<?= $form->field($model, 'creationTo') ?>
```

### 处理关系型模型

当我们在一个网格视图中显示活动数据的时候，你可能会遇到这种情况，就是显示关联表的列的值，
例如：发帖者的名字，而不是显示他的 `id`。当 `Post` 模型有一个关联的属性名（译者注： `Post` 模型中用 `hasOne` 定义 `getAuthor()` 函数）
叫 `author` 并且作者模型（译者注：本例的作者模型是 `users` ）有一个属性叫 `name`，
那么你可以通过在 [[yii\grid\GridView::$columns]] 中定义属性名为 `author.name` 来处理。
这时的网格视图能显示作者名了，但是默认是不支持按作者名排序和过滤的。
你需要调整上个章节介绍的 `PostSearch` 模型，以添加此功能。

为了使关联列能够排序，你需要连接关系表，
以及添加排序规则到数据提供者的排序组件中：

```php
$query = Post::find();
$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);

// 连接与 `users` 表相关联的 `author` 表
// 并将 `users` 表的别名设为 `author`
$query->joinWith(['author' => function($query) { $query->from(['author' => 'users']); }]);
// since version 2.0.7, the above line can be simplified to $query->joinWith('author AS author');
// 使得关联字段可以排序
$dataProvider->sort->attributes['author.name'] = [
    'asc' => ['author.name' => SORT_ASC],
    'desc' => ['author.name' => SORT_DESC],
];

// ...
```

过滤也需要像上面一样调用joinWith方法。你也需要在属性和规则中定义该列，就像下面这样：

```php
public function attributes()
{
    // 添加关联字段到可搜索属性集合
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

然后在 `search()` 方法中，你仅需要添加一个额外过滤条件：

```php
$query->andFilterWhere(['LIKE', 'author.name', $this->getAttribute('author.name')]);
```

> Info: 在上面的代码中，我们使用相同的字符串作为关联名称和表别名；
> 然而，当你的表别名和关联名称不相同的时候，你得注意在哪使用你的别名，在哪使用你的关联名称。
> 一个简单的规则是在每个构建数据库查询的地方使用别名，而在所有其他和定义相关的诸如：
> `attributes()` 和 `rules()` 等地方使用关联名称。
> 
> 例如，你使用 `au` 作为作者关系表的别名，那么联查语句就要写成像下面这样：
> 
> ```php
> $query->joinWith(['author' => function($query) { $query->from(['au' => 'users']); }]);
> ```
>
> 当别名已经在关联函数中定义了时，也可以只调用 `$query->joinWith(['author']);`。
>
> 在过滤条件中，别名必须使用，但属性名称保持不变：
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
> 同样，当指定使用 [[yii\data\Sort::defaultOrder|defaultOrder]] 来排序的时候，
>你需要使用关联名称替代别名：
> 
> ```php
> $dataProvider->sort->defaultOrder = ['author.name' => SORT_ASC];
> ```

> Info: 更多关于 `joinWith` 和在后台执行查询的相关信息，
> 可以查看 [active record docs on joining with relations](db-active-record.md#joining-with-relations)。

#### SQL 视图用于过滤、排序和显示数据

还有另外一种方法可以更快、更有用的 SQL 视图。例如，我们要在 `GridView` 
中显示用户和他们的简介，可以这样创建 SQL 视图：

```sql
CREATE OR REPLACE VIEW vw_user_info AS
    SELECT user.*, user_profile.lastname, user_profile.firstname
    FROM user, user_profile
    WHERE user.id = user_profile.user_id
```

然后你需要创建活动记录模型来代表这个视图：

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
            // 在这定义你的规则
        ];
    }

    /**
     * @inheritdoc
     */
    public static function attributeLabels()
    {
        return [
            // 在这定义你的属性标签
        ];
    }


}
```

之后你可以使用这个 UserView 活动记录和搜索模型，无需附加的排序和过滤属性的规则。
所有属性都可开箱即用。请注意，这种方法有利有弊：

- 你不需要指定不同排序和过滤条件，一切都包装好了；
- 它可以更快，因为数据的大小，SQL 查询的执行（对于每个关联数据你都不需要额外的查询）都得到优化；
- 因为在 SQL 视图中这仅仅是一个简单的映射UI，所以在你的实体中，它可能缺乏某方面的逻辑，所以，假如你有一些诸如 `isActive`、`isDeleted` 或者其他影响到 UI 的方法，
  你也需要在这个类中复制他们。


### 单个页面多个网格视图部件

你可以在一个单独页面中使用多个网格视图，但是一些额外的配置是必须的，为的就是它们相互之间不干扰。
当使用多个网格视图实例的时候，你必须要为生成的排序和分页对象配置不同的参数名，
以便于每个网格视图有它们各自独立的排序和分页。
你可以通过设置 [[yii\data\Sort::sortParam|sortParam]] 和 
[[yii\data\Pagination::pageParam|pageParam]]，对应于数据提供者的
[[yii\data\BaseDataProvider::$sort|sort]] 和 
[[yii\data\BaseDataProvider::$pagination|pagination]] 实例。

假如我们想要同时显示 `Post` 和 `User` 模型，这两个模型已经在 `$userProvider` 和 `$postProvider` 这两个数据提供者中准备好，
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

### 在 GridView 使用 Pjax

[[yii\widgets\Pjax|Pjax]] 允许您更新页面的某个部分，
而不是重新加载整个页面。
使用过滤器时，可以使用它仅更新 [[yii\grid\GridView|GridView]] 内容。

```php
use yii\widgets\Pjax;
use yii\grid\GridView;

Pjax::begin([
    // PJax options
]);
    Gridview::widget([
        // GridView options
    ]);
Pjax::end();
```

Pjax 也适用于 [[yii\widgets\Pjax|Pjax]] 小部件之间的链接以及
[[yii\widgets\Pjax::$linkSelector|Pjax::$linkSelector]] 指定的链接。
但是这可能是 [[yii\grid\ActionColumn|ActionColumn]] 链接的问题。
要防止这种情况，请在编辑
[[yii\grid\ActionColumn::$buttons|ActionColumn::$buttons]] 属性时将 HTML 属性 `data-pjax="0"` 添加到链接中。

#### 在 Gii 中使用 Pjax 的 GridView/ListView

从 2.0.5 开始，[Gii](start-gii.md) 的 CRUD 生成器有一个 `$enablePjax` 选项，
可以通过 web 界面或者命令行使用。

```php
yii gii/crud --controllerClass="backend\\controllers\PostController" \
  --modelClass="common\\models\\Post" \
  --enablePjax=1
```

这会生成一个由 [[yii\widgets\Pjax|Pjax]] 小部件包含的
[[yii\grid\GridView|GridView]] 或者 [[yii\widgets\ListView|ListView]]。

延伸阅读
---------------

- [Rendering Data in Yii 2 with GridView and ListView](https://www.sitepoint.com/rendering-data-in-yii-2-with-gridview-and-listview/) by Arno Slatius.
