<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\module;

use yii\base\Module;

/**
 * Module pre-wired with two controllers in {@see Module::$controllerMap} for {@see ModuleTest} dispatch tests.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class TestModule extends Module
{
    public $controllerMap = [
        'test-controller1' => ModuleTestController::class,
        'test-controller2' => ModuleTestController::class,
    ];
}
