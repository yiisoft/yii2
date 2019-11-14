<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Yii;
use yii\base\InvalidConfigException;

/**
 * FileDependency 是基于文件的最后更新时间实现的依赖类。
 *
 * 如果由 [[fileName]] 指明的文件最后更新时间发生了变化，
 * 这个依赖就被认为是发生了变化。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileDependency extends Dependency
{
    /**
     * @var string 文件路径或者 [path alias](guide:concept-aliases) 别名，检测依赖是否发生变化时，
     * 会使用文件的最后更新时间。
     */
    public $fileName;


    /**
     * 生成在判断依赖是否发生变化时用到的依赖数据。
     * 该方法返回文件的最后更新时间。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return mixed 判断依赖是否发生变化时的依赖数据。
     * @throws InvalidConfigException 如果 [[fileName]] 为空时。
     */
    protected function generateDependencyData($cache)
    {
        if ($this->fileName === null) {
            throw new InvalidConfigException('FileDependency::fileName must be set');
        }

        $fileName = Yii::getAlias($this->fileName);
        clearstatcache(false, $fileName);
        return @filemtime($fileName);
    }
}
