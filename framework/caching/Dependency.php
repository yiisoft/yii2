<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * Dependency 是缓存依赖类的基类。
 *
 * 子类应该覆盖它的 [[generateDependencyData()]] 方法，
 * 用来产生实际具体的依赖数据。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Dependency extends \yii\base\BaseObject
{
    /**
     * @var mixed 存储在缓存中的依赖数据，
     * 将来会取出来和最新的依赖数据进行比较。
     */
    public $data;
    /**
     * @var bool 依赖是否重用。True 表示当前依赖对象的依赖数据只在每个请求里生成一次。
     * 这允许你在多个单独的缓存调用中使用相同的缓存依赖对象，
     * 这样就会生成相同的数据页但是减少了每次生成缓存数据的开销。
     * 默认是 false。
     */
    public $reusable = false;

    /**
     * @var array static 为重用依赖配置的缓存数组。
     */
    private static $_reusableData = [];


    /**
     * 通过生成和保存依赖相关的数据来计算依赖。
     * 该方法会在缓存对象把要缓存的数据写入缓存之前调用。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件
     */
    public function evaluateDependency($cache)
    {
        if ($this->reusable) {
            $hash = $this->generateReusableHash();
            if (!array_key_exists($hash, self::$_reusableData)) {
                self::$_reusableData[$hash] = $this->generateDependencyData($cache);
            }
            $this->data = self::$_reusableData[$hash];
        } else {
            $this->data = $this->generateDependencyData($cache);
        }
    }

    /**
     * 返回表明依赖是否发生变化的值。
     * @deprecated 从 2.0.11 版本可用，将要从 2.1 版本移除。请用 [[isChanged()]] 方法代替。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return bool 当前依赖是否发生了变化。
     */
    public function getHasChanged($cache)
    {
        return $this->isChanged($cache);
    }

    /**
     * 检测依赖是否发生了变化。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return bool 当前依赖是否发生了变化。
     * @since 2.0.11
     */
    public function isChanged($cache)
    {
        if ($this->reusable) {
            $hash = $this->generateReusableHash();
            if (!array_key_exists($hash, self::$_reusableData)) {
                self::$_reusableData[$hash] = $this->generateDependencyData($cache);
            }
            $data = self::$_reusableData[$hash];
        } else {
            $data = $this->generateDependencyData($cache);
        }

        return $data !== $this->data;
    }

    /**
     * 重置所有重用依赖的缓存数据。
     */
    public static function resetReusableData()
    {
        self::$_reusableData = [];
    }

    /**
     * 生成一个唯一的散列，它可以用来接收可重用的缓存数据。
     * @return string 与缓存数据对应的唯一散列值。
     * @see reusable
     */
    protected function generateReusableHash()
    {
        $data = $this->data;
        $this->data = null;  // https://github.com/yiisoft/yii2/issues/3052
        $key = sha1(serialize($this));
        $this->data = $data;
        return $key;
    }

    /**
     * 生成在判断依赖是否发生变化时用到的依赖数据。
     * 依赖驱动类应该覆盖该方法生成实际的依赖数据。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return mixed 判断依赖是否发生变化时用到的依赖数据。
     */
    abstract protected function generateDependencyData($cache);
}
