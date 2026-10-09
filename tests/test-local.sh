#!/usr/bin/env bash

# TODO: add mariadb, sqlite and oracle
case $1 in
'default')
    shift
    docker compose up -d
    docker compose run --rm php vendor/bin/phpunit -v $@
    docker compose down -v
  ;;
'caching')
    export COMPOSE_FILE=docker-compose.caching.yml
    export COMPOSE_PROJECT_NAME=yii2tests-default
    shift
    docker compose up --build -d
    docker compose run --rm php vendor/bin/phpunit -v --group caching --exclude-group db $@
    docker compose down -v --remove-orphans
  ;;
'mssql')
    export COMPOSE_FILE=docker-compose.mssql.yml
    export COMPOSE_PROJECT_NAME=yii2tests-mssql
    shift
    docker compose up --build -d
    docker compose run --rm mssql /opt/mssql-tools18/bin/sqlcmd -C -S mssql -U SA -P YourStrong!Passw0rd -Q "CREATE DATABASE yiitest"
    docker compose run --rm php vendor/bin/phpunit -v --group mssql $@
    docker compose down -v --remove-orphans
  ;;
'pgsql')
    export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml
    export COMPOSE_PROJECT_NAME=yii2tests-pgsql
    shift
    docker compose up -d
    docker compose run --rm php vendor/bin/phpunit -v --group pgsql $@
    docker compose down -v
  ;;
'mysql')
    export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml
    export COMPOSE_PROJECT_NAME=yii2tests-mysql
    shift
    docker compose up -d
    docker compose run --rm php vendor/bin/phpunit -v --group mysql $@
    docker compose down -v
  ;;
*)
    echo "Warning: No job argument specified"
  ;;
esac

echo "Done."
