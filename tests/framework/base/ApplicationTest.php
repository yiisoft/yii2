<?php

namespace yiiunit\framework\base;

use Yii;
use yii\log\Dispatcher;
use yiiunit\data\ar\Cat;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Type;
use yiiunit\TestCase;

class ApplicationTest extends TestCase
{
    public function testContainerDefinitions()
    {
        $this->mockApplication([
            'container' => [
                'definitions' => [
                    'model.order' => Order::className(),
                    Cat::className() => Type::className(),
                    'test\TraversableInterface' => [
                        ['class' => 'yiiunit\data\base\TraversableObject'],
                        [['item1', 'item2']]
                    ],
                    'foo.using.closure' => function () {
                        return new Foo();
                    }
                ]
            ]
        ]);

        $this->assertInstanceOf(Order::className(), Yii::createObject('model.order'));
        $this->assertInstanceOf(Type::className(), Yii::createObject(Cat::className()));

        $traversable = Yii::createObject('test\TraversableInterface');
        $this->assertInstanceOf('yiiunit\data\base\TraversableObject', $traversable);
        $this->assertEquals('item1', $traversable->current());

        $this->assertInstanceOf('yiiunit\framework\base\Foo', Yii::createObject('foo.using.closure'));
    }

    public function testContainerSingletons()
    {
        $this->mockApplication([
            'container' => [
                'singletons' => [
                    'model.order' => Order::className(),
                    'test\TraversableInterface' => [
                        ['class' => 'yiiunit\data\base\TraversableObject'],
                        [['item1', 'item2']]
                    ],
                    'foo.using.closure' => function () {
                        return new Foo();
                    }
                ]
            ]
        ]);


        $order = Yii::createObject('model.order');
        $sameOrder = Yii::createObject('model.order');
        $this->assertSame($order, $sameOrder);

        $traversable = Yii::createObject('test\TraversableInterface');
        $sameTraversable = Yii::createObject('test\TraversableInterface');
        $this->assertSame($traversable, $sameTraversable);

        $foo = Yii::createObject('foo.using.closure');
        $sameFoo = Yii::createObject('foo.using.closure');
        $this->assertSame($foo, $sameFoo);
    }

    public function testContainerSettingsAffectBootstrap()
    {
        $this->mockApplication([
            'container' => [
                'definitions' => [
                    Dispatcher::className() => DispatcherMock::className()
                ]
            ],
            'bootstrap' => ['log']
        ]);

        $this->assertInstanceOf(DispatcherMock::className(), Yii::$app->log);
    }
}

class Foo {

}

class DispatcherMock extends Dispatcher
{

}
