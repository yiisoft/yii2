<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PDO;
use yiiunit\framework\db\AnyCaseValue;

/**
 * @group db
 * @group mysql
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'mysql';

    protected $_serverVersion;

    protected function getServerVersion($refresh = false)
    {
        if ($refresh || !isset($this->_serverVersion)) {
            $databases = self::getParam('databases');
            $this->database = $databases[$this->driverName];
            $connection = $this->getConnection();
            $this->_serverVersion = $connection->getSlavePdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        }

        return $this->_serverVersion;
    }

    public function testGetSchemaNames()
    {
        $this->markTestSkipped('Schemas are not supported in MySQL.');
    }

    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();
        $result['1: check'][2] = false;

        $result['2: primary key'][2]->name = null;
        $result['2: check'][2] = false;

        // Work aroung bug in MySQL 5.1 - it creates only this table in lowercase. O_o
        $result['3: foreign key'][2][0]->foreignTableName = new AnyCaseValue('T_constraints_2');
        $result['3: check'][2] = false;

        // https://github.com/yiisoft/yii2/issues/15248
        if (version_compare($this->getServerVersion(), '8.0.1', '>=') === true) {
            $result['3: foreign key'][2][0]->foreignColumnNames = ['c_id_1', 'c_id_2'];
        }

        $result['4: check'][2] = false;

        return $result;
    }
}
