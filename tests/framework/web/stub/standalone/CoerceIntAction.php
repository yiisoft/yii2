<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stub\standalone;

use yii\web\Action;

/**
 * Action used for testing scalar coercion in {@see \yii\web\Action::resolveStandaloneParams()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CoerceIntAction extends Action
{
    public function run(int $id): int
    {
        return $id;
    }
}
