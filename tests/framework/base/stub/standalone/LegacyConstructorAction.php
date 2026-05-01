<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\standalone;

use yii\base\Action;
use yii\base\Controller;

/**
 * Standalone action declaring the historical positional constructor `__construct($id, $controller, $config)`.
 *
 * Used to verify that {@see \yii\base\Module::runMappedAction()} can still instantiate a legacy-shaped action through
 * the unified DI path even though `$id` arrives as `null` in the constructor and is assigned afterwards by the
 * dispatcher.
 *
 * Records the values of `$id` and `$controller` observed inside the constructor itself and inside `init()` so that
 * tests can inspect each lifecycle moment independently.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LegacyConstructorAction extends Action
{
    public string|null $idSeenInConstructor = 'sentinel';
    public Controller|null $controllerSeenInConstructor = null;
    public string|null $idSeenInInit = 'sentinel';

    public function __construct($id = null, $controller = null, $config = [])
    {
        $this->idSeenInConstructor = $id;
        $this->controllerSeenInConstructor = $controller;

        parent::__construct($id, $controller, $config);
    }

    public function init(): void
    {
        parent::init();

        $this->idSeenInInit = $this->id;
    }

    public function run(): string
    {
        return 'legacy';
    }
}
