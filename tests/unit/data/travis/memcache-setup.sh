#!/bin/sh

if (php --version | grep -i HipHop > /dev/null); then
  echo "skipping memcache on HHVM"
else
  mkdir -p ~/.phpenv/versions/$(phpenv version-name)/etc
  echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
  echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi
