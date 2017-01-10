FROM codemix/yii2-base:2.0-apache

# Install system packages for PHP extensions recommended for Yii 2.0 Framework
RUN apt-key update && \
    apt-get update && \
    apt-get -y install \
            g++ \
            git \
            libicu-dev \
            libmcrypt-dev \
            libfreetype6-dev \
            libjpeg-dev \
            libjpeg62-turbo-dev \
            libmcrypt-dev \
            libpng12-dev \
            libpq5 \
            libpq-dev \
            zlib1g-dev \
            mysql-client \
            openssh-client \
            libxml2-dev \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions required for Yii 2.0 Framework
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ && \
    docker-php-ext-configure bcmath && \
    docker-php-ext-install \
        gd \
        intl \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        mcrypt \
        zip \
        bcmath \
        soap


# Project source-code
WORKDIR /project
ADD composer.* /project/
RUN /usr/local/bin/composer install --prefer-dist
ADD ./ /project
