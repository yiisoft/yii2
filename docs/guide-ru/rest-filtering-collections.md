Фильтрация коллекций
=====================

Коллекции ресурсов можно фильтровать с помощью компонента [[yii\data\DataFilter]], доступного начиная с версии 2.0.13.
Он позволяет валидировать и формировать условия фильтрации, переданные в запросе, а его расширенная версия
[[yii\data\ActiveDataFilter]] приводит их к формату, пригодному для [[yii\db\QueryInterface::where()]].


## Настройка провайдера данных для фильтрации <span id="configuring-data-provider-for-filtering"></span>

Как упоминалось в разделе [Коллекции](rest-resources.md#collections), для вывода отсортированного и
постранично разбитого списка ресурсов можно использовать [провайдер данных](output-data-providers.md).
Его же можно использовать для фильтрации.

```php
$filter = new ActiveDataFilter([
    'searchModel' => 'app\models\PostSearch',
]);

$filterCondition = null;
// Фильтры можно загрузить из любого источника. Например,
// если вы предпочитаете JSON в теле запроса,
// используйте Yii::$app->request->getBodyParams() ниже:
if ($filter->load(Yii::$app->request->get())) {
    $filterCondition = $filter->build();
    if ($filterCondition === false) {
        // Serializer извлечёт из него ошибки
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

Модель `PostSearch` определяет, какие свойства и значения допустимы для фильтрации:

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

Вместо отдельной модели для правил поиска можно использовать [[yii\base\DynamicModel]], если специальная
бизнес-логика не требуется.

```php
$filter = new ActiveDataFilter([
    'searchModel' => (new DynamicModel(['id', 'title']))
        ->addRule(['id'], 'integer')
        ->addRule(['title'], 'string', ['min' => 2, 'max' => 200]),
]);
```

Определение `searchModel` обязательно - оно контролирует, какие условия фильтрации доступны конечному пользователю.


## Запрос фильтрации <span id="filtering-request"></span>

Обычно от конечного пользователя ожидается передача необязательных условий фильтрации в запросе одним или несколькими
допустимыми способами (которые должны быть описаны в документации API). Например, если фильтрация выполняется через
POST-запрос с использованием JSON, запрос может выглядеть так:

```json
{
    "filter": {
        "id": {"in": [2, 5, 9]},
        "title": {"like": "cheese"}
    }
}
```

Условия выше означают:
- `id` должен быть 2, 5 или 9, **И**
- `title` должен содержать слово `cheese`.

Те же условия в GET-запросе:

```
?filter[id][in][]=2&filter[id][in][]=5&filter[id][in][]=9&filter[title][like]=cheese
```

Ключевое слово `filter` можно изменить через свойство [[yii\data\DataFilter::$filterAttributeName]].


## Ключевые слова фильтрации <span id="filter-control-keywords"></span>

По умолчанию допустимы следующие ключевые слова:

| ключевое слово | соответствует |
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

Список можно расширить через свойство [[yii\data\DataFilter::$filterControls]], например, добавив несколько
псевдонимов для одного оператора:

```php
[
    'eq' => '=',
    '=' => '=',
    '==' => '=',
    '===' => '=',
    // ...
]
```

Учтите, что любое не указанное ключевое слово не будет распознано как оператор фильтрации и будет воспринято как
имя атрибута - избегайте конфликтов между ключевыми словами и именами атрибутов (например, если есть оператор
`like` и атрибут `like`, задать условие для такого атрибута будет невозможно).

> Note: При определении ключевых слов учитывайте формат обмена данными вашего API.
  Каждое ключевое слово должно быть валидным для этого формата. Например, в XML имя тега может начинаться
  только с буквы, поэтому операторы вроде `>`, `=` или `$gt` нарушат XML-схему.

> Note: При добавлении нового ключевого слова проверьте, не нужно ли также обновить
  [[yii\data\DataFilter::$conditionValidators]] и/или [[yii\data\DataFilter::$operatorTypes]], чтобы
  получить корректный результат запроса с учётом сложности оператора и его логики работы.


## Обработка значений null <span id="handling-the-null-values"></span>

Хотя `null` легко использовать в JSON, передать его через GET-запрос невозможно без путаницы между литералом
`null` и строкой `"null"`. Начиная с версии 2.0.40 свойство [[yii\data\DataFilter::$nullValue]] позволяет
настроить слово-заменитель для литерала `null` (по умолчанию `"NULL"`).


## Псевдонимы атрибутов <span id="aliasing-attributes"></span>

Если нужно задать псевдоним для атрибута или фильтровать по связанной таблице, используйте
[[yii\data\DataFilter::$attributeMap]]:

```php
[
    'carPart' => 'car_part', // carPart будет фильтровать по свойству car_part
    'authorName' => '{{author}}.[[name]]', // authorName будет фильтровать по полю name связанной таблицы author
]
```

## Настройка фильтров для `ActiveController` <span id="configuring-filters-for-activecontroller"></span>

[[yii\rest\ActiveController]] содержит набор готовых REST-действий, которые также можно настроить для использования
фильтров через свойство [[yii\rest\IndexAction::$dataFilter]]. Один из способов - через
[[yii\rest\ActiveController::actions()]]:

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

Теперь коллекцию (доступную через действие `index`) можно фильтровать по свойствам `id` и `clockIn`.
