Codeception Extension for Yii 2
===============================

This extension provides [Codeception](http://codeception.com/) integration for the Yii Framework 2.0.

It provides classes that help with testing with codeception:

- a base class for unit-tests: `yii\codeception\TestCase`;
- a base class for codeception page-objects: `yii\codeception\BasePage`.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-codeception "*"
```

or add

```json
"yiisoft/yii2-codeception": "*"
```

to the require section of your composer.json.


Usage
-----

When using codeception page-objects they have some similar code, this code was extracted and put into the `BasePage`
class to reduce code duplication. Simply extend your page object from this class, like it is done in `yii2-app-basic` and
`yii2-app-advanced` boilerplates.

For unit testing there is a `TestCase` class which holds some common features like application creation before each test
and application destroy after each test. You can configure a mock application using this class.
`TestCase` is extended from `Codeception\TestCase\Case` so all methods and assertions are available.
You may use codeception modules and fire events in your test, just use methods:

```php
<?php
#in your unit-test
$this->getModule('CodeHelper'); #or some other module
```

You also can use all guy methods by accessing guy instance like:

```php
<?php
$this->codeGuy->someMethodFromModule();
```

to fire event do this:

```php
<?php
use Codeception\Event\TestEvent;

public function testSomething()
{
	$this->fire('myevent', new TestEvent($this));
}
```
this event can be catched in modules and helpers. If your test is in the group, then event name will be followed by the groupname, 
for example ```myevent.somegroup```.

Execution of special tests methods is (for example on ```UserTest``` class):

```
tests\unit\models\UserTest::setUpBeforeClass();

	tests\unit\models\UserTest::_before();

		tests\unit\models\UserTest::setUp();

			tests\unit\models\UserTest::testSomething();

		tests\unit\models\UserTest::tearDown();

	tests\unit\models\UserTest::_after();

tests\unit\models\UserTest::tearDownAfterClass();
```

If you use special methods dont forget to call its parent.

```php
<?php

SomeConsoleTest extends \yii\codeception\TestCase
{
	// this is the config file to load as application config
	public static $applicationConfig = '@app/config/web.php';

	// this defines the application class to use for mock applications
	protected $applicationClass = 'yii\web\Application';
}
```

The `$applicationConfig` property may be set for all tests in a `_bootstrap.php` file like this:

```php
<?php

yii\codeception\TestCase::$applicationConfig = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../config/web.php'),
	require(__DIR__ . '/../../config/codeception/unit.php')
);
```

Don't forget that you have to include autoload and Yii class in the `_bootstrap.php` file.

You also can reconfigure some components for tests, for this purpose there is a `$config` property in the `TestCase` class.

```php
<?php

SomeOtherTest extends \yii\codeception\TestCase
{
	public $config = [
		'components' => [
			'mail' => [
				'useFileTransport' => true,
			],
		]
	];
}
```

Because of Codeception buffers all output you can't make simple `var_dump()` in the TestCase, instead you need to use
`Codeception\Util\Debug::debug()` function and then run test with `--debug` key, for example:

```php
<?php

use Codeception\Util\Debug;

SomeDebugTest extends \yii\codeception\TestCase
{
	public function testSmth()
	{
		Debug::debug('some string');
		Debug::debug($someArray);
		Debug::debug($someObject);
	}

}
```

Then run command `php codecept.phar run --debug unit/SomeDebugTest` and you will see in output:

```html
  some string

  Array
  (
      [0] => 1
      [1] => 2
      [2] => 3
      [3] => 4
      [4] => 5
  )
  
  yii\web\User Object
  (
      [identityClass] => app\models\User
      [enableAutoLogin] => 
      [loginUrl] => Array
          (
              [0] => site/login
          )
  
      [identityCookie] => Array
          (
              [name] => _identity
              [httpOnly] => 1
          )
  
      [authTimeout] => 
      [autoRenewCookie] => 1
      [idVar] => __id
      [authTimeoutVar] => __expire
      [returnUrlVar] => __returnUrl
      [_access:yii\web\User:private] => Array
          (
          )
  
      [_identity:yii\web\User:private] => 
      [_events:yii\base\Component:private] => 
      [_behaviors:yii\base\Component:private] => 
  )

```

For further instructions refer to the testing section in the [Yii Definitive Guide](https://github.com/yiisoft/yii2/blob/master/docs/guide/testing.md).
