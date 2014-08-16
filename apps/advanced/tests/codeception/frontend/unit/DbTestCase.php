<?php

namespace codeception\frontend\unit;

/**
 * @inheritdoc
 */
class DbTestCase extends \yii\codeception\DbTestCase
{
    public $appConfig = '@codeception/frontend/unit/_config.php';
}
