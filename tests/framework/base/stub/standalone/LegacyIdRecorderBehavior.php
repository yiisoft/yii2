<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\standalone;

use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\base\Controller;

/**
 * Behavior that records the action ID seen at every {@see Controller::EVENT_BEFORE_ACTION} and
 * {@see Controller::EVENT_AFTER_ACTION} dispatch.
 *
 * Pushes `[stage, id]` tuples into {@see LegacyConstructorBehaviorAction::$events} so tests can verify that the
 * dispatcher-assigned ID is observable at event time even though the behavior was attached during construction (when
 * the ID was still `null`).
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LegacyIdRecorderBehavior extends Behavior
{
    public function events(): array
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'recordBeforeAction',
            Controller::EVENT_AFTER_ACTION => 'recordAfterAction',
        ];
    }

    public function recordBeforeAction(ActionEvent $event): void
    {
        LegacyConstructorBehaviorAction::$events[] = ['beforeAction', $event->action->id];
    }

    public function recordAfterAction(ActionEvent $event): void
    {
        LegacyConstructorBehaviorAction::$events[] = ['afterAction', $event->action->id];
    }
}
