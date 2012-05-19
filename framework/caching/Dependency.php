<?php
/**
 * CCacheDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCacheDependency is the base class for cache dependency classes.
 *
 * CCacheDependency implements the {@link ICacheDependency} interface.
 * Child classes should override its {@link generateDependentData} for
 * actual dependency checking.
 *
 * @property boolean $hasChanged Whether the dependency has changed.
 * @property mixed $dependentData The data used to determine if dependency has been changed.
 * This data is available after {@link evaluateDependency} is called.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching.dependencies
 * @since 1.0
 */
class CCacheDependency extends CComponent implements ICacheDependency
{
	/**
	 * @var boolean Whether this dependency is reusable or not.
	 * If set to true, dependent data for this cache dependency will only be generated once per request.
	 * You can then use the same cache dependency for multiple separate cache calls on the same page
	 * without the overhead of re-evaluating the dependency each time.
	 * Defaults to false;
	 * @since 1.1.11
	 */
	public $reuseDependentData=false;

	/**
	 * @var array cached data for reusable dependencies.
	 * @since 1.1.11
	 */
	private static $_reusableData=array();

	private $_hash;
	private $_data;

	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 * This method is invoked by cache before writing data into it.
	 */
	public function evaluateDependency()
	{
		if ($this->reuseDependentData)
		{
			$hash=$this->getHash();
			if (!isset(self::$_reusableData[$hash]['dependentData']))
				self::$_reusableData[$hash]['dependentData']=$this->generateDependentData();
			$this->_data=self::$_reusableData[$hash]['dependentData'];
		}
		else
			$this->_data=$this->generateDependentData();
	}

	/**
	 * @return boolean whether the dependency has changed.
	 */
	public function getHasChanged()
	{
		if ($this->reuseDependentData)
		{
			$hash=$this->getHash();
			if (!isset(self::$_reusableData[$hash]['hasChanged']))
			{
				if (!isset(self::$_reusableData[$hash]['dependentData']))
					self::$_reusableData[$hash]['dependentData']=$this->generateDependentData();
				self::$_reusableData[$hash]['hasChanged']=self::$_reusableData[$hash]['dependentData']!=$this->_data;
			}
			return self::$_reusableData[$hash]['hasChanged'];
		}
		else
			return $this->generateDependentData()!=$this->_data;
	}

	/**
	 * @return mixed the data used to determine if dependency has been changed.
	 * This data is available after {@link evaluateDependency} is called.
	 */
	public function getDependentData()
	{
		return $this->_data;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * Derived classes should override this method to generate actual dependent data.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependentData()
	{
		return null;
	}
	/**
	 * Generates a unique hash that identifies this cache dependency.
	 * @return string the hash for this cache dependency
	 */
	private function getHash()
	{
		if($this->_hash===null)
			$this->_hash=sha1(serialize($this));
		return $this->_hash;
	}
}