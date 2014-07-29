#!/bin/sh
#
# install CUBRID DBMS

if (php --version | grep -i HHVM > /dev/null); then
    echo "Skipping CUBRID on HHVM"
    exit 0
fi

# cubrid dbms
echo 'yes' | sudo add-apt-repository ppa:cubrid/cubrid
sudo apt-get update
sudo apt-get install cubrid
/etc/profile.d/cubrid.sh
sudo apt-get install cubrid-demodb

# cubrid pdo
install_pdo_cubrid() {
    wget "http://pecl.php.net/get/PDO_CUBRID-9.2.0.0001.tgz" &&
    tar -zxf "PDO_CUBRID-9.2.0.0001.tgz" &&
    sh -c "cd PDO_CUBRID-9.2.0.0001 && phpize && ./configure && make && sudo make install"

    echo "extension=pdo_cubrid.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

    return $?
}

install_pdo_cubrid > ~/pdo_cubrid.log || ( echo "=== PDO CUBRID BUILD FAILED ==="; cat ~/pdo_cubrid.log )

echo "Installed CUBRID `dpkg -s cubrid |grep Version`"
