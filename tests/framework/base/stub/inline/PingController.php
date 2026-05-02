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
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class PingController extends Controller
{
    public bool $methodInvoked = false;

    public function actionPing(): string
    {
        $this->methodInvoked = true;

        return 'pong';
    }
}
