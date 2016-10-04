<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\InvalidRouteException;
use yii\helpers\Inflector;

// define STDIN, STDOUT and STDERR if the PHP SAPI did not define them (e.g. creating console application in web env)
// http://php.net/manual/en/features.commandline.io-streams.php
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
defined('STDERR') or define('STDERR', fopen('php://stderr', 'w'));

/**
 * Application represents a console application.
 *
 * Application extends from [[\yii\base\Application]] by providing functionalities that are
 * specific to console requests. In particular, it deals with console requests
 * through a command-based approach:
 *
 * - A console application consists of one or several possible user commands;
 * - Each user command is implemented as a class extending [[\yii\console\Controller]];
 * - User specifies which command to run on the command line;
 * - The command processes the user request with the specified parameters.
 *
 * The command classes should be under the namespace specified by [[controllerNamespace]].
 * Their naming should follow the same naming convention as controllers. For example, the `help` command
 * is implemented using the `HelpController` class.
 *
 * To run the console application, enter the following on the command line:
 *
 * ```
 * yii <route> [--param1=value1 --param2 ...]
 * ```
 *
 * where `<route>` refers to a controller route in the form of `ModuleID/ControllerID/ActionID`
 * (e.g. `sitemap/create`), and `param1`, `param2` refers to a set of named parameters that
 * will be used to initialize the controller action (e.g. `--since=0` specifies a `since` parameter
 * whose value is 0 and a corresponding `$since` parameter is passed to the action method).
 *
 * A `help` command is provided by default, which lists available commands and shows their usage.
 * To use this command, simply type:
 *
 * ```
 * yii help
 * ```
 *
 * @property ErrorHandler $errorHandler The error handler application component. This property is read-only.
 * @property Request $request The request component. This property is read-only.
 * @property Response $response The response component. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \yii\base\Application
{
    /**
     * The option name for specifying the application configuration file path.
     */
    const OPTION_APPCONFIG = 'appconfig';

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
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $config = $this->loadConfig($config);
        parent::__construct($config);
    }

    /**
     * Loads the configuration.
     * This method will check if the command line option [[OPTION_APPCONFIG]] is specified.
     * If so, the corresponding file will be loaded as the application configuration.
     * Otherwise, the configuration provided as the parameter will be returned back.
     * @param array $config the configuration provided in the constructor.
     * @return array the actual configuration to be used by the application.
     */
    protected function loadConfig($config)
    {
        if (!empty($_SERVER['argv'])) {
            $option = '--' . self::OPTION_APPCONFIG . '=';
            foreach ($_SERVER['argv'] as $param) {
                if (strpos($param, $option) !== false) {
                    $path = substr($param, strlen($option));
                    if (!empty($path) && is_file($file = Yii::getAlias($path))) {
                        return require($file);
                    } else {
                        exit("The configuration file does not exist: $path\n");
                    }
                }
            }
        }

        return $config;
    }

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
            $response->exitStatus = $result;

            return $response;
        }
    }

    /**
     * Runs a controller action specified by a route.
     * This method parses the specified route and creates the corresponding child module(s), controller and action
     * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
     * If the route is empty, the method will use [[defaultRoute]].
     *
     * For example, to run `public function actionTest($a, $b)` assuming that the controller has options the following
     * code should be used:
     *
     * ```php
     * \Yii::$app->runAction('controller/test', ['option' => 'value', $a, $b]);
     * ```
     *
     * @param string $route the route that specifies the action.
     * @param array $params the parameters to be passed to the action
     * @return integer|Response the result of the action. This can be either an exit code or Response object.
     * Exit code 0 means normal, and other values mean abnormal. Exit code of `null` is treaded as `0` as well.
     * @throws Exception if the route is invalid
     */
    public function runAction($route, $params = [])
    {
        try {
            $res = parent::runAction($route, $params);
            return is_object($res) ? $res : (int)$res;
        } catch (InvalidRouteException $e) {
            $message = "Unknown command \"$route\"";
            if($alternatives = $this->findAlternatives($route)){
                $message .= count($alternatives) > 1 ?  "\nDid you mean one of these?" : "\nDid you mean this?";
                $message .= "\n    " . implode("\n    ", $alternatives);
            }
            throw new Exception($message, 0, $e);
        }
    }

    /**
     *
     * @param string $route
     * @return array
     */
    public function findAlternatives($route)
    {
        $commands = $this->getCommands();
        $threshold = 1e3;
        $alternatives = [];

        $commandParts = [];
        foreach ($commands as $command) {
            $commandParts[$command] = explode('/', $command);
        }

        foreach (explode('/', $route) as $i => $subname) {
            foreach ($commandParts as $command => $parts) {
                $exists = isset($alternatives[$command]);
                if (!isset($parts[$i]) && $exists) {
                    $alternatives[$command] += $threshold;
                    continue;
                } elseif (!isset($parts[$i])) {
                    continue;
                }

                $lev = levenshtein($subname, $parts[$i]);
                if ($lev <= strlen($subname) / 3 || '' !== $subname && false !== strpos($parts[$i], $subname)) {
                    $alternatives[$command] = $exists ? $alternatives[$command] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$command] += $threshold;
                }
            }
        }

        foreach ($commands as $command) {
            $lev = levenshtein($route, $command);
            if ($lev <= strlen($route) / 3 || strpos($command, $route) !== false) {
                $alternatives[$command] = isset($alternatives[$command]) ? $alternatives[$command] - $lev : $lev;
            }
        }
        
        $alternatives = array_filter($alternatives, function ($lev) use ($threshold) { 
            return $lev < 2 * $threshold;
        });
        asort($alternatives);

        return array_keys($alternatives);
    }
    
    /**
     * Returns all available command names.
     * @return array all available command names
     */
    public function getCommands()
    {
        $commands = $this->getModuleCommands($this);
        sort($commands);
        return array_unique($commands);
    }

    /**
     * Returns available commands of a specified module.
     * @param \yii\base\Module $module the module instance
     * @return array the available command names
     */
    protected function getModuleCommands($module)
    {
        $prefix = $module instanceof static ? '' : $module->getUniqueId() . '/';

        $commands = [];
        foreach (array_keys($module->controllerMap) as $id) {
            $commands[] = $prefix . $id;
            $controller = Yii::createObject($module->controllerMap[$id], [$id, $module]);
            foreach ($this->getActions($controller) as $actionId) {
                $commands[] = $prefix . $id . '/' . $actionId;
            }
        }

        foreach ($module->getModules() as $id => $child) {
            if (($child = $module->getModule($id)) === null) {
                continue;
            }
            foreach ($this->getModuleCommands($child) as $command) {
                $commands[] = $command;
            }
        }

        $controllerPath = $module->getControllerPath();
        if (is_dir($controllerPath)) {
            $files = scandir($controllerPath);
            foreach ($files as $file) {
                if (!empty($file) && substr_compare($file, 'Controller.php', -14, 14) === 0) {
                    $controllerClass = $module->controllerNamespace . '\\' . substr(basename($file), 0, -4);
                    if ($this->validateControllerClass($controllerClass)) {
                        $id = Inflector::camel2id(substr(basename($file), 0, -14));
                        $commands[] = $prefix . $id;
                        $controller = Yii::createObject($controllerClass, [$id, $module]);
                        foreach ($this->getActions($controller) as $actionId) {
                            $commands[] = $prefix . $id . '/' . $actionId;
                        }
                    }
                }
            }
        }

        return $commands;
    }

    /**
     * Validates if the given class is a valid console controller class.
     * @param string $controllerClass
     * @return boolean
     */
    protected function validateControllerClass($controllerClass)
    {
        if (class_exists($controllerClass)) {
            $class = new \ReflectionClass($controllerClass);
            return !$class->isAbstract() && $class->isSubclassOf('yii\console\Controller');
        } else {
            return false;
        }
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
            if ($name !== 'actions' && $method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0) {
                $actions[] = Inflector::camel2id(substr($name, 6), '-', true);
            }
        }

        return array_unique($actions);
    }

    /**
     * Returns the configuration of the built-in commands.
     * @return array the configuration of the built-in commands.
     */
    public function coreCommands()
    {
        return [
            'asset' => 'yii\console\controllers\AssetController',
            'cache' => 'yii\console\controllers\CacheController',
            'fixture' => 'yii\console\controllers\FixtureController',
            'help' => 'yii\console\controllers\HelpController',
            'message' => 'yii\console\controllers\MessageController',
            'migrate' => 'yii\console\controllers\MigrateController',
            'serve' => 'yii\console\controllers\ServeController',
        ];
    }

    /**
     * Returns the error handler component.
     * @return ErrorHandler the error handler application component.
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * Returns the request component.
     * @return Request the request component.
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Returns the response component.
     * @return Response the response component.
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * @inheritdoc
     */
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'request' => ['class' => 'yii\console\Request'],
            'response' => ['class' => 'yii\console\Response'],
            'errorHandler' => ['class' => 'yii\console\ErrorHandler'],
        ]);
    }
}
