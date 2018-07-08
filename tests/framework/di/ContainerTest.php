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
use yii\di\CircularReferenceException;
use yii\validators\NumberValidator;
use yiiunit\data\ar\Cat;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Type;
use yiiunit\framework\di\stubs\Bar;
use yiiunit\framework\di\stubs\BarSetter;
use yiiunit\framework\di\stubs\Foo;
use yiiunit\framework\di\stubs\FooProperty;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\di\stubs\QuxHolder;
use yiiunit\framework\di\stubs\QuxInterface;
use yiiunit\TestCase;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @group di
 */
class ContainerTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        Yii::$container = new Container();
    }

    public function testDefault()
    {
        $namespace = __NAMESPACE__ . '\stubs';
        $QuxInterface = "$namespace\\QuxInterface";
        $Foo = Foo::class;
        $Bar = Bar::class;
        $Qux = Qux::class;

        // automatic wiring
        $container = new Container();
        $container->set($QuxInterface, $Qux);
        $foo = $container->get($Foo);
        $this->assertInstanceOf($Foo, $foo);
        $this->assertInstanceOf($Bar, $foo->bar);
        $this->assertInstanceOf($Qux, $foo->bar->qux);
        $foo2 = $container->get($Foo);
        $this->assertNotSame($foo, $foo2);

        // full wiring
        $container = new Container();
        $container->set($QuxInterface, $Qux);
        $container->set($Bar);
        $container->set($Qux);
        $container->set($Foo);
        $foo = $container->get($Foo);
        $this->assertInstanceOf($Foo, $foo);
        $this->assertInstanceOf($Bar, $foo->bar);
        $this->assertInstanceOf($Qux, $foo->bar->qux);

        // wiring by closure
        $container = new Container();
        $container->set('foo', function () {
            $qux = new Qux();
            $bar = new Bar($qux);
            return new Foo($bar);
        });
        $foo = $container->get('foo');
        $this->assertInstanceOf($Foo, $foo);
        $this->assertInstanceOf($Bar, $foo->bar);
        $this->assertInstanceOf($Qux, $foo->bar->qux);

        // wiring by closure which uses container
        $container = new Container();
        $container->set($QuxInterface, $Qux);
        $container->set('foo', function (Container $c, $params, $config) {
            return $c->get(Foo::class);
        });
        $foo = $container->get('foo');
        $this->assertInstanceOf($Foo, $foo);
        $this->assertInstanceOf($Bar, $foo->bar);
        $this->assertInstanceOf($Qux, $foo->bar->qux);

        // predefined constructor parameters
        $container = new Container();
        $container->set('foo', $Foo, [Instance::of('bar')]);
        $container->set('bar', $Bar, [Instance::of('qux')]);
        $container->set('qux', $Qux);
        $foo = $container->get('foo');
        $this->assertInstanceOf($Foo, $foo);
        $this->assertInstanceOf($Bar, $foo->bar);
        $this->assertInstanceOf($Qux, $foo->bar->qux);

        // predefined property parameters
        $fooSetter = FooProperty::class;
        $barSetter = BarSetter::class;

        $container = new Container();
        $container->set('foo', ['__class' => $fooSetter, 'bar' => Instance::of('bar')]);
        $container->set('bar', ['__class' => $barSetter, 'qux' => Instance::of('qux')]);
        $container->set('qux', $Qux);
        $foo = $container->get('foo');
        $this->assertInstanceOf($fooSetter, $foo);
        $this->assertInstanceOf($barSetter, $foo->bar);
        $this->assertInstanceOf($Qux, $foo->bar->qux);

        // wiring by closure
        $container = new Container();
        $container->set('qux', new Qux());
        $qux1 = $container->get('qux');
        $qux2 = $container->get('qux');
        $this->assertSame($qux1, $qux2);

        // config
        $container = new Container();
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
            'components' => [
                'qux' => [
                    '__class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    '__class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            '__class' => 'yiiunit\framework\di\stubs\Qux',
            'a' => 'independent',
        ]);

        // use component of application
        $callback = function ($param, stubs\QuxInterface $qux, Bar $bar) {
            return [$param, $qux instanceof Qux, $qux->a, $bar->qux->a];
        };
        $result = Yii::$container->invoke($callback, ['D426']);
        $this->assertEquals(['D426', true, 'belongApp', 'independent'], $result);

        // another component of application
        $callback = function ($param, stubs\QuxInterface $qux2, $other = 'default') {
            return [$param, $qux2 instanceof Qux, $qux2->a, $other];
        };
        $result = Yii::$container->invoke($callback, ['M2792684']);
        $this->assertEquals(['M2792684', true, 'belongAppQux2', 'default'], $result);

        // component not belong application
        $callback = function ($param, stubs\QuxInterface $notBelongApp, $other) {
            return [$param, $notBelongApp instanceof Qux, $notBelongApp->a, $other];
        };
        $result = Yii::$container->invoke($callback, ['MDM', 'not_default']);
        $this->assertEquals(['MDM', true, 'independent', 'not_default'], $result);


        $myFunc = function ($a, NumberValidator $b, $c = 'default') {
            return[$a, \get_class($b), $c];
        };
        $result = Yii::$container->invoke($myFunc, ['a']);
        $this->assertEquals(['a', 'yii\validators\NumberValidator', 'default'], $result);

        $result = Yii::$container->invoke($myFunc, ['ok', 'value_of_c']);
        $this->assertEquals(['ok', 'yii\validators\NumberValidator', 'value_of_c'], $result);

        // use native php function
        $this->assertEquals(Yii::$container->invoke('trim', [' M2792684  ']), 'M2792684');

        // use helper function
        $array = ['M36', 'D426', 'Y2684'];
        $this->assertFalse(Yii::$container->invoke(['yii\helpers\ArrayHelper', 'isAssociative'], [$array]));


        $myFunc = function (\yii\console\Request $request, \yii\console\Response $response) {
            return [$request, $response];
        };
        [$request, $response] = Yii::$container->invoke($myFunc);
        $this->assertEquals($request, Yii::$app->request);
        $this->assertEquals($response, Yii::$app->response);
    }

    public function testAssociativeInvoke()
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    '__class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    '__class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        $closure = function ($a, $x = 5, $b) {
            return $a > $b;
        };
        $this->assertFalse(Yii::$container->invoke($closure, ['b' => 5, 'a' => 1]));
        $this->assertTrue(Yii::$container->invoke($closure, ['b' => 1, 'a' => 5]));
    }

    public function testResolveCallableDependencies()
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    '__class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    '__class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        $closure = function ($a, $b) {
            return $a > $b;
        };
        $this->assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, ['b' => 5, 'a' => 1]));
        $this->assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, ['a' => 1, 'b' => 5]));
        $this->assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, [1, 5]));
    }

    public function testOptionalDependencies()
    {
        $container = new Container();
        // Test optional unresolvable dependency.
        $closure = function (QuxInterface $test = null) {
            return $test;
        };
        $this->assertNull($container->invoke($closure));
    }

    public function testSetDependencies()
    {
        $container = new Container();
        $container->setDefinitions([
            'model.order' => Order::class,
            Cat::class => Type::class,
            'test\TraversableInterface' => [
                ['__class' => 'yiiunit\data\base\TraversableObject'],
                [['item1', 'item2']],
            ],
            'qux.using.closure' => function () {
                return new Qux();
            },
        ]);
        $container->setDefinitions([]);

        $this->assertInstanceOf(Order::class, $container->get('model.order'));
        $this->assertInstanceOf(Type::class, $container->get(Cat::class));

        $traversable = $container->get('test\TraversableInterface');
        $this->assertInstanceOf('yiiunit\data\base\TraversableObject', $traversable);
        $this->assertEquals('item1', $traversable->current());

        $this->assertInstanceOf('yiiunit\framework\di\stubs\Qux', $container->get('qux.using.closure'));
    }

    public function testContainerSingletons()
    {
        $container = new Container();
        $container->setSingletons([
            'model.order' => Order::class,
            'test\TraversableInterface' => [
                ['__class' => 'yiiunit\data\base\TraversableObject'],
                [['item1', 'item2']],
            ],
            'qux.using.closure' => function () {
                return new Qux();
            },
        ]);
        $container->setSingletons([]);

        $order = $container->get('model.order');
        $sameOrder = $container->get('model.order');
        $this->assertSame($order, $sameOrder);

        $traversable = $container->get('test\TraversableInterface');
        $sameTraversable = $container->get('test\TraversableInterface');
        $this->assertSame($traversable, $sameTraversable);

        $foo = $container->get('qux.using.closure');
        $sameFoo = $container->get('qux.using.closure');
        $this->assertSame($foo, $sameFoo);
    }

    public function testVariadicConstructor()
    {
        $container = new Container();
        $container->get('yiiunit\framework\di\stubs\Variadic');

        $this->assertTrue(true, 'Should be not exception above');
    }

    public function testVariadicCallable()
    {
        $container = new Container();
        $func = function (QuxInterface ...$quxes) {
            return "That's a whole lot of quxes!";
        };
        $container->invoke($func);

        $this->assertTrue(true, 'Should be not exception above');
    }

    public function testCircularReference()
    {
        $container = new Container();
        $container->set(Bar::class, Qux::class);
        $container->set(Qux::class, Bar::class);

        $this->expectException(CircularReferenceException::class);
        $container->get(Qux::class);
    }

    public function testCircularReferenceWithInstance()
    {
        $container = new Container();
        $container->set(Bar::class, 'qux');
        $container->set('qux', Qux::class, [Instance::of(Bar::class)]);

        $this->expectException(CircularReferenceException::class);
        $container->get(Bar::class);
    }

    public function testMethodCallInConfigurable()
    {
        $container = new Container();
        $qux = new Qux();
        $container->set('barSetter', [
            '__class' => BarSetter::class,
            'setQux()' => [$qux],
        ]);

        $res = $container->get('barSetter');
        $this->assertInstanceOf(BarSetter::class, $res);
        $this->assertSame($qux, $res->getQux());
    }

    public function testMethodCall()
    {
        $container = new Container();
        $qux = new Qux();
        $container->set('quxHolder', [
            '__class' => QuxHolder::class,
            'setQux()' => [$qux],
            'otherQux' => $qux,
        ]);

        $res = $container->get('quxHolder');
        $this->assertInstanceOf(QuxHolder::class, $res);
        $this->assertSame($qux, $res->getQux());
        $this->assertSame($qux, $res->otherQux);
    }
}
