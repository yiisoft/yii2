<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use yii\mutex\MysqlMutex;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Class MysqlMutexTest
 *
 * @group mutex
 * @group db
 * @group mysql
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
