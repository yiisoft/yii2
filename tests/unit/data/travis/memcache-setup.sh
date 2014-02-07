#!/bin/bash

if [ "$TRAVIS_PHP_VERSION" != "hhvm" ]; then
	echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	echo "extension=memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
else
	echo "Skipping memcache installation on HHVM."
fi