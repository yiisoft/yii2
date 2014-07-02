Using template engines
======================

> Note: This section is under development.

By default, Yii uses PHP as its template language, but you can configure Yii to support other rendering engines, such as
[Twig](http://twig.sensiolabs.org/) or [Smarty](http://www.smarty.net/).

The `view` component is responsible for rendering views. You can add a custom template engine by reconfiguring this
component's behavior:

```php
[
    'components' => [
        'view' => [
            'class' => 'yii\web\View',
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    //'cachePath' => '@runtime/Twig/cache',
                    //'options' => [], /*  Array of twig options */
                    'globals' => ['html' => '\yii\helpers\Html'],
                    'uses' => ['yii\bootstrap'],
                ],
                // ...
            ],
        ],
    ],
]
```

In the code above, both Smarty and Twig are configured to be useable by the view files. But in order to get these extensions into your project, you need to also modify
your `composer.json` file to include them, too:

```
"yiisoft/yii2-smarty": "*",
"yiisoft/yii2-twig": "*",
```
That code would be added to the `require` section of `composer.json`. After making that change and saving the file, you can install the extensions by running `composer update --prefer-dist` in the command-line.

Twig
----

To use Twig, you need to create templates in files that have the `.twig` extension (or use another file extension but
configure the component accordingly). Unlike standard view files, when using Twig you must include the extension
in your `$this->render()` controller call:

```php
return $this->render('renderer.twig', ['username' => 'Alex']);
```

### Template syntax

The best resource to learn Twig basics is its official documentation you can find at
[twig.sensiolabs.org](http://twig.sensiolabs.org/documentation). Additionally there are Yii-specific addtions
described below.

#### Method and function calls

If you need result you can call a method or a function using the following syntax:

```
{% set result = my_function({'a' : 'b'}) %}
{% set result = myObject.my_function({'a' : 'b'}) %}
```

If you need to echo result instead of assigning it to a variable:

```
{{ my_function({'a' : 'b'}) }}
{{ myObject.my_function({'a' : 'b'}) }}
```

In case you don't need result you shoud use `void` wrapper:

```
{{ void(my_function({'a' : 'b'})) }}
{{ void(myObject.my_function({'a' : 'b'})) }}
```

#### Importing namespaces and classes

You can import additional classes and namespaces right in the template:

```
Namespace import:
{{ use('/app/widgets') }}

Class import:
{{ use('/yii/widgets/ActiveForm') }}

Aliased class import:
{{ use({'alias' => '/app/widgets/MyWidget'}) }}
```

#### Widgets

Extension helps using widgets in convenient way converting their syntax to function calls:

```
{{ use('yii/bootstrap') }}
{{ nav_bar_begin({
    'brandLabel': 'My Company',
}) }}
    {{ nav_widget({
        'options': {
            'class': 'navbar-nav navbar-right',
        },
        'items': [{
            'label': 'Home',
            'url': '/site/index',
        }]
    }) }}
{{ nav_bar_end() }}
```

In the template above `nav_bar_begin`, `nav_bar_end` or `nav_widget` consists of two parts. First part is widget name
coverted to lowercase and underscores: `NavBar` becomes `nav_bar`, `Nav` becomes `nav`. `_begin`, `_end` and `_widget`
are the same as `::begin()`, `::end()` and `::widget()` calls of a widget.

One could also use more generic `widget_end()` that executes `Widget::end()`.

#### Assets

Assets could be registered the following way:

```
{{ use('yii/web/JqueryAsset') }}
{{ register_jquery_asset() }}
```

In the call above `register` identifies that we're working with assets while `jquery_asset` translates to `JqueryAsset`
class that we've already imported with `use`.

#### Forms

You can build forms the following way:

```
{{ use('yii/widgets/ActiveForm') }}
{% set form = active_form_begin({
    'id' : 'login-form',
    'options' : {'class' : 'form-horizontal'},
}) %}
    {{ form.field(model, 'username') | raw }}
    {{ form.field(model, 'password').passwordInput() | raw }}

    <div class="form-group">
        <input type="submit" value="Login" class="btn btn-primary" />
    </div>
{{ active_form_end() }}
```


#### URLs

There are two functions you can use for building URLs:

```php
<a href="{{ path('blog/view', {'alias' : post.alias}) }}">{{ post.title }}</a>
<a href="{{ url('blog/view', {'alias' : post.alias}) }}">{{ post.title }}</a>
```

`path` generates relative URL while `url` generates absolute one. Internally both are using [[\yii\helpers\Url]].

#### Additional variables

Within Twig templates the following variables are always defined:

- `app`, which equates to `\Yii::$app`
- `this`, which equates to the current `View` object

### Additional configuration

Yii Twig extension allows you to define your own syntax and bring regular helper classes into templates. Let's review
configuration options.

#### Globals

You can add global helpers or values via the application configuration's `globals` variable. You can define both Yii
helpers and your own variables there:

```php
'globals' => [
    'html' => '\yii\helpers\Html',
    'name' => 'Carsten',
    'GridView' => '\yii\grid\GridView',
],
```

Once configured, in your template you can use the globals in the following way:

```
Hello, {{name}}! {{ html.a('Please login', 'site/login') | raw }}.

{{ GridView.widget({'dataProvider' : provider}) | raw }}
```

#### Functions

You can define additional functions like the following:

```php
'functions' => [
    'rot13' => 'str_rot13',
    'truncate' => '\yii\helpers\StringHelper::truncate',
],
```

In template they could be used like the following:

```
`{{ rot13('test') }}`
`{{ truncate(post.text, 100) }}`
```

#### Filters

Additional filters may be added via the application configuration's `filters` option:

```php
'filters' => [
    'jsonEncode' => '\yii\helpers\Json::encode',
],
```

Then in the template you can apply filter using the following syntax:

```
{{ model|jsonEncode }}
```


Smarty
------

To use Smarty, you need to create templates in files that have the `.tpl` extension (or use another file extension but configure the component accordingly). Unlike standard view files, when using Smarty you must include the extension in your `$this->render()`
or `$this->renderPartial()` controller calls:

```php
return $this->render('renderer.tpl', ['username' => 'Alex']);
```

### Additional functions

Yii adds the following construct to the standard Smarty syntax:

```php
<a href="{path route='blog/view' alias=$post.alias}">{$post.title}</a>
```

Internally, the `path()` function calls Yii's `Url::to()` method.

### Additional variables

Within Smarty templates, you can also make use of these variables:

- `$app`, which equates to `\Yii::$app`
- `$this`, which equates to the current `View` object

