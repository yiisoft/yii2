<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\Object;
use Yii;

/**
 * Database represents the Mongo database information.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Database extends Object
{
	/**
	 * @var \MongoDB Mongo database instance.
	 */
	public $mongoDb;
	/**
	 * @var Collection[] list of collections.
	 */
	private $_collections = [];

	/**
	 * Returns the Mongo collection with the given name.
	 * @param string $name collection name
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return Collection mongo collection instance.
	 */
	public function getCollection($name, $refresh = false)
	{
		if ($refresh || !array_key_exists($name, $this->_collections)) {
			$this->_collections[$name] = $this->selectCollection($name);
		}
		return $this->_collections[$name];
	}

	/**
	 * Selects collection with given name.
	 * @param string $name collection name.
	 * @return Collection collection instance.
	 */
	protected function selectCollection($name)
	{
		return Yii::createObject([
			'class' => 'yii\mongo\Collection',
			'mongoCollection' => $this->mongoDb->selectCollection($name)
		]);
	}

	/**
	 * Drops this database.
	 */
	public function drop()
	{
		$this->mongoDb->drop();
	}
}