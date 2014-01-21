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
 * DbFixture is the base class for DB-related fixtures.
 *
 * DbFixture provides the [[db]] connection to be used by DB fixtures.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class DbFixture extends Fixture
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbFixture object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!is_object($this->db)) {
			throw new InvalidConfigException("The 'db' property must be either a DB connection instance or the application component ID of a DB connection.");
		}
	}
}
