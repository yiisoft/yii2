Page Caching
============

Page caching refers to caching the content of a whole page on the server-side. Later when the same page
is requested again, its content will be served from the cache instead of regenerating it from scratch.

Page caching is supported by [[yii\filters\PageCache]], an [action filter](structure-filters.md).
It can be used like the following in a controller class:

```php
public function behaviors()
{
    return [
        [
            '__class' => 'yii\filters\PageCache',
            'only' => ['index'],
            'duration' => 60,
            'variations' => [
                \Yii::$app->language,
            ],
            'dependency' => [
                '__class' => 'yii\caching\DbDependency',
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
        ],
    ];
}
```

The above code states that page caching should be used only for the `index` action. The page content should
be cached for at most 60 seconds and should be variated by the current application language
and the cached page should be invalidated if the total number of posts is changed.

As you can see, page caching is very similar to [fragment caching](caching-fragment.md). They both support options such
as `duration`, `dependencies`, `variations`, and `enabled`. Their main difference is that page caching is
implemented as an [action filter](structure-filters.md) while fragment caching a [widget](structure-widgets.md).

You can use [fragment caching](caching-fragment.md) as well as [dynamic content](caching-fragment.md#dynamic-content)
together with page caching.

