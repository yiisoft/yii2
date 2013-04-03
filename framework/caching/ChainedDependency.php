<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ChainedDependency represents a dependency which is composed of a list of other dependencies.
 *
 * When [[dependOnAll]] is true, if any of the dependencies has changed, this dependency is
 * considered changed; When [[dependOnAll]] is false, if one of the dependencies has NOT changed,
 * this dependency is considered NOT changed.
 *
 * @property boolean $hasChanged Whether the dependency is changed or not.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ChainedDependency extends Dependency
{
	/**
	 * @var Dependency[] list of dependencies that this dependency is composed of.
	 * Each array element must be a dependency object.
	 */
	public $dependencies;
	/**
	 * @var boolean whether this dependency is depending on every dependency in [[dependencies]].
	 * Defaults to true, meaning if any of the dependencies has changed, this dependency is considered changed.
	 * When it is set false, it means if one of the dependencies has NOT changed, this dependency
	 * is considered NOT changed.
	 */
	public $dependOnAll = true;

	/**
	 * Constructor.
	 * @param Dependency[] $dependencies list of dependencies that this dependency is composed of.
	 * Each array element should be a dependency object.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($dependencies = array(), $config = array())
	{
		$this->dependencies = $dependencies;
		parent::__construct($config);
	}

	/**
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 */
	public function evaluateDependency()
	{
		foreach ($this->dependencies as $dependency) {
			$dependency->evaluateDependency();
		}
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method does nothing in this class.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependencyData()
	{
		return null;
	}

	/**
	 * Performs the actual dependency checking.
	 * This method returns true if any of the dependency objects
	 * reports a dependency change.
	 * @return boolean whether the dependency is changed or not.
	 */
	public function getHasChanged()
	{
		foreach ($this->dependencies as $dependency) {
			if ($this->dependOnAll && $dependency->getHasChanged()) {
				return true;
			} elseif (!$this->dependOnAll && !$dependency->getHasChanged()) {
				return false;
			}
		}
		return !$this->dependOnAll;
	}
}
