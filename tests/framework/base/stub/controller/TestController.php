<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\controller;

use yii\base\Controller;
use yiiunit\framework\base\ControllerTest;

/**
 * Controller used to record action invocations for {@see ControllerTest}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
final class TestController extends Controller
{
    public function actionTest1()
    {
        ControllerTest::$actionRuns[] = $this->action->uniqueId;

        return 'test1';
    }

    public function actionTest2()
    {
        ControllerTest::$actionRuns[] = $this->action->uniqueId;

        return 'test2';
    }

    public function actionTest3(): void
    {
    }

    public function actionTestTest(): void
    {
    }

    public function actionTest_test(): void
    {
    }
}
