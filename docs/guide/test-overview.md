Testing
=======

> Note: This section is under development.

TODO:

- https://github.com/yiisoft/yii2/blob/master/extensions/codeception/README.md

Testing is an important part of software development. Whether we are aware of it or not, we conduct testing continuously.
For example, when we write a class in PHP, we may debug it step by step or simply use echo or die statements to verify
that implementation is correct. In case of web application we're entering some test data in forms to ensure the page
interacts with us as expected. The testing process could be automated so that each time when we need to test something,
we just need to call up the code that perform testing for us. This is known as automated testing, which is the main topic
of testing chapters.

The testing support provided by Yii includes:

- [Unit testing](test-unit.md) - verifies that a single unit of code is working as expected.
- [Functional testing](test-functional.md) - verifies scenarios from a user's perspective via browser emulation.
- [Acceptance testing](test-acceptance.md) - verifies scenarios from a user's perspective in a browser.

Yii provides ready to use test sets for all three testing types in both basic and advanced application templates.  

Test environment setup
----------------------

In order to run tests with Yii you need to install [Codeception](http://codeception.com/). A good way to install it is
the following:

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
