<?php
namespace yiiunit\framework\db\oci;

use yiiunit\framework\data\ActiveDataProviderTest;
use yii\data\ActiveDataProvider;
use yiiunit\data\ar\Order;

/**
 * @group db
 * @group oci
 * @group data
 */
class OracleActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'oci';
}
