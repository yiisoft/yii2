Functional Tests
================

Functional test verifies scenarios from a user's perspective. It is similar to [acceptance test](test-acceptance.md)
but instead of communicating via HTTP it is filling up environment such as POST and GET parameters and then executes
application instance right from the code.

Functional tests are generally faster than acceptance tests and are providing detailed stack traces on failures.
As a rule of thumb, they should be preferred unless you have a special web server setup or complex UI powered by
JavaScript.

Functional testing is implemented with the help of Codeception framework which has a nice documentation about it:

- [Codeception for Yii framework](http://codeception.com/for/yii)
- [Codeception Functional Tests](http://codeception.com/docs/04-FunctionalTests)

## Running basic and advanced template tests

If you've started with advanced template, please refer to ["testing" guide](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md)
for more details about running tests.  

If you've started with basic template, check its [README "testing" section](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing).
