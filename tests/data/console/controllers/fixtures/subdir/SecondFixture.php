<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\console\controllers\fixtures\subdir;

use yii\test\Fixture;
use yiiunit\data\console\controllers\fixtures\FixtureStorage;

class SecondFixture extends Fixture
{
    public function load(): void
    {
        FixtureStorage::$subdirSecondFixtureData[] = 'some data set for subdir/second fixture';
    }

    public function unload(): void
    {
        FixtureStorage::$subdirSecondFixtureData = [];
    }
}
