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
 *
 * @group behaviors
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
			'created_at' => 'integer',
			'updated_at' => 'integer',
		];
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

		$this->assertTrue($model->created_at >= $currentTime);
		$this->assertTrue($model->updated_at >= $currentTime);
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

		$model->created_at = $enforcedTime;
		$model->updated_at = $enforcedTime;
		$model->save(false);

		$this->assertEquals($enforcedTime, $model->created_at, 'Create time has been set on update!');
		$this->assertTrue($model->updated_at >= $currentTime, 'Update time has NOT been set on update!');
	}
}

/**
 * Test Active Record class with [[AutoTimestamp]] behavior attached.
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 */
class ActiveRecordAutoTimestamp extends ActiveRecord
{
	public function behaviors()
	{
		return [
			'timestamp' => [
				'class' => AutoTimestamp::className(),
				'attributes' => [
					static::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
					static::EVENT_BEFORE_UPDATE => 'updated_at',
				],
			],
		];
	}

	public static function tableName()
	{
		return 'test_auto_timestamp';
	}
}
