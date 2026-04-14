<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

use yii\base\Behavior;

/**
 * Stub {@see Behavior} exposing public/private properties and a `test()` method for Component tests.
 *
 * @extends Behavior<NewComponent>
 */
final class NewBehavior extends Behavior
{
    public $p;
    private $p2;

    public function getP2()
    {
        return $this->p2;
    }

    public function setP2($value): void
    {
        $this->p2 = $value;
    }

    public function test()
    {
        $this->owner->behaviorCalled = true;

        return 2;
    }
}
