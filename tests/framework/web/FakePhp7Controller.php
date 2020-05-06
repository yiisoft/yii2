<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;
use yii\web\Request;
use yiiunit\framework\web\stubs\VendorImage;

/**
 * @author Brandon Kelly <branodn@craftcms.com>
 * @since 2.0.31
 */
class FakePhp7Controller extends Controller
{
    public $enableCsrfValidation = false;

    public function actionAksi1(int $foo, float $bar = null, bool $true, bool $false)
    {
    }

    public function actionInjection(?Request $request, ?Post $post)
    {
    }
}
