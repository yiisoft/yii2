#!/bin/sh -e

mkdir -p ~/.phpenv/versions/$(phpenv version-name)/etc

# memcache is not available on PHP 7, memcacheD is.
echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
