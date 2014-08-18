<?php

namespace codeception\frontend\unit;

/**
 * @inheritdoc
 */
class DbTestCase extends \yii\codeception\DbTestCase
{
    public $appConfig = '@codeception/config/frontend/unit.php';
}
