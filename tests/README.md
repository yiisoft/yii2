# Yii 2.0 Unit tests

## DIRECTORY STRUCTURE

    data/            models, config and other test data
        config.php   this file contains configuration for database and caching backends
    framework/       the framework unit tests
    runtime/         the application runtime dir for the yii test app

## HOW TO RUN THE TESTS

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

A single test class could be run like the following:

```
phpunit tests/framework/base/ObjectTest.php
```

## TEST CONFIGURATION

PHPUnit configuration is in `phpunit.xml.dist` in repository root folder.
You can create your own phpunit.xml to override dist config.

Database and other backend system configuration can be found in `tests/data/config.php`
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

## DOCKERIZED TESTING

Get started by going to the `tests` directory and copy the environment configuration.

```bash
cd tests
cp .env-dist .env
```

The newly created `.env` file defines the configuration files used by `docker-compose`. By default MySQL, Postgres etc. services are disabled.

> You can choose services available for testing by merging `docker-compose.[...].yml` files in `.env`. For example, if you only want to test with MySQL, you can modify the `COMPOSE_FILE` variable as follows:

```env
COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml
```

> Note: The files `docker-compose.caching.yml` and `docker-compose.mssql.yml` cannot be merged with `docker-compose.yml`.

### Running tests via shell script

You need to go to the `tests` directory and run the `test-local.sh` script. The first argument can be: `default`, `caching`, `mssql`, `pgsql`, `mysql`. You can pass additional arguments to this script to control the behavior of PHPUnit. For example:

```bash
cd tests
sh test-local.sh default --exclude caching,db
```

### Manually running the tests

You can also run tests manually. To do this, you need to start the container and run the tests. For example:

```bash
docker compose up -d
docker compose exec php vendor/bin/phpunit -v
```
