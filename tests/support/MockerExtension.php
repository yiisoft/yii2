<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\support;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Xepozz\InternalMocker\Mocker;
use Xepozz\InternalMocker\MockerState;

/**
 * PHPUnit extension that registers internal-function mocks for test execution.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2
 */
final class MockerExtension implements Extension
{
    /**
     * Registers event subscribers that initialize and reset mock state.
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(
            new class () implements StartedSubscriber {
                public function notify(Started $event): void
                {
                    MockerExtension::load();
                }
            },
            new class () implements PreparationStartedSubscriber {
                public function notify(PreparationStarted $event): void
                {
                    MockerState::resetState();
                }
            },
        );
    }

    /**
     * Loads configured function mocks and snapshots their initial state.
     */
    public static function load(): void
    {
        $mocks = [
            [
                'namespace' => 'yii\caching',
                'name' => 'extension_loaded',
            ],
        ];

        $mocker = new Mocker();
        $mocker->load($mocks);

        MockerState::saveState();
    }
}
