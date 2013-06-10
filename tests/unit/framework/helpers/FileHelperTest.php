<?php

use yii\helpers\base\FileHelper;
use yii\test\TestCase;

/**
 * Unit test for [[yii\helpers\base\FileHelper]]
 * @see FileHelper
 */
class FileHelperTest extends TestCase
{
	/**
	 * @var string test files path.
	 */
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

	/**
	 * Creates test files structure,
	 * @param array $items file system objects to be created in format: objectName => objectContent
	 * Arrays specifies directories, other values - files.
	 * @param string $basePath structure base file path.
	 */
	protected function createFileStructure(array $items, $basePath = '') {
		if (empty($basePath)) {
			$basePath = $this->testFilePath;
		}
		foreach ($items as $name => $content) {
			$itemName = $basePath . DIRECTORY_SEPARATOR . $name;
			if (is_array($content)) {
				mkdir($itemName, 0777, true);
				$this->createFileStructure($content, $itemName);
			} else {
				file_put_contents($itemName, $content);
			}
		}
	}

	// Tests :

	public function testCopyDirectory()
	{
		$srcDirName = 'test_src_dir';
		$files = array(
			'file1.txt' => 'file 1 content',
			'file2.txt' => 'file 2 content',
		);
		$this->createFileStructure(array(
			$srcDirName => $files
		));

		$basePath = $this->testFilePath;
		$srcDirName = $basePath . DIRECTORY_SEPARATOR . $srcDirName;
		$dstDirName = $basePath . DIRECTORY_SEPARATOR . 'test_dst_dir';

		FileHelper::copyDirectory($srcDirName, $dstDirName);

		$this->assertTrue(file_exists($dstDirName), 'Destination directory does not exist!');
		foreach ($files as $name => $content) {
			$fileName = $dstDirName . DIRECTORY_SEPARATOR . $name;
			$this->assertTrue(file_exists($fileName), 'Directory file is missing!');
			$this->assertEquals($content, file_get_contents($fileName), 'Incorrect file content!');
		}
	}

	public function testRemoveDirectory()
	{
		$dirName = 'test_dir_for_remove';
		$this->createFileStructure(array(
			$dirName => array(
				'file1.txt' => 'file 1 content',
				'file2.txt' => 'file 2 content',
				'test_sub_dir' => array(
					'sub_dir_file_1.txt' => 'sub dir file 1 content',
					'sub_dir_file_2.txt' => 'sub dir file 2 content',
				),
			),
		));

		$basePath = $this->testFilePath;
		$dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;

		FileHelper::removeDirectory($dirName);

		$this->assertFalse(file_exists($dirName), 'Unable to remove directory!');
	}

	public function testFindFiles()
	{
		$dirName = 'test_dir';
		$this->createFileStructure(array(
			$dirName => array(
				'file_1.txt' => 'file 1 content',
				'file_2.txt' => 'file 2 content',
				'test_sub_dir' => array(
					'file_1_1.txt' => 'sub dir file 1 content',
					'file_1_2.txt' => 'sub dir file 2 content',
				),
			),
		));
		$basePath = $this->testFilePath;
		$dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;
		$expectedFiles = array(
			$dirName . DIRECTORY_SEPARATOR . 'file_1.txt',
			$dirName . DIRECTORY_SEPARATOR . 'file_2.txt',
			$dirName . DIRECTORY_SEPARATOR . 'test_sub_dir' . DIRECTORY_SEPARATOR . 'file_1_1.txt',
			$dirName . DIRECTORY_SEPARATOR . 'test_sub_dir' . DIRECTORY_SEPARATOR . 'file_1_2.txt',
		);

		$foundFiles = FileHelper::findFiles($dirName);
		sort($expectedFiles);
		$this->assertEquals($expectedFiles, $foundFiles);
	}
}