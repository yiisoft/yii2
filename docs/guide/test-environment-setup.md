Testing environment setup
======================

> Note: This section is under development.

Yii2 has officially maintained integration with [`Codeception`](https://github.com/Codeception/Codeception) testing
framework that allows you to create the following test types:

- [Unit testing](test-unit.md) - verifies that a single unit of code is working as expected;
- [Functional testing](test-functional.md) - verifies scenarios from a user's perspective via browser emulation;
- [Acceptance testing](test-acceptance.md) - verifies scenarios from a user's perspective in a browser.

Yii provides ready to use test sets for all three test types in both
[`yii2-basic`](https://github.com/yiisoft/yii2/tree/master/apps/basic) and
[`yii2-advanced`](https://github.com/yiisoft/yii2/tree/master/apps/advanced) project templates.

In order to run tests you need to install [Codeception](https://github.com/Codeception/Codeception).
You can install it either locally - for particular project only, or globally - for your development machine.

For the local installation use following commands:

```
composer require "codeception/codeception=2.0.*"
composer require "codeception/specify=*"
composer require "codeception/verify=*"
```

For the global installation you will need to use `global` directive:

```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

If you've never used Composer for global packages before, run `composer global status`. It should output:

```
Changed current directory to <directory>
```

Then add `<directory>/vendor/bin` to you `PATH` environment variable. Now we're able to use `codecept` from command
line globally.

> Note: global installation allows you use Codeception for all projects you are working on your development machine and
  allows running `codecept` shell command globally without specifying path. However, such approach may be inappropriate,
  for example, if 2 different projects require different versions of Codeception installed.
  For the simplicity all shell commands related to the tests running around this guide are written assuming Codeception
  has been installed globally.
