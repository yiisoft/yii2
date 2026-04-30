<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\actions\post;

use yii\base\Action;

/**
 * Action used for testing {@see \yii\base\Module::actions()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class IndexAction extends Action
{
    public function run(): string
    {
        return 'post-index';
    }
}
