#!/bin/sh -e

if [ "$(expr "$TRAVIS_PHP_VERSION" "<" "5.5")" -eq 1 ]; then
	echo "extension = apc.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	echo "apc.enable_cli = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
else
	echo "Not installing APC as it is not available in PHP 5.5 anymore."
fi