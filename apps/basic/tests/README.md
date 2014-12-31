This directory contains various tests for the basic application.

Tests in `codeception` directory are developed with [Codeception PHP Testing Framework](http://codeception.com/).

After creating the basic application, follow these steps to prepare for the tests:

1. Install Codeception if it's not yet installed:

```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

If you've never used Composer for global packages run `composer global status`. It should output:

```
Changed current directory to <directory>
```

Then add `<directory>/vendor/bin` to you `PATH` environment variable. Now we're able to use `codecept` from command
line globally.

2. Install faker extension by running the following from template root directory where `composer.json` is:

```
composer require --dev yiisoft/yii2-faker:*
```

3. Create `yii2_basic_tests` database and update it by applying migrations:

```
codeception/bin/yii migrate
```

4. Build the test suites:

```
codecept build
```

5. In order to be able to run acceptance tests you need to start a webserver. The simplest way is to use PHP built in
webserver. In the `web` directory execute the following:

```
php -S localhost:8080
```

6. Now you can run the tests with the following commands:

```
# run all available tests
codecept run
# run acceptance tests
codecept run acceptance
# run functional tests
codecept run functional
# run unit tests
codecept run unit
```

Code coverage support
---------------------

By default, code coverage is disabled in `codeception.yml` configuration file, you should uncomment needed rows to be able
to collect code coverage. You can run your tests and collect coverage with the following command:

```
#collect coverage for all tests
codecept run --coverage-html --coverage-xml

#collect coverage only for unit tests
codecept run unit --coverage-html --coverage-xml

#collect coverage for unit and functional tests
codecept run functional,unit --coverage-html --coverage-xml
```

You can see code coverage output under the `tests/_output` directory.

###Remote code coverage

When you run your tests not in the same process where code coverage is collected, then you should uncomment `remote` option and its
related options, to be able to collect code coverage correctly. To setup remote code coverage you should follow [instructions](http://codeception.com/docs/11-Codecoverage)
from codeception site.

1. install `Codeception c3` remote support `composer require "codeception/c3:*"`;

2. copy `c3.php` file under your `web` directory;

3. include `c3.php` file in your `index-test.php` file before application run, so it can catch needed requests.

Configuration options that are used by remote code coverage:

- c3_url: url pointing to entry script that includes `c3.php` file, so `Codeception` will be able to produce code coverage;
- remote: whether to enable remote code coverage or not;
- remote_config: path to the `codeception.yml` configuration file, from the directory where `c3.php` file is located. This is needed
  so that `Codeception` can create itself instance and collect code coverage correctly.

By default `c3_url` and `remote_config` setup correctly, you only need to copy and include `c3.php` file in your `index-test.php`

After that you should be able to collect code coverage from tests that run through `PhpBrowser` or `WebDriver` with same command
as for other tests:

```
#collect coverage from remote
codecept run acceptance --coverage-html --coverage-xml
```

Please refer to [Codeception tutorial](http://codeception.com/docs/01-Introduction) for
more details about writing and running acceptance, functional and unit tests.
