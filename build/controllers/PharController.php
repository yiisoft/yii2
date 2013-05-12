<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\console\Controller;

/**
 * Support iterator class for PHAR package builder.
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class YiiIterator implements Iterator
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
			$relative = substr($file, $this->_basePathLength);
			if (is_dir($file)) {
				$this->_scan($file);
			} else {
				$this->_files[] = array($relative, $file);
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
 * Yii PHAR package generator/builder.
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class PharController extends Controller
{
	public function actionIndex()
	{
		echo "Building PHAR package...\n";

		$pharPath = realpath(__DIR__ . '/../../yii') . '/yii.phar';
		if (is_file($pharPath)) {
			unlink($pharPath);
		}

		$phar = new Phar($pharPath, 0, 'yii');
		$phar->buildFromIterator(new YiiIterator(__DIR__ . '/../../yii/'));

		echo "Done!\n";
	}
}
