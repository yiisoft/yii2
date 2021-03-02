<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;

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

    public function actionStringy(string $foo = null)
    {
    }
}

/**
 * @author Djibril <djidji01@gmail.com>
 */
class FakeAccessParamPhp7Controller extends Controller
{
    public $enableCsrfValidation = false;

    public function actionAksi1(int $foo, float $bar = null, bool $true, bool $false)
    {
        return $this->actionParams;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        } elseif ($action->getRequestedParam('foo') === 0) {
            return false;
        } elseif ($action->getRequestedParam('bar') === null) {
            return false;
        } elseif ($action->getRequestedParam('true') === false) {
            return false;
        } elseif ($action->getRequestedParam('false') === false) {
            return false;
        } 
        return true;
    }
}
