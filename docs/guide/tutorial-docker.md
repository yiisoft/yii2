Yii and Docker
==============

For development and deployments Yii applications can be run as Docker containers. A container is like a lightweight isolated virtual machine that maps its services to host's ports, i.e. a webserver in a container on port 80 is available on port 8888 on your (local)host. 

Containers can solve many issues such as having identical software versions at developer's computer and the server, fast deployments or simulating multi-server architecture while developing.

You can read more about Docker containers on [docker.com](https://www.docker.com/why-docker).

## Requirements

- `docker`
- `docker-compose`

Visit the [download page](https://www.docker.com/products/container-runtime) to get the Docker tooling.

## Installation

After installation, you should be able to run `docker ps` and see an output similar to

```
CONTAINER ID   IMAGE   COMMAND   CREATED   STATUS   PORTS
```

This means your Docker daemon is up and running.

Additionally, run `docker-compose version`, your output should look like this

```
docker-compose version 1.20.0, build unknown
docker-py version: 3.1.3
CPython version: 3.6.4
OpenSSL version: OpenSSL 1.1.0g  2 Nov 2017
```

With Compose you can configure manage all services required for your application, such as databases and caching.

## Resources

- PHP-base images for Yii can be found at [yii2-docker](https://github.com/yiisoft/yii2-docker)
- Docker support for [yii2-app-basic](https://github.com/yiisoft/yii2-app-basic#install-with-docker)
- Docker support for [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced/pull/347) is in development

## Usage

Basic commands for Docker are

    docker-compose up -d
    
to start all services in your stack, in the background

    docker-compose ps
    
to list running services

    docker-compose logs -f
    
to view logs for all services, continuously

    docker-compose stop
    
to stop all services in your stack, gracefully

    docker-compose kill
    
to stop all services in your stack, immediately

    docker-compose down -v
    
to stop and remove all services, **be aware of data loss when not using host-volumes**

To run commands in a container

    docker-compose run --rm php composer install
    
runs composer installation in a new container

    docker-compose exec php bash
    
executes a bash in a *running* `php` service


## Advanced topics

### Yii framework tests

You can run the dockerized framework tests for Yii itself as described [here](https://github.com/yiisoft/yii2/blob/master/tests/README.md#dockerized-testing).

### Database administration tools

When running MySQL as (`mysql`), you can add phpMyAdmin container to your stack like the following:

```
    phpmyadmin:
        image: phpmyadmin/phpmyadmin
        ports:
            - '8888:80'
        environment:
            - PMA_ARBITRARY=1
            - PMA_HOST=mysql
        depends_on:
            - mysql
```
