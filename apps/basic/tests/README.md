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

Please refer to [Codeception tutorial](http://codeception.com/docs/01-Introduction) for
more details about writing and running acceptance, functional and unit tests.
