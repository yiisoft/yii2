<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\SecurityHelper;
use yii\helpers\StringHelper;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Response extends \yii\base\Response
{
	/**
	 * @var integer the HTTP status code that should be used when redirecting in AJAX mode.
	 * This is used by [[redirect()]]. A 2xx code should normally be used for this purpose
	 * so that the AJAX handler will treat the response as a success.
	 * @see redirect
	 */
	public $ajaxRedirectCode = 278;
	/**
	 * @var string
	 */
	public $content;
	/**
	 * @var string
	 */
	public $statusText;
	/**
	 * @var string the charset to use. If not set, [[\yii\base\Application::charset]] will be used.
	 */
	public $charset;
	/**
	 * @var string the version of the HTTP protocol to use
	 */
	public $version = '1.0';
	/**
	 * @var array list of HTTP status codes and the corresponding texts
	 */
	public static $httpStatuses = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		118 => 'Connection timed out',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		210 => 'Content Different',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Reserved',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		310 => 'Too many Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested range unsatisfiable',
		417 => 'Expectation failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable entity',
		423 => 'Locked',
		424 => 'Method failure',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		449 => 'Retry With',
		450 => 'Blocked by Windows Parental Controls',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway ou Proxy Error',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		507 => 'Insufficient storage',
		508 => 'Loop Detected',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
	);

	/**
	 * @var integer the HTTP status code to send with the response.
	 */
	private $_statusCode;
	/**
	 * @var HeaderCollection
	 */
	private $_headers;


	public function init()
	{
		if ($this->charset === null) {
			$this->charset = Yii::$app->charset;
		}
	}

	public function begin()
	{
		parent::begin();
		$this->beginBuffer();
	}

	public function end()
	{
		$this->content .= $this->endBuffer();
		$this->send();
		parent::end();
	}

	/**
	 * @return integer the HTTP status code to send with the response.
	 */
	public function getStatusCode()
	{
		return $this->_statusCode;
	}

	public function setStatusCode($value, $text = null)
	{
		$this->_statusCode = (int)$value;
		if ($this->getIsInvalid()) {
			throw new InvalidParamException("The HTTP status code is invalid: $value");
		}
		if ($text === null) {
			$this->statusText = isset(self::$httpStatuses[$this->_statusCode]) ? self::$httpStatuses[$this->_statusCode] : '';
		} else {
			$this->statusText = $text;
		}
	}

	/**
	 * Returns the header collection.
	 * The header collection contains the currently registered HTTP headers.
	 * @return HeaderCollection the header collection
	 */
	public function getHeaders()
	{
		if ($this->_headers === null) {
			$this->_headers = new HeaderCollection;
		}
		return $this->_headers;
	}

	public function renderJson($data)
	{
		$this->getHeaders()->set('Content-Type', 'application/json');
		$this->content = Json::encode($data);
		$this->send();
	}

	public function renderJsonp($data, $callbackName)
	{
		$this->getHeaders()->set('Content-Type', 'text/javascript');
		$data = Json::encode($data);
		$this->content = "$callbackName($data);";
		$this->send();
	}

	/**
	 * Sends the response to the client.
	 * @return boolean true if the response was sent
	 */
	public function send()
	{
		$this->sendHeaders();
		$this->sendContent();
		for ($level = ob_get_level(); $level > 0; --$level) {
			if (!@ob_end_flush()) {
				ob_clean();
			}
		}
		flush();
	}

	public function reset()
	{
		$this->_headers = null;
		$this->_statusCode = null;
		$this->statusText = null;
		$this->content = null;
	}

	/**
	 * Sends the response headers to the client
	 */
	protected function sendHeaders()
	{
		if (headers_sent()) {
			return;
		}
		$statusCode = $this->getStatusCode();
		if ($statusCode !== null) {
			header("HTTP/{$this->version} $statusCode {$this->statusText}");
		}
		if ($this->_headers) {
			$headers = $this->getHeaders();
			foreach ($headers as $name => $values) {
				foreach ($values as $value) {
					header("$name: $value", false);
				}
			}
			$headers->removeAll();
		}
		$this->sendCookies();
	}

	/**
	 * Sends the cookies to the client.
	 */
	protected function sendCookies()
	{
		if ($this->_cookies === null) {
			return;
		}
		$request = Yii::$app->getRequest();
		if ($request->enableCookieValidation) {
			$validationKey = $request->getCookieValidationKey();
		}
		foreach ($this->getCookies() as $cookie) {
			$value = $cookie->value;
			if ($cookie->expire != 1  && isset($validationKey)) {
				$value = SecurityHelper::hashData(serialize($value), $validationKey);
			}
			setcookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
		}
		$this->getCookies()->removeAll();
	}

	/**
	 * Sends the response content to the client
	 */
	protected function sendContent()
	{
		echo $this->content;
		$this->content = null;
	}

	/**
	 * Sends a file to the browser.
	 * @param string $filePath the path of the file to be sent.
	 * @param string $attachmentName the file name shown to the user. If null, it will be determined from `$filePath`.
	 * @param string $mimeType the MIME type of the content. If null, it will be guessed based on `$filePath`
	 */
	public function sendFile($filePath, $attachmentName = null, $mimeType = null)
	{
		if ($mimeType === null && ($mimeType = FileHelper::getMimeTypeByExtension($filePath)) === null) {
			$mimeType = 'application/octet-stream';
		}
		if ($attachmentName === null) {
			$attachmentName = basename($filePath);
		}
		$handle = fopen($filePath, 'rb');
		$this->sendStreamAsFile($handle, $attachmentName, $mimeType);
	}

	/**
	 * Sends the specified content as a file to the browser.
	 * @param string $content the content to be sent. The existing [[content]] will be discarded.
	 * @param string $attachmentName the file name shown to the user.
	 * @param string $mimeType the MIME type of the content.
	 */
	public function sendContentAsFile($content, $attachmentName, $mimeType = 'application/octet-stream')
	{
		$headers = $this->getHeaders();
		$contentLength = StringHelper::strlen($content);
		$range = $this->getHttpRange($contentLength);
		if ($range === false) {
			$headers->set('Content-Range', "bytes */$contentLength");
			throw new HttpException(416, Yii::t('yii', 'Requested range not satisfiable'));
		}

		$headers->addDefault('Pragma', 'public')
			->addDefault('Accept-Ranges', 'bytes')
			->addDefault('Expires', '0')
			->addDefault('Content-Type', $mimeType)
			->addDefault('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
			->addDefault('Content-Transfer-Encoding', 'binary')
			->addDefault('Content-Length', StringHelper::strlen($content))
			->addDefault('Content-Disposition', "attachment; filename=\"$attachmentName\"");

		list($begin, $end) = $range;
		if ($begin !=0 || $end != $contentLength - 1) {
			$this->setStatusCode(206);
			$headers->set('Content-Range', "bytes $begin-$end/$contentLength");
			$this->content = StringHelper::substr($content, $begin, $end - $begin + 1);
		} else {
			$this->setStatusCode(200);
			$this->content = $content;
		}

		$this->send();
	}

	/**
	 * Sends the specified stream as a file to the browser.
	 * @param resource $handle the handle of the stream to be sent.
	 * @param string $attachmentName the file name shown to the user.
	 * @param string $mimeType the MIME type of the stream content.
	 * @throws HttpException if the requested range cannot be satisfied.
	 */
	public function sendStreamAsFile($handle, $attachmentName, $mimeType = 'application/octet-stream')
	{
		$headers = $this->getHeaders();
		fseek($handle, 0, SEEK_END);
		$fileSize = ftell($handle);

		$range = $this->getHttpRange($fileSize);
		if ($range === false) {
			$headers->set('Content-Range', "bytes */$fileSize");
			throw new HttpException(416, Yii::t('yii', 'Requested range not satisfiable'));
		}

		list($begin, $end) = $range;
		if ($begin !=0 || $end != $fileSize - 1) {
			$this->setStatusCode(206);
			$headers->set('Content-Range', "bytes $begin-$end/$fileSize");
		} else {
			$this->setStatusCode(200);
		}

		$length = $end - $begin + 1;

		$headers->addDefault('Pragma', 'public')
			->addDefault('Accept-Ranges', 'bytes')
			->addDefault('Expires', '0')
			->addDefault('Content-Type', $mimeType)
			->addDefault('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
			->addDefault('Content-Transfer-Encoding', 'binary')
			->addDefault('Content-Length', $length)
			->addDefault('Content-Disposition', "attachment; filename=\"$attachmentName\"");

		$this->send();

		fseek($handle, $begin);
		set_time_limit(0); // Reset time limit for big files
		$chunkSize = 8 * 1024 * 1024; // 8MB per chunk
		while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
			if ($pos + $chunkSize > $end) {
				$chunkSize = $end - $pos + 1;
			}
			echo fread($handle, $chunkSize);
			flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
		}
		fclose($handle);
	}

	/**
	 * Determines the HTTP range given in the request.
	 * @param integer $fileSize the size of the file that will be used to validate the requested HTTP range.
	 * @return array|boolean the range (begin, end), or false if the range request is invalid.
	 */
	protected function getHttpRange($fileSize)
	{
		if (!isset($_SERVER['HTTP_RANGE']) || $_SERVER['HTTP_RANGE'] === '-') {
			return array(0, $fileSize - 1);
		}
		if (!preg_match('/^bytes=(\d*)-(\d*)$/', $_SERVER['HTTP_RANGE'], $matches)) {
			return false;
		}
		if ($matches[1] === '') {
			$start = $fileSize - $matches[2];
			$end = $fileSize - 1;
		} elseif ($matches[2] !== '') {
			$start = $matches[1];
			$end = $matches[2];
			if ($end >= $fileSize) {
				$end = $fileSize - 1;
			}
		} else {
			$start = $matches[1];
			$end = $fileSize - 1;
		}
		if ($start < 0 || $start > $end) {
			return false;
		} else {
			return array($start, $end);
		}
	}

	/**
	 * Sends existing file to a browser as a download using x-sendfile.
	 *
	 * X-Sendfile is a feature allowing a web application to redirect the request for a file to the webserver
	 * that in turn processes the request, this way eliminating the need to perform tasks like reading the file
	 * and sending it to the user. When dealing with a lot of files (or very big files) this can lead to a great
	 * increase in performance as the web application is allowed to terminate earlier while the webserver is
	 * handling the request.
	 *
	 * The request is sent to the server through a special non-standard HTTP-header.
	 * When the web server encounters the presence of such header it will discard all output and send the file
	 * specified by that header using web server internals including all optimizations like caching-headers.
	 *
	 * As this header directive is non-standard different directives exists for different web servers applications:
	 * 
	 * - Apache: [X-Sendfile](http://tn123.org/mod_xsendfile) 
	 * - Lighttpd v1.4: [X-LIGHTTPD-send-file](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
	 * - Lighttpd v1.5: [X-Sendfile](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
	 * - Nginx: [X-Accel-Redirect](http://wiki.nginx.org/XSendfile)
	 * - Cherokee: [X-Sendfile and X-Accel-Redirect](http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)
	 *
	 * So for this method to work the X-SENDFILE option/module should be enabled by the web server and
	 * a proper xHeader should be sent.
	 *
	 * **Note**
	 * 
	 * This option allows to download files that are not under web folders, and even files that are otherwise protected 
	 * (deny from all) like `.htaccess`.
	 *
	 * **Side effects**
	 * 
	 * If this option is disabled by the web server, when this method is called a download configuration dialog
	 * will open but the downloaded file will have 0 bytes.
	 *
	 * **Known issues**
	 * 
	 * There is a Bug with Internet Explorer 6, 7 and 8 when X-SENDFILE is used over an SSL connection, it will show
	 * an error message like this: "Internet Explorer was not able to open this Internet site. The requested site 
	 * is either unavailable or cannot be found.". You can work around this problem by removing the `Pragma`-header.
	 *
	 * **Example**
	 * 
	 * ~~~
	 * Yii::app()->request->xSendFile('/home/user/Pictures/picture1.jpg');
	 * ~~~
	 *
	 * @param string $filePath file name with full path
	 * @param string $mimeType the MIME type of the file. If null, it will be determined based on `$filePath`.
	 * @param string $attachmentName file name shown to the user. If null, it will be determined from `$filePath`.
	 * @param string $xHeader the name of the x-sendfile header.
	 */
	public function xSendFile($filePath, $attachmentName = null, $mimeType = null, $xHeader = 'X-Sendfile')
	{
		if ($mimeType === null && ($mimeType = FileHelper::getMimeTypeByExtension($filePath)) === null) {
			$mimeType = 'application/octet-stream';
		}
		if ($attachmentName === null) {
			$attachmentName = basename($filePath);
		}

		$this->getHeaders()
			->addDefault($xHeader, $filePath)
			->addDefault('Content-Type', $mimeType)
			->addDefault('Content-Disposition', "attachment; filename=\"$attachmentName\"");

		$this->send();
	}

	/**
	 * Redirects the browser to the specified URL.
	 * This method will send out a "Location" header to achieve the redirection.
	 * In AJAX mode, this normally will not work as expected unless there are some
	 * client-side JavaScript code handling the redirection. To help achieve this goal,
	 * this method will use [[ajaxRedirectCode]] as the HTTP status code when performing
	 * redirection in AJAX mode. The following JavaScript code may be used on the client
	 * side to handle the redirection response:
	 *
	 * ~~~
	 * $(document).ajaxSuccess(function(event, xhr, settings) {
	 *     if (xhr.status == 278) {
	 *         window.location = xhr.getResponseHeader('Location');
	 *     }
	 * });
	 * ~~~
	 *
	 * @param array|string $url the URL to be redirected to. [[\yii\helpers\Html::url()]]
	 * will be used to normalize the URL. If the resulting URL is still a relative URL
	 * (one without host info), the current request host info will be used.
	 * @param integer $statusCode the HTTP status code. If null, it will use 302
	 * for normal requests, and [[ajaxRedirectCode]] for AJAX requests.
	 * See [[http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html]]
	 * for details about HTTP status code
	 * @param boolean $terminate whether to terminate the current application
	 */
	public function redirect($url, $statusCode = null, $terminate = true)
	{
		$url = Html::url($url);
		if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
			$url = Yii::$app->getRequest()->getHostInfo() . $url;
		}
		if ($statusCode === null) {
			$statusCode = Yii::$app->getRequest()->getIsAjax() ? $this->ajaxRedirectCode : 302;
		}
		$this->getHeaders()->set('Location', $url);
		$this->setStatusCode($statusCode);
		if ($terminate) {
			Yii::$app->end();
		}
	}

	/**
	 * Refreshes the current page.
	 * The effect of this method call is the same as the user pressing the refresh button of his browser
	 * (without re-posting data).
	 * @param boolean $terminate whether to terminate the current application after calling this method
	 * @param string $anchor the anchor that should be appended to the redirection URL.
	 * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
	 */
	public function refresh($terminate = true, $anchor = '')
	{
		$this->redirect(Yii::$app->getRequest()->getUrl() . $anchor, $terminate);
	}

	private $_cookies;

	/**
	 * Returns the cookie collection.
	 * Through the returned cookie collection, you add or remove cookies as follows,
	 *
	 * ~~~
	 * // add a cookie
	 * $response->cookies->add(new Cookie(array(
	 *     'name' => $name,
	 *     'value' => $value,
	 * ));
	 *
	 * // remove a cookie
	 * $response->cookies->remove('name');
	 * // alternatively
	 * unset($response->cookies['name']);
	 * ~~~
	 *
	 * @return CookieCollection the cookie collection.
	 */
	public function getCookies()
	{
		if ($this->_cookies === null) {
			$this->_cookies = new CookieCollection;
		}
		return $this->_cookies;
	}

	/**
	 * @return boolean whether this response has a valid [[statusCode]].
	 */
	public function getIsInvalid()
	{
		return $this->getStatusCode() < 100 || $this->getStatusCode() >= 600;
	}

	/**
	 * @return boolean whether this response is informational
	 */
	public function getIsInformational()
	{
		return $this->getStatusCode() >= 100 && $this->getStatusCode() < 200;
	}

	/**
	 * @return boolean whether this response is successful
	 */
	public function getIsSuccessful()
	{
		return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
	}

	/**
	 * @return boolean whether this response is a redirection
	 */
	public function getIsRedirection()
	{
		return $this->getStatusCode() >= 300 && $this->getStatusCode() < 400;
	}

	/**
	 * @return boolean whether this response indicates a client error
	 */
	public function getIsClientError()
	{
		return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
	}

	/**
	 * @return boolean whether this response indicates a server error
	 */
	public function getIsServerError()
	{
		return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
	}

	/**
	 * @return boolean whether this response is OK
	 */
	public function getIsOk()
	{
		return $this->getStatusCode() == 200;
	}

	/**
	 * @return boolean whether this response indicates the current request is forbidden
	 */
	public function getIsForbidden()
	{
		return $this->getStatusCode() == 403;
	}

	/**
	 * @return boolean whether this response indicates the currently requested resource is not found
	 */
	public function getIsNotFound()
	{
		return $this->getStatusCode() == 404;
	}

	/**
	 * @return boolean whether this response is empty
	 */
	public function getIsEmpty()
	{
		return in_array($this->getStatusCode(), array(201, 204, 304));
	}
}
