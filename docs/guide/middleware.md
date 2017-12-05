Middleware
==========

Middleware will be closure or class. Class must implemented by yii\web\MiddlewareInterface

## Global

Global middleware for all routes can add in application config:

```php
        [
            'middleware' => \app\middleware\CopyrightMiddleware::class,
            'priority' => 10,
        ],        
        function (\yii\web\Request $request, \yii\web\Response $response) {
            $response->addHeader("heaader", "custom");
        }
```

```php
namespace app\middleware;

use yii\web\MiddlewareInterface;
use yii\web\Request;
use yii\web\Response;

class CopyrightMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Response $response)
    {
        $response->addHeader('copyright', 'Yiisoft');
    }
}
```

## Router

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        [
            'pattern' => '/',
            'route' => 'site/index',
            'middleware' => [
                [
                    'middleware' => function ($request, \yii\web\Response $response) {
                        echo "router middleware!";
                    },
                    'priority' => 5,
                    'except' => ['view', 'update']
                ],
                function ($request, \yii\web\Response $response) {
                    $response->addHeader("router_middleware", "exists");
                }
            ]
        ]
    ],
],
```

## Controller

```php
public function middleware()
{
    return [
        [
            'middleware' => function(Request $request, Response $response) {
                $response->addHeader('site', 'index');
            },
            'only' => ['index']
        ]
    ];
}
```

## Middleware configure

Middleware must configured by class name, closure or array:

```php
[
    'middleware' => 'className', //class name or closure
    'priority' => 1, //integer value, middleware will be executed in desc order (default null)
    'only' => ['index', 'view'], // middleware will be executed for only this actions (default for all actions)
    'except' => ['update'], // middleware will not be executed for this actions (default empty array)
]
```