<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ExpressionDependency represents a dependency based on the result of a PHP expression.
 *
 * ExpressionDependency will use `eval()` to evaluate the PHP expression.
 * The dependency is reported as unchanged if and only if the result of the expression is
 * the same as the one evaluated when storing the data to cache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExpressionDependency extends Dependency
{
	/**
	 * @var string the PHP expression whose result is used to determine the dependency.
	 */
	public $expression = 'true';

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the result of the PHP expression.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependencyData()
	{
		return eval("return {$this->expression};");
	}
}
