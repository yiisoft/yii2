This folder contains various tests for the basic application.
These tests are developed with [Codeception PHP Testing Framework](http://codeception.com/).

To run the tests, follow these steps:

1. [Install Codeception](http://codeception.com/quickstart) if you do not have it yet.
2. Update tests
2. Create test configuration files based on your environment:
   - Copy `acceptance.suite.dist.yml` to `acceptance.suite.yml` and customize it;
   - Copy `functional.suite.dist.yml` to `functional.suite.yml` and customize it;
   - Copy `unit.suite.dist.yml` to `unit.suite.yml` and customize it.
3. Switch to the parent folder and run tests:

```
cd ..
php codecept.phar build    // rebuild test scripts, only need to be run once
php codecept.phar run      // run all available tests
```

Please refer to [Codeception tutorial](http://codeception.com/docs/01-Introduction) for
more details about writing acceptance, functional and unit tests.
