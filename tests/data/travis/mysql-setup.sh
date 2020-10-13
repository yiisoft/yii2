#!/bin/sh -e

sudo service mysql stop

docker run --name mysql -p 3306:3306 -e MYSQL_ROOT_PASSWORD=root -d mysql:5.7

while ! mysqladmin ping -h 127.0.0.1 --silent; do
    sleep 1
done
