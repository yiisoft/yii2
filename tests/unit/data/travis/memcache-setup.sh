#!/bin/sh

if (php --version | grep -i HHVM > /dev/null); then
  echo "skipping memcache on HHVM"
else
  echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
  echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi
