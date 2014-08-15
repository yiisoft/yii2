#!/bin/sh

if (php --version | grep -i HipHop > /dev/null); then
  echo "skipping application init on HHVM"
else

    mysql -e 'CREATE DATABASE yii2_advanced_acceptance;';
    mysql -e 'CREATE DATABASE yii2_advanced_functional;';
    mysql -e 'CREATE DATABASE yii2_advanced_unit;';
    cd apps/advanced/tests/codeception/bin
    php yii_acceptance migrate --interactive=0
    php yii_functional migrate --interactive=0
    php yii_unit migrate --interactive=0
    cd ../../../../..
fi
