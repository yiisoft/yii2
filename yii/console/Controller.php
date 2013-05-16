<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\Action;
use yii\base\InlineAction;
use yii\base\InvalidRouteException;
use yii\helpers\Console;

/**
 * Controller is the base class of console command classes.
 *
 * A controller consists of one or several actions known as sub-commands.
 * Users call a console command by specifying the corresponding route which identifies a controller action.
 * The `yii` program is used when calling a console command, like the following:
 *
 * ~~~
 * yii <route> [--param1=value1 --param2 ...]
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
	/**
	 * @var boolean whether the call of [[confirm()]] requires a user input.
	 * If false, [[confirm()]] will always return true no matter what user enters or not.
	 */
	public $interactive = true;

	/**
	 * @var bool whether to enable ANSI style in output.
	 * Setting this will affect [[ansiFormat()]], [[stdout()]] and [[stderr()]].
	 * If not set it will be auto detected using [[yii\helpers\Console::streamSupportsAnsiColors()]] with STDOUT
	 * for [[ansiFormat()]] and [[stdout()]] and STDERR for [[stderr()]].
	 */
	public $colors;

	/**
	 * Runs an action with the specified action ID and parameters.
	 * If the action ID is empty, the method will use [[defaultAction]].
	 * @param string $id the ID of the action to be executed.
	 * @param array $params the parameters (name-value pairs) to be passed to the action.
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
	 * @see createAction
	 */
	public function runAction($id, $params = array())
	{
		if (!empty($params)) {
			$options = $this->globalOptions();
			foreach ($params as $name => $value) {
				if (in_array($name, $options, true)) {
					$this->$name = $value;
					unset($params[$name]);
				}
			}
		}
		return parent::runAction($id, $params);
	}

	/**
	 * Binds the parameters to the action.
	 * This method is invoked by [[Action]] when it begins to run with the given parameters.
	 * This method will first bind the parameters with the [[globalOptions()|global options]]
	 * available to the action. It then validates the given arguments.
	 * @param Action $action the action to be bound with parameters
	 * @param array $params the parameters to be bound to the action
	 * @return array the valid parameters that the action can run with.
	 * @throws Exception if there are unknown options or missing arguments
	 */
	public function bindActionParams($action, $params)
	{
		if (!empty($params)) {
			$options = $this->globalOptions();
			foreach ($params as $name => $value) {
				if (in_array($name, $options, true)) {
					$this->$name = $value;
					unset($params[$name]);
				}
			}
		}

		$args = isset($params[Request::ANONYMOUS_PARAMS]) ? $params[Request::ANONYMOUS_PARAMS] : array();
		unset($params[Request::ANONYMOUS_PARAMS]);
		if (!empty($params)) {
			throw new Exception(Yii::t('yii', 'Unknown options: {params}', array(
				'{params}' => implode(', ', array_keys($params)),
			)));
		}

		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($this, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$missing = array();
		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();
			if (!isset($args[$i])) {
				if ($param->isDefaultValueAvailable()) {
					$args[$i] = $param->getDefaultValue();
				} else {
					$missing[] = $name;
				}
			}
		}

		if (!empty($missing)) {
			throw new Exception(Yii::t('yii', 'Missing required arguments: {params}', array(
				'{params}' => implode(', ', $missing),
			)));
		}

		return $args;
	}

	/**
	 * Formats a string with ANSI codes
	 *
	 * You may pass additional parameters using the constants defined in [[yii\helpers\base\Console]].
	 *
	 * Example:
	 * ~~~
	 * $this->ansiFormat('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
	 * ~~~
	 *
	 * @param string $string the string to be formatted
	 * @return string
	 */
	public function ansiFormat($string)
	{
		if ($this->ansi === true || $this->ansi === null && Console::streamSupportsAnsiColors(STDOUT)) {
			$args = func_get_args();
			array_shift($args);
			$string = Console::ansiFormat($string, $args);
		}
		return $string;
	}

	/**
	 * Prints a string to STDOUT
	 *
	 * You may optionally format the string with ANSI codes by
	 * passing additional parameters using the constants defined in [[yii\helpers\base\Console]].
	 *
	 * Example:
	 * ~~~
	 * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
	 * ~~~
	 *
	 * @param string $string the string to print
	 * @return int|boolean Number of bytes printed or false on error
	 */
	public function stdout($string)
	{
		if ($this->ansi === true || $this->ansi === null && Console::streamSupportsAnsiColors(STDOUT)) {
			$args = func_get_args();
			array_shift($args);
			$string = Console::ansiFormat($string, $args);
		}
		return Console::stdout($string);
	}

	/**
	 * Prints a string to STDERR
	 *
	 * You may optionally format the string with ANSI codes by
	 * passing additional parameters using the constants defined in [[yii\helpers\base\Console]].
	 *
	 * Example:
	 * ~~~
	 * $this->stderr('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
	 * ~~~
	 *
	 * @param string $string the string to print
	 * @return int|boolean Number of bytes printed or false on error
	 */
	public function stderr($string)
	{
		if ($this->ansi === true || $this->ansi === null && Console::streamSupportsAnsiColors(STDERR)) {
			$args = func_get_args();
			array_shift($args);
			$string = Console::ansiFormat($string, $args);
		}
		return fwrite(STDERR, $string);
	}

	/**
	 * Prompts the user for input and validates it
	 *
	 * @param string $text prompt string
	 * @param array $options the options to validate the input:
	 *  - required: whether it is required or not
	 *  - default: default value if no input is inserted by the user
	 *  - pattern: regular expression pattern to validate user input
	 *  - validator: a callable function to validate input. The function must accept two parameters:
	 *      - $input: the user input to validate
	 *      - $error: the error value passed by reference if validation failed.
	 * @return string the user input
	 */
	public function prompt($text, $options = array())
	{
		if ($this->interactive) {
			return Console::prompt($text, $options);
		} else {
			return isset($options['default']) ? $options['default'] : '';
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
		if ($this->interactive) {
			return Console::confirm($message, $default);
		} else {
			return true;
		}
	}

	/**
	 * Gives the user an option to choose from. Giving '?' as an input will show
	 * a list of options to choose from and their explanations.
	 *
	 * @param string $prompt the prompt message
	 * @param array  $options Key-value array of options to choose from
	 *
	 * @return string An option character the user chose
	 */
	public function select($prompt, $options = array())
	{
		return Console::select($prompt, $options);
	}

	/**
	 * Returns the names of the global options for this command.
	 * A global option requires the existence of a public member variable whose
	 * name is the option name.
	 * Child classes may override this method to specify possible global options.
	 *
	 * Note that the values setting via global options are not available
	 * until [[beforeAction()]] is being called.
	 *
	 * @return array the names of the global options for this command.
	 */
	public function globalOptions()
	{
		return array();
	}
}
