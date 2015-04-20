Yii 2.0 Unit tests
==================

DIRECTORY STRUCTURE
-------------------

    data/            models, config and other test data
        config.php   this file contains configuration for database and caching backends
    framework/       the framework unit tests
    runtime/         the application runtime dir for the yii test app


HOW TO RUN THE TESTS
--------------------

Make sure you have PHPUnit installed and that you installed all composer dependencies (run `composer update` in the repo base directory).

Run PHPUnit in the yii repo base directory.

```
phpunit
```

You can run tests for specific groups only:

```
phpunit --group=mysql,base,i18n
```

You can get a list of available groups via `phpunit --list-groups`.

A single test class could be run like the follwing:

```
phpunit tests/framework/base/ObjectTest.php
```

TEST CONFIGURATION
------------------

PHPUnit configuration is in `phpunit.xml.dist` in repository root folder.
You can create your own phpunit.xml to override dist config.

Database and other backend system configuration can be found in `unit/data/config.php`
adjust them to your needs to allow testing databases and caching in your environment.
You can override configuration values by creating a `config.local.php` file
and manipulate the `$config` variable.
For example to change MySQL username and password your `config.local.php` should
contain the following:

```php
<?php
$config['databases']['mysql']['username'] = 'yiitest';
$config['databases']['mysql']['password'] = 'changeme';
```
