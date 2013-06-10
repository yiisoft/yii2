<?php

use yii\helpers\base\FileHelper;
use yii\test\TestCase;

/**
 * Unit test for [[yii\helpers\base\FileHelper]]
 * @see FileHelper
 */
class FileHelperTest extends TestCase
{
	private $testFilePath = '';

	public function setUp()
	{
		$this->testFilePath = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . get_class($this);
		$this->createDir($this->testFilePath);
	}

	public function tearDown()
	{
		$this->removeDir($this->testFilePath);
	}

	/**
	 * Creates directory.
	 * @param string $dirName directory full name.
	 */
	protected function createDir($dirName)
	{
		if (!file_exists($dirName)) {
			mkdir($dirName, 0777, true);
		}
	}

	/**
	 * Removes directory.
	 * @param string $dirName directory full name.
	 */
	protected function removeDir($dirName)
	{
		if (!empty($dirName) && file_exists($dirName)) {
			exec("rm -rf {$dirName}");
		}
	}

	// Tests :

	public function testCopyDirectory() {
		$basePath = $this->testFilePath;
		$srcDirName = $basePath . DIRECTORY_SEPARATOR . 'test_src_dir';
		mkdir($srcDirName, 0777, true);
		$files = array(
			'file1.txt' => 'file 1 content',
			'file2.txt' => 'file 2 content',
		);
		foreach ($files as $name => $content) {
			file_put_contents($srcDirName . DIRECTORY_SEPARATOR . $name, $content);
		}
		$dstDirName = $basePath . DIRECTORY_SEPARATOR . 'test_dst_dir';

		FileHelper::copyDirectory($srcDirName, $dstDirName);

		$this->assertTrue(file_exists($dstDirName), 'Destination directory does not exist!');
		foreach ($files as $name => $content) {
			$fileName = $dstDirName . DIRECTORY_SEPARATOR . $name;
			$this->assertTrue(file_exists($fileName), 'Directory file is missing!');
			$this->assertEquals($content, file_get_contents($fileName), 'Incorrect file content!');
		}
	}
}