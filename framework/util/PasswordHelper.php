<?php
/**
 * PasswordHelper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * PasswordHelper provides a simple API for secure password hashing and verification.
 *
 * PasswordHelper uses the Blowfish hash algorithm available in many PHP runtime
 * environments through the PHP [crypt()](http://php.net/manual/en/function.crypt.php)
 * built-in function. As of Dec 2012 it is the strongest algorithm available in PHP
 * and the only algorithm without some security concerns surrounding it. For this reason,
 * PasswordHelper fails to initialize when run in and environment that does not have
 * crypt() and its Blowfish option. Systems with the option include:
 *
 * 1. Most *nix systems since PHP 4 (the algorithm is part of the library function crypt(3));
 * 2. All PHP systems since 5.3.0;
 * 3. All PHP systems with the [Suhosin patch](http://www.hardened-php.net/suhosin/).
 *
 * For more information about password hashing, crypt() and Blowfish, please read
 * the Yii Wiki article [Use crypt() for password storage](http://www.yiiframework.com/wiki/425/use-crypt-for-password-storage/)
 * and the PHP RFC [Adding simple password hashing API](http://wiki.php.net/rfc/password_hash).
 *
 * PasswordHelper throws an exception if the Blowfish hash algorithm is not
 * available in the runtime PHP's crypt() function. It can be used as follows
 *
 * Generate a hash from a password:
 *
 * ~~~
 * $hash = PasswordHelper::hashPassword($password);
 * ~~~
 *
 * This hash can be stored in a database (e.g. `CHAR(64) CHARACTER SET latin1` on MySQL). The
 * hash is usually generated and saved to the database when the user enters a new password.
 * But it can also be useful to generate and save a hash after validating a user's
 * password in order to change the cost or refresh the salt.
 *
 * To verify a password, fetch the user's saved hash from the database (into `$hash`) and:
 *
 * ~~~
 * if (PasswordHelper::verifyPassword($password, $hash) {
 *     // password is good
 * } else {
 *     // password is bad
 * }
 * ~~~
 *
 * @author Tom Worster <fsb@thefsb.org>
 * @since 2.0
 */

class PasswordHelper
{

	/**
	 * Encrypts data.
	 * @param string $data data to be encrypted.
	 * @param string $key the encryption secret key
	 * @return string the encrypted data
	 * @throws Exception if PHP Mcrypt extension is not loaded or failed to be initialized
	 */
	public static function encrypt($data, $key)
	{
		$module = static::openCryptModule();
		$key = StringHelper::substr($key, 0, mcrypt_enc_get_key_size($module));
		srand();
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
		mcrypt_generic_init($module, $key, $iv);
		$encrypted = $iv . mcrypt_generic($module, $data);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return $encrypted;
	}

	/**
	 * Decrypts data
	 * @param string $data data to be decrypted.
	 * @param string $key the decryption secret key
	 * @return string the decrypted data
	 * @throws Exception if PHP Mcrypt extension is not loaded or failed to be initialized
	 */
	public static function decrypt($data, $key)
	{
		$module = static::openCryptModule();
		$key = StringHelper::substr($key, 0, mcrypt_enc_get_key_size($module));
		$ivSize = mcrypt_enc_get_iv_size($module);
		$iv = StringHelper::substr($data, 0, $ivSize);
		mcrypt_generic_init($module, $key, $iv);
		$decrypted = mdecrypt_generic($module, StringHelper::substr($data, $ivSize, StringHelper::strlen($data)));
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return rtrim($decrypted, "\0");
	}

	/**
	 * Prefixes data with an HMAC.
	 * @param string $data data to be hashed.
	 * @param string $key the private key to be used for generating HMAC. Defaults to null, meaning using {@link validationKey}.
	 * @return string data prefixed with HMAC
	 */
	public static function hashData($data, $key)
	{
		return hash_hmac('sha1', $data, $key) . $data;
	}

	/**
	 * Validates if data is tampered.
	 * @param string $data data to be validated. The data must be previously
	 * generated using {@link hashData()}.
	 * @param string $key the private key to be used for generating HMAC. Defaults to null, meaning using {@link validationKey}.
	 * @return string the real data with HMAC stripped off. False if the data
	 * is tampered.
	 */
	public function validateData($data, $key = null)
	{
		$len = $this->strlen($this->computeHMAC('test'));
		if ($this->strlen($data) >= $len) {
			$hmac = $this->substr($data, 0, $len);
			$data2 = $this->substr($data, $len, $this->strlen($data));
			return $hmac === $this->computeHMAC($data2, $key) ? $data2 : false;
		} else {
			return false;
		}
	}

	/**
	 * Opens the mcrypt module.
	 * @return resource the mcrypt module handle.
	 * @throws InvalidConfigException if mcrypt extension is not installed
	 * @throws Exception if mcrypt initialization fails
	 */
	protected static function openCryptModule()
	{
		if (!extension_loaded('mcrypt')) {
			throw new InvalidConfigException('The mcrypt PHP extension is not installed.');
		}
		$module = @mcrypt_module_open('des', '', MCRYPT_MODE_CBC, '');
		if ($module === false) {
			throw new Exception('Failed to initialize the mcrypt module.');
		}
		return $module;
	}

	/**
	 * Generate a secure hash from a password and a random salt.
	 *
	 * Uses the PHP [crypt()](http://php.net/manual/en/function.crypt.php) built-in function
	 * with the Blowfish hash option.
	 *
	 * @param string $password The password to be hashed.
	 * @param integer $cost Cost parameter used by the Blowfish hash algorithm.
	 * The higher the value of cost,
	 * the longer it takes to generate the hash and to verify a password against it. Higher cost
	 * therefore slows down a brute-force attack. For best protection against brute for attacks,
	 * set it to the highest value that is tolerable on production servers. The time taken to
	 * compute the hash doubles for every increment by one of $cost. So, for example, if the
	 * hash takes 1 second to compute when $cost is 14 then then the compute time varies as
	 * 2^($cost - 14) seconds.
	 * @throws Exception on bad password parameter or cost parameter
	 * @return string The password hash string, ASCII and not longer than 64 characters.
	 */
	public static function hashPassword($password, $cost = 13)
	{
		$salt = static::generateSalt($cost);
		$hash = crypt($password, $salt);

		if (!is_string($hash) || strlen($hash) < 32) {
			throw new Exception('Unknown error occurred while generating hash.');
		}

		return $hash;
	}

	/**
	 * Verifies a password against a hash.
	 * @param string $password The password to verify.
	 * @param string $hash The hash to verify the password against.
	 * @return boolean whether the password is correct.
	 * @throws InvalidParamException on bad password or hash parameters or if crypt() with Blowfish hash is not available.
	 */
	public static function verifyPassword($password, $hash)
	{
		if (!is_string($password) || $password === '') {
			throw new InvalidParamException('Password must be a string and cannot be empty.');
		}

		if (!preg_match('/^\$2[axy]\$(\d\d)\$[\./0-9A-Za-z]{22}/', $hash, $matches) || $matches[1] < 4 || $matches[1] > 30) {
			throw new InvalidParamException('Hash is invalid.');
		}

		$test = crypt($password, $hash);
		$n = strlen($test);
		if (strlen($test) < 32 || $n !== strlen($hash)) {
			return false;
		}

		// Use a for-loop to compare two strings to prevent timing attacks. See:
		// http://codereview.stackexchange.com/questions/13512
		$check = 0;
		for ($i = 0; $i < $n; ++$i) {
			$check |= (ord($test[$i]) ^ ord($hash[$i]));
		}

		return $check === 0;
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
	 * @throws InvalidParamException if the cost parameter is not between 4 and 30
	 */
	protected static function generateSalt($cost = 13)
	{
		$cost = (int)$cost;
		if ($cost < 4 || $cost > 30) {
			throw new InvalidParamException('Cost must be between 4 and 31.');
		}

		// Get 20 * 8bits of pseudo-random entropy from mt_rand().
		$rand = '';
		for ($i = 0; $i < 20; ++$i) {
			$rand .= chr(mt_rand(0, 255));
		}

		// Add the microtime for a little more entropy.
		$rand .= microtime();
		// Mix the bits cryptographically into a 20-byte binary string.
		$rand = sha1($rand, true);
		// Form the prefix that specifies Blowfish algorithm and cost parameter.
		$salt = sprintf("$2y$%02d$", $cost);
		// Append the random salt data in the required base64 format.
		$salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));
		return $salt;
	}
}