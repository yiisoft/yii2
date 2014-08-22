Testing environments setup
==========================

> Note: This section is under development.

By default Yii2 is bundled with [`Codeception`](https://github.com/Codeception/Codeception) testing framework 
that allows you to write unit tests as functional and acceptance tests for UI too. You can also use other testing frameworks
and third party libs like: [Phpunit](https://github.com/sebastianbergmann/phpunit/), [Behat](https://github.com/Behat/Behat), [AspectMock](https://github.com/Codeception/AspectMock), [Mockery](https://github.com/padraic/mockery) and so on.


## Codeception

Codeception testing support provided by Yii includes:

- [Unit testing](test-unit.md) - verifies that a single unit of code is working as expected;
- [Functional testing](test-functional.md) - verifies scenarios from a user's perspective via browser emulation;
- [Acceptance testing](test-acceptance.md) - verifies scenarios from a user's perspective in a browser.

Yii provides ready to use test sets for all three testing types in both [`yii2-basic`](https://github.com/yiisoft/yii2/tree/master/apps/basic) and [`yii2-advanced`](https://github.com/yiisoft/yii2/tree/master/apps/advanced) application templates.

In order to run tests with Yii you need to install [Codeception](https://github.com/Codeception/Codeception). A good way to install it is
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


## Phpunit

	TBD


## Behat

	TBD
