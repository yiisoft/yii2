<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\standalone;

use yii\base\Action;

/**
 * Action used for testing standalone action resolver with constructor injection.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class InjectingAction extends Action
{
    public ActionTestService $service;

    public function __construct($id, $controller, ActionTestService $service, $config = [])
    {
        $this->service = $service;

        parent::__construct($id, $controller, $config);
    }

    public function run(): string
    {
        return $this->service->name();
    }
}
