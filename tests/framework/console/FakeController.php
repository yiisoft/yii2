<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use yii\console\Controller;
use yii\console\Response;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{
    public $test;

    public $testArray = [];

    public $alias;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'test',
            'testArray',
            'alias'
        ]);
    }

    public function optionAliases()
    {
        return [
            't' => 'test',
            'ta' => 'testArray',
            'a' => 'alias'
        ];
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
        return $this->alias;
    }

    public function actionAksi6()
    {
        return $this->testArray;
    }

    public function actionStatus($status = 0)
    {
        return $status;
    }

    public function actionResponse($status = 0)
    {
        $response = new Response();
        $response->exitStatus = (int)$status;
        return $response;
    }
}
