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
 * Controller with a long hyphenated route used by {@see ModuleTest::testCreateControllerByID()} to validate the
 * {@see Module::createControllerByID()} CamelCase mapping.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class VeryComplexNameTestController extends Controller
{
    public function actionIndex(): void
    {
        ModuleTest::$actionRuns[] = $this->action->uniqueId;
    }
}
