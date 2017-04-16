#!/bin/sh

php -r "readfile('https://getcomposer.org/installer');" | php &&
./composer.phar install
