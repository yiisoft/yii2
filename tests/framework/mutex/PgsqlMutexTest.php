<?php

namespace yiiunit\framework\mutex;

use yii\mutex\PgsqlMutex;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Class PgsqlMutexTest
 *
 * @group mutex
 * @group pgsql
 * 
 * @package yiiunit\framework\mutex
 */
class PgsqlMutexTest extends DatabaseTestCase
{
    use MutexTestTrait;

    protected $driverName = 'pgsql';

    /**
     * @return PgsqlMutex
     * @throws \yii\base\InvalidConfigException
     */
    protected function createMutex()
    {
        return \Yii::createObject([
            'class' => PgsqlMutex::className(),
            'db' => $this->getConnection(),
        ]);
    }

}
