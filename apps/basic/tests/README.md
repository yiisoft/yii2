This folder contains various tests for the basic application.
These tests are developed with [Codeception PHP Testing Framework](http://codeception.com/).

After creating the basic application, follow these steps to prepare for the tests:

1. To install `Codeception` and its dependencies through composer, run the following commands:

   ```
   php composer.phar require --dev "codeception/codeception *" "codeception/specify *"
   ```

2. In the file `_bootstrap.php`, modify the definition of the constant `TEST_ENTRY_URL` so
   that it points to the correct entry script URL.
3. Go to the application base directory and build the test suites:

   ```
   vendor/bin/codecept build
   ```

Now you can run the tests with the following commands:

```
# run all available tests
vendor/bin/codecept run
# run acceptance tests
vendor/bin/codecept run acceptance
# run functional tests
vendor/bin/codecept run functional
# run unit tests
vendor/bin/codecept run unit
```

Please refer to [Codeception tutorial](http://codeception.com/docs/01-Introduction) for
more details about writing and running acceptance, functional and unit tests.
