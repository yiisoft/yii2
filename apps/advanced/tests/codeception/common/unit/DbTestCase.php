<?php

namespace codeception\common\unit;

/**
 * @inheritdoc
 */
class DbTestCase extends \yii\codeception\DbTestCase
{
    public $appConfig = '@codeception/config/common/unit.php';
}
