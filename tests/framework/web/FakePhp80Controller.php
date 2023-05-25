<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;

class FakePhp80Controller extends Controller
{
    public $enableCsrfValidation = false;

    public function actionInjection(int|string $arg, int|string $second)
    {

    }
}
