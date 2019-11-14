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
 * FixtureTrait 提供一系列用于加载，卸载和访问测试用例中的夹具的函数能力。
 *
 * 通过使用 FixtureTrait ，一个测试类可以通过改写  [[fixtures()]] 方法来指定哪些夹具被加载。然后，可以通过使用 [[loadFixtures()]] 和
 *  [[unloadFixtures()]] 方法来加载卸载夹具。当一个夹具被加载后，因为 PHP `__get()` 魔术方法的缘故，它可以以一个对象属性的方式被访问。
 * 同样，如果夹具是 [[ActiveFixture]] 的实例，你可以通过 `$this->fixtureName('model name')` 类似的语法访问一个 AR 模型。
 *
 * 关于 FixtureTrait 更多的使用详情，参考 [guide article on fixtures](guide:test-fixtures)
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait FixtureTrait
{
    /**
     * @var array 当前测试可用的夹具对象列表。
     * 这个数组的键是对应的夹具类名。
     * 这个夹具以它们的依赖顺序排列。即，如果夹具B依赖A，那么A排列在B的前面。
     */
    private $_fixtures;


    /**
     * 声明当前测试用例需要的夹具。
     *
     * 这个方法的返回值必须是夹具配置数组，例如：
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
     * 注意：一个测试用例实际使用的夹具包括 [[globalFixtures()]] 和 [[fixtures()]] 中声明的夹具。
     *
     * @return array 当前测试用例需要的夹具。
     */
    public function fixtures()
    {
        return [];
    }

    /**
     * 声明被所有测试用例都需要的共享夹具。
     * 它的返回值类似 [[fixtures()]]。
     * 你应该在基类中重写这个方法。
     *
     * @return array 不同的测试用例所共享的夹具。
     * @see fixtures()
     */
    public function globalFixtures()
    {
        return [];
    }

    /**
     * 加载特定的夹具。
     *
     * 这个方法会调用每个夹具对象的 [[Fixture::load()]] 方法。
     *
     * @param Fixture[] $fixtures 被加载的夹具。如果这个参数未指定，将会默认使用 [[getFixtures()]] 的返回值。
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
     * 卸载指定的夹具。
     * 这个方法将会调用每个夹具对象的 [[Fixture::unload()]] 方法。
     * @param Fixture[] $fixtures 将被卸载的夹具。如果这个参数未指定，将会默认使用 [[getFixtures()]] 的返回值。
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
     * 初始化夹具
     * @since 2.0.12
     */
    public function initFixtures()
    {
        $this->unloadFixtures();
        $this->loadFixtures();
    }

    /**
     * 返回 [[globalFixtures()]] 和 [[fixtures()]] 指定的夹具对象。
     * @return Fixture[] 当前测试用例加载的夹具对象。
     */
    public function getFixtures()
    {
        if ($this->_fixtures === null) {
            $this->_fixtures = $this->createFixtures(array_merge($this->globalFixtures(), $this->fixtures()));
        }

        return $this->_fixtures;
    }

    /**
     * 方法指定的夹具。
     * @param string $name 夹具名称。可以是夹具别名，如果没有使用的话，也可以是类名。
     * @return Fixture 夹具对象，如果指定的对象不存在，返回null。
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
     * 创建指定的夹具实例。
     * 所有的依赖夹具也会被创建。
     * @param array $fixtures 将要被创建的夹具。你可以提供夹具名称或者夹具配置数组。
     * 如果未提供这个参数，那么 [[globalFixtures()]] 和 [[fixtures()]] 中指定的夹具将会被创建。
     * @return Fixture[] 创建的夹具实例。
     * @throws InvalidConfigException 如果夹具没有正确的配置，或者检测到循环依赖。
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
                } elseif ($instances[$name] === false) {
                    throw new InvalidConfigException("A circular dependency is detected for fixture '$class'.");
                }
            }
        }

        return $instances;
    }
}
