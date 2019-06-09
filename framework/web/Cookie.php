<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * Cookie represents information related with a cookie, such as [[name]], [[value]], [[domain]], etc.
 *
 * For more details and usage information on Cookie, see the [guide article on handling cookies](guide:runtime-sessions-cookies).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Cookie extends \yii\base\BaseObject
{
    const SAME_SITE_LAX = 'Lax';
    const SAME_SITE_STRICT = 'Strict';

    /**
     * @var string name of the cookie
     */
    public $name;
    /**
     * @var string value of the cookie
     */
    public $value = '';
    /**
     * @var string domain of the cookie
     */
    public $domain = '';
    /**
     * @var int the timestamp at which the cookie expires. This is the server timestamp.
     * Defaults to 0, meaning "until the browser is closed".
     */
    public $expire = 0;
    /**
     * @var string the path on the server in which the cookie will be available on. The default is '/'.
     */
    public $path = '/';
    /**
     * @var bool whether cookie should be sent via secure connection
     */
    public $secure = false;
    /**
     * @var bool whether the cookie should be accessible only through the HTTP protocol.
     * By setting this property to true, the cookie will not be accessible by scripting languages,
     * such as JavaScript, which can effectively help to reduce identity theft through XSS attacks.
     */
    public $httpOnly = true;
    /**
     * @var string SameSite prevents the browser from sending this cookie along with cross-site requests.
     * Please note that this feature is only supported in PHP version >= 7.3.0 
     * For securtiy, an exception will be thrown if `sameSite` is set in an unsupported version of PHP.
     * To use this feature across different PHP versions check the version first. E.g.
     * ```php
     * $cookie->sameSite = PHP_VERSION_ID >= 70300 ? yii\web\Cookie::SAME_SITE_LAX : null,
     * ```
     * See https://www.owasp.org/index.php/SameSite for more information about sameSite.
     *
     * @since 2.0.21
     */
    public $sameSite = null;


    /**
     * Magic method to turn a cookie object into a string without having to explicitly access [[value]].
     *
     * ```php
     * if (isset($request->cookies['name'])) {
     *     $value = (string) $request->cookies['name'];
     * }
     * ```
     *
     * @return string The value of the cookie. If the value property is null, an empty string will be returned.
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
