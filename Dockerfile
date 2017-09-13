FROM dmstr/php-yii2:7.0-fpm-1.9-beta2-alpine-nginx

# Project source-code
WORKDIR /project
ADD composer.* /project/
RUN /usr/local/bin/composer install --prefer-dist
ADD ./ /project
ENV PATH /project/vendor/bin:${PATH}
