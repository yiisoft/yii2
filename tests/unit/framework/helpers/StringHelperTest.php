<?php
namespace yiiunit\framework\helpers;

use yii\helpers\FileHelper;
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
		$this->assertEquals(4, StringHelper::byteLen('this'));
		$this->assertEquals(6, StringHelper::byteLen('это'));
	}

	public function testSubstr()
	{
		$this->assertEquals('th', StringHelper::byteSubstr('this', 0, 2));
		$this->assertEquals('э', StringHelper::byteSubstr('это', 0, 2));
	}

	public function testBasename()
	{
		$this->assertEquals('', FileHelper::basename(''));

		$this->assertEquals('file', FileHelper::basename('file'));
		$this->assertEquals('file.test', FileHelper::basename('file.test', '.test2'));
		$this->assertEquals('file', FileHelper::basename('file.test', '.test'));

		$this->assertEquals('file', FileHelper::basename('/file'));
		$this->assertEquals('file.test', FileHelper::basename('/file.test', '.test2'));
		$this->assertEquals('file', FileHelper::basename('/file.test', '.test'));

		$this->assertEquals('file', FileHelper::basename('/path/to/file'));
		$this->assertEquals('file.test', FileHelper::basename('/path/to/file.test', '.test2'));
		$this->assertEquals('file', FileHelper::basename('/path/to/file.test', '.test'));

		$this->assertEquals('file', FileHelper::basename('\file'));
		$this->assertEquals('file.test', FileHelper::basename('\file.test', '.test2'));
		$this->assertEquals('file', FileHelper::basename('\file.test', '.test'));

		$this->assertEquals('file', FileHelper::basename('C:\file'));
		$this->assertEquals('file.test', FileHelper::basename('C:\file.test', '.test2'));
		$this->assertEquals('file', FileHelper::basename('C:\file.test', '.test'));

		$this->assertEquals('file', FileHelper::basename('C:\path\to\file'));
		$this->assertEquals('file.test', FileHelper::basename('C:\path\to\file.test', '.test2'));
		$this->assertEquals('file', FileHelper::basename('C:\path\to\file.test', '.test'));

		// mixed paths
		$this->assertEquals('file.test', FileHelper::basename('/path\to/file.test'));
		$this->assertEquals('file.test', FileHelper::basename('/path/to\file.test'));
		$this->assertEquals('file.test', FileHelper::basename('\path/to\file.test'));

		// \ and / in suffix
		$this->assertEquals('file', FileHelper::basename('/path/to/filete/st', 'te/st'));
		$this->assertEquals('st', FileHelper::basename('/path/to/filete/st', 'te\st'));
		$this->assertEquals('file', FileHelper::basename('/path/to/filete\st', 'te\st'));
		$this->assertEquals('st', FileHelper::basename('/path/to/filete\st', 'te/st'));

		// http://www.php.net/manual/en/function.basename.php#72254
		$this->assertEquals('foo', FileHelper::basename('/bar/foo/'));
		$this->assertEquals('foo', FileHelper::basename('\\bar\\foo\\'));
	}
}
