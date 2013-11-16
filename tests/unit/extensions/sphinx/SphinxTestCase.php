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
	 * @var Connection Sphinx connection instance.
	 */
	protected $sphinx;
	/**
	 * @var array Database connection configuration.
	 */
	protected $dbConfig = [
		'dsn' => 'mysql:host=127.0.0.1;',
		'username' => '',
		'password' => '',
	];
	/**
	 * @var \yii\db\Connection database connection instance.
	 */
	protected $db;

	public static function setUpBeforeClass()
	{
		static::loadClassMap();
	}

	protected function setUp()
	{
		parent::setUp();
		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
			$this->markTestSkipped('pdo and pdo_mysql extension are required.');
		}
		$config = $this->getParam('sphinx');
		if (!empty($config)) {
			$this->sphinxConfig = $config['sphinx'];
			$this->dbConfig = $config['db'];
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
		$basePath = realpath(__DIR__. '/../../../../extensions/sphinx');
		$files = FileHelper::findFiles($basePath);
		foreach ($files as $file) {
			$classRelativePath = str_replace($basePath, '', $file);
			$classFullName = str_replace(['/', '.php'], ['\\', ''], $baseNameSpace . $classRelativePath);
			Yii::$classMap[$classFullName] = $file;
		}
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @param bool $open whether to open test database
	 * @return \yii\sphinx\Connection
	 */
	public function getConnection($reset = false, $open = true)
	{
		if (!$reset && $this->sphinx) {
			return $this->sphinx;
		}
		$db = new Connection;
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
		}
		$this->sphinx = $db;
		return $db;
	}

	/**
	 * Truncates the runtime index.
	 * @param string $indexName index name.
	 */
	protected function truncateRuntimeIndex($indexName)
	{
		if ($this->sphinx) {
			$this->sphinx->createCommand('TRUNCATE RTINDEX ' . $indexName)->execute();
		}
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @param bool $open whether to open and populate test database
	 * @return \yii\db\Connection
	 */
	public function getDbConnection($reset = true, $open = true)
	{
		if (!$reset && $this->db) {
			return $this->db;
		}
		$db = new \yii\db\Connection;
		$db->dsn = $this->dbConfig['dsn'];
		if (isset($this->dbConfig['username'])) {
			$db->username = $this->dbConfig['username'];
			$db->password = $this->dbConfig['password'];
		}
		if (isset($this->dbConfig['attributes'])) {
			$db->attributes = $this->dbConfig['attributes'];
		}
		if ($open) {
			$db->open();
			if (!empty($this->dbConfig['fixture'])) {
				$lines = explode(';', file_get_contents($this->dbConfig['fixture']));
				foreach ($lines as $line) {
					if (trim($line) !== '') {
						$db->pdo->exec($line);
					}
				}
			}
		}
		$this->db = $db;
		return $db;
	}
}