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
php composer.phar require --prefer-dist yiisoft/yii2-codeception "*"
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

Getting Codeception modules
---------------------------

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
Codeception events
------------------

To fire event do this:

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


Special test method chaining
----------------------------

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

Customizing application config
------------------------------

```php
<?php

SomeConsoleTest extends \yii\codeception\TestCase
{
	// this is the config file to load as application config
	public $appConfig = '@app/path/to/my/custom/config/for/test.php';
}
```

The `$appConfig` property could be an array or a valid alias, pointing to the file that returns a config array. You can specify
application class in the config, for example for testing console commands or features you can create `_console.php` config under
`tests/unit` directory like this:

```php
return yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../config/console.php'),
	require(__DIR__ . '/../_config.php'),
	[
		'class' => 'yii\console\Application',
		'components' => [
			//override console components if needed
		],
	]
);
```

and then just use your `ConsoleTestCase` like the following:

```php

use \yii\codeception\TestCase;

class ConsoleTestCase extends TestCase
{
	public $appConfig = '@tests/unit/_console.php';
}
```

You can extend other console test cases from this basic `ConsoleTestCase`.

Reconfiguring components for testing
------------------------------------

You also can reconfigure some components for tests, for this purpose in your `setUp` method of your test case 
you can do this for example:

```php
<?php

use \yii\codeception\TestCase;
use Yii;

class MailTest extends TestCase
{

	protected function setUp()
	{
		//dont forget to call parent method that will setup Yii application
		parent::setUp();

		Yii::$app->mail->fileTransportCallback = function ($mailer, $message) {
			return 'testing_message.eml';
		};
	}

}
```

You dont need to worry about application instances and isolation because application will be created [each time](https://github.com/yiisoft/yii2/blob/master/extensions/codeception/TestCase.php#L31) before test.
You also can mock application in some other custom way, for this purposes you have method [`mockApplication`](https://github.com/yiisoft/yii2/blob/master/extensions/codeception/TestCase.php#L55) available in your test case,
this method will create new application instance and replace old one. Use this method when you need to create application with config that is not suitable for all other test methods in current tests case, for example:

```php

use \yii\codeception\TestCase;

class SomeMyTest extends TestCase
{

	public function testOne()
	{
		...
	}

	public function testTwo()
	{
		$this->mockApplication([
			'language' => 'ru-RU',
			'components' => [
				'db' => [
					//your custom configuration here
				],
			],
		]);

		//your expectations and assertions goes here
	}

	public function testThree()
	{
		...
	}

}
```

Additional debug output
-----------------------

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
