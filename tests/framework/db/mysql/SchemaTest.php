<?php

namespace yiiunit\framework\db\mysql;
use yii\db\Expression;

/**
 * @group db
 * @group mysql
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'mysql';

    public function testLoadDefaultDatetimeColumn(){
        if (version_compare($this->getConnection()->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.6', '>=')) {
            $sql = <<<SQL
CREATE TABLE  IF NOT EXISTS `datetime_test`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

            $this->getConnection()->createCommand($sql)->execute();

            $schema = $this->getConnection()->getTableSchema('datetime_test');

            $dt = $schema->columns['dt'];

            $this->assertInstanceOf(Expression::className(),$dt->defaultValue);
        }
    }
}
