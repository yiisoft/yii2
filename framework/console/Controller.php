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
 * Command represents an executable console command.
 *
 * It works like {@link \yii\web\Controller} by parsing command line options and dispatching
 * the request to a specific action with appropriate option values.
 *
 * Users call a console command via the following command format:
 * <pre>
 * yiic CommandName ActionName --Option1=Value1 --Option2=Value2 ...
 * </pre>
 *
 * Child classes mainly needs to implement various action methods whose name must be
 * prefixed with "action". The parameters to an action method are considered as options
 * for that specific action. The action specified as {@link defaultAction} will be invoked
 * when a user does not specify the action name in his command.
 *
 * Options are bound to action parameters via parameter names. For example, the following
 * action method will allow us to run a command with <code>yiic sitemap --type=News</code>:
 * <pre>
 * class SitemapCommand {
 *     public function actionIndex($type) {
 *         ....
 *     }
 * }
 * </pre>
 *
 * @property string $name The command name.
 * @property CommandRunner $commandRunner The command runner instance.
 * @property string $help The command description. Defaults to 'Usage: php entry-script.php command-name'.
 * @property array $optionHelp The command option help information. Each array element describes
 * the help information for a single action.
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
	 * @throws Exception whenever this method is invoked
	 */
	public function invalidActionParams($action, $exception)
	{
	}

	/**
	 * This method is invoked when extra parameters are provided to an action when it is executed.
	 * The default implementation does nothing.
	 * @param Action $action the action being executed
	 * @param array $expected the expected action parameters (name => value)
	 * @param array $actual the actual action parameters (name => value)
	 */
	public function extraActionParams($action, $expected, $actual)
	{
	}

	/**
	 * Provides the command description.
	 * This method may be overridden to return the actual command description.
	 * @return string the command description. Defaults to 'Usage: php entry-script.php command-name'.
	 */
	public function getHelp()
	{
		$help = 'Usage: ' . $this->getCommandRunner()->getScriptName() . ' ' . $this->getName();
		$options = $this->getOptionHelp();
		if (empty($options))
			return $help;
		if (count($options) === 1)
			return $help . ' ' . $options[0];
		$help .= " <action>\nActions:\n";
		foreach ($options as $option)
			$help .= '    ' . $option . "\n";
		return $help;
	}

	/**
	 * Provides the command option help information.
	 * The default implementation will return all available actions together with their
	 * corresponding option information.
	 * @return array the command option help information. Each array element describes
	 * the help information for a single action.
	 */
	public function getOptionHelp()
	{
		$options = array();
		$class = new \ReflectionClass(get_class($this));
		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			$name = $method->getName();
			if (!strncasecmp($name, 'action', 6) && strlen($name) > 6) {
				$name = substr($name, 6);
				$name[0] = strtolower($name[0]);
				$help = $name;

				foreach ($method->getParameters() as $param) {
					$optional = $param->isDefaultValueAvailable();
					$defaultValue = $optional ? $param->getDefaultValue() : null;
					$name = $param->getName();
					if ($optional)
						$help .= " [--$name=$defaultValue]";
					else
						$help .= " --$name=value";
				}
				$options[] = $help;
			}
		}
		return $options;
	}

	/**
	 * Displays a usage error.
	 * This method will then terminate the execution of the current application.
	 * @param string $message the error message
	 */
	public function usageError($message)
	{
		echo "Error: $message\n\n" . $this->getHelp() . "\n";
		exit(1);
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
		if ($default !== null) {
			$message .= " [$default] ";
		}
		else {
			$message .= ' ';
		}

		if (extension_loaded('readline')) {
			$input = readline($message);
			if ($input) {
				readline_add_history($input);
			}
		} else {
			echo $message;
			$input = fgets(STDIN);
		}
		if ($input === false) {
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
	 * @return bool if user confirmed
	 */
	public function confirm($message)
	{
		echo $message . ' [yes|no] ';
		return !strncasecmp(trim(fgets(STDIN)), 'y', 1);
	}
}