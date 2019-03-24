<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\InvalidRouteException;

// 如果PHP SAPI没有定义它们，则定义STDIN，STDOUT和STDERR(e.g. 在web env中创建控制台应用程序)
// http://php.net/manual/en/features.commandline.io-streams.php
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
defined('STDERR') or define('STDERR', fopen('php://stderr', 'w'));

/**
 * Application代表一个控制台应用程序。
 * Application继承自 [[\yii\base\Application]] 通过提供特定于
 * 控制台请求的功能。特别是，它处理控制台请求
 * 通过基于命令的方法：

 * - 控制台应用程序包含一个或多个可能的用户命令;
 * - 每个用户命令都实现为继承 [[\yii\console\Controller]] 的类;
 * - 用户指定在命令行上运行哪个命令;
 * - 该命令使用指定的参数处理用户请求。

 * 命令类应位于 [[controllerNamespace]] 指定的命名空间下。
 * 它们的命名应遵循与控制器相同的命名约定。例如，`help` 命令
 * 使用 `HelpController` 类实现。

 * 要运行控制台应用程序，请在命令行中输入以下内容：

 * ```
 * yii <route> [--param1=value1 --param2 ...]
 * ```

 * 其中 `<route>` 指的是 `ModuleID/ControllerID/ActionID` 形式的控制器路由
 * (e.g. `sitemap/create`), 和 `param1`, `param2` 指的是一组命名参数
 * 将用于初始化控制器动作 (e.g. `--since=0` 指定一个 `since` 参数
 * 其值为0并且将相应的 `$since` 参数传递给动作方法).

 * 默认提供`help`命令，列出可用命令并显示其用法。
 * 要使用此命令，只需键入：

 * ```
 * yii help
 * ```

 * @property ErrorHandler $errorHandler 错误处理程序应用程序组件。此属性是只读的。
 * @property Request $request 请求组件。此属性是只读的。
 * @property Response $response 响应组件。此属性是只读的。

 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \yii\base\Application
{
    /**
     * 用于指定应用程序配置文件路径的选项名称。
     */
    const OPTION_APPCONFIG = 'appconfig';

    /**
     * @var string 此应用程序的默认路由。默认为'help'，
     * 表示`help`命令。
     */
    public $defaultRoute = 'help';
    /**
     * @var bool 是否启用核心框架提供的命令。
     * 默认为true。
     */
    public $enableCoreCommands = true;
    /**
     * @var Controller 当前运行的控制器实例
     */
    public $controller;


    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        $config = $this->loadConfig($config);
        parent::__construct($config);
    }

    /**
     * 加载配置。
     * 此方法将检查是否指定了命令行选项 [[OPTION_APPCONFIG]] 。
     * 如果是，则将相应的文件作为应用程序配置加载。
     * 否则，将返回作为参数提供的配置。
     * @param array $config 构造函数中提供的配置。
     * @return array 应用程序使用的实际配置。
     */
    protected function loadConfig($config)
    {
        if (!empty($_SERVER['argv'])) {
            $option = '--' . self::OPTION_APPCONFIG . '=';
            foreach ($_SERVER['argv'] as $param) {
                if (strpos($param, $option) !== false) {
                    $path = substr($param, strlen($option));
                    if (!empty($path) && is_file($file = Yii::getAlias($path))) {
                        return require $file;
                    }

                    exit("The configuration file does not exist: $path\n");
                }
            }
        }

        return $config;
    }

    /**
     * 初始化应用程序。
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
        // 确保我们有'help'命令，以便我们列出可用的命令
        if (!isset($this->controllerMap['help'])) {
            $this->controllerMap['help'] = 'yii\console\controllers\HelpController';
        }
    }

    /**
     * 处理指定的请求。
     * @param Request $request 要处理的请求
     * @return Response 生成的响应
     */
    public function handleRequest($request)
    {
        list($route, $params) = $request->resolve();
        $this->requestedRoute = $route;
        $result = $this->runAction($route, $params);
        if ($result instanceof Response) {
            return $result;
        }

        $response = $this->getResponse();
        $response->exitStatus = $result;

        return $response;
    }

    /**
     * 运行路由指定的控制器操作。
     * 此方法解析指定的路由并创建相应的子模块，控制器和动作
     * 实例。然后调用 [[Controller::runAction()]] 通过给定的参数来运行动作。
     * 如果路由为空，则方法将使用 [[defaultRoute]] 。
     *
     * 例如，运行 `public function actionTest($a, $b)` 假设控制器有选项
     * 应使用以下代码：
     *
     * ```php
     * \Yii::$app->runAction('controller/test', ['option' => 'value', $a, $b]);
     * ```
     *
     * @param string $route 指定动作的路由。
     * @param array $params 要传递给动作的参数
     * @return int|Response 动作的结果。这可以是退出码或Response对象。
     * 退出代码0表示正常，其他值表示异常。退出代码`null`也被视为`0`.
     * @throws Exception 如果路由无效
     */
    public function runAction($route, $params = [])
    {
        try {
            $res = parent::runAction($route, $params);
            return is_object($res) ? $res : (int) $res;
        } catch (InvalidRouteException $e) {
            throw new UnknownCommandException($route, $this, 0, $e);
        }
    }

    /**
     * 返回内置命令的配置。
     * @return array 内置命令的配置。
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
     * 返回错误处理程序组件。
     * @return ErrorHandler 错误处理程序应用程序组件。
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * 返回请求组件。
     * @return Request 请求组件。
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * 返回响应组件。
     * @return Response 响应组件。
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * {@inheritdoc}
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
