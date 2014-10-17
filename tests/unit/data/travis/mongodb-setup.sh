#!/bin/sh -e
#
# install mongodb

mongod --version

if (php --version | grep -i HipHop > /dev/null); then
  echo "mongodb does not work on HHVM currently, skipping"
else
  echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi

# enable text search
mongo --eval 'db.adminCommand( { setParameter: true, textSearchEnabled : true})'

cat /etc/mongodb.conf
