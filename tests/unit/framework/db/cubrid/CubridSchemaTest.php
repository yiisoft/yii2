<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\SchemaTest;

class CubridSchemaTest extends SchemaTest
{
	public $driverName = 'cubrid';

	public function testGetPDOType()
	{
		$values = array(
			null => \PDO::PARAM_NULL,
			'' => \PDO::PARAM_STR,
			'hello' => \PDO::PARAM_STR,
			0 => \PDO::PARAM_INT,
			1 => \PDO::PARAM_INT,
			1337 => \PDO::PARAM_INT,
			true => \PDO::PARAM_INT, // CUBRID PDO does not support PARAM_BOOL
			false => \PDO::PARAM_INT, // CUBRID PDO does not support PARAM_BOOL
		);

		$schema = $this->getConnection()->schema;

		foreach($values as $value => $type) {
			$this->assertEquals($type, $schema->getPdoType($value));
		}
		$this->assertEquals(\PDO::PARAM_LOB, $schema->getPdoType($fp=fopen(__FILE__, 'rb')));
		fclose($fp);
	}
}
