<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\module;

use yii\base\Controller;
use yiiunit\framework\base\ModuleTest;

/**
 * Controller invoked from {@see TestModule} to record action invocations for {@see ModuleTest}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ModuleTestController extends Controller
{
    public function actionTest1(): void
    {
        ModuleTest::$actionRuns[] = $this->action->uniqueId;
    }

    public function actionTest2(): void
    {
        ModuleTest::$actionRuns[] = $this->action->uniqueId;
    }
}
