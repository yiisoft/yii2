#!/bin/sh
#
# install mongodb

mongod --version

echo "extension = mongo.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# version 2.6 has this enabled by default
#sudo sh -c 'echo "setParameter = textSearchEnabled=true" >> /etc/mongodb.conf'
cat /etc/mongodb.conf

#sudo service mongodb restart
