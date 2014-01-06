API documentation generator for Yii 2
=====================================

This extension provides an API documentation generator for the Yii framework 2.0.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-apidoc "*"
```

or add

```json
"yiisoft/yii2-apidoc": "*"
```

to the require section of your composer.json.

Usage
-----

To generate API documentation, run the `apidoc` command.

```
vendor/bin/apidoc source/directory ./output
```

By default the `offline` template will be used. You can choose a different templates with the `--template=name` parameter.
Currently there is only the `offline` template available.

You may also add the `yii\apidoc\commands\RenderController` to your console application class map and
run it inside of your applications console app.

Creating your own templates
---------------------------

TDB

Using the model layer
---------------------

TDB