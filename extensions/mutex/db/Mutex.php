<?php

namespace yii\mutex\db;

use Yii;
use yii\db\Connection;
use yii\base\InvalidConfigException;

abstract class Mutex extends \yii\mutex\Mutex
{
	/**
	 * @var string|Connection
	 */
	public $db = 'db';

	public function init()
	{
		parent::init();
		$this->db = Yii::$app->getComponent($this->db);
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException('');
		}
	}

	public function getIsDistributed()
	{
		return true;
	}
}
