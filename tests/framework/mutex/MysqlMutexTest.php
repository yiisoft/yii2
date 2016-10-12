<?php

namespace yiiunit\framework\mutex;

use yii\mutex\MysqlMutex;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Class MysqlMutexTest
 *
 * @group mutex
 * @group mysql
 * 
 * @package yiiunit\framework\mutex
 */
class MysqlMutexTest extends DatabaseTestCase
{
    use MutexTestTrait;

    protected $driverName = 'mysql';

    /**
     * @return MysqlMutex
     * @throws \yii\base\InvalidConfigException
     */
    protected function createMutex()
    {
        return \Yii::createObject([
            'class' => MysqlMutex::className(),
            'db' => $this->getConnection(),
        ]);
    }

}
