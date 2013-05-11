<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\base\Application;
use yii\base\InlineAction;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\Request;
use yii\helpers\StringHelper;

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
	 * @param string $command The name of the command to show help about.
	 * If not provided, all available commands will be displayed.
	 * @return integer the exit status
	 * @throws Exception if the command for help is unknown
	 */
	public function actionIndex($command = null)
	{
		if ($command !== null) {
			$result = Yii::$app->createController($command);
			if ($result === false) {
				throw new Exception(Yii::t('yii|No help for unknown command "{command}".', array(
					'{command}' => $command,
				)));
			}

			list($controller, $actionID) = $result;

			$actions = $this->getActions($controller);
			if ($actionID !== '' || count($actions) === 1 && $actions[0] === $controller->defaultAction) {
				$this->getActionHelp($controller, $actionID);
			} else {
				$this->getControllerHelp($controller);
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
		$commands = $this->getModuleCommands(Yii::$app);
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
			if (strcmp(substr($file, -14), 'Controller.php') === 0) {
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
		if (!empty($commands)) {
			echo "The following commands are available:\n\n";
			foreach ($commands as $command) {
				echo "* $command\n";
			}
			echo "\nTo see the help of each command, enter:\n";
			echo "\n  yiic help <command-name>\n\n";
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
		if (!empty($actions)) {
			echo "\nSUB-COMMANDS\n\n";
			$prefix = $controller->getUniqueId();
			foreach ($actions as $action) {
				if ($action === $controller->defaultAction) {
					echo "* $prefix/$action (default)";
				} else {
					echo "* $prefix/$action";
				}
				$summary = $this->getActionSummary($controller, $action);
				if ($summary !== '') {
					echo ': ' . $summary;
				}
				echo "\n";
			}
			echo "\n\nTo see the detailed information about individual sub-commands, enter:\n";
			echo "\n  yiic help <sub-command>\n\n";
		}
	}

	/**
	 * Returns the short summary of the action.
	 * @param Controller $controller the controller instance
	 * @param string $actionID action ID
	 * @return string the summary about the action
	 */
	protected function getActionSummary($controller, $actionID)
	{
		$action = $controller->createAction($actionID);
		if ($action === null) {
			return '';
		}
		if ($action instanceof InlineAction) {
			$reflection = new \ReflectionMethod($controller, $action->actionMethod);
		} else {
			$reflection = new \ReflectionClass($action);
		}
		$tags = $this->parseComment($reflection->getDocComment());
		if ($tags['description'] !== '') {
			$limit = 73 - strlen($action->getUniqueId());
			if ($actionID === $controller->defaultAction) {
				$limit -= 10;
			}
			if ($limit < 0) {
				$limit = 50;
			}
			$description = $tags['description'];
			if (($pos = strpos($tags['description'], "\n")) !== false) {
				$description = substr($description, 0, $pos);
			}
			$text = substr($description, 0, $limit);
			return strlen($description) > $limit ? $text . '...' : $text;
		} else {
			return '';
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
			throw new Exception(Yii::t('yii|No help for unknown sub-command "{command}".', array(
				'{command}' => rtrim($controller->getUniqueId() . '/' . $actionID, '/'),
			)));
		}
		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($controller, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$tags = $this->parseComment($method->getDocComment());
		$options = $this->getOptionHelps($controller);

		if ($tags['description'] !== '') {
			echo "\nDESCRIPTION";
			echo "\n\n" . $tags['description'] . "\n\n";
		}

		echo "\nUSAGE\n\n";
		if ($action->id === $controller->defaultAction) {
			echo 'yiic ' . $controller->getUniqueId();
		} else {
			echo "yiic " . $action->getUniqueId();
		}
		list ($required, $optional) = $this->getArgHelps($method, isset($tags['param']) ? $tags['param'] : array());
		if (!empty($required)) {
			echo ' <' . implode('> <', array_keys($required)) . '>';
		}
		if (!empty($optional)) {
			echo ' [' . implode('] [', array_keys($optional)) . ']';
		}
		if (!empty($options)) {
			echo ' [...options...]';
		}
		echo "\n\n";

		if (!empty($required) || !empty($optional)) {
			echo implode("\n\n", array_merge($required, $optional)) . "\n\n";
		}

		$options = $this->getOptionHelps($controller);
		if (!empty($options)) {
			echo "\nOPTIONS\n\n";
			echo implode("\n\n", $options) . "\n\n";
		}
	}

	/**
	 * Returns the help information about arguments.
	 * @param \ReflectionMethod $method
	 * @param string $tags the parsed comment block related with arguments
	 * @return array the required and optional argument help information
	 */
	protected function getArgHelps($method, $tags)
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
				$optional[$name] = $this->formatOptionHelp('* ' . $name, false, $type, $param->getDefaultValue(), $comment);
			} else {
				$required[$name] = $this->formatOptionHelp('* ' . $name, true, $type, null, $comment);
			}
		}

		return array($required, $optional);
	}

	/**
	 * Returns the help information about the options available for a console controller.
	 * @param Controller $controller the console controller
	 * @return array the help information about the options
	 */
	protected function getOptionHelps($controller)
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
				$options[$name] = $this->formatOptionHelp('--' . $name, false, $type, $defaultValue, $comment);
			} else {
				$options[$name] = $this->formatOptionHelp('--' . $name, false, null, $defaultValue, '');
			}
		}
		ksort($options);
		return $options;
	}

	/**
	 * Parses the comment block into tags.
	 * @param string $comment the comment block
	 * @return array the parsed tags
	 */
	protected function parseComment($comment)
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
	 * Generates a well-formed string for an argument or option.
	 * @param string $name the name of the argument or option
	 * @param boolean $required whether the argument is required
	 * @param string $type the type of the option or argument
	 * @param mixed $defaultValue the default value of the option or argument
	 * @param string $comment comment about the option or argument
	 * @return string the formatted string for the argument or option
	 */
	protected function formatOptionHelp($name, $required, $type, $defaultValue, $comment)
	{
		$doc = '';
		$comment = trim($comment);

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

		$name = $required ? "$name (required)" : $name;
		return $doc === '' ? $name : "$name: $doc";
	}
}
