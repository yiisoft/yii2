<?php
namespace yiiunit\framework\console\controllers;


use yii\console\controllers\CacheController;


/**
 * CacheController that discards output.
 */
class SilencedCacheController extends CacheController
{
    /**
     * @inheritdoc
     */
    public function stdout($string)
    {
        // do nothing
    }
}