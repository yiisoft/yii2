<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\Object;

/**
 * Class QueryBuilder
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends Object
{
	/**
	 * @var Connection the Mongo connection.
	 */
	public $db;

	/**
	 * Constructor.
	 * @param Connection $connection the Mongo connection.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($connection, $config = [])
	{
		$this->db = $connection;
		parent::__construct($config);
	}

	// TODO
}