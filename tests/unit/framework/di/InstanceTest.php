<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use yii\base\Component;
use yii\db\Connection;
use yii\di\Container;
use yii\di\Instance;
use yiiunit\TestCase;

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
        $this->assertTrue(Instance::ensure([
            'class' => 'yii\db\Connection',
            'dsn' => 'test',
        ], 'yii\db\Connection', $container) instanceof Connection);
    }
}
