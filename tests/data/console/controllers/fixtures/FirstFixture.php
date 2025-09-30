<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\Fixture;

class FirstFixture extends Fixture
{
    public function load(): void
    {
        FixtureStorage::$firstFixtureData[] = 'some data set for first fixture';
    }

    public function unload(): void
    {
        FixtureStorage::$firstFixtureData = [];
    }
}
