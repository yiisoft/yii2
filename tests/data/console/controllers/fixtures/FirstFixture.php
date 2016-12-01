<?php

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\Fixture;

class FirstFixture extends Fixture
{
    public function load()
    {
        FixtureStorage::$firstFixtureData[] = 'some data set for first fixture';
    }

    public function unload()
    {
        FixtureStorage::$firstFixtureData = [];
    }

}
