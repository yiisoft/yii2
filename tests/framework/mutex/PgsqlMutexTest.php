<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yii\mutex\PgsqlMutex;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Class PgsqlMutexTest.
 *
 * @group mutex
 * @group db
 * @group pgsql
 */
class PgsqlMutexTest extends DatabaseTestCase
{
    use MutexTestTrait;

    protected $driverName = 'pgsql';

    /**
     * @return PgsqlMutex
     * @throws InvalidConfigException
     */
    protected function createMutex()
    {
        return Yii::createObject([
            'class' => PgsqlMutex::class,
            'db' => $this->getConnection(),
        ]);
    }
}
