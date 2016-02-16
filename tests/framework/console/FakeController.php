<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use yii\console\Controller;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{
    public $test;

    public $testArray = [];

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'test',
            'testArray'
        ]);
    }

    public function shortCuts()
    {
        return ['t' => 'test', 'ta' => 'testArray'];
    }


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

    public function actionAksi4()
    {
        return $this->test;
    }

    public function actionAksi5()
    {
        return $this->testArray;
    }
}
