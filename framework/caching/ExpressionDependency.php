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
 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
 * please refer to the [php manual](https://secure.php.net/manual/en/language.expressions.php).
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExpressionDependency extends Dependency
{
    /**
     * @var string the string representation of a PHP expression whose result is used to determine the dependency.
     * A PHP expression can be any PHP code that evaluates to a value. To learn more about what an expression is,
     * please refer to the [php manual](https://secure.php.net/manual/en/language.expressions.php).
     */
    public $expression = 'true';
    /**
     * @var mixed custom parameters associated with this dependency. You may get the value
     * of this property in [[expression]] using `$this->params`.
     */
    public $params;


    /**
     * Generates the data needed to determine if dependency has been changed.
     * This method returns the result of the PHP expression.
     * @param CacheInterface $cache the cache component that is currently evaluating this dependency
     * @return mixed the data needed to determine if dependency has been changed.
     */
    protected function generateDependencyData($cache)
    {
        return eval("return {$this->expression};");
    }
}
