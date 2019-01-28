<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ChainedDependency 表示由一系列其它依赖类组成的组合依赖类。
 *
 * 当 [[dependOnAll]] 是 true 时，如果依赖组合里任何一个依赖发生了变化，那么该依赖
 * 就被认为是发生了变化；当 [[dependOnAll]] 是 false 时，如果依赖组合里任何其中一个依赖没有发生变化，
 * 那么该依赖就被认为是没有发生变化。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ChainedDependency extends Dependency
{
    /**
     * @var Dependency[] 该组合依赖类包含的其它依赖列表。
     * 每个数组元素必须是一个依赖对象。
     */
    public $dependencies = [];
    /**
     * @var bool 是否当前组合依赖对象依赖在 [[dependencies]] 里的每个依赖对象。
     * 默认是 true，这表明任何一个依赖发生变化，组合依赖对象就被认为是发生了变化。
     * 当设置为 false 时，这表明依赖里任何其中一个依赖对象没有发生变化，
     * 组合依赖对象就被认为是没有发生变化。
     */
    public $dependOnAll = true;


    /**
     * 通过生成和保存依赖相关的数据来计算依赖。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     */
    public function evaluateDependency($cache)
    {
        foreach ($this->dependencies as $dependency) {
            $dependency->evaluateDependency($cache);
        }
    }

    /**
     * 生成在判断依赖是否发生变化时用到的依赖数据。
     * 该方法在当前类里什么也不需要做。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return mixed 判断依赖是否发生变化时用到的依赖数据。
     */
    protected function generateDependencyData($cache)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isChanged($cache)
    {
        foreach ($this->dependencies as $dependency) {
            if ($this->dependOnAll && $dependency->isChanged($cache)) {
                return true;
            } elseif (!$this->dependOnAll && !$dependency->isChanged($cache)) {
                return false;
            }
        }

        return !$this->dependOnAll;
    }
}
