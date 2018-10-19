#!/usr/bin/env bash

# Generate a "hash" from 0-99 for subnet tuple (10, 11, ..., 199) - it's a hack
echo "Creating CI_BUILD_ID and CI_PIPELINE_ID from first argument..."
export CI_BUILD_ID=$(expr $((32#${1})) % 99)
export CI_PIPELINE_ID=${1}

# Copy snippets from https://git.hrzg.de/ci/lint
case $1 in
'default')
    export ISOLATION=buildpipeline${CI_PIPELINE_ID}
    export COMPOSE_PROJECT_NAME=${ISOLATION}default
    docker-compose up -d
    docker-compose run --rm php vendor/bin/phpunit -v --exclude caching,db
    docker-compose down -v
  ;;
'caching')
    export ISOLATION=buildpipeline${CI_PIPELINE_ID}
    export COMPOSE_PROJECT_NAME=${ISOLATION}caching
    docker-compose up -d
    docker-compose run --rm php bash -c "while ! curl mysql:3306; do ((c++)) && ((c==30)) && break; sleep 2; done"
    docker-compose run --rm php vendor/bin/phpunit -v --group caching
    docker-compose down -v
  ;;
'mssql')
    export ISOLATION=buildpipeline${CI_PIPELINE_ID}
    export COMPOSE_PROJECT_NAME=${ISOLATION}mssql
    cd mssql
    docker-compose up --build -d
    docker-compose run --rm php bash -c 'while [ true ]; do curl mssql:1433; if [ $? == 52 ]; then break; fi; ((c++)) && ((c==15)) && break; sleep 5; done'
    sleep 10
    docker-compose run --rm sqlcmd sh -c 'sqlcmd -S mssql -U sa -Q "CREATE DATABASE yii2test" -P Microsoft-12345'
    sleep 10
    docker-compose logs mssql
    docker-compose config
    pwd
    docker-compose ps
    docker-compose run --rm php php -i
    docker-compose run --rm php vendor/bin/phpunit -v --group mssql
    docker-compose down -v --remove-orphans
  ;;
'pgsql')
    export ISOLATION=buildpipeline${CI_PIPELINE_ID}
    export COMPOSE_PROJECT_NAME=${ISOLATION}pgsql
    docker-compose up -d
    docker-compose run --rm php bash -c 'while [ true ]; do curl postgres:5432; if [ $? == 52 ]; then break; fi; ((c++)) && ((c==25)) && break; sleep 2; done'
    docker-compose run --rm php vendor/bin/phpunit -v --group pgsql
    docker-compose down -v
  ;;
*)
    echo "Warning: No job argument specified"
  ;;
esac

echo "Done."