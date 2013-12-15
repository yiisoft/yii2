Codeception Extension for Yii 2
===============================

This extension provides a `Codeception` mail solution for Yii 2. It includes some classes that are useful
for unit-testing (```TestCase```) or for codeception page-objects (```BasePage```).

When using codeception page-objects they have some similar code, this code was extracted and put into the ```BasePage```
class to reduce code duplication. Simply extend your page object from this class, like it is done in ```yii2-basic``` and 
```yii2-advanced``` boilerplates.

For unit testing there is a ```TestCase``` class which holds some common features like application creation before each test
and application destroy after each test. You can configure your application by this class. ```TestCase``` is extended from ```PHPUnit_Framework_TestCase``` so all
methods and assertions are available.

```php
SomeConsoleTest extends yii\codeception\TestCase
{
	# by default it is @tests/unit/_bootstrap.php which holds some basic things like: 
	# including composer autoload, include BaseYii class.
	public $baseConfig = '@app/config/console.php';

	public $applicationClass = 'yii\console\Application';
}
```
Dont forget that you still need to include autoload and BaseYii class, like in the _bootstrap.php file (comments above).

You also can reconfigure some components for tests, for this purpose there is a ```$config``` property in the testcase.

```php
SomeOtherTest extends yii\codeception\TestCase
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

Because of Codeception buffers all output you cant make simple ```var_dump()``` in the TestCase, instead you need to use
```Codeception\Util\Debug::debug()``` function and then run test with ```--debug``` key, for example:

```php

use \Codeception\Util\Debug;

SomeDebugTest extends yii\codeception\TestCase
{

	public function testSmth()
	{
		Debug::debug('some my string');
		Debug::debug($someArray);
		Debug::debug($someObject);
	}

}
```

Then run command ```php codecept.phar run --debug unit/SomeDebugTest``` (Codeception also available through composer) and you will see in output:

```html
  some my string

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


For further instructions refer to the related section in the Yii Definitive Guide (https://github.com/yiisoft/yii2/blob/master/docs/guide/testing.md).


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
