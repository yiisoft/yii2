<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yiiunit\TestCase;

/**
 * @group base
 */
class SecurityTest extends TestCase
{
    /**
     * @var ExposedSecurity
     */
    protected $security;

    protected function setUp()
    {
        parent::setUp();
        $this->security = new ExposedSecurity();
        $this->security->derivationIterations = 1000; // speed up test running
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

    public function testEncryptByPassword()
    {
        $data = 'known data';
        $key = 'secret';

        $encryptedData = $this->security->encryptByPassword($data, $key);
        $this->assertFalse($data === $encryptedData);
        $decryptedData = $this->security->decryptByPassword($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);

        $tampered = $encryptedData;
        $tampered[20] = ~$tampered[20];
        $decryptedData = $this->security->decryptByPassword($tampered, $key);
        $this->assertTrue(false === $decryptedData);
    }

    public function testEncryptByKey()
    {
        $data = 'known data';
        $key = $this->security->generateRandomKey(80);

        $encryptedData = $this->security->encryptByKey($data, $key);
        $this->assertFalse($data === $encryptedData);
        $decryptedData = $this->security->decryptByKey($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);

        $encryptedData = $this->security->encryptByKey($data, $key, $key);
        $decryptedData = $this->security->decryptByKey($encryptedData, $key, $key);
        $this->assertEquals($data, $decryptedData);

        $tampered = $encryptedData;
        $tampered[20] = ~$tampered[20];
        $decryptedData = $this->security->decryptByKey($tampered, $key);
        $this->assertTrue(false === $decryptedData);

        $decryptedData = $this->security->decryptByKey($encryptedData, $key, $key . "\0");
        $this->assertTrue(false === $decryptedData);
    }

    public function testGenerateRandomKey()
    {
        $length = 21;
        $key = $this->security->generateRandomKey($length);
        $this->assertEquals($length, strlen($key));
    }

    public function testGenerateRandomString()
    {
        $length = 21;
        $key = $this->security->generateRandomString($length);
        $this->assertEquals($length, strlen($key));
        $this->assertEquals(1, preg_match('/[A-Za-z0-9_-]+/', $key));
    }

    public function dataProviderPbkdf2()
    {
        return [
            [
                'sha1',
                'password',
                'salt',
                1,
                20,
                '0c60c80f961f0e71f3a9b524af6012062fe037a6'
            ],
            [
                'sha1',
                'password',
                'salt',
                2,
                20,
                'ea6c014dc72d6f8ccd1ed92ace1d41f0d8de8957'
            ],
            [
                'sha1',
                'password',
                'salt',
                4096,
                20,
                '4b007901b765489abead49d926f721d065a429c1'
            ],
            [
                'sha1',
                'password',
                'salt',
                16777216,
                20,
                'eefe3d61cd4da4e4e9945b3d6ba2158c2634e984'
            ],
            [
                'sha1',
                'passwordPASSWORDpassword',
                'saltSALTsaltSALTsaltSALTsaltSALTsalt',
                4096,
                25,
                '3d2eec4fe41c849b80c8d83662c0e44a8b291a964cf2f07038'
            ],
            [
                'sha1',
                "pass\0word",
                "sa\0lt",
                4096,
                16,
                '56fa6aa75548099dcc37d7f03425e0c3'
            ],
            [
                'sha256',
                'password',
                'salt',
                1,
                20,
                '120fb6cffcf8b32c43e7225256c4f837a86548c9'
            ],
            [
                'sha256',
                "pass\0word",
                "sa\0lt",
                4096,
                32,
                '89b69d0516f829893c696226650a86878c029ac13ee276509d5ae58b6466a724'
            ],
            [
                'sha256',
                'passwordPASSWORDpassword',
                'saltSALTsaltSALTsaltSALTsaltSALTsalt',
                4096,
                40,
                '348c89dbcbd32b2f32d814b8116e84cf2b17347ebc1800181c4e2a1fb8dd53e1c635518c7dac47e9'
            ],
        ];
    }

    /**
     * @dataProvider dataProviderPbkdf2
     *
     * @param string $hash
     * @param string $password
     * @param string $salt
     * @param int $iterations
     * @param int $length
     * @param string $okm
     */
    public function testPbkdf2($hash, $password, $salt, $iterations, $length, $okm)
    {
        $this->security->derivationIterations = $iterations;
        $DK = $this->security->pbkdf2($hash, $password, $salt, $iterations, $length);
        $this->assertEquals($okm, bin2hex($DK));
    }

    public function dataProviderDeriveKey()
    {
        // See Appendix A in https://tools.ietf.org/html/rfc5869
        return [
            [
                'Hash' => 'sha256',
                'IKM' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
                'salt' => '000102030405060708090a0b0c',
                'info' => 'f0f1f2f3f4f5f6f7f8f9',
                'L' => 42,
                'PRK' => '077709362c2e32df0ddc3f0dc47bba6390b6c73bb50f9c3122ec844ad7c2b3e5',
                'OKM' => '3cb25f25faacd57a90434f64d0362f2a2d2d0a90cf1a5a4c5db02d56ecc4c5bf34007208d5b887185865',
            ],
            [
                'Hash' => 'sha256',
                'IKM' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f404142434445464748494a4b4c4d4e4f',
                'salt' => '606162636465666768696a6b6c6d6e6f707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e8f909192939495969798999a9b9c9d9e9fa0a1a2a3a4a5a6a7a8a9aaabacadaeaf',
                'info' => 'b0b1b2b3b4b5b6b7b8b9babbbcbdbebfc0c1c2c3c4c5c6c7c8c9cacbcccdcecfd0d1d2d3d4d5d6d7d8d9dadbdcdddedfe0e1e2e3e4e5e6e7e8e9eaebecedeeeff0f1f2f3f4f5f6f7f8f9fafbfcfdfeff',
                'L' => 82,
                'PRK' => '06a6b88c5853361a06104c9ceb35b45cef760014904671014a193f40c15fc244',
                'OKM' => 'b11e398dc80327a1c8e7f78c596a49344f012eda2d4efad8a050cc4c19afa97c59045a99cac7827271cb41c65e590e09da3275600c2f09b8367793a9aca3db71cc30c58179ec3e87c14c01d5c1f3434f1d87',
            ],
            [
                'Hash' => 'sha256',
                'IKM' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
                'salt' => '',
                'info' => '',
                'L' => 42,
                'PRK' => '19ef24a32c717b167f33a91d6f648bdf96596776afdb6377ac434c1c293ccb04',
                'OKM' => '8da4e775a563c18f715f802a063c5a31b8a11f5c5ee1879ec3454e5f3c738d2d9d201395faa4b61a96c8',
            ],
            [
                'Hash' => 'sha1',
                'IKM' => '0b0b0b0b0b0b0b0b0b0b0b',
                'salt' => '000102030405060708090a0b0c',
                'info' => 'f0f1f2f3f4f5f6f7f8f9',
                'L' => 42,
                'PRK' => '9b6c18c432a7bf8f0e71c8eb88f4b30baa2ba243',
                'OKM' => '085a01ea1b10f36933068b56efa5ad81a4f14b822f5b091568a9cdd4f155fda2c22e422478d305f3f896',
            ],
            [
                'Hash' => 'sha1',
                'IKM' => '000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f404142434445464748494a4b4c4d4e4f',
                'salt' => '606162636465666768696a6b6c6d6e6f707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e8f909192939495969798999a9b9c9d9e9fa0a1a2a3a4a5a6a7a8a9aaabacadaeaf',
                'info' => 'b0b1b2b3b4b5b6b7b8b9babbbcbdbebfc0c1c2c3c4c5c6c7c8c9cacbcccdcecfd0d1d2d3d4d5d6d7d8d9dadbdcdddedfe0e1e2e3e4e5e6e7e8e9eaebecedeeeff0f1f2f3f4f5f6f7f8f9fafbfcfdfeff',
                'L' => 82,
                'PRK' => '8adae09a2a307059478d309b26c4115a224cfaf6',
                'OKM' => '0bd770a74d1160f7c9f12cd5912a06ebff6adcae899d92191fe4305673ba2ffe8fa3f1a4e5ad79f3f334b3b202b2173c486ea37ce3d397ed034c7f9dfeb15c5e927336d0441f4c4300e2cff0d0900b52d3b4',
            ],
            [
                'Hash' => 'sha1',
                'IKM' => '0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b',
                'salt' => '',
                'info' => '',
                'L' => 42,
                'PRK' => 'da8c8a73c7fa77288ec6f5e7c297786aa0d32d01',
                'OKM' => '0ac1af7002b3d761d1e55298da9d0506b9ae52057220a306e07b6b87e8df21d0ea00033de03984d34918',
            ],
            [
                'Hash' => 'sha1',
                'IKM' => '0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c0c',
                'salt' => null,
                'info' => '',
                'L' => 42,
                'PRK' => '2adccada18779e7c2077ad2eb19d3f3e731385dd',
                'OKM' => '2c91117204d745f3500d636a62f64f0ab3bae548aa53d423b0d1f27ebba6f5e5673a081d70cce7acfc48',
            ]
        ];
    }

    /**
     * @dataProvider dataProviderDeriveKey
     *
     * @param string $hash
     * @param string $ikm
     * @param string $salt
     * @param string $info
     * @param int $l
     * @param string $prk
     * @param string $okm
     */
    public function testHkdf($hash, $ikm, $salt, $info, $l, $prk, $okm)
    {
        $dk = $this->security->hkdf($hash, hex2bin($ikm), hex2bin($salt), hex2bin($info), $l);
        $this->assertEquals($okm, bin2hex($dk));
    }

    public function dataProviderCompareStrings()
    {
        return [
            ["", ""],
            [false, ""],
            [null, ""],
            [0, ""],
            [0.00, ""],
            ["", null],
            ["", false],
            ["", 0],
            ["", "\0"],
            ["\0", ""],
            ["\0", "\0"],
            ["0", "\0"],
            [0, "\0"],
            ["user", "User"],
            ["password", "password"],
            ["password", "passwordpassword"],
            ["password1", "password"],
            ["password", "password2"],
            ["", "password"],
            ["password", ""],
        ];
    }

    /**
     * @dataProvider dataProviderCompareStrings
     *
     * @param $expected
     * @param $actual
     */
    public function testCompareStrings($expected, $actual)
    {
        $this->assertEquals(strcmp($expected, $actual) === 0, $this->security->compareString($expected, $actual));
    }
}