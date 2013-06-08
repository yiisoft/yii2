<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * ErrorHandler handles uncaught PHP errors and exceptions.
 *
 * ErrorHandler displays these errors using appropriate views based on the
 * nature of the errors and the mode the application runs at.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class ErrorHandler extends Component
{
	/**
	 * @var integer maximum number of source code lines to be displayed. Defaults to 25.
	 */
	public $maxSourceLines = 25;
	/**
	 * @var integer maximum number of trace source code lines to be displayed. Defaults to 10.
	 */
	public $maxTraceSourceLines = 10;
	/**
	 * @var boolean whether to discard any existing page output before error display. Defaults to true.
	 */
	public $discardExistingOutput = true;
	/**
	 * @var string the route (e.g. 'site/error') to the controller action that will be used
	 * to display external errors. Inside the action, it can retrieve the error information
	 * by Yii::$app->errorHandler->error. This property defaults to null, meaning ErrorHandler
	 * will handle the error display.
	 */
	public $errorAction;
	/**
	 * @var string the path of the view file for rendering exceptions and errors.
	 */
	public $mainView = '@yii/views/errorHandler/main.php';
	/**
	 * @var string the path of the view file for rendering exceptions and errors call stack element.
	 */
	public $callStackItemView = '@yii/views/errorHandler/callStackItem.php';
	/**
	 * @var string the path of the view file for rendering previous exceptions.
	 */
	public $previousExceptionView = '@yii/views/errorHandler/previousException.php';
	/**
	 * @var \Exception the exception that is being handled currently.
	 */
	public $exception;


	/**
	 * Handles exception.
	 * @param \Exception $exception to be handled.
	 */
	public function handle($exception)
	{
		$this->exception = $exception;
		if ($this->discardExistingOutput) {
			$this->clearOutput();
		}
		$this->renderException($exception);
	}

	/**
	 * Renders exception.
	 * @param \Exception $exception to be handled.
	 */
	protected function renderException($exception)
	{
		if ($this->errorAction !== null) {
			Yii::$app->runAction($this->errorAction);
		} elseif (!(Yii::$app instanceof \yii\web\Application)) {
			Yii::$app->renderException($exception);
		} else {
			if (!headers_sent()) {
				if ($exception instanceof HttpException) {
					header('HTTP/1.0 ' . $exception->statusCode . ' ' . $exception->getName());
				} else {
					header('HTTP/1.0 500 ' . get_class($exception));
				}
			}
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
				Yii::$app->renderException($exception);
			} else {
				// if there is an error during error rendering it's useful to
				// display PHP error in debug mode instead of a blank screen
				if (YII_DEBUG) {
					ini_set('display_errors', 1);
				}

				$view = new View();
				$request = '';
				foreach (array('GET', 'POST', 'SERVER', 'FILES', 'COOKIE', 'SESSION', 'ENV') as $name) {
					if (!empty($GLOBALS['_' . $name])) {
						$request .= '$_' . $name . ' = ' . var_export($GLOBALS['_' . $name], true) . ";\n\n";
					}
				}
				$request = rtrim($request, "\n\n");
				echo $view->renderFile($this->mainView, array(
					'exception' => $exception,
					'request' => $request,
				), $this);
			}
		}
	}

	/**
	 * Converts special characters to HTML entities.
	 * @param string $text to encode.
	 * @return string encoded original text.
	 */
	public function htmlEncode($text)
	{
		return htmlspecialchars($text, ENT_QUOTES, Yii::$app->charset);
	}

	/**
	 * Removes all output echoed before calling this method.
	 */
	public function clearOutput()
	{
		// the following manual level counting is to deal with zlib.output_compression set to On
		for ($level = ob_get_level(); $level > 0; --$level) {
			@ob_end_clean();
		}
	}

	/**
	 * Adds informational links to the given PHP type/class.
	 * @param string $code type/class name to be linkified.
	 * @return string linkified with HTML type/class name.
	 */
	public function addTypeLinks($code)
	{
		$html = '';
		if (strpos($code, '\\') !== false) {
			// namespaced class
			foreach (explode('\\', $code) as $part) {
				$html .= '<a href="http://yiiframework.com/doc/api/2.0/' . $this->htmlEncode($part) . '" target="_blank">' . $this->htmlEncode($part) . '</a>\\';
			}
			$html = rtrim($html, '\\');
		} elseif (strpos($code, '()') !== false) {
			// method/function call
			$self = $this;
			$html = preg_replace_callback('/^(.*)\(\)$/', function ($matches) use ($self) {
				return '<a href="http://yiiframework.com/doc/api/2.0/' . $self->htmlEncode($matches[1]) . '" target="_blank">' .
					$self->htmlEncode($matches[1]) . '</a>()';
			}, $code);
		}
		return $html;
	}

	/**
	 * Creates HTML containing link to the page with the information on given HTTP status code.
	 * @param integer $statusCode to be used to generate information link.
	 * @param string $statusDescription Description to display after the the status code.
	 * @return string generated HTML with HTTP status code information.
	 */
	public function createHttpStatusLink($statusCode, $statusDescription)
	{
		return '<a href="http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#' . (int)$statusCode .'" target="_blank">HTTP ' . (int)$statusCode . ' &ndash; ' . $statusDescription . '</a>';
	}

	/**
	 * Renders the previous exception stack for a given Exception.
	 * @param \Exception $exception the exception whose precursors should be rendered.
	 * @return string HTML content of the rendered previous exceptions.
	 * Empty string if there are none.
	 */
	public function renderPreviousExceptions($exception)
	{
		if (($previous = $exception->getPrevious()) === null) {
			return '';
		}
		$view = new View();
		return $view->renderFile($this->previousExceptionView, array(
			'exception' => $previous,
			'previousHtml' => $this->renderPreviousExceptions($previous),
		), $this);
	}

	/**
	 * Renders a single call stack element.
	 * @param string|null $file name where call has happened.
	 * @param integer|null $line number on which call has happened.
	 * @param string|null $class called class name.
	 * @param string|null $method called function/method name.
	 * @param integer $index number of the call stack element.
	 * @return string HTML content of the rendered call stack element.
	 */
	public function renderCallStackItem($file, $line, $class, $method, $index)
	{
		$lines = array();
		$begin = $end = 0;
		if ($file !== null && $line !== null) {
			$line--; // adjust line number from one-based to zero-based
			$lines = @file($file);
			if ($line < 0 || $lines === false || ($lineCount = count($lines)) < $line + 1) {
				return '';
			}

			$half = (int)(($index == 0 ? $this->maxSourceLines : $this->maxTraceSourceLines) / 2);
			$begin = $line - $half > 0 ? $line - $half : 0;
			$end = $line + $half < $lineCount ? $line + $half : $lineCount - 1;
		}

		$view = new View();
		return $view->renderFile($this->callStackItemView, array(
			'file' => $file,
			'line' => $line,
			'class' => $class,
			'method' => $method,
			'index' => $index,
			'lines' => $lines,
			'begin' => $begin,
			'end' => $end,
		), $this);
	}

	/**
	 * Determines whether given name of the file belongs to the framework.
	 * @param string $file name to be checked.
	 * @return boolean whether given name of the file belongs to the framework.
	 */
	public function isCoreFile($file)
	{
		return $file === null || strpos(realpath($file), YII_PATH . DIRECTORY_SEPARATOR) === 0;
	}

	/**
	 * Creates string containing HTML link which refers to the home page of determined web-server software
	 * and its full name.
	 * @return string server software information hyperlink.
	 */
	public function createServerInformationLink()
	{
		static $serverUrls = array(
			'http://httpd.apache.org/' => array('apache'),
			'http://nginx.org/' => array('nginx'),
			'http://lighttpd.net/' => array('lighttpd'),
			'http://gwan.com/' => array('g-wan', 'gwan'),
			'http://iis.net/' => array('iis', 'services'),
			'http://php.net/manual/en/features.commandline.webserver.php' => array('development'),
		);
		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			foreach ($serverUrls as $url => $keywords) {
				foreach ($keywords as $keyword) {
					if (stripos($_SERVER['SERVER_SOFTWARE'], $keyword) !== false) {
						return '<a href="' . $url . '" target="_blank">' . $this->htmlEncode($_SERVER['SERVER_SOFTWARE']) . '</a>';
					}
				}
			}
		}
		return '';
	}

	/**
	 * Creates string containing HTML link which refers to the page with the current version
	 * of the framework and version number text.
	 * @return string framework version information hyperlink.
	 */
	public function createFrameworkVersionLink()
	{
		return '<a href="http://github.com/yiisoft/yii2/" target="_blank">' . $this->htmlEncode(Yii::getVersion()) . '</a>';
	}
}
