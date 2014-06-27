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
     * Data provider for [[testPasswordHash()]]
     * @return array test data
     */
    public function dataProviderPasswordHash()
    {
        return [
            [
                'crypt',
                false
            ],
            [
                'password_hash',
                !function_exists('password_hash')
            ],
        ];
    }

    /**
     * @dataProvider dataProviderPasswordHash
     *
     * @param string $passwordHashStrategy
     * @param boolean $isSkipped
     */
    public function testPasswordHash($passwordHashStrategy, $isSkipped)
    {
        if ($isSkipped) {
            $this->markTestSkipped("Unable to test '{$passwordHashStrategy}' password hash strategy");
            return;
        }
        $this->security->passwordHashStrategy = $passwordHashStrategy;

        $password = 'secret';
        $hash = $this->security->generatePasswordHash($password);
        $this->assertTrue($this->security->validatePassword($password, $hash));
        $this->assertFalse($this->security->validatePassword('test', $hash));
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
                true,
                false,
            ],
            [
                'hmac',
                false,
                false,
            ],
            [
                'pbkdf2',
                true,
                !function_exists('hash_pbkdf2')
            ],
            [
                'pbkdf2',
                false,
                !function_exists('hash_pbkdf2')
            ],
        ];
    }

    /**
     * @dataProvider dataProviderEncrypt
     *
     * @param string $deriveKeyStrategy
     * @param boolean $useDeriveKeyUniqueSalt
     * @param boolean $isSkipped
     */
    public function testEncrypt($deriveKeyStrategy, $useDeriveKeyUniqueSalt, $isSkipped)
    {
        if ($isSkipped) {
            $this->markTestSkipped("Unable to test '{$deriveKeyStrategy}' derive key strategy");
            return;
        }
        $this->security->deriveKeyStrategy = $deriveKeyStrategy;
        $this->security->useDeriveKeyUniqueSalt = $useDeriveKeyUniqueSalt;

        $data = 'known data';
        $key = 'secret';
        $encryptedData = $this->security->encrypt($data, $key);
        $this->assertFalse($data === $encryptedData);
        $decryptedData = $this->security->decrypt($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);
    }

    public function testGetSecretKey()
    {
        $this->security->autoGenerateSecretKey = false;
        $keyName = 'testGet';
        $keyValue = 'testGetValue';
        $this->security->secretKeys = [
            $keyName => $keyValue
        ];
        $this->assertEquals($keyValue, $this->security->getSecretKey($keyName));

        $this->setExpectedException('yii\base\InvalidParamException');
        $this->security->getSecretKey('notExistingKey');
    }

    /*public function testGenerateSecretKey()
    {
        $this->security->autoGenerateSecretKey = true;
        $keyValue = $this->security->getSecretKey('test');
        $this->assertNotEmpty($keyValue);
    }*/
}
