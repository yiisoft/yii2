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

You can combine them to generate class API and guide documentation in one place:

```
# generate API docs
vendor/bin/apidoc api source/directory ./output
# generate the guide (order is important to allow the guide to link to the apidoc)
vendor/bin/apidoc guide source/docs ./output
```

By default the `bootstrap` template will be used. You can choose a different template with the `--template=name` parameter.
Currently there is only the `bootstrap` template available.

You may also add the `yii\apidoc\commands\ApiController` and `GuideController` to your console application command map
and run them inside of your applications console app.

### Advanced usage

The following script can be used to generate API documentation and guide in different directories and also multiple guides
in different languages (like it is done on yiiframework.com):

```sh
#!/bin/sh

# set these paths to match your environment
YII_PATH=~/dev/yiisoft/yii2
APIDOC_PATH=~/dev/yiisoft/yii2/extensions/apidoc
OUTPUT=yii2docs

cd $APIDOC_PATH
./apidoc api $YII_PATH/framework/,$YII_PATH/extensions $OUTPUT/api --guide=../guide-en --guidePrefix= --interactive=0
./apidoc guide $YII_PATH/docs/guide    $OUTPUT/guide-en --apiDocs=../api --guidePrefix= --interactive=0
./apidoc guide $YII_PATH/docs/guide-ru $OUTPUT/guide-ru --apiDocs=../api --guidePrefix= --interactive=0
# repeat the last line for more languages
```

### Creating a PDF of the guide

You need `pdflatex` and GNU `make` for this.

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
