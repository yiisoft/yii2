<?php

namespace yii\mutex\db\mysql;

use Yii;
use yii\base\InvalidConfigException;

class Mutex extends \yii\mutex\db\Mutex
{
	public function init()
	{
		parent::init();
		if ($this->db->driverName !== 'mysql') {
			throw new InvalidConfigException('');
		}
	}

	protected function acquire($name, $timeout = 0)
	{
		return (boolean)$this->db
			->createCommand('SELECT GET_LOCK(:name, :timeout)', array(':name' => $name, ':timeout' => $timeout))
			->queryScalar();
	}

	protected function release($name)
	{
		return (boolean)$this->db
			->createCommand('SELECT RELEASE_LOCK(:name)', array(':name' => $name))
			->queryScalar();
	}

	protected function getIsAcquired($name)
	{
		return (boolean)$this->db
			->createCommand('SELECT IS_FREE_LOCK(:name)', array(':name' => $name))
			->queryScalar();
	}
}
