<?php

namespace yiiunit\framework\behaviors;

use Yii;
use yii\db\Expression;
use yiiunit\TestCase;
use yii\db\Connection;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Unit test for [[\yii\behaviors\TimestampBehavior]].
 * @see TimestampBehavior
 *
 * @group behaviors
 */
class TimestampBehaviorTest extends TestCase
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
                ]
            ]
        ]);

        $columns = [
            'id' => 'pk',
            'created_at' => 'integer NOT NULL',
            'updated_at' => 'integer',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_auto_timestamp', $columns)->execute();

        $columns = [
            'id' => 'pk',
            'created_at' => 'string NOT NULL',
            'updated_at' => 'string',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_auto_timestamp_string', $columns)->execute();
    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
    }

    // Tests :

    public function testNewRecord()
    {
        $currentTime = time();

        ActiveRecordTimestamp::$behaviors = [
            TimestampBehavior::className(),
        ];
        $model = new ActiveRecordTimestamp();
        $model->save(false);

        $this->assertTrue($model->created_at >= $currentTime);
        $this->assertTrue($model->updated_at >= $currentTime);
    }

    /**
     * @depends testNewRecord
     */
    public function testUpdateRecord()
    {
        $currentTime = time();

        ActiveRecordTimestamp::$behaviors = [
            TimestampBehavior::className(),
        ];
        $model = new ActiveRecordTimestamp();
        $model->save(false);

        $enforcedTime = $currentTime - 100;

        $model->created_at = $enforcedTime;
        $model->updated_at = $enforcedTime;
        $model->save(false);

        $this->assertEquals($enforcedTime, $model->created_at, 'Create time has been set on update!');
        $this->assertTrue($model->updated_at >= $currentTime, 'Update time has NOT been set on update!');
    }

    public function expressionProvider()
    {
        return [
            [function() { return '2015-01-01'; }, '2015-01-01'],
            [new Expression("strftime('%Y')"), date('Y')],
        ];
    }

    /**
     * @dataProvider expressionProvider
     */
    public function testNewRecordExpression($expression, $expected)
    {
        ActiveRecordTimestamp::$tableName = 'test_auto_timestamp_string';
        ActiveRecordTimestamp::$behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => $expression,
            ],
        ];
        $model = new ActiveRecordTimestamp();
        $model->save(false);
        if ($expression instanceof Expression) {
            $this->assertInstanceOf(Expression::className(), $model->created_at);
            $this->assertInstanceOf(Expression::className(), $model->updated_at);
            $model->refresh();
        }
        $this->assertEquals($expected, $model->created_at);
        $this->assertEquals($expected, $model->updated_at);
    }

    /**
     * @depends testNewRecord
     */
    public function testUpdateRecordExpression()
    {
        ActiveRecordTimestamp::$tableName = 'test_auto_timestamp_string';
        ActiveRecordTimestamp::$behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => new Expression("strftime('%Y')"),
            ],
        ];
        $model = new ActiveRecordTimestamp();
        $model->save(false);

        $enforcedTime = date('Y') - 1;

        $model->created_at = $enforcedTime;
        $model->updated_at = $enforcedTime;
        $model->save(false);
        $this->assertEquals($enforcedTime, $model->created_at, 'Create time has been set on update!');
        $this->assertInstanceOf(Expression::className(), $model->updated_at);
        $model->refresh();
        $this->assertEquals($enforcedTime, $model->created_at, 'Create time has been set on update!');
        $this->assertEquals(date('Y'), $model->updated_at);
    }
}

/**
 * Test Active Record class with [[TimestampBehavior]] behavior attached.
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 */
class ActiveRecordTimestamp extends ActiveRecord
{
    public static $behaviors;
    public static $tableName = 'test_auto_timestamp';

    public function behaviors()
    {
        return static::$behaviors;
    }

    public static function tableName()
    {
        return static::$tableName;
    }
}
