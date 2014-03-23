<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\InvalidConfigException;

/**
 * GroupDependency marks a cached data item with a group name.
 *
 * You may invalidate the cached data items with the same group name all at once
 * by calling [[invalidate()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GroupDependency extends Dependency
{
    /**
     * @var string the group name. This property must be set.
     */
    public $group;

    /**
     * Generates the data needed to determine if dependency has been changed.
     * This method does nothing in this class.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return mixed the data needed to determine if dependency has been changed.
     * @throws InvalidConfigException if [[group]] is not set.
     */
    protected function generateDependencyData($cache)
    {
        if ($this->group === null) {
            throw new InvalidConfigException('GroupDependency::group must be set');
        }
        $version = $cache->get([__CLASS__, $this->group]);
        if ($version === false) {
            $version = $this->invalidate($cache, $this->group);
        }

        return $version;
    }

    /**
     * Performs the actual dependency checking.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return boolean whether the dependency is changed or not.
     * @throws InvalidConfigException if [[group]] is not set.
     */
    public function getHasChanged($cache)
    {
        if ($this->group === null) {
            throw new InvalidConfigException('GroupDependency::group must be set');
        }
        $version = $cache->get([__CLASS__, $this->group]);

        return $version === false || $version !== $this->data;
    }

    /**
     * Invalidates all of the cached data items that have the same [[group]].
     * @param Cache $cache the cache component that caches the data items
     * @param string $group the group name
     * @return string the current version number
     */
    public static function invalidate($cache, $group)
    {
        $version = microtime();
        $cache->set([__CLASS__, $group], $version);

        return $version;
    }
}
