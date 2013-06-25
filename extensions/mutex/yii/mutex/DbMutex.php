<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Yii;
use yii\db\Connection;
use yii\base\InvalidConfigException;

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
abstract class DbMutex extends Mutex
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the Mutex object is created, if you want to change this property, you should only assign
	 * it with a DB connection object.
	 */
	public $db = 'db';

	/**
	 * Initializes generic database table based mutex implementation.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException('Mutex::db must be either a DB connection instance or the application component ID of a DB connection.');
		}
	}
}
