Error Handling
==============

Error handling in Yii is different than handling errors in plain PHP. First of all, Yii will convert all non-fatal errors
to *exceptions*:

```php
use yii\base\ErrorException;
use Yii;

try {
	10/0;
} catch (ErrorException) {
	Yii::warning("Tried dividing by zero.");
}

// execution may continue
```

As demonstrated above you may handle errors using `try`-`catch`.


Second, even fatal errors in Yii are rendered in a nice way. This means that in debugging mode, you can trace the causes
of fatal errors in order to more quickly identify the cause of the problem.

Rendering errors in a dedicated controller action
-------------------------------------------------

The default Yii error page is great when developing a site, and is acceptable for production sites if `YII_DEBUG`
is turned off in your bootstrap index.php file. But but you may want to customize the default error page to make it
more suitable for your project.

The easiest way to create a custom error page it is to use a dedicated controller action for error rendering. First,
you'll need to configure the `errorHandler` component in the application's configuration:

```php
return [
    // ...
    'components' => [
        // ...
        'errorHandler' => [
            'errorAction' => 'site/error',
    ],
```

With that configuration in place, whenever an error occurs, Yii will execute the "error" action of the "Site" controller.
That action should look for an exception and, if present, render the proper view file, passing along the exception:

```php
public function actionError()
{
    if (\Yii::$app->exception !== null) {
        return $this->render('error', ['exception' => \Yii::$app->exception]);
    }
}
```

Next, you would create the `views/site/error.php` file, which would make use of the exception. The exception object has
the following properties:

- `statusCode`: the HTTP status code (e.g. 403, 500). Available for HTTP exceptions only.
- `code`: the code of the exception.
- `type`: the error type (e.g. HttpException, PHP Error).
- `message`: the error message.
- `file`: the name of the PHP script file where the error occurs.
- `line`: the line number of the code where the error occurs.
- `trace`: the call stack of the error.
- `source`: the context source code where the error occurs.

Rendering errors without a dedicated controller action
------------------------------------------------------

Instead of creating a dedicated action within the Site controller, you could just indicate to Yii what class should
be used to handle errors:

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

After associating the class with the error as in the above, define the `views/site/error.php` file, which will
automatically be used. The view will be passed three variables:

- `$name`: the error name
- `$message`: the error message
- `$exception`: the exception being handled

The `$exception` object will have the same properties outlined above.
