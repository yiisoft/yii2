API documentation generator for Yii 2
=====================================

This extension provides an API documentation generator for the Yii framework 2.0.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-apidoc "*"
```

or add

```json
"yiisoft/yii2-apidoc": "*"
```

to the require section of your composer.json.

Usage
-----

This extension offers two commands:

- `api` to generate class API documentation.
- `guide` to render nice HTML pages from markdown files such as the yii guide.

Simple usage for stand alone class documentation:

```
vendor/bin/apidoc api source/directory ./output
```

Simple usage for stand alone guide documentation:

```
vendor/bin/apidoc guide source/docs ./output
```

You can combine them to generate class API and guide doc in one place:

```
# first generate guide docs to allow links from code to guide you may skip this if you do not need these.
vendor/bin/apidoc guide source/docs ./output
# second generate API docs
vendor/bin/apidoc api source/directory ./output
# third run guide docs again to have class links enabled
vendor/bin/apidoc guide source/docs ./output
```

By default the `bootstrap` template will be used. You can choose a different templates with the `--template=name` parameter.
Currently there is only the `bootstrap` template available.

You may also add the `yii\apidoc\commands\RenderController` to your console application class map and
run it inside of your applications console app.

Creating a PDF from the guide
-----------------------------

You need `pdflatex` and `make` for this.

```
vendor/bin/apidoc guide source/docs ./output --template=pdf
cd ./output
make pdf
```

If all runs without errors the PDF will be `guide.pdf` in the `output` dir.

Special Markdown Syntax
-----------------------

We have a special Syntax for linking to classes in the API documentation.
See the [code style guide](https://github.com/yiisoft/yii2/blob/master/docs/internals/core-code-style.md#markdown) for details.

Creating your own templates
---------------------------

TDB

Using the model layer
---------------------

TDB
