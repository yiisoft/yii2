<?php

namespace yiiunit\framework\behaviors;

use Yii;
use yiiunit\TestCase;
use yii\db\Connection;
use yii\db\ActiveRecord;
use yii\behaviors\AutoTimestamp;

/**
 * Unit test for [[\yii\behaviors\AutoTimestamp]].
 * @see AutoTimestamp
 */
class AutoTimestampTest extends TestCase
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

	public function setUp() {
		$this->mockApplication(
			array(
				'components' => array(
					'db' => array(
						'class' => '\yii\db\Connection',
						'dsn' => 'sqlite::memory:',
					)
				)
			)
		);

		$columns = array(
			'id' => 'pk',
			'create_time' => 'integer',
			'update_time' => 'integer',
		);
		Yii::$app->getDb()->createCommand()->createTable('test_auto_timestamp', $columns)->execute();
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

		$model = new ActiveRecordAutoTimestamp();
		$model->save(false);

		$this->assertTrue($model->create_time >= $currentTime);
		$this->assertTrue($model->update_time >= $currentTime);
	}

	/**
	 * @depends testNewRecord
	 */
	public function testUpdateRecord()
	{
		$currentTime = time();

		$model = new ActiveRecordAutoTimestamp();
		$model->save(false);

		$enforcedTime = $currentTime - 100;

		$model->create_time = $enforcedTime;
		$model->update_time = $enforcedTime;
		$model->save(false);

		$this->assertEquals($enforcedTime, $model->create_time, 'Create time has been set on update!');
		$this->assertTrue($model->update_time >= $currentTime, 'Update time has NOT been set on update!');
	}
}

/**
 * Test Active Record class with [[AutoTimestamp]] behavior attached.
 *
 * @property integer $id
 * @property integer $create_time
 * @property integer $update_time
 */
class ActiveRecordAutoTimestamp extends ActiveRecord
{
	public function behaviors()
	{
		return array(
			'timestamp' => array(
				'class' => AutoTimestamp::className(),
				'attributes' => array(
					static::EVENT_BEFORE_INSERT => array('create_time', 'update_time'),
					static::EVENT_BEFORE_UPDATE => 'update_time',
				),
			),
		);
	}

	public static function tableName()
	{
		return 'test_auto_timestamp';
	}
}