<?php
/**
 * ErrorHandler class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ErrorHandler handles uncaught PHP errors and exceptions.
 *
 * ErrorHandler displays these errors using appropriate views based on the
 * nature of the errors and the mode the application runs at.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
use yii\util\VarDumper;

class ErrorHandler extends ApplicationComponent
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
	 * @var string the route (eg 'site/error') to the controller action that will be used to display external errors.
	 * Inside the action, it can retrieve the error information by \Yii::$application->errorHandler->error.
	 * This property defaults to null, meaning ErrorHandler will handle the error display.
	 */
	public $errorAction;
	/**
	 * @var string the path of the view file for rendering exceptions
	 */
	public $exceptionView = '@yii/views/exception.php';
	/**
	 * @var string the path of the view file for rendering errors
	 */
	public $errorView = '@yii/views/error.php';
	/**
	 * @var \Exception the exception that is being handled currently
	 */
	public $exception;
	/**
	 * @var boolean whether to log errors also using error_log(). Defaults to true.
	 * Note that errors captured by the error handler are always logged by [[\Yii::error()]].
	 */
	public $logErrors = true;

	public function init()
	{
		set_exception_handler(array($this, 'handleException'));
		set_error_handler(array($this, 'handleError'), error_reporting());
	}

	/**
	 * Handles PHP execution errors such as warnings, notices.
	 *
	 * This method is used as a PHP error handler. It will simply raise an `ErrorException`.
	 *
	 * @param integer $code the level of the error raised
	 * @param string $message the error message
	 * @param string $file the filename that the error was raised in
	 * @param integer $line the line number the error was raised at
	 * @throws \ErrorException the error exception
	 */
	public function handleError($code, $message, $file, $line)
	{
		throw new \ErrorException($message, 0, $code, $file, $line);
	}

	/**
	 * @param \Exception $exception
	 */
	public function handleException($exception)
	{
		// disable error capturing to avoid recursive errors while handling exceptions
		restore_error_handler();
		restore_exception_handler();

		$this->exception = $exception;
		$this->logException($exception);

		if ($this->discardExistingOutput) {
			$this->clearOutput();
		}

		try {
			$this->render($exception);
		} catch (\Exception $e) {
			// use the most primitive way to display exception thrown in the error view
			$this->renderAsText($e);
		}


		try {
			\Yii::$application->end(1);
		} catch (Exception $e2) {
			// use the most primitive way to log error occurred in end()
			$msg = get_class($e2) . ': ' . $e2->getMessage() . ' (' . $e2->getFile() . ':' . $e2->getLine() . ")\n";
			$msg .= $e2->getTraceAsString() . "\n";
			$msg .= "Previous error:\n";
			$msg .= $e2->getTraceAsString() . "\n";
			$msg .= '$_SERVER=' . var_export($_SERVER, true);
			error_log($msg);
			exit(1);
		}
	}

	protected function render($exception)
	{
		if ($this->errorAction !== null) {
			\Yii::$application->runController($this->errorAction);
		} elseif (\Yii::$application instanceof \yii\web\Application) {
			if (!headers_sent()) {
				$errorCode = $exception instanceof HttpException ? $exception->statusCode : 500;
				header("HTTP/1.0 $errorCode " . get_class($exception));
			}
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
				$this->renderAsText($exception);
			} else {
				$this->renderAsHtml($exception);
			}
		} else {
			$this->renderAsText($exception);
		}
	}

	/**
	 * Returns server and Yii version information.
	 * @return string server version information.
	 */
	public function getVersionInfo()
	{
		$version = '<a href="http://www.yiiframework.com/">Yii Framework</a>/' . \Yii::getVersion();
		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			$version = $_SERVER['SERVER_SOFTWARE'] . ' ' . $version;
		}
		return $version;
	}

	/**
	 * Converts arguments array to its string representation
	 *
	 * @param array $args arguments array to be converted
	 * @return string string representation of the arguments array
	 */
	public function argumentsToString($args)
	{
		$isAssoc = $args !== array_values($args);
		$count = 0;
		foreach ($args as $key => $value) {
			$count++;
			if ($count >= 5) {
				if ($count > 5) {
					unset($args[$key]);
				} else {
					$args[$key] = '...';
				}
				continue;
			}

			if (is_object($value)) {
				$args[$key] = get_class($value);
			} elseif (is_bool($value)) {
				$args[$key] = $value ? 'true' : 'false';
			} elseif (is_string($value)) {
				if (strlen($value) > 64) {
					$args[$key] = '"' . substr($value, 0, 64) . '..."';
				} else {
					$args[$key] = '"' . $value . '"';
				}
			} elseif (is_array($value)) {
				$args[$key] = 'array(' . $this->argumentsToString($value) . ')';
			} elseif ($value === null) {
				$args[$key] = 'null';
			} elseif (is_resource($value)) {
				$args[$key] = 'resource';
			}

			if (is_string($key)) {
				$args[$key] = '"' . $key . '" => ' . $args[$key];
			} elseif ($isAssoc) {
				$args[$key] = $key . ' => ' . $args[$key];
			}
		}
		return implode(', ', $args);
	}

	/**
	 * Returns a value indicating whether the call stack is from application code.
	 * @param array $trace the trace data
	 * @return boolean whether the call stack is from application code.
	 */
	public function isCoreCode($trace)
	{
		if (isset($trace['file'])) {
			return $trace['file'] === 'unknown' || strpos(realpath($trace['file']), YII_PATH . DIRECTORY_SEPARATOR) === 0;
		}
		return false;
	}

	/**
	 * Renders the source code around the error line.
	 * @param string $file source file path
	 * @param integer $errorLine the error line number
	 * @param integer $maxLines maximum number of lines to display
	 */
	public function renderSourceCode($file, $errorLine, $maxLines)
	{
		$errorLine--; // adjust line number to 0-based from 1-based
		if ($errorLine < 0 || ($lines = @file($file)) === false || ($lineCount = count($lines)) <= $errorLine) {
			return;
		}

		$halfLines = (int)($maxLines / 2);
		$beginLine = $errorLine - $halfLines > 0 ? $errorLine - $halfLines : 0;
		$endLine = $errorLine + $halfLines < $lineCount ? $errorLine + $halfLines : $lineCount - 1;
		$lineNumberWidth = strlen($endLine + 1);

		$output = '';
		for ($i = $beginLine; $i <= $endLine; ++$i) {
			$isErrorLine = $i === $errorLine;
			$code = sprintf("<span class=\"ln" . ($isErrorLine ? ' error-ln' : '') . "\">%0{$lineNumberWidth}d</span> %s", $i + 1, $this->htmlEncode(str_replace("\t", '    ', $lines[$i])));
			if (!$isErrorLine) {
				$output .= $code;
			} else {
				$output .= '<span class="error">' . $code . '</span>';
			}
		}
		echo '<div class="code"><pre>' . $output . '</pre></div>';
	}

	public function renderTrace($trace)
	{
		$count = 0;
		echo "<table>\n";
		foreach ($trace as $n => $t) {
			if ($this->isCoreCode($t)) {
				$cssClass = 'core collapsed';
			} elseif (++$count > 3) {
				$cssClass = 'app collapsed';
			} else {
				$cssClass = 'app expanded';
			}

			$hasCode = isset($t['file']) && $t['file'] !== 'unknown' && is_file($t['file']);
			echo "<tr class=\"trace $cssClass\"><td class=\"number\">#$n</td><td class=\"content\">";
			echo '<div class="trace-file">';
			if ($hasCode) {
				echo '<div class="plus">+</div><div class="minus">-</div>';
			}
			echo '&nbsp;';
			if(isset($t['file'])) {
				echo $this->htmlEncode($t['file']) . '(' . $t['line'] . '): ';
			}
			if (!empty($t['class'])) {
				echo '<strong>' . $t['class'] . '</strong>' . $t['type'];
			}
			echo '<strong>' . $t['function'] . '</strong>';
			echo '(' . (empty($t['args']) ? '' : $this->htmlEncode($this->argumentsToString($t['args']))) . ')';
			echo '</div>';
			if ($hasCode) {
				$this->renderSourceCode($t['file'], $t['line'], $this->maxTraceSourceLines);
			}
			echo "</td></tr>\n";
		}
		echo '</table>';
	}

	public function htmlEncode($text)
	{
		return htmlspecialchars($text, ENT_QUOTES, \Yii::$application->charset);
	}

	public function logException($exception)
	{
		$category = get_class($exception);
		if ($exception instanceof HttpException) {
			/** @var $exception HttpException */
			$category .= '\\' . $exception->statusCode;
		} elseif ($exception instanceof \ErrorException) {
			/** @var $exception \ErrorException */
			$category .= '\\' . $exception->getSeverity();
		}
		\Yii::error((string)$exception, $category);
		if ($this->logErrors) {
			error_log($exception);
		}
	}

	public function clearOutput()
	{
		// the following manual level counting is to deal with zlib.output_compression set to On
		for ($level = ob_get_level(); $level > 0; --$level) {
			@ob_end_clean();
		}
	}

	/**
	 * @param \Exception $exception
	 */
	public function renderAsText($exception)
	{
		if (YII_DEBUG) {
			echo $exception;
		} else {
			echo get_class($exception) . ': ' . $exception->getMessage();
		}
	}

	/**
	 * @param \Exception $exception
	 */
	public function renderAsHtml($exception)
	{
		$view = new View;
		$view->context = $this;
		$name = !YII_DEBUG || $exception instanceof HttpException ? $this->errorView : $this->exceptionView;
		echo $view->render($name, array(
			'exception' => $exception,
		));
	}
}
