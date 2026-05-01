<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\provider;

use yiiunit\framework\base\stub\controller\Test1Controller;
use yiiunit\framework\base\stub\controller\TestController;

/**
 * Data provider for {@see \yiiunit\framework\base\ControllerTest} test cases.
 *
 * Provides representative input/output pairs for the inline-action resolver and the action-ID regex used by
 * {@see \yii\base\Controller::createAction()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ControllerProvider
{
    /**
     * @return array<int, array{class-string, string, string|null}>
     */
    public static function inlineAction(): array
    {
        return [
            [Test1Controller::class, 'test-test_test_2', 'actionTestTest_test_2'],
            [Test1Controller::class, 'test_1', 'actionTest_1'],
            [Test1Controller::class, 'test_test', 'actionTest_test'],
            [TestController::class, 'non-existent-id', null],
            [TestController::class, 'test-test', 'actionTestTest'],
            [TestController::class, 'test3', 'actionTest3'],
        ];
    }

    /**
     * @return array<int, array{string, int}>
     */
    public static function actionIdMatcher(): array
    {
        return [
            ['!', 0],
            ['-apple', 0],
            ['9', 1],
            ['a', 1],
            ['app^le-999', 0],
            ['apple--id', 0],
            ['apple-999', 1],
            ['apple-id', 1],
            ['apple.', 0],
            ['apple333]', 0],
            ['apple\33', 0],
            ['apple_222', 1],
        ];
    }
}
