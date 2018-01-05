<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use ArrayIterator;
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

    private static $_wasActionIndexCalled = false;

    public static function getWasActionIndexCalled()
    {
        $wasCalled = self::$_wasActionIndexCalled;
        self::$_wasActionIndexCalled = false;

        return $wasCalled;
    }

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'test',
            'testArray',
            'alias',
        ]);
    }

    public function optionAliases()
    {
        return [
            't' => 'test',
            'ta' => 'testArray',
            'a' => 'alias',
        ];
    }

    public function actionIndex()
    {
        self::$_wasActionIndexCalled = true;
    }

    public function actionAksi1($fromParam, $other = 'default')
    {
        return new ArrayIterator([$fromParam, $other]);
    }

    public function actionAksi2(array $values, $value)
    {
        return new ArrayIterator([$values, $value]);
    }

    public function actionAksi3($available, $missing)
    {
    }

    public function actionAksi4()
    {
        return new ArrayIterator([$this->test]);
    }

    public function actionAksi5()
    {
        return new ArrayIterator([$this->alias]);
    }

    public function actionAksi6()
    {
        return new ArrayIterator($this->testArray);
    }

    public function actionWithComplexTypeHint(self $typedArgument, $simpleArgument)
    {
        return $simpleArgument;
    }

    public function actionStatus($status = 0)
    {
        return $status;
    }

    public function actionResponse($status = 0)
    {
        $response = new Response();
        $response->exitStatus = (int) $status;
        return $response;
    }
}
