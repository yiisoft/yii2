FROM yiisoftware/yii2-php:7.1-apache

# Project source-code
WORKDIR /project
ADD composer.* /project/
RUN /usr/local/bin/composer install --prefer-dist
ADD ./ /project
ENV PATH /project/vendor/bin:${PATH}
