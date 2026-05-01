<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stub\standalone;

use yii\web\Action;

/**
 * Standalone {@see \yii\web\Action} declaring the historical positional constructor `__construct($id, $controller, $config)`.
 *
 * Symmetric to the base-Action regression: verifies that {@see \yii\base\Module::runMappedAction()} can still
 * instantiate a legacy-shaped {@see \yii\web\Action} subclass through the dispatch path, that the constructor and
 * `init()` observe the route segment ID, and that HTTP-aware parameter binding via {@see \yii\web\Action::resolveStandaloneParams()}
 * still operates after construction.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LegacyConstructorWebAction extends Action
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

    public function run(int $id): int
    {
        return $id;
    }
}
