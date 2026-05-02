<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\inline;

use yii\base\Controller;

/**
 * Controller used for testing the inline action lifecycle wrapper.
 *
 * Records each invocation step into a shared {@see $callLog} so test cases can assert the exact ordering of
 * {@see InlineAction::beforeRun()}, the controller method, and {@see InlineAction::afterRun()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class PingController extends Controller
{
    public static array $callLog = [];

    public function actionPing(): string
    {
        self::$callLog[] = 'actionPing';

        return 'pong';
    }
}
