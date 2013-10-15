<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group cubrid
 */
class CubridSchemaTest extends SchemaTest
{
	public $driverName = 'cubrid';

	public function testGetPDOType()
	{
		$values = array(
			array(null, \PDO::PARAM_NULL),
			array('', \PDO::PARAM_STR),
			array('hello', \PDO::PARAM_STR),
			array(0, \PDO::PARAM_INT),
			array(1, \PDO::PARAM_INT),
			array(1337, \PDO::PARAM_INT),
			array(true, \PDO::PARAM_INT),
			array(false, \PDO::PARAM_INT),
			array($fp=fopen(__FILE__, 'rb'), \PDO::PARAM_LOB),
		);

		/** @var Schema $schema */
		$schema = $this->getConnection()->schema;

		foreach($values as $value) {
			$this->assertEquals($value[1], $schema->getPdoType($value[0]));
		}
		fclose($fp);
	}
}
