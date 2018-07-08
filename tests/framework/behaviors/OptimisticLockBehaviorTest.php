<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use Yii;
use yii\behaviors\OptimisticLockBehavior;
use yii\web\Request;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\behaviors\OptimisticLockBehavior]].
 * @see OptimisticLockBehavior
 *
 * @group behaviors
 */
class OptimisticLockBehaviorTest extends TestCase
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
            'version' => 'integer NOT NULL',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_auto_lock_version', $columns)->execute();

        $columns = [
            'id' => 'pk',
            'version' => 'string NOT NULL',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_auto_lock_version_string', $columns)->execute();
    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
        gc_enable();
        gc_collect_cycles();
    }

    // Tests :

    public function testUpdateRecordWithinConsoleRequest()
    {
        ActiveRecordLockVersion::$behaviors = [
            OptimisticLockBehavior::class,
        ];
        $model = new ActiveRecordLockVersion();
        $model->version = 0;
        $model->save(false);

        // upgrade model

        $model->upgrade();

        $this->assertEquals(1, $model->version, 'updated version should equal 1');

        // a console request should use the version number as loaded from database (the behavior should be omitted)

        $model->markAttributeDirty('version');

        $model->save(false);

        $this->assertEquals(2, $model->version, 'updated version should equal 2');
    }


    public function testNewRecord()
    {
        // create a record without any version

        $request = new Request();
        Yii::$app->set('request', $request);

        ActiveRecordLockVersion::$behaviors = [
            OptimisticLockBehavior::class,
        ];
        $model = new ActiveRecordLockVersion();
        $model->save(false);

        $this->assertEquals(0, $model->version, 'init version should equal 0');

        // create a record starting from version 5

        $request->setParsedBody(['version' => 5]);
        Yii::$app->set('request', $request);

        $model = new ActiveRecordLockVersion();
        $model->save(false);

        $this->assertEquals(5, $model->version, 'init version should equal 5');
    }


    public function testUpdateRecord()
    {
        $request = new Request();
        Yii::$app->set('request', $request);

        ActiveRecordLockVersion::$behaviors = [
            OptimisticLockBehavior::class,
        ];
        $model = new ActiveRecordLockVersion();
        $model->save(false);

        // upgrade model

        $model->upgrade();

        $this->assertEquals(1, $model->version, 'updated version should equal 1');

        // save stale data without sending version

        $thrown = false;

        try {
            $model->save(false);
        } catch (\yii\db\StaleObjectException $e) {
            $this->assertContains('The object being updated is outdated.', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown, 'A StaleObjectException exception should have been thrown.');

        // save stale data by sending an outdated version

        $request->setParsedBody(['version' => 0]);
        Yii::$app->set('request', $request);

        $thrown = false;

        try {
            $model->save(false);
        } catch (\yii\db\StaleObjectException $e) {
            $this->assertContains('The object being updated is outdated.', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown, 'A StaleObjectException exception should have been thrown.');

        // save stale data by sending an 'invalid' version number

        $request->setParsedBody(['version' => 'yii']);
        Yii::$app->set('request', $request);

        $thrown = false;

        try {
            $model->save(false);
        } catch (\yii\db\StaleObjectException $e) {
            $this->assertContains('The object being updated is outdated.', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown, 'A StaleObjectException exception should have been thrown.');

        // the behavior should set version to 0 when user input is not a valid number.

        $this->assertEquals(0, $model->version, 'updated version should equal 0');

        // a successful update by sending the correct version

        $request->setParsedBody(['version' => '1']);
        Yii::$app->set('request', $request);

        $model->save(false);

        $this->assertEquals(2, $model->version, 'updated version should equal 2');
    }

     public function testDeleteRecord()
    {
        $request = new Request();
        Yii::$app->set('request', $request);

        ActiveRecordLockVersion::$behaviors = [
            OptimisticLockBehavior::class,
        ];
        $model = new ActiveRecordLockVersion();
        $model->save(false);

        // upgrade model version to 1

        $model->upgrade();

        // delete stale data without sending version

        $thrown = false;

        try {
            $model->delete();
        } catch (\yii\db\StaleObjectException $e) {
            $this->assertContains('The object being deleted is outdated.', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown, 'A StaleObjectException exception should have been thrown.');

        // delete stale data by sending an outdated version

        $request->setParsedBody(['version' => 0]);
        Yii::$app->set('request', $request);

        $thrown = false;

        try {
            $model->delete();
        } catch (\yii\db\StaleObjectException $e) {
            $this->assertContains('The object being deleted is outdated.', $e->getMessage());
            $thrown = true;
        }

        $this->assertTrue($thrown, 'A StaleObjectException exception should have been thrown.');

        // a successful update by sending the correct version

        $request->setParsedBody(['version' => '1']);
        Yii::$app->set('request', $request);

        $model->delete();

        $this->assertEquals(1, $model->version, 'deleted version should remain 1');
    }
}

/**
 * Test Active Record class with [[OptimisticLockBehavior]] behavior attached.
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 */
class ActiveRecordLockVersion extends ActiveRecord
{
    public static $behaviors;
    public static $lockAttribute = 'version';
    public static $tableName = 'test_auto_lock_version';

    public function behaviors()
    {
        return static::$behaviors;
    }

    public function optimisticLock()
    {
        return static::$lockAttribute;
    }

    public static function tableName()
    {
        return static::$tableName;
    }
}
