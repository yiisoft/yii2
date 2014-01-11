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
	 * @var boolean whether to run the command interactively.
	 */
	public $interactive = true;

	/**
	 * @var boolean whether to enable ANSI color in the output.
	 * If not set, ANSI color will only be enabled for terminals that support it.
	 */
	public $color;

	/**
	 * Returns a value indicating whether ANSI color is enabled.
	 *
	 * ANSI color is enabled only if [[color]] is set true or is not set
	 * and the terminal supports ANSI color.
	 *
	 * @param resource $stream the stream to check.
	 * @return boolean Whether to enable ANSI style in output.
	 */
	public function isColorEnabled($stream = STDOUT)
	{
		return $this->color ===  null ? Console::streamSupportsAnsiColors($stream) : $this->color;
	}

	/**
	 * Runs an action with the specified action ID and parameters.
	 * If the action ID is empty, the method will use [[defaultAction]].
	 * @param string $id the ID of the action to be executed.
	 * @param array $params the parameters (name-value pairs) to be passed to the action.
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
	 * @see createAction
	 */
	public function runAction($id, $params = [])
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
		$args = [];
		if (!empty($params)) {
			$options = $this->globalOptions();
			foreach ($params as $name => $value) {
				if (in_array($name, $options, true)) {
					$this->$name = $value;
				} elseif (is_int($name)) {
					$args[] = $value;
				} else {
					throw new Exception(Yii::t('yii', 'Unknown option: --{name}', ['name' => $name]));
				}
			}
		}

		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($this, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$missing = [];
		foreach ($method->getParameters() as $i => $param) {
			if ($param->isArray() && isset($args[$i])) {
				$args[$i] = preg_split('/\s*,\s*/', $args[$i]);
			}
			if (!isset($args[$i])) {
				if ($param->isDefaultValueAvailable()) {
					$args[$i] = $param->getDefaultValue();
				} else {
					$missing[] = $param->getName();
				}
			}
		}

		if (!empty($missing)) {
			throw new Exception(Yii::t('yii', 'Missing required arguments: {params}', ['params' => implode(', ', $missing)]));
		}

		return $args;
	}

	/**
	 * Formats a string with ANSI codes
	 *
	 * You may pass additional parameters using the constants defined in [[yii\helpers\Console]].
	 *
	 * Example:
	 *
	 * ~~~
	 * echo $this->ansiFormat('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
	 * ~~~
	 *
	 * @param string $string the string to be formatted
	 * @return string
	 */
	public function ansiFormat($string)
	{
		if ($this->isColorEnabled()) {
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
	 * passing additional parameters using the constants defined in [[yii\helpers\Console]].
	 *
	 * Example:
	 *
	 * ~~~
	 * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
	 * ~~~
	 *
	 * @param string $string the string to print
	 * @return int|boolean Number of bytes printed or false on error
	 */
	public function stdout($string)
	{
		if ($this->isColorEnabled()) {
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
	 * passing additional parameters using the constants defined in [[yii\helpers\Console]].
	 *
	 * Example:
	 *
	 * ~~~
	 * $this->stderr('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
	 * ~~~
	 *
	 * @param string $string the string to print
	 * @return int|boolean Number of bytes printed or false on error
	 */
	public function stderr($string)
	{
		if ($this->isColorEnabled(STDERR)) {
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
	 *
	 *  - required: whether it is required or not
	 *  - default: default value if no input is inserted by the user
	 *  - pattern: regular expression pattern to validate user input
	 *  - validator: a callable function to validate input. The function must accept two parameters:
	 *      - $input: the user input to validate
	 *      - $error: the error value passed by reference if validation failed.
	 * @return string the user input
	 */
	public function prompt($text, $options = [])
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
	 * @return boolean whether user confirmed.
	 * Will return true if [[interactive]] is false.
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
	public function select($prompt, $options = [])
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
		return ['color', 'interactive'];
	}
}
