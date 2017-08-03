<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use Yii;
use yii\base\DynamicModel;
use yii\behaviors\AttributeTypecastBehavior;
use yii\db\ActiveRecord;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\behaviors\AttributeTypecastBehavior]].
 * @see AttributeTypecastBehavior
 *
 * @group behaviors
 */
class AttributeTypecastBehaviorTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }

    protected function setUp()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $columns = [
            'id' => 'pk',
            'name' => 'string',
            'amount' => 'integer',
            'price' => 'float',
            'isActive' => 'boolean',
            'callback' => 'string',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_attribute_typecast', $columns)->execute();
    }

    protected function tearDown()
    {
        parent::tearDown();
        AttributeTypecastBehavior::clearAutoDetectedAttributeTypes();
        gc_enable();
        gc_collect_cycles();
    }

    // Tests :

    public function testTypecast()
    {
        $model = new ActiveRecordAttributeTypecast();

        $model->name = 123;
        $model->amount = '58';
        $model->price = '100.8';
        $model->isActive = 1;
        $model->callback = 'foo';

        $model->getAttributeTypecastBehavior()->typecastAttributes();

        $this->assertSame('123', $model->name);
        $this->assertSame(58, $model->amount);
        $this->assertSame(100.8, $model->price);
        $this->assertTrue($model->isActive);
        $this->assertSame('callback: foo', $model->callback);
    }

    /**
     * @depends testTypecast
     */
    public function testSkipNull()
    {
        $model = new ActiveRecordAttributeTypecast();
        $model->getAttributeTypecastBehavior()->skipOnNull = true;

        $model->name = null;
        $model->amount = null;
        $model->price = null;
        $model->isActive = null;
        $model->callback = null;

        $model->getAttributeTypecastBehavior()->typecastAttributes();

        $this->assertNull($model->name);
        $this->assertNull($model->amount);
        $this->assertNull($model->price);
        $this->assertNull($model->isActive);
        $this->assertNull($model->callback);

        $model->getAttributeTypecastBehavior()->skipOnNull = false;
        $model->getAttributeTypecastBehavior()->typecastAttributes();

        $this->assertSame('', $model->name);
        $this->assertSame(0, $model->amount);
        $this->assertSame(0.0, $model->price);
        $this->assertFalse($model->isActive);
        $this->assertSame('callback: ', $model->callback);
    }

    /**
     * @depends testTypecast
     */
    public function testEvents()
    {
        $model = new ActiveRecordAttributeTypecast();

        $model->callback = 'validate';
        $model->validate();
        $this->assertSame('callback: validate', $model->callback);

        $model->callback = 'insert';
        $model->save(false);
        $this->assertSame('callback: insert', $model->callback);

        $model->callback = 'update';
        $model->save(false);
        $this->assertSame('callback: update', $model->callback);

        $model->updateAll(['callback' => 'find']);
        $model->refresh();
        $this->assertSame('callback: find', $model->callback);
    }

    public function testAutoDetectAttributeTypes()
    {
        $model = (new DynamicModel(['name' => null, 'amount' => null, 'price' => null, 'isActive' => null]))
            ->addRule('name', 'string')
            ->addRule('amount', 'integer')
            ->addRule('price', 'number')
            ->addRule('isActive', 'boolean');

        $behavior = new AttributeTypecastBehavior();

        $behavior->attach($model);

        $expectedAttributeTypes = [
            'name' => AttributeTypecastBehavior::TYPE_STRING,
            'amount' => AttributeTypecastBehavior::TYPE_INTEGER,
            'price' => AttributeTypecastBehavior::TYPE_FLOAT,
            'isActive' => AttributeTypecastBehavior::TYPE_BOOLEAN,
        ];
        $this->assertEquals($expectedAttributeTypes, $behavior->attributeTypes);
    }

    /**
     * @depends testSkipNull
     *
     * @see https://github.com/yiisoft/yii2/issues/12880
     */
    public function testSkipNotSelectedAttribute()
    {
        $model = new ActiveRecordAttributeTypecast();
        $model->name = 'skip-not-selected';
        $model->amount = '58';
        $model->price = '100.8';
        $model->isActive = 1;
        $model->callback = 'foo';
        $model->save(false);

        /* @var $model ActiveRecordAttributeTypecast */
        $model = ActiveRecordAttributeTypecast::find()
            ->select(['id', 'name'])
            ->limit(1)
            ->one();

        $model->getAttributeTypecastBehavior()->typecastAttributes();
        $model->save(false);

        $model->refresh();
        $this->assertEquals('58', $model->amount);
    }
}

/**
 * Test Active Record class with [[AttributeTypecastBehavior]] behavior attached.
 *
 * @property int $id
 * @property string $name
 * @property int $amount
 * @property float $price
 * @property bool $isActive
 * @property string $callback
 *
 * @property AttributeTypecastBehavior $attributeTypecastBehavior
 */
class ActiveRecordAttributeTypecast extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'attributeTypecast' => [
                'class' => AttributeTypecastBehavior::className(),
                'attributeTypes' => [
                    'name' => AttributeTypecastBehavior::TYPE_STRING,
                    'amount' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'price' => AttributeTypecastBehavior::TYPE_FLOAT,
                    'isActive' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                    'callback' => function ($value) {
                        return 'callback: ' . $value;
                    },
                ],
                'typecastAfterValidate' => true,
                'typecastBeforeSave' => true,
                'typecastAfterFind' => true,
            ],
        ];
    }

    public static function tableName()
    {
        return 'test_attribute_typecast';
    }

    public function rules()
    {
        return [
            ['name', 'string'],
            ['amount', 'integer'],
            ['price', 'number'],
            ['isActive', 'boolean'],
        ];
    }

    /**
     * @return AttributeTypecastBehavior
     */
    public function getAttributeTypecastBehavior()
    {
        return $this->getBehavior('attributeTypecast');
    }
}
