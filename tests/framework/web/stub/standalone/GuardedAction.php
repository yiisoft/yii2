<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stub\standalone;

use yii\filters\VerbFilter;
use yii\web\Action;

/**
 * Standalone action guarded by a {@see VerbFilter} behavior, used to test real filters through module dispatch.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class GuardedAction extends Action
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['guarded' => ['POST']],
            ],
        ];
    }

    public function run(): string
    {
        return 'ok';
    }
}
