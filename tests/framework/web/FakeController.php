<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;
use yiiunit\framework\di\stubs\QuxInterface;
use yiiunit\framework\web\stubs\Bar;
use yii\validators\EmailValidator;

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

    public function testSetResponseData($data, $format = null)
    {
        return $this->setResponseData($data, $format);
    }
}
