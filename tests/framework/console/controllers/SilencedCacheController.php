<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use yii\console\controllers\CacheController;

/**
 * CacheController that discards output.
 */
class SilencedCacheController extends CacheController
{
    /**
     * {@inheritdoc}
     */
    public function stdout($string)
    {
        // do nothing
    }
}
