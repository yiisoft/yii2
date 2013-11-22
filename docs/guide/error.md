Error Handling
==============

Error handling in Yii is different from plain PHP. First of all, all non-fatal errors are converted to exceptions so
you can use `try`-`catch` to work with these. Second, even fatal errors are rendered in a nice way. In debug mode that
means you have a trace and a piece of code where it happened so it takes less time to analyze and fix it.

Using controller action to render errors
----------------------------------------

Default Yii error page is great for development mode and is OK for production if `YII_DEBUG` is turned off but you may
have an idea how to make it more suitable for your project. An easiest way to customize it is to use controller action
for error rendering. In order to do so you need to configure `errorHandler` component via application config:

```php
return [
    // ...
    'components' => [
        // ...
        'errorHandler' => [
            'errorAction' => 'site/error',
    ],
```

After it is done in case of error Yii will launch `SiteController::actionError()`. Since errors are converted to
exceptions we can get exception from error handler:

```php
public function actionError()
{
    $exception = \Yii::$app->getErrorHandler()->exception;
    $this->render('myerror', ['message' => $exception->getMessage()]);
}
```

Since most of the time you need to adjust look and feel only, Yii provides `ErrorAction` class that can be used in
controller instead of implementing action yourself:

```php
public function actions()
{
    return [
        'error' => [
            'class' => 'yii\web\ErrorAction',
        ],
    ];
}
```

After defining `actions` in `SiteController` as shown above you can create `views/site/error.php`. In the view there
are three varialbes available:

- `$name`: the error name
- `$message`: the error message
- `$exception`: the exception being handled

