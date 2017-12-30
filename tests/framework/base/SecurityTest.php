<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base {

    /**
     * emulate availability of functions, to test different branches of Security class
     * where different execution paths are chosen based on calling function_exists.
     *
     * This function overrides function_exists from the root namespace in yii\base.
     * @param string $name
     */
    function function_exists($name)
    {
        if (isset(\yiiunit\framework\base\SecurityTest::$functions[$name])) {
            return \yiiunit\framework\base\SecurityTest::$functions[$name];
        }

        return \function_exists($name);
    }
    /**
     * Emulate chunked reading of fread(), to test different branches of Security class
     * where different execution paths are chosen based on the return value of fopen/fread.
     *
     * This function overrides fopen and fread from the root namespace in yii\base.
     * @param string $filename
     * @param mixed $mode
     */
    function fopen($filename, $mode)
    {
        if (\yiiunit\framework\base\SecurityTest::$fopen !== null) {
            return \yiiunit\framework\base\SecurityTest::$fopen;
        }

        return \fopen($filename, $mode);
    }
    function fread($handle, $length)
    {
        if (\yiiunit\framework\base\SecurityTest::$fread !== null) {
            return \yiiunit\framework\base\SecurityTest::$fread;
        }
        if (\yiiunit\framework\base\SecurityTest::$fopen !== null) {
            return $length < 8 ? \str_repeat('s', $length) : 'test1234';
        }

        return \fread($handle, $length);
    }
} // closing namespace yii\base;

namespace yiiunit\framework\base {

use yii\base\Security;
use yiiunit\TestCase;

/**
 * @group base
 */
class SecurityTest extends TestCase
{
    const CRYPT_VECTORS = 'old';

    /**
     * @var array set of functions for which a fake return value for `function_exists()` is provided.
     */
    public static $functions = [];
    /**
     * @var resource|false|null fake return value for fopen() in \yii\base namespace. Normal behavior if this is null.
     */
    public static $fopen;
    public static $fread;

    /**
     * @var ExposedSecurity
     */
    protected $security;

    protected function setUp()
    {
        static::$functions = [];
        static::$fopen = null;
        static::$fread = null;
        parent::setUp();
        $this->security = new ExposedSecurity();
        $this->security->derivationIterations = 1000; // speed up test running
    }

    protected function tearDown()
    {
        static::$functions = [];
        static::$fopen = null;
        static::$fread = null;
        parent::tearDown();
    }

    // Tests :

    public function testHashData()
    {
        $data = 'known data';
        $key = 'secret';
        $hashedData = $this->security->hashData($data, $key);
        $this->assertNotSame($data, $hashedData);
        $this->assertEquals($data, $this->security->validateData($hashedData, $key));
        $hashedData[strlen($hashedData) - 1] = 'A';
        $this->assertFalse($this->security->validateData($hashedData, $key));
    }

    public function testPasswordHash()
    {
        $this->security->passwordHashCost = 4;  // minimum blowfish's value is enough for tests

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
        $this->assertNotSame($data, $encryptedData);
        $decryptedData = $this->security->decryptByPassword($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);

        $tampered = $encryptedData;
        $tampered[20] = ~$tampered[20];
        $decryptedData = $this->security->decryptByPassword($tampered, $key);
        $this->assertFalse($decryptedData);
    }

    public function testEncryptByKey()
    {
        $data = 'known data';
        $key = $this->security->generateRandomKey(80);

        $encryptedData = $this->security->encryptByKey($data, $key);
        $this->assertNotSame($data, $encryptedData);
        $decryptedData = $this->security->decryptByKey($encryptedData, $key);
        $this->assertEquals($data, $decryptedData);

        $encryptedData = $this->security->encryptByKey($data, $key, $key);
        $decryptedData = $this->security->decryptByKey($encryptedData, $key, $key);
        $this->assertEquals($data, $decryptedData);

        $tampered = $encryptedData;
        $tampered[20] = ~$tampered[20];
        $decryptedData = $this->security->decryptByKey($tampered, $key);
        $this->assertFalse($decryptedData);

        $decryptedData = $this->security->decryptByKey($encryptedData, $key, $key . "\0");
        $this->assertFalse($decryptedData);
    }

    /**
     * Generates test vectors like this: `[key/password, plaintext, ciphertext]`.
     *
     * The output can then be used for testing compatibility of data encrypted in one
     * version of Yii and decrypted in another.
     */
    public function notestGenerateVectors()
    {
        $bin1024 =
            'badec0c7d9ca734e161a1df6ca4daa8cdbf6b3bbb60ec404b47a23226ec266b1
            3837ffc969e9c23e2bbba72facb491a6a3271193a35026a9ebc93698d689bf7b
            84fc384f544cc5d71c2945c8c48ae6348c753322fcaf75171b7d8f1e178e8545
            3d5c79f03bae6d9705cabbe7004ec81e188812a66313297fcf5d4c61a48614d2
            1b5379fae60736f88bc737257bc1cfbef7016108dd1f3a537aaa681544f5bb20
            c2d352212d6a2e460311ab7b93b611218b30c7402709bff89ecf814310c2db97
            81f4142a54d6d41f82c33208c188a023c70befd697b496efb7c7b8569474a9d5
            025a8ea8311c831b3de2b81800f28589b6aef537f6ada2e3d92a1ddd39cfd6b4
            8113c8b36be890b099ca4e08655db0e9b5c211e706af052b776924233321412c
            b1b540f08d88acd664c55854b05e31a15d4b0dc93b4514f62a88c3d4a451fe34
            74d2fbaee742f232959b5f6c2b71a8b6ad95a0d5362f39309aca710f37a4dae2
            228e27d35f4d3fc87ee6ece4538587fad7835f9803f5ca64dfd856ae767a3482
            c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c04
            0f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed9273644463b
            1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334aa4
            d998a599053cd477f54fc0af62a8ef75eb91096b153751b201b8374c4956508c
            6c82ea2ceb0265e74c96032787fe01139b578c3a14fd4e32a85195375b25da60
            4f21187ee01df6042971d7c2b9dd8b611527377f28f933559f6b2edfe7bd8896
            00627830bcdffcb884989013585cc6da726c7cc57c69e8664a244d234465f2a5
            e736512a65e563a2a726915b50247aff165190c75d151139f2b31211a3acf94e
            236d27b441fe64e88a443778359d57ba0fed8edbb01cc60a116f9599da4e47bf
            e96850ac12d1199a080f1b591c60eae613fba444f9fe31fd42ee333678532556
            07fbfeef02cdbb2d0a8d2329adc7fba22aa4cd124c6e66e0b0aca796ba6aeb69
            f8e76a9d1aaabe351bc42fefa6054977859b51158ba7143980d76db5b2cc6aec
            9f83eac5edf79357632fb60cb4811e71892436e582375860faf8d8c518ee33ca
            e08b18af7faa227d97196d7b041bcb366118751fbeaa92d80f86324ce8711cfd
            65005cad0ce30878f508eb1ea2735e212ef3e42a78467d4f31b483087c3c63a3
            2fa62f2ae92e2acdfc3cf21b3419acc347ff51ccb17161e829cc4a24965fd930
            b983d63153b9c13dd85340d7184bf01734d4357c1aabfbdc9711193ffa974832
            4f88cf801f35708db0901ffaa55415ca4334678633dda763b5fbfba7cfd51dc5
            79149ec9284a4db2349dbc5264d5fca7a72034261716d90a996cb57ba09f7d07
            29a2f10cc58eec98d86e9306a3fbf2e711046e86b5a86cc0eed7625be1a01ebf';
        $bin1024 = hex2bin(preg_replace('{\s+}', '', $bin1024));

        $inputs = [
            '0',
            '0123456789',
            '0123456789abcde',
            '0123456789abcdef',
            '0123456789abcdef0',
            mb_substr($bin1024, 7, 5, '8bit'),
            base64_encode(mb_substr($bin1024, 269, 11, '8bit')),
            mb_substr($bin1024, 383, 97, '8bit'),
            $bin1024,
        ];

        foreach (['Key', 'Password'] as $method) {
            $keygen = 'generateRandom' . ($method === 'Key' ? 'Key' : 'String');
            $encrypt = 'encryptBy' . $method;
            $decrypt = 'decryptBy' . $method;

            foreach ($inputs as $data) {
                $key = $this->security->$keygen(16);
                $encrypted = $this->security->$encrypt($data, $key);

                $keyHex = $method === 'Key' ? bin2hex($key) : $key;
                $dataHex = trim(chunk_split(bin2hex($data), 64, "\n\t"));
                $encryptedHex = trim(chunk_split(bin2hex($encrypted), 64, "\n\t"));

                echo <<<TEXT
[
    '$keyHex',
    '$dataHex',
    '$encryptedHex',
],

TEXT;

                $key2 = $method === 'Key' ? hex2bin(preg_replace('{\s+}', '', $keyHex)) : $key;
                $data2 = hex2bin(preg_replace('{\s+}', '', $dataHex));
                $encrypted2 = hex2bin(preg_replace('{\s+}', '', $encryptedHex));

                $this->assertEquals($data, $this->security->$decrypt($encrypted2, $key2));
                $this->assertEquals($data2, $this->security->$decrypt($encrypted2, $key));
            }
        }
    }

    public function dataProviderEncryptByKeyCompat()
    {
        // these ciphertexts generated using Yii 2.0.2 which is based on mcrypt.
        $mcrypt = [
            [
                'b86b7529a525d148cc73798da528d8a0',
                '30',
                '4fb4765bea6beb208ba9395bf6d7497f37626463323730396362366338376536
                3236656535613035373835323061343031613764393062353439623834353135
                376435383635393065393636656663378807b00020ea10101bbfc2ef0dfee082
                fcbc1927daee7e6d061bc7a7c8d8e877',
            ],
            [
                '7e214bb6b27108918c007c0f9e1bdd8a',
                '30313233343536373839',
                '98981d39f0c985aca577e2eb9472059339623332343335646634613630616335
                6133323639663336633533313032353164393562303434623533326331653561
                6566346561643066643331393437623366e93fe0ef0a220ef1228be9f9195475
                eb303b841b6371841126c13ed419d931',
            ],
            [
                'c139f67915206b7296bc6ab383a2786c',
                '303132333435363738396162636465',
                '5773028f2be0df720cb3be2cb989e9e161616430393538363437623862326264
                6436336133643738333866353339393932326432356433306630663062613331
                386666313033626661333065313031623d5e980deacb6369592c90e7afca6e6b
                06736654378cc93b2d238e6360969956',
            ],
            [
                '910c052157953194d552e3a3f0061ab7',
                '30313233343536373839616263646566',
                '3141c48cc0b73ee43d5cd9ae5c8a153f66343039333638396632653239623434
                3162616664636331316238336265626536336231653764663732396638336138
                34636438616265376138613539656164b004dd4ebddba049ae068936643dd475
                c07696b0cc173c2eb4c2d7bc3364845e6e3656b463b04e2271cb10824f1365ec',
            ],
            [
                '28d0ad09ce367ca8e0772bbeeea6c40d',
                '3031323334353637383961626364656630',
                '1f19013959a0985cf0235c4ff46a20c135626235633437383532343736613963
                3765646334666263323832643438663361323266303264383337613736363133
                666432373632343236313331656362354de2203149f8b4f348c06ea4e2eb94e5
                de4deaf1d198e14e7c448421002bdfa9ed979502d86750d70aebaa1c727ae79e',
            ],
            [
                'b658ac4b9fa13c3c1d40699aba886b7b',
                '4e161a1df6',
                '154015ebb822a55869721a0b1c22497762653266663264333631373938653539
                3465316466396535343966373738666663646232656163383733633739323334
                316363623562353462363133623134643d118216985ac7e3f6888a95552b6a67
                6ff70daf8dfb65b4d5f62a936b7f64d7',
            ],
            [
                'baeb43072f20af0f85c6252aa9f0d472',
                '5862447074634952357761764253733d',
                'beb6dcd0fb444cbf759a241bd0ea26e537336135333532656136653262303736
                3061656635326136376230333132663839626431366265363565643064616362
                39633830313066356330653466613232dd9cfe4f4495f34a971a25d881dd48a3
                12093a6307a87e4544e217cf9d39dd536a5882d2b9867abefef5c1dad127447e',
            ],
            [
                'd527f854e8f37c49c71b82c48d8b4775',
                '82c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c
                040f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed927364446
                3b1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334a
                a4',
                'baa84c2c05b2ac8efd9fc8f9824efb5d63306366393939626532323532646433
                6539346265383936336262303339336635333164383339326436376631353366
                6433616639653638393063376137626574bd7aa8c5f313e2d8b7a4ca4af35090
                9695845c9d62cd91c486307d3ae0701e4abd1fa69a255aab1e6ac2874fa1bc0a
                065340418a6669427bcdec751300ce7666a38d17d850f7dae4ea9567045356e8
                f347941226bf7b2d4dd3a21c8aa0b381f8ad06c3a55a7d2cb967ad148142c287
                f12a563f9cd2c6224e7efb8fdd130bb9',
            ],
            [
                '01025bfec21dda4342809cf20382de29',
                'badec0c7d9ca734e161a1df6ca4daa8cdbf6b3bbb60ec404b47a23226ec266b1
                3837ffc969e9c23e2bbba72facb491a6a3271193a35026a9ebc93698d689bf7b
                84fc384f544cc5d71c2945c8c48ae6348c753322fcaf75171b7d8f1e178e8545
                3d5c79f03bae6d9705cabbe7004ec81e188812a66313297fcf5d4c61a48614d2
                1b5379fae60736f88bc737257bc1cfbef7016108dd1f3a537aaa681544f5bb20
                c2d352212d6a2e460311ab7b93b611218b30c7402709bff89ecf814310c2db97
                81f4142a54d6d41f82c33208c188a023c70befd697b496efb7c7b8569474a9d5
                025a8ea8311c831b3de2b81800f28589b6aef537f6ada2e3d92a1ddd39cfd6b4
                8113c8b36be890b099ca4e08655db0e9b5c211e706af052b776924233321412c
                b1b540f08d88acd664c55854b05e31a15d4b0dc93b4514f62a88c3d4a451fe34
                74d2fbaee742f232959b5f6c2b71a8b6ad95a0d5362f39309aca710f37a4dae2
                228e27d35f4d3fc87ee6ece4538587fad7835f9803f5ca64dfd856ae767a3482
                c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c04
                0f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed9273644463b
                1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334aa4
                d998a599053cd477f54fc0af62a8ef75eb91096b153751b201b8374c4956508c
                6c82ea2ceb0265e74c96032787fe01139b578c3a14fd4e32a85195375b25da60
                4f21187ee01df6042971d7c2b9dd8b611527377f28f933559f6b2edfe7bd8896
                00627830bcdffcb884989013585cc6da726c7cc57c69e8664a244d234465f2a5
                e736512a65e563a2a726915b50247aff165190c75d151139f2b31211a3acf94e
                236d27b441fe64e88a443778359d57ba0fed8edbb01cc60a116f9599da4e47bf
                e96850ac12d1199a080f1b591c60eae613fba444f9fe31fd42ee333678532556
                07fbfeef02cdbb2d0a8d2329adc7fba22aa4cd124c6e66e0b0aca796ba6aeb69
                f8e76a9d1aaabe351bc42fefa6054977859b51158ba7143980d76db5b2cc6aec
                9f83eac5edf79357632fb60cb4811e71892436e582375860faf8d8c518ee33ca
                e08b18af7faa227d97196d7b041bcb366118751fbeaa92d80f86324ce8711cfd
                65005cad0ce30878f508eb1ea2735e212ef3e42a78467d4f31b483087c3c63a3
                2fa62f2ae92e2acdfc3cf21b3419acc347ff51ccb17161e829cc4a24965fd930
                b983d63153b9c13dd85340d7184bf01734d4357c1aabfbdc9711193ffa974832
                4f88cf801f35708db0901ffaa55415ca4334678633dda763b5fbfba7cfd51dc5
                79149ec9284a4db2349dbc5264d5fca7a72034261716d90a996cb57ba09f7d07
                29a2f10cc58eec98d86e9306a3fbf2e711046e86b5a86cc0eed7625be1a01ebf',
                '08ad33a166a9b39ba7522ccf5414123062643565356136373131666663323763
                3636343466313761356339656234343064626331343366346432343062626634
                3264393630336535353431613338653717747da97dd4d63e215dc8bb9ce231d5
                b3cc9c287dfadb1b5acce0d4da2816de16114665271204188d91b642cdfdb494
                66a89caa29a9139354f69c4c188d76d88d36d14004268f327c98f4736ba31728
                a46d8f50d1665bcf157e3164d756286eceac4ce81ba3ffd1b79ff0862f5f529b
                d44f0e2fbcb88c388095cb894fd4ce059ba8f5436f0a15b2fa9a40acd6645ad1
                c6f16365250715cb0552dcfc220e844ba67e2a1206b1725a6bbb8f0e87b7067f
                b51fc0226cd963bddf549227d5eaf5f996061d9c3b7e3306bd9be1114aed6199
                6e44ee1144bebfca9a1064d40419837e54006f54161dd028b2702faa5cde214c
                bc11ed1c77d4042b333e772473f4764fcdfcabdd0319f33d96ad0ab904cd2620
                31683afc4c96dc27fde0570aaedc922a552d46687a606bb579b6ab8050f418f8
                f6a3fc33ef5eeba8dbf54b5a90a1034ff8a68d9ea691d9a1d6b8f41376735bec
                6c0f541f927cf107f2184f96235a6fca6d1db013966d8527389f8a79f4afd7bb
                297c85d56f74faaa069679edc78b16b17ab8d72de3788f2ac2fe8a2e15ddfb3f
                14ffd9ef18c95acf340f702a974b580cac93928417650b8f9e7409619774e483
                af66b18ec7547f744099e2bb4125905e5dd553beca90d03fa7d7bba201ab97ac
                92506c305ff79afe9c99a73cb20b9048464beeb100df0ecfb40161f95dd8708a
                6fd09e1b166f1e27429b1ca50f990a45425dec25b2a29dcde4fd1c23b0d660ba
                10241bc20cdc59c3609a7d6b094538d56b878c62dca3a54fbc53d815ae0549f6
                978ea37a644add2bd2720b37a3502d683b0a8b9ce269bb3db8a44f3e8c3ba507
                7759de171e1d040dfe5c9a610e21e3b41d95403654122ed7b75111622f14bb50
                fd7a3e637bba73fc8c4e22702b52329c224860a9e04b38b8c23527c732a281dc
                f92103d71decf673b3fa83feab2c1937811e0b41163019e1b45e664d4acc570c
                2d4b24dc138285478c3898b0567c026a1651bbada0fb62ab5ce0d96e3601aec1
                c6f04ecb1b53f1d852893edb971703dcd919c2dd883d0851e2fc491cf9abf1b7
                c1652bac73a5a9e9bdbf164db948cc5b8299fd7f5535fa927130dc4e183ef35e
                bb08723625e4cf62f253111e6f016e6cfba2e9cec30e273e4b2dcefa80d1c528
                82c91609acf5989eec18afbefc25c898763c50d2a5cc54250d2672a39758f948
                1866c26d260bf774db05280697d2b1a95b0481afa2b80bb205186ac290bc3770
                b19a652dc2f5d6611363cd851d921edad36851d64f4cac6ff67299455e120cee
                65a0d9471d1c952d7e81ba5e67e0a8a41a9a68f50d88a2b62151b564ffc2e435
                8373e55df6cad337589a89054c050f93b4b252aa65466029313bf7311ae735a5
                26cd2bac41af846c069ed614485cf62bd66c939dfeb27ecaaa197aa344a5488b
                b3a2dca884c7fdc4ac08816b2b5ba3b7414f7ff7a06a3a6fe75e20c9eb97a881
                ac130616f176d1f26ae24bf4392e0a0e',
            ],
        ];

        // these ciphertexts generated using my branch 7215-replace-mcrypt which is based on openssl.
        $openssl = [
            [
                '67ae7370e4a144523543f1e3edf35b26',
                '30',
                '706e1670ec6beb91c565299710e4f43231336530323838313435653933623539
                3238313639623066323530646630653236313832333361346664623132393436
                34326131663964636634366665306439e6142bf3d0e6a5952231d8bfabbacd83
                ff04c61400529385d63a4b8f8696982d',
            ],
            [
                '5d4bae62bea3c8f4c49dd1a38a4e1b2a',
                '30313233343536373839',
                '4c9a34e1977dd656fd3e18d4bc2b00c762353830326262373135396530643235
                3332643937653632353331626532373336653761643866646233336365323233
                30633138663034313665653038306161eb77ac98344a0bf946f98e892e6e15db
                b9e221a7c25135a36562c3ecb2981fcd',
            ],
            [
                '6832851af3c63a1e303bdf1bca38dd9c',
                '303132333435363738396162636465',
                'd09d2faa163e774abd020bf8cd623f8962646233313330626133613964353834
                3436663631383139653438326563623537373830663962303531366130383130
                3333303531613337386534626136636415db7a6885b67f69d9974f5879ae7497
                08811fc036508f23f55f3f9d16b9ff9d',
            ],
            [
                'e8a9799f4254b26484ea447918dcaad4',
                '30313233343536373839616263646566',
                '1a76a05af572abe9b4e7a67d7f64baff39666636363332323133373034386531
                3761353236333263643965313139303666636535343862363737333032323331
                313136366562336535663033316231303811542783f13798b17d40aa7ec9b489
                34404ab50784ad1722f5f4d4462f702d3a625ef712635b274a970a47b516b137',
            ],
            [
                '27cbf5a7c4327582393b2ec277cfe957',
                '3031323334353637383961626364656630',
                '60e2e14b404c5bd55b192ef2f1c7131c32356261636361313131336265323534
                3439623033346337346133346239363337633736663831343632626638303038
                64653361396366356165633061666538d2137b2defca383668827b6983f86254
                bd849c3e8c9a44b6e1ed203491d73b4cb0cb83658240a89baa9261755d707879',
            ],
            [
                '7b157beb08e8a8ac7d74f789ccbacae5',
                '4e161a1df6',
                'a17ca346c4a88404b8a345b1d075173a66356137326335343137393136353762
                3930636535636466336534316438376437376639366535666636313235653262
                38393464663134313430333633343232b319ce688676fde03d092d73df75705f
                bde594d9b2bfe41580a458555982dc70',
            ],
            [
                '88704df5b4881d2246388dc7c68066cc',
                '5862447074634952357761764253733d',
                '9069fbdd47ee81faf34eff40f8fee94333393037333936333439653537636132
                6561613863336165333336323562353161636330366438633264376461626563
                3836363462646536323261333034643070e4b0a3d196f36a4afb92a7ae4ef7b8
                eb44e9db29638fa32140e379ae7aa6b7e68f454635ade137165383fd3a5c049b',
            ],
            [
                '1d203fba1cd2a7b92abb8f40eb985538',
                '82c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c
                040f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed927364446
                3b1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334a
                a4',
                'fade5d841aa10a29a2ef5236371ffc2964343165383664636264393666383364
                6165646139316438666531633230306537343364336166666463656466306564
                63326239653938303332656464346463136bd6dd0b7530490b91024ed944bc3e
                3fc4050d20ce05a9ed992ede75f62bdd2523d0cbc93493baf07ef98c895a353b
                5baaf26200572aa2e5bd22508db227556c5ee9eb7425418e9852c595e6ac0e61
                37c186e04a3f19d855d8c4b8a8e6ad1be179ea5c816fe461a4cec212297873c8
                5f96ee5c024cd88d1c32975fd95acd73',
            ],
            [
                '1f93719d7a66a724c3841835fbcb33fd',
                'badec0c7d9ca734e161a1df6ca4daa8cdbf6b3bbb60ec404b47a23226ec266b1
                3837ffc969e9c23e2bbba72facb491a6a3271193a35026a9ebc93698d689bf7b
                84fc384f544cc5d71c2945c8c48ae6348c753322fcaf75171b7d8f1e178e8545
                3d5c79f03bae6d9705cabbe7004ec81e188812a66313297fcf5d4c61a48614d2
                1b5379fae60736f88bc737257bc1cfbef7016108dd1f3a537aaa681544f5bb20
                c2d352212d6a2e460311ab7b93b611218b30c7402709bff89ecf814310c2db97
                81f4142a54d6d41f82c33208c188a023c70befd697b496efb7c7b8569474a9d5
                025a8ea8311c831b3de2b81800f28589b6aef537f6ada2e3d92a1ddd39cfd6b4
                8113c8b36be890b099ca4e08655db0e9b5c211e706af052b776924233321412c
                b1b540f08d88acd664c55854b05e31a15d4b0dc93b4514f62a88c3d4a451fe34
                74d2fbaee742f232959b5f6c2b71a8b6ad95a0d5362f39309aca710f37a4dae2
                228e27d35f4d3fc87ee6ece4538587fad7835f9803f5ca64dfd856ae767a3482
                c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c04
                0f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed9273644463b
                1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334aa4
                d998a599053cd477f54fc0af62a8ef75eb91096b153751b201b8374c4956508c
                6c82ea2ceb0265e74c96032787fe01139b578c3a14fd4e32a85195375b25da60
                4f21187ee01df6042971d7c2b9dd8b611527377f28f933559f6b2edfe7bd8896
                00627830bcdffcb884989013585cc6da726c7cc57c69e8664a244d234465f2a5
                e736512a65e563a2a726915b50247aff165190c75d151139f2b31211a3acf94e
                236d27b441fe64e88a443778359d57ba0fed8edbb01cc60a116f9599da4e47bf
                e96850ac12d1199a080f1b591c60eae613fba444f9fe31fd42ee333678532556
                07fbfeef02cdbb2d0a8d2329adc7fba22aa4cd124c6e66e0b0aca796ba6aeb69
                f8e76a9d1aaabe351bc42fefa6054977859b51158ba7143980d76db5b2cc6aec
                9f83eac5edf79357632fb60cb4811e71892436e582375860faf8d8c518ee33ca
                e08b18af7faa227d97196d7b041bcb366118751fbeaa92d80f86324ce8711cfd
                65005cad0ce30878f508eb1ea2735e212ef3e42a78467d4f31b483087c3c63a3
                2fa62f2ae92e2acdfc3cf21b3419acc347ff51ccb17161e829cc4a24965fd930
                b983d63153b9c13dd85340d7184bf01734d4357c1aabfbdc9711193ffa974832
                4f88cf801f35708db0901ffaa55415ca4334678633dda763b5fbfba7cfd51dc5
                79149ec9284a4db2349dbc5264d5fca7a72034261716d90a996cb57ba09f7d07
                29a2f10cc58eec98d86e9306a3fbf2e711046e86b5a86cc0eed7625be1a01ebf',
                '712929635f5be013159aec81296b96ec36333734373565366166383031313734
                3331623863663837343237633863376539643865643339383531363139396566
                34313731633765393462663264316130de90698e64fa4abc91639e72baee83bc
                2caf85f91318e0cbd0db5fa08c4ffb582ec55ca43de53a43f2844af35d5f87b9
                8faa623107aee2e083f1c7aeedcb0472c93bb9eacbd39d839d5bf94c44658d7f
                d70817f5d6b120f91ef86880f93e99151bc1ed13ed263a3ccc7243e5ea97f39f
                1ce2ce6b05a2b78f05c5d72041e35466068f52fb3d2ba3afa7594d7bc0981c54
                8b31ae7e5b7e7e0e6f2fac9a336e6516d7e4b5cc658e1fe634daf9ad097715be
                14d54ef19adc381db31db78714a09e997dc7732853d39885566e41a2c0ecb08d
                ef8ab56359a0e312446d9f1555539ad29e13080d438d6817280d4dbfe6cd4ab9
                4a357dbddcea1bc90e0d0fa6d556b1ba75c23a1d3818ea91e0fa5b8005b8020e
                f7a80ed2ee60aa8ee588e101dbf3b64bf6a3dca5b1d5bcb96eed5c594bab1dcf
                c1f61d74ad3ff0f5fdf176a327e8de33d123cdd61c6c4dc946429c566ac0f77f
                48215d5889365fa664a879babd4758fb4d824cfb9e4bf6500ffec0e4ebefe4f6
                2e521a7cd563fc954a9161047461e4054f324c5cf4f9e949566c9ac17c45aac1
                abf98caed42242c51aa0d81d732c538e437c4024e8f04eeeab6619aed46599e7
                f66c2041e0c346affab1f79cd7352a66686fac2b38615f8fc172ab0967fd4435
                9dea9f41b57ba5a752e20456f2254e8eef576867ebda0f48fe47a2e91fc8d8af
                dc1bf98a8d3530b4b02996fe4b05ec3dab200f9e79a4261c233caa9a33762f1f
                4b3482ab6f16f5dbd7bd87ed17e21c30140ce61fc2468c054ce51dded2683d7c
                375d69d662729d6fb8629b8dc25dcc5596f87f627a2138a3ef368a2a1591902f
                84ffc457bd556a346815986f153fd3a99ef169444436ac81853b318c0908cb33
                cf332edadfb870cab419365312c18aeda418b8b571783a6b2d8c397c33a22b31
                55958cef153f4d9cfc3a1c6288ff17bfdd92132e1f1e5fe8041e30a383084811
                fe892d7fc33abff10dd20db07277677b16e7f90137adfc3cb36dd85de3618769
                bf8f3ce13642f9ea430f455d388281208190b335915e256320274a904ecd0938
                d3c34d99c88e3186a132777fc7b74b43efab1a08376035061de56a4fb6de611c
                41d2139c77a516c4f8144b123696356b4b9a752a9cd857630af4ef02339172e6
                361a2fd6a18e3baaf8a3f7e9811ad5fb61abba7ae893b1e7748df2c5b7704eb9
                65606b0253cba6a0561b0e70593c724f99e07d3e9a857aef894a64a35969b354
                4726d35504d7b8c0c06cbf9106c5d504674daa879b39328d2c83e0f4e5622ea1
                4fb742458214b410e2736d8cefabfb125c4769701711c15ab870b5ff192d4c71
                e805ac5100352e33227b162ebae123e20c477719c52c59e192c2e3731806404d
                d4359f840b11ad495357210e259e6b9e8fe5e8f600e8746fe1a483d45b694324
                0809649ed7320b0022a5ef7b414635933d6d18ec7218f829121d12dcb573ed77
                79ab0519d2df17dbd8988b32ac0711ef',
            ],
        ];

        return static::CRYPT_VECTORS === 'new' ? $openssl : $mcrypt;
    }

    /**
     * @dataProvider dataProviderEncryptByKeyCompat
     *
     * @param string $key encryption key hex string
     * @param string $data plaintext hex string
     * @param string $encrypted ciphertext hex string
     */
    public function testEncryptByKeyCompat($key, $data, $encrypted)
    {
        $key = hex2bin(preg_replace('{\s+}', '', $key));
        $data = hex2bin(preg_replace('{\s+}', '', $data));
        $encrypted = hex2bin(preg_replace('{\s+}', '', $encrypted));

        $this->assertEquals($data, $this->security->decryptByKey($encrypted, $key));
    }

    public function dataProviderEncryptByPasswordCompat()
    {
        // these ciphertexts generated using Yii 2.0.2 which is based on mcrypt.
        $mcrypt = [
            [
                'LtogIEhy59ve0Huy',
                '30',
                '83325c8abe8dc0afd801acb7785dc29c32393439653930663266396466653862
                3965303964653935326238343065363734346264633932376364376430653933
                37303963376232336634306339396136e6f4e7dc3e2fd23be186f037e4caa6d0
                4ae8cb894d80c08bb790417af9cc176f',
            ],
            [
                '1_VTumNNc7VV463t',
                '30313233343536373839',
                'a247ac24f3aa60e894904f58954ce8bf39386530366165343538336132616231
                6532353066663430646432383531333465373336646333386437323733633763
                646665346232623635393962626162323a44a150a556addd97addfb43a32f600
                aa2c479664682a308e6cdd523967cb4a',
            ],
            [
                'DBpoIPndKRm2Rfem',
                '303132333435363738396162636465',
                '9ed20f30824312032e5e34e2c5ab61e333313930616634396137623261363863
                3334323832333664626265663435643633323033383862613865633961356261
                30353634393536636465643833633531b62ce156f34b92790b2d26312f3fd7d0
                0a646da4d636f6998f1b0d859f255dc6',
            ],
            [
                's1e1oRE1iM_oortb',
                '30313233343536373839616263646566',
                '5b03427dba481b5af8760edc3788fa0e66666663623539353163346466663761
                6434663337383566366265663931303662366530653633663235303532373763
                37346330393537646630616535373230c98134da00d77753741c1f0bb483f109
                622a889f950310cd51d7d48d63202b20d378eac85f7d0c851fc9905d322aef96',
            ],
            [
                'i1E7JvOQaESAKoeH',
                '3031323334353637383961626364656630',
                '7cf2ed4612d07de3ecfd54ac0e576e8539613861656439333965623533626238
                3361653764663438643330383130313462613961636462383836383862313962
                346439313666343336313766366538637bd566b1eecb0b1e0896eb1fd0fd11d9
                dcb9eee5cc3d90c4046a6849e8ad152caf85e8f96de3b24b4d523a2d60533e0b',
            ],
            [
                'QUN24gpNGodXurMM',
                '4e161a1df6',
                '49fc2d51b3e6325d86334ea8872699ef31613661333031373065373834643033
                3863343734366435343035316562643438636133306233373431323066333330
                32613330623364326162326434306637042f9b4a005ed3b9532181d020378800
                deefcfa36d77ed4abdf35546c0bb4aec',
            ],
            [
                '1COt9D8ZsfclCic2',
                '5862447074634952357761764253733d',
                'c4d5f90054faf3699d983795f44bdb1430643734643832343265323064323434
                6535346565323733323764376266646539366431353266666639386231626438
                653237646165393461626435656332332b4244fccb47ad8cdea56109c7d5a417
                13b3844d9857507db59d0000037b169f7b67cf0ea793c0254bffc55342d8e4c7',
            ],
            [
                'eUqmv4chMnO1H5cq',
                '82c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c
                040f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed927364446
                3b1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334a
                a4',
                '31435e0bf8c0be9a395791288f6d058839336130623938343839626436636433
                6664373131666361383739633163373130323936653730306331663836373263
                656135303466323439326564626162378298df437dab821e8b2f7086962ffeb1
                7a674022ee498470e5e8fdff8905aed39e424588ecee69965bb6856f0356860e
                aa978ffa42ccfef6d4fb00026014e107736f3ee9b2206a2cd52b18e3068cf6aa
                077c7304128a3cd92d4fc29dfa7c180eaf85feec791618db1ed01695536cf8ec
                7923c0b3fb974fd0ff92faa62723e94f',
            ],
            [
                '3Cl5v2Lmn61PiQ3H',
                'badec0c7d9ca734e161a1df6ca4daa8cdbf6b3bbb60ec404b47a23226ec266b1
                3837ffc969e9c23e2bbba72facb491a6a3271193a35026a9ebc93698d689bf7b
                84fc384f544cc5d71c2945c8c48ae6348c753322fcaf75171b7d8f1e178e8545
                3d5c79f03bae6d9705cabbe7004ec81e188812a66313297fcf5d4c61a48614d2
                1b5379fae60736f88bc737257bc1cfbef7016108dd1f3a537aaa681544f5bb20
                c2d352212d6a2e460311ab7b93b611218b30c7402709bff89ecf814310c2db97
                81f4142a54d6d41f82c33208c188a023c70befd697b496efb7c7b8569474a9d5
                025a8ea8311c831b3de2b81800f28589b6aef537f6ada2e3d92a1ddd39cfd6b4
                8113c8b36be890b099ca4e08655db0e9b5c211e706af052b776924233321412c
                b1b540f08d88acd664c55854b05e31a15d4b0dc93b4514f62a88c3d4a451fe34
                74d2fbaee742f232959b5f6c2b71a8b6ad95a0d5362f39309aca710f37a4dae2
                228e27d35f4d3fc87ee6ece4538587fad7835f9803f5ca64dfd856ae767a3482
                c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c04
                0f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed9273644463b
                1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334aa4
                d998a599053cd477f54fc0af62a8ef75eb91096b153751b201b8374c4956508c
                6c82ea2ceb0265e74c96032787fe01139b578c3a14fd4e32a85195375b25da60
                4f21187ee01df6042971d7c2b9dd8b611527377f28f933559f6b2edfe7bd8896
                00627830bcdffcb884989013585cc6da726c7cc57c69e8664a244d234465f2a5
                e736512a65e563a2a726915b50247aff165190c75d151139f2b31211a3acf94e
                236d27b441fe64e88a443778359d57ba0fed8edbb01cc60a116f9599da4e47bf
                e96850ac12d1199a080f1b591c60eae613fba444f9fe31fd42ee333678532556
                07fbfeef02cdbb2d0a8d2329adc7fba22aa4cd124c6e66e0b0aca796ba6aeb69
                f8e76a9d1aaabe351bc42fefa6054977859b51158ba7143980d76db5b2cc6aec
                9f83eac5edf79357632fb60cb4811e71892436e582375860faf8d8c518ee33ca
                e08b18af7faa227d97196d7b041bcb366118751fbeaa92d80f86324ce8711cfd
                65005cad0ce30878f508eb1ea2735e212ef3e42a78467d4f31b483087c3c63a3
                2fa62f2ae92e2acdfc3cf21b3419acc347ff51ccb17161e829cc4a24965fd930
                b983d63153b9c13dd85340d7184bf01734d4357c1aabfbdc9711193ffa974832
                4f88cf801f35708db0901ffaa55415ca4334678633dda763b5fbfba7cfd51dc5
                79149ec9284a4db2349dbc5264d5fca7a72034261716d90a996cb57ba09f7d07
                29a2f10cc58eec98d86e9306a3fbf2e711046e86b5a86cc0eed7625be1a01ebf',
                '0c5164ead7f48c5d95f5907399c146a261353462323734323738656231323164
                3962333166373062373734636630623932616638626236393339363861646366
                35643933393537303331343436303330a927c7a879272f81e1032d57530b5e69
                60f70e14b7607e8e17583aa8197f547e375e07b7a9d6c11be406f5e01aac59e2
                7e54bb3c33662ea294cfbd0e7bb8e5eb886edee341509a752fc3b9706807a948
                00edd0bff8e624275420bff50995de70d2692a052e1710a0b9135f9f44abcb02
                24a189f1bb414e4d73fb539728d40b47ebc34fdb5ec2fa61848bb828de5179a1
                e799eb09367bda9b59b3bdc51d3f92c1bd7dacf751b9324059869bf6adc3d88d
                226a153a75cae1a2426ea187ef62c97bbd35e97450da87d4da9aed08ebf30a3c
                a3369cc65a17acebb8a6ff8ff698743a3782990bc5e8cd03a6882c0f7c50868c
                6b3ce967c9ea317555eb972e9bb7beb7b3215160a0bcca8c7f92a085beca256e
                4484b1cdcaee495917d8aa4dbf7675806f7f57e77a770ea7db6e080150b43f56
                15709b371a303e89b032eac7fec3ee954ea52940a2e59343fd0fd59ac2d4f095
                74d2863eed13774c63a94e87f7ccdbf0741a56074a7210e2f022809ca48637d8
                057d80fb190f339ddee2b6f7aaef3f2c1848026bd33e377ac554e2b29dede4fc
                11e040b3837c24e6cd93430c2f9c19138cd1b505681958a09f223f65e6d7b123
                43e426e204ee32411b1590133d58fbc4806ff784db93cd0205a7b4a1f4b96ee4
                02b06ac00520118c89a7c4f1dfb4ac941a3d6f839e9312ee8cd9c3c02ef5cf2a
                29d266fcfd29c24b994bcd3c67000999486bad6b060f87d5c843f3f132be5a7c
                6ab2aab2091a83f0673efc4bf658e6c181c800a2eab5272c9cc8e9bdcf4ee061
                de613989f107f1755385697ad64cd1c613f1f742a980c48e27638b8423b82eba
                555c59020ee5794099377e816e8983a7579fc97178e5ac0f98bdda6dbf3f38f1
                3f8b2d12cc792e729468a8408482985ac73e831a6ca5f67176f68bf216b77147
                fe4743ae60f628defa590b0b0b7d8c39d24ef6980ffc0c47882fa0e3f04ed3e5
                6d8654e11a0f25a2b3dd03e900e3d59922fa7ccd544f98a04828a34826fd9bfe
                e8c841ca6146cc96443ab2cbed3f846f5241d27b15f81df80e2b35219044e933
                4967334529fe949604a19f7cad76842a16928066a01fb9ec750e78ae68ddcb4f
                833b5e89377ea31c7c87666c300f36f7583383f783f979cedbd05585c50caade
                73f9f38dfcc5f915106573694ce497a0787ce3fecf9678a75b62f258aea300e8
                2176e5c51009ea1fbdf266606cfb93cc62f9abd6c056625df053da8d9e175d63
                ca716f148365a41796889e6d24e6c9a6ee6ac57bf7c35b45d4000ec638d191e5
                54fe6b031f87b82f6076e9672f7162de1dafedb9fed9d38adeb999c7fc46f801
                cd546342404315b06faff51d549b2eb94390d40a9ad826c1595add3f11afb909
                16dc20173f13010008bc12a6355d582205897e7eb885f526a08bee072dabbb15
                3b1777d8e7d96b31db8b64d64404ae82b3506f10ef198fad6321aa8cf04b5f54
                d5236014d4b5ce20fbfba77f090d3573',
            ],
        ];

        // these ciphertexts generated using my branch 7215-replace-mcrypt which is based on openssl.
        $openssl = [
            [
                '3DZsVH4gt5xBueho',
                '30',
                'e19edc37d284b77a5a4600334b67317662393266323434393038666330653031
                3730313664646137663830626366383363336239313664336637653133313933
                306133613836363563643538396163642b6c12f20e7e1ae97422ee659914cc57
                e99c7c97c7a4957e78ab957b18be4551',
            ],
            [
                'CVALyUpDdMOSaxJN',
                '30313233343536373839',
                'a2535797ac0c83932fd9a2b8d6b2602662663439623337616631623434333233
                3033343439313063656162373231666330333261383561356632393932386462
                3631666364313236303962373636316296930828e0e154a0e8d262846835f242
                e42de861cba81df69fe6fe5a5970fdf7',
            ],
            [
                'wBZcBIhNSFiaCWE8',
                '303132333435363738396162636465',
                'd77b5d334d2820b5b5a54c5f71fce21130373832656561636661623564383766
                6338343636626662346566623035333461653566353366656438303063376561
                32643033343166376162376333373166c6cf828b68ff940e8de977e3471d1d51
                2e51bab7ee0f976dd6d87727b508f7cf',
            ],
            [
                'PYeAhK5nWPIxGD2F',
                '30313233343536373839616263646566',
                '0c2b35c26794c55b94228af98ba7378133326361353835323130363730333039
                3531343463653763663366386462363733666537646466326339303439373730
                64343038313234393232313362653639dbec4d9abb2dcffbf186366476df34d2
                500744fd27eda1e0ea0e54280b091d32ebd1e786402507cb3c591503e27f195b',
            ],
            [
                'B9u8Tl9tRBgmnSHk',
                '3031323334353637383961626364656630',
                '8401d45dbce698cf9342152c75cd8f5164356231643534643561646636316462
                6331373265623831373165363336616165343432383161633962646333373932
                3139323034393665303863356331363743e5873d38758489b9d5800aabf31f3e
                cf36e1e47955f5c96acdf8dc6cc7280c825949d46546796ec5a114985f5fe598',
            ],
            [
                'cLHX5BvVQcdlozS6',
                '4e161a1df6',
                'e04aed1dc385788c3c777bc7b3cfa20c32656633363730393363366138643831
                3866303330613461666664623039326437623739613236333566353762333461
                34653132366163653031303134353161eefebead8d190e854c05f598adfb8d7b
                ef30c86c7cc7003f261b8ce26c62da55',
            ],
            [
                'r8EZMeBVex-LC3c5',
                '5862447074634952357761764253733d',
                'ae03ee587e7df6407a17d43e3381b76563616366633165613463383063353930
                6163613736656635663234613263323833393131363661333737333133356366
                3963313236356663373035323264623224b4aa1536a8deba1dfa026efa614fb9
                4915763a2629a00fe5ff8f1afc894f1b644f9b08ebc7baefc06229f177b5e446',
            ],
            [
                'TRS5GvlQ2WCoEzHY',
                '82c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c
                040f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed927364446
                3b1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334a
                a4',
                'f05611e02a2cccbfea4679e8110087a061396332303030396562663732626138
                3530363534313362373731316330363964393564653831636566646261363265
                38323536366666633438396566323939bce309f535fca3d1fb23e8503f8cf3b8
                bca4c4bcfb4021592268f88070b5203f5023a6a39034e34048bc944c22e037fd
                e6ca8ca17e2bdc8169d1e714830de6932cfe0dcdd1728e8bf848a6f4f7154d1c
                0f8c650e0ef650ba3b90372eb6d13e93e3c79610291a523a3967a3049b04f1d4
                c899e1554b04f906c2e6408a16702d19',
            ],
            [
                'x_a2LHnyqH8WAwkZ',
                'badec0c7d9ca734e161a1df6ca4daa8cdbf6b3bbb60ec404b47a23226ec266b1
                3837ffc969e9c23e2bbba72facb491a6a3271193a35026a9ebc93698d689bf7b
                84fc384f544cc5d71c2945c8c48ae6348c753322fcaf75171b7d8f1e178e8545
                3d5c79f03bae6d9705cabbe7004ec81e188812a66313297fcf5d4c61a48614d2
                1b5379fae60736f88bc737257bc1cfbef7016108dd1f3a537aaa681544f5bb20
                c2d352212d6a2e460311ab7b93b611218b30c7402709bff89ecf814310c2db97
                81f4142a54d6d41f82c33208c188a023c70befd697b496efb7c7b8569474a9d5
                025a8ea8311c831b3de2b81800f28589b6aef537f6ada2e3d92a1ddd39cfd6b4
                8113c8b36be890b099ca4e08655db0e9b5c211e706af052b776924233321412c
                b1b540f08d88acd664c55854b05e31a15d4b0dc93b4514f62a88c3d4a451fe34
                74d2fbaee742f232959b5f6c2b71a8b6ad95a0d5362f39309aca710f37a4dae2
                228e27d35f4d3fc87ee6ece4538587fad7835f9803f5ca64dfd856ae767a3482
                c2f315c69f20ec7ae391fc6c2de85281d54c97f74981b4efec0f5e4606765c04
                0f1f45386bed4fdb9f998b0e7027c7414bea667b1205027c55eed9273644463b
                1832bc5338e3a7e10bf24d8e69f167b94551b6240f65b416feebd28599334aa4
                d998a599053cd477f54fc0af62a8ef75eb91096b153751b201b8374c4956508c
                6c82ea2ceb0265e74c96032787fe01139b578c3a14fd4e32a85195375b25da60
                4f21187ee01df6042971d7c2b9dd8b611527377f28f933559f6b2edfe7bd8896
                00627830bcdffcb884989013585cc6da726c7cc57c69e8664a244d234465f2a5
                e736512a65e563a2a726915b50247aff165190c75d151139f2b31211a3acf94e
                236d27b441fe64e88a443778359d57ba0fed8edbb01cc60a116f9599da4e47bf
                e96850ac12d1199a080f1b591c60eae613fba444f9fe31fd42ee333678532556
                07fbfeef02cdbb2d0a8d2329adc7fba22aa4cd124c6e66e0b0aca796ba6aeb69
                f8e76a9d1aaabe351bc42fefa6054977859b51158ba7143980d76db5b2cc6aec
                9f83eac5edf79357632fb60cb4811e71892436e582375860faf8d8c518ee33ca
                e08b18af7faa227d97196d7b041bcb366118751fbeaa92d80f86324ce8711cfd
                65005cad0ce30878f508eb1ea2735e212ef3e42a78467d4f31b483087c3c63a3
                2fa62f2ae92e2acdfc3cf21b3419acc347ff51ccb17161e829cc4a24965fd930
                b983d63153b9c13dd85340d7184bf01734d4357c1aabfbdc9711193ffa974832
                4f88cf801f35708db0901ffaa55415ca4334678633dda763b5fbfba7cfd51dc5
                79149ec9284a4db2349dbc5264d5fca7a72034261716d90a996cb57ba09f7d07
                29a2f10cc58eec98d86e9306a3fbf2e711046e86b5a86cc0eed7625be1a01ebf',
                '5d841d0cb575fdb8c7e2cc48be47021d38646237366663323933346163643933
                3861336335396666356139383066663033336333303731336263663262626136
                643635333333393362306631333266320d73812258a45d90f8240d59c4e39e87
                8d4d999f36403a6f0b1d69ab9416792c5e8f2cf427af8423c8213a885fde488c
                db95217c64c542f5e2be6b4375ff82e5c72a9f165049546b38295006f50665b1
                354350de4a68b5f16a18f7df53e0999f4f7ba5ba0676e416a0444d5b7accda8b
                093ae31a23e3cb63b6f404c437071435b8143f282c4cb4403a2b538af5a0d94f
                1e582c3bb9d5379ba5d576643854c232d74f303dcac91a711bf440fb24e7ab2a
                70ef69008ffb59ad2455d4f1482e77114489a3b5a250384b24062f546f86073a
                91fdd85d34bd7814b4ce70ec8d6ea34f98067d7101050f3800f9f1fd92003856
                223f8ca142749c2ef4c8d1991a62b0ff86623bf9afce65d55fb5efe80089cee4
                e4f12e94e1748c5740f075a94a2290ba2dc892fdfde516ebc190a4db63e77f93
                54a3bec5ef695572dddcc9d7c43609724c73bc5bfe79d5f322890e4f39a31e6b
                3fb9388c78e133c58e395ca03eca2e8ab9520e4d2e5421e0d9a1f781a564dda7
                720d56f413312762da078e0226053672983ecf5bdf18086a6f617071814d61e4
                27e6f02167b8d38e381607e4238f21c0b6e6d9222f1cc6348b9f7d6fb084cc3b
                306b7acbb94f4b3ab6b66fe539865fb804899d3f64c8bca6bd02ee5509022a50
                03d63e259bb414391fc10ae9b2e42c68a2be743488b69ca77c70741820aaeeb0
                da2ff00c07787a39ec613e665d78c30b5f57a14fcbe24f00cf55eeb174e23dac
                a9eb3587bc8dc8fdf5ef062b7f1659b45c48246055fda699b6ae8b9fcc46a380
                ebc6b648662ef5fed1a4fe16c9aa310cc16f5ad642b80549262f5c77335f5435
                a43d30459297b754350a9dd635b0ca5342fc798d369225f6d692eea0c901eb72
                fd10af1199b7847ffc1a0c5915902fe339772183727c31497c752e3beb1cf010
                2c97ab270def6628bdae630172d73a9fc0afed1d893870003828f64518512886
                57d62ba52c8d325aa8409b0a40754dc3f84d1c8898e01e20c03464b83d2dae5d
                3d9f279778fb1161ac5d8f9c466fd0c6bdd6a21553ea9252ff018ae99b0d4425
                0e55d177fb1e0275da97474ea052d85d96b7432c1be840e5994b127b147d1a0c
                64f1cea9115a37225c8b49960e0693680ee5593c81784c850811e1f10ce4f9a3
                365e3d2d240e0eef8da6404d5a93ebd000a98d5d33dd5a238327e88dfdad2744
                ed0c4321a543c1a3231e53550e816c531b73bafec21e32daeecd199c7a2be75f
                450ce4d39e10ad81a7ca74877e1661376d7cce557a1d4dde53b503e1512efef6
                d607d5074ca8ba29db067454e529aec867907c6eded03ce90835d72974cddcc5
                83628bd2948a78a2d666ed89889f59b1dd5b05704b7e68a801ad9f93809e0d8a
                ae72a72923883d4de81d867bf639eb5dc581429041ca78763235fe11251254c8
                cca8bfe10e8810035e4cce023b6527744d0ea839bb035db99adc3ce742a5491d
                c446220a6cb416a0bd3362b424dcdf3e',
            ],
        ];

        return static::CRYPT_VECTORS === 'new' ? $openssl : $mcrypt;
    }

    /**
     * @dataProvider dataProviderEncryptByPasswordCompat
     *
     * @param string $password encryption password
     * @param string $data plaintext hex string
     * @param string $encrypted ciphertext hex string
     */
    public function testEncryptByPasswordCompat($password, $data, $encrypted)
    {
        $data = hex2bin(preg_replace('{\s+}', '', $data));
        $encrypted = hex2bin(preg_replace('{\s+}', '', $encrypted));

        $this->assertEquals($data, $this->security->decryptByPassword($encrypted, $password));
    }


    public function randomKeyInvalidInputs()
    {
        return [
            [0],
            [-1],
            ['0'],
            ['34'],
            [[]],
        ];
    }

    /**
     * @dataProvider randomKeyInvalidInputs
     * @expectedException \yii\base\InvalidArgumentException
     * @param mixed $input
     */
    public function testRandomKeyInvalidInput($input)
    {
        $key1 = $this->security->generateRandomKey($input);
    }

    public function testGenerateRandomKey()
    {
        // test various string lengths
        for ($length = 1; $length < 64; $length++) {
            $key1 = $this->security->generateRandomKey($length);
            $this->assertInternalType('string', $key1);
            $this->assertEquals($length, strlen($key1));
            $key2 = $this->security->generateRandomKey($length);
            $this->assertInternalType('string', $key2);
            $this->assertEquals($length, strlen($key2));
            if ($length >= 7) { // avoid random test failure, short strings are likely to collide
                $this->assertNotEquals($key1, $key2);
            }
        }

        // test for /dev/urandom, reading larger data to see if loop works properly
        $length = 1024 * 1024;
        $key1 = $this->security->generateRandomKey($length);
        $this->assertInternalType('string', $key1);
        $this->assertEquals($length, strlen($key1));
        $key2 = $this->security->generateRandomKey($length);
        $this->assertInternalType('string', $key2);
        $this->assertEquals($length, strlen($key2));
        $this->assertNotEquals($key1, $key2);

        // force /dev/urandom reading loop to deal with chunked data
        // the above test may have read everything in one run.
        // not sure if this can happen in real life but if it does
        // we should be prepared
        static::$fopen = fopen('php://memory', 'rwb');
        $length = 1024 * 1024;
        $key1 = $this->security->generateRandomKey($length);
        $this->assertInternalType('string', $key1);
        $this->assertEquals($length, strlen($key1));
    }

    protected function randTime(Security $security, $count, $length, $message)
    {
        $t = microtime(true);
        for ($i = 0; $i < $count; $i += 1) {
            $key = $security->generateRandomKey($length);
        }
        $t = microtime(true) - $t;
        $nbytes = number_format($count * $length, 0);
        $milisec = number_format(1000 * ($t), 3);
        $rate = number_format($count * $length / $t / 1000000, 3);
        fwrite(STDERR, "$message: $count x $length B = $nbytes B in $milisec ms => $rate MB/s\n");
    }

    public function testGenerateRandomKeySpeed()
    {
        self::markTestSkipped('Comment markTestSkipped in testGenerateRandomKeySpeed() in order to get RNG benchmark.');
        $tests = [
            "function_exists('random_bytes')",
            "defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : null",
            'PHP_VERSION_ID',
            'PHP_OS',
            "function_exists('mcrypt_create_iv') ? bin2hex(mcrypt_create_iv(4, MCRYPT_DEV_URANDOM)) : null",
            'DIRECTORY_SEPARATOR',
            "ini_get('open_basedir')",
        ];
        if (DIRECTORY_SEPARATOR === '/') {
            $tests[] = "sprintf('%o', lstat(PHP_OS === 'FreeBSD' ? '/dev/random' : '/dev/urandom')['mode'] & 0170000)";
            $tests[] = "bin2hex(file_get_contents(PHP_OS === 'FreeBSD' ? '/dev/random' : '/dev/urandom', false, null, 0, 8))";
        }
        foreach ($tests as $i => $test) {
            $result = eval('return ' . $test . ';');
            fwrite(STDERR, sprintf("%2d %s ==> %s\n", $i + 1, $test, var_export($result, true)));
        }

        foreach ([16, 2000, 262144] as $block) {
            $security = new Security();
            foreach (range(1, 10) as $nth) {
                $this->randTime($security, 1, $block, "Call $nth");
            }
            unset($security);
        }

        $security = new Security();
        $this->randTime($security, 10000, 16, 'Rate test');

        $security = new Security();
        $this->randTime($security, 10000, 5000, 'Rate test');
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
        return array_filter([
            [
                'sha1',
                'password',
                'salt',
                1,
                20,
                '0c60c80f961f0e71f3a9b524af6012062fe037a6',
            ],
            [
                'sha1',
                'password',
                'salt',
                2,
                20,
                'ea6c014dc72d6f8ccd1ed92ace1d41f0d8de8957',
            ],
            [
                'sha1',
                'password',
                'salt',
                4096,
                20,
                '4b007901b765489abead49d926f721d065a429c1',
            ],
            getenv('TRAVIS') == true ? [
                'sha1',
                'password',
                'salt',
                16777216,
                20,
                'eefe3d61cd4da4e4e9945b3d6ba2158c2634e984',
            ] : null,
            [
                'sha1',
                'passwordPASSWORDpassword',
                'saltSALTsaltSALTsaltSALTsaltSALTsalt',
                4096,
                25,
                '3d2eec4fe41c849b80c8d83662c0e44a8b291a964cf2f07038',
            ],
            [
                'sha1',
                "pass\0word",
                "sa\0lt",
                4096,
                16,
                '56fa6aa75548099dcc37d7f03425e0c3',
            ],
            [
                'sha256',
                'password',
                'salt',
                1,
                20,
                '120fb6cffcf8b32c43e7225256c4f837a86548c9',
            ],
            [
                'sha256',
                "pass\0word",
                "sa\0lt",
                4096,
                32,
                '89b69d0516f829893c696226650a86878c029ac13ee276509d5ae58b6466a724',
            ],
            [
                'sha256',
                'passwordPASSWORDpassword',
                'saltSALTsaltSALTsaltSALTsaltSALTsalt',
                4096,
                40,
                '348c89dbcbd32b2f32d814b8116e84cf2b17347ebc1800181c4e2a1fb8dd53e1c635518c7dac47e9',
            ],
        ]);
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
            ],
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
            ['', ''],
            ['', "\0"],
            ["\0", ''],
            ["\0", "\0"],
            ['0', "\0"],
            ['user', 'User'],
            ['password', 'password'],
            ['password', 'passwordpassword'],
            ['password1', 'password'],
            ['password', 'password2'],
            ['', 'password'],
            ['password', ''],
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

    /**
     * @dataProvider maskProvider
     * @param mixed $unmaskedToken
     */
    public function testMasking($unmaskedToken)
    {
        $maskedToken = $this->security->maskToken($unmaskedToken);
        $this->assertGreaterThan(mb_strlen($unmaskedToken, '8bit') * 2, mb_strlen($maskedToken, '8bit'));
        $this->assertEquals($unmaskedToken, $this->security->unmaskToken($maskedToken));
    }

    public function testUnMaskingInvalidStrings()
    {
        $this->assertEquals('', $this->security->unmaskToken(''));
        $this->assertEquals('', $this->security->unmaskToken('1'));
    }

    /**
     * @expectedException \yii\base\InvalidArgumentException
     */
    public function testMaskingInvalidStrings()
    {
        $this->security->maskToken('');
    }

    /**
     * @return array
     */
    public function maskProvider()
    {
        return [
            ['1'],
            ['SimpleToken'],
            ['Token with special characters: %d1    5"'],
            ['Token with UTF8 character: †'],
        ];
    }
}
} // closing namespace yiiunit\framework\base;
