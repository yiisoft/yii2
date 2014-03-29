<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use yii\di\Container;
use yii\di\Instance;
use yiiunit\framework\di\stubs\Bar;
use yiiunit\framework\di\stubs\Foo;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\TestCase;


/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ContainerTest extends TestCase
{
    public function testDefault()
    {
        $namespace = __NAMESPACE__ . '\stubs';
        $QuxInterface = "$namespace\\QuxInterface";
        $Foo = Foo::className();
        $Bar = Bar::className();
        $Qux = Qux::className();

        // automatic wiring
        $container = new Container;
        $container->set($QuxInterface, $Qux);
        $foo = $container->get($Foo);
        $this->assertTrue($foo instanceof $Foo);
        $this->assertTrue($foo->bar instanceof $Bar);
        $this->assertTrue($foo->bar->qux instanceof $Qux);

        // full wiring
        $container = new Container;
        $container->set($QuxInterface, $Qux);
        $container->set($Bar);
        $container->set($Qux);
        $container->set($Foo);
        $foo = $container->get($Foo);
        $this->assertTrue($foo instanceof $Foo);
        $this->assertTrue($foo->bar instanceof $Bar);
        $this->assertTrue($foo->bar->qux instanceof $Qux);

        // wiring by closure
        $container = new Container;
        $container->set('foo', function () {
            $qux = new Qux;
            $bar = new Bar($qux);
            return new Foo($bar);
        });
        $foo = $container->get('foo');
        $this->assertTrue($foo instanceof $Foo);
        $this->assertTrue($foo->bar instanceof $Bar);
        $this->assertTrue($foo->bar->qux instanceof $Qux);

        // wiring by closure which uses container
        $container = new Container;
        $container->set($QuxInterface, $Qux);
        $container->set('foo', function (Container $c, $params, $config) {
            return $c->get(Foo::className());
        });
        $foo = $container->get('foo');
        $this->assertTrue($foo instanceof $Foo);
        $this->assertTrue($foo->bar instanceof $Bar);
        $this->assertTrue($foo->bar->qux instanceof $Qux);

        // predefined constructor parameters
        $container = new Container;
        $container->set('foo', $Foo, [Instance::of('bar')]);
        $container->set('bar', $Bar, [Instance::of('qux')]);
        $container->set('qux', $Qux);
        $foo = $container->get('foo');
        $this->assertTrue($foo instanceof $Foo);
        $this->assertTrue($foo->bar instanceof $Bar);
        $this->assertTrue($foo->bar->qux instanceof $Qux);
    }
}
