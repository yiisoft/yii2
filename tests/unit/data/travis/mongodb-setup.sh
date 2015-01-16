#!/bin/sh -e
#
# install mongodb

if (php --version | grep -i HipHop > /dev/null); then
  echo "mongodb does not work on HHVM currently, skipping"
  exit 0
else
  echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi

echo "MongoDB Server version:"
mongod --version

echo "MongoDB PHP Extension version:"
php -i |grep mongo -4 |grep -2 Version

# enable text search
mongo --eval 'db.adminCommand( { setParameter: true, textSearchEnabled : true})'

cat /etc/mongodb.conf
