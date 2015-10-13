<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\OrderItem;
use yiiunit\TestCase;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\web\stubs\Bar;
use yiiunit\framework\web\stubs\OtherQux;
use yii\base\InlineAction;

/**
 * @group web
 */
class ControllerTest extends TestCase
{

    public function testBindActionParams()
    {
        $this->mockApplication([
            'components'=>[
                'barBelongApp'=>[
                    'class'=>  Bar::className(),
                    'foo'=>'belong_app'
                ],
                'quxApp'=>[
                    'class' => OtherQux::className(),
                    'b' => 'belong_app'
                ]
            ]
        ]);

        $controller = new FakeController('fake', Yii::$app);
        $aksi1 = new InlineAction('aksi1', $controller, 'actionAksi1');
        $aksi2 = new InlineAction('aksi2', $controller, 'actionAksi2');
        $aksi3 = new InlineAction('aksi3', $controller, 'actionAksi3');

        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => Qux::className(),
            'a' => 'D426'
        ]);
        Yii::$container->set(Bar::className(),[
            'foo' => 'independent'
        ]);
        
        $params = ['fromGet'=>'from query params','q'=>'d426','validator'=>'avaliable'];

        list($bar, $fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertTrue($bar instanceof Bar);
        $this->assertNotEquals($bar, Yii::$app->barBelongApp);
        $this->assertEquals('independent', $bar->foo);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('default', $other);

        list($barBelongApp, $qux) = $controller->bindActionParams($aksi2, $params);
        $this->assertTrue($barBelongApp instanceof Bar);
        $this->assertEquals($barBelongApp, Yii::$app->barBelongApp);
        $this->assertEquals('belong_app', $barBelongApp->foo);
        $this->assertTrue($qux instanceof Qux);
        $this->assertEquals('D426', $qux->a);

        list($quxApp) = $controller->bindActionParams($aksi3, $params);
        $this->assertTrue($quxApp instanceof OtherQux);
        $this->assertEquals($quxApp, Yii::$app->quxApp);
        $this->assertEquals('belong_app', $quxApp->b);

        $result = $controller->runAction('aksi4', $params);
        $this->assertEquals(['independent', 'other_qux', 'd426'], $result);

        $result = $controller->runAction('aksi5', $params);
        $this->assertEquals(['d426', 'independent', 'other_qux'], $result);

        $result = $controller->runAction('aksi6', $params);
        $this->assertEquals(['d426', false, true], $result);
        
        // Manually inject an instance of \StdClass
        // In this case we don't want a newly created instance, but use the existing one
        $stdClass = new \StdClass;
        $stdClass->test = 'dummy';
        $result = $controller->runAction('aksi7', array_merge($params, ['validator' => $stdClass]));
        $this->assertEquals(['d426', 'dummy'], $result);
        
        // Manually inject a string instead of an instance of \StdClass
        // Since this is wrong usage, we expect a new instance of the type hinted \StdClass anyway
        $stdClass = 'string';
        $result = $controller->runAction('aksi8', array_merge($params, ['validator' => $stdClass]));
        $this->assertEquals(['d426', 'object'], $result);
    }

    public function getConnection() {
        $databases = self::getParam('databases');

        // Try all.
        foreach($databases as $config) {
            unset($config['fixture']);
            if (!isset($config['class'])) {
                $config['class'] = 'yii\db\Connection';
            }
            try {
                $db = \Yii::createObject($config);
                $db->open();
                break;
            } catch (\Exception $e) {}

        }

        if (!isset($db)) {
            $this->markTestSkipped("No database available.");
        }
        return $db;



    }

    public function testBindActiveRecord() {
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();

        $controller = new FakeController('fake', Yii::$app);



        $showCustomer = new InlineAction('ShowCustomer', $controller, 'actionShowCustomer');
        /** @var \yiiunit\data\ar\Customer $customer */
        list($customer) = $controller->bindActionParams($showCustomer, ['customer' => "1"]);
        $this->assertInstanceOf(Customer::className(), $customer);
        // Check email to make sure we have actually queried and not just created an object and set the id..
        $this->assertEquals('user1@example.com', $customer->email);

        $showOrderItem = new InlineAction('ShowOrderItem', $controller, 'actionShowOrderItem');

        /** @var \yiiunit\data\ar\OrderItem $orderItem */
        list($orderItem) = $controller->bindActionParams($showOrderItem, ['orderItem' => ['order_id' => "1", 'item_id' => "1"]]);
        $this->assertInstanceOf(OrderItem::className(), $orderItem);
        // Check subtotal to make sure we have actually queried and not just created an object and set the id..
        $this->assertEquals(30, $orderItem->subtotal);


        $showOptionalCustomer = new InlineAction('ShowOptionalCustomer', $controller, 'actionShowOptionalCustomer');
        list($customer) = $controller->bindActionParams($showOptionalCustomer, []);
        $this->assertNull($customer);

        list($customer) = $controller->bindActionParams($showOptionalCustomer, ['customer' => 10]);
        $this->assertNull($customer);

    }

    /**
     * @expectedException \yii\web\NotFoundHttpException
     */
    public function testBindActiveRecordNotFound1() {
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();

        $controller = new FakeController('fake', Yii::$app);
        $showCustomer = new InlineAction('ShowCustomer', $controller, 'actionShowCustomer');
        $controller->bindActionParams($showCustomer, ['customer' => "10"]);

    }

    /**
     * @expectedException \yii\web\NotFoundHttpException
     */
    public function testBindActiveRecordNotFound2() {
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();

        $controller = new FakeController('fake', Yii::$app);
        $showOrderItem = new InlineAction('ShowOrderItem', $controller, 'actionShowOrderItem');

        $controller->bindActionParams($showOrderItem, ['orderItem' => ['order_id' => "1111", 'item_id' => "1"]]);


    }

    /**
     * @expectedException \yii\web\BadRequestHttpException
     */
    public function testBindActiveRecordMalformed1() {
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();

        $controller = new FakeController('fake', Yii::$app);
        $showCustomer = new InlineAction('ShowCustomer', $controller, 'actionShowCustomer');
        $controller->bindActionParams($showCustomer, ['customer' => ["test" => "10"]]);

    }
    /**
     * @expectedException \yii\web\BadRequestHttpException
     */
    public function testBindActiveRecordMalformed2() {
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();

        $controller = new FakeController('fake', Yii::$app);
        $showOrderItem = new InlineAction('ShowOrderItem', $controller, 'actionShowOrderItem');

        $controller->bindActionParams($showOrderItem, ['orderItem' => ['order_id' => ["test" => "1"], 'item_id' => "1"]]);
    }




}
