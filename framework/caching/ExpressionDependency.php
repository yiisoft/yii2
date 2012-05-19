<?php
/**
 * CExpressionDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CExpressionDependency represents a dependency based on the result of a PHP expression.
 *
 * CExpressionDependency performs dependency checking based on the
 * result of a PHP {@link expression}.
 * The dependency is reported as unchanged if and only if the result is
 * the same as the one evaluated when storing the data to cache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching.dependencies
 * @since 1.0
 */
class CExpressionDependency extends CCacheDependency
{
	/**
	 * @var string the PHP expression whose result is used to determine the dependency.
	 * The expression can also be a valid PHP callback,
	 * including class method name (array(ClassName/Object, MethodName)),
	 * or anonymous function (PHP 5.3.0+). The function/method will be passed with a
	 * parameter which is the dependency object itself.
	 */
	public $expression;

	/**
	 * Constructor.
	 * @param string $expression the PHP expression whose result is used to determine the dependency.
	 */
	public function __construct($expression='true')
	{
		$this->expression=$expression;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the result of the PHP expression.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependentData()
	{
		return $this->evaluateExpression($this->expression);
	}
}
