<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * Dependency is the base class for cache dependency classes.
 *
 * Child classes should override its [[generateDependencyData()]] for generating
 * the actual dependency data.
 *
 * @property boolean $hasChanged Whether the dependency has changed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Dependency extends \yii\base\Object
{
	/**
	 * @var mixed the dependency data that is saved in cache and later is compared with the
	 * latest dependency data.
	 */
	public $data;
	/**
	 * @var boolean whether this dependency is reusable or not. True value means that dependent
	 * data for this cache dependency will only be generated once per request. This allows you
	 * to use the same cache dependency for multiple separate cache calls while generating the same
	 * page without an overhead of re-evaluating dependency data each time. Defaults to false.
	 */
	public $reuseData = false;

	/**
	 * @var array static storage of cached data for reusable dependencies.
	 */
	private static $_reusableData = array();
	/**
	 * @var string a unique hash value for this cache dependency.
	 */
	private $_hash;

	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 * This method is invoked by cache before writing data into it.
	 */
	public function evaluateDependency()
	{
		if (!$this->reuseData) {
			$this->data = $this->generateDependencyData();
		} else {
			if ($this->_hash === null) {
				$this->_hash = sha1(serialize($this));
			}
			if (!array_key_exists($this->_hash, self::$_reusableData)) {
				self::$_reusableData[$this->_hash] = $this->generateDependencyData();
			}
			$this->data = self::$_reusableData[$this->_hash];
		}
	}

	/**
	 * @return boolean whether the dependency has changed.
	 */
	public function getHasChanged()
	{
		if (!$this->reuseData) {
			return $this->generateDependencyData() !== $this->data;
		} else {
			if ($this->_hash === null) {
				$this->_hash = sha1(serialize($this));
			}
			if (!array_key_exists($this->_hash, self::$_reusableData)) {
				self::$_reusableData[$this->_hash] = $this->generateDependencyData();
			}
			return self::$_reusableData[$this->_hash] !== $this->_data;
		}
	}

	/**
	 * Resets all cached data for reusable dependencies.
	 */
	public static function resetReusableData()
	{
		self::$_reusableData = array();
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * Derived classes should override this method to generate the actual dependency data.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	abstract protected function generateDependencyData();
}
