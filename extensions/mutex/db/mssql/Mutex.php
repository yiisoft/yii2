<?php

namespace yii\mutex\db\mssql;

use Yii;
use yii\base\InvalidConfigException;

class Mutex extends \yii\mutex\db\Mutex
{
	public function init()
	{
		parent::init();
		$driverName = $this->db->driverName;
		if ($driverName !== 'sqlsrv' && $driverName !== 'dblib' && $driverName !== 'mssql') {
			throw new InvalidConfigException('');
		}
	}

	protected function acquire($name, $timeout = 0)
	{
		// http://msdn.microsoft.com/en-us/library/ms189823.aspx
	}

	protected function release($name)
	{
		// http://msdn.microsoft.com/en-us/library/ms178602.aspx
	}
}
