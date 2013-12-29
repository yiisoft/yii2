<?php
/**
 * Simple file system wrapper for twig to process twig files
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\twig;


/**
 * Twig view file loader class.
 *
 * @author dev-mraj <dev.meghraj@gmail.com>
 * @version 1.0.0
 */
class TwigSimpleFileLoader implements \Twig_LoaderInterface {

	/**
	 * @var string Path to directory
	 */
	private $_dir;

	/*
	 * @param @dir string path to directory
	 */
	public function __construct($dir)
	{
		$this->_dir=$dir;
	}

	/**
	 * Compare a file's freshness with previously stored timestamp
	 *
	 * @param $name string file name to check
	 * @param $time int timestamp to compare with
	 * @return bool true if file is still fresh and not changes, false otherwise
	 */
	public function isFresh($name, $time)
	{
		return filemtime($this->getFilePath($name))<=$time;
	}

	/**
	 * get the source of given file name
	 *
	 * @param $name string file name
	 * @return string contents of given file name
	 */
	public function getSource($name)
	{
		return file_get_contents($this->getFilePath($name));
	}

	/**
	 * get a unique key that can represent this file uniquely among other files.
	 * @param $name
	 * @return string
	 */
	public function getCacheKey($name)
	{
		return $this->getFilePath($name);
	}

	/**
	 * internally used to get absolute path of given file name
	 * @param $name string file name
	 * @return string absolute path of file
	 */
	protected  function getFilePath($name){
		return $this->_dir.'/'.$name;
	}

}