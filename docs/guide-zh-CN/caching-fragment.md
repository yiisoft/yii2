片段缓存
=======

片段缓存指的是缓存页面内容中的某个片段。例如，一个页面显示了逐年销售额的摘要表格，
可以把表格缓存下来，以消除每次请求都要重新生成表格的耗时。
片段缓存是基于[数据缓存](caching-data.md)实现的。

在[视图](structure-views.md)中使用以下结构启用片段缓存：

```php
if ($this->beginCache($id)) {

    // ... 在此生成内容 ...

    $this->endCache();
}
```

调用 [[yii\base\View::beginCache()|beginCache()]] 和 [[yii\base\View::endCache()|endcache()]] 方法包裹内容生成逻辑。
如果缓存中存在该内容，[[yii\base\View::beginCache()|beginCache()]] 方法将渲染内容并返回 false，
因此将跳过内容生成逻辑。否则，内容生成逻辑被执行，
一直执行到[[yii\base\View::endCache()|endCache()]] 时，
生成的内容将被捕获并存储在缓存中。

和[数据缓存](caching-data.md)一样，每个片段缓存也需要全局唯一的 `$id` 标记。


## 缓存选项 <span id="caching-options"></span>

如果要为片段缓存指定额外配置项，
请通过向 [[yii\base\View::beginCache()|beginCache()]] 
方法第二个参数传递配置数组。在框架内部，该数组将被用来配置一个 [[yii\widget\FragmentCache]] 
小部件用以实现片段缓存功能。

### 过期时间（duration） <span id="duration"></span>

或许片段缓存中最常用的一个配置选项就是 [[yii\widgets\FragmentCache::duration|duration]] 了。
它指定了内容被缓存的秒数。
以下代码缓存内容最多一小时：

```php
if ($this->beginCache($id, ['duration' => 3600])) {

    // ... 在此生成内容 ...

    $this->endCache();
}
```

如果该选项未设置，则它将采用默认值 60，这意味着缓存的内容将在 60 秒后过期。


### 依赖 <span id="dependencies"></span>

和[数据缓存](caching-data.md)一样，片段缓存的内容一样可以设置缓存依赖。
例如一段被缓存的文章，是否重新缓存取决于它是否被修改过。

通过设置 [[yii\widgets\FragmentCache::dependency|dependency]] 选项来指定依赖，
该选项的值可以是一个 [[yii\caching\Dependency]] 类的派生类，也可以是创建缓存对象的配置数组。
以下代码指定了一个片段缓存，它依赖于 `update_at` 字段是否被更改过的。

```php
$dependency = [
    'class' => 'yii\caching\DbDependency',
    'sql' => 'SELECT MAX(updated_at) FROM post',
];

if ($this->beginCache($id, ['dependency' => $dependency])) {

    // ... 在此生成内容 ...

    $this->endCache();
}
```


### 变化 <span id="variations"></span>

缓存的内容可能需要根据一些参数的更改而变化。
例如一个 Web 应用支持多语言，同一段视图代码也许需要生成多个语言的内容。
因此可以设置缓存根据应用当前语言而变化。

通过设置 [[yii\widgets\FragmentCache::variations|variations]] 选项来指定变化，
该选项的值应该是一个标量，每个标量代表不同的变化系数。
例如设置缓存根据当前语言而变化可以用以下代码：

```php
if ($this->beginCache($id, ['variations' => [Yii::$app->language]])) {

    // ... 在此生成内容 ...

    $this->endCache();
}
```


### 开关 <span id="toggling-caching"></span>

有时你可能只想在特定条件下开启片段缓存。例如，一个显示表单的页面，可能只需要在初次请求时缓存表单（通过 GET 请求）。
随后请求所显示（通过 POST 请求）的表单不该使用缓存，因为此时表单中可能包含用户输入内容。
鉴于此种情况，可以使用 [[yii\widgets\FragmentCache::enabled|enabled]] 选项来指定缓存开关，
如下所示：

```php
if ($this->beginCache($id, ['enabled' => Yii::$app->request->isGet])) {

    // ... 在此生成内容 ...

    $this->endCache();
}
```


## 缓存嵌套 <span id="nested-caching"></span>

片段缓存可以被嵌套使用。一个片段缓存可以被另一个包裹。
例如，评论被缓存在里层，同时整个评论的片段又被缓存在外层的文章中。
以下代码展示了片段缓存的嵌套使用：

```php
if ($this->beginCache($id1)) {

    // ...在此生成内容...

    if ($this->beginCache($id2, $options2)) {

        // ...在此生成内容...

        $this->endCache();
    }

    // ...在此生成内容...

    $this->endCache();
}
```

可以为嵌套的缓存设置不同的配置项。例如，内层缓存和外层缓存使用不同的过期时间。
甚至当外层缓存的数据过期失效了，内层缓存仍然可能提供有效的片段缓存数据。
但是，反之则不然。如果外层片段缓存没有过期而被视为有效，
此时即使内层片段缓存已经失效，它也将继续提供同样的缓存副本。
因此，你必须谨慎处理缓存嵌套中的过期时间和依赖，
否则外层的片段很有可能返回的是不符合你预期的失效数据。
> 译注：外层的失效时间应该短于内层，外层的依赖条件应该低于内层，以确保最小的片段，返回的是最新的数据。

## 动态内容 <span id="dynamic-content"></span>

使用片段缓存时，可能会遇到一大段较为静态的内容中有少许动态内容的情况。
例如，一个显示着菜单栏和当前用户名的页面头部。
还有一种可能是缓存的内容可能包含每次请求
都需要执行的 PHP 代码（例如注册资源包的代码）。
这两个问题都可以使用**动态内容**功能解决。

动态内容的意思是这部分输出的内容不该被缓存，即便是它被包裹在片段缓存中。
为了使内容保持动态，每次请求都执行 PHP 代码生成，
即使这些代码已经被缓存了。

可以在片段缓存中调用 [[yii\base\View::renderDynamic()]] 去插入动态内容，
如下所示：

```php
if ($this->beginCache($id1)) {

    // ...在此生成内容...

    echo $this->renderDynamic('return Yii::$app->user->identity->name;');

    // ...在此生成内容...

    $this->endCache();
}
```

[[yii\base\View::renderDynamic()|renderDynamic()]] 方法接受一段 PHP 代码作为参数。
代码的返回值被看作是动态内容。这段代码将在每次请求时都执行，
无论其外层的片段缓存是否被存储。

> Note: 从版本 2.0.14 开始，动态内容 API 通过 [[yii\base\DynamicContentAwareInterface]] 接口及其 [[yii\base\DynamicContentAwareTrait]] 特质开放。
  举个例子，你可以参考 [[yii\widgets\FragmentCache]] 类。
