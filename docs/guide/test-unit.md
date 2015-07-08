Unit Tests
==========

> Note: This section is under development.

A unit test verifies that a single unit of code is working as expected. In object-oriented programming, the most basic
code unit is a class. A unit test thus mainly needs to verify that each of the class interface methods works properly.
That is, given different input parameters, the test verifies the method returns expected results.
Unit tests are usually developed by people who write the classes being tested.

Unit testing in Yii is built on top of PHPUnit and, optionally, Codeception so it's recommended to go through their docs:

- [PHPUnit docs starting from chapter 2](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html).
- [Codeception Unit Tests](http://codeception.com/docs/05-UnitTests).

Running basic and advanced template unit tests
----------------------------------------------

Please refer to instructions provided in `apps/advanced/tests/README.md` and `apps/basic/tests/README.md`.

Framework unit tests
--------------------

If you want to run unit tests for Yii framework itself follow
"[Getting started with Yii2 development](https://github.com/yiisoft/yii2/blob/master/docs/internals/getting-started.md)".
