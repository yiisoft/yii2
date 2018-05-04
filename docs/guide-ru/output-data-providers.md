Провайдеры данных
==============

В разделах [Постраничное разделение данных](output-pagination.md) и [Сортировка](output-sorting.md) было описано, 
как сделать возможность для конечных пользователей, чтобы они могли выбирать определённую страницу для вывода данных и
сортировку их по некоторым колонкам.

Провайдер данных это класс, который реализует [[yii\data\DataProviderInterface]]. Такая реализация поддерживает в основном 
разбивку на страницы и сортировку. Они обычно используются для работы [виджетов данных](output-data-widgets.md), что позволяет
конечным пользователям интерактивно использовать сортировку данных и их разбивку на страницы.

В Yii реализованы следующие классы провайдеров данных:

* [[yii\data\ActiveDataProvider]]: использует [[yii\db\Query]] или [[yii\db\ActiveQuery]] для запроса данных из базы данных,
возвращая их в виде массива или экземпляров [Active Record](db-active-record.md).
* [[yii\data\SqlDataProvider]]: выполняет запрос SQL к базе данных и возвращает результат в виде массива.
* [[yii\data\ArrayDataProvider]]: принимает большой массив и возвращает выборку из него с возможностью сортировки и разбивки
 на страницы.

Использование всех этих провайдеров данных имеет общую закономерность:

```php
// создание провайдера данных с конфигурацией для сортировки и постраничной разбивки
$provider = new XyzDataProvider([
    'pagination' => [...],
    'sort' => [...],
]);

// Получение данных с разбивкой на страницы и сортировкой.
$models = $provider->getModels();

// получение количества данных на текущей странице
$count = $provider->getCount();

// получение общего количества данных на всех страницах
$totalCount = $provider->getTotalCount();
```

Определение поведений сортировки и разбивки для провайдера данных устанавливается через его свойства
[[yii\data\BaseDataProvider::pagination|pagination]] и [[yii\data\BaseDataProvider::sort|sort]], которые соответствуют
настройкам [[yii\data\Pagination]] and [[yii\data\Sort]]. Вы можете отключить сортировку и разбивку на страницы путём
выставления их настроек в `false`.

[Виджеты данных](output-data-widgets.md), такие как [[yii\grid\GridView]], имеют свойство `dataProvider`, которое может
принимать экземпляр провайдера данных для отображения его данных. Например:

```php
echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

Эти провайдеры данных в некоторой степени различаются по использовании, в зависимости от источника данных. Далее 
опишем более подробно использование каждого провайдера данных.

## ActiveDataProvider <span id="active-data-provider"></span> 

Для использования [[yii\data\ActiveDataProvider]], необходимо настроить его свойство [[yii\data\ActiveDataProvider::query|query]].
Оно принимает любой [[yii\db\Query]] или [[yii\db\ActiveQuery]] объект. Если использовать первый, то данные будут возвращены в 
виде массивов, если второй - данные также могут быть возвращены в виде массивов, а также в виде экземпляров 
[Active Record](db-active-record.md). Например:

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'defaultOrder' => [
            'created_at' => SORT_DESC,
            'title' => SORT_ASC, 
        ]
    ],
]);

// возвращает массив Post объектов
$posts = $provider->getModels();
```

Если изменить `$query` в этом примере на следующий код, то будут возвращены сырые массивы.

```php
use yii\db\Query;

$query = (new Query())->from('post')->where(['status' => 1]); 
```

> Note: Если query содержит условия сортировки в `orderBy`, то новые условия, полученные от конечных пользователей
 (через настройки `sort`) будут добавлены к существующим условиям в `orderBy`. Любые условия в `limit` и `offset` 
 будут переписаны запросом конечного пользователя к различным страницам ( через конфигурацию  `pagination`).

По умолчанию, [[yii\data\ActiveDataProvider]] использует компонент приложения `db` для подключения к базе данных. Можно
использовать разные базы данных, настроив подключение через конфигурацию свойства [[yii\data\ActiveDataProvider::db]].

## SqlDataProvider <span id="sql-data-provider"></span>

[[yii\data\SqlDataProvider]] работает с сырыми запросами SQL, которые используются для извлечение необходимых данных.
Основываясь на спецификации из [[yii\data\SqlDataProvider::sort|sort]] и  [[yii\data\SqlDataProvider::pagination|pagination]],
провайдер данных будет добавлять `ORDER BY` и `LIMIT` конструкции к SQL запросу, для возврата только запрошенной 
страницы данных с учётом определённой сортировки.

Для использования [[yii\data\SqlDataProvider]], необходимо настроить свойства [[yii\data\SqlDataProvider::sql|sql]] и
[[yii\data\SqlDataProvider::totalCount|totalCount]]. Например:

```php
use yii\data\SqlDataProvider;

$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM post WHERE status=:status
', [':status' => 1])->queryScalar();

$provider = new SqlDataProvider([
    'sql' => 'SELECT * FROM post WHERE status=:status',
    'params' => [':status' => 1],
    'totalCount' => $count,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => [
            'title',
            'view_count',
            'created_at',
        ],
    ],
]);

// возвращает массив данных
$models = $provider->getModels();
```

> Совет: Свойство [[yii\data\SqlDataProvider::totalCount|totalCount]] обязательно только тогда, когда вам нужна разбивка
на страницы. Всё потому, что запрос SQL [[yii\data\SqlDataProvider::sql|sql]] будет изменяться провайдером данных для возврата
только текущей запрошенной страницы. Провайдеру необходимо знать общее количество данных в запросе для корректного 
вычисления разбивки на доступные страницы.

## ArrayDataProvider <span id="array-data-provider"></span>

[[yii\data\ArrayDataProvider]] лучше использовать для работы с большим массивом. Этот провайдер помогает вернуть выборку
из большого массива с сортировкой по одному или нескольким колонкам. Для использования [[yii\data\ArrayDataProvider]]
необходимо определить свойство [[yii\data\ArrayDataProvider::allModels|allModels]], как большой массив. Элементы в 
большом массиве могут быть ассоциативными массивами (например результаты выборки из [DAO](db-dao.md)) или объекты (
[Active Record](db-active-record.md) экземпляры). Например:

```php
use yii\data\ArrayDataProvider;

$data = [
    ['id' => 1, 'name' => 'name 1', ...],
    ['id' => 2, 'name' => 'name 2', ...],
    ...
    ['id' => 100, 'name' => 'name 100', ...],
];

$provider = new ArrayDataProvider([
    'allModels' => $data,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => ['id', 'name'],
    ],
]);

// получает строки для текущей запрошенной странице
$rows = $provider->getModels();
``` 

> Note: Сравнивая с [Active Data Provider](#active-data-provider) и [SQL Data Provider](#sql-data-provider),
ArrayDataProvider менее эффективный потому, что требует загрузки *всех* данных в память.


## Принципы работы с ключами данных <span id="working-with-keys"></span>

При возврате данных с помощью провайдера, часто требуется идентификация каждого элемента по уникальному ключу. Например,
если данные - это какая-то информация по клиенту, то возможно понадобится использовать ID клиента, как ключ для данных по 
каждому клиенту. Провайдер данных через [[yii\data\DataProviderInterface::getModels()]] может вернуть список из ключей
и соответствующего набора данных. Например,

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
]);

// возвращает массив объектов Post
$posts = $provider->getModels();

// возвращает значения первичного ключа в соответствии с $posts
$ids = $provider->getKeys();
```

В вышеописанном примере, так как [[yii\data\ActiveDataProvider]] предоставляется один [[yii\db\ActiveQuery]] объект, то
в этом случае провайдер достаточно умён, чтобы вернуть значения первичных ключей в качестве идентификатора. Также есть
возможность настроить способ вычисления значения идентификатора, через настройку [[yii\data\ActiveDataProvider::key]], как
имя колонки или функцию вычисления значений ключа. Например:

```php
// в качестве ключа используется столбец "slug"
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => 'slug',
]);

// в качестве ключа используется md5(id)
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => function ($model) {
        return md5($model->id);
    }
]);
```


## Создание своего провайдера данных <span id="custom-data-provider"></span>

Для создания своих классов провайдера данных, необходимо реализовать [[yii\data\DataProviderInterface]]. Простой способ 
сделать это - наследовать [[yii\data\BaseDataProvider]], который помогает сфокусироваться на логике ядра провайдера данных.
В основном необходимо реализовать следующие методы:
                                                   
- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]:подготавливает модели данных, которые будут доступны
 в текущей странице и возвращает их в виде массива.
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]: принимает массив имеющихся в настоящее время моделей 
данных и возвращает ключи, связанные с ними.
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]:возвращает значение, указывающее общее количество
 моделей данных в провайдере данных.

Ниже приведён пример провайдера данных, который эффективно считывает данные из CSV:

```php
<?php
use yii\data\BaseDataProvider;

class CsvDataProvider extends BaseDataProvider
{
    /**
     * @var string name of the CSV file to read
     */
    public $filename;
    
    /**
     * @var string|callable name of the key column or a callable returning it
     */
    public $key;
    
    /**
     * @var SplFileObject
     */
    protected $fileObject; // SplFileObject is very convenient for seeking to particular line in a file
    
 
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // open file
        $this->fileObject = new SplFileObject($this->filename);
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $models = [];
        $pagination = $this->getPagination();
 
        if ($pagination === false) {
            // in case there's no pagination, read all lines
            while (!$this->fileObject->eof()) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        } else {
            // in case there's pagination, read only a single page
            $pagination->totalCount = $this->getTotalCount();
            $this->fileObject->seek($pagination->getOffset());
            $limit = $pagination->getLimit();
 
            for ($count = 0; $count < $limit; ++$count) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        }
 
        return $models;
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
 
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
 
            return $keys;
        } else {
            return array_keys($models);
        }
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        $count = 0;
 
        while (!$this->fileObject->eof()) {
            $this->fileObject->next();
            ++$count;
        }
 
        return $count;
    }
}
```
