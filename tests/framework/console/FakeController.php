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

    public function actionAksi1(Bar $bar, $fromParam, $other = 'default')
    {
        return[$bar, $fromParam, $other];
    }

    public function actionAksi2(Bar $barBelongApp, QuxInterface $qux)
    {
        return[$barBelongApp, $qux];
    }

    public function actionAksi3(QuxInterface $quxApp)
    {
        return[$quxApp];
    }

    public function actionAksi4(Bar $bar, QuxInterface $quxApp, array $values, $value)
    {
        return [$bar->foo, $quxApp->quxMethod(), $values, $value];
    }

    public function actionAksi5($q, Bar $bar, QuxInterface $quxApp)
    {
        return [$q, $bar->foo, $quxApp->quxMethod()];
    }

    public function actionAksi6($q, EmailValidator $validator)
    {
        return [$q, $validator->validate($q), $validator->validate('misbahuldmunir@gmail.com')];
    }
    
    public function actionAksi7(Bar $bar, $avaliable, $missing)
    {
        
    }

    public function actionAksi8($arg1, $arg2)
    {
        return func_get_args();
    }

    public function actionAksi9($arg1, $arg2, QuxInterface $quxApp)
    {
        return func_get_args();
    }
}
