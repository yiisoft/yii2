Filters
=======

> Note: This section is under development.

You may apply some action filters to controller actions to accomplish tasks such as determining
who can access the current action, decorating the result of the action, etc.

An action filter is an instance of a class extending [[yii\base\ActionFilter]].

To use an action filter, attach it as a behavior to a controller or a module. The following
example shows how to enable HTTP caching for the `index` action:

```php
public function behaviors()
{
    return [
        'httpCache' => [
            'class' => \yii\filters\HttpCache::className(),
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

You may use multiple action filters at the same time. These filters will be applied in the
order they are declared in `behaviors()`. If any of the filter cancels the action execution,
the filters after it will be skipped.

When you attach a filter to a controller, it can be applied to all actions of that controller;
If you attach a filter to a module (or application), it can be applied to the actions of any controller
within that module (or application).

To create a new action filter, extend from [[yii\base\ActionFilter]] and override the
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] and [[yii\base\ActionFilter::afterAction()|afterAction()]]
methods. The former will be executed before an action runs while the latter after an action runs.
The return value of [[yii\base\ActionFilter::beforeAction()|beforeAction()]] determines whether
an action should be executed or not. If `beforeAction()` of a filter returns false, the filters after this one
will be skipped and the action will not be executed.

The [authorization](authorization.md) section of this guide shows how to use the [[yii\filters\AccessControl]] filter,
and the [caching](caching.md) section gives more details about the [[yii\filters\PageCache]] and [[yii\filters\HttpCache]] filters.
These built-in filters are also good references when you learn to create your own filters.
