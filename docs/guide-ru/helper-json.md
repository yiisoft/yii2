Json хелпер
===========

Json хелпер предоставляет набор статических методов для кодирования и декодирования JSON.
Он обрабатывает ошибки кодирования, а метод [[yii\helpers\Json::encode()]] не кодирует JavaScript-выражения,
представленные в виде объектов [[yii\web\JsExpression]].
По умолчанию кодирование выполняется с опциями `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
Подробнее см. [PHP:json_encode](https://www.php.net/manual/ru/function.json-encode.php).

## Форматированный вывод <span id="pretty-print"></span>

По умолчанию [[yii\helpers\Json::encode()]] выводит неотформатированный JSON (без пробелов).
Чтобы сделать его удобочитаемым, можно включить форматированный вывод.

> Note: Форматированный вывод полезен при отладке во время разработки, но не рекомендуется в production-окружении.

Чтобы включить форматированный вывод в одном вызове, укажите соответствующую опцию:

```php
$data = ['a' => 1, 'b' => 2];
$json = yii\helpers\Json::encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```

Также можно включить форматированный вывод глобально. Например, в конфигурации или в index.php:

```php
yii\helpers\Json::$prettyPrint = YII_DEBUG; // форматированный вывод в режиме отладки
```
