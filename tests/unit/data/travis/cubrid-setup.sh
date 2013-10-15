#!/bin/sh
#
# install CUBRID DBMS

# cubrid dbms
echo 'yes' | sudo add-apt-repository ppa:cubrid/cubrid
sudo apt-get update
sudo apt-get install cubrid
/etc/profile.d/cubrid.sh
sudo apt-get install cubrid-demodb

# cubrid pdo
sudo apt-get install php5-cubrid
echo '/opt/cubrid/' | pecl install pdo_cubrid
echo "extension=pdo_cubrid.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
/etc/profile.d/cubrid.sh
