<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\BaseInflector;

/**
 * Forces Inflector::slug to use PHP even if intl is available.
 */
class FallbackInflector extends BaseInflector
{
    /**
     * {@inheritdoc}
     */
    protected static function hasIntl()
    {
        return false;
    }
}
