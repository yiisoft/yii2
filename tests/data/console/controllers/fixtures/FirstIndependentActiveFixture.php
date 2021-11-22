<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\ActiveFixture;

class FirstIndependentActiveFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Profile';

    public function load()
    {
        FixtureStorage::$activeFixtureSequence[] = self::className();
        parent::load();
    }
}
