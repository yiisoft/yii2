<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\actions;

use yii\base\Action;

/**
 * Standalone {@see Action} declaring the historical positional constructor `__construct($id, $controller, $config)`,
 * placed under the action namespace so {@see \yii\base\Module::createStandaloneAction()} can discover it by convention
 * from the route `legacy-discoverable`.
 *
 * Symmetric to {@see \yiiunit\framework\base\stub\standalone\LegacyConstructorAction} but reachable via the
 * namespace-based dispatch path rather than {@see \yii\base\Module::$actionMap}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LegacyDiscoverableAction extends Action
{
    public string|null $idSeenInConstructor = 'sentinel';
    public string|null $idSeenInInit = 'sentinel';

    public function __construct($id = null, $controller = null, $config = [])
    {
        $this->idSeenInConstructor = $id;

        parent::__construct($id, $controller, $config);
    }

    public function init(): void
    {
        parent::init();

        $this->idSeenInInit = $this->id;
    }

    public function run(): string
    {
        return 'legacy-discoverable';
    }
}
