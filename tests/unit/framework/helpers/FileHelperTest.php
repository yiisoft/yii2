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
		if (!file_exists($this->testFilePath)) {
			$this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
		}
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
		if (!empty($dirName) && is_dir($dirName)) {
			if ($handle = opendir($dirName)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != '.' && $entry != '..') {
						if (is_dir($dirName . DIRECTORY_SEPARATOR . $entry) === true) {
							$this->removeDir($dirName . DIRECTORY_SEPARATOR . $entry);
						} else {
							unlink($dirName . DIRECTORY_SEPARATOR . $entry);
						}
					}
				}
				closedir($handle);
				rmdir($dirName);
			}
		}
	}

	/**
	 * Get file permission mode.
	 * @param string $file file name.
	 * @return string permission mode.
	 */
	protected function getMode($file)
	{
		return substr(sprintf('%o', fileperms($file)), -4);
	}

	/**
	 * Creates test files structure,
	 * @param array $items file system objects to be created in format: objectName => objectContent
	 * Arrays specifies directories, other values - files.
	 * @param string $basePath structure base file path.
	 */
	protected function createFileStructure(array $items, $basePath = '')
	{
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

	/**
	 * Asserts that file has specific permission mode.
	 * @param integer $expectedMode expected file permission mode.
	 * @param string $fileName file name.
	 * @param string $message error message
	 */
	protected function assertFileMode($expectedMode, $fileName, $message='')
	{
		$expectedMode = sprintf('%o', $expectedMode);
		$this->assertEquals($expectedMode, $this->getMode($fileName), $message);
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

	/**
	 * @depends testCopyDirectory
	 */
	public function testCopyDirectoryPermissions()
	{
		if (substr(PHP_OS, 0, 3) == 'WIN') {
			$this->markTestSkipped("Can't reliably test it on Windows because fileperms() always return 0777.");
		}

		$srcDirName = 'test_src_dir';
		$subDirName = 'test_sub_dir';
		$fileName = 'test_file.txt';
		$this->createFileStructure(array(
			$srcDirName => array(
				$subDirName => array(),
				$fileName => 'test file content',
			),
		));

		$basePath = $this->testFilePath;
		$srcDirName = $basePath . DIRECTORY_SEPARATOR . $srcDirName;
		$dstDirName = $basePath . DIRECTORY_SEPARATOR . 'test_dst_dir';

		$dirMode = 0755;
		$fileMode = 0755;
		$options = array(
			'dirMode' => $dirMode,
			'fileMode' => $fileMode,
		);
		FileHelper::copyDirectory($srcDirName, $dstDirName, $options);

		$this->assertFileMode($dirMode, $dstDirName, 'Destination directory has wrong mode!');
		$this->assertFileMode($dirMode, $dstDirName . DIRECTORY_SEPARATOR . $subDirName, 'Copied sub directory has wrong mode!');
		$this->assertFileMode($fileMode, $dstDirName . DIRECTORY_SEPARATOR . $fileName, 'Copied file has wrong mode!');
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
		sort($foundFiles);
		$this->assertEquals($expectedFiles, $foundFiles);
	}

	/**
	 * @depends testFindFiles
	 */
	public function testFindFileFilter()
	{
		$dirName = 'test_dir';
		$passedFileName = 'passed.txt';
		$this->createFileStructure(array(
			$dirName => array(
				$passedFileName => 'passed file content',
				'declined.txt' => 'declined file content',
			),
		));
		$basePath = $this->testFilePath;
		$dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;

		$options = array(
			'filter' => function($path) use ($passedFileName) {
				return $passedFileName == basename($path);
			}
		);
		$foundFiles = FileHelper::findFiles($dirName, $options);
		$this->assertEquals(array($dirName . DIRECTORY_SEPARATOR . $passedFileName), $foundFiles);
	}

	/**
	 * @depends testFindFiles
	 */
	public function testFindFilesExclude()
	{
		$dirName = 'test_dir';
		$fileName = 'test_file.txt';
		$excludeFileName = 'exclude_file.txt';
		$this->createFileStructure(array(
			$dirName => array(
				$fileName => 'file content',
				$excludeFileName => 'exclude file content',
			),
		));
		$basePath = $this->testFilePath;
		$dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;

		$options = array(
			'except' => array($excludeFileName),
		);
		$foundFiles = FileHelper::findFiles($dirName, $options);
		$this->assertEquals(array($dirName . DIRECTORY_SEPARATOR . $fileName), $foundFiles);
	}

	/**
	 * @depends testFindFiles
	 */
	public function testFindFilesFileType()
	{
		$dirName = 'test_dir';
		$fileType = 'dat';
		$fileName = 'test_file.' . $fileType;
		$excludeFileName = 'exclude_file.txt';
		$this->createFileStructure(array(
			$dirName => array(
				$fileName => 'file content',
				$excludeFileName => 'exclude file content',
			),
		));
		$basePath = $this->testFilePath;
		$dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;

		$options = array(
			'fileTypes' => array($fileType),
		);
		$foundFiles = FileHelper::findFiles($dirName, $options);
		$this->assertEquals(array($dirName . DIRECTORY_SEPARATOR . $fileName), $foundFiles);
	}

	public function testMkdir() {
		$basePath = $this->testFilePath;

		$dirName = $basePath . DIRECTORY_SEPARATOR . 'test_dir';
		FileHelper::mkdir($dirName);
		$this->assertTrue(file_exists($dirName), 'Unable to create directory!');

		$dirName = $basePath . DIRECTORY_SEPARATOR . 'test_dir_level_1' . DIRECTORY_SEPARATOR . 'test_dir_level_2';
		FileHelper::mkdir($dirName, null, true);
		$this->assertTrue(file_exists($dirName), 'Unable to create directory recursively!');
	}

	public function testGetMimeTypeByExtension()
	{
		$magicFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'mime_type.php';
		$mimeTypeMap = array(
			'txa' => 'application/json',
			'txb' => 'another/mime',
		);
		$magicFileContent = '<?php return ' . var_export($mimeTypeMap, true) . ';';
		file_put_contents($magicFile, $magicFileContent);

		foreach ($mimeTypeMap as $extension => $mimeType) {
			$fileName = 'test.' . $extension;
			$this->assertNull(FileHelper::getMimeTypeByExtension($fileName));
			$this->assertEquals($mimeType, FileHelper::getMimeTypeByExtension($fileName, $magicFile));
		}
	}
}
