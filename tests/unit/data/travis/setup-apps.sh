#!/bin/sh

if (php --version | grep -i HHVM > /dev/null); then
  echo "skipping application setup on HHVM"
else

    # basic application:

    composer install --dev --prefer-dist -d apps/basic
    cd apps/basic && composer require --dev codeception/codeception:2.0.* codeception/specify:* codeception/verify:*
    php vendor/bin/codecept build && cd ../..


    # advanced application:

    composer install --dev --prefer-dist -d apps/advanced
    cd apps/advanced && composer require --dev codeception/codeception:2.0.* codeception/specify:* codeception/verify:*
    ./init --env=Development
    sed -i s/root/travis/ common/config/main-local.php
    cd backend && php ../vendor/bin/codecept build
    cd ../common && php ../vendor/bin/codecept build
    cd ../frontend && php ../vendor/bin/codecept build && cd ../../..

    # boot server
    cd apps && php -S localhost:8080 > /dev/null 2>&1 &

fi
