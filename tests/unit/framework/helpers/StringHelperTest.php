<?php
namespace yiiunit\framework\helpers;

use yii\helpers\StringHelper;
use yiiunit\TestCase;

/**
 * StringHelperTest
 * @group helpers
 */
class StringHelperTest extends TestCase
{
	public function testStrlen()
	{
		$this->assertEquals(4, StringHelper::byteLength('this'));
		$this->assertEquals(6, StringHelper::byteLength('это'));
	}

	public function testSubstr()
	{
		$this->assertEquals('th', StringHelper::byteSubstr('this', 0, 2));
		$this->assertEquals('э', StringHelper::byteSubstr('это', 0, 2));
	}

	public function testBasename()
	{
		$this->assertEquals('', StringHelper::basename(''));

		$this->assertEquals('file', StringHelper::basename('file'));
		$this->assertEquals('file.test', StringHelper::basename('file.test', '.test2'));
		$this->assertEquals('file', StringHelper::basename('file.test', '.test'));

		$this->assertEquals('file', StringHelper::basename('/file'));
		$this->assertEquals('file.test', StringHelper::basename('/file.test', '.test2'));
		$this->assertEquals('file', StringHelper::basename('/file.test', '.test'));

		$this->assertEquals('file', StringHelper::basename('/path/to/file'));
		$this->assertEquals('file.test', StringHelper::basename('/path/to/file.test', '.test2'));
		$this->assertEquals('file', StringHelper::basename('/path/to/file.test', '.test'));

		$this->assertEquals('file', StringHelper::basename('\file'));
		$this->assertEquals('file.test', StringHelper::basename('\file.test', '.test2'));
		$this->assertEquals('file', StringHelper::basename('\file.test', '.test'));

		$this->assertEquals('file', StringHelper::basename('C:\file'));
		$this->assertEquals('file.test', StringHelper::basename('C:\file.test', '.test2'));
		$this->assertEquals('file', StringHelper::basename('C:\file.test', '.test'));

		$this->assertEquals('file', StringHelper::basename('C:\path\to\file'));
		$this->assertEquals('file.test', StringHelper::basename('C:\path\to\file.test', '.test2'));
		$this->assertEquals('file', StringHelper::basename('C:\path\to\file.test', '.test'));

		// mixed paths
		$this->assertEquals('file.test', StringHelper::basename('/path\to/file.test'));
		$this->assertEquals('file.test', StringHelper::basename('/path/to\file.test'));
		$this->assertEquals('file.test', StringHelper::basename('\path/to\file.test'));

		// \ and / in suffix
		$this->assertEquals('file', StringHelper::basename('/path/to/filete/st', 'te/st'));
		$this->assertEquals('st', StringHelper::basename('/path/to/filete/st', 'te\st'));
		$this->assertEquals('file', StringHelper::basename('/path/to/filete\st', 'te\st'));
		$this->assertEquals('st', StringHelper::basename('/path/to/filete\st', 'te/st'));

		// http://www.php.net/manual/en/function.basename.php#72254
		$this->assertEquals('foo', StringHelper::basename('/bar/foo/'));
		$this->assertEquals('foo', StringHelper::basename('\\bar\\foo\\'));
	}
}
