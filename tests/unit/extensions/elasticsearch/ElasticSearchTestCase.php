<?php

namespace yiiunit\extensions\elasticsearch;

use Yii;
use yii\elasticsearch\Connection;
use yiiunit\TestCase;

Yii::setAlias('@yii/elasticsearch', __DIR__ . '/../../../../extensions/elasticsearch');

/**
 * ElasticSearchTestCase is the base class for all elasticsearch related test cases
 */
class ElasticSearchTestCase extends TestCase
{
    protected function setUp()
    {
        $this->mockApplication();

        $databases = self::getParam('databases');
        $params = isset($databases['elasticsearch']) ? $databases['elasticsearch'] : null;
        if ($params === null || !isset($params['dsn'])) {
            $this->markTestSkipped('No elasticsearch server connection configured.');
        }
        $dsn = explode('/', $params['dsn']);
        $host = $dsn[2];
        if (strpos($host, ':')===false) {
            $host .= ':9200';
        }
        if (!@stream_socket_client($host, $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('No elasticsearch server running at ' . $params['dsn'] . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }

        parent::setUp();
    }

    /**
     * @param  boolean    $reset whether to clean up the test database
     * @return Connection
     */
    public function getConnection($reset = true)
    {
        $databases = self::getParam('databases');
        $params = isset($databases['elasticsearch']) ? $databases['elasticsearch'] : [];
        $db = new Connection();
        if ($reset) {
            $db->open();
        }

        return $db;
    }
}
