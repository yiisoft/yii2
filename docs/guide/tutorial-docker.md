Yii and Docker
==============

For development and deployments Yii applications can be run as Docker containers.

You can read more about Docker containers on [docker.com](https://www.docker.com/what-docker).

## Requirements

- `docker`
- `docker-compose`

Visit the [download page](https://www.docker.com/community-edition) for Docker tools.

## Resources

PHP-base images for Yii can be found at [yii2-docker](https://github.com/yiisoft/yii2-docker).


*TBD:* Docker support for [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced/pull/347).

## Setup

After installation of Docker, you should be able to run `docker ps` and see an output similar to

```
CONTAINER ID   IMAGE   COMMAND   CREATED   STATUS   PORTS
```

## Usage

For a quick-start you can run the dockerized framework tests as described [here](https://github.com/yiisoft/yii2/blob/master/tests/README.md#dockerized-testing).

## Advanced topics

### Database administration tools

When running MySQL as (`mysql`), you can add for example a PHPmyAdmin container to your stack.

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