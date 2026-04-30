<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\actions\sub;

use yii\base\Controller;

/**
 * Intentionally extends Controller (not Action) to assert that the standalone resolver rejects classes with the Action
 * suffix that are actually Controller subclasses.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class FooAction extends Controller
{
    public function actionIndex(): string
    {
        return 'unreachable';
    }
}
