#!/bin/sh
#
# install mongodb

echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
sudo sh -c 'echo "setParameter = textSearchEnabled=true" >> /etc/mongodb.conf'
cat /etc/mongodb.conf

mongod --version

sudo service mongodb restart
