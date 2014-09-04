<?php

namespace yiiunit\extensions\gii;

use Yii;
use yiiunit\TestCase;

Yii::setAlias('@yii/gii', __DIR__ . '/../../../../extensions/gii');

/**
 * GiiTestCase is the base class for all gii related test cases
 */
class GiiTestCase extends TestCase
{
    protected $driverName = 'sqlite';

    public function setUp()
    {
        $databases = self::getParam('databases');

        $config = $databases[$this->driverName];
        $pdo_database = 'pdo_'.$this->driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            $this->markTestSkipped('pdo and '.$pdo_database.' extension are required.');
        }

        $this->mockApplication([
           'components' => [
               'db' => [
                   'class' => isset($config['class']) ? $config['class'] : 'yii\db\Connection',
                   'dsn' => $config['dsn'],
                   'username' => isset($config['username']) ? $config['username'] : null,
                   'password' => isset($config['password']) ? $config['password'] : null,
               ],
           ],
        ]);

        if(isset($config['fixture'])) {
            Yii::$app->db->open();
            $lines = explode(';', file_get_contents($config['fixture']));
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    Yii::$app->db->pdo->exec($line);
                }
            }
        }
    }
}