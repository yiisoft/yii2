<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ExpressionDependency 是基于 PHP 表达式的结果实现的依赖类。
 *
 * ExpressionDependency 将会使用 `eval()` 函数解析 PHP 表达式。
 * 这个依赖只有在和存入缓存数据时解析的表达式结果一样时，
 * 会报告无变化。
 *
 * 一个 PHP 表达式可以是任何产生值的 PHP 代码。想了解更多表达式是什么， 
 * 请参考 [php manual](http://www.php.net/manual/en/language.expressions.php) 手册。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExpressionDependency extends Dependency
{
    /**
     * @var string PHP 表达式的字符串表示，它的结果用来判断依赖是否发生变化。
     * 一个 PHP 表达式可以是任何产生值的 PHP 代码。想了解更多表达式是什么， 
     * 请参考 [php manual](http://www.php.net/manual/en/language.expressions.php) 手册。
     */
    public $expression = 'true';
    /**
     * @var mixed 有关这个依赖的自定义参数。
     * 你可以在 [[expression]] 里用 `$this->params` 获得该属性的值。
     */
    public $params;


    /**
     * 生成在判断依赖是否发生变化时用到的依赖数据。
     * 该方法返回 PHP 表达式的结果。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return mixed 判断依赖是否发生变化时用到的依赖数据。
     */
    protected function generateDependencyData($cache)
    {
        return eval("return {$this->expression};");
    }
}
