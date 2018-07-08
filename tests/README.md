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


DOCKERIZED TESTING
------------------

Get started by going to the `tests` directory and copy the environment configuration.

    cd tests
    cp .env-dist .env

The newly created `.env` file defines the configuration files used by `docker-compose`. By default MySQL, Postgres and Caching services are enabled.

> You can choose services available for testing by merging `docker-compose.[...].yml` files in `.env`. For example, if you only want to test with MySQL, you can modify the `COMPOSE_FILE` variable as follows 

>     COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml

When starting the stack now, you get containers for databases and caching servers to test with.

    docker-compose up -d

After all services have been initialized and the stack is fully up and running enter the PHP container    
    
    docker-compose exec php bash

Run a group of unit tests
    
    $ vendor/bin/phpunit -v --group base --debug

#### Examples for running phpunit in a separate container
    
    docker-compose run php vendor/bin/phpunit -v --group caching,db   
    docker-compose run php vendor/bin/phpunit -v --exclude base,caching,db,i18n,log,mutex,rbac,validators,web
    docker-compose run php vendor/bin/phpunit -v --exclude wincache,xcache

> Note: Documentation about [installing additional extensions](https://github.com/yiisoft/yii2-docker/blob/master/docs/install-extensions.md) can be found at `yiisoft/yii2-docker`.

### MSSQL

> Note: Images for testing MSSQL are based on `bylexus/apache-php7` (Ubuntu) since drivers are not available for Debian or Alpine.     

**experimental**

- needs 3.5 GB RAM, Docker-host with >4.5 GB is recommended for testing
- database CLI `tsgkadot/mssql-tools`   

Example commands    
    
    cd tests/mssql

Using a shell    
    
    docker-compose run --rm sqlcmd sqlcmd -S mssql -U sa -P Microsoft-12345

Create database with sqlcmd     
     
    $ sqlcmd -S mssql -U sa -P Microsoft-12345 -Q "CREATE DATABASE yii2test"

Create database (one-liner)

    docker-compose run --rm sqlcmd sqlcmd -S mssql -U sa -P Microsoft-12345 -Q "CREATE DATABASE yii2test"

Run MSSQL tests

    docker-compose run php 
    $ vendor/bin/phpunit --group mssql

### Build triggers

    curl -X POST \
         -F token=${TOKEN} \
         -F ref=travis \
         -F "variables[DOCKER_MYSQL_IMAGE]=mysql:5.6" \
         -F "variables[DOCKER_POSTGRES_IMAGE]=postgres:9.5" \
         ${TRIGGER_URL}

### Run tests locally

#### Via shell script
    
    cd tests
    sh test-local.sh default

#### Via runner

*experimental*

docker-compose configuration

    runner:
      image: schmunk42/gitlab-runner
      entrypoint: bash
      working_dir: /project
      volumes:
        - ../:/project
        - /var/run/docker.sock:/var/run/docker.sock
      environment:
        - RUNNER_BUILDS_DIR=${PWD}/..    

Start runner bash        
        
    docker-compose -f docker-compose.runner.yml run runner

Execute jobs via shell runner (with docker-compose support)    
    
    $ gitlab-runner exec shell build
    $ gitlab-runner exec shell test
    
        