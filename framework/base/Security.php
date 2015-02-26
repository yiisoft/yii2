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
 * > Note: this class requires 'OpenSSL' PHP extension for random key/string generation on Windows and
 * for encryption/decryption on all platforms. For the highest security level PHP version >= 5.5.0 is recommended.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Tom Worster <fsb@thefsb.org>
 * @author Klimov Paul <klimov.paul@gmail.com>
 * @since 2.0
 */
class Security extends Component
{
    /**
     * @var string The cipher to use for encryption and decryption.
     */
    public $cipher = 'AES-128-CBC';
    /**
     * @var array[] Look-up table of block sizes and key sizes for each supported OpenSSL cipher.
     *
     * In each element, the key is one of the ciphers supported by OpenSSL (@see openssl_get_cipher_methods()).
     * The value is an array of two integers, the first is the cipher's block size in bytes and the second is
     * the key size in bytes.
     *
     * > Warning: All OpenSSL ciphers that we recommend are in the default value, i.e. AES in CBC mode.
     *
     * > Note: Yii's encryption protocol uses the same size for cipher key, HMAC signature key and key
     * derivation salt.
     */
    public $allowedCiphers = [
        'AES-128-CBC' => [16, 16],
        'AES-192-CBC' => [16, 24],
        'AES-256-CBC' => [16, 32],
    ];
    /**
     * @var string Hash algorithm for key derivation. Recommend sha256, sha384 or sha512.
     * @see hash_algos()
     */
    public $kdfHash = 'sha256';
    /**
     * @var string Hash algorithm for message authentication. Recommend sha256, sha384 or sha512.
     * @see hash_algos()
     */
    public $macHash = 'sha256';
    /**
     * @var string HKDF info value for derivation of message authentication key.
     * @see hkdf()
     */
    public $authKeyInfo = 'AuthorizationKey';
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
     * Encrypts data.
     *
     * @param string $data data to be encrypted
     * @param boolean $passwordBased set true to use password-based key derivation
     * @param string $secret the encryption password or key
     * @param string $info context/application specific information, e.g. a user ID
     * See [RFC 5869 Section 3.2](https://tools.ietf.org/html/rfc5869#section-3.2) for more details.
     *
     * @return string the encrypted data
     * @throws InvalidConfigException on OpenSSL not loaded
     * @throws Exception on OpenSSL error
     * @see decrypt()
     */
    protected function encrypt($data, $passwordBased, $secret, $info)
    {
        if (!extension_loaded('openssl')) {
            throw new InvalidConfigException('Encryption requires the OpenSSL PHP extension');
        }
        if (!isset($this->allowedCiphers[$this->cipher][0], $this->allowedCiphers[$this->cipher][1])) {
            throw new InvalidConfigException($this->cipher . ' is not an allowed cipher');
        }

        list($blockSize, $keySize) = $this->allowedCiphers[$this->cipher];

        $keySalt = $this->generateRandomKey($keySize);
        if ($passwordBased) {
            $key = $this->pbkdf2($this->kdfHash, $secret, $keySalt, $this->derivationIterations, $keySize);
        } else {
            $key = $this->hkdf($this->kdfHash, $secret, $keySalt, $info, $keySize);
        }

        $iv = $this->generateRandomKey($blockSize);

        $encrypted = openssl_encrypt($data, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new \yii\base\Exception('OpenSSL failure on encryption: ' . openssl_error_string());
        }

        $authKey = $this->hkdf($this->kdfHash, $key, null, $this->authKeyInfo, $keySize);
        $hashed = $this->hashData($iv . $encrypted, $authKey);

        /*
         * Output: [keySalt][MAC][IV][ciphertext]
         * - keySalt is KEY_SIZE bytes long
         * - MAC: message authentication code, length same as the output of MAC_HASH
         * - IV: initialization vector, length $blockSize
         */
        return $keySalt . $hashed;
    }

    /**
     * Decrypts data.
     *
     * @param string $data encrypted data to be decrypted.
     * @param boolean $passwordBased set true to use password-based key derivation
     * @param string $secret the decryption password or key
     * @param string $info context/application specific information, @see encrypt()
     *
     * @return bool|string the decrypted data or false on authentication failure
     * @throws InvalidConfigException on OpenSSL not loaded
     * @throws Exception on OpenSSL error
     * @see encrypt()
     */
    protected function decrypt($data, $passwordBased, $secret, $info)
    {
        if (!extension_loaded('openssl')) {
            throw new InvalidConfigException('Encryption requires the OpenSSL PHP extension');
        }
        if (!isset($this->allowedCiphers[$this->cipher][0], $this->allowedCiphers[$this->cipher][1])) {
            throw new InvalidConfigException($this->cipher . ' is not an allowed cipher');
        }

        list($blockSize, $keySize) = $this->allowedCiphers[$this->cipher];

        $keySalt = StringHelper::byteSubstr($data, 0, $keySize);
        if ($passwordBased) {
            $key = $this->pbkdf2($this->kdfHash, $secret, $keySalt, $this->derivationIterations, $keySize);
        } else {
            $key = $this->hkdf($this->kdfHash, $secret, $keySalt, $info, $keySize);
        }

        $authKey = $this->hkdf($this->kdfHash, $key, null, $this->authKeyInfo, $keySize);
        $data = $this->validateData(StringHelper::byteSubstr($data, $keySize, null), $authKey);
        if ($data === false) {
            return false;
        }

        $iv = StringHelper::byteSubstr($data, 0, $blockSize);
        $encrypted = StringHelper::byteSubstr($data, $blockSize, null);

        $decrypted = openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            throw new \yii\base\Exception('OpenSSL failure on decryption: ' . openssl_error_string());
        }

        return $decrypted;
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
        $hash = hash_hmac($this->macHash, $data, $key, $rawHash);
        if (!$hash) {
            throw new InvalidConfigException('Failed to generate HMAC with hash algorithm: ' . $this->macHash);
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
        $test = @hash_hmac($this->macHash, '', '', $rawHash);
        if (!$test) {
            throw new InvalidConfigException('Failed to generate HMAC with hash algorithm: ' . $this->macHash);
        }
        $hashLength = StringHelper::byteLength($test);
        if (StringHelper::byteLength($data) >= $hashLength) {
            $hash = StringHelper::byteSubstr($data, 0, $hashLength);
            $pureData = StringHelper::byteSubstr($data, $hashLength, null);

            $calculatedHash = hash_hmac($this->macHash, $pureData, $key, $rawHash);

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
     * @throws InvalidConfigException if OpenSSL extension is required (e.g. on Windows) but not installed.
     * @throws Exception on failure.
     */
    public function generateRandomKey($length = 32)
    {
        /*
         * Strategy
         *
         * The most common platform is Linux, on which /dev/urandom is the best choice. Many other OSs
         * implement a device called /dev/urandom for Linux compat and it is good too. So if there is
         * a /dev/urandom then it is our first choice regardless of OS.
         *
         * Nearly all other modern Unix-like systems (the BSDs, Unixes and OS X) have a /dev/random
         * that is a good choice. If we didn't get bytes from /dev/urandom then we try this next but
         * only if the system is not Linux. Do not try to read /dev/random on Linux.
         *
         * Finally, OpenSSL can supply CSPR bytes. It is our last resort. On Windows this reads from
         * CryptGenRandom, which is the right thing to do. On other systems that don't have a Unix-like
         * /dev/urandom, it will deliver bytes from its own CSPRNG that is seeded from kernel sources
         * of randomness. Even though it is fast, we don't generally prefer OpenSSL over /dev/urandom
         * because an RNG in user space memory is undesirable.
         *
         * For background, see http://sockpuppet.org/blog/2014/02/25/safely-generate-random-numbers/
         */

        $bytes = '';

        // If we are on Linux or any OS that mimics the Linux /dev/urandom device, e.g. FreeBSD or OS X,
        // then read from /dev/urandom.
        if (file_exists('/dev/urandom')) {
            $handle = fopen('/dev/urandom', 'r');
            if ($handle !== false) {
                $bytes .= fread($handle, $length);
                fclose($handle);
            }
        }

        if (StringHelper::byteLength($bytes) >= $length) {
            return StringHelper::byteSubstr($bytes, 0, $length);
        }

        // If we are not on Linux and there is a /dev/random device then we have a BSD or Unix device
        // that won't block. It's not safe to read from /dev/random on Linux.
        if (php_uname('s') !== 'Linux' && file_exists('/dev/random')) {
            $handle = fopen('/dev/random', 'r');
            if ($handle !== false) {
                $bytes .= fread($handle, $length);
                fclose($handle);
            }
        }

        if (StringHelper::byteLength($bytes) >= $length) {
            return StringHelper::byteSubstr($bytes, 0, $length);
        }

        if (!extension_loaded('openssl')) {
            throw new InvalidConfigException('The OpenSSL PHP extension is not installed.');
        }

        $bytes .= openssl_random_pseudo_bytes($length, $cryptoStrong);

        if (StringHelper::byteLength($bytes) < $length || !$cryptoStrong) {
            throw new Exception('Unable to generate random bytes.');
        }

        return StringHelper::byteSubstr($bytes, 0, $length);
    }

    /**
     * Generates a random string of specified length.
     * The string generated matches [A-Za-z0-9_-]+ and is transparent to URL-encoding.
     *
     * @param integer $length the length of the key in characters
     * @return string the generated random key
     * @throws InvalidConfigException if OpenSSL extension is needed but not installed.
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
     * @throws InvalidParamException on bad password/hash parameters or if crypt() with Blowfish hash is not available.
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
