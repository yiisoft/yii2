Yii и Docker
=============

Для разработки и деплоя приложения Yii можно запускать в Docker-контейнерах. Контейнер - это легковесная изолированная виртуальная машина, которая пробрасывает свои сервисы на порты хоста, т.е. веб-сервер в контейнере на порту 80 доступен на порту 8888 вашего (локального) хоста.

Контейнеры решают множество проблем: одинаковые версии ПО на машине разработчика и сервере, быстрый деплой, имитация многосерверной архитектуры при разработке.

Подробнее о Docker-контейнерах можно прочитать на [docker.com](https://www.docker.com/why-docker).

## Требования

- `docker`
- `docker-compose`

Для установки Docker перейдите на [страницу загрузки](https://www.docker.com/products/container-runtime).

## Установка

После установки команда `docker ps` должна вывести примерно следующее:

```
CONTAINER ID   IMAGE   COMMAND   CREATED   STATUS   PORTS
```

Это значит, что демон Docker запущен и работает.

Также выполните `docker-compose version`, вывод должен быть похож на:

```
docker-compose version 1.20.0, build unknown
docker-py version: 3.1.3
CPython version: 3.6.4
OpenSSL version: OpenSSL 1.1.0g  2 Nov 2017
```

С помощью Compose можно настраивать и управлять всеми сервисами, необходимыми приложению, - базами данных, кэшированием и т.д.

## Ресурсы

- Базовые PHP-образы для Yii: [yii2-docker](https://github.com/yiisoft/yii2-docker)
- Поддержка Docker для [yii2-app-basic](https://github.com/yiisoft/yii2-app-basic#install-with-docker)
- Поддержка Docker для [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced/pull/347) в разработке

## Использование

Основные команды Docker:

    docker-compose up -d

запуск всех сервисов стека в фоновом режиме

    docker-compose ps

список запущенных сервисов

    docker-compose logs -f

непрерывный просмотр логов всех сервисов

    docker-compose stop

корректная остановка всех сервисов стека

    docker-compose kill

немедленная остановка всех сервисов стека

    docker-compose down -v

остановка и удаление всех сервисов, **будьте осторожны с потерей данных при отсутствии host-volumes**

Запуск команд в контейнере:

    docker-compose run --rm php composer install

запуск установки Composer в новом контейнере

    docker-compose exec php bash

запуск bash в *работающем* сервисе `php`


## Продвинутые темы

### Тесты фреймворка Yii

Вы можете запустить тесты фреймворка Yii в Docker, как описано [здесь](https://github.com/yiisoft/yii2/blob/master/tests/README.md#dockerized-testing).

### Инструменты администрирования баз данных

При использовании MySQL (`mysql`) можно добавить контейнер phpMyAdmin в стек следующим образом:

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
