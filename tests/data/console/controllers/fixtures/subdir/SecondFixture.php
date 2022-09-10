<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers\fixtures\subdir;

use yii\test\Fixture;
use yiiunit\data\console\controllers\fixtures\FixtureStorage;

class SecondFixture extends Fixture
{
    public function load()
    {
        FixtureStorage::$subdirSecondFixtureData[] = 'some data set for subdir/second fixture';
    }

    public function unload()
    {
        FixtureStorage::$subdirSecondFixtureData = [];
    }
}
