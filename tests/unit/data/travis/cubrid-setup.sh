#!/bin/sh -e
#
# install CUBRID DBMS

if (php --version | grep -i HipHop > /dev/null); then
    echo "Skipping CUBRID on HHVM"
    exit 0
fi

CWD=$(pwd)

# cubrid dbms
mkdir -p cubrid/$CUBRID_VERSION
cd cubrid
if (test -f $CUBRID_VERSION-linux.x86_64.tar.gz); then
    echo "CUBRID is already installed"
else
    wget http://ftp.cubrid.org/CUBRID_Engine/$CUBRID_VERSION-linux.x86_64.tar.gz -O $CUBRID_VERSION-linux.x86_64.tar.gz
fi

    cd $CUBRID_VERSION
    tar xzf ../../$CUBRID_VERSION-linux.x86_64.tar.gz
    cd ../..


# setting cubrid env
CUBRID=$CWD/cubrid/$CUBRID_VERSION/CUBRID
CUBRID_DATABASES=$CUBRID/databases
CUBRID_LANG=en_US

ld_lib_path=`printenv LD_LIBRARY_PATH`
if [ "$ld_lib_path" = "" ]
then
    LD_LIBRARY_PATH=$CUBRID/lib
else
    LD_LIBRARY_PATH=$CUBRID/lib:$LD_LIBRARY_PATH
fi

SHLIB_PATH=$LD_LIBRARY_PATH
LIBPATH=$LD_LIBRARY_PATH
PATH=$CUBRID/bin:$CUBRID/cubridmanager:$PATH

export CUBRID
export CUBRID_DATABASES
export CUBRID_LANG
export LD_LIBRARY_PATH
export SHLIB_PATH
export LIBPATH
export PATH

# start cubrid
cubrid service start
# create and start the demo db
$CUBRID/demo/make_cubrid_demo.sh
cubrid server start demodb

echo ""
echo "Installed CUBRID $CUBRID_VERSION"
echo ""

# cubrid pdo
install_pdo_cubrid() {
    if (test "! (-f PDO_CUBRID-$CUBRID_PDO_VERSION.tgz)"); then
        wget "http://pecl.php.net/get/PDO_CUBRID-$CUBRID_PDO_VERSION.tgz" -O PDO_CUBRID-$CUBRID_PDO_VERSION.tgz
    fi
    tar -zxf "PDO_CUBRID-$CUBRID_PDO_VERSION.tgz"
    sh -c "cd PDO_CUBRID-$CUBRID_PDO_VERSION && phpize && ./configure --prefix=$CWD/cubrid/PDO_CUBRID-$CUBRID_PDO_VERSION && make"

    echo "extension=$CWD/cubrid/PDO_CUBRID-$CUBRID_PDO_VERSION/modules/pdo_cubrid.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

    return $?
}

install_pdo_cubrid > ~/pdo_cubrid.log || ( echo "=== PDO CUBRID BUILD FAILED ==="; cat ~/pdo_cubrid.log; exit 1 )

echo ""
echo "Installed CUBRID PDO $CUBRID_PDO_VERSION"
echo ""

cd ..
