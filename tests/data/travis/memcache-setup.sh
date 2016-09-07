#!/bin/sh -e

if (php --version | grep -i HipHop > /dev/null); then
  echo "skipping memcache on HHVM"
else
  mkdir -p ~/.phpenv/versions/$(phpenv version-name)/etc

  # memcache is not available on PHP 7, memcacheD is.
  if [ $(phpenv version-name) = 5.* ]; then
    echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
  else
    echo "skipping memcache on php 7"
  fi
  echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi
