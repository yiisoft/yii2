Controllers
===========

So you have the resource data and you have specified how the resource data should be formatted, the next thing
to do is to create controller actions to expose the resource data to end users.

Yii provides two base controller classes to simplify your work of creating RESTful actions:
[[yii\rest\Controller]] and [[yii\rest\ActiveController]]. The difference between these two controllers
is that the latter provides a default set of actions that are specified designed to deal with
resources represented as ActiveRecord. So if you are using ActiveRecord and you are comfortable with
the provided built-in actions, you may consider creating your controller class by extending from
the latter. Otherwise, extending from [[yii\rest\Controller]] will allow you to develop actions
from scratch.

Both [[yii\rest\Controller]] and [[yii\rest\ActiveController]] provide the following features which will
be described in detail in the next few sections:

* Response format negotiation;
* API version negotiation;
* HTTP method validation;
* User authentication;
* Rate limiting.

[[yii\rest\ActiveController]] in addition provides the following features specifically for working
with ActiveRecord:

* A set of commonly used actions: `index`, `view`, `create`, `update`, `delete`, `options`;
* User authorization in regard to the requested action and resource.

When creating a new controller class, a convention in naming the controller class is to use
the type name of the resource and use singular form. For example, to serve user information,
the controller may be named as `UserController`.

Creating a new action is similar to creating an action for a Web application. The only difference
is that instead of rendering the result using a view by calling the `render()` method, for RESTful actions
you directly return the data. The [[yii\rest\Controller::serializer|serializer]] and the
[[yii\web\Response|response object]] will handle the conversion from the original data to the requested
format. For example,

```php
public function actionSearch($keyword)
{
    $result = SolrService::search($keyword);
    return $result;
}
```

If your controller class extends from [[yii\rest\ActiveController]], you should set
its [[yii\rest\ActiveController::modelClass||modelClass]] property to be the name of the resource class
that you plan to serve through this controller. The class must implement [[yii\db\ActiveRecordInterface]].

With [[yii\rest\ActiveController]], you may want to disable some of the built-in actions or customize them.
To do so, override the `actions()` method like the following:

```php
public function actions()
{
    $actions = parent::actions();

    // disable the "delete" and "create" actions
    unset($actions['delete'], $actions['create']);

    // customize the data provider preparation with the "prepareDataProvider()" method
    $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    return $actions;
}

public function prepareDataProvider()
{
    // prepare and return a data provider for the "index" action
}
```

The following list summarizes the built-in actions supported by [[yii\rest\ActiveController]]:

* [[yii\rest\IndexAction|index]]: list resources page by page;
* [[yii\rest\ViewAction|view]]: return the details of a specified resource;
* [[yii\rest\CreateAction|create]]: create a new resource;
* [[yii\rest\UpdateAction|update]]: update an existing resource;
* [[yii\rest\DeleteAction|delete]]: delete the specified resource;
* [[yii\rest\OptionsAction|options]]: return the supported HTTP methods.

