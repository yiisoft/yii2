<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stubs;

use yii\web\UrlRule;

class CachedUrlRule extends UrlRule
{
    public $createCounter = 0;

    public function createUrl($manager, $route, $params)
    {
        $this->createCounter++;
        return parent::createUrl($manager, $route, $params);
    }
}
