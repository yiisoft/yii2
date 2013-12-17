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
   - If you want to run acceptance tests, you need to download [selenium standalone](http://www.seleniumhq.org/download/)
     and start it with command `java -jar {selenium-standalone-name}.jar`.
     After that you can use `WebDriver` codeception module that will connect to selenium and launch browser.
     This also allows you to use [Xvfb](https://en.wikipedia.org/wiki/Xvfb) in your tests which allows you to run tests
     without showing the running browser on the screen. There is codeception [blog post](http://codeception.com/05-24-2013/jenkins-ci-practice.html)
     that explains how it works.

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
