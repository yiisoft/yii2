Yii 2.0 Unit tests
==================

DIRECTORY STRUCTURE
-------------------

      unit/                Unit tests to run with PHPUnit
          data/            models, config and other test data
              config.php   this file contains configuration for database and caching backends
          framework/       the framework unit tests
          runtime/         the application runtime dir for the yii test app
      web/                 webapp for functional testing


HOW TO RUN THE TESTS
--------------------

Make sure you have PHPUnit installed.

Run PHPUnit in the yii repo base directory.

```php
phpunit
```

You can run tests for specific groups only:

```php
phpunit --group=mysql,base,i18n
```

You can get a list of available groups via `phpunit --list-groups`.

TEST CONFIGURATION
------------------

PHPUnit configuration is in `phpunit.xml.dist` in repository root folder.
You can create your own phpunit.xml to override dist config.

Database and other backend system configuration can be found in `unit/data/config.php`
adjust them to your needs to allow testing databases and caching in your environment.