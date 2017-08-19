Контроллеры
===========

После создания классов ресурсов и настройки способа форматирования ресурсных данных следующим шагом 
является создание действий контроллеров для предоставления ресурсов конечным пользователям через RESTful API.

В Yii есть два базовых класса контроллеров для упрощения вашей работы по созданию RESTful-действий:
[[yii\rest\Controller]] и [[yii\rest\ActiveController]]. Разница между этими двумя контроллерами в том,
что у последнего есть набор действий по умолчанию, который специально создан для работы с ресурсами,
представленными [Active Record](db-active-record.md). Так что если вы используете [Active Record](db-active-record.md)
и вас устраивает предоставленный набор встроенных действий, вы можете унаследовать классы ваших контроллеров
от [[yii\rest\ActiveController]], что позволит вам создать полноценные RESTful API, написав минимум кода.

[[yii\rest\Controller]] и [[yii\rest\ActiveController]] имеют следующие возможности, некоторые из которых
будут подробно описаны в следующих разделах:

* Проверка HTTP-метода;
* [Согласование содержимого и форматирование данных](rest-response-formatting.md);
* [Аутентификация](rest-authentication.md);
* [Ограничение частоты запросов](rest-rate-limiting.md).

[[yii\rest\ActiveController]], кроме того, предоставляет следующие возможности:

* Набор наиболее часто используемых действий: `index`, `view`, `create`, `update`, `delete` и `options`;
* Авторизация пользователя для запрашиваемых действия и ресурса.


## Создание классов контроллеров <span id="creating-controller"></span>

При создании нового класса контроллера в имени класса обычно используется
название типа ресурса в единственном числе. Например, контроллер, отвечающий за предоставление информации о пользователях,
можно назвать `UserController`.

Создание нового действия похоже на создание действия для Web-приложения. Единственное отличие в том,
что в RESTful-действиях вместо рендера результата в представлении с помощью вызова метода `render()`
вы просто возвращаете данные. Выполнение преобразования исходных данных в запрошенный формат ложится на
[[yii\rest\Controller::serializer|сериализатор]] и [[yii\web\Response|объект ответа]].
Например:

```php
public function actionView($id)
{
    return User::findOne($id);
}
```


## Фильтры <span id="filters"></span>

Большинство возможностей RESTful API, предоставляемых [[yii\rest\Controller]], реализовано на основе [фильтров](structure-filters.md).
В частности, следующие фильтры будут выполняться в том порядке, в котором они перечислены:

* [[yii\filters\ContentNegotiator|contentNegotiator]]: обеспечивает согласование содержимого, более подробно описан 
  в разделе [Форматирование ответа](rest-response-formatting.md);
* [[yii\filters\VerbFilter|verbFilter]]: обеспечивает проверку HTTP-метода;
* [[yii\filters\auth\AuthMethod|authenticator]]: обеспечивает аутентификацию пользователя, более подробно описан
  в разделе [Аутентификация](rest-authentication.md);
* [[yii\filters\RateLimiter|rateLimiter]]: обеспечивает ограничение частоты запросов, более подробно описан 
  в разделе [Ограничение частоты запросов](rest-rate-limiting.md).

Эти именованные фильтры объявлены в методе [[yii\rest\Controller::behaviors()|behaviors()]].
Вы можете переопределить этот метод для настройки отдельных фильтров, отключения каких-либо из них или для добавления
ваших собственных фильтров. Например, если вы хотите использовать только базовую HTTP-аутентификацию, вы можете
написать такой код:

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::class,
    ];
    return $behaviors;
}
```


## Наследование от `ActiveController` <span id="extending-active-controller"></span>

Если ваш класс контроллера наследуется от [[yii\rest\ActiveController]], вам следует установить
значение его свойства [[yii\rest\ActiveController::modelClass|modelClass]] равным имени класса ресурса,
который вы планируете обслуживать с помощью этого контроллера. Класс ресурса должен быть унаследован от [[yii\db\ActiveRecord]].


### Настройка действий <span id="customizing-actions"></span>

По умолчанию [[yii\rest\ActiveController]] предоставляет набор из следующих действий:

* [[yii\rest\IndexAction|index]]: постраничный список ресурсов;
* [[yii\rest\ViewAction|view]]: возвращает подробную информацию об указанном ресурсе;
* [[yii\rest\CreateAction|create]]: создание нового ресурса;
* [[yii\rest\UpdateAction|update]]: обновление существующего ресурса;
* [[yii\rest\DeleteAction|delete]]: удаление указанного ресурса;
* [[yii\rest\OptionsAction|options]]: возвращает поддерживаемые HTTP-методы.

Все эти действия объявляются в методе [[yii\rest\ActiveController::actions()|actions()]].
Вы можете настроить эти действия или отключить какие-то из них, переопределив метод `actions()`, как показано ниже:

```php
public function actions()
{
    $actions = parent::actions();

    // отключить действия "delete" и "create"
    unset($actions['delete'], $actions['create']);

    // настроить подготовку провайдера данных с помощью метода "prepareDataProvider()"
    $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    return $actions;
}

public function prepareDataProvider()
{
    // подготовить и вернуть провайдер данных для действия "index"
}
```

Чтобы узнать, какие опции доступны для настройки классов отдельных действий, обратитесь к соответствующим разделам справочника классов.


### Выполнение контроля доступа <span id="performing-access-check"></span>

При предоставлении ресурсов через RESTful API часто бывает нужно проверять, имеет ли текущий пользователь разрешение
на доступ к запрошенному ресурсу (или ресурсам) и манипуляцию им (или ими). Для [[yii\rest\ActiveController]] эта задача
может быть решена переопределением метода [[yii\rest\ActiveController::checkAccess()|checkAccess()]] следующим образом:

```php
/**
 * Проверяет права текущего пользователя.
 *
 * Этот метод должен быть переопределен, чтобы проверить, имеет ли текущий пользователь
 * право выполнения указанного действия над указанной моделью данных.
 * Если у пользователя нет доступа, следует выбросить исключение [[ForbiddenHttpException]].
 *
 * @param string $action ID действия, которое надо выполнить
 * @param \yii\base\Model $model модель, к которой нужно получить доступ. Если `null`, это означает, что модель, к которой нужно получить доступ, отсутствует.
 * @param array $params дополнительные параметры
 * @throws ForbiddenHttpException если у пользователя нет доступа
 */
public function checkAccess($action, $model = null, $params = [])
{
    // проверить, имеет ли пользователь доступ к $action и $model
    // выбросить ForbiddenHttpException, если доступ следует запретить
    if ($action === 'update' || $action === 'delete') {
        if ($model->author_id !== \Yii::$app->user->id)
            throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
    }
}
```

Метод `checkAccess()` будет вызван действиями по умолчанию контроллера [[yii\rest\ActiveController]]. Если вы создаёте
новые действия и хотите в них выполнять контроль доступа, вы должны вызвать этот метод явно в своих новых действиях.

> Tip: вы можете реализовать метод `checkAccess()` с помощью ["Контроля доступа на основе ролей" (RBAC)](security-authorization.md).
