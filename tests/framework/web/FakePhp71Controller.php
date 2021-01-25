<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\data\DataProviderInterface;
use yii\web\Controller;
use yii\web\Request;
use yiiunit\framework\web\stubs\VendorImage;

/**
 * @author Sam Mousa<sam@mousa.nl>
 * @since 2.0.36
 */
class FakePhp71Controller extends Controller
{
    public $enableCsrfValidation = false;

    public function actionInjection($before, Request $request, $between, VendorImage $vendorImage, Post $post = null, $after)
    {

    }

    public function actionNullableInjection(?Request $request, ?Post $post)
    {
    }

    public function actionModuleServiceInjection(DataProviderInterface $dataProvider)
    {
    }
}

/**
 * @author Djibril <djidji01@gmail.com>
 */
class FakeAccessParamPhp71Controller extends FakePhp71Controller
{
    public $csrfParam = '_csrf';
    public $dataProviderkey = 'key';

    public function actionInjection($before, Request $request, $between, VendorImage $vendorImage, Post $post = null, $after)
    {
        return 'injection executed';

    }

    public function actionNullInjection(Request $request, ?Post $post)
    {
        return 'null injection executed';
    }

    public function actionModuleServiceInjection(DataProviderInterface $dataProvider)
    {
        return 'module service executed';
    }
    
    public function beforeAction($action)
    {
        if ($action->actionMethod === 'actionInjection') {
            if ( $action->getRequestedParam('request')->enableCsrfValidation === false) {
                return false;
            } elseif ($action->getRequestedParam('vendorImage')->name !== 'name') {
                return false;
            } 

        } elseif ($action->actionMethod === 'actionNullInjection') {            
            if ($action->getRequestedParam('request')->csrfParam !== $this->csrfParam) {
                return false;
            }
        } elseif ($action->actionMethod === 'actionModuleServiceInjection') {
            if ($action->getRequestedParam('dataProvider')->key !== $this->dataProviderkey) {
                return false;
            }
        }

        return true;
    }
}
