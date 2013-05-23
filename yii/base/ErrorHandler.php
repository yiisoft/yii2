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
	public $view = '@yii/views/errorHandler.php';
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
				echo $view->renderFile($this->view, array('e' => $exception), $this);
			}
		}
	}

	/**
	 * Converts special characters to HTML entities.
	 * @param string $text to encode.
	 * @return string encoded text.
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
}
