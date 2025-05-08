<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yiiunit\framework\test\CustomerDbTestCase;

/**
 * @group db
 * @group pgsql
 * @group test
 */
class ActiveFixtureTest extends \yiiunit\framework\test\ActiveFixtureTest
{
    public $driverName = 'pgsql';

    public function testFixturesLoadingResetsSeqence()
    {
        $test = new CustomerDbTestCase();
        $test->setUp();
        $fixture = $test->getFixture('customers');

        $sequenceName = $fixture->getTableSchema()->sequenceName;
        $sequenceNextVal = $this->getConnection()->createCommand("SELECT currval('$sequenceName')")->queryScalar();
        $this->assertEquals($fixture->getModel('customer2')->id + 1, $sequenceNextVal);

        $test->tearDown();
    }
}
