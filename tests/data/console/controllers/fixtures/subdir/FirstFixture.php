<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers\fixtures\subdir;

use yii\test\Fixture;
use yiiunit\data\console\controllers\fixtures\FixtureStorage;

class FirstFixture extends Fixture
{
    public function load(): void
    {
        FixtureStorage::$subdirFirstFixtureData[] = 'some data set for first fixture in subdir';
    }

    public function unload(): void
    {
        FixtureStorage::$subdirFirstFixtureData = [];
    }
}
