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
        $foo2 = $container->get($Foo);
        $this->assertFalse($foo === $foo2);

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

        // wiring by closure
        $container = new Container;
        $container->set('qux', new Qux);
        $qux1 = $container->get('qux');
        $qux2 = $container->get('qux');
        $this->assertTrue($qux1 === $qux2);

        // config
        $container = new Container;
        $container->set('qux', $Qux);
        $qux = $container->get('qux', [], ['a' => 2]);
        $this->assertEquals(2, $qux->a);
        $qux = $container->get('qux', [3]);
        $this->assertEquals(3, $qux->a);
        $qux = $container->get('qux', [3, ['a' => 4]]);
        $this->assertEquals(4, $qux->a);
    }
}
