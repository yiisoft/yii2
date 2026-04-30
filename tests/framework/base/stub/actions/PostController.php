<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\actions;

use yii\base\Controller;

/**
 * Controller used for testing {@see \yii\base\Module::actions()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class PostController extends Controller
{
    public function actionView(): string
    {
        return 'controller-view';
    }
}
