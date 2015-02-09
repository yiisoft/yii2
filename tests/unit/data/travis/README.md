This directory contains scripts for automated test runs via the [Travis CI](http://travis-ci.org) build service. They are used for the preparation of worker instances by setting up needed extensions and configuring database access.

These scripts might be used to configure your own system for test runs. But since their primary purpose remains to support Travis in running the test cases, you would be best advised to stick to the setup notes in the tests themselves.

The scripts are:

 - [`apc-setup.sh`](apc-setup.sh)
   Installs and configures the [apc pecl extension](http://pecl.php.net/package/apc)
 - [`cubrid-setup.sh`](cubrid-setup.sh)
   Prepares the [CUBRID](http://www.cubrid.org/) server instance by installing the server and PHP PDO driver
 - [`init-apps.sh`](init-apps.sh)
   Prepare test environment for basic and advanced application
 - [`memcache-setup.sh`](memcache-setup.sh)
   Compiles and installs the [memcache pecl extension](http://pecl.php.net/package/memcache)
 - [`mongodb-setup.sh`](mongodb-setup.sh)
   Enables Mongo DB PHP extension
 - [`setup-apps.sh`](setup-apps.sh)
   Prepare test environment for basic and advanced application
 - [`sphinx-setup.sh`](sphinx-setup.sh)
   Prepares the [Sphinx](http://sphinxsearch.com/) server instances by installing the server and attaching it to MySQL