Testing environment setup
======================

Yii 2 has officially maintained integration with [`Codeception`](https://github.com/Codeception/Codeception) testing
framework that allows you to create the following test types:

- [Unit](test-unit.md) - verifies that a single unit of code is working as expected;
- [Functional](test-functional.md) - verifies scenarios from a user's perspective via browser emulation;
- [Acceptance](test-acceptance.md) - verifies scenarios from a user's perspective in a browser.

Yii provides ready to use test sets for all three test types in both
[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) and
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced) project templates.

Codeception comes preinstalled with both basic and advanced project templates.
In case you are not using one of these templates, you can follow the steps in the next section to install it.

## Setting up Codeception in a Yii application

### Installing Codeception

Codeception can be installed using Composer by issuing the following console command:

```
composer require --dev 'codeception/codeception:^5.0' codeception/module-yii2 codeception/module-asserts codeception/module-filesystem codeception/module-phpbrowser codeception/verify
```

This command will install Codeception as well as some useful Codeception modules as development dependencies.
Here is a short description of the packages installed:

- `codeception/codeception:^5.0`: This is the package for the main Codeception framework. The `^5.0` in the version constraint
  means that the package can be any version within the `5.x` series, but not version 6 or higher.

  > Note: Codeception 5 requires PHP 8.0 or higher. Newer releases of Codeception and its modules require newer PHP versions
  > (for example, Codeception 5.3 requires PHP 8.2 and `codeception/module-yii2` 2.0 requires PHP 8.3), but Composer
  > automatically selects the newest releases that are compatible with your PHP version. If your application is still running
  > on PHP 7.4, change the version constraint of Codeception to `^4.0`. This guide assumes Codeception 5 is installed; the
  > directory structure of Codeception 4 differs significantly, check the
  > [Codeception 5 release notes](https://codeception.com/07-28-2022/codeception-5.html) for details.

- `codeception/module-yii2`: This is the Codeception module that provides the integration with the Yii framework.
  It boots your Yii application in the test environment, lets functional tests send requests to it without a web server,
  and provides Yii-specific actions for working with Active Record, fixtures, emails and routes.
- `codeception/module-asserts`: This is a Codeception module that provides the common PHPUnit assertion methods,
  such as `assertEquals`, `assertContains` or `assertGreaterThan`, as actions, so that they can be used as `$I->assertEquals()`
  in Cest tests and as `$this->tester->assertEquals()` in unit tests.
- `codeception/module-filesystem`: This is a Codeception module that provides methods for interacting with the filesystem
  during tests, such as creating, deleting, and checking files and directories. This can be useful for testing code that
  involves reading from or writing to the filesystem.
- `codeception/module-phpbrowser`: This is a Codeception module that provides a browser emulator for acceptance tests.
  It accesses your application over HTTP like a real browser would, allowing you to click on links, fill out forms and
  submit data, but it does not execute JavaScript.
- `codeception/verify`: This is a standalone assertion library that wraps the PHPUnit assertions into a more readable,
  BDD style syntax such as `verify($value)->equals($expected)`. It is optional, but used by the tests shipped with the project templates.

> Info: The `--dev` flag indicates that the packages being installed are development dependencies, which means they are not
> needed for running the project in production. When installing the project in production you should always use
> `composer install` with the `--no-dev` flag.

### Creating test config and directory structure

After installing Codeception you need to create the directory structure and configuration files.
Codeception comes with a `bootstrap` command that does most of this work:

```
vendor/bin/codecept bootstrap
```

This will create a new Codeception configuration file named `codeception.yml` in your application's root directory.

It will set up three different test suites by default: `Unit`, `Functional`, and `Acceptance`.
You can learn more about the differences between these types of tests in the [Introduction section of the Codeception documentation](https://codeception.com/docs/Introduction).

It will also generate the `tests/` directory with the following structure:

- `tests/Support` this directory is used for storing helper classes and traits that can be used in your acceptance,
  functional, and unit tests. You can use these to write custom actions or verification methods you can re-use in your tests.

  There is an actor class for every test suite:

  - `UnitTester.php`
  - `FunctionalTester.php`
  - `AcceptanceTester.php`

  These classes are the `$I` object used in your tests and they are the place to put custom actions. The actions provided
  by the modules enabled in a suite are generated by Codeception into the `_generated` directory, which is included by the
  actor classes. Files in this directory should not be included in version control, so the bootstrap command placed a
  `.gitignore` file inside. The actor classes are regenerated automatically every time you run the tests, so there is
  no need to run `vendor/bin/codecept build` manually after changing the modules of a suite.

  The `Helper` directory is meant for your own helper modules and the `Data` directory for data files used by your tests.

- For every test suite there is a `*.suite.yml` which is the configuration file for the specific test suite:
  `Unit.suite.yml`, `Functional.suite.yml`, and `Acceptance.suite.yml`.
- The actual tests for each test suite are placed in the directories named `Unit`, `Functional`, and `Acceptance`.
- The `_output` directory contains test output such as HTML and screenshots of failing tests and other test report data.
  Files in this directory should not be included in version control, so the bootstrap command placed a `.gitignore` file inside.

> Tip: Codeception places the actor classes and your tests in the `Tests` namespace by default. If you prefer another one,
> for example the `app\tests` namespace used by the basic project template, pass it to the bootstrap command:
> `vendor/bin/codecept bootstrap --namespace 'app\tests'`.

### Setting up test suites for Yii

For testing you need a separate [application configuration](concept-configurations.md#application-configurations) for the test environment.
This can be based on the application configuration used in your development and production environment but should contain the following changes:

- Use a separate database.
  Tests will make a lot of changes to the database and should always start in a clean environment to make sure test
  results are always reproducible. So you should configure the `db` application component to use a separate database.
  If you have other database connections you need to adjust these as well.
- If you have a mailer component, configure it to not send any emails:

  ```php
  // ...
  'components' => [
      // ...
      'mailer' => [
          'class' => \yii\symfonymailer\Mailer::class,
          // ... keep other settings here

          // send all mails to a file by default.
          'useFileTransport' => true,
      ],
  ],
  ```

- In general, make sure your test environment does not contain any configuration that allows to connect to production
  databases or services. This is to avoid data loss or unexpected behavior in production systems.

In the following we assume that your application's test configuration is located in `config/test.php`.
Adjust the paths as necessary to match your configuration file location.

The Yii2 module needs the global `Yii` class, which is defined in `vendor/yiisoft/yii2/Yii.php`. This file is not
autoloaded by Composer (the `yiisoft/yii2` package only registers the `yii\` namespace for autoloading) and the module
does not include it itself. Create a bootstrap file at `tests/_bootstrap.php` that includes it, so it is loaded once
before any test suite is run:

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```

Then modify the `codeception.yml` file to enable the bootstrap file and to tell the Yii2 module where to find your test
configuration. The module settings placed here apply to all suites that enable the Yii2 module:

```yaml
bootstrap: _bootstrap.php
modules:
    config:
        Yii2:
            configFile: config/test.php
```

> Note: The value of `bootstrap` is relative to the `tests` directory. If you need suite-specific bootstrap code, you can
> add a `bootstrap` setting to the `*.suite.yml` file of a suite as well; in that case the path is relative to the suite's
> directory, for example `tests/Unit/_bootstrap.php`.

#### Unit tests

The unit test suite only has the `Asserts` module enabled by default. To enable the Yii2 module, add it to the
`tests/Unit.suite.yml` configuration file:

```yaml
actor: UnitTester
modules:
    enabled:
        - Asserts
        - Yii2:
            part: [orm, email, fixtures]
step_decorators: ~
```

The `part` setting limits the module to the Yii-specific actions for Active Record, emails and fixtures, since unit tests
do not send requests to the application. The Yii2 module creates a fresh application instance in `Yii::$app` for every
test and, by default, wraps every test in a database transaction that is rolled back afterwards. The actions of the
enabled modules are available in unit tests via the `$this->tester` property.

For a quick start you may generate a unit test and run it with the following commands:

```
vendor/bin/codecept g:test Unit First
vendor/bin/codecept run Unit
```

The output should be similar to the following:

```
Codeception PHP Testing Framework v5.3.6

Tests.Unit Tests (1) -------------------------------------------------------------------------------------
✔ FirstTest: Some feature (0.01s)
----------------------------------------------------------------------------------------------------------
Time: 00:00.058, Memory: 8.00 MB

OK (1 test, 0 assertions)
```

For more information on unit tests continue in the [Unit Tests subsection](test-unit.md).

#### Functional tests

The functional test suite has no modules enabled by default. To enable the Yii2 module, add it to the
`tests/Functional.suite.yml` configuration file:

```yaml
actor: FunctionalTester
modules:
    enabled:
        - Yii2
        - Asserts
step_decorators: ~
```

In functional tests the Yii2 module is used without the `part` setting, so that all its actions are available.
Requests such as `$I->amOnRoute('site/index')` or `$I->amOnPage('/')` are not sent over HTTP; the module fills the request
parameters and runs the application right from the test process, so no web server is needed.

For a quick start you may generate a functional test and run it with the following commands:

```
vendor/bin/codecept g:cest Functional First
vendor/bin/codecept run Functional
```

The output should be similar to the following:

```
Codeception PHP Testing Framework v5.3.6

Tests.Functional Tests (1) -------------------------------------------------------------------------------
✔ FirstCest: Try to test (0.01s)
----------------------------------------------------------------------------------------------------------
Time: 00:00.058, Memory: 8.00 MB

OK (1 test, 0 assertions)
```

For more information on functional tests continue in the [Functional Tests subsection](test-functional.md).

#### Acceptance tests

Unlike functional tests, acceptance tests access the application over HTTP, so the application has to be served by a
web server and runs in a separate process. This means that the application has to be configured for testing in its
entry script, not by the Yii2 module.

Create a separate entry script `web/index-test.php` next to your regular `web/index.php` that loads the test configuration:

```php
<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/test.php';

(new yii\web\Application($config))->run();
```

The acceptance test suite has the `PhpBrowser` module enabled by default. Set its `url` to the address your test web
server will be reachable at, including the test entry script, and add the Yii2 module so that you can prepare and verify
the data in the database:

```yaml
actor: AcceptanceTester
modules:
    enabled:
        - PhpBrowser:
            url: http://localhost:8080/index-test.php
        - Yii2:
            part: orm
            entryScript: index-test.php
            transaction: false
step_decorators:
    - Codeception\Step\ConditionalAssertion
    - Codeception\Step\TryTo
    - Codeception\Step\Retry
```

Note the differences to the other suites:

- The test entry script is part of the `url`, so that every request made by the tests, such as `$I->amOnPage('/')`, is
  handled by `index-test.php` and thus uses the test configuration. A request to `http://localhost:8080/` would be
  answered by your regular `web/index.php` instead. The `entryScript` setting makes URLs created with [[yii\helpers\Url]]
  inside the tests point to the test entry script as well.
- The database transaction the module wraps each test in has to be disabled, because the application being tested runs
  in the web server process and would not see data created inside a transaction of the test process. As a result,
  changes made during a test are not rolled back automatically, so the test database has to be reset by other means,
  for example by loading [fixtures](test-fixtures.md) before the tests.

The web server can be started manually before running the tests using PHP's built-in web server:

```
php -S localhost:8080 -t web
```

Alternatively you can let Codeception start and stop it for you by enabling the `RunProcess` extension in the
`tests/Acceptance.suite.yml` configuration file. The extension requires the `symfony/process` package, which can be
installed with `composer require --dev symfony/process`:

```yaml
extensions:
    enabled:
        - Codeception\Extension\RunProcess:
            0: php -S localhost:8080 -t web
            sleep: 1
```

For a quick start you may generate an acceptance test and run it with the following commands:

```
vendor/bin/codecept g:cest Acceptance First
vendor/bin/codecept run Acceptance
```

If you need to test JavaScript-powered pages, replace the `PhpBrowser` module with the `WebDriver` module, which drives
a real browser via Selenium Server or a browser driver such as ChromeDriver or GeckoDriver. Install it with
`composer require --dev codeception/module-webdriver` and configure it in place of `PhpBrowser`:

```yaml
modules:
    enabled:
        - WebDriver:
            url: http://localhost:8080/index-test.php
            browser: chrome
        - Yii2:
            # same settings as above
```

Check the [WebDriver module documentation](https://codeception.com/docs/modules/WebDriver) for details on how to set up
the browser driver. For more information on acceptance tests continue in the [Acceptance Tests subsection](test-acceptance.md).

### Running the tests

To run all test suites at once, run the `run` command without arguments:

```
vendor/bin/codecept run
```

You can also run a single suite, a single test file or a single test:

```
vendor/bin/codecept run Unit
vendor/bin/codecept run Functional FirstCest
vendor/bin/codecept run Functional FirstCest:tryToTest
```

Add the `--steps` option to see the actions performed by each test, or the `--debug` option to get detailed output
including the requests that were sent. See the [Codeception documentation](https://codeception.com/docs/GettingStarted#Running-Tests)
for more options.
