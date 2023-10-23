<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\Fixture;

class SecondFixture extends Fixture
{
    public function load(): void
    {
        FixtureStorage::$secondFixtureData[] = 'some data set for second fixture';
    }

    public function unload(): void
    {
        FixtureStorage::$secondFixtureData = [];
    }
}
