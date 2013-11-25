<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use \yii\base\Component;

/**
 * Class Command
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Command extends Component
{
	/**
	 * @var Connection the Mongo connection that this command is associated with
	 */
	public $db;

	/**
	 * Drop the current database
	 */
	public function dropDb()
	{
		$this->db->db->drop();
	}
}