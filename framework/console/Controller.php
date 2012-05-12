<?php
/**
 * Command class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

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
	 * Executes the command.
	 * The default implementation will parse the input parameters and
	 * dispatch the command request to an appropriate action with the corresponding
	 * option values
	 * @param array $args command line parameters for this command.
	 */
	public function run($args)
	{
		list($action, $options, $args) = $this->resolveRequest($args);
		$methodName = 'action' . $action;
		if (!preg_match('/^\w+$/', $action) || !method_exists($this, $methodName))
				{
					$this->usageError("Unknown action: " . $action);
				}

		$method = new \ReflectionMethod($this, $methodName);
		$params = array();
		// named and unnamed options
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			if (isset($options[$name])) {
				if ($param->isArray())
					$params[] = is_array($options[$name]) ? $options[$name] : array($options[$name]);
				else if (!is_array($options[$name]))
					$params[] = $options[$name];
				else
					$this->usageError("Option --$name requires a scalar. Array is given.");
			} else if ($name === 'args')
				$params[] = $args;
			else if ($param->isDefaultValueAvailable())
				$params[] = $param->getDefaultValue();
			else
				$this->usageError("Missing required option --$name.");
			unset($options[$name]);
		}

		// try global options
		if (!empty($options)) {
			$class = new \ReflectionClass(get_class($this));
			foreach ($options as $name => $value) {
				if ($class->hasProperty($name)) {
					$property = $class->getProperty($name);
					if ($property->isPublic() && !$property->isStatic()) {
						$this->$name = $value;
						unset($options[$name]);
					}
				}
			}
		}

		if (!empty($options))
			$this->usageError("Unknown options: " . implode(', ', array_keys($options)));

		if ($this->beforeAction($action, $params)) {
			$method->invokeArgs($this, $params);
			$this->afterAction($action, $params);
		}
	}

	/**
	 * Parses the command line arguments and determines which action to perform.
	 * @param array $args command line arguments
	 * @return array the action name, named options (name=>value), and unnamed options
	 */
	protected function resolveRequest($args)
	{
		$options = array(); // named parameters
		$params = array(); // unnamed parameters
		foreach ($args as $arg) {
			if (preg_match('/^--(\w+)(=(.*))?$/', $arg, $matches)) // an option
			{
				$name = $matches[1];
				$value = isset($matches[3]) ? $matches[3] : true;
				if (isset($options[$name])) {
					if (!is_array($options[$name]))
						$options[$name] = array($options[$name]);
					$options[$name][] = $value;
				} else
					$options[$name] = $value;
			} else if (isset($action))
				$params[] = $arg;
			else
				$action = $arg;
		}
		if (!isset($action))
			$action = $this->defaultAction;

		return array($action, $options, $params);
	}

	/**
	 * @return string the command name.
	 */
	public function getName()
	{
		return $this->_name;
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
	 * Renders a view file.
	 * @param string $_viewFile_ view file path
	 * @param array $_data_ optional data to be extracted as local view variables
	 * @param boolean $_return_ whether to return the rendering result instead of displaying it
	 * @return mixed the rendering result if required. Null otherwise.
	 */
	public function renderFile($_viewFile_, $_data_ = null, $_return_ = false)
	{
		if (is_array($_data_))
			extract($_data_, EXTR_PREFIX_SAME, 'data');
		else
			$data = $_data_;
		if ($_return_) {
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		} else
			require($_viewFile_);
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