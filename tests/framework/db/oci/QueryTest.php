<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\db\Query;

/**
 * @group db
 * @group oci
 */
class QueryTest extends \yiiunit\framework\db\QueryTest
{
    protected $driverName = 'oci';

    public function testOne()
    {
        $db = $this->getConnection();

        $result = (new Query())->from('customer')->where(['[[status]]' => 2])->one($db);
        $this->assertEquals('user3', $result['name']);

        $result = (new Query())->from('customer')->where(['[[status]]' => 3])->one($db);
        $this->assertFalse($result);
    }
}
