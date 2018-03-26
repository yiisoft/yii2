<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use yii\base\Exception;

/**
 * Synchronize Exception
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @since (unknown yet)
 */
class SyncException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Synchronize Exception';
    }

}
