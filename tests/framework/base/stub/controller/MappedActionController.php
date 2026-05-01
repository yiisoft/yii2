<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\controller;

use yii\base\Controller;

/**
 * Controller declaring an external action through {@see Controller::actions()} for action-map resolution tests.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class MappedActionController extends Controller
{
    public function actions(): array
    {
        return [
            'external' => ExternalAction::class,
        ];
    }
}
