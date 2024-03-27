<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\data\DataProviderInterface;
use yii\web\Controller;
use yii\web\Request;
use yiiunit\framework\web\stubs\VendorImage;
use yiiunit\framework\web\stubs\ModelBindingStub;

/**
 * @author Sam Mousa<sam@mousa.nl>
 * @since 2.0.36
 */
class FakePhp71Controller extends Controller
{
    public $enableCsrfValidation = false;

    public function actionInjection(
        $before,
        Request $request,
        $between,
        VendorImage $vendorImage,
        ?Post $post,
        $after
    ) {
    }

    public function actionNullableInjection(?Request $request, ?Post $post)
    {
    }

    public function actionModuleServiceInjection(DataProviderInterface $dataProvider)
    {
    }

    public function actionModelBindingInjection(ModelBindingStub $model)
    {

    }
}
