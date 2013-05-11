<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
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
	 * @var string the route (eg 'site/error') to the controller action that will be used to display external errors.
	 * Inside the action, it can retrieve the error information by \Yii::$app->errorHandler->error.
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
	 * Handles exception
	 * @param \Exception $exception
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
	 * Renders exception
	 * @param \Exception $exception
	 */
	protected function renderException($exception)
	{
		if ($this->errorAction !== null) {
			\Yii::$app->runAction($this->errorAction);
		} elseif (\Yii::$app instanceof \yii\web\Application) {
			if (!headers_sent()) {
				$errorCode = $exception instanceof HttpException ? $exception->statusCode : 500;
				header("HTTP/1.0 $errorCode " . get_class($exception));
			}
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
				\Yii::$app->renderException($exception);
			} else {
				// if there is an error during error rendering it's useful to
				// display PHP error in debug mode instead of a blank screen
				if (YII_DEBUG) {
					ini_set('display_errors', 1);
				}

				$view = new View;
				if (!YII_DEBUG || $exception instanceof UserException) {
					$viewName = $this->errorView;
				} else {
					$viewName = $this->exceptionView;
				}
				echo $view->renderFile($viewName, array(
					'exception' => $exception,
				), $this);
			}
		} else {
			\Yii::$app->renderException($exception);
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

	/**
	 * Renders calls stack trace
	 * @param array $trace
	 */
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
			if (isset($t['file'])) {
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

	/**
	 * Converts special characters to HTML entities
	 * @param string $text text to encode
	 * @return string
	 */
	public function htmlEncode($text)
	{
		return htmlspecialchars($text, ENT_QUOTES, \Yii::$app->charset);
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
	public function renderAsHtml($exception)
	{
		$view = new View;
		$name = !YII_DEBUG || $exception instanceof HttpException ? $this->errorView : $this->exceptionView;
		echo $view->renderFile($name, array(
			'exception' => $exception,
		), $this);
	}
}
