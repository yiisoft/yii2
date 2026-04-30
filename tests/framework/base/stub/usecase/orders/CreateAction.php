<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\usecase\orders;

use yii\base\Action;

/**
 * Action used for testing that a standalone action can be resolved and executed when the action ID contains a dash.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CreateAction extends Action
{
    public function run(): string
    {
        return 'usecase-orders-create';
    }
}
