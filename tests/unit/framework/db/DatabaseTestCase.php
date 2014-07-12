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
        $databases = self::getParam('databases');
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
     * @param  boolean            $reset whether to clean up the test database
     * @param  boolean            $open  whether to open and populate test database
     * @return \yii\db\Connection
     */
    public function getConnection($reset = true, $open = true)
    {
        if (!$reset && $this->db) {
            return $this->db;
        }
        $config = $this->database;
        if (isset($config['fixture'])) {
            $fixture = $config['fixture'];
            unset($config['fixture']);
        } else {
            $fixture = null;
        }
        return $this->db = $this->prepareDatabase($config, $fixture);
    }

    public function prepareDatabase($config, $fixture)
    {
        if (!isset($config['class'])) {
            $config['class'] = 'yii\db\Connection';
        }
        /* @var $db \yii\db\Connection */
        $db = \Yii::createObject($config);
        $db->open();
        if ($fixture !== null) {
            $lines = explode(';', file_get_contents($fixture));
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $db->pdo->exec($line);
                }
            }
        }
        return $db;
    }
}
