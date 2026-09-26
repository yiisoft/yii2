Ресурсы
=========

RESTful API строятся вокруг доступа к *ресурсам* и управления ими. Вы можете думать о ресурсах как
о [моделях](structure-models.md) из [MVC](https://ru.wikipedia.org/wiki/Model-View-Controller).

Хотя не существует никаких ограничений на то, как представить ресурс, в Yii ресурсы обычно представляются
как объекты [[yii\base\Model]] или дочерние классы (например [[yii\db\ActiveRecord]]), потому как:

* [[yii\base\Model]] реализует интерфейс [[yii\base\Arrayable]], который позволяет задать способ отдачи данных
  ресурса через RESTful API.
* [[yii\base\Model]] поддерживает [валидацию](input-validation.md), что полезно для RESTful API реализующего ввод данных.
* [[yii\db\ActiveRecord]] даёт мощную поддержку работы с БД, что актуально если данные ресурса хранятся в ней.

В этом разделе, мы сосредоточимся на том, как при помощи класса ресурса, наследуемого от [[yii\base\Model]]
(или дочерних классов) задать какие данные будут возвращаться RESTful API. Если класс ресурса не наследуется от
[[yii\base\Model]], возвращаются все его public свойства.


## Поля <span id="fields"></span>

Когда ресурс включается в ответ RESTful API, необходимо сериализовать его в строку. Yii разбивает этот процесс на два этапа.
Сначала ресурс конвертируется в массив при помощи [[yii\rest\Serializer]]. На втором этапе массив сериализуется в строку
заданного формата (например, JSON или XML) при помощи [[yii\web\ResponseFormatterInterface|форматтера ответа]].
Именно на этом стоит сосредоточиться при разработке класса ресурса.

Вы можете указать какие данные включать в представление ресурса в виде массива путём переопределения методов
[[yii\base\Model::fields()|fields()]] и/или [[yii\base\Model::extraFields()|extraFields()]]. Разница между ними в том,
что первый определяет набор полей, которые всегда будут включены в массив, а второй определяет дополнительные поля, которые
пользователь может запросить через параметр `expand`:

```
// вернёт все поля объявленные в fields()
http://localhost/users

// вернёт только поля id и email, если они объявлены в методе fields()
http://localhost/users?fields=id,email

// вернёт все поля объявленные в fields() и поле profile если оно указано в extraFields()
http://localhost/users?expand=profile

// вернёт только id, email и profile, если они объявлены в fields() и extraFields()
http://localhost/users?fields=id,email&expand=profile
```


### Переопределение `fields()` <span id="overriding-fields"></span>

По умолчанию, [[yii\base\Model::fields()]] возвращает все атрибуты модели как поля, а
[[yii\db\ActiveRecord::fields()]] возвращает только те атрибуты, которые были объявлены в схеме БД.

Вы можете переопределить `fields()` для того, чтобы добавить, удалить, переименовать или переобъявить поля. Значение,
возвращаемое `fields()`, должно быть массивом. Его ключи — это названия полей. Значения могут быть либо именами
свойств/атрибутов, либо анонимными функциями, которые возвращают значение соответствующих свойств. Когда
название поля совпадает с именем аттрибута вы можете опустить ключ массива:

```php
// явное перечисление всех атрибутов лучше всего использовать когда вы хотите быть уверенным что изменение
// таблицы БД или атрибутов модели не повлияет на изменение полей, отдаваемых API (что важно для поддержки обратной
// совместимости API).
public function fields()
{
    return [
        // название поля совпадает с именем атрибута
        'id',
        // название поля "email", атрибут "email_address"
        'email' => 'email_address',
        // название поля "name", значение определяется callback-ом PHP
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// отбрасываем некоторые поля. Лучше всего использовать в случае наследования
public function fields()
{
    $fields = parent::fields();

    // удаляем небезопасные поля
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: По умолчанию все атрибуты модели будут включены в ответы API. Вы должны убедиться в том, что отдаются
> только безопасные данные. В противном случае для исключения небезопасных полей необходимо переопределить метод
> `fields()`. В приведённом выше примере мы исключаем `auth_key`, `password_hash` и `password_reset_token`.


### Переопределение `extraFields()` <span id="overriding-extra-fields"></span>

По умолчанию, [[yii\base\Model::extraFields()]] и [[yii\db\ActiveRecord::extraFields()]] возвращают пустой массив.

Формат возвращаемых `extraFields()` данных такой же как у `fields()`. Как правило, `extraFields()`
используется для указания полей, значения которых являются объектами. Например учитывая следующее объявление полей

```php
public function fields()
{
    return ['id', 'email'];
}

public function extraFields()
{
    return ['profile'];
}
```

запрос `http://localhost/users?fields=id,email&expand=profile` может возвращать следующие JSON данные:

```php
[
    {
        "id": 100,
        "email": "100@example.com",
        "profile": {
            "id": 100,
            "age": 30,
        }
    },
    ...
]
```


## Ссылки <span id="links"></span>

Согласно [HATEOAS](https://ru.wikipedia.org/wiki/HATEOAS), расшифровывающемуся как Hypermedia as the Engine of Application State,
RESTful API должны возвращать достаточно информации для того, чтобы клиенты могли определить возможные действия над ресурсами.
Ключевой момент HATEOAS заключается в том, чтобы возвращать вместе с данными набора гиперссылок, указывающих на связанную
с ресурсом информацию.

Поддержку HATEOAS в ваши классы ресурсов можно добавить реализовав интерфейс [[yii\web\Linkable]]. Этот интерфейс
содержит единственный метод [[yii\web\Linkable::getLinks()|getLinks()]], который возвращает список [[yii\web\Link|ссылок]].
Обычно вы должны вернуть хотя бы ссылку `self` с  URL самого ресурса:

```php
use yii\db\ActiveRecord;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;

class User extends ActiveRecord implements Linkable
{
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['user/view', 'id' => $this->id], true),
        ];
    }
}
```

При отправке ответа объект `User` содержит поле `_links`, значение которого — ссылки, связанные с объектом:

```
{
    "id": 100,
    "email": "user@example.com",
    // ...
    "_links" => {
        "self": {
            "href": "https://example.com/users/100"
        }
    }
}
```


## Коллекции <span id="collections"></span>

Объекты ресурсов могут группироваться в *коллекции*. Каждая коллекция содержит список объектов ресурсов одного типа.

Несмотря на то, что коллекции можно представить в виде массива, удобнее использовать
[провайдеры данных](output-data-providers.md) так как они поддерживают сортировку и постраничную разбивку.
Для RESTful APIs, которые работают с коллекциями, данные возможности используются довольно часто. Например, следующее
действие контроллера возвращает провайдер данных для ресурса постов:

```php
namespace app\controllers;

use yii\rest\Controller;
use yii\data\ActiveDataProvider;
use app\models\Post;

class PostController extends Controller
{
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Post::find(),
        ]);
    }
}
```

При отправке ответа RESTful API, [[yii\rest\Serializer]] сериализует массив объектов ресурсов для текущей страницы.
Кроме того, он добавит HTTP заголовки, содержащие информацию о страницах:

* `X-Pagination-Total-Count`: общее количество ресурсов;
* `X-Pagination-Page-Count`: количество страниц;
* `X-Pagination-Current-Page`: текущая страница (начиная с 1);
* `X-Pagination-Per-Page`: количество ресурсов на страницу;
* `Link`: набор ссылок, позволяющий клиенту пройти все страницы ресурсов.

Примеры вы можете найти в разделе «[быстрый старт](rest-quick-start.md#trying-it-out)».
