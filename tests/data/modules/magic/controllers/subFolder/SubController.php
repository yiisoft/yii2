<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\modules\magic\controllers\subFolder;

use yii\console\Controller;

class SubController extends Controller
{
    public function actionTest(): string
    {
        return '';
    }
}
