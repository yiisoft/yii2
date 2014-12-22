<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\helpers\StringHelper;
use Yii;

/**
 * Security provides a set of methods to handle common security-related tasks.
 *
 * In particular, Security supports the following features:
 *
 * - Encryption/decryption: [[encryptByKey()]], [[decryptByKey()]], [[encryptByPassword()]] and [[decryptByPassword()]]
 * - Key derivation using standard algorithms: [[pbkdf2()]] and [[hkdf()]]
 * - Data tampering prevention: [[hashData()]] and [[validateData()]]
 * - Password validation: [[generatePasswordHash()]] and [[validatePassword()]]
 *
 * > Note: this class requires 'mcrypt' PHP extension. For the highest security level PHP version >= 5.5.0 is recommended.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Tom Worster <fsb@thefsb.org>
 * @author Klimov Paul <klimov.paul@gmail.com>
 * @since 2.0
 */
class Security extends Component
{
    /**
     * Cipher algorithm for mcrypt module.
     * AES has 128-bit block size and three key sizes: 128, 192 and 256 bits.
     * mcrypt offers the Rijndael cipher with block sizes of 128, 192 and 256
     * bits but only the 128-bit Rijndael is standardized in AES.
     * So to use AES in mycrypt, specify `'rijndael-128'` cipher and mcrypt
     * chooses the appropriate AES based on the length of the supplied key.
     */
    const MCRYPT_CIPHER = 'rijndael-128';
    /**
     * Block cipher operation mode for mcrypt module.
     */
    const MCRYPT_MODE = 'cbc';
    /**
     * Size in bytes of encryption key, message authentication key and KDF salt.
     */
    const KEY_SIZE = 16;
    /**
     * Hash algorithm for key derivation.
     */
    const KDF_HASH = 'sha256';
    /**
     * Hash algorithm for message authentication.
     */
    const MAC_HASH = 'sha256';
    /**
     * HKDF info value for derivation of message authentication key.
     */
    const AUTH_KEY_INFO = 'AuthorizationKey';

    /**
     * @var integer derivation iterations count.
     * Set as high as possible to hinder dictionary password attacks.
     */
    public $derivationIterations = 100000;
    /**
     * @var string strategy, which should be used to generate password hash.
     * Available strategies:
     * - 'password_hash' - use of PHP `password_hash()` function with PASSWORD_DEFAULT algorithm.
     *   This option is recommended, but it requires PHP version >= 5.5.0
     * - 'crypt' - use PHP `crypt()` function.
     */
    public $passwordHashStrategy = 'crypt';

    private $_cryptModule;


    /**
     * Encrypts data using a password.
     * Derives keys for encryption and authentication from the password using PBKDF2 and a random salt,
     * which is deliberately slow to protect against dictionary attacks. Use [[encryptByKey()]] to
     * encrypt fast using a cryptographic key rather than a password. Key derivation time is
     * determined by [[$derivationIterations]], which should be set as high as possible.
     * The encrypted data includes a keyed message authentication code (MAC) so there is no need
     * to hash input or output data.
     * > Note: Avoid encrypting with passwords wherever possible. Nothing can protect against
     * poor-quality or compromised passwords.
     * @param string $data the data to encrypt
     * @param string $password the password to use for encryption
     * @return string the encrypted data
     * @see decryptByPassword()
     * @see encryptByKey()
     */
    public function encryptByPassword($data, $password)
    {
        return $this->encrypt($data, true, $password, null);
    }

    /**
     * Encrypts data using a cryptograhic key.
     * Derives keys for encryption and authentication from the input key using HKDF and a random salt,
     * which is very fast relative to [[encryptByPassword()]]. The input key must be properly
     * random -- use [[generateRandomKey()]] to generate keys.
     * The encrypted data includes a keyed message authentication code (MAC) so there is no need
     * to hash input or output data.
     * @param string $data the data to encrypt
     * @param string $inputKey the input to use for encryption and authentication
     * @param string $info optional context and application specific information, see [[hkdf()]]
     * @return string the encrypted data
     * @see decryptByPassword()
     * @see encryptByKey()
     */
    public function encryptByKey($data, $inputKey, $info = null)
    {
        return $this->encrypt($data, false, $inputKey, $info);
    }

    /**
     * Verifies and decrypts data encrypted with [[encryptByPassword()]].
     * @param string $data the encrypted data to decrypt
     * @param string $password the password to use for decryption
     * @return bool|string the decrypted data or false on authentication failure
     * @see encryptByPassword()
     */
    public function decryptByPassword($data, $password)
    {
        return $this->decrypt($data, true, $password, null);
    }

    /**
     * Verifies and decrypts data encrypted with [[encryptByPassword()]].
     * @param string $data the encrypted data to decrypt
     * @param string $inputKey the input to use for encryption and authentication
     * @param string $info optional context and application specific information, see [[hkdf()]]
     * @return bool|string the decrypted data or false on authentication failure
     * @see encryptByKey()
     */
    public function decryptByKey($data, $inputKey, $info = null)
    {
        return $this->decrypt($data, false, $inputKey, $info);
    }

    /**
     * Initializes the mcrypt module.
     * @return resource the mcrypt module handle.
     * @throws InvalidConfigException if mcrypt extension is not installed
     * @throws Exception if mcrypt initialization fails
     */
    protected function getCryptModule()
    {
        if ($this->_cryptModule === null) {
            if (!extension_loaded('mcrypt')) {
                throw new InvalidConfigException('The mcrypt PHP extension is not installed.');
            }

            $this->_cryptModule = @mcrypt_module_open(self::MCRYPT_CIPHER, '', self::MCRYPT_MODE, '');
            if ($this->_cryptModule === false) {
                $this->_cryptModule = null;
                throw new Exception('Failed to initialize the mcrypt module.');
            }
        }

        return $this->_cryptModule;
    }

    public function __destruct()
    {
        if ($this->_cryptModule !== null) {
            mcrypt_module_close($this->_cryptModule);
            $this->_cryptModule = null;
        }
    }


    /**
     * Encrypts data.
     * @param string $data data to be encrypted
     * @param boolean $passwordBased set true to use password-based key derivation
     * @param string $secret the encryption password or key
     * @param string $info context/application specific information, e.g. a user ID
     * See [RFC 5869 Section 3.2](https://tools.ietf.org/html/rfc5869#section-3.2) for more details.
     * @return string the encrypted data
     * @throws Exception if PHP Mcrypt extension is not loaded or failed to be initialized
     * @see decrypt()
     */
    protected function encrypt($data, $passwordBased, $secret, $info)
    {
        $module = $this->getCryptModule();

        $keySalt = $this->generateRandomKey(self::KEY_SIZE);
        if ($passwordBased) {
            $key = $this->pbkdf2(self::KDF_HASH, $secret, $keySalt, $this->derivationIterations, self::KEY_SIZE);
        } else {
            $key = $this->hkdf(self::KDF_HASH, $secret, $keySalt, $info, self::KEY_SIZE);
        }

        $data = $this->addPadding($data);
        $ivSize = mcrypt_enc_get_iv_size($module);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);
        mcrypt_generic_init($module, $key, $iv);
        $encrypted = mcrypt_generic($module, $data);
        mcrypt_generic_deinit($module);

        $authKey = $this->hkdf(self::KDF_HASH, $key, null, self::AUTH_KEY_INFO, self::KEY_SIZE);
        $hashed = $this->hashData($iv . $encrypted, $authKey);

        /*
         * Output: [keySalt][MAC][IV][ciphertext]
         * - keySalt is KEY_SIZE bytes long
         * - MAC: message authentication code, length same as the output of MAC_HASH
         * - IV: initialization vector, length set by CRYPT_CIPHER and CRYPT_MODE, mcrypt_enc_get_iv_size()
         */
        return $keySalt . $hashed;
    }

    /**
     * Decrypts data.
     * @param string $data encrypted data to be decrypted.
     * @param boolean $passwordBased set true to use password-based key derivation
     * @param string $secret the decryption password or key
     * @param string $info context/application specific information, @see encrypt()
     * @return bool|string the decrypted data or false on authentication failure
     * @see encrypt()
     */
    protected function decrypt($data, $passwordBased, $secret, $info)
    {
        $keySalt = StringHelper::byteSubstr($data, 0, self::KEY_SIZE);
        if ($passwordBased) {
            $key = $this->pbkdf2(self::KDF_HASH, $secret, $keySalt, $this->derivationIterations, self::KEY_SIZE);
        } else {
            $key = $this->hkdf(self::KDF_HASH, $secret, $keySalt, $info, self::KEY_SIZE);
        }

        $authKey = $this->hkdf(self::KDF_HASH, $key, null, self::AUTH_KEY_INFO, self::KEY_SIZE);
        $data = $this->validateData(StringHelper::byteSubstr($data, self::KEY_SIZE, null), $authKey);
        if ($data === false) {
            return false;
        }

        $module = $this->getCryptModule();
        $ivSize = mcrypt_enc_get_iv_size($module);
        $iv = StringHelper::byteSubstr($data, 0, $ivSize);
        $encrypted = StringHelper::byteSubstr($data, $ivSize, null);
        mcrypt_generic_init($module, $key, $iv);
        $decrypted = mdecrypt_generic($module, $encrypted);
        mcrypt_generic_deinit($module);

        return $this->stripPadding($decrypted);
    }

    /**
     * Adds a padding to the given data (PKCS #7).
     * @param string $data the data to pad
     * @return string the padded data
     */
    protected function addPadding($data)
    {
        $module = $this->getCryptModule();
        $blockSize = mcrypt_enc_get_block_size($module);
        $pad = $blockSize - (StringHelper::byteLength($data) % $blockSize);

        return $data . str_repeat(chr($pad), $pad);
    }

    /**
     * Strips the padding from the given data.
     * @param string $data the data to trim
     * @return string the trimmed data
     */
    protected function stripPadding($data)
    {
        $end = StringHelper::byteSubstr($data, -1, null);
        $last = ord($end);
        $n = StringHelper::byteLength($data) - $last;
        if (StringHelper::byteSubstr($data, $n, null) === str_repeat($end, $last)) {
            return StringHelper::byteSubstr($data, 0, $n);
        }

        return false;
    }

    /**
     * Derives a key from the given input key using the standard HKDF algorithm.
     * Implements HKDF specified in [RFC 5869](https://tools.ietf.org/html/rfc5869).
     * Recommend use one of the SHA-2 hash algorithms: sha224, sha256, sha384 or sha512.
     * @param string $algo a hash algorithm supported by `hash_hmac()`, e.g. 'SHA-256'
     * @param string $inputKey the source key
     * @param string $salt the random salt
     * @param string $info optional info to bind the derived key material to application-
     * and context-specific information, e.g. a user ID or API version, see
     * [RFC 5869](https://tools.ietf.org/html/rfc5869)
     * @param integer $length length of the output key in bytes. If 0, the output key is
     * the length of the hash algorithm output.
     * @throws InvalidParamException when HMAC generation fails.
     * @return string the derived key
     */
    public function hkdf($algo, $inputKey, $salt = null, $info = null, $length = 0)
    {
        $test = @hash_hmac($algo, '', '', true);
        if (!$test) {
            throw new InvalidParamException('Failed to generate HMAC with hash algorithm: ' . $algo);
        }
        $hashLength = StringHelper::byteLength($test);
        if (is_string($length) && preg_match('{^\d{1,16}$}', $length)) {
            $length = (int) $length;
        }
        if (!is_integer($length) || $length < 0 || $length > 255 * $hashLength) {
            throw new InvalidParamException('Invalid length');
        }
        $blocks = $length !== 0 ? ceil($length / $hashLength) : 1;

        if ($salt === null) {
            $salt = str_repeat("\0", $hashLength);
        }
        $prKey = hash_hmac($algo, $inputKey, $salt, true);

        $hmac = '';
        $outputKey = '';
        for ($i = 1; $i <= $blocks; $i++) {
            $hmac = hash_hmac($algo, $hmac . $info . chr($i), $prKey, true);
            $outputKey .= $hmac;
        }

        if ($length !== 0) {
            $outputKey = StringHelper::byteSubstr($outputKey, 0, $length);
        }
        return $outputKey;
    }

    /**
     * Derives a key from the given password using the standard PBKDF2 algorithm.
     * Implements HKDF2 specified in [RFC 2898](http://tools.ietf.org/html/rfc2898#section-5.2)
     * Recommend use one of the SHA-2 hash algorithms: sha224, sha256, sha384 or sha512.
     * @param string $algo a hash algorithm supported by `hash_hmac()`, e.g. 'SHA-256'
     * @param string $password the source password
     * @param string $salt the random salt
     * @param integer $iterations the number of iterations of the hash algorithm. Set as high as
     * possible to hinder dictionary password attacks.
     * @param integer $length length of the output key in bytes. If 0, the output key is
     * the length of the hash algorithm output.
     * @return string the derived key
     * @throws InvalidParamException when hash generation fails due to invalid params given.
     */
    public function pbkdf2($algo, $password, $salt, $iterations, $length = 0)
    {
        if (function_exists('hash_pbkdf2')) {
            $outputKey = hash_pbkdf2($algo, $password, $salt, $iterations, $length, true);
            if ($outputKey === false) {
                throw new InvalidParamException('Invalid parameters to hash_pbkdf2()');
            }
            return $outputKey;
        }

        // todo: is there a nice way to reduce the code repetition in hkdf() and pbkdf2()?
        $test = @hash_hmac($algo, '', '', true);
        if (!$test) {
            throw new InvalidParamException('Failed to generate HMAC with hash algorithm: ' . $algo);
        }
        if (is_string($iterations) && preg_match('{^\d{1,16}$}', $iterations)) {
            $iterations = (int) $iterations;
        }
        if (!is_integer($iterations) || $iterations < 1) {
            throw new InvalidParamException('Invalid iterations');
        }
        if (is_string($length) && preg_match('{^\d{1,16}$}', $length)) {
            $length = (int) $length;
        }
        if (!is_integer($length) || $length < 0) {
            throw new InvalidParamException('Invalid length');
        }
        $hashLength = StringHelper::byteLength($test);
        $blocks = $length !== 0 ? ceil($length / $hashLength) : 1;

        $outputKey = '';
        for ($j = 1; $j <= $blocks; $j++) {
            $hmac = hash_hmac($algo, $salt . pack('N', $j), $password, true);
            $xorsum = $hmac;
            for ($i = 1; $i < $iterations; $i++) {
                $hmac = hash_hmac($algo, $hmac, $password, true);
                $xorsum ^= $hmac;
            }
            $outputKey .= $xorsum;
        }

        if ($length !== 0) {
            $outputKey = StringHelper::byteSubstr($outputKey, 0, $length);
        }
        return $outputKey;
    }

    /**
     * Prefixes data with a keyed hash value so that it can later be detected if it is tampered.
     * There is no need to hash inputs or outputs of [[encryptByKey()]] or [[encryptByPassword()]]
     * as those methods perform the task.
     * @param string $data the data to be protected
     * @param string $key the secret key to be used for generating hash. Should be a secure
     * cryptographic key.
     * @param boolean $rawHash whether the generated hash value is in raw binary format. If false, lowercase
     * hex digits will be generated.
     * @return string the data prefixed with the keyed hash
     * @throws InvalidConfigException when HMAC generation fails.
     * @see validateData()
     * @see generateRandomKey()
     * @see hkdf()
     * @see pbkdf2()
     */
    public function hashData($data, $key, $rawHash = false)
    {
        $hash = hash_hmac(self::MAC_HASH, $data, $key, $rawHash);
        if (!$hash) {
            throw new InvalidConfigException('Failed to generate HMAC with hash algorithm: ' . self::MAC_HASH);
        }
        return $hash . $data;
    }

    /**
     * Validates if the given data is tampered.
     * @param string $data the data to be validated. The data must be previously
     * generated by [[hashData()]].
     * @param string $key the secret key that was previously used to generate the hash for the data in [[hashData()]].
     * function to see the supported hashing algorithms on your system. This must be the same
     * as the value passed to [[hashData()]] when generating the hash for the data.
     * @param boolean $rawHash this should take the same value as when you generate the data using [[hashData()]].
     * It indicates whether the hash value in the data is in binary format. If false, it means the hash value consists
     * of lowercase hex digits only.
     * hex digits will be generated.
     * @return string the real data with the hash stripped off. False if the data is tampered.
     * @throws InvalidConfigException when HMAC generation fails.
     * @see hashData()
     */
    public function validateData($data, $key, $rawHash = false)
    {
        $test = @hash_hmac(self::MAC_HASH, '', '', $rawHash);
        if (!$test) {
            throw new InvalidConfigException('Failed to generate HMAC with hash algorithm: ' . self::MAC_HASH);
        }
        $hashLength = StringHelper::byteLength($test);
        if (StringHelper::byteLength($data) >= $hashLength) {
            $hash = StringHelper::byteSubstr($data, 0, $hashLength);
            $pureData = StringHelper::byteSubstr($data, $hashLength, null);

            $calculatedHash = hash_hmac(self::MAC_HASH, $pureData, $key, $rawHash);

            if ($this->compareString($hash, $calculatedHash)) {
                return $pureData;
            }
        }
        return false;
    }

    /**
     * Generates specified number of random bytes.
     * Note that output may not be ASCII.
     * @see generateRandomString() if you need a string.
     *
     * @param integer $length the number of bytes to generate
     * @return string the generated random bytes
     * @throws InvalidConfigException if mcrypt extension is not installed.
     * @throws Exception on failure.
     */
    public function generateRandomKey($length = 32)
    {
        if (!extension_loaded('mcrypt')) {
            throw new InvalidConfigException('The mcrypt PHP extension is not installed.');
        }
        $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        if ($bytes === false) {
            throw new Exception('Unable to generate random bytes.');
        }
        return $bytes;
    }

    /**
     * Generates a random string of specified length.
     * The string generated matches [A-Za-z0-9_-]+ and is transparent to URL-encoding.
     *
     * @param integer $length the length of the key in characters
     * @return string the generated random key
     * @throws InvalidConfigException if mcrypt extension is not installed.
     * @throws Exception on failure.
     */
    public function generateRandomString($length = 32)
    {
        $bytes = $this->generateRandomKey($length);
        // '=' character(s) returned by base64_encode() are always discarded because
        // they are guaranteed to be after position $length in the base64_encode() output.
        return strtr(substr(base64_encode($bytes), 0, $length), '+/', '_-');
    }

    /**
     * Generates a secure hash from a password and a random salt.
     *
     * The generated hash can be stored in database.
     * Later when a password needs to be validated, the hash can be fetched and passed
     * to [[validatePassword()]]. For example,
     *
     * ~~~
     * // generates the hash (usually done during user registration or when the password is changed)
     * $hash = Yii::$app->getSecurity()->generatePasswordHash($password);
     * // ...save $hash in database...
     *
     * // during login, validate if the password entered is correct using $hash fetched from database
     * if (Yii::$app->getSecurity()->validatePassword($password, $hash) {
     *     // password is good
     * } else {
     *     // password is bad
     * }
     * ~~~
     *
     * @param string $password The password to be hashed.
     * @param integer $cost Cost parameter used by the Blowfish hash algorithm.
     * The higher the value of cost,
     * the longer it takes to generate the hash and to verify a password against it. Higher cost
     * therefore slows down a brute-force attack. For best protection against brute for attacks,
     * set it to the highest value that is tolerable on production servers. The time taken to
     * compute the hash doubles for every increment by one of $cost.
     * @return string The password hash string. When [[passwordHashStrategy]] is set to 'crypt',
     * the output is always 60 ASCII characters, when set to 'password_hash' the output length
     * might increase in future versions of PHP (http://php.net/manual/en/function.password-hash.php)
     * @throws Exception on bad password parameter or cost parameter.
     * @throws InvalidConfigException when an unsupported password hash strategy is configured.
     * @see validatePassword()
     */
    public function generatePasswordHash($password, $cost = 13)
    {
        switch ($this->passwordHashStrategy) {
            case 'password_hash':
                if (!function_exists('password_hash')) {
                    throw new InvalidConfigException('Password hash key strategy "password_hash" requires PHP >= 5.5.0, either upgrade your environment or use another strategy.');
                }
                /** @noinspection PhpUndefinedConstantInspection */
                return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
            case 'crypt':
                $salt = $this->generateSalt($cost);
                $hash = crypt($password, $salt);
                // strlen() is safe since crypt() returns only ascii
                if (!is_string($hash) || strlen($hash) !== 60) {
                    throw new Exception('Unknown error occurred while generating hash.');
                }
                return $hash;
            default:
                throw new InvalidConfigException("Unknown password hash strategy '{$this->passwordHashStrategy}'");
        }
    }

    /**
     * Verifies a password against a hash.
     * @param string $password The password to verify.
     * @param string $hash The hash to verify the password against.
     * @return boolean whether the password is correct.
     * @throws InvalidParamException on bad password or hash parameters or if crypt() with Blowfish hash is not available.
     * @throws InvalidConfigException when an unsupported password hash strategy is configured.
     * @see generatePasswordHash()
     */
    public function validatePassword($password, $hash)
    {
        if (!is_string($password) || $password === '') {
            throw new InvalidParamException('Password must be a string and cannot be empty.');
        }

        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches) || $matches[1] < 4 || $matches[1] > 30) {
            throw new InvalidParamException('Hash is invalid.');
        }

        switch ($this->passwordHashStrategy) {
            case 'password_hash':
                if (!function_exists('password_verify')) {
                    throw new InvalidConfigException('Password hash key strategy "password_hash" requires PHP >= 5.5.0, either upgrade your environment or use another strategy.');
                }
                return password_verify($password, $hash);
            case 'crypt':
                $test = crypt($password, $hash);
                $n = strlen($test);
                if ($n !== 60) {
                    return false;
                }
                return $this->compareString($test, $hash);
            default:
                throw new InvalidConfigException("Unknown password hash strategy '{$this->passwordHashStrategy}'");
        }
    }

    /**
     * Generates a salt that can be used to generate a password hash.
     *
     * The PHP [crypt()](http://php.net/manual/en/function.crypt.php) built-in function
     * requires, for the Blowfish hash algorithm, a salt string in a specific format:
     * "$2a$", "$2x$" or "$2y$", a two digit cost parameter, "$", and 22 characters
     * from the alphabet "./0-9A-Za-z".
     *
     * @param integer $cost the cost parameter
     * @return string the random salt value.
     * @throws InvalidParamException if the cost parameter is out of the range of 4 to 31.
     */
    protected function generateSalt($cost = 13)
    {
        $cost = (int) $cost;
        if ($cost < 4 || $cost > 31) {
            throw new InvalidParamException('Cost must be between 4 and 31.');
        }

        // Get a 20-byte random string
        $rand = $this->generateRandomKey(20);
        // Form the prefix that specifies Blowfish (bcrypt) algorithm and cost parameter.
        $salt = sprintf("$2y$%02d$", $cost);
        // Append the random salt data in the required base64 format.
        $salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));

        return $salt;
    }

    /**
     * Performs string comparison using timing attack resistant approach.
     * @see http://codereview.stackexchange.com/questions/13512
     * @param string $expected string to compare.
     * @param string $actual user-supplied string.
     * @return boolean whether strings are equal.
     */
    public function compareString($expected, $actual)
    {
        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = StringHelper::byteLength($expected);
        $actualLength = StringHelper::byteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }
        return $diff === 0;
    }
}
