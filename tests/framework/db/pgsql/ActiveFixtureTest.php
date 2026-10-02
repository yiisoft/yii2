<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yiiunit\data\ar\Customer;
use yiiunit\framework\test\CustomerDbTestCase;
use yiiunit\framework\test\CustomerFixture;

/**
 * @group db
 * @group pgsql
 * @group test
 */
class ActiveFixtureTest extends \yiiunit\framework\test\ActiveFixtureTest
{
    public $driverName = 'pgsql';

    public function testFixturesLoadingResetsSeqence(): void
    {
        $test = new CustomerDbTestCase();
        $test->setUp();
        $fixture = $test->getFixture('customers');
        $this->assertInstanceOf(CustomerFixture::class, $fixture);

        $sequenceName = $fixture->getTableSchema()->sequenceName;
        $sequenceNextVal = $this->getConnection()->createCommand("SELECT currval('$sequenceName')")->queryScalar();

        $customer2 = $fixture->getModel('customer2');
        $this->assertInstanceOf(Customer::class, $customer2);

        $this->assertEquals($customer2->id + 1, $sequenceNextVal);

        $test->tearDown();
    }
}
