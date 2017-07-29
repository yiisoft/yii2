#!/bin/sh -e

yes '' | pecl install -f apcu-5.1.8
#echo "extension = apcu.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "apc.enabled = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
echo "apc.enable_cli = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
