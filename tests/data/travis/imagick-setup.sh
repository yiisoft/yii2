#!/bin/sh -e

if [ $(phpenv version-name) = '5.4' ] || [ $(phpenv version-name) = '5.5' ] || [ $(phpenv version-name) = '5.6' ]; then
	yes '' | pecl install imagick
fi
