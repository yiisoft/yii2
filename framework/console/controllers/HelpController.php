<?php
/**
 * HelpController class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\base\Application;
use yii\console\Exception;
use yii\base\InlineAction;
use yii\console\Controller;
use yii\console\Request;
use yii\util\StringHelper;

/**
 * This command provides help information about console commands.
 *
 * This command displays the available command list in
 * the application or the detailed instructions about using
 * a specific command.
 *
 * This command can be used as follows on command line:
 *
 * ~~~
 * yiic help [command name]
 * ~~~
 *
 * In the above, if the command name is not provided, all
 * available commands will be displayed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelpController extends Controller
{
	/**
	 * Displays available commands or the detailed information
	 * about a particular command. For example,
	 *
	 * ~~~
	 * yiic help          # list available commands
	 * yiic help message  # display help info about "message"
	 * ~~~
	 *
	 * @param array $args The name of the command to show help about.
	 * If not provided, all available commands will be displayed.
	 * @return integer the exit status
	 * @throws Exception if the command for help is unknown
	 */
	public function actionIndex($args = array())
	{
		if (isset($args[0])) {
			$result = Yii::$application->createController($args[0]);
			if ($result === false) {
				throw new Exception(Yii::t('yii', 'No help for unknown command "{command}".', array(
					'{command}' => $args[0],
				)));
			}

			list($controller, $actionID) = $result;

			if ($actionID === '') {
				$this->getControllerHelp($controller);
			} else {
				$this->getActionHelp($controller, $actionID);
			}
		} else {
			$this->getHelp();
		}
	}

	/**
	 * Returns all available command names.
	 * @return array all available command names
	 */
	public function getCommands()
	{
		$commands = $this->getModuleCommands(Yii::$application);
		sort($commands);
		return array_unique($commands);
	}

	/**
	 * Returns all available actions of the specified controller.
	 * @param Controller $controller the controller instance
	 * @return array all available action IDs.
	 */
	public function getActions($controller)
	{
		$actions = array_keys($controller->actions());
		$class = new \ReflectionClass($controller);
		foreach ($class->getMethods() as $method) {
			$name = $method->getName();
			if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
				$actions[] = StringHelper::camel2id(substr($name, 6));
			}
		}
		sort($actions);
		return array_unique($actions);
	}

	/**
	 * Returns available commands of a specified module.
	 * @param \yii\base\Module $module the module instance
	 * @return array the available command names
	 */
	protected function getModuleCommands($module)
	{
		$prefix = $module instanceof Application ? '' : $module->getUniqueID() . '/';

		$commands = array();
		foreach (array_keys($module->controllerMap) as $id) {
			$commands[] = $prefix . $id;
		}

		foreach ($module->getModules() as $id => $child) {
			if (($child = $module->getModule($id)) === null) {
				continue;
			}
			foreach ($this->getModuleCommands($child) as $command) {
				$commands[] = $prefix . $id . '/' . $command;
			}
		}

		$files = scandir($module->getControllerPath());
		foreach ($files as $file) {
			if(strcmp(substr($file,-14),'Controller.php') === 0 && is_file($file)) {
				$commands[] = $prefix . lcfirst(substr(basename($file), 0, -14));
			}
		}

		return $commands;
	}

	/**
	 * Displays all available commands.
	 */
	protected function getHelp()
	{
		$commands = $this->getCommands();
		if ($commands !== array()) {
			echo "Usage: yiic <command-name> [...options...] [...arguments...]\n\n";
			echo "The following commands are available:\n\n";
			foreach ($commands as $command) {
				echo " * $command\n";
			}
			echo "\nTo see the help of each command, enter:\n";
			echo "\n    yiic help <command-name>\n";
		} else {
			echo "\nNo commands are found.\n";
		}
	}

	/**
	 * Displays the overall information of the command.
	 * @param Controller $controller the controller instance
	 */
	protected function getControllerHelp($controller)
	{
		$class = new \ReflectionClass($controller);
		$comment = strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($class->getDocComment(), '/'))), "\r", '');
		if (preg_match('/^\s*@\w+/m', $comment, $matches, PREG_OFFSET_CAPTURE)) {
			$comment = trim(substr($comment, 0, $matches[0][1]));
		}

		if ($comment !== '') {
			echo "\nDESCRIPTION\n";
			echo "\n" . $comment . "\n\n";
		}

		$actions = $this->getActions($controller);
		if ($actions !== array()) {
			echo "\nSUB-COMMANDS\n\n";
			$prefix = $controller->getUniqueId();
			foreach ($actions as $action) {
				if ($controller->defaultAction === $action) {
					echo " * $prefix/$action (or $prefix)\n";
				} else {
					echo " * $prefix/$action\n";
				}
			}
			echo "\nTo see the help of each sub-command, enter:\n";
			echo "\n    yiic help <sub-command>\n\n";
		}
	}

	/**
	 * Displays the detailed information of a command action.
	 * @param Controller $controller the controller instance
	 * @param string $actionID action ID
	 * @throws Exception if the action does not exist
	 */
	protected function getActionHelp($controller, $actionID)
	{
		$action = $controller->createAction($actionID);
		if ($action === null) {
			throw new Exception(Yii::t('yii', 'No help for unknown sub-command "{command}".', array(
				'{command}' => $action->getUniqueId(),
			)));
		}
		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($controller, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$tags = $this->parseComment($method->getDocComment());
		$options = $this->getOptions($method, isset($tags['param']) ? $tags['param'] : array());
		$globalOptions = $this->getGlobalOptions($controller);
		$options = array_merge($options, $globalOptions);

		echo "\nUSAGE\n\n";
		if ($action->id === $controller->defaultAction) {
			echo 'yiic ' . $controller->getUniqueId();
		} else {
			echo "yiic " . $action->getUniqueId();
		}
		if (isset($options[Request::ANONYMOUS_PARAMS])) {
			if (count($options) > 1) {
				echo ' [...options...]';
			}
			echo " [...arguments...]";
		} elseif (count($options)) {
			echo " [...options...]";
		}
		echo "\n\n";

		if ($tags['description'] !== '') {
			echo "\nDESCRIPTION";
			echo "\n\n" . $tags['description'] . "\n\n";
		}

		if (isset($options[Request::ANONYMOUS_PARAMS])) {
			echo "\nARGUMENTS\n\n";
			echo $options[Request::ANONYMOUS_PARAMS] . "\n\n";
			unset($options[Request::ANONYMOUS_PARAMS]);
		}

		if ($options !== array()) {
			echo "\nOPTIONS\n\n";
			echo implode("\n\n", $options) . "\n\n";
		}
	}

	function parseComment($comment)
	{
		$tags = array();
		$comment = "@description \n" . strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($comment, '/'))), "\r", '');
		$parts = preg_split('/^\s*@/m', $comment, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($parts as $part) {
			if (preg_match('/^(\w+)(.*)/ms', trim($part), $matches)) {
				$name = $matches[1];
				if (!isset($tags[$name])) {
					$tags[$name] = trim($matches[2]);
				} elseif (is_array($tags[$name])) {
					$tags[$name][] = trim($matches[2]);
				} else {
					$tags[$name] = array($tags[$name], trim($matches[2]));
				}
			}
		}
		return $tags;
	}

	/**
	 * @param \ReflectionMethod $method
	 * @param string $meta
	 * @return array
	 */
	protected function getOptions($method, $tags)
	{
		if (is_string($tags)) {
			$tags = array($tags);
		}
		$params = $method->getParameters();
		$optional = $required = array();
		foreach ($params as $i => $param) {
			$name = $param->getName();
			$tag = isset($tags[$i]) ? $tags[$i] : '';
			if (preg_match('/^([^\s]+)\s+(\$\w+\s+)?(.*)/s', $tag, $matches)) {
				$type = $matches[1];
				$comment = $matches[3];
			} else {
				$type = null;
				$comment = $tag;
			}
			if ($param->isDefaultValueAvailable()) {
				$optional[$name] = $this->formatOptionHelp($name, false, $type, $param->getDefaultValue(), $comment);
			} else {
				$required[$name] = $this->formatOptionHelp($name, true, $type, null, $comment);
			}
		}

		ksort($required);
		ksort($optional);

		return array_merge($required, $optional);
	}

	protected function formatOptionHelp($name, $required, $type, $defaultValue, $comment)
	{
		$doc = '';
		$comment = trim($comment);

		if ($name === Request::ANONYMOUS_PARAMS) {
			return $comment;
		}

		if ($defaultValue !== null && !is_array($defaultValue)) {
			if ($type === null) {
				$type = gettype($defaultValue);
			}
			$doc = "$type (defaults to " . var_export($defaultValue, true) . ")";
		} elseif (trim($type) !== '') {
			$doc = $type;
		}

		if ($doc === '') {
			$doc = $comment;
		} elseif ($comment !== '') {
			$doc .= "\n" . preg_replace("/^/m", "  ", $comment);
		}

		$name = $required ? "--$name (required)" : "--$name";
		return $doc === '' ? $name : "$name: $doc";
	}

	/**
	 * @param Controller $controller
	 * @return array
	 */
	protected function getGlobalOptions($controller)
	{
		$optionNames = $controller->globalOptions();
		if (empty($optionNames)) {
			return array();
		}

		$class = new \ReflectionClass($controller);
		$options = array();
		foreach ($class->getProperties() as $property) {
			$name = $property->getName();
			if (!in_array($name, $optionNames, true)) {
				continue;
			}
			$defaultValue = $property->getValue($controller);
			$tags = $this->parseComment($property->getDocComment());
			if (isset($tags['var']) || isset($tags['property'])) {
				$doc = isset($tags['var']) ? $tags['var'] : $tags['property'];
				if (is_array($doc)) {
					$doc = reset($doc);
				}
				if (preg_match('/^([^\s]+)(.*)/s', $doc, $matches)) {
					$type = $matches[1];
					$comment = $matches[2];
				} else {
					$type = null;
					$comment = $doc;
				}
				$options[$name] = $this->formatOptionHelp($name, false, $type, $defaultValue, $comment);
			} else {
				$options[$name] = $this->formatOptionHelp($name, false, null, $defaultValue, '');
			}
		}
		ksort($options);
		return $options;
	}
}