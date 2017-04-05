<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\InvalidConfigException;

/**
 * Cookie represents information related with a cookie, such as [[name]], [[value]], [[domain]], etc.
 *
 * For more details and usage information on Cookie, see the [guide article on handling cookies](guide:runtime-sessions-cookies).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Cookie extends \yii\base\Object
{
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

    /**
     * Unserializes a cookie received by the client.
     * @param string $data json encoded cookie value
     * @return self|null
     * @since 2.0.12
     */
    public static function fromDataString($data)
    {
        if (($unserialized = json_decode($data, true)) !== false) {
            $result = new self();
            $result->name = $unserialized[0];
            $result->value = $unserialized[1];
            $result->expire = 0;
            return $result;
        }
    }

    /**
     * Unserializes a cookie received by the client.
     * @return string the json encoded value of the cookie
     * @throws InvalidConfigException if cookie does not have a name
     * @since 2.0.12
     */
    public function toDataString()
    {
        if (!isset($this->name)) {
            throw new InvalidConfigException("Cookie serialization requires a name.");
        }
        return json_encode([
            $this->name,
            $this->value
        ]);
    }
}
