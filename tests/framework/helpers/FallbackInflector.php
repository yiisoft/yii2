<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
