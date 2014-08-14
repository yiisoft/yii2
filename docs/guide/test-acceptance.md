Acceptance Tests
================

> Note: This section is under development.

- http://codeception.com/docs/04-AcceptanceTests
- https://github.com/yiisoft/yii2/blob/master/apps/advanced/README.md#testing
- https://github.com/yiisoft/yii2/blob/master/apps/basic/tests/README.md

How to run webserver
--------------------

In order to perform acceptance tests you need a web server. Since PHP 5.4 has built-in one, we can use it. For the basic
application template it would be:

```
cd web
php -S localhost:8080
```

In order for the tests to work correctly you need to adjust `TEST_ENTRY_URL` in `_bootstrap.php` file. It should point
to `index-test.php` of your webserver. Since we're running directly from its directory the line would be:

```php
defined('TEST_ENTRY_URL') or define('TEST_ENTRY_URL', '/index-test.php');
```
