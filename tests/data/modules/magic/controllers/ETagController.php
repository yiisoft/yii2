<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\modules\magic\controllers;

use yii\console\Controller;

class ETagController extends Controller
{
    public function actionListETags(): string
    {
        return '';
    }

    public function actionDelete(): string
    {
        return 'deleted';
    }
}
