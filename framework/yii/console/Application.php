<?php
/**
 * Console Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\InvalidRouteException;

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
 * yii <route> [--param1=value1 --param2 ...]
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
 * yii help
 * ~~~
 *
 * @property Response $response The response component. This property is read-only.
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
	 * @var Controller the currently active controller instance
	 */
	public $controller;

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
	 * Handles the specified request.
	 * @param Request $request the request to be handled
	 * @return Response the resulting response
	 */
	public function handleRequest($request)
	{
		list ($route, $params) = $request->resolve();
		$this->requestedRoute = $route;
		$result = $this->runAction($route, $params);
		if ($result instanceof Response) {
			return $result;
		} else {
			$response = $this->getResponse();
			$response->exitStatus = (int)$result;
			return $response;
		}
	}

	/**
	 * Returns the response component.
	 * @return Response the response component
	 */
	public function getResponse()
	{
		return $this->getComponent('response');
	}

	/**
	 * Runs a controller action specified by a route.
	 * This method parses the specified route and creates the corresponding child module(s), controller and action
	 * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
	 * If the route is empty, the method will use [[defaultRoute]].
	 * @param string $route the route that specifies the action.
	 * @param array $params the parameters to be passed to the action
	 * @return integer the status code returned by the action execution. 0 means normal, and other values mean abnormal.
	 * @throws Exception if the route is invalid
	 */
	public function runAction($route, $params = [])
	{
		try {
			return parent::runAction($route, $params);
		} catch (InvalidRouteException $e) {
			throw new Exception(Yii::t('yii', 'Unknown command "{command}".', ['command' => $route]), 0, $e);
		}
	}

	/**
	 * Returns the configuration of the built-in commands.
	 * @return array the configuration of the built-in commands.
	 */
	public function coreCommands()
	{
		return [
			'message' => 'yii\console\controllers\MessageController',
			'help' => 'yii\console\controllers\HelpController',
			'migrate' => 'yii\console\controllers\MigrateController',
			'cache' => 'yii\console\controllers\CacheController',
			'asset' => 'yii\console\controllers\AssetController',
			'fixture' => 'yii\console\controllers\FixtureController',
		];
	}

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	public function registerCoreComponents()
	{
		parent::registerCoreComponents();
		$this->setComponents([
			'request' => ['class' => 'yii\console\Request'],
			'response' => ['class' => 'yii\console\Response'],
		]);
	}
}
