<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\ActiveFixture;

class DependentActiveFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Customer';

    public $depends = [
        'yiiunit\data\console\controllers\fixtures\FirstIndependentActiveFixture',
        'yiiunit\data\console\controllers\fixtures\SecondIndependentActiveFixture',
    ];

    public function load(): void
    {
        FixtureStorage::$activeFixtureSequence[] = self::class;
        parent::load();
    }
}
