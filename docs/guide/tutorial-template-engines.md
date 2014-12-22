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
                    'cachePath' => '@runtime/Twig/cache',
                    // Array of twig options:
                    'options' => [
                        'auto_reload' => true,
                    ],
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
[twig.sensiolabs.org](http://twig.sensiolabs.org/documentation). Additionally there are Yii-specific syntax extensions
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

#### Setting object properties

There's a special function called `set` that allows you to set property of an object. For example, the following
in the template will change page title:

```
{{ set(this, 'title', 'New title') }}
```

#### Importing namespaces and classes

You can import additional classes and namespaces right in the template:

```
Namespace import:
{{ use('/app/widgets') }}

Class import:
{{ use('/yii/widgets/ActiveForm') }}

Aliased class import:
{{ use({'alias' : '/app/widgets/MyWidget'}) }}
```

#### Referencing other templates

There are two ways of referencing templates in `include` and `extends` statements:

```
{% include "comment.twig" %}
{% extends "post.twig" %}

{% include "@app/views/snippets/avatar.twig" %}
{% extends "@app/views/layouts/2columns.twig" %}
```

In the first case the view will be searched relatively to the current template path. For `comment.twig` and `post.twig`
that means these will be searched in the same directory as the currently rendered template.

In the second case we're using path aliases. All the Yii aliases such as `@app` are available by default.

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

To use Smarty, you need to create templates in files that have the `.tpl` extension (or use another file extension but
configure the component accordingly). Unlike standard view files, when using Smarty you must include the extension in
your `$this->render()` or `$this->renderPartial()` controller calls:

```php
return $this->render('renderer.tpl', ['username' => 'Alex']);
```

### Template syntax

The best resource to learn Smarty template syntax is its official documentation you can find at
[www.smarty.net](http://www.smarty.net/docs/en/). Additionally there are Yii-specific syntax extensions
described below.

#### Setting object properties

There's a special function called `set` that allows you to set common properties of the view and controller. Currently
available properties are `title`, `theme` and `layout`:

```
{set title="My Page"}
{set theme="frontend"}
{set layout="main.tpl"}
```

For title there's dedicated block as well:

```
{title}My Page{/title}
```

#### Setting meta tags

Meta tags could be set like to following:

```
{meta keywords="Yii,PHP,Smarty,framework"}
```

There's also dedicated block for description:

```
{description}This is my page about Smarty extension{/description}
```

#### Calling object methods

Sometimes you need calling

#### Importing static classes, using widgets as functions and blocks

You can import additional static classes right in the template:

```
{use class="yii\helpers\Html"}
{Html::mailto('eugenia@example.com')}
```

If you want you can set custom alias:

```
{use class="yii\helpers\Html" as="Markup"}
{Markup::mailto('eugenia@example.com')}
```

Extension helps using widgets in convenient way converting their syntax to function calls or blocks. For regular widgets
function could be used like the following:

```
{use class='@yii\grid\GridView' type='function'}
{GridView dataProvider=$provider}
```

For widgets with `begin` and `end` methods such as ActiveForm it's better to use block:

```
{use class='yii\widgets\ActiveForm' type='block'}
{ActiveForm assign='form' id='login-form' action='/form-handler' options=['class' => 'form-horizontal']}
    {$form->field($model, 'firstName')}
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <input type="submit" value="Login" class="btn btn-primary" />
        </div>
    </div>
{/ActiveForm}
```

If you're using particular widget a lot, it is a good idea to declare it in application config and remove `{use class`
call from templates:

```php
'components' => [
    'view' => [
        // ...
        'renderers' => [
            'tpl' => [
                'class' => 'yii\smarty\ViewRenderer',
                'widgets' => [
                    'blocks' => [
                        'ActiveForm' => '\yii\widgets\ActiveForm',
                    ],
                ],
            ],
        ],
    ],
],
```

#### Referencing other templates

There are two main ways of referencing templates in `include` and `extends` statements:

```
{include 'comment.tpl'}
{extends 'post.tpl'}

{include '@app/views/snippets/avatar.tpl'}
{extends '@app/views/layouts/2columns.tpl'}
```

In the first case the view will be searched relatively to the current template path. For `comment.tpl` and `post.tpl`
that means these will be searched in the same directory as the currently rendered template.

In the second case we're using path aliases. All the Yii aliases such as `@app` are available by default.

#### CSS, JavaScript and asset bundles

In order to register JavaScript and CSS files the following syntax could be used:

```
{registerJsFile url='http://maps.google.com/maps/api/js?sensor=false' position='POS_END'}
{registerCssFile url='@assets/css/normalizer.css'}
```

If you need JavaScript and CSS directly in the template there are convenient blocks:

```
{registerJs key='show' position='POS_LOAD'}
    $("span.show").replaceWith('<div class="show">');
{/registerJs}

{registerCss}
div.header {
    background-color: #3366bd;
    color: white;
}
{/registerCss}
```

Asset bundles could be registered the following way:

```
{use class="yii\web\JqueryAsset"}
{JqueryAsset::register($this)|void}
```

Here we're using `void` modifier because we don't need method call result.

#### URLs

There are two functions you can use for building URLs:

```php
<a href="{path route='blog/view' alias=$post.alias}">{$post.title}</a>
<a href="{url route='blog/view' alias=$post.alias}">{$post.title}</a>
```

`path` generates relative URL while `url` generates absolute one. Internally both are using [[\yii\helpers\Url]].

#### Additional variables

Within Smarty templates the following variables are always defined:

- `$app`, which equates to `\Yii::$app`
- `$this`, which equates to the current `View` object

#### Accessing config params

Yii parameters that are available in your application through `Yii::$app->params->something` could be used the following
way:

```
`{#something#}`
```
