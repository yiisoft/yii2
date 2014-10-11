#!/bin/sh -e
#
# install mongodb

mongod --version

echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# enable text search
mongo --eval 'db.adminCommand( { setParameter: true, textSearchEnabled : true})'

cat /etc/mongodb.conf
