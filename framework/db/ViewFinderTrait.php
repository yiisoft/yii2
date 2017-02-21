<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ViewFinderTrait implements the method getViewNames for finding views in a database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Bob Olde Hampsink <b.oldehampsink@nerds.company>
 * @since 2.0.12
 */
trait ViewFinderTrait
{
    /**
     * @var array list of ALL view names in the database
     */
    private $_viewNames = [];

    /**
     * Returns all views names in the database.
     * @param string $schema the schema of the views. Defaults to empty string, meaning the current or default schema.
     * @return array all views names in the database. The names have NO schema name prefix.
     */
    abstract protected function findViewNames($schema = '');

    /**
     * Returns all view names in the database.
     * @param string $schema the schema of the views. Defaults to empty string, meaning the current or default schema name.
     * If not empty, the returned view names will be prefixed with the schema name.
     * @param bool $refresh whether to fetch the latest available view names. If this is false,
     * view names fetched previously (if available) will be returned.
     * @return string[] all view names in the database.
     */
    public function getViewNames($schema = '', $refresh = false)
    {
        if (!isset($this->_viewNames[$schema]) || $refresh) {
            $this->_viewNames[$schema] = $this->findViewNames($schema);
        }

        return $this->_viewNames[$schema];
    }
}
