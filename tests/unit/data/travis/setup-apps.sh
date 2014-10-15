#!/bin/sh -e

if (php --version | grep -i HipHop > /dev/null); then
  echo "skipping application setup on HHVM"
else

    # basic application:

    composer install --dev --prefer-dist -d apps/basic
    cd apps/basic && sed -i "s/'cookieValidationKey' => ''/'cookieValidationKey' => 'testkey'/" config/web.php
    cd tests && codecept build && cd ../../..


    # advanced application:

    composer install --dev --prefer-dist -d apps/advanced
    cd apps/advanced && ./init --env=Development
    sed -i s/root/travis/ common/config/main-local.php
    sed -i "s/'cookieValidationKey' => ''/'cookieValidationKey' => 'testkey'/" frontend/config/main.php
    sed -i "s/'cookieValidationKey' => ''/'cookieValidationKey' => 'testkey'/" backend/config/main.php
    cd tests/codeception/backend && codecept build
    cd ../common && codecept build
    cd ../console && codecept build
    cd ../frontend && codecept build
    cd ../../../
fi
