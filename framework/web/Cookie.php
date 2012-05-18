<?php
/**
 * CHttpCookie class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * A CHttpCookie instance stores a single cookie, including the cookie name, value, domain, path, expire, and secure.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CHttpCookie extends CComponent
{
	/**
	 * @var string name of the cookie
	 */
	public $name;
	/**
	 * @var string value of the cookie
	 */
	public $value='';
	/**
	 * @var string domain of the cookie
	 */
	public $domain='';
	/**
	 * @var integer the timestamp at which the cookie expires. This is the server timestamp. Defaults to 0, meaning "until the browser is closed".
	 */
	public $expire=0;
	/**
	 * @var string the path on the server in which the cookie will be available on. The default is '/'.
	 */
	public $path='/';
	/**
	 * @var boolean whether cookie should be sent via secure connection
	 */
	public $secure=false;
	/**
	 * @var boolean whether the cookie should be accessible only through the HTTP protocol.
	 * By setting this property to true, the cookie will not be accessible by scripting languages,
	 * such as JavaScript, which can effectly help to reduce identity theft through XSS attacks.
	 * Note, this property is only effective for PHP 5.2.0 or above.
	 */
	public $httpOnly=false;

	/**
	 * Constructor.
	 * @param string $name name of this cookie
	 * @param string $value value of this cookie
	 */
	public function __construct($name,$value)
	{
		$this->name=$name;
		$this->value=$value;
	}
}
