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
 * Action used for testing that an action that does not implement {@see \yii\base\Action::run()} can be resolved and
 * executed without error.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class NoRunAction extends Action
{
}
