<?php
/**
 * CChainedCacheDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CChainedCacheDependency represents a list of cache dependencies.
 *
 * If any of the dependencies reports a dependency change, CChainedCacheDependency
 * will return true for the checking.
 *
 * To add dependencies to CChainedCacheDependency, use {@link getDependencies Dependencies}
 * which gives a {@link CTypedList} instance and can be used like an array
 * (see {@link CList} for more details}).
 *
 * @property CTypedList $dependencies List of dependency objects.
 * @property boolean $hasChanged Whether the dependency is changed or not.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching.dependencies
 * @since 1.0
 */
class CChainedCacheDependency extends CComponent implements ICacheDependency
{
	private $_dependencies=null;

	/**
	 * Constructor.
	 * @param array $dependencies the dependencies to be added to this chain.
	 * @since 1.1.4
	 */
	public function __construct($dependencies=array())
	{
		if(!empty($dependencies))
			$this->setDependencies($dependencies);
	}

	/**
	 * @return CTypedList list of dependency objects
	 */
	public function getDependencies()
	{
		if($this->_dependencies===null)
			$this->_dependencies=new CTypedList('ICacheDependency');
		return $this->_dependencies;
	}

	/**
	 * @param array $values list of dependency objects or configurations to be added to this chain.
	 * If a depedency is specified as a configuration, it must be an array that can be recognized
	 * by {@link YiiBase::createComponent}.
	 */
	public function setDependencies($values)
	{
		$dependencies=$this->getDependencies();
		foreach($values as $value)
		{
			if(is_array($value))
				$value=Yii::createComponent($value);
			$dependencies->add($value);
		}
	}

	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 */
	public function evaluateDependency()
	{
		if($this->_dependencies!==null)
		{
			foreach($this->_dependencies as $dependency)
				$dependency->evaluateDependency();
		}
	}

	/**
	 * Performs the actual dependency checking.
	 * This method returns true if any of the dependency objects
	 * reports a dependency change.
	 * @return boolean whether the dependency is changed or not.
	 */
	public function getHasChanged()
	{
		if($this->_dependencies!==null)
		{
			foreach($this->_dependencies as $dependency)
				if($dependency->getHasChanged())
					return true;
		}
		return false;
	}
}
