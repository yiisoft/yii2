<?php

namespace yiiunit\extensions\redis;

/**
 * @group redis
 */
class RedisConnectionTest extends RedisTestCase
{
    /**
     * test connection to redis and selection of db
     */
    public function testConnect()
    {
        $db = $this->getConnection(false);
        $db->open();
        $this->assertTrue($db->ping());
        $db->set('YIITESTKEY', 'YIITESTVALUE');
        $db->close();

        $db = $this->getConnection(false);
        $db->database = 0;
        $db->open();
        $this->assertEquals('YIITESTVALUE', $db->get('YIITESTKEY'));
        $db->close();

        $db = $this->getConnection(false);
        $db->database = 1;
        $db->open();
        $this->assertNull($db->get('YIITESTKEY'));
        $db->close();
    }

    public function keyValueData()
    {
        return [
            [123],
            [-123],
            [0],
            ['test'],
            ["test\r\ntest"],
            [''],
        ];
    }

    /**
     * @dataProvider keyValueData
     */
    public function testStoreGet($data)
    {
        $db = $this->getConnection(true);

        $db->set('hi', $data);
        $this->assertEquals($data, $db->get('hi'));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/4745
     */
    public function testReturnType()
    {
        $redis = $this->getConnection();
        $redis->executeCommand('SET',['key1','val1']);
        $redis->executeCommand('HMSET',['hash1','hk3','hv3','hk4','hv4']);
        $redis->executeCommand('RPUSH',['newlist2','tgtgt','tgtt','44',11]);
        $redis->executeCommand('SADD',['newset2','segtggttval','sv1','sv2','sv3']);
        $redis->executeCommand('ZADD',['newz2',2,'ss',3,'pfpf']);
        $allKeys = $redis->executeCommand('KEYS',['*']);
        sort($allKeys);
        $this->assertEquals(['hash1', 'key1', 'newlist2', 'newset2', 'newz2'], $allKeys);
        $expected = [
            'hash1' => 'hash',
            'key1' => 'string',
            'newlist2' => 'list',
            'newset2' => 'set',
            'newz2' => 'zset',
        ];
        foreach($allKeys as $key) {
            $this->assertEquals($expected[$key], $redis->executeCommand('TYPE',[$key]));
        }
    }
}
