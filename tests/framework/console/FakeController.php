<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use yii\console\Controller;
use yiiunit\framework\di\stubs\QuxInterface;
use yiiunit\framework\web\stubs\Bar;
use yii\validators\EmailValidator;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{

    public function actionAksi1($fromParam, $other = 'default')
    {
        return[$fromParam, $other];
    }

    public function actionAksi2(array $values, $value)
    {
        return [$values, $value];
    }

    public function actionAksi3($available, $missing)
    {
    }
}
