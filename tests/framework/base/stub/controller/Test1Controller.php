<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\controller;

use yii\base\Controller;

/**
 * Controller exposing action methods with underscored names to exercise the inline-action ID parser.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
final class Test1Controller extends Controller
{
    public function actionTest_1(): void
    {
    }

    public function actionTest_test(): void
    {
    }

    public function actionTestTest_test_2(): void
    {
    }
}
