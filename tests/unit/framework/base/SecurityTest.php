<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yiiunit\TestCase;
use yii\base\Security;

/**
 * @group base
 */
class SecurityTest extends TestCase
{
    /**
     * @var Security
     */
    protected $security;

    protected function setUp()
    {
        parent::setUp();
        $this->security = new Security();
        $this->security->derivationIterations = 100; // speed up test running
    }

    // Tests :

    public function testPasswordHash()
    {
        $password = 'secret';
        $hash = $this->security->generatePasswordHash($password);
        $this->assertTrue($this->security->validatePassword($password, $hash));
        $this->assertFalse($this->security->validatePassword('test', $hash));
    }

    public function testHashData()
    {
        $data = 'known data';
        $key = 'secret';
        $hashedData = $this->security->hashData($data, $key);
        $this->assertFalse($data === $hashedData);
        $this->assertEquals($data, $this->security->validateData($hashedData, $key));
        $hashedData[strlen($hashedData) - 1] = 'A';
        $this->assertFalse($this->security->validateData($hashedData, $key));
    }

    public function testEncrypt()
    {
        $data = 'known data';
        $key = 'secret';
        $encryptedData = $this->security->encrypt($data, $key);
        $this->assertFalse($data === $encryptedData);
        $decryptedData = $this->security->decrypt($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);
    }
}
