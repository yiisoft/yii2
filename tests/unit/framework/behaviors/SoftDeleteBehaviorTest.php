<?php

namespace yiiunit\framework\behaviors;

use Yii;
use yiiunit\TestCase;
use yii\db\Connection;
use yii\db\ActiveRecord;
use yii\behaviors\SoftDeleteBehavior;

/**
 * Unit test for [[\yii\behaviors\SoftDeleteBehavior]]
 * @see SoftDeleteBehavior
 * 
 * @group behaviors
 */
class SoftDeleteBehaviorTest extends TestCase
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
            'deleted' => 'tinyint(1)',
            'deleted_at' => 'integer',
        ];

        Yii::$app->getDb()->createCommand()->createTable('test_soft_delete', $columns)->execute();

        $model = new ActiveRecordSoftDelete();
        $model->deleted = $model->deleted_at = 0;
        $model->save();
    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
    }

    // Tests :

    public function testSoftDelete()
    {
        $model = ActiveRecordSoftDelete::find()->one();
        $model->delete();

        $this->assertEquals($model->deleted, 1);
        $this->assertTrue($model->deleted_at >= time());
    }
    
    public function testHardDelete()
    {
        $model = ActiveRecordSoftDelete::find()->one();
        
        $deleted = $model->deleteHard();
        
        $this->assertEquals($deleted, 1);
        $this->assertNull($model->id);
    }

}

class ActiveRecordSoftDelete extends ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => SoftDeleteBehavior::className()
            ]
        ];
    }

    public static function tableName()
    {
        return 'test_soft_delete';
    }

}
