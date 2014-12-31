Gii Extension for Yii 2
========================

This extension provides a Web-based code generator, called Gii, for Yii 2 applications.
You can use Gii to quickly generate models, forms, modules, CRUD, etc.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-gii "*"
```

or add

```
"yiisoft/yii2-gii": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
        // ...
    ],
    // ...
];
```

You can then access Gii through the following URL:

```
http://localhost/path/to/index.php?r=gii
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/gii
```

Using the same configuration for your console application, you will also be able to access Gii via
command line as follows,

```
# change path to your application's base path
cd path/to/AppBasePath

# show help information about Gii
yii help gii

# show help information about the model generator in Gii
yii help gii/model

# generate City model from city table
yii gii/model --tableName=city --modelClass=City
```
