<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\standalone;

use yii\base\Action;

/**
 * Standalone action with a legacy positional constructor that attaches a behavior reading `$this->id`.
 *
 * Verifies that the behavior layer keeps working when the dispatcher assigns the action ID **after** construction:
 * the behavior is attached at construction time (when `$this->id` is `null`), but its event handler runs after the
 * dispatcher has assigned the real ID, so the recorded ID must match the route segment.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LegacyConstructorBehaviorAction extends Action
{
    /**
     * @var array<int, array{string, string|null}>
     */
    public static array $events = [];

    public function __construct($id = null, $controller = null, $config = [])
    {
        parent::__construct($id, $controller, $config);
    }

    public function behaviors(): array
    {
        return [
            'recorder' => [
                'class' => LegacyIdRecorderBehavior::class,
            ],
        ];
    }

    public function run(): string
    {
        return 'legacy-behavior';
    }
}
