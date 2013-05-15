<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\HttpException;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
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
	 * Sends a file to user.
	 * @param string $fileName file name
	 * @param string $content content to be set.
	 * @param string $mimeType mime type of the content. If null, it will be guessed automatically based on the given file name.
	 * @param boolean $terminate whether to terminate the current application after calling this method
	 * @throws \yii\base\HttpException when range request is not satisfiable.
	 */
	public function sendFile($fileName, $content, $mimeType = null, $terminate = true)
	{
		if ($mimeType === null && (($mimeType = FileHelper::getMimeTypeByExtension($fileName)) === null)) {
			$mimeType = 'application/octet-stream';
		}

		$fileSize = StringHelper::strlen($content);
		$contentStart = 0;
		$contentEnd = $fileSize - 1;

		// tell the client that we accept range requests
		header('Accept-Ranges: bytes');

		if (isset($_SERVER['HTTP_RANGE'])) {
			// client sent us a multibyte range, can not hold this one for now
			if (strpos(',', $_SERVER['HTTP_RANGE']) !== false) {
				header("Content-Range: bytes $contentStart-$contentEnd/$fileSize");
				throw new HttpException(416, 'Requested Range Not Satisfiable');
			}

			$range = str_replace('bytes=', '', $_SERVER['HTTP_RANGE']);

			// range requests starts from "-", so it means that data must be dumped the end point.
			if ($range[0] === '-') {
				$contentStart = $fileSize - substr($range, 1);
			} else {
				$range = explode('-', $range);
				$contentStart = $range[0];
				$contentEnd = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $fileSize - 1;
			}

			/* Check the range and make sure it's treated according to the specs.
			 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
			 */
			// End bytes can not be larger than $end.
			$contentEnd = ($contentEnd > $fileSize) ? $fileSize : $contentEnd;

			// Validate the requested range and return an error if it's not correct.
			$wrongContentStart = ($contentStart > $contentEnd || $contentStart > $fileSize - 1 || $contentStart < 0);

			if ($wrongContentStart) {   
				header("Content-Range: bytes $contentStart-$contentEnd/$fileSize");
				throw new HttpException(416, 'Requested Range Not Satisfiable');
			}

			header('HTTP/1.1 206 Partial Content');
			header("Content-Range: bytes $contentStart-$contentEnd/$fileSize");
		} else {
			header('HTTP/1.1 200 OK');
		}

		$length = $contentEnd - $contentStart + 1; // Calculate new content length

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: ' . $mimeType);
		header('Content-Length: ' . $length);
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Content-Transfer-Encoding: binary');
		$content = StringHelper::substr($content, $contentStart, $length);

		if ($terminate) {
			// clean up the application first because the file downloading could take long time
			// which may cause timeout of some resources (such as DB connection)
			ob_start();
			Yii::$app->end(0, false);
			ob_end_clean();
			echo $content;
			exit(0);
		} else {
			echo $content;
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
	 * <ul>
	 * <li>Apache: {@link http://tn123.org/mod_xsendfile X-Sendfile}</li>
	 * <li>Lighttpd v1.4: {@link http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file X-LIGHTTPD-send-file}</li>
	 * <li>Lighttpd v1.5: {@link http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file X-Sendfile}</li>
	 * <li>Nginx: {@link http://wiki.nginx.org/XSendfile X-Accel-Redirect}</li>
	 * <li>Cherokee: {@link http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile X-Sendfile and X-Accel-Redirect}</li>
	 * </ul>
	 * So for this method to work the X-SENDFILE option/module should be enabled by the web server and
	 * a proper xHeader should be sent.
	 *
	 * <b>Note:</b>
	 * This option allows to download files that are not under web folders, and even files that are otherwise protected (deny from all) like .htaccess
	 *
	 * <b>Side effects</b>:
	 * If this option is disabled by the web server, when this method is called a download configuration dialog
	 * will open but the downloaded file will have 0 bytes.
	 *
	 * <b>Known issues</b>:
	 * There is a Bug with Internet Explorer 6, 7 and 8 when X-SENDFILE is used over an SSL connection, it will show
	 * an error message like this: "Internet Explorer was not able to open this Internet site. The requested site is either unavailable or cannot be found.".
	 * You can work around this problem by removing the <code>Pragma</code>-header.
	 *
	 * <b>Example</b>:
	 * <pre>
	 * <?php
	 *    Yii::app()->request->xSendFile('/home/user/Pictures/picture1.jpg', array(
	 *        'saveName' => 'image1.jpg',
	 *        'mimeType' => 'image/jpeg',
	 *        'terminate' => false,
	 *    ));
	 * ?>
	 * </pre>
	 * @param string $filePath file name with full path
	 * @param array $options additional options:
	 * <ul>
	 * <li>saveName: file name shown to the user, if not set real file name will be used</li>
	 * <li>mimeType: mime type of the file, if not set it will be guessed automatically based on the file name, if set to null no content-type header will be sent.</li>
	 * <li>xHeader: appropriate x-sendfile header, defaults to "X-Sendfile"</li>
	 * <li>terminate: whether to terminate the current application after calling this method, defaults to true</li>
	 * <li>forceDownload: specifies whether the file will be downloaded or shown inline, defaults to true</li>
	 * <li>addHeaders: an array of additional http headers in header-value pairs</li>
	 * </ul>
	 * @todo
	 */
	public function xSendFile($filePath, $options = array())
	{
		if (!isset($options['forceDownload']) || $options['forceDownload']) {
			$disposition = 'attachment';
		} else {
			$disposition = 'inline';
		}

		if (!isset($options['saveName'])) {
			$options['saveName'] = basename($filePath);
		}

		if (!isset($options['mimeType'])) {
			if (($options['mimeType'] = CFileHelper::getMimeTypeByExtension($filePath)) === null) {
				$options['mimeType'] = 'text/plain';
			}
		}

		if (!isset($options['xHeader'])) {
			$options['xHeader'] = 'X-Sendfile';
		}

		if ($options['mimeType'] !== null) {
			header('Content-type: ' . $options['mimeType']);
		}
		header('Content-Disposition: ' . $disposition . '; filename="' . $options['saveName'] . '"');
		if (isset($options['addHeaders'])) {
			foreach ($options['addHeaders'] as $header => $value) {
				header($header . ': ' . $value);
			}
		}
		header(trim($options['xHeader']) . ': ' . $filePath);

		if (!isset($options['terminate']) || $options['terminate']) {
			Yii::$app->end();
		}
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
	 * @param boolean $terminate whether to terminate the current application
	 * @param integer $statusCode the HTTP status code. Defaults to 302.
	 * See [[http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html]]
	 * for details about HTTP status code.
	 * Note that if the request is an AJAX request, [[ajaxRedirectCode]] will be used instead.
	 */
	public function redirect($url, $terminate = true, $statusCode = 302)
	{
		$url = Html::url($url);
		if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
			$url = Yii::$app->getRequest()->getHostInfo() . $url;
		}
		if (Yii::$app->getRequest()->getIsAjaxRequest()) {
			$statusCode = $this->ajaxRedirectCode;
		}
		header('Location: ' . $url, true, $statusCode);
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
		return Yii::$app->getRequest()->getCookies();
	}
}
