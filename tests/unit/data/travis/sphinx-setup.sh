#!/bin/sh

SCRIPT=$(readlink -f "$0")
CWD=$(dirname "$SCRIPT")

# install sphinxsearch:
echo 'yes' | sudo add-apt-repository ppa:builds/sphinxsearch-daily
sudo apt-get update
sudo apt-get install sphinxsearch

# setup test Sphinx indexes:
indexer --config $CWD/../sphinx/sphinx.conf --all

# run searchd:
searchd --config $CWD/../sphinx/sphinx.conf
