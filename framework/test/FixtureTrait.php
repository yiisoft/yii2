<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\InvalidConfigException;

/**
 * FixtureTrait provides functionalities for loading, unloading and accessing fixtures for a test case.
 *
 * By using FixtureTrait, a test class will be able to specify which fixtures to load by overriding
 * the [[fixtures()]] method. It can then load and unload the fixtures using [[loadFixtures()]] and [[unloadFixtures()]].
 * Once a fixture is loaded, it can be accessed like an object property, thanks to the PHP `__get()` magic method.
 * Also, if the fixture is an instance of [[ActiveFixture]], you will be able to access AR models
 * through the syntax `$this->fixtureName('model name')`.
 *
 * For more details and usage information on FixtureTrait, see the [guide article on fixtures](guide:test-fixtures).
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
    public function fixtures()
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
    public function globalFixtures()
    {
        return [];
    }

    /**
     * Loads the specified fixtures.
     * This method will call [[Fixture::load()]] for every fixture object.
     * @param Fixture[] $fixtures the fixtures to be loaded. If this parameter is not specified,
     * the return value of [[getFixtures()]] will be used.
     */
    public function loadFixtures($fixtures = null)
    {
        if ($fixtures === null) {
            $fixtures = $this->getFixtures();
        }

        /* @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            $fixture->beforeLoad();
        }
        foreach ($fixtures as $fixture) {
            $fixture->load();
        }
        foreach (array_reverse($fixtures) as $fixture) {
            $fixture->afterLoad();
        }
    }

    /**
     * Unloads the specified fixtures.
     * This method will call [[Fixture::unload()]] for every fixture object.
     * @param Fixture[] $fixtures the fixtures to be loaded. If this parameter is not specified,
     * the return value of [[getFixtures()]] will be used.
     */
    public function unloadFixtures($fixtures = null)
    {
        if ($fixtures === null) {
            $fixtures = $this->getFixtures();
        }

        /* @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            $fixture->beforeUnload();
        }
        $fixtures = array_reverse($fixtures);
        foreach ($fixtures as $fixture) {
            $fixture->unload();
        }
        foreach ($fixtures as $fixture) {
            $fixture->afterUnload();
        }
    }

    /**
     * Returns the fixture objects as specified in [[globalFixtures()]] and [[fixtures()]].
     * @return Fixture[] the loaded fixtures for the current test case
     */
    public function getFixtures()
    {
        if ($this->_fixtures === null) {
            $this->_fixtures = $this->createFixtures(array_merge($this->globalFixtures(), $this->fixtures()));
        }

        return $this->_fixtures;
    }

    /**
     * Returns the named fixture.
     * @param string $name the fixture name. This can be either the fixture alias name, or the class name if the alias is not used.
     * @return Fixture the fixture object, or null if the named fixture does not exist.
     */
    public function getFixture($name)
    {
        if ($this->_fixtures === null) {
            $this->_fixtures = $this->createFixtures(array_merge($this->globalFixtures(), $this->fixtures()));
        }
        $name = ltrim($name, '\\');

        return isset($this->_fixtures[$name]) ? $this->_fixtures[$name] : null;
    }

    /**
     * Creates the specified fixture instances.
     * All dependent fixtures will also be created.
     * @param array $fixtures the fixtures to be created. You may provide fixture names, closure, Fixture or fixture configurations.
     * If this parameter is not provided, the fixtures specified in [[globalFixtures()]] and [[fixtures()]] will be created.
     * @return Fixture[] the created fixture instances
     * @throws InvalidConfigException if fixtures are not properly configured or if a circular dependency among
     * the fixtures is detected.
     */
    protected function createFixtures(array $fixtures)
    {
        // normalize fixture configurations
        $aliases = [];  // index or class name => index
        $stack = [];
        foreach ($fixtures as $name => $fixture) {
            if (is_string($fixture)) {
                $class = ltrim($fixture, '\\');
                $fixtures[$name] = ['class' => $class];
                array_unshift($stack, [$name, $class, $fixtures[$name], false]);
            } elseif (is_array($fixture) && isset($fixture['class'])) {
                $class = ltrim($fixture['class'], '\\');
                array_unshift($stack, [$name, $class, $fixture, false]);
            } elseif (is_callable($fixture, true)) {
                array_unshift($stack, [$name, null, $fixture, false]);
            } elseif ($fixture instanceof Fixture) {
                $class = get_class($fixture);
                array_unshift($stack, [$name, $class, $fixture, true]);
            } elseif (is_array($fixture)) {
                throw new InvalidConfigException('Fixture configuration must be an array containing a "class" element.');
            } else {
                throw new InvalidConfigException('Unsupported configuration type: ' . gettype($fixture));
            }
            $aliases[$name] = $name;
            if (isset($class) && !array_key_exists($class, $aliases)) {
                $aliases[$class] = $name;
            }
        }

        // create fixture instances
        $instances = [];
        while ((list($name, $class, $fixture, $preload) = array_pop($stack)) !== null) {
            $index = $name;
            if ($fixture instanceof Fixture && !$preload) {
                isset($class) || $class = get_class($fixture);
                isset($index) || $index = $class;
                unset($instances[$index]);  // unset so that the fixture is added to the last in the next line
                $instances[$index] = $fixture;
                continue;
            }
            if ($fixture instanceof Fixture) { //Preload
                isset($index) || $index = $class;
                if (!isset($instances[$index])) {
                    $instances[$index] = false;
                    $stack[] = [$name, $class, $fixture, false];
                } elseif ($instances[$index] === false) {
                    throw new InvalidConfigException("A circular dependency is detected for fixture '$class'.");
                }
            } elseif (is_callable($fixture, true)) {
                $fixture = Yii::createObject($fixture);
                $class = get_class($fixture);
                isset($index) || $index = $class;
                if (!isset($instances[$index])) {
                    $instances[$index] = false;
                    $stack[] = [$index, $class, $fixture, false];
                } elseif ($instances[$index] === false) {
                    throw new InvalidConfigException("A circular dependency is detected for fixture '$class'.");
                }
            } elseif (is_array($fixture) && isset($fixture['class'])) {
                isset($index) || $index = $class;
                if (!isset($instances[$index])) {
                    $instances[$index] = false;
                    $fixture = Yii::createObject($fixture);
                    $stack[] = [$index, get_class($fixture), $fixture, false];
                } elseif ($instances[$index] === false) {
                    throw new InvalidConfigException("A circular dependency is detected for fixture '$class'.");
                }
            } else {
                break;
            }
            foreach ($fixture->depends as $dep) {
                // need to use the configuration provided in test case
                if ($aliases[$dep]) {
                    $stack[] = [$aliases[$dep], null, $fixtures[$aliases[$dep]], false];
                } else {
                    $stack[] = [null, $dep, ['class' => $dep], false];
                }
            }

        }

        return $instances;
    }

}
