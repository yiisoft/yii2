<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yiiunit\TestCase;
use yii\helpers\Security;

/**
 * @group helpers
 */
class SecurityTest extends TestCase
{
    public function testPasswordHash()
    {
        $password = 'secret';
        $hash = Security::generatePasswordHash($password);
        $this->assertTrue(Security::validatePassword($password, $hash));
        $this->assertFalse(Security::validatePassword('test', $hash));
    }

    public function testHashData()
    {
        $data = 'known data';
        $key = 'secret';
        $hashedData = Security::hashData($data, $key);
        $this->assertFalse($data === $hashedData);
        $this->assertEquals($data, Security::validateData($hashedData, $key));
        $hashedData[strlen($hashedData) - 1] = 'A';
        $this->assertFalse(Security::validateData($hashedData, $key));
    }

    public function testEncrypt()
    {
        $data = 'known data';
        $key = 'secret';
        $encryptedData = Security::encrypt($data, $key);
        $this->assertFalse($data === $encryptedData);
        $decryptedData = Security::decrypt($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);
    }
}
