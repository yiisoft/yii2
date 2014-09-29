Yii PHP Framework Version 2
===========================

This is the core framework code of [Yii 2](https://github.com/yiisoft/yii2#readme).


Installation
------------

The preferred way to install the Yii framework is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar global require "fxp/composer-asset-plugin:1.0.0-beta2"
php composer.phar require --prefer-dist "yiisoft/yii2 2.0.0-rc" "yiisoft/yii2-composer 2.0.0-rc"
```
`2.0.0-rc` versions may be replaced by `dev-master`, to obtain development version.

or add

```json
"yiisoft/yii2": "2.0.0-rc",
"yiisoft/yii2-composer": "2.0.0-rc"
```

to the require section of your composer.json.

Next, you should modify the entry script of your app system by including the following code at the beginning:

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$yiiConfig = require(__DIR__ . '/../config/yii/web.php');
new yii\web\Application($yiiConfig);
```
