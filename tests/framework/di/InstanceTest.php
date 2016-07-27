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

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @group di
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

    public function testExceptionRefersTo()
    {
        $container = new Container;
        $container->set('db', [
            'class' => 'yii\db\Connection',
            'dsn' => 'test',
        ]);

        $this->setExpectedException('yii\base\InvalidConfigException', '"db" refers to a yii\db\Connection component. yii\base\Widget is expected.');

        Instance::ensure('db', 'yii\base\Widget', $container);
        Instance::ensure(['class' => 'yii\db\Connection', 'dsn' => 'test'], 'yii\base\Widget', $container);
    }

    public function testExceptionInvalidDataType()
    {
        $this->setExpectedException('yii\base\InvalidConfigException', 'Invalid data type: yii\db\Connection. yii\base\Widget is expected.');
        Instance::ensure(new Connection, 'yii\base\Widget');
    }

    public function testExceptionComponentIsNotSpecified()
    {
        $this->setExpectedException('yii\base\InvalidConfigException', 'The required component is not specified.');
        Instance::ensure('');
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

    /**
     * This tests the usage example given in yii\di\Instance class PHPdoc
     */
    public function testLazyInitializationExample()
    {
        Yii::$container = new Container;
        Yii::$container->set('cache', [
            'class' => 'yii\caching\DbCache',
            'db' => Instance::of('db')
        ]);
        Yii::$container->set('db', [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:path/to/file.db',
        ]);

        $this->assertInstanceOf('yii\caching\DbCache', $cache = Yii::$container->get('cache'));
        $this->assertInstanceOf('yii\db\Connection', $db = $cache->db);
        $this->assertEquals('sqlite:path/to/file.db', $db->dsn);
    }
}
