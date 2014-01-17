<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * DbFixture represents the fixture needed for setting up a DB connection.
 *
 * Its main task is to toggle integrity check of the database during data loading.
 * This is needed by other DB-related fixtures (e.g. [[ActiveFixture]]) so that they can populate
 * data into the database without triggering integrity check errors.
 *
 * Besides, DbFixture also attempts to load an [[initScript|initialization script]] if it exists.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbFixture extends Fixture
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbFixture object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';
	/**
	 * @var string the init script file that should be executed when loading this fixture.
	 * This should be either a file path or path alias. Note that if the file does not exist,
	 * no error will be raised.
	 */
	public $initScript = '@app/tests/fixtures/init.php';
	/**
	 * @var array list of database schemas that the test tables may reside in. Defaults to
	 * [''], meaning using the default schema (an empty string refers to the
	 * default schema). This property is mainly used when turning on and off integrity checks
	 * so that fixture data can be populated into the database without causing problem.
	 */
	public $schemas = [''];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("The 'db' property must be either a DB connection instance or the application component ID of a DB connection.");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function beforeLoad()
	{
		$this->checkIntegrity(false);
	}

	/**
	 * @inheritdoc
	 */
	public function afterLoad()
	{
		$this->checkIntegrity(true);
	}

	/**
	 * @inheritdoc
	 */
	public function load()
	{
		$file = Yii::getAlias($this->initScript);
		if (is_file($file)) {
			require($file);
		}
	}

	/**
	 * Enables or disables database integrity check.
	 * This method may be used to temporarily turn off foreign constraints check.
	 * @param boolean $check whether to enable database integrity check
	 */
	public function checkIntegrity($check)
	{
		foreach ($this->schemas as $schema) {
			$this->db->createCommand()->checkIntegrity($check, $schema)->execute();
		}
	}
}
