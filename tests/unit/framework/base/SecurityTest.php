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

    /**
     * Data provider for [[testEncrypt()]]
     * @return array test data
     */
    public function dataProviderEncrypt()
    {
        return [
            [
                'hmac',
                false
            ],
            [
                'pbkdf2',
                !function_exists('hash_pbkdf2')
            ],
        ];
    }

    /**
     * @dataProvider dataProviderEncrypt
     *
     * @param string $deriveKeyStrategy
     * @param boolean $isSkipped
     */
    public function testEncrypt($deriveKeyStrategy, $isSkipped)
    {
        if ($isSkipped) {
            $this->markTestSkipped("Unable to test '{$deriveKeyStrategy}' derive key strategy");
            return;
        }
        $this->security->deriveKeyStrategy = $deriveKeyStrategy;

        $data = 'known data';
        $key = 'secret';
        $encryptedData = $this->security->encrypt($data, $key);
        $this->assertFalse($data === $encryptedData);
        $decryptedData = $this->security->decrypt($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);
    }
}
