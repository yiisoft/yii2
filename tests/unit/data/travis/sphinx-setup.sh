#!/bin/sh

SCRIPT=$(readlink -f "$0")
CWD=$(dirname "$SCRIPT")

# install sphinxsearch:
echo 'yes' | sudo add-apt-repository ppa:builds/sphinxsearch-daily
sudo apt-get update
sudo apt-get install sphinxsearch

# log files
sudo mkdir /var/log/sphinx
sudo touch /var/log/sphinx/searchd.log
sudo touch /var/log/sphinx/query.log
chmod -R 777 /var/log/sphinx # ugly

# setup test Sphinx indexes:
indexer --config $CWD/../sphinx/sphinx.conf --all

# run searchd:
searchd --config $CWD/../sphinx/sphinx.conf
