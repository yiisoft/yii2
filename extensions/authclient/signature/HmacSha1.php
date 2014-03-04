<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\signature;

use yii\base\NotSupportedException;

/**
 * HmacSha1 represents 'HMAC-SHA1' signature method.
 *
 * Note: This class require PHP "Hash" extension({@link http://php.net/manual/en/book.hash.php}).
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class HmacSha1 extends BaseMethod
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (!function_exists('hash_hmac')) {
			throw new NotSupportedException('PHP "Hash" extension is required.');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'HMAC-SHA1';
	}

	/**
	 * @inheritdoc
	 */
	public function generateSignature($baseString, $key)
	{
		return base64_encode(hash_hmac('sha1', $baseString, $key, true));
	}
}
