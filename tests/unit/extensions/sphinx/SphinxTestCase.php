<?php

namespace yiiunit\extensions\sphinx;

use yii\helpers\FileHelper;
use yii\sphinx\Connection;
use Yii;
use yiiunit\TestCase as TestCase;

/**
 * Base class for the Sphinx test cases.
 */
class SphinxTestCase extends TestCase
{
	/**
	 * @var array Sphinx connection configuration.
	 */
	protected $sphinxConfig = [
		'dsn' => 'mysql:host=127.0.0.1;port=9306;',
		'username' => '',
		'password' => '',
	];
	/**
	 * @var Connection
	 */
	protected $sphinx;

	public static function setUpBeforeClass()
	{
		static::loadClassMap();
	}

	protected function setUp()
	{
		parent::setUp();
		//$this->sphinxConfig = $this->getParam('sphinx');
		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
			$this->markTestSkipped('pdo and pdo_mysql extension are required.');
		}
		$this->mockApplication();
		static::loadClassMap();
	}

	protected function tearDown()
	{
		if ($this->sphinx) {
			$this->sphinx->close();
		}
		$this->destroyApplication();
	}

	/**
	 * Adds sphinx extension files to [[Yii::$classPath]],
	 * avoiding the necessity of usage Composer autoloader.
	 */
	protected static function loadClassMap()
	{
		$baseNameSpace = 'yii/sphinx';
		$basePath = realpath(__DIR__. '/../../../../extensions/sphinx/yii/sphinx');
		$files = FileHelper::findFiles($basePath);
		foreach ($files as $file) {
			$classRelativePath = str_replace($basePath, '', $file);
			$classFullName = str_replace(['/', '.php'], ['\\', ''], $baseNameSpace . $classRelativePath);
			Yii::$classMap[$classFullName] = $file;
		}
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @param bool $open whether to open and populate test database
	 * @return \yii\db\Connection
	 */
	public function getConnection($reset = true, $open = true)
	{
		if (!$reset && $this->sphinx) {
			return $this->sphinx;
		}
		$db = new \yii\db\Connection;
		$db->dsn = $this->sphinxConfig['dsn'];
		if (isset($this->sphinxConfig['username'])) {
			$db->username = $this->sphinxConfig['username'];
			$db->password = $this->sphinxConfig['password'];
		}
		if (isset($this->sphinxConfig['attributes'])) {
			$db->attributes = $this->sphinxConfig['attributes'];
		}
		if ($open) {
			$db->open();
			if (!empty($this->sphinxConfig['fixture'])) {
				$lines = explode(';', file_get_contents($this->sphinxConfig['fixture']));
				foreach ($lines as $line) {
					if (trim($line) !== '') {
						$db->pdo->exec($line);
					}
				}
			}
		}
		$this->sphinx = $db;
		return $db;
	}
}