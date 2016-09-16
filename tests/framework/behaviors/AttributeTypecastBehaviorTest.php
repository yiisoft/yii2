<?php

namespace yiiunit\framework\behaviors;

use Yii;
use yiiunit\TestCase;
use yii\db\ActiveRecord;
use yii\behaviors\AttributeTypecastBehavior;

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
                ]
            ]
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
        $this->assertSame(true, $model->isActive);
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
        $this->assertSame(false, $model->isActive);
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
        $model = new ActiveRecordAttributeTypecast();

        $model->getAttributeTypecastBehavior()->attributeTypes = null;
        $model->getAttributeTypecastBehavior()->init();

        $expectedAttributeTypes = [
            'name' => AttributeTypecastBehavior::TYPE_STRING,
            'amount' => AttributeTypecastBehavior::TYPE_INTEGER,
            'price' => AttributeTypecastBehavior::TYPE_FLOAT,
            'isActive' => AttributeTypecastBehavior::TYPE_BOOLEAN,
        ];
        $this->assertEquals($expectedAttributeTypes, $model->getAttributeTypecastBehavior()->attributeTypes);
    }
}

/**
 * Test Active Record class with [[AttributeTypecastBehavior]] behavior attached.
 *
 * @property integer $id
 * @property string $name
 * @property integer $amount
 * @property float $price
 * @property boolean $isActive
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