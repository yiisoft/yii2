<?php
/**
 * Response class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\util\FileHelper;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Response extends \yii\base\Response
{
	/**
	 * Sends a file to user.
	 * @param string $fileName file name
	 * @param string $content content to be set.
	 * @param string $mimeType mime type of the content. If null, it will be guessed automatically based on the given file name.
	 * @param boolean $terminate whether to terminate the current application after calling this method
	 * @todo
	 */
	public function sendFile($fileName, $content, $mimeType = null, $terminate = true)
	{
		if ($mimeType === null && ($mimeType = FileHelper::getMimeType($fileName)) === null) {
			$mimeType = 'application/octet-stream';
		}
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Content-type: $mimeType");
		if (ob_get_length() === false) {
			header('Content-Length: ' . (function_exists('mb_strlen') ? mb_strlen($content, '8bit') : strlen($content)));
		}
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		header('Content-Transfer-Encoding: binary');

		if ($terminate) {
			// clean up the application first because the file downloading could take long time
			// which may cause timeout of some resources (such as DB connection)
			Yii::app()->end(0, false);
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
	 * <b>Example</b>:
	 * <pre>
	 * <?php
	 *    Yii::app()->request->xSendFile('/home/user/Pictures/picture1.jpg',array(
	 *        'saveName'=>'image1.jpg',
	 *        'mimeType'=>'image/jpeg',
	 *        'terminate'=>false,
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
	 * <li>forceDownload: specifies whether the file will be downloaded or shown inline, defaults to true. (Since version 1.1.9.)</li>
	 * <li>addHeaders: an array of additional http headers in header-value pairs (available since version 1.1.10)</li>
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
			Yii::app()->end();
		}
	}


	/**
	 * Redirects the browser to the specified URL.
	 * @param string $url URL to be redirected to. If the URL is a relative one, the base URL of
	 * the application will be inserted at the beginning.
	 * @param boolean $terminate whether to terminate the current application
	 * @param integer $statusCode the HTTP status code. Defaults to 302. See {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html}
	 * for details about HTTP status code.
	 */
	public function redirect($url, $terminate = true, $statusCode = 302)
	{
		if (strpos($url, '/') === 0) {
			$url = $this->getHostInfo() . $url;
		}
		header('Location: ' . $url, true, $statusCode);
		if ($terminate) {
			Yii::app()->end();
		}
	}
}
