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
    private $_db;

    protected function setUp()
    {
        parent::setUp();
        $databases = self::getParam('databases');
        $this->database = $databases[$this->driverName];
        $pdo_database = 'pdo_'.$this->driverName;
        if ($this->driverName === 'oci') {
            $pdo_database = 'oci8';
        }

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            $this->markTestSkipped('pdo and '.$pdo_database.' extension are required.');
        }
        $this->mockApplication();
    }

    protected function tearDown()
    {
        if ($this->_db) {
            $this->_db->close();
        }
        $this->destroyApplication();
    }

    /**
     * @param  boolean $reset whether to clean up the test database
     * @param  boolean $open  whether to open and populate test database
     * @return \yii\db\Connection
     */
    public function getConnection($reset = true, $open = true)
    {
        if (!$reset && $this->_db) {
            return $this->_db;
        }
        $config = $this->database;
        if (isset($config['fixture'])) {
            $fixture = $config['fixture'];
            unset($config['fixture']);
        } else {
            $fixture = null;
        }
        try {
            $this->_db = $this->prepareDatabase($config, $fixture, $open);
        } catch (\Exception $e) {
            $this->markTestSkipped("Something wrong when preparing database: " . $e->getMessage());
        }
        return $this->_db;
    }

    public function prepareDatabase($config, $fixture, $open = true)
    {
        if (!isset($config['class'])) {
            $config['class'] = 'yii\db\Connection';
        }
        /* @var $db \yii\db\Connection */
        $db = \Yii::createObject($config);
        if (!$open) {
            return $db;
        }
        $db->open();
        if ($fixture !== null) {
            if ($this->driverName === 'oci') {
                list($drops, $creates) = explode('/* STATEMENTS */', file_get_contents($fixture), 2);
                list($statements, $triggers, $data) = explode('/* TRIGGERS */', $creates, 3);
                $lines = array_merge(explode('--', $drops), explode(';', $statements), explode('/', $triggers), explode(';', $data));
            } else {
                $lines = explode(';', file_get_contents($fixture));
            }
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $db->pdo->exec($line);
                }
            }
        }
        return $db;
    }
}
