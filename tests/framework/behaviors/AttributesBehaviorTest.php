<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use Yii;
use yii\behaviors\AttributesBehavior;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\behaviors\AttributesBehavior]].
 * @see AttributesBehavior
 *
 * @group behaviors
 */
class AttributesBehaviorTest extends TestCase
{
    /**
     * @var Connection test db connection
     */
    protected $dbConnection;

    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }

    public function setUp()
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
            'alias' => 'string',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_attribute', $columns)->execute();
    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
    }

    // Tests :

    /**
     * @return array
     */
    public function preserveNonEmptyValuesDataProvider()
    {
        return [
            [
                'John Doe',
                false,
                'John Doe',
                null,
            ],
            [
                'John Doe',
                false,
                'John Doe',
                'Johnny',
            ],
            [
                'John Doe',
                true,
                'John Doe',
                null,
            ],
            [
                'Johnny',
                true,
                'John Doe',
                'Johnny',
            ],
        ];
    }

    /**
     * @dataProvider preserveNonEmptyValuesDataProvider
     * @param string $aliasExpected
     * @param bool $preserveNonEmptyValues
     * @param string $name
     * @param string|null $alias
     */
    public function testPreserveNonEmptyValues(
        $aliasExpected,
        $preserveNonEmptyValues,
        $name,
        $alias
    ) {
        $model = new ActiveRecordWithAttributesBehavior();
        $model->attributesBehavior->preserveNonEmptyValues = $preserveNonEmptyValues;
        $model->name = $name;
        $model->alias = $alias;
        $model->validate();

        $this->assertEquals($aliasExpected, $model->alias);
    }

    /**
     * @return array
     */
    public function orderProvider()
    {
        return [
            [
                'name: Johnny',
                [ActiveRecordWithAttributesBehavior::EVENT_BEFORE_VALIDATE => ['name', 'alias']],
                // 1: name = alias; 2: alias = name; check alias
                'John Doe', // name
                'Johnny', // alias
            ],
            [
                'John Doe',
                [ActiveRecordWithAttributesBehavior::EVENT_BEFORE_VALIDATE => ['alias', 'name']],
                // 2: alias = name; 1: name = alias; check alias
                'John Doe', // name
                'Johnny', // alias
            ],
        ];
    }

    /**
     * @dataProvider orderProvider
     * @param string $aliasExpected
     * @param array $order
     * @param string $name
     * @param string $alias
     */
    public function testOrder(
        $aliasExpected,
        $order,
        $name,
        $alias
    ) {
        $model = new ActiveRecordWithAttributesBehavior();
        $model->attributesBehavior->order = $order;
        $model->name = $name;
        $model->alias = $alias;
        $model->validate();

        $this->assertEquals($aliasExpected, $model->alias);
    }
}

/**
 * Test Active Record class with [[AttributesBehavior]] behavior attached.
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 *
 * @property AttributesBehavior $attributesBehavior
 */
class ActiveRecordWithAttributesBehavior extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'attributes' => [
                'class' => AttributesBehavior::className(),
                'attributes' => [
                    'alias' => [
                        self::EVENT_BEFORE_VALIDATE => function ($event) {
                            return $event->sender->name;
                        },
                    ],
                    'name' => [
                        self::EVENT_BEFORE_VALIDATE => function ($event, $attribute) {
                            return $attribute . ': ' . $event->sender->alias;
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'test_attribute';
    }

    /**
     * @return AttributesBehavior
     */
    public function getAttributesBehavior()
    {
        return $this->getBehavior('attributes');
    }
}
