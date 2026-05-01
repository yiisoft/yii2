<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\actions;

use yii\base\Action;
use yiiunit\framework\base\stub\standalone\ActionTestService;

/**
 * Action used for testing convention-based discovery of a standalone action that uses a PHP `8.x` promoted constructor
 * for dependency injection.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class HealthAction extends Action
{
    public function __construct(private readonly ActionTestService $service)
    {
    }

    public function run(): string
    {
        return 'health-' . $this->service->name();
    }
}
