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
 * Module that registers {@see TestController} in its controller map so nested route dispatch can be exercised.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class RoutableModule extends Module
{
    public function init()
    {
        parent::init();

        $this->controllerMap = [
            'test-controller' => TestController::class,
        ];
    }
}
