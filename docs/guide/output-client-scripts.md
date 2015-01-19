Working with Client Scripts
===========================

> Note: This section is under development.

### Registering scripts

With the [[yii\web\View]] object you can register scripts. There are two dedicated methods for it:
[[yii\web\View::registerJs()|registerJs()]] for inline scripts and
[[yii\web\View::registerJsFile()|registerJsFile()]] for external scripts.
Inline scripts are useful for configuration and dynamically generated code.
The method for adding these can be used as follows:

```php
$this->registerJs("var options = ".json_encode($options).";", View::POS_END, 'my-options');
```

The first argument is the actual JS code we want to insert into the page. The second argument
determines where script should be inserted into the page. Possible values are:

- [[yii\web\View::POS_HEAD|View::POS_HEAD]] for head section.
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] for right after opening `<body>`.
- [[yii\web\View::POS_END|View::POS_END]] for right before closing `</body>`.
- [[yii\web\View::POS_READY|View::POS_READY]] for executing code on document `ready` event. This will register [[yii\web\JqueryAsset|jQuery]] automatically.
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] for executing code on document `load` event. This will register [[yii\web\JqueryAsset|jQuery]] automatically.

The last argument is a unique script ID that is used to identify code block and replace existing one with the same ID
instead of adding a new one. If you don't provide it, the JS code itself will be used as the ID.

An external script can be added like the following:

```php
$this->registerJsFile('http://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
```

The arguments for [[yii\web\View::registerJsFile()|registerJsFile()]] are similar to those for
[[yii\web\View::registerCssFile()|registerCssFile()]]. In the above example,
we register the `main.js` file with the dependency on `JqueryAsset`. This means the `main.js` file
will be added AFTER `jquery.js`. Without this dependency specification, the relative order between
`main.js` and `jquery.js` would be undefined.

Like for [[yii\web\View::registerCssFile()|registerCssFile()]], it is also highly recommended that you use
[asset bundles](structure-assets.md) to register external JS files rather than using [[yii\web\View::registerJsFile()|registerJsFile()]].


### Registering asset bundles

As was mentioned earlier it's preferred to use asset bundles instead of using CSS and JavaScript directly. You can get
details on how to define asset bundles in [asset manager](structure-assets.md) section of the guide. As for using already defined
asset bundle, it's very straightforward:

```php
\frontend\assets\AppAsset::register($this);
```



### Registering CSS

You can register CSS using [[yii\web\View::registerCss()|registerCss()]] or [[yii\web\View::registerCssFile()|registerCssFile()]].
The former registers a block of CSS code while the latter registers an external CSS file. For example,

```php
$this->registerCss("body { background: #f00; }");
```

The code above will result in adding the following to the head section of the page:

```html
<style>
body { background: #f00; }
</style>
```

If you want to specify additional properties of the style tag, pass an array of name-values to the third argument.
If you need to make sure there's only a single style tag use fourth argument as was mentioned in meta tags description.

```php
$this->registerCssFile("http://example.com/css/themes/black-and-white.css", [
    'depends' => [BootstrapAsset::className()],
    'media' => 'print',
], 'css-print-theme');
```

The code above will add a link to CSS file to the head section of the page.

* The first argument specifies the CSS file to be registered.
* The second argument specifies the HTML attributes for the resulting `<link>` tag. The option `depends`
  is specially handled. It specifies which asset bundles this CSS file depends on. In this case, the dependent
  asset bundle is [[yii\bootstrap\BootstrapAsset|BootstrapAsset]]. This means the CSS file will be added
  *after* the CSS files in [[yii\bootstrap\BootstrapAsset|BootstrapAsset]].
* The last argument specifies an ID identifying this CSS file. If it is not provided, the URL of the CSS file will be
  used instead.


It is highly recommended that you use [asset bundles](structure-assets.md) to register external CSS files rather than
using [[yii\web\View::registerCssFile()|registerCssFile()]]. Using asset bundles allows you to combine and compress
multiple CSS files, which is desirable for high traffic websites.
