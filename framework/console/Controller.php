<?php
/**
 * Controller class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\base\Action;
use yii\base\Exception;

/**
 * Controller is the base class of console command classes.
 *
 * A controller consists of one or several actions known as sub-commands.
 * Users call a console command by specifying the corresponding route which identifies a controller action.
 * The `yiic` program is used when calling a console command, like the following:
 *
 * ~~~
 * yiic <route> [--param1=value1 --param2 ...]
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
	/**
	 * This method is invoked when the request parameters do not satisfy the requirement of the specified action.
	 * The default implementation will throw an exception.
	 * @param Action $action the action being executed
	 * @param Exception $exception the exception about the invalid parameters
	 */
	public function invalidActionParams($action, $exception)
	{
		echo \Yii::t('yii', 'Error: {message}', array(
			'{message}' => $exception->getMessage(),
		));
		\Yii::$application->end(1);
	}

	/**
	 * This method is invoked when extra parameters are provided to an action while it is executed.
	 * @param Action $action the action being executed
	 * @param array $expected the expected action parameters (name => value)
	 * @param array $actual the actual action parameters (name => value)
	 */
	public function extraActionParams($action, $expected, $actual)
	{
		unset($expected['args'], $actual['args']);

		$keys = array_diff(array_keys($actual), array_keys($expected));
		if (!empty($keys)) {
			echo \Yii::t('yii', 'Error: Unknown parameter(s): {params}', array(
				'{params}' => implode(', ', $keys),
			)) . "\n";
			\Yii::$application->end(1);
		}
	}

	/**
	 * Reads input via the readline PHP extension if that's available, or fgets() if readline is not installed.
	 *
	 * @param string $message to echo out before waiting for user input
	 * @param string $default the default string to be returned when user does not write anything.
	 * Defaults to null, means that default string is disabled.
	 * @return mixed line read as a string, or false if input has been closed
	 */
	public function prompt($message, $default = null)
	{
		if($default !== null) {
			$message .= " [$default] ";
		}
		else {
			$message .= ' ';
		}

		if(extension_loaded('readline')) {
			$input = readline($message);
			if($input !== false) {
				readline_add_history($input);
			}
		}
		else {
			echo $message;
			$input = fgets(STDIN);
		}

		if($input === false) {
			return false;
		}
		else {
			$input = trim($input);
			return ($input === '' && $default !== null) ? $default : $input;
		}
	}

	/**
	 * Asks user to confirm by typing y or n.
	 *
	 * @param string $message to echo out before waiting for user input
	 * @param boolean $default this value is returned if no selection is made.
	 * @return boolean whether user confirmed
	 */
	public function confirm($message, $default = false)
	{
		echo $message . ' (yes|no) [' . ($default ? 'yes' : 'no') . ']:';

		$input = trim(fgets(STDIN));
		return empty($input) ? $default : !strncasecmp($input, 'y', 1);
	}
}