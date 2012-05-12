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
 * - Each user command is implemented as a class extending [[\yii\console\Controller]];
 * - User specifies which command to run on the command line;
 * - The command processes the user request with the specified parameters.
 *
 * The command classes reside in the directory specified by [[controllerPath]].
 * Their naming should follow the same naming as controllers. For example, the `help` command
 * is implemented using the `HelpController` class.
 *
 * To run the console application, enter the following on the command line:
 *
 * ~~~
 * yiic <route> [...options...]
 * ~~~
 *
 * where `<route>` refers to a controller route in the form of `ModuleID/ControllerID/ActionID`
 * (e.g. `sitemap/create`), and `options` refers to a set of named parameters that will be used
 * to initialize the command controller instance and the corresponding action (e.g. `--since=0`
 * specifies a `since` parameter whose value is 0).
 *
 * A `help` command is provided by default, which may list available commands and show their usage.
 * To use this command, simply type:
 *
 * ~~~
 * yiic help
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
		if (!isset($this->controllers['help'])) {
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
