<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use Yii;
use yii\di\Container;
use yii\di\Instance;
use yiiunit\framework\di\stubs\Bar;
use yiiunit\framework\di\stubs\Foo;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\di\stubs\MyModel;
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

    public function testInvoke()
    {
        $this->mockApplication([
            'components'=>[
                'qux' => [
                    'class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongApp',
                ]
            ]
        ]);

        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => 'yiiunit\framework\di\stubs\Qux',
            'a' => 'independent',
        ]);
        $callback = function($param, stubs\QuxInterface $qux, Bar $bar){
            return [$param, $qux instanceof Qux, $qux->a, $bar->qux->a];
        };
        $result = Yii::$container->invoke($callback, ['D426']);
        $this->assertEquals(['D426', true, 'belongApp', 'independent'], $result);


        //*****
        $myObj = new MyModel([
            'name' => 'dee',
            'email' => 'dee@mdm.com'
        ]);
        
        $useAtEvent = [];
        $myObj->on('afterValidate', function ($event, stubs\QuxInterface $qux)use(&$useAtEvent){
            $useAtEvent[0] = $event->name;
            $useAtEvent[1] = $qux->a;
        });

        $this->assertEquals([], $useAtEvent);
        $myObj->validate();
        $this->assertEquals([], $myObj->errors);
        $this->assertEquals(['afterValidate', 'belongApp'], $useAtEvent);
        $this->assertEquals($myObj->qux->a, 'independent');

        //*****
        $myObj = new MyModel([
            'name' => 3426,
            'email' => 'dee@mdm.com',
            'qux' => new Qux('not_injected')
        ]);

        $myObj->on('afterValidate', function (stubs\QuxInterface $qux){
            $this->assertTrue($qux instanceof stubs\QuxInterface);
        });
        $this->assertFalse($myObj->validate());
        $this->assertEquals('Is not string', $myObj->getFirstError('name'));
        $this->assertEquals($myObj->qux->a, 'not_injected');


        //*****
        list($param, $qux, $validator) = Yii::$container->invoke([$myObj, 'test'], ['D426']);
        $this->assertEquals('D426', $param);
        $this->assertEquals(Yii::$app->qux, $qux);
        $this->assertFalse($validator->validate('not_valid_email'));
    }
}
