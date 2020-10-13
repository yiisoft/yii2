ARG DOCKER_YII2_PHP_IMAGE
FROM ${DOCKER_YII2_PHP_IMAGE}

# Project source-code
WORKDIR /project
ADD composer.* /project/
# Apply testing patches
ADD tests/phpunit_mock_objects.patch /project/tests/phpunit_mock_objects.patch
ADD tests/phpunit_getopt.patch /project/tests/phpunit_getopt.patch
# Install packgaes
RUN /usr/local/bin/composer install --prefer-dist
ADD ./ /project
ENV PATH /project/vendor/bin:${PATH}
