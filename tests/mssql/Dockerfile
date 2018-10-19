FROM bylexus/apache-php7


# https://www.microsoft.com/en-us/sql-server/developer-get-started/php-ubuntu
RUN apt-get update
RUN apt-get install -y curl  apt-transport-https

RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
RUN curl https://packages.microsoft.com/config/ubuntu/16.04/prod.list > /etc/apt/sources.list.d/mssql-release.list

RUN apt-get update \
 && apt-get install -y unixodbc-dev-utf16 php-dev \
 && pecl install sqlsrv pdo_sqlsrv

RUN echo ";priority=20\nextension=sqlsrv.so" > /etc/php/7.0/cli/conf.d/20-sqlsrv.ini
RUN echo ";priority=20\nextension=sqlsrv.so" > /etc/php/7.0/apache2/conf.d/20-sqlsrv.ini
RUN echo ";priority=30\nextension=pdo_sqlsrv.so" > /etc/php/7.0/cli/conf.d/30-pdo_sqlsrv.ini
RUN echo ";priority=30\nextension=pdo_sqlsrv.so" > /etc/php/7.0/apache2/conf.d/30-pdo_sqlsrv.ini

# IMPORTANT NOTICE! Install `msodbcsql` after `unixodbc-dev-utf16` and `pdo_sqlsrv`, due to dependency & build issues
RUN ACCEPT_EULA=Y apt-get install -y msodbcsql

# Install system packages for composer (git)
RUN apt-get update && \
    apt-get -y install \
            git \
            php-curl \
        --no-install-recommends && \
        rm -rf /tmp/* /var/tmp/*
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
RUN composer.phar global require --optimize-autoloader \
        "hirak/prestissimo"


# Project source-code
WORKDIR /project
ADD composer.* /project/
RUN /usr/local/bin/composer.phar install --prefer-dist
ADD ./ /project

# https://github.com/Microsoft/msphpsql/issues/161
RUN apt-get install -y locales \
 && echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
 && locale-gen

# Debug installation
RUN dpkg -L msodbcsql
