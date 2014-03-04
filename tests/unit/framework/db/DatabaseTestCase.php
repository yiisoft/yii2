<?php
namespace yiiunit\framework\db;

use yii\db\Connection;
use yiiunit\TestCase as TestCase;

abstract class DatabaseTestCase extends TestCase
{
	protected $database;
	protected $driverName = 'mysql';
	/**
	 * @var Connection
	 */
	protected $db;

	protected function setUp()
	{
		parent::setUp();
		$databases = $this->getParam('databases');
		$this->database = $databases[$this->driverName];
		$pdo_database = 'pdo_'.$this->driverName;

		if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
			$this->markTestSkipped('pdo and '.$pdo_database.' extension are required.');
		}
		$this->mockApplication();
	}

	protected function tearDown()
	{
		if ($this->db) {
			$this->db->close();
		}
		$this->destroyApplication();
	}

	/**
	 * @param boolean $reset whether to clean up the test database
	 * @param boolean $open whether to open and populate test database
	 * @return \yii\db\Connection
	 */
	public function getConnection($reset = true, $open = true)
	{
		if (!$reset && $this->db) {
			return $this->db;
		}
		$db = new \yii\db\Connection;
		$db->dsn = $this->database['dsn'];
		if (isset($this->database['username'])) {
			$db->username = $this->database['username'];
			$db->password = $this->database['password'];
		}
		if (isset($this->database['attributes'])) {
			$db->attributes = $this->database['attributes'];
		}
		if ($open) {
			$db->open();
			$lines = explode(';', file_get_contents($this->database['fixture']));
			foreach ($lines as $line) {
				if (trim($line) !== '') {
					$db->pdo->exec($line);
				}
			}
		}
		$this->db = $db;
		return $db;
	}
}
