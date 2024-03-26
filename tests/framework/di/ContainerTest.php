<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\di;

use Yii;
use yii\di\Container;
use yii\di\Instance;
use yii\validators\NumberValidator;
use yiiunit\data\ar\Cat;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Type;
use yiiunit\framework\di\stubs\Alpha;
use yiiunit\framework\di\stubs\Bar;
use yiiunit\framework\di\stubs\BarSetter;
use yiiunit\framework\di\stubs\Beta;
use yiiunit\framework\di\stubs\Car;
use yiiunit\framework\di\stubs\Corge;
use yiiunit\framework\di\stubs\Foo;
use yiiunit\framework\di\stubs\FooProperty;
use yiiunit\framework\di\stubs\Kappa;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\di\stubs\QuxAnother;
use yiiunit\framework\di\stubs\QuxInterface;
use yiiunit\framework\di\stubs\QuxFactory;
use yiiunit\framework\di\stubs\UnionTypeNotNull;
use yiiunit\framework\di\stubs\UnionTypeNull;
use yiiunit\framework\di\stubs\UnionTypeWithClass;
use yiiunit\framework\di\stubs\Zeta;
use yiiunit\TestCase;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @group di
 */
class ContainerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Yii::$container = new Container();
    }

    public function testDefault(): void
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
        $container->set('foo', fn(Container $c, $params, $config) => $c->get(Foo::class));
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
        $container->set('foo', ['class' => $fooSetter, 'bar' => Instance::of('bar')]);
        $container->set('bar', ['class' => $barSetter, 'qux' => Instance::of('qux')]);
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

    public function testInvoke(): void
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    'class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    'class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => 'yiiunit\framework\di\stubs\Qux',
            'a' => 'independent',
        ]);

        // use component of application
        $callback = fn($param, stubs\QuxInterface $qux, Bar $bar) => [$param, $qux instanceof Qux, $qux->a, $bar->qux->a];
        $result = Yii::$container->invoke($callback, ['D426']);
        $this->assertEquals(['D426', true, 'belongApp', 'independent'], $result);

        // another component of application
        $callback = fn($param, stubs\QuxInterface $qux2, $other = 'default') => [$param, $qux2 instanceof Qux, $qux2->a, $other];
        $result = Yii::$container->invoke($callback, ['M2792684']);
        $this->assertEquals(['M2792684', true, 'belongAppQux2', 'default'], $result);

        // component not belong application
        $callback = fn($param, stubs\QuxInterface $notBelongApp, $other) => [$param, $notBelongApp instanceof Qux, $notBelongApp->a, $other];
        $result = Yii::$container->invoke($callback, ['MDM', 'not_default']);
        $this->assertEquals(['MDM', true, 'independent', 'not_default'], $result);


        $myFunc = fn($a, NumberValidator $b, $c = 'default') => [$a, $b::class, $c];
        $result = Yii::$container->invoke($myFunc, ['a']);
        $this->assertEquals(['a', 'yii\validators\NumberValidator', 'default'], $result);

        $result = Yii::$container->invoke($myFunc, ['ok', 'value_of_c']);
        $this->assertEquals(['ok', 'yii\validators\NumberValidator', 'value_of_c'], $result);

        // use native php function
        $this->assertEquals(Yii::$container->invoke('trim', [' M2792684  ']), 'M2792684');

        // use helper function
        $array = ['M36', 'D426', 'Y2684'];
        $this->assertFalse(Yii::$container->invoke(['yii\helpers\ArrayHelper', 'isAssociative'], [$array]));


        $myFunc = fn(\yii\console\Request $request, \yii\console\Response $response) => [$request, $response];
        [$request, $response] = Yii::$container->invoke($myFunc);
        $this->assertEquals($request, Yii::$app->request);
        $this->assertEquals($response, Yii::$app->response);
    }

    public function testAssociativeInvoke(): void
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    'class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    'class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        $closure = fn($a, $b, $x = 5) => $a > $b;
        $this->assertFalse(Yii::$container->invoke($closure, ['b' => 5, 'a' => 1]));
        $this->assertTrue(Yii::$container->invoke($closure, ['b' => 1, 'a' => 5]));
    }

    public function testResolveCallableDependencies(): void
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    'class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    'class' => 'yiiunit\framework\di\stubs\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        $closure = fn($a, $b) => $a > $b;
        $this->assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, ['b' => 5, 'a' => 1]));
        $this->assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, ['a' => 1, 'b' => 5]));
        $this->assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, [1, 5]));
    }

    public function testOptionalDependencies(): void
    {
        $container = new Container();
        // Test optional unresolvable dependency.
        $closure = function (?QuxInterface $test = null) {
            return $test;
        };
        $this->assertNull($container->invoke($closure));
    }

    public function testSetDependencies(): void
    {
        $container = new Container();
        $container->setDefinitions([
            'model.order' => Order::class,
            Cat::class => Type::class,
            'test\TraversableInterface' => [
                ['class' => 'yiiunit\data\base\TraversableObject'],
                [['item1', 'item2']],
            ],
            'qux.using.closure' => fn() => new Qux(),
            'rollbar',
            'baibaratsky\yii\rollbar\Rollbar'
        ]);
        $container->setDefinitions([]);

        $this->assertInstanceOf(Order::class, $container->get('model.order'));
        $this->assertInstanceOf(Type::class, $container->get(Cat::class));

        $traversable = $container->get('test\TraversableInterface');
        $this->assertInstanceOf('yiiunit\data\base\TraversableObject', $traversable);
        $this->assertEquals('item1', $traversable->current());

        $this->assertInstanceOf('yiiunit\framework\di\stubs\Qux', $container->get('qux.using.closure'));

        try {
            $container->get('rollbar');
            $this->fail('InvalidConfigException was not thrown');
        } catch (\Exception $e) {
            $this->assertInstanceOf('yii\base\InvalidConfigException', $e);
        }
    }

    public function testStaticCall(): void
    {
        $container = new Container();
        $container->setDefinitions([
            'qux' => QuxFactory::create(...),
        ]);

        $qux = $container->get('qux');
        $this->assertInstanceOf(Qux::class, $qux);
        $this->assertSame(42, $qux->a);
    }

    public function testObject(): void
    {
        $container = new Container();
        $container->setDefinitions([
            'qux' => new Qux(42),
        ]);

        $qux = $container->get('qux');
        $this->assertInstanceOf(Qux::class, $qux);
        $this->assertSame(42, $qux->a);
    }

    public function testDi3Compatibility(): void
    {
        $container = new Container();
        $container->setDefinitions([
            'test\TraversableInterface' => [
                '__class' => 'yiiunit\data\base\TraversableObject',
                '__construct()' => [['item1', 'item2']],
            ],
            'qux' => [
                '__class' => Qux::class,
                'a' => 42,
            ],
        ]);

        $qux = $container->get('qux');
        $this->assertInstanceOf(Qux::class, $qux);
        $this->assertSame(42, $qux->a);

        $traversable = $container->get('test\TraversableInterface');
        $this->assertInstanceOf('yiiunit\data\base\TraversableObject', $traversable);
        $this->assertEquals('item1', $traversable->current());
    }

    public function testInstanceOf(): void
    {
        $container = new Container();
        $container->setDefinitions([
            'qux' => [
                'class' => Qux::class,
                'a' => 42,
            ],
            'bar' => [
                '__class' => Bar::class,
                '__construct()' => [
                    Instance::of('qux')
                ],
            ],
        ]);
        $bar = $container->get('bar');
        $this->assertInstanceOf(Bar::class, $bar);
        $qux = $bar->qux;
        $this->assertInstanceOf(Qux::class, $qux);
        $this->assertSame(42, $qux->a);
    }

    public function testReferencesInArrayInDependencies(): void
    {
        $quxInterface = 'yiiunit\framework\di\stubs\QuxInterface';
        $container = new Container();
        $container->resolveArrays = true;
        $container->setSingletons([
            $quxInterface => [
                'class' => Qux::class,
                'a' => 42,
            ],
            'qux' => Instance::of($quxInterface),
            'bar' => [
                'class' => Bar::class,
            ],
            'corge' => [
                '__class' => Corge::class,
                '__construct()' => [
                    [
                        'qux' => Instance::of('qux'),
                        'bar' => Instance::of('bar'),
                        'q33' => new Qux(33),
                    ],
                ],
            ],
        ]);
        $corge = $container->get('corge');
        $this->assertInstanceOf(Corge::class, $corge);
        $qux = $corge->map['qux'];
        $this->assertInstanceOf(Qux::class, $qux);
        $this->assertSame(42, $qux->a);
        $bar = $corge->map['bar'];
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertSame($qux, $bar->qux);
        $q33 = $corge->map['q33'];
        $this->assertInstanceOf(Qux::class, $q33);
        $this->assertSame(33, $q33->a);
    }

    public function testGetByInstance(): void
    {
        $container = new Container();
        $container->setSingletons([
            'one' => Qux::class,
            'two' => Instance::of('one'),
        ]);
        $one = $container->get(Instance::of('one'));
        $two = $container->get(Instance::of('two'));
        $this->assertInstanceOf(Qux::class, $one);
        $this->assertSame($one, $two);
        $this->assertSame($one, $container->get('one'));
        $this->assertSame($one, $container->get('two'));
    }

    public function testWithoutDefinition(): void
    {
        $container = new Container();

        $one = $container->get(Qux::class);
        $two = $container->get(Qux::class);
        $this->assertInstanceOf(Qux::class, $one);
        $this->assertInstanceOf(Qux::class, $two);
        $this->assertSame(1, $one->a);
        $this->assertSame(1, $two->a);
        $this->assertNotSame($one, $two);
    }

    public function testGetByClassIndirectly(): void
    {
        $container = new Container();
        $container->setSingletons([
            'qux' => Qux::class,
            Qux::class => [
                'a' => 42,
            ],
        ]);

        $qux = $container->get('qux');
        $this->assertInstanceOf(Qux::class, $qux);
        $this->assertSame(42, $qux->a);
    }

    public function testThrowingNotFoundException(): void
    {
        $this->expectException(\yii\di\NotInstantiableException::class);

        $container = new Container();
        $container->get('non_existing');
    }

    public function testContainerSingletons(): void
    {
        $container = new Container();
        $container->setSingletons([
            'model.order' => Order::class,
            'test\TraversableInterface' => [
                ['class' => 'yiiunit\data\base\TraversableObject'],
                [['item1', 'item2']],
            ],
            'qux.using.closure' => fn() => new Qux(),
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
        if (\defined('HHVM_VERSION')) {
            static::markTestSkipped('Can not test on HHVM because it does not support variadics.');
        }

        $container = new Container();
        $container->get('yiiunit\framework\di\stubs\Variadic');

        $this->assertTrue(true);
    }

    public function testVariadicCallable()
    {
        if (\defined('HHVM_VERSION')) {
            static::markTestSkipped('Can not test on HHVM because it does not support variadics.');
        }

        require __DIR__ . '/testContainerWithVariadicCallable.php';

        $this->assertTrue(true);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18245
     */
    public function testDelayedInitializationOfSubArray(): void
    {
        $definitions = [
            'test' => [
                'class' => Corge::class,
                '__construct()' => [
                    [Instance::of('setLater')],
                ],
            ],
        ];

        $application = Yii::createObject([
            '__class' => \yii\web\Application::class,
            'basePath' => __DIR__,
            'id' => 'test',
            'components' => [
                'request' => [
                    'baseUrl' => '123'
                ],
            ],
            'container' => [
                'definitions' => $definitions,
            ],
        ]);

        Yii::$container->set('setLater', new Qux());
        Yii::$container->get('test');

        $this->assertTrue(true);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18304
     */
    public function testNulledConstructorParameters(): void
    {
        $alpha = (new Container())->get(Alpha::class);
        $this->assertInstanceOf(Beta::class, $alpha->beta);
        $this->assertNull($alpha->omega);

        $QuxInterface = __NAMESPACE__ . '\stubs\QuxInterface';
        $container = new Container();
        $container->set($QuxInterface, Qux::class);
        $alpha = $container->get(Alpha::class);
        $this->assertInstanceOf(Beta::class, $alpha->beta);
        $this->assertInstanceOf($QuxInterface, $alpha->omega);
        $this->assertNull($alpha->unknown);
        $this->assertNull($alpha->color);

        $container = new Container();
        $container->set(__NAMESPACE__ . '\stubs\AbstractColor', __NAMESPACE__ . '\stubs\Color');
        $alpha = $container->get(Alpha::class);
        $this->assertInstanceOf(__NAMESPACE__ . '\stubs\Color', $alpha->color);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18284
     */
    public function testNamedConstructorParameters(): void
    {
        $test = (new Container())->get(Car::class, [
            'name' => 'Hello',
            'color' => 'red',
        ]);
        $this->assertSame('Hello', $test->name);
        $this->assertSame('red', $test->color);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18284
     */
    public function testInvalidConstructorParameters(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Dependencies indexed by name and by position in the same array are not allowed.');
        (new Container())->get(Car::class, [
            'color' => 'red',
            'Hello',
        ]);
    }

    public static function dataNotInstantiableException(): array
    {
        return [
            [Bar::class],
            [Kappa::class],
        ];
    }

    /**
     * @dataProvider dataNotInstantiableException
     *
     * @see https://github.com/yiisoft/yii2/pull/18379
     *
     * @param string $class The class name.
     */
    public function testNotInstantiableException(string $class): void
    {
        $this->expectException(\yii\di\NotInstantiableException::class);
        (new Container())->get($class);
    }

    public function testNullTypeConstructorParameters(): void
    {
        $zeta = (new Container())->get(Zeta::class);
        $this->assertInstanceOf(Beta::class, $zeta->beta);
        $this->assertInstanceOf(Beta::class, $zeta->betaNull);
        $this->assertNull($zeta->color);
        $this->assertNull($zeta->colorNull);
        $this->assertNull($zeta->qux);
        $this->assertNull($zeta->quxNull);
        $this->assertNull($zeta->unknown);
        $this->assertNull($zeta->unknownNull);
    }

    public function testUnionTypeWithNullConstructorParameters(): void
    {
        $unionType = (new Container())->get(UnionTypeNull::class);
        $this->assertInstanceOf(UnionTypeNull::class, $unionType);
    }

    public function testUnionTypeWithoutNullConstructorParameters(): void
    {
        $unionType = (new Container())->get(UnionTypeNotNull::class, ['value' => 'a']);
        $this->assertInstanceOf(UnionTypeNotNull::class, $unionType);

        $unionType = (new Container())->get(UnionTypeNotNull::class, ['value' => 1]);
        $this->assertInstanceOf(UnionTypeNotNull::class, $unionType);

        $unionType = (new Container())->get(UnionTypeNotNull::class, ['value' => 2.3]);
        $this->assertInstanceOf(UnionTypeNotNull::class, $unionType);

        $unionType = (new Container())->get(UnionTypeNotNull::class, ['value' => true]);
        $this->assertInstanceOf(UnionTypeNotNull::class, $unionType);

        $this->expectException('TypeError');
        (new Container())->get(UnionTypeNotNull::class);
    }

    public function testUnionTypeWithClassConstructorParameters(): void
    {
        $unionType = (new Container())->get(UnionTypeWithClass::class, ['value' => new Beta()]);
        $this->assertInstanceOf(UnionTypeWithClass::class, $unionType);
        $this->assertInstanceOf(Beta::class, $unionType->value);

        $this->expectException('TypeError');
        (new Container())->get(UnionTypeNotNull::class);
    }

    public function testResolveCallableDependenciesUnionTypes(): void
    {
        $this->mockApplication([
            'components' => [
                Beta::class,
            ],
        ]);

        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => Qux::class,
        ]);

        $className = 'yiiunit\framework\di\stubs\StaticMethodsWithUnionTypes';

        $params = Yii::$container->resolveCallableDependencies([$className, 'withBetaUnion']);
        $this->assertInstanceOf(Beta::class, $params[0]);

        $params = Yii::$container->resolveCallableDependencies([$className, 'withBetaUnionInverse']);
        $this->assertInstanceOf(Beta::class, $params[0]);

        $params = Yii::$container->resolveCallableDependencies([$className, 'withBetaAndQuxUnion']);
        $this->assertInstanceOf(Beta::class, $params[0]);

        $params = Yii::$container->resolveCallableDependencies([$className, 'withQuxAndBetaUnion']);
        $this->assertInstanceOf(Qux::class, $params[0]);
    }

    public function testResolveCallableDependenciesIntersectionTypes(): void
    {
        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => Qux::class,
        ]);

        $className = 'yiiunit\framework\di\stubs\StaticMethodsWithIntersectionTypes';

        $params = Yii::$container->resolveCallableDependencies([$className, 'withQuxInterfaceAndQuxAnotherIntersection']);
        $this->assertInstanceOf(Qux::class, $params[0]);

        $params = Yii::$container->resolveCallableDependencies([$className, 'withQuxAnotherAndQuxInterfaceIntersection']);
        $this->assertInstanceOf(QuxAnother::class, $params[0]);
    }
}
