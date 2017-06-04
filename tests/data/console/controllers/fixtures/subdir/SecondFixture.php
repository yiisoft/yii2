<?php

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
