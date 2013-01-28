<?php
/**
 * Console Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
 * Their naming should follow the same naming convention as controllers. For example, the `help` command
 * is implemented using the `HelpController` class.
 *
 * To run the console application, enter the following on the command line:
 *
 * ~~~
 * yiic <route> [--param1=value1 --param2 ...]
 * ~~~
 *
 * where `<route>` refers to a controller route in the form of `ModuleID/ControllerID/ActionID`
 * (e.g. `sitemap/create`), and `param1`, `param2` refers to a set of named parameters that
 * will be used to initialize the controller action (e.g. `--since=0` specifies a `since` parameter
 * whose value is 0 and a corresponding `$since` parameter is passed to the action method).
 *
 * A `help` command is provided by default, which lists available commands and shows their usage.
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

	/**
	 * Initialize the application.
	 */
	public function init()
	{
		parent::init();
		if ($this->enableCoreCommands) {
			foreach ($this->coreCommands() as $id => $command) {
				if (!isset($this->controllerMap[$id])) {
					$this->controllerMap[$id] = $command;
				}
			}
		}
		// ensure we have the 'help' command so that we can list the available commands
		if (!isset($this->controllerMap['help'])) {
			$this->controllerMap['help'] = 'yii\console\controllers\HelpController';
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
		/** @var $request Request */
		$request = $this->getRequest();
		if ($request->getIsConsoleRequest()) {
			return $this->runAction($request->route, $request->params);
		} else {
			die('This script must be run from the command line.');
		}
	}

	/**
	 * Returns the configuration of the built-in commands.
	 * @return array the configuration of the built-in commands.
	 */
	public function coreCommands()
	{
		return array(
			'message' => 'yii\console\controllers\MessageController',
			'help' => 'yii\console\controllers\HelpController',
			'migrate' => 'yii\console\controllers\MigrateController',
			'shell' => 'yii\console\controllers\ShellController',
			'create' => 'yii\console\controllers\CreateController',
		);
	}

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	public function registerCoreComponents()
	{
		parent::registerCoreComponents();
		$this->setComponents(array(
			'request' => array(
				'class' => 'yii\console\Request',
			),
			'response' => array(
				'class' => 'yii\console\Response',
			),
		));
	}
}
