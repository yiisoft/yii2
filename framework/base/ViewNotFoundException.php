<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewNotFoundException represents an exception caused by view file not found.
 *
 * @author Alexander Makarov
 * @since 2.0.10
 */
class ViewNotFoundException extends InvalidArgumentException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'View not Found';
    }
}
