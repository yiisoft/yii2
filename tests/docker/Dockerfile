FROM alpine
RUN apk add --update --no-cache \
    php7-common \
    php7 \
    php7-ctype \
    php7-curl \
    php7-dom \
    php7-fileinfo \
    php7-gd \
    php7-iconv \
    php7-intl \
    php7-json \
    php7-mbstring \
    php7-mcrypt \
    php7-pcntl \
    php7-pdo \
    php7-openssl \
    php7-memcached \
    php7-mysqlnd \
    php7-pdo_mysql \
    php7-pdo_pgsql \
    php7-pdo_sqlite \
    php7-phar \
    php7-posix \
    php7-session \
    php7-soap \
    php7-sockets \
    php7-tokenizer \
    php7-xml \
    php7-xmlreader \
    php7-xmlwriter \
    php7-zip \
    php7-zlib \
    php7-simplexml \
    tini
WORKDIR /project
ENTRYPOINT ["/sbin/tini", "--", "/project/vendor/bin/phpunit"]