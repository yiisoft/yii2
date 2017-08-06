<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

/**
 * @group db
 * @group mssql
 * @group validators
 */
class ExistValidatorTest extends \yiiunit\framework\validators\ExistValidatorTest
{
    public $driverName = 'sqlsrv';
}
