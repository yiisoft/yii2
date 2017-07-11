Testing environment setup
======================

Yii 2 has officially maintained integration with [`Codeception`](https://github.com/Codeception/Codeception) testing
framework that allows you to create the following test types:

- [Unit testing](test-unit.md) - verifies that a single unit of code is working as expected;
- [Functional testing](test-functional.md) - verifies scenarios from a user's perspective via browser emulation;
- [Acceptance testing](test-acceptance.md) - verifies scenarios from a user's perspective in a browser.

Yii provides ready to use test sets for all three test types in both
[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) and
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced) project templates.

Codeception comes preinstalled with both basic and advanced project templates.
In case you are not using one of these templates, Codeception could be installed
by issuing the following console commands:

```
composer require codeception/codeception
composer require codeception/specify
composer require codeception/verify
```
