Cross Origin Resource Sharing (CORS)
====================================

Cross-origin resource sharing (CORS) is a mechanism that allows many resources (e.g. fonts, JavaScript, etc.)
on a web page to be requested from another domain outside the domain the resource originated from.
In particular, JavaScript's AJAX calls can use the XMLHttpRequest mechanism. Such "cross-domain" requests would
otherwise be forbidden by web browsers, per the same origin security policy.
CORS defines a way in which the browser and the server can interact to determine whether or not to allow the cross-origin request.

To enable CORS management, the [[yii\filters\Cors|Cors filter]] should be added to the target controllers.

The [[yii\filters\Cors|Cors filter]] should be defined before Authentication / Authorization filters to make sure the CORS headers
will always be sent.

```php
public function behaviors()
{
    $behaviors = ArrayHelper::merge([
        'corsHeaders' => [
            'class' => \yii\filters\Cors::className(),
        ],
    ], parent::behaviors());
    return $behaviors;
}
```

The Cors filtering could be tuned using the `cors` property.

* `cors['Origin']`: array used to define allowed origins. Can be `['*']` (everyone) or `['http://www.myserver.net', 'http://www.myotherserver.com']`. Default to `['*']`.
* `cors['Access-Control-Request-Method']`: array of allowed verbs like `['GET', 'OPTIONS', 'HEAD']`.  Default to `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`.
* `cors['Access-Control-Request-Headers']`: array of allowed headers. Can be `['*']` all headers or specific ones `['X-Request-With']`. Default to `['*']`.
* `cors['Access-Control-Allow-Credentials']`: define if current request can be made using credentials. Can be `true`, `false`. Default to `true`.
* `cors['Access-Control-Max-Age']`: define lifetime of pre-flight request. Default to `86400`.

For example, allowing CORS for origin : `http://www.myserver.net` with method `GET`, `HEAD` and `OPTIONS` and do not send `Access-Control-Allow-Credentials` header :

```php
public function behaviors()
{
    $behaviors = ArrayHelper::merge([
        'corsHeaders' => [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['http://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => null,
            ],
        ],
    ], parent::behaviors());
    return $behaviors;
}
```

You may tune the CORS headers by overriding default parameters on a per action basis.
For example adding the `Access-Control-Allow-Credentials` for `login` action could be done like this :

```php
public function behaviors()
{
    $behaviors = ArrayHelper::merge([
        'corsHeaders' => [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['http://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => null,
            ],
            'actions' => [
                'login' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ]
        ],
    ], parent::behaviors());
    return $behaviors;
}
```

