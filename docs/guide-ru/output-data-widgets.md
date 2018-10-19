Виджеты для данных
============

Yii предоставляет набор [виджетов](structure-widgets.md), которые могут быть использованы для отображения данных.
В то время как виджет [DetailView](#detail-view) может быть использован для отображения данных по одной записи, то
виджеты [ListView](#list-view) и [GridView](#grid-view) могут быть использованы для показа данных в виде списка или
таблицы с возможностью сортировки, фильтрации и разбивки данных постранично.


DetailView <a name="detail-view"></a>
----------

Виджет [[yii\widgets\DetailView|DetailView]] отображает детали по данным для одной [[yii\widgets\DetailView::$model|model]].

Этот виджет лучше использовать для отображения данных модели в обычном формате(т.е. каждый атрибут модели будет представлен
в виде строки в таблице). Модель может быть либо объектом класса [[\yii\base\Model]] или его наследником, таких как
[active record](db-active-record.md) , либо ассоциативным массивом.

DetailView использует свойство [[yii\widgets\DetailView::$attributes|$attributes]] для определений, какие атрибуты модели
должны быть показаны и в каком формате. Обратитесь к разделу [Форматирование данных](output-formatting.md) за возможными
настройками форматирования.

Обычное использование DetailView сводится к следующему коду:

```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',                                           // title свойство (обычный текст)
        'description:html',                                // description свойство, как HTML
        [                                                  // name свойство зависимой модели owner
            'label' => 'Owner',
            'value' => $model->owner->name,            
            'contentOptions' => ['class' => 'bg-red'],     // настройка HTML атрибутов для тега, соответсвующего value
            'captionOptions' => ['tooltip' => 'Tooltip'],  // настройка HTML атрибутов для тега, соответсвующего label
        ],
        'created_at:datetime',                             // дата создания в формате datetime
    ],
]);
```

ListView <a name="list-view"></a>
--------

Виджет [[yii\widgets\ListView|ListView]] использует для отображения информации [провайдера данных](output-data-providers.md).
Каждая модель отображается, используя определённый [[yii\widgets\ListView::$itemView|вид]]. Поскольку провайдер включает
в себя разбивку на страницы, сортировку и фильтрацию, то его использование удобно для отображения информации конечному
пользователю и создания интерфейса управления данными.

Обычное использование сводится к следующему коду:

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

`_post` файл вид, который может содержать следующее:


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

В вышеописанном коде текущая модель доступна как `$model`. Кроме этого доступны дополнительные переменные:

- `$key`: mixed, значение ключа в соответствии с данными.
- `$index`: integer, индекс элемента данных в массиве элементов, возвращенных поставщику данных, который начинается с 0.
- `$widget`: ListView, это экземпляр виджета.

Если необходимо послать дополнительные данные в каждый вид, то можно использовать свойство [[yii\widgets\ListView::$viewParams|$viewParams]]
как ключ-значение, например:

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

Они также станут доступны в виде в качестве переменных.


GridView <a name="grid-view"></a>
--------

Таблица данных или GridView - это один из сверхмощных Yii виджетов. Он может быть полезен, если необходимо быстро создать
административный раздел системы. GridView использует данные, как [провайдер данных](output-data-providers.md) и отображает
каждую строку используя [[yii\grid\GridView::columns|columns]] для предоставления данных в таблице.

Каждая строка из таблицы представлена данными из одиночной записи и колонка, как правило, представляет собой атрибут
записи (некоторые столбцы могут соответствовать сложным выражениям атрибутов или статическому тексту).

Минимальный код, который необходим для использования GridView:

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

В вышеприведённом коде сначала создаётся провайдер данных и затем используется GridView для отображения атрибутов для
каждого элемента из провайдера данных. Отображенная таблица оснащена функционалом сортировки и разбивки на страницы из
коробки.

### Колонки таблицы

Колонки таблицы настраиваются с помощью определённых [[yii\grid\Column]] классов, которые настраиваются в свойстве
[[yii\grid\GridView::columns|columns]] виджета GridView. В зависимости от типа колонки и их настроек, данные отображаются
по разному. По умолчанию это класс [[yii\grid\DataColumn]], который представляет атрибут модели с возможностью сортировки
и фильтрации по нему.

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        // Обычные поля определенные данными содержащимися в $dataProvider.
        // Будут использованы данные из полей модели.
        'id',
        'username',
        // Более сложный пример.
        [
            'class' => 'yii\grid\DataColumn', // может быть опущено, поскольку является значением по умолчанию
            'value' => function ($data) {
                return $data->name; // $data['name'] для массивов, например, при использовании SqlDataProvider.
            },
        ],
    ],
]);
```

Учтите, что если [[yii\grid\GridView::columns|columns]] не сконфигурирована, то Yii попытается отобразить все возможные
колонки из провайдера данных.

### Классы колонок

Колонки таблицы могут быть настроены, используя различные классы колонок:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- тут
            // тут можно настроить дополнительные свойства
        ],
```

В дополнение к классам колонок от Yii, вы можете самостоятельно создать свой собственный класс.

Каждый класс колонки наследуется от [[yii\grid\Column]], так что есть некоторые общие параметры, которые можно установить
при настройке колонок.

- [[yii\grid\Column::header|header]] позволяет установить содержание для строки заголовка.
- [[yii\grid\Column::footer|footer]] позволяет установить содержание для "подвала".
- [[yii\grid\Column::visible|visible]] определяет, должен ли столбец быть видимым.
- [[yii\grid\Column::content|content]] позволяет передавать действительный обратный вызов, который будет возвращать данные для строки.Формат следующий:

  ```php
  function ($model, $key, $index, $column) {
      return 'a string';
  }
  ```

Вы можете задать различные параметры контейнера HTML через массивы:

- [[yii\grid\Column::headerOptions|headerOptions]]
- [[yii\grid\Column::footerOptions|footerOptions]]
- [[yii\grid\Column::filterOptions|filterOptions]]
- [[yii\grid\Column::contentOptions|contentOptions]]


#### DataColumn <span id="data-column"></span>

[[yii\grid\DataColumn|Data column]] используется для отображения и сортировки данных. По умолчанию этот тип
используется для всех колонок.

Основная настройка этой колонки - это свойство [[yii\grid\DataColumn::format|format]]. Значение этого свойства посылается
в методы `formatter` [компонента](structure-application-components.md), который по умолчанию [[\yii\i18n\Formatter|Formatter]]

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
        'created_at:datetime', // короткий вид записи формата
        [
            'label' => 'Education',
            'attribute' => 'education',
            'filter' => ['0' => 'Elementary', '1' => 'Secondary', '2' => 'Higher'],
            'filterInputOptions' => ['prompt' => 'All educations', 'class' => 'form-control', 'id' => null]
        ],
    ],
]);
```

В вышеприведённом коде  `text` соответствует [[\yii\i18n\Formatter::asText()]]. В качестве первого аргумента для этого
метода будет передаваться значение колонки. Во второй колонки описано  `date`, которая соответствует [[\yii\i18n\Formatter::asDate()]].
В качестве первого аргумента, опять же, будет передаваться значение колонки, в то время как второй аргумент будет
'php:Y-m-d'.

Доступный список форматов смотрите в разделе [Форматирование данных](output-formatting.md).

Для конфигурации колонок данных также доступен короткий вид записи, который описан в API документации для [[yii\grid\GridView::columns|колонок]].

Используйте [[yii\grid\DataColumn::filter|filter]] и [[yii\grid\DataColumn::filterInputOptions|filterInputOptions]] для
настройки HTML кода фильтра.

По умолчанию заголовки колонок генерируются используя [[yii\data\Sort::link]]. Это можно изменить через свойство
[[yii\grid\Column::header]]. Для изменения заголовка нужно задать [[yii\grid\DataColumn::$label]], как в
примере выше. По умолчанию текст будет взят из модели данных. Подробное описание ищите в [[yii\grid\DataColumn::getHeaderCellLabel]].

#### ActionColumn

[[yii\grid\ActionColumn|ActionColumn]] отображает кнопки действия, такие как изменение или удаление для каждой строки.

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\ActionColumn',
            // вы можете настроить дополнительные свойства здесь.
        ],
```

Доступные свойства для конфигурации:

- [[yii\grid\ActionColumn::controller|controller]] это идентификатор контроллера, который должен обрабатывать действия.
 Если не установлен, то будет использоваться текущий активный контроллер.
- [[yii\grid\ActionColumn::template|template]] определяет шаблон для каждой ячейки в колонке действия. Маркеры заключённые
 в фигурные скобки являются ID действием контроллера (также называются *именами кнопок* в контексте колонки действия).
 Они могут быть заменены, через свойство [[yii\grid\ActionColumn::$buttons|buttons]]. Например, маркер `{view}` будет
 заменён результатом из функции, определённой в `buttons['view']`. Если такая функция не может быть найдена, то маркер
 заменяется на пустую строку. По умолчанию шаблон имеет вид `{view} {update} {delete}`.
- [[yii\grid\ActionColumn::buttons|buttons]] массив из функций для отображения кнопок. Ключи массива представлены как
 имена кнопок (как описывалось выше), а значения представлены в качестве анонимных функций, которые выводят кнопки. Замыкания
 должны использоваться в следующем виде:

  ```php
  function ($url, $model, $key) {
      // возвращаем HTML код для кнопки
  }
  ```
  где, `$url` - это URL, который будет повешен как ссылка на кнопку, `$model` - это объект модели для текущей строки и
  `$key` - это ключ для модели из провайдера данных.

- [[yii\grid\ActionColumn::urlCreator|urlCreator]] замыкание, которое создаёт URL используя информацию из модели. Вид
 замыкания должен быть таким же как и в [[yii\grid\ActionColumn::createUrl()]]. Если свойство не задано, то URL для кнопки
 будет создана используя метод [[yii\grid\ActionColumn::createUrl()]].
- [[yii\grid\ActionColumn::visibleButtons|visibleButtons]] это массив условий видимости каждой из кнопок.
 Ключи массива представлены как имена кнопок (как описывалось выше), а значения представлены как булево значение или
 анонимная функция. Если имя кнопки не описано в массиве, она будет отображена по умолчанию.
 Замыкания должны использоваться в следующем виде:

 ```php
 function ($model, $key, $index) {
   return $model->status === 'editable'; // отображать ли кнопку
 }
 ```

 Или вы можете передать булево значение:

 ```php
 [
     'update' => \Yii::$app->user->can('update')
 ]
 ```

#### CheckboxColumn

[[yii\grid\CheckboxColumn|Checkbox column]] отображает колонку как флаг (сheckbox).

Для добавления CheckboxColumn в виджет GridView, необходимо добавить его в  [[yii\grid\GridView::$columns|columns]]:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        // ...
        [
            'class' => 'yii\grid\CheckboxColumn',
            // вы можете настроить дополнительные свойства здесь.
        ],
    ],
```

Пользователи могут нажимать на флаги для выделения строк в таблице. Отмеченные строки могут быть обработаны с помощью
JavaScript кода:

```javascript
var keys = $('#grid').yiiGridView('getSelectedRows');
// массив ключей для отмеченных строк
```

#### SerialColumn

[[yii\grid\SerialColumn|Serial column]] выводит в строках номера начиная с `1` и увеличивая их по мере вывода строк.

Использование очень простое :

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'], // <-- тут
        // ...
```


### Сортировка данных

> Note: Эта секция под разработкой
>
> - https://github.com/yiisoft/yii2/issues/1576

### Фильтрация данных

Для фильтрации данных в GridView необходима [модель](structure-models.md), которая описывает форму для фильтрации, внося
условия в запрос поиска для провайдера данных.
Общепринятой практикой считается использование [active records](db-active-record.md) и создание для неё класса модели для
поиска, которая содержит необходимую функциональность(может быть сгенерирована через [Gii](start-gii.md)). Класс модели
для поиска должен описывать правила валидации и реализовать метод `search()`, который будет возвращать провайдер данных.

Для поиска возможных `Post` моделей, можно создать `PostSearch` наподобие следующего примера:

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
        // только поля определенные в rules() будут доступны для поиска
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

        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // изменяем запрос добавляя в его фильтрацию
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', $this->title])
              ->andFilterWhere(['like', 'creation_date', $this->creation_date]);

        return $dataProvider;
    }
}

```

Теперь можно использовать этот метод в контроллере, чтобы получить провайдер данных для GridView:

```php
$searchModel = new PostSearch();
$dataProvider = $searchModel->search(Yii::$app->request->get());

return $this->render('myview', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);
```

и в виде присвоить их  `$dataProvider` и `$searchModel` в виджете GridView:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        // ...
    ],
]);
```

### Отдельная форма фильтрации

Фильтров в шапке GridView достаточно для большинства задач, но добавление отдельной формы фильтрации не представляет
особой сложности. Она бывает полезна в случае необходимости фильтрации по полям, которые не отображаются в GridView
или особых условий фильтрации, например по диапазону дат.

Создайте частичное представление `_search.php` со следующим содержимым:

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
        <?= Html::submitButton('Искать', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Сбросить', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
```

и добавьте его отображение в `index.php` таким образом:

```php
<?= $this->render('_search', ['model' => $searchModel]) ?>
```

> Note: если вы используете Gii для генерации CRUD кода, отдельная форма фильтрации (`_search.php`)
генерируется по умолчанию, но закомментирована в представлении `index.php`. Вам остается только раскомментировать
эту строку и форма готова к использованию!

Для фильтра по диапазону дат мы можем добавить дополнительные атрибуты `createdFrom` и `createdTo` в поисковую модель
(их нет в соответствующей таблице модели):

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

Расширим условия запроса в методе `search()`:

```php
$query->andFilterWhere(['>=', 'creation_date', $this->createdFrom])
      ->andFilterWhere(['<=', 'creation_date', $this->createdTo]);
```

И добавим соответствующие поля в форму фильтрации:

```php
<?= $form->field($model, 'creationFrom') ?>

<?= $form->field($model, 'creationTo') ?>
```

### Отображение зависимых моделей

Бывают случаи, когда необходимо в GridView вывести в колонке значения из зависимой модели для active records, например
имя автора новости, вместо его `id`. Для этого необходимо задать [[yii\grid\GridView::$columns]] как `author.name`, если
же модель `Post` содержит зависимость с именем `author` и имя автора хранится в атрибуте `name`. GridView отобразит
имя автора, но вот сортировка и фильтрации по этому полю будет не доступна. Необходимо дополнить некоторый функционал в
`PostSearch` модель, которая была упомянута в предыдущем разделе.

Для включения сортировки по зависимой колонки необходимо присоединить зависимую таблицу и добавить правило в компонент
Sort для провайдера данных.:

```php
$query = Post::find();
$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);

// присоединяем зависимость `author` которая является связью с таблицей `users`
// и устанавливаем алиас таблицы в значение `author`
$query->joinWith(['author' => function($query) { $query->from(['author' => 'users']); }]);
// добавляем сортировку по колонке из зависимости
$dataProvider->sort->attributes['author.name'] = [
    'asc' => ['author.name' => SORT_ASC],
    'desc' => ['author.name' => SORT_DESC],
];

// ...
```

Фильтрации также необходим вызов joinWith, как описано выше. Также необходимо определить для поиска столбец в атрибутах
и правилах:

```php
public function attributes()
{
    // делаем поле зависимости доступным для поиска
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

В `search()` просто добавляется другое условие фильтрации:

```php
$query->andFilterWhere(['LIKE', 'author.name', $this->getAttribute('author.name')]);
```

> Info: В коде, что выше, используется такая же строка, как и имя зависимости и псевдонима таблицы.
> Однако, когда ваш псевдоним и имя связи различаются, вы должны обратить внимание, где вы используете псевдоним,
> а где имя связи. Простым правилом для этого является использование псевдонима в каждом месте, которое используется
> для построения запроса к базе данных, и имя связи во всех других определениях, таких как `attributes()`, `rules()` и т.д.
>
> Например, если вы используете псевдоним `au` для связи с таблицей автора, то joinWith будет выглядеть так:
>
> ```php
> $query->joinWith(['author' => function($query) { $query->from(['au' => 'users']); }]);
> ```
> Это также возможно вызвать как `$query->joinWith(['author']);`, когда псевдоним определен в определении отношения.
>
> Псевдоним должен быть использован в состоянии фильтра, но имя атрибута остается неизменным:
>
> ```php
> $query->andFilterWhere(['LIKE', 'au.name', $this->getAttribute('author.name')]);
> ```
>
> То же самое верно и для определения сортировки:
>
> ```php
> $dataProvider->sort->attributes['author.name'] = [
>      'asc' => ['au.name' => SORT_ASC],
>      'desc' => ['au.name' => SORT_DESC],
> ];
> ```
>
> Кроме того, при определении [[yii\data\Sort::defaultOrder|defaultOrder]] для сортировки необходимо использовать имя
> зависимости вместо псевдонима:
>
> ```php
> $dataProvider->sort->defaultOrder = ['author.name' => SORT_ASC];
> ```

> Info: Для подробной информации по `joinWith` и запросам, выполняемым в фоновом режиме, обратитесь к
> [active record документации](db-active-record.md#joining-with-relations).

#### Использование SQL видов для вывода данных, их сортировки и фильтрации.

Существует и другой подход, который быстре и более удобен - SQL виды. Например, если необходимо показать таблицу из
пользователей и их профилей, то можно выбрать такой путь:

```sql
CREATE OR REPLACE VIEW vw_user_info AS
    SELECT user.*, user_profile.lastname, user_profile.firstname
    FROM user, user_profile
    WHERE user.id = user_profile.user_id
```

Теперь вам необходимо создать ActiveRecord, через который будут доступны данные из вида выше:

```php

namespace app\models\views\grid;

use yii\db\ActiveRecord;

class UserView extends ActiveRecord
{

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // здесь определяйте ваши правила
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            // здесь определяйте ваши метки атрибутов
        ];
    }
}
```

После этого вы можете использовать UserView в модели поиска, без каких-либо дополнительных условий по сортировке и фильтрации.
Все атрибуты будут работать из коробки. Но такая реализация имеет свои плюсы и минусы:

- вам не надо определять условия сортировок и фильтраций. Всё работает из коробки;
- это намного быстрее данных, так как некоторые запросы уже выполнены (т.е. для каждой зависимости не нужно выполнять дополнительные запросы)
- поскольку это простое отображение данных из sql вида, то в модели будет отсутствовать некоторая доменная логика, например
такие методы как `isActive`, `isDeleted`, необходимо продублировать в классе, который описывает вид.

### Несколько GridViews на одной странице

Вы можете использовать больше одной GridView на одной странице. Для этого нужно внести некоторые дополнительные настройки
для того, чтобы они друг другу не мешали.
При использовании нескольких экземпляров GridView вы должны настроить различные имена параметров для сортировки и ссылки
для разбиения на страницы так, чтобы каждый GridView имел свою индивидуальную сортировку и разбиение на страницы.
Сделать это возможно через настройку [[yii\data\Sort::sortParam|sortParam]] и [[yii\data\Pagination::pageParam|pageParam]]
свойств провайдеров данных [[yii\data\BaseDataProvider::$sort|sort]] и [[yii\data\BaseDataProvider::$pagination|pagination]]

Допустим мы хотим список моделей `Post` и `User`, для которых мы уже подготовили провайдеры данных `$userProvider` и
`$postProvider`, тогда код будет выглядеть следующим образом:

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

### Использование GridView с Pjax

> Note: Секция находится в стадии разработки

TBD
