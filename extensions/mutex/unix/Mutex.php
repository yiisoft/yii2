<?php

namespace yii\mutex\unix;

use Yii;
use yii\base\InvalidConfigException;

class Mutex extends \yii\mutex\Mutex
{
	private $_files = array();


	public function init()
	{
		if (stripos(php_uname('s'), 'win') === 0) {
			throw new InvalidConfigException('');
		}
	}

	protected function acquire($name, $timeout = 0)
	{
		$file = fopen(Yii::$app->getRuntimePath() . '/mutex.' . md5($name) . '.lock', 'w+');
		if ($file === false) {
			return false;
		}
		$waitTime = 0;
		while (!flock($file, LOCK_EX | LOCK_NB)) {
			$waitTime++;
			if ($waitTime > $timeout) {
				fclose($file);
				return false;
			}
			sleep(1);
		}
		$this->_files[$name] = $file;
		return true;
	}

	protected function release($name)
	{
		if (!isset($this->_files[$name]) || !flock($this->_files[$name], LOCK_UN)) {
			return false;
		} else {
			fclose($this->_files[$name]);
			unset($this->_files[$name]);
			return true;
		}
	}

	protected function getIsAcquired($name)
	{
		return false;
	}

	public function getIsDistributed()
	{
		return false;
	}
}
