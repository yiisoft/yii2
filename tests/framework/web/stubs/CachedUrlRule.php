<?php

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
