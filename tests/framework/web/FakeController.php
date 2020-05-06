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
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionAksi1($fromGet, $other = 'default')
    {
    }

    public function actionInjection($before, Request $request, $between, VendorImage $vendorImage, Post $post = null, $after)
    {

    }
}
