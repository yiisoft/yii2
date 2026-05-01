<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\controller;

use yii\base\Module;

/**
 * Module whose {@see beforeAction()} unconditionally rejects the action so {@see Controller::runAction()} can exercise
 * its short-circuit branch.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class VetoingModule extends Module
{
    /**
     * @var array<int, string>
     */
    public array $afterActionsCalled = [];

    public function beforeAction($action)
    {
        return false;
    }

    public function afterAction($action, $result)
    {
        $this->afterActionsCalled[] = $action->id;

        return $result;
    }
}
