<?php
/**
 * Console Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\base\Exception;
use yii\util\ReflectionHelper;

/**
 * Application represents a console application.
 *
 * Application extends from [[yii\base\Application]] by providing functionalities that are
 * specific to console requests. In particular, it deals with console requests
 * through a command-based approach:
 *
 * - A console application consists of one or several possible user commands;
 * - Each user command is implemented as a class extending [[Command]];
 * - User specifies which command to run on the command line;
 * - The command processes the user request with the specified parameters.
 *
 * The command classes reside in the directory specified by [[commandPath]].
 * The name of the class should be of the form `<command-name>Command` (e.g. `HelpCommand`).
 *
 * To run the console application, enter the following on the command line:
 *
 * ~~~
 * yiic <command-name> [param 1] [param 2] ...
 * ~~~
 *
 * You may use the following line to see help instructions about a command:
 *
 * ~~~
 * yiic help <command-name>
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \yii\base\Application
{
	/**
	 * @var string the default route of this application. Defaults to 'help',
	 * meaning the `help` command.
	 */
	public $defaultRoute = 'help';
	/**
	 * @var boolean whether to enable the commands provided by the core framework.
	 * Defaults to true.
	 */
	public $enableCoreCommands = true;

	public function init()
	{
		parent::init();
		if ($this->enableCoreCommands) {
			foreach ($this->coreCommands() as $id => $command) {
				if (!isset($this->controllers[$id])) {
					$this->controllers[$id] = $command;
				}
			}
		}
		// ensure we have the 'help' command so that we can list the available commands
		if ($this->defaultRoute === 'help' && !isset($this->controllers['help'])) {
			$this->controllers['help'] = 'yii\console\commands\HelpController';
		}
	}

	/**
	 * Processes the request.
	 * The request is represented in terms of a controller route and action parameters.
	 * @return integer the exit status of the controller action (0 means normal, non-zero values mean abnormal)
	 * @throws Exception if the route cannot be resolved into a controller
	 */
	public function processRequest()
	{
		if (!isset($_SERVER['argv'])) {
			die('This script must be run from the command line.');
		}
		list($route, $params) = $this->resolveRequest($_SERVER['argv']);
		return $this->runController($route, $params);
	}

	/**
	 * Runs a controller with the given route and parameters.
	 * @param string $route the route (e.g. `post/create`)
	 * @param array $params the parameters to be passed to the controller action
	 * @return integer the exit status (0 means normal, non-zero values mean abnormal)
	 * @throws Exception if the route cannot be resolved into a controller
	 */
	public function runController($route, $params = array())
	{
		$result = $this->createController($route);
		if ($result === false) {
			throw new Exception(\Yii::t('yii', 'Unable to resolve the request.'));
		}
		list($controller, $action) = $result;
		$priorController = $this->controller;
		$this->controller = $controller;
		$params = ReflectionHelper::initObjectWithParams($controller, $params);
		$status = $controller->run($action, $params);
		$this->controller = $priorController;
		return $status;
	}

	/**
	 * Resolves the request.
	 * @param array $args the arguments passed via the command line
	 * @return array the controller route and the parameters for the controller action
	 */
	protected function resolveRequest($args)
	{
		array_shift($args);  // the 1st argument is the yiic script name

		if (isset($args[0])) {
			$route = $args[0];
			array_shift($args);
		} else {
			$route = '';
		}

		$params = array();
		foreach ($args as $arg) {
			if (preg_match('/^--(\w+)(=(.*))?$/', $arg, $matches)) {
				$name = $matches[1];
				$params[$name] = isset($matches[3]) ? $matches[3] : true;
			} else {
				$params['args'][] = $arg;
			}
		}

		return array($route, $params);
	}

	/**
	 * Searches for commands under the specified directory.
	 * @param string $path the directory containing the command class files.
	 * @return array list of commands (command name=>command class file)
	 */
	public function findCommands($path)
	{
		if (($dir = @opendir($path)) === false)
			return array();
		$commands = array();
		while (($name = readdir($dir)) !== false) {
			$file = $path . DIRECTORY_SEPARATOR . $name;
			if (!strcasecmp(substr($name, -11), 'Command.php') && is_file($file))
				$commands[strtolower(substr($name, 0, -11))] = $file;
		}
		closedir($dir);
		return $commands;
	}

	/**
	 * Adds commands from the specified command path.
	 * If a command already exists, the new one will be ignored.
	 * @param string $path the alias of the directory containing the command class files.
	 */
	public function addCommands($path)
	{
		if (($commands = $this->findCommands($path)) !== array()) {
			foreach ($commands as $name => $file) {
				if (!isset($this->commands[$name]))
					$this->commands[$name] = $file;
			}
		}
	}

	/**
	 * @param string $name command name (case-insensitive)
	 * @return CConsoleCommand the command object. Null if the name is invalid.
	 */
	public function createCommand($name)
	{
		$name = strtolower($name);
		if (isset($this->commands[$name])) {
			if (is_string($this->commands[$name])) // class file path or alias
			{
				if (strpos($this->commands[$name], '/') !== false || strpos($this->commands[$name], '\\') !== false) {
					$className = substr(basename($this->commands[$name]), 0, -4);
					if (!class_exists($className, false))
						require_once($this->commands[$name]);
				}
				else // an alias
					$className = Yii::import($this->commands[$name]);
				return new $className($name, $this);
			}
			else // an array configuration
				return Yii::createComponent($this->commands[$name], $name, $this);
		}
		else if ($name === 'help')
			return new CHelpCommand('help', $this);
		else
			return null;
	}

	public function coreCommands()
	{
		return array(
			'message' => 'yii\console\commands\MessageController',
			'help' => 'yii\console\commands\HelpController',
			'migrate' => 'yii\console\commands\MigrateController',
			'shell' => 'yii\console\commands\ShellController',
			'app' => 'yii\console\commands\AppController',
		);
	}
}
