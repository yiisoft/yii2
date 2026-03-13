<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rest\stubs;

use yii\rest\ActiveController;

class RestController extends ActiveController
{
    public $actions = [];

    public function actions()
    {
        return $this->actions;
    }
}
