<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\console\controllers\fixtures;

use yii\test\ActiveFixture;

class FirstIndependentActiveFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Profile';

    public function load(): void
    {
        FixtureStorage::$activeFixtureSequence[] = self::class;
        parent::load();
    }
}
