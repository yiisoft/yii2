<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\base\UnknownPropertyException;

/**
 * FixtureTrait provides functionalities for loading, unloading and accessing fixtures for a test case.
 *
 * By using FixtureTrait, a test class will be able to specify which fixtures to load by overriding
 * the [[fixtures()]] method. It can then load and unload the fixtures using [[loadFixtures()]] and [[unloadFixtures()]].
 * Once a fixture is loaded, it can be accessed like an object property, thanks to the PHP `__get()` magic method.
 * Also, if the fixture is an instance of [[ActiveFixture]], you will be able to access AR models
 * through the syntax `$this->fixtureName('model name')`.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait FixtureTrait
{
	/**
	 * @var array the list of fixture objects available for the current test.
	 * The array keys are the corresponding fixture class names.
	 * The fixtures are listed in their dependency order. That is, fixture A is listed before B
	 * if B depends on A.
	 */
	private $_fixtures;
	/**
	 * @var array the fixture class names indexed by the corresponding fixture names (aliases).
	 */
	private $_fixtureAliases;


	/**
	 * Returns the value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $object->property;`.
	 * @param string $name the property name
	 * @return mixed the property value
	 * @throws UnknownPropertyException if the property is not defined
	 */
	public function __get($name)
	{
		$fixture = $this->getFixture($name);
		if ($fixture !== null) {
			return $fixture;
		} else {
			throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
		}
	}

	/**
	 * Calls the named method which is not a class method.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when an unknown method is being invoked.
	 * @param string $name the method name
	 * @param array $params method parameters
	 * @throws UnknownMethodException when calling unknown method
	 * @return mixed the method return value
	 */
	public function __call($name, $params)
	{
		$fixture = $this->getFixture($name);
		if ($fixture instanceof ActiveFixture) {
			return $fixture->getModel(reset($params));
		} else {
			throw new UnknownMethodException('Unknown method: ' . get_class($this) . "::$name()");
		}
	}

	/**
	 * Declares the fixtures that are needed by the current test case.
	 * The return value of this method must be an array of fixture configurations. For example,
	 *
	 * ```php
	 * [
	 *     // anonymous fixture
	 *     PostFixture::className(),
	 *     // "users" fixture
	 *     'users' => UserFixture::className(),
	 *     // "cache" fixture with configuration
	 *     'cache' => [
	 *          'class' => CacheFixture::className(),
	 *          'host' => 'xxx',
	 *     ],
	 * ]
	 * ```
	 *
	 * Note that the actual fixtures used for a test case will include both [[globalFixtures()]]
	 * and [[fixtures()]].
	 *
	 * @return array the fixtures needed by the current test case
	 */
	protected function fixtures()
	{
		return [];
	}

	/**
	 * Declares the fixtures shared required by different test cases.
	 * The return value should be similar to that of [[fixtures()]].
	 * You should usually override this method in a base class.
	 * @return array the fixtures shared and required by different test cases.
	 * @see fixtures()
	 */
	protected function globalFixtures()
	{
		return [];
	}

	/**
	 * Loads the fixtures.
	 * This method will load the fixtures specified by `$fixtures` or [[globalFixtures()]] and [[fixtures()]].
	 * @param array $fixtures the fixtures to loaded. If not set, [[fixtures()]] will be loaded instead.
	 * @throws InvalidConfigException if fixtures are not properly configured or if a circular dependency among
	 * the fixtures is detected.
	 */
	protected function loadFixtures($fixtures = null)
	{
		if ($fixtures === null) {
			$fixtures = array_merge($this->globalFixtures(), $this->fixtures());
		}

		// normalize fixture configurations
		$config = [];  // configuration provided in test case
		$this->_fixtureAliases = [];
		foreach ($fixtures as $name => $fixture) {
			if (!is_array($fixture)) {
				$fixtures[$name] = $fixture = ['class' => $fixture];
			} elseif (!isset($fixture['class'])) {
				throw new InvalidConfigException("You must specify 'class' for the fixture '$name'.");
			}
			$config[$fixture['class']] = $fixture;
			$this->_fixtureAliases[$name] = $fixture['class'];
		}

		// create fixture instances
		$this->_fixtures = [];
		$stack = array_reverse($fixtures);
		while (($fixture = array_pop($stack)) !== null) {
			if ($fixture instanceof Fixture) {
				$class = get_class($fixture);
				unset($this->_fixtures[$class]);  // unset so that the fixture is added to the last in the next line
				$this->_fixtures[$class] = $fixture;
			} else {
				$class = $fixture['class'];
				if (!isset($this->_fixtures[$class])) {
					$this->_fixtures[$class] = false;
					$stack[] = $fixture = Yii::createObject($fixture);
					foreach ($fixture->depends as $dep) {
						// need to use the configuration provided in test case
						$stack[] = isset($config[$dep]) ? $config[$dep] : ['class' => $dep];
	 				}
				} elseif ($this->_fixtures[$class] === false) {
					throw new InvalidConfigException("A circular dependency is detected for fixture '$class'.");
				}
			}
		}

		// load fixtures
		/** @var Fixture $fixture */
		foreach ($this->_fixtures as $fixture) {
			$fixture->beforeLoad();
		}
		foreach ($this->_fixtures as $fixture) {
			$fixture->load();
		}
		foreach ($this->_fixtures as $fixture) {
			$fixture->afterLoad();
		}
	}

	/**
	 * Unloads all existing fixtures.
	 */
	protected function unloadFixtures()
	{
		/** @var Fixture $fixture */
		foreach (array_reverse($this->_fixtures) as $fixture) {
			$fixture->unload();
		}
	}

	/**
	 * @return array the loaded fixtures for the current test case
	 */
	protected function getFixtures()
	{
		return $this->_fixtures;
	}

	/**
	 * Returns the named fixture.
	 * @param string $name the fixture alias or class name
	 * @return Fixture the fixture object, or null if the named fixture does not exist.
	 */
	protected function getFixture($name)
	{
		$class = isset($this->_fixtureAliases[$name]) ? $this->_fixtureAliases[$name] : $name;
		return isset($this->_fixtures[$class]) ? $this->_fixtures[$class] : null;
	}
}
