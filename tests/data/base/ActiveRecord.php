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
 * Stub {@see Component} with a `save()` method that triggers a `save` event for Event tests.
 */
class ActiveRecord extends Component
{
    public function save(): void
    {
        $this->trigger('save');
    }
}
