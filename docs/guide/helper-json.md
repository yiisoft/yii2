Json Helper
==========

Json helper provides a set of static methods for encoding and decoding JSON.
It handles encoding errors and the `[[yii\helpers\Json::encode()]]` method will not encode a JavaScript expression that is represented in 
terms of a `[[yii\web\JsExpression]]` object.
By default, encoding is done with the `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE` options.
Please see [PHP:json_encode](https://www.php.net/manual/en/function.json-encode.php) for more information.

## Pretty Print <span id="pretty-print"></span>

By default the `[[yii\helpers\Json::encode()]]` method will output unformatted JSON (e.g. without whitespaces).
To make it more readable for humans you can turn on "pretty printing".

> Note: Pretty Print can useful for debugging during development but is not recommended in a production environment. 

To enable pretty print in a single instance you can specify it as an option. E.g.

```php
$data = ['a' => 1, 'b' => 2];
$json = yii\helpers\Json::encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```
You can also enable pretty printing of the JSON helper globally. For example in your config for or index.php: 
```php
yii\helpers\Json::$prettyPrint = YII_DEBUG; // use "pretty" output in debug mode
```
