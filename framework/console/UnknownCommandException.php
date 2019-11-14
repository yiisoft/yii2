<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\console\controllers\HelpController;

/**
 * UnknownCommandException 表示不正确使用控制台命令导致的异常。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0.11
 */
class UnknownCommandException extends Exception
{
    /**
     * @var string 无法识别的命令名。
     */
    public $command;

    /**
     * @var Application
     */
    protected $application;


    /**
     * 异常的构造函数。
     *
     * @param string $route 找不到的命令的路由。
     * @param Application $application 所涉及的控制台应用程序实例。
     * @param int $code 异常代码。
     * @param \Exception $previous 用于异常链接的上一个异常。
     */
    public function __construct($route, $application, $code = 0, \Exception $previous = null)
    {
        $this->command = $route;
        $this->application = $application;
        parent::__construct("Unknown command \"$route\".", $code, $previous);
    }

    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Unknown command';
    }

    /**
     * 根据字符串相似性为 [[$command]] 建议替代命令。
     *
     * 使用以下步骤搜索备选方案：
     *
     * - 建议以 `$command` 开头的备选方案
     * - 通过计算未知命令和所有可用命令之间的 Levenshtein 距离来查找
     *   拼写错误。Levenshtein 距离定义为将 str1 转换为 str2
     *   所需替换、插入或删除的最小字符数。
     *
     * @see http://php.net/manual/en/function.levenshtein.php
     * @return array 按相似性排序的建议备选方案列表。
     */
    public function getSuggestedAlternatives()
    {
        $help = $this->application->createController('help');
        if ($help === false || $this->command === '') {
            return [];
        }
        /** @var $helpController HelpController */
        list($helpController, $actionID) = $help;

        $availableActions = [];
        foreach ($helpController->getCommands() as $command) {
            $result = $this->application->createController($command);
            if ($result === false) {
                continue;
            }
            // add the command itself (default action)
            $availableActions[] = $command;

            // add all actions of this controller
            /** @var $controller Controller */
            list($controller, $actionID) = $result;
            $actions = $helpController->getActions($controller);
            if (!empty($actions)) {
                $prefix = $controller->getUniqueId();
                foreach ($actions as $action) {
                    $availableActions[] = $prefix . '/' . $action;
                }
            }
        }

        return $this->filterBySimilarity($availableActions, $this->command);
    }

    /**
     * 根据字符串相似性查找建议替代命令。
     *
     * 使用以下步骤搜索备选方案：
     *
     * - 建议以 `$command` 开头的备选方案
     * - 通过计算未知命令和所有可用命令之间的 Levenshtein 距离来查找
     *   拼写错误。Levenshtein 距离定义为将 str1 转换为 str2
     *   所需替换、插入或删除的最小字符数。
     *
     * @see http://php.net/manual/en/function.levenshtein.php
     * @param array $actions 可用命令名称。
     * @param string $command 要比较的命令。
     * @return array 按相似性排序的建议备选方案列表。
     */
    private function filterBySimilarity($actions, $command)
    {
        $alternatives = [];

        // suggest alternatives that begin with $command first
        foreach ($actions as $action) {
            if (strpos($action, $command) === 0) {
                $alternatives[] = $action;
            }
        }

        // calculate the Levenshtein distance between the unknown command and all available commands.
        $distances = array_map(function ($action) use ($command) {
            $action = strlen($action) > 255 ? substr($action, 0, 255) : $action;
            $command = strlen($command) > 255 ? substr($command, 0, 255) : $command;
            return levenshtein($action, $command);
        }, array_combine($actions, $actions));

        // we assume a typo if the levensthein distance is no more than 3, i.e. 3 replacements needed
        $relevantTypos = array_filter($distances, function ($distance) {
            return $distance <= 3;
        });
        asort($relevantTypos);
        $alternatives = array_merge($alternatives, array_flip($relevantTypos));

        return array_unique($alternatives);
    }
}
