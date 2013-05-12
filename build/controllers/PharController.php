<?php

use \yii\console\Controller;

/**
 *
 */
class Yii2Iterator implements Iterator
{
	/**
	 * @var integer
	 */
	private $_index = 0;
	/**
	 * @var integer
	 */
	private $_basePathLength = 0;
	/**
	 * @var string[]
	 */
	private $_files = array();

	public function __construct($path)
	{
		$basePath = realpath($path);
		$this->_basePathLength = strlen($basePath);
		$this->_scan($basePath);
	}

	private function _scan($path)
	{
		foreach (glob($path . '/*') as $file) {
			if (is_dir($file)) {
				$this->_scan($file);
			} else {
				$this->_files[] = array(substr($file, $this->_basePathLength), $file);
			}
		}
	}

	public function current()
	{
		return $this->_files[$this->_index][1];
	}

	public function next()
	{
		$this->_index++;
	}

	public function key()
	{
		return $this->_files[$this->_index][0];
	}

	public function valid()
	{
 		return isset($this->_files[$this->_index]);
	}

	public function rewind()
	{
		$this->_index = 0;
	}
}

/**
 *
 */
class PharController extends Controller
{
	/**
	 *
	 */
	public function actionIndex()
	{
		$pharPath = realpath(__DIR__ . '/../../yii') . '/yii.phar';
		if (is_file($pharPath)) {
			unlink($pharPath);
		}

		$phar = new Phar($pharPath, 0, 'yii');
		$phar->buildFromIterator(new Yii2Iterator(__DIR__ . '/../../yii/'));
	}
}
