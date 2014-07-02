#!/bin/sh

if (php --version | grep -i HHVM > /dev/null); then
  echo "skipping application init on HHVM"
else

    mysql -e 'CREATE DATABASE yii2_advanced_acceptance;';
    mysql -e 'CREATE DATABASE yii2_advanced_functional;';
    mysql -e 'CREATE DATABASE yii2_advanced_unit;';
    cd apps/advanced/frontend/tests/acceptance && php yii migrate --interactive=0
    cd ../functional && php yii migrate --interactive=0
    cd ../unit && php yii migrate --interactive=0 && cd ../../../../..

fi
