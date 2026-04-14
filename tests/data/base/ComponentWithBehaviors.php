<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

use yii\base\Component;

/**
 * Stub {@see Component} that attaches {@see NewBehavior} and {@see NewBehavior2} via `behaviors()`.
 */
final class ComponentWithBehaviors extends Component
{
    public function behaviors(): array
    {
        return [
            'named' => NewBehavior::class,
            NewBehavior2::class,
        ];
    }
}
