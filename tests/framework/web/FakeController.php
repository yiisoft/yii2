<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;

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
}

/**
 * @author Djibril <djidji01@gmail.com>
 */
class FakeAccessParamInBeforeActionController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionAksi1($fromGet, $other = 'default')
    {
        return $this->actionParams;
    }

    public function beforeAction($action)
    {        
        if (!parent::beforeAction($action)) {
            return false;
        } elseif ($action->getRequestedParam('fromGet') === false) {
            return false;
        } elseif ($action->getRequestedParam('other') === false) {
            return false;
        }

        return true;
    }
}
