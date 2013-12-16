This folder contains various tests for the basic application.
These tests are developed with [Codeception PHP Testing Framework](http://codeception.com/).

To run the tests, follow these steps:

1. Download Codeception([Quickstart step 1](http://codeception.com/quickstart)) and put the codeception.phar in the
   application base directory (not in this `tests` directory!).
2. Adjust the test configuration files based on your environment:
   - Configure the URL for [acceptance tests](http://codeception.com/docs/04-AcceptanceTests) in `acceptance.suite.yml`.
     The URL should point to the `index-test-acceptance.php` file that is located under the `web` directory of the application.
   - `functional.suite.yml` for [functional testing](http://codeception.com/docs/05-FunctionalTests) and
     `unit.suite.yml` for [unit testing](http://codeception.com/docs/06-UnitTests) should already work out of the box
     and should not need to be adjusted.
3. Go to the application base directory and build the test suites:
   ```
   php codecept.phar build    // rebuild test scripts, only need to be run once
   ```
4. Run the tests:
   ```
   php codecept.phar run      // run all available tests
   // you can also run a test suite alone:
   php codecept.phar run acceptance
   php codecept.phar run functional
   php codecept.phar run unit
   ```

Please refer to [Codeception tutorial](http://codeception.com/docs/01-Introduction) for
more details about writing acceptance, functional and unit tests.
