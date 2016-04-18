<?php

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\Fixture;

class GlobalFixture extends Fixture
{

    public function load()
    {
        FixtureStorage::$globalFixturesData[] = 'some data set for global fixture';
    }

    public function unload()
    {
        FixtureStorage::$globalFixturesData = [];
    }

}
