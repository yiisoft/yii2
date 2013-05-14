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
	 * @var string[]
	 */
	private $_files = array();

	public function __construct()
	{
		$this->_scan(YII_PATH);
	}

	private function _scan($path)
	{
		foreach (glob($path . '/*') as $file) {
			if (is_dir($file)) {
				$this->_scan($file);
			} else {
				$this->_files[] = array(substr($file, strlen(YII_PATH)), $file);
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
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class PharController extends Controller
{
	/**
	 * Build PHAR version of the Yii.
	 *
	 * This command is intended to build Yii Framework PHAR package.
	 * Every file of the framework will be packed into a single universal
	 * archive and could be easily distributed with the end user's
	 * web application as a single file.
	 *
	 * Such way of middleware distribution is very comfortable to maintain
	 * and incidentally improves application performance characteristics
	 * when used alongside opcode caching.
	 *
	 * After running this command you shall see generated PHAR file
	 * in the main Yii directory. Standard PHAR file name is `yii.phar`.
	 * Existing old PHAR file would be silently removed.
	 */
	public function actionIndex()
	{
		echo "Building PHAR package...\n";

		if (is_file(YII_PATH . '/yii.phar')) {
			unlink(YII_PATH . '/yii.phar');
		}

		$phar = new Phar(YII_PATH . '/yii.phar', 0, 'yii');
		$phar->buildFromIterator(new YiiIterator());

		echo "Done!\n";
	}
}
