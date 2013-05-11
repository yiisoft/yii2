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
	 * Evaluates the dependency by generating and saving the data related with dependency.
	 * This method is invoked by cache before writing data into it.
	 */
	public function evaluateDependency()
	{
		$this->data = $this->generateDependencyData();
	}

	/**
	 * @return boolean whether the dependency has changed.
	 */
	public function getHasChanged()
	{
		return $this->generateDependencyData() !== $this->data;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * Derived classes should override this method to generate the actual dependency data.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	abstract protected function generateDependencyData();
}
