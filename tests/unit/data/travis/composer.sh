#!/bin/sh -e

composer self-update && composer --version

composer install --prefer-dist

if [ "$TRAVIS_PHP_VERSION" == "hhvm" ]; then
	composer require "phpunit/phpunit dev-hhvm" --prefer-dist
fi