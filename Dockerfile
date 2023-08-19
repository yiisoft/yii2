ARG DOCKER_YII2_PHP_IMAGE
FROM ${DOCKER_YII2_PHP_IMAGE}

# Project source-code
WORKDIR /project
ADD composer.* /project/
# Install packages
RUN /usr/local/bin/composer install --prefer-dist
ADD ./ /project
ENV PATH /project/vendor/bin:${PATH}
