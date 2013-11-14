This folder contains various tests for the basic application.
These tests are developed with [Codeception PHP Testing Framework](http://codeception.com/).

To run the tests, follow these steps:

1. [Install Codeception](http://codeception.com/quickstart) if you do not have it yet.
2. Create test configuration files based on your environment:
   - Copy `acceptance.suite.dist.yml` to `acceptance.suite.yml` and customize it;
   - Copy `functional.suite.dist.yml` to `functional.suite.yml` and customize it;
   - Copy `unit.suite.dist.yml` to `unit.suite.yml` and customize it.
3. Switch to the parent folder and run tests:

```
php codecept.phar run
```
