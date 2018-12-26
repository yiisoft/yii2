<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ViewFinderTrait 实现了 getViewNames 方法，用于在数据库中查找视图。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Bob Olde Hampsink <b.oldehampsink@nerds.company>
 * @since 2.0.12
 */
trait ViewFinderTrait
{
    /**
     * @var array 数据库中所有视图名称的列表
     */
    private $_viewNames = [];

    /**
     * 返回数据库中的所有视图名称。
     * @param string $schema 视图的结构。默认为空字符串，表示当前或默认结构。
     * @return array 数据库中的所有视图名称。名称前面没有模式名称的前缀。
     */
    abstract protected function findViewNames($schema = '');

    /**
     * 返回数据库中的所有视图名称。
     * @param string $schema 视图的结构。默认为空字符串，表示当前或默认结构名称。
     * 如果不为空，则返回的视图名称将以模式名称为前缀。
     * @param bool $refresh 是否获取最新的可用视图名称。
     * 如果为 false，则返回先前获取的视图名称（如果可用）。
     * @return string[] 数据库中的所有视图名称。
     */
    public function getViewNames($schema = '', $refresh = false)
    {
        if (!isset($this->_viewNames[$schema]) || $refresh) {
            $this->_viewNames[$schema] = $this->findViewNames($schema);
        }

        return $this->_viewNames[$schema];
    }
}
