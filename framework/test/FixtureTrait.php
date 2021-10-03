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
     *
     * The return value of this method must be an array of fixture configurations. For example,
     *
     * ```php
     * [
     *     // anonymous fixture
     *     PostFixture::class,
     *     // "users" fixture
     *     'users' => UserFixture::class,
     *     // "cache" fixture with configuration
     *     'cache' => [
     *          'class' => CacheFixture::class,
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
     * Initialize the fixtures.
     * @since 2.0.12
     */
    public function initFixtures()
    {
        $this->unloadFixtures();
        $this->loadFixtures();
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
     * All dependent fixtures will also be created. Duplicate fixtures and circular dependencies will only be created once.
     * @param array $fixtures the fixtures to be created. You may provide fixture names or fixture configurations.
     * If this parameter is not provided, the fixtures specified in [[globalFixtures()]] and [[fixtures()]] will be created.
     * @return Fixture[] the created fixture instances
     * @throws InvalidConfigException if fixtures are not properly configured
     */
    protected function createFixtures(array $fixtures)
    {
        // normalize fixture configurations
        $config = [];  // configuration provided in test case
        $aliases = [];  // class name => alias or class name
        foreach ($fixtures as $name => $fixture) {
            if (!is_array($fixture)) {
                $class = ltrim($fixture, '\\');
                $fixtures[$name] = ['class' => $class];
                $aliases[$class] = is_int($name) ? $class : $name;
            } elseif (isset($fixture['class'])) {
                $class = ltrim($fixture['class'], '\\');
                $config[$class] = $fixture;
                $aliases[$class] = $name;
            } else {
                throw new InvalidConfigException("You must specify 'class' for the fixture '$name'.");
            }
        }

        // create fixture instances
        $instances = [];
        $stack = array_reverse($fixtures);
        while (($fixture = array_pop($stack)) !== null) {
            if ($fixture instanceof Fixture) {
                $class = get_class($fixture);
                $name = isset($aliases[$class]) ? $aliases[$class] : $class;
                unset($instances[$name]);  // unset so that the fixture is added to the last in the next line
                $instances[$name] = $fixture;
            } else {
                $class = ltrim($fixture['class'], '\\');
                $name = isset($aliases[$class]) ? $aliases[$class] : $class;
                if (!isset($instances[$name])) {
                    $instances[$name] = false;
                    $stack[] = $fixture = Yii::createObject($fixture);
                    foreach ($fixture->depends as $dep) {
                        // need to use the configuration provided in test case
                        $stack[] = isset($config[$dep]) ? $config[$dep] : ['class' => $dep];
                    }
                }
                // if the fixture is already loaded (ie. a circular dependency or if two fixtures depend on the same fixture) just skip it.
            }
        }

        return $instances;
    }
}
