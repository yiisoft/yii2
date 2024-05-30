<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yiiunit\TestCase;

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

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }

    protected function setUp(): void
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

    protected function tearDown(): void
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
        gc_enable();
        gc_collect_cycles();
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

    /**
     * @depends testNewRecord
     */
    public function testUpdateCleanRecord()
    {
        ActiveRecordTimestamp::$behaviors = [
            TimestampBehavior::className(),
        ];
        $model = new ActiveRecordTimestamp();
        $model->save(false);

        $model->on(
            ActiveRecordTimestamp::EVENT_AFTER_UPDATE,
            function ($event) {
                $this->assertEmpty($event->changedAttributes);
            }
        );

        $model->save(false);
    }

    public function expressionProvider()
    {
        return [
            [function () { return '2015-01-01'; }, '2015-01-01'],
            [new Expression("strftime('%Y')"), date('Y')],
            ['2015-10-20', '2015-10-20'],
            [time(), time()],
            [[$this, 'arrayCallable'], '2015-10-20'],
        ];
    }

    /**
     * @dataProvider expressionProvider
     * @param mixed $expression
     * @param mixed $expected
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
        if ($expression instanceof ExpressionInterface) {
            $this->assertInstanceOf('yii\db\ExpressionInterface', $model->created_at);
            $this->assertInstanceOf('yii\db\ExpressionInterface', $model->updated_at);
            $model->refresh();
        }
        $this->assertEquals($expected, $model->created_at);
        $this->assertEquals($expected, $model->updated_at);
    }

    public function arrayCallable($event)
    {
        return '2015-10-20';
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

    public function testTouchingNewRecordGeneratesException()
    {
        ActiveRecordTimestamp::$behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => new Expression("strftime('%Y')"),
            ],
        ];
        $model = new ActiveRecordTimestamp();

        $this->expectException('yii\base\InvalidCallException');

        $model->touch('created_at');
    }

    public function testTouchingNotNewRecord()
    {
        ActiveRecordTimestamp::$behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => new Expression("strftime('%Y')"),
            ],
        ];
        $model = new ActiveRecordTimestamp();
        $enforcedTime = date('Y') - 1;
        $model->created_at = $enforcedTime;
        $model->updated_at = $enforcedTime;
        $model->save(false);
        $expectedCreatedAt = new Expression("strftime('%Y')");

        $model->touch('created_at');

        $this->assertEquals($expectedCreatedAt, $model->created_at);
    }
}

/**
 * Test Active Record class with [[TimestampBehavior]] behavior attached.
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
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
