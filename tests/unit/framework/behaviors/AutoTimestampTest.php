<?php

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
	/**
	 * @var string test table name.
	 */
	protected static $testTableName = 'test_table';
	/**
	 * @var string test Active Record class name.
	 */
	protected static $testActiveRecordClassName;

	public static function setUpBeforeClass()
	{
		if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
			static::markTestSkipped('PDO and SQLite extensions are required.');
		}
		static::$testActiveRecordClassName = get_called_class() . '_TestActiveRecord_' . sha1(uniqid());
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
		Yii::$app->getDb()->createCommand()->createTable(self::$testTableName, $columns)->execute();

		$this->declareTestActiveRecordClass();
	}

	public function tearDown()
	{
		Yii::$app->getDb()->close();
		parent::tearDown();
	}

	/**
	 * Declares test Active Record class with auto timestamp behavior attached.
	 */
	protected function declareTestActiveRecordClass()
	{
		$className = static::$testActiveRecordClassName;
		if (class_exists($className, false)) {
			return true;
		}

		$activeRecordClassName = ActiveRecord::className();
		$behaviorClassName = AutoTimestamp::className();
		$tableName = static::$testTableName;

		$classDefinitionCode = <<<EOL
class {$className} extends {$activeRecordClassName}
{
	public function behaviors()
	{
		return array(
			'timestamp' => array(
				'class' => '{$behaviorClassName}',
				'attributes' => array(
					static::EVENT_BEFORE_INSERT => array('create_time', 'update_time'),
					static::EVENT_BEFORE_UPDATE => 'update_time',
				),
			),
		);
	}

	public static function tableName()
	{
		return '{$tableName}';
	}
}
EOL;
		eval($classDefinitionCode);
		return true;
	}

	// Tests :

	public function testNewRecord()
	{
		$currentTime = time();

		$className = static::$testActiveRecordClassName;
		$model = new $className();
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

		$className = static::$testActiveRecordClassName;
		$model = new $className();
		$model->save(false);

		$enforcedTime = $currentTime - 100;

		$model->create_time = $enforcedTime;
		$model->update_time = $enforcedTime;
		$model->save(false);

		$this->assertEquals($enforcedTime, $model->create_time, 'Create time has been set on update!');
		$this->assertTrue($model->update_time >= $currentTime, 'Update time has NOT been set on update!');
	}
}