# Yii 2.0 Unit tests

## DIRECTORY STRUCTURE

    data/            models, config and other test data
        config.php   this file contains host-side configuration for database backends
    docker/          Docker Compose files for local backend services
    framework/       the framework unit tests
    runtime/         the application runtime dir for the yii test app

## HOW TO RUN THE TESTS

Make sure all Composer dependencies are installed in the repository root.

Run PHPUnit in the yii repo base directory.

```bash
vendor/bin/phpunit
```

You can run tests for specific groups only:

```bash
vendor/bin/phpunit --group=mysql,base,i18n
```

You can get a list of available groups via `vendor/bin/phpunit --list-groups`.

A single test class could be run like the following:

```bash
vendor/bin/phpunit tests/framework/base/ObjectTest.php
```

## TEST CONFIGURATION

PHPUnit configuration is in `phpunit.xml.dist` in repository root folder.
You can create your own phpunit.xml to override dist config.

Database and backend configuration can be found in `tests/data/config.php`.
The default host-side settings expect services to be reachable on:

- `127.0.0.1:3306` for MySQL-compatible backends
- `localhost:5432` for PostgreSQL
- `127.0.0.1:1433` for SQL Server
- `localhost:1521/FREE` for Oracle

You can override configuration values by creating a `tests/data/config.local.php` file and manipulating the `$config` 
variable. For example, to change MySQL username and password your `config.local.php` should contain the following:

```php
<?php
$config['databases']['mysql']['username'] = 'yiitest';
$config['databases']['mysql']['password'] = 'changeme';
```

## Dockerized testing

Docker Compose files for local backend services are stored in `tests/docker/`.
These files start only the external services. PHPUnit always runs on the host and connects using the defaults from 
`tests/data/config.php`.

There is no `.env-dist` or shell wrapper anymore. If you need to override the image used by a backend, pass the variable
inline when starting the service. For example:

```bash
DOCKER_MYSQL_IMAGE=mysql:8.4 docker compose -f tests/docker/docker-compose.mysql.yml up -d --wait
```

Stop the running service before starting another backend that uses the same host port.

### MariaDB

```bash
docker compose -f tests/docker/docker-compose.mariadb.yml up -d --wait
vendor/bin/phpunit --group mysql
docker compose -f tests/docker/docker-compose.mariadb.yml down -v
```

MariaDB uses the same PHPUnit group as MySQL.

### Memcached caching tests

> [!IMPORTANT]
> Caching tests require the `memcached` PHP extension installed locally.

```bash
docker compose -f tests/docker/docker-compose.caching.yml up -d --wait
vendor/bin/phpunit --group caching --exclude-group db
docker compose -f tests/docker/docker-compose.caching.yml down -v
```

### MSSQL

> [!IMPORTANT]
> MSSQL tests require the `pdo_sqlsrv` PHP extension installed locally.

Start the SQL Server container, create the `yiitest` database inside the container,
run the test group, and then stop the service:

```bash
docker compose -f tests/docker/docker-compose.mssql.yml up -d --wait
docker compose -f tests/docker/docker-compose.mssql.yml exec -T mssql \
  /opt/mssql-tools18/bin/sqlcmd \
  -C \
  -S localhost \
  -U SA \
  -P 'YourStrong!Passw0rd' \
  -Q "IF DB_ID(N'yiitest') IS NULL CREATE DATABASE yiitest;"
vendor/bin/phpunit --group mssql
docker compose -f tests/docker/docker-compose.mssql.yml down -v
```

### MySQL

```bash
docker compose -f tests/docker/docker-compose.mysql.yml up -d --wait
vendor/bin/phpunit --group mysql
docker compose -f tests/docker/docker-compose.mysql.yml down -v
```

### Oracle tests

> [!IMPORTANT]
> Oracle tests require the `oci8` and `pdo_oci` PHP extensions installed locally.

Start the Oracle container and run the tests manually:

```bash
docker compose -f tests/docker/docker-compose.oracle.yml up -d --wait
vendor/bin/phpunit -v --group oci
docker compose -f tests/docker/docker-compose.oracle.yml down -v
```

### PostgreSQL

```bash
docker compose -f tests/docker/docker-compose.pgsql.yml up -d --wait
vendor/bin/phpunit -v --group pgsql
docker compose -f tests/docker/docker-compose.pgsql.yml down -v
```
