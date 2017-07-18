<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\Fixture;

class SecondFixture extends Fixture
{
    public function load()
    {
        FixtureStorage::$secondFixtureData[] = 'some data set for second fixture';
    }

    public function unload()
    {
        FixtureStorage::$secondFixtureData = [];
    }
}
