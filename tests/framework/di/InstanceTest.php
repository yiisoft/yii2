<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\di\Container;
use yii\di\Instance;
use yiiunit\TestCase;
use yii\base\InvalidConfigException;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InstanceTest extends TestCase
{
    public function testOf()
    {
        $container = new Container;
        $className = Component::className();
        $instance = Instance::of($className);

        $this->assertTrue($instance instanceof Instance);
        $this->assertTrue($instance->get($container) instanceof Component);
        $this->assertTrue(Instance::ensure($instance, $className, $container) instanceof Component);
        $this->assertTrue($instance->get($container) !== Instance::ensure($instance, $className, $container));
    }

    public function testEnsure()
    {
        $container = new Container;
        $container->set('db', [
            'class' => 'yii\db\Connection',
            'dsn' => 'test',
        ]);

        $this->assertTrue(Instance::ensure('db', 'yii\db\Connection', $container) instanceof Connection);
        $this->assertTrue(Instance::ensure(new Connection, 'yii\db\Connection', $container) instanceof Connection);
        $this->assertTrue(Instance::ensure(['class' => 'yii\db\Connection', 'dsn' => 'test'], 'yii\db\Connection', $container) instanceof Connection);
    }

    public function testEnsureWithoutType()
    {
        $container = new Container;
        $container->set('db', [
            'class' => 'yii\db\Connection',
            'dsn' => 'test',
        ]);

        $this->assertTrue(Instance::ensure('db', null, $container) instanceof Connection);
        $this->assertTrue(Instance::ensure(new Connection, null, $container) instanceof Connection);
        $this->assertTrue(Instance::ensure(['class' => 'yii\db\Connection', 'dsn' => 'test'], null, $container) instanceof Connection);
    }

    public function testEnsureMinimalSettings()
    {
        Yii::$container->set('db', [
            'class' => 'yii\db\Connection',
            'dsn' => 'test',
        ]);

        $this->assertTrue(Instance::ensure('db') instanceof Connection);
        $this->assertTrue(Instance::ensure(new Connection) instanceof Connection);
        $this->assertTrue(Instance::ensure(['class' => 'yii\db\Connection', 'dsn' => 'test']) instanceof Connection);

        Yii::$container = new Container;
    }

    public function testCheckException()
    {
        $container = new Container;
        $container->set('db', [
            'class' => 'yii\db\Connection',
            'dsn' => 'test',
        ]);

        try {
            Instance::ensure('db', 'yii\base\Widget', $container);
        } catch (InvalidConfigException $e) {
            $this->assertEquals('"db" refers to a yii\db\Connection component. yii\base\Widget is expected.', $e->getMessage());
        }
        try {
            Instance::ensure(new Connection, 'yii\base\Widget', $container);
        } catch (InvalidConfigException $e) {
            $this->assertEquals('Invalid data type: yii\db\Connection. yii\base\Widget is expected.', $e->getMessage());
        }
        try {
            Instance::ensure(['class' => 'yii\db\Connection', 'dsn' => 'test'], 'yii\base\Widget', $container);
        } catch (InvalidConfigException $e) {
            $this->assertEquals('"db" refers to a yii\db\Connection component. yii\base\Widget is expected.', $e->getMessage());
        }
        try {
            Instance::ensure(null, null, $container);
        } catch (InvalidConfigException $e) {
            $this->assertEquals('The required component is not specified.', $e->getMessage());
        }
    }

    public function testGet()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'test',
                ]
            ]
        ]);

        $container = Instance::of('db');

        $this->assertTrue($container->get() instanceof Connection);

        $this->destroyApplication();
    }
}
