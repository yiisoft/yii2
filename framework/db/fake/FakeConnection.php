<?php

namespace yii\db\fake;

use yii\db\Connection;

class FakeConnection extends \yii\db\Connection
{
    protected $proxyConnection;

    /**
     * FakeConnection constructor.
     *
     * @param Connection $connection
     * @param array $config
     */
    public function __construct($connection, array $config = [])
    {
        $this->proxyConnection = $connection;

        parent::__construct($config);
    }

    public $commandClass = 'yii\db\fake\FakeCommand';
    public $loggerClass = 'yii\db\fake\FakeConnectionLogger';

    public function getSchema()
    {
        return $this->proxyConnection->getSchema();
    }

    /**
     * @return \yii\db\ConnectionLoggerInterface|FakeConnectionLoggerInterface
     */
    public function getLogger()
    {
        return parent::getLogger();
    }

    public function open()
    {
        return true;
    }

    public function close()
    {
        return true;
    }
}
