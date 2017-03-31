<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseSecurityHelper provides concrete implementation for [[SecurityHelper]].
 *
 * Do not use BaseSecurityHelper. Use [[SecurityHelper]] instead.
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0.12
 */
class BaseSecurityHelper
{
    /**
     * Masks a token to make it uncompressible.
     * Applies random mask to a token and prepends mask used to the result making the string always unique.
     * Used to mitigate BREACH attack by randomizing how token is outputted on each request.
     * @param string $token An unmasked token.
     * @return string A masked token.
     * @since 2.0.12
     */
    public static function maskToken($token)
    {
        // Mask always equal length (in bytes) to token.
        $mask = Yii::$app->security->generateRandomKey(StringHelper::byteLength($token));
        return StringHelper::base64UrlEncode($mask . ($mask ^ $token));
    }

    /**
     * Unmasks a token previously masked by `maskToken`.
     * @param $maskedToken A masked token.
     * @return string An unmasked token, or an empty string in case of invalid token format.
     * @since 2.0.12
     */
    public static function unmaskToken($maskedToken)
    {
        $decoded = StringHelper::base64UrlDecode($maskedToken);
        $length = StringHelper::byteLength($decoded) / 2;
        // Check if the masked token has an even length.
        if (!is_int($length)) {
            return "";
        }
        return StringHelper::byteSubstr($decoded, $length, $length) ^ StringHelper::byteSubstr($decoded, 0, $length);
    }
}
