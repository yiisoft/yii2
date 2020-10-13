FROM php:5-fpm

# /usr/local/lib/php/extensions/no-debug-non-zts-20131226/cubrid.so
RUN pecl install pdo_cubrid-9.3.0.0001
RUN echo "extension=pdo_cubrid.so" > /usr/local/etc/php/conf.d/cubrid.ini


# Install system packages for composer (git)
RUN apt-get update && \
    apt-get -y install \
            git \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
# Register the COMPOSER_HOME environment variable
ENV COMPOSER_HOME /composer
# Add global binary directory to PATH and make sure to re-export it
ENV PATH /usr/local/bin:$PATH
# Allow Composer to be run as root
ENV COMPOSER_ALLOW_SUPERUSER 1
# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer.phar \
        --install-dir=/usr/local/bin


# Project source-code
WORKDIR /project
ADD composer.* /project/
RUN /usr/local/bin/composer.phar install --prefer-dist
ADD ./ /project
