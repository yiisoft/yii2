<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use yii\base\InvalidConfigException;
use yii\db\TableSchema;

/**
 * Class RecordSchema defines the data schema for a redis active record
 *
 * As there is no schema in a redis DB this class is used to define one.
 *
 * @package yii\db\redis
 */
class RecordSchema extends TableSchema
{
	/**
	 * @var string[] column names.
	 */
	public $columns = array();

	/**
	 * @return string the column type
	 */
	public function getColumn($name)
	{
		parent::getColumn($name);
	}

	public function init()
	{
		if (empty($this->name)) {
			throw new InvalidConfigException('name of RecordSchema must not be empty.');
		}
		if (empty($this->primaryKey)) {
			throw new InvalidConfigException('primaryKey of RecordSchema must not be empty.');
		}
		if (!is_array($this->primaryKey)) {
			$this->primaryKey = array($this->primaryKey);
		}
		foreach($this->primaryKey as $pk) {
			if (!isset($this->columns[$pk])) {
				throw new InvalidConfigException('primaryKey '.$pk.' is not a colum of RecordSchema.');
			}
		}
	}
}