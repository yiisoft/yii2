<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
    /**
     * SameSite policy Lax will prevent the cookie from being sent by the browser in all cross-site browsing context
     * during CSRF-prone request methods (e.g. POST, PUT, PATCH etc).
     * E.g. a POST request from https://otherdomain.com to https://yourdomain.com will not include the cookie, however a GET request will.
     * When a user follows a link from https://otherdomain.com to https://yourdomain.com it will include the cookie
     * @see sameSite
     */
    const SAME_SITE_LAX = 'Lax';
    /**
     * SameSite policy Strict will prevent the cookie from being sent by the browser in all cross-site browsing context
     * regardless of the request method and even when following a regular link.
     * E.g. a GET request from https://otherdomain.com to https://yourdomain.com or a user following a link from
     * https://otherdomain.com to https://yourdomain.com will not include the cookie.
     * @see sameSite
     */
    const SAME_SITE_STRICT = 'Strict';
    /**
     * SameSite policy None disables the SameSite policy so cookies will be sent in all contexts,
     * i.e in responses to both first-party and cross-origin requests.
     * E.g. a POST request from https://otherdomain.com to https://yourdomain.com will include the cookie.
     * Note: If `sameSite` is set to None, the `secure` attribute must be set to `true` (otherwise the cookie will be blocked by the browser).
     * @see sameSite
     * @see secure
     * @since 2.0.43
     */
    const SAME_SITE_NONE = 'None';

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
     *
     * See https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite for more information about sameSite.
     *
     * @since 2.0.21
     */
    public $sameSite = self::SAME_SITE_LAX;


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
