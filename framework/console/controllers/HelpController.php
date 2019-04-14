<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use Yii;
use yii\base\Application;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\Inflector;

/**
 * 提供有关控制台命令的帮助信息。
 *
 * 此命令显示应用程序中的可用命令列表
 * 或有关使用特定命令的
 * 详细说明。
 *
 * 此命令可在命令行中使用如下：
 *
 * ```
 * yii help [command name]
 * ```
 *
 * 在上述命令中，如果未提供命令名称，
 * 将显示所有可用的命令。
 *
 * @property array $commands 所有可用的命令名。此属性为只读。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelpController extends Controller
{
    /**
     * 显示可用的命令或有关特定命令的
     * 详细信息。
     *
     * @param string $command 要显示帮助的命令的名称。
     * 如果未提供，将显示所有可用的命令。
     * @return int 退出状态
     * @throws Exception 如果帮助命令未知
     */
    public function actionIndex($command = null)
    {
        if ($command !== null) {
            $result = Yii::$app->createController($command);
            if ($result === false) {
                $name = $this->ansiFormat($command, Console::FG_YELLOW);
                throw new Exception("No help for unknown command \"$name\".");
            }

            list($controller, $actionID) = $result;

            $actions = $this->getActions($controller);
            if ($actionID !== '' || count($actions) === 1 && $actions[0] === $controller->defaultAction) {
                $this->getSubCommandHelp($controller, $actionID);
            } else {
                $this->getCommandHelp($controller);
            }
        } else {
            $this->getDefaultHelp();
        }
    }

    /**
     * 以计算机可读的格式列出所有可用的控制器和动作。
     * 这用于完成 shell。
     * @since 2.0.11
     */
    public function actionList()
    {
        foreach ($this->getCommandDescriptions() as $command => $description) {
            $result = Yii::$app->createController($command);
            if ($result === false || !($result[0] instanceof Controller)) {
                continue;
            }
            /** @var $controller Controller */
            list($controller, $actionID) = $result;
            $actions = $this->getActions($controller);
            if (!empty($actions)) {
                $prefix = $controller->getUniqueId();
                $this->stdout("$prefix\n");
                foreach ($actions as $action) {
                    $this->stdout("$prefix/$action\n");
                }
            }
        }
    }

    /**
     * 以机器可读格式列出 $action 的所有可用选项。
     * 这用于完成 shell。
     *
     * @param string $action 动作的路由
     * @since 2.0.11
     */
    public function actionListActionOptions($action)
    {
        $result = Yii::$app->createController($action);

        if ($result === false || !($result[0] instanceof Controller)) {
            return;
        }

        /** @var Controller $controller */
        list($controller, $actionID) = $result;
        $action = $controller->createAction($actionID);
        if ($action === null) {
            return;
        }

        foreach ($controller->getActionArgsHelp($action) as $argument => $help) {
            $description = preg_replace("~\R~", '', addcslashes($help['comment'], ':')) ?: $argument;
            $this->stdout($argument . ':' . $description . "\n");
        }

        $this->stdout("\n");
        foreach ($controller->getActionOptionsHelp($action) as $argument => $help) {
            $description = preg_replace("~\R~", '', addcslashes($help['comment'], ':'));
            $this->stdout('--' . $argument . ($description ? ':' . $description : '') . "\n");
        }
    }

    /**
     * 显示 $action 的使用信息。
     *
     * @param string $action 动作路由
     * @since 2.0.11
     */
    public function actionUsage($action)
    {
        $result = Yii::$app->createController($action);

        if ($result === false || !($result[0] instanceof Controller)) {
            return;
        }

        /** @var Controller $controller */
        list($controller, $actionID) = $result;
        $action = $controller->createAction($actionID);
        if ($action === null) {
            return;
        }

        $scriptName = $this->getScriptName();
        if ($action->id === $controller->defaultAction) {
            $this->stdout($scriptName . ' ' . $this->ansiFormat($controller->getUniqueId(), Console::FG_YELLOW));
        } else {
            $this->stdout($scriptName . ' ' . $this->ansiFormat($action->getUniqueId(), Console::FG_YELLOW));
        }

        foreach ($controller->getActionArgsHelp($action) as $name => $arg) {
            if ($arg['required']) {
                $this->stdout(' <' . $name . '>', Console::FG_CYAN);
            } else {
                $this->stdout(' [' . $name . ']', Console::FG_CYAN);
            }
        }

        $this->stdout("\n");
    }

    /**
     * 返回所有可用的命令名。
     * @return array 所有可用的命令名。
     */
    public function getCommands()
    {
        $commands = $this->getModuleCommands(Yii::$app);
        sort($commands);
        return array_unique($commands);
    }

    /**
     * 返回命令数组及其说明。
     * @return array 所有可用命令作为键，其描述为值。
     */
    protected function getCommandDescriptions()
    {
        $descriptions = [];
        foreach ($this->getCommands() as $command) {
            $description = '';

            $result = Yii::$app->createController($command);
            if ($result !== false && $result[0] instanceof Controller) {
                list($controller, $actionID) = $result;
                /** @var Controller $controller */
                $description = $controller->getHelpSummary();
            }

            $descriptions[$command] = $description;
        }

        return $descriptions;
    }

    /**
     * 返回指定控制器的所有可用动作。
     * @param Controller $controller 控制器实例
     * @return array 所有可用的动作 IDs.
     */
    public function getActions($controller)
    {
        $actions = array_keys($controller->actions());
        $class = new \ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($name !== 'actions' && $method->isPublic() && !$method->isStatic() && strncmp($name, 'action', 6) === 0) {
                $actions[] = Inflector::camel2id(substr($name, 6), '-', true);
            }
        }
        sort($actions);

        return array_unique($actions);
    }

    /**
     * 返回指定模块的可用命令。
     * @param \yii\base\Module $module 模块实例
     * @return array 可用的命令名称
     */
    protected function getModuleCommands($module)
    {
        $prefix = $module instanceof Application ? '' : $module->getUniqueId() . '/';

        $commands = [];
        foreach (array_keys($module->controllerMap) as $id) {
            $commands[] = $prefix . $id;
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
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerPath, \RecursiveDirectoryIterator::KEY_AS_PATHNAME));
            $iterator = new \RegexIterator($iterator, '/.*Controller\.php$/', \RecursiveRegexIterator::GET_MATCH);
            foreach ($iterator as $matches) {
                $file = $matches[0];
                $relativePath = str_replace($controllerPath, '', $file);
                $class = strtr($relativePath, [
                    '/' => '\\',
                    '.php' => '',
                ]);
                $controllerClass = $module->controllerNamespace . $class;
                if ($this->validateControllerClass($controllerClass)) {
                    $dir = ltrim(pathinfo($relativePath, PATHINFO_DIRNAME), '\\/');

                    $command = Inflector::camel2id(substr(basename($file), 0, -14), '-', true);
                    if (!empty($dir)) {
                        $command = $dir . '/' . $command;
                    }
                    $commands[] = $prefix . $command;
                }
            }
        }

        return $commands;
    }

    /**
     * 验证给定的类是否是有效的控制台控制器类。
     * @param string $controllerClass
     * @return bool
     */
    protected function validateControllerClass($controllerClass)
    {
        if (class_exists($controllerClass)) {
            $class = new \ReflectionClass($controllerClass);
            return !$class->isAbstract() && $class->isSubclassOf('yii\console\Controller');
        }

        return false;
    }

    /**
     * 显示所有可用命令。
     */
    protected function getDefaultHelp()
    {
        $commands = $this->getCommandDescriptions();
        $this->stdout($this->getDefaultHelpHeader());
        if (!empty($commands)) {
            $this->stdout("\nThe following commands are available:\n\n", Console::BOLD);
            $len = 0;
            foreach ($commands as $command => $description) {
                $result = Yii::$app->createController($command);
                if ($result !== false && $result[0] instanceof Controller) {
                    /** @var $controller Controller */
                    list($controller, $actionID) = $result;
                    $actions = $this->getActions($controller);
                    if (!empty($actions)) {
                        $prefix = $controller->getUniqueId();
                        foreach ($actions as $action) {
                            $string = $prefix . '/' . $action;
                            if ($action === $controller->defaultAction) {
                                $string .= ' (default)';
                            }
                            if (($l = strlen($string)) > $len) {
                                $len = $l;
                            }
                        }
                    }
                } elseif (($l = strlen($command)) > $len) {
                    $len = $l;
                }
            }
            foreach ($commands as $command => $description) {
                $this->stdout('- ' . $this->ansiFormat($command, Console::FG_YELLOW));
                $this->stdout(str_repeat(' ', $len + 4 - strlen($command)));
                $this->stdout(Console::wrapText($description, $len + 4 + 2), Console::BOLD);
                $this->stdout("\n");

                $result = Yii::$app->createController($command);
                if ($result !== false && $result[0] instanceof Controller) {
                    list($controller, $actionID) = $result;
                    $actions = $this->getActions($controller);
                    if (!empty($actions)) {
                        $prefix = $controller->getUniqueId();
                        foreach ($actions as $action) {
                            $string = '  ' . $prefix . '/' . $action;
                            $this->stdout('  ' . $this->ansiFormat($string, Console::FG_GREEN));
                            if ($action === $controller->defaultAction) {
                                $string .= ' (default)';
                                $this->stdout(' (default)', Console::FG_YELLOW);
                            }
                            $summary = $controller->getActionHelpSummary($controller->createAction($action));
                            if ($summary !== '') {
                                $this->stdout(str_repeat(' ', $len + 4 - strlen($string)));
                                $this->stdout(Console::wrapText($summary, $len + 4 + 2));
                            }
                            $this->stdout("\n");
                        }
                    }
                    $this->stdout("\n");
                }
            }
            $scriptName = $this->getScriptName();
            $this->stdout("\nTo see the help of each command, enter:\n", Console::BOLD);
            $this->stdout("\n  $scriptName " . $this->ansiFormat('help', Console::FG_YELLOW) . ' '
                . $this->ansiFormat('<command-name>', Console::FG_CYAN) . "\n\n");
        } else {
            $this->stdout("\nNo commands are found.\n\n", Console::BOLD);
        }
    }

    /**
     * 显示命令的整体信息。
     * @param Controller $controller 控制器实例
     */
    protected function getCommandHelp($controller)
    {
        $controller->color = $this->color;

        $this->stdout("\nDESCRIPTION\n", Console::BOLD);
        $comment = $controller->getHelp();
        if ($comment !== '') {
            $this->stdout("\n$comment\n\n");
        }

        $actions = $this->getActions($controller);
        if (!empty($actions)) {
            $this->stdout("\nSUB-COMMANDS\n\n", Console::BOLD);
            $prefix = $controller->getUniqueId();

            $maxlen = 5;
            foreach ($actions as $action) {
                $len = strlen($prefix . '/' . $action) + 2 + ($action === $controller->defaultAction ? 10 : 0);
                if ($maxlen < $len) {
                    $maxlen = $len;
                }
            }
            foreach ($actions as $action) {
                $this->stdout('- ' . $this->ansiFormat($prefix . '/' . $action, Console::FG_YELLOW));
                $len = strlen($prefix . '/' . $action) + 2;
                if ($action === $controller->defaultAction) {
                    $this->stdout(' (default)', Console::FG_GREEN);
                    $len += 10;
                }
                $summary = $controller->getActionHelpSummary($controller->createAction($action));
                if ($summary !== '') {
                    $this->stdout(str_repeat(' ', $maxlen - $len + 2) . Console::wrapText($summary, $maxlen + 2));
                }
                $this->stdout("\n");
            }
            $scriptName = $this->getScriptName();
            $this->stdout("\nTo see the detailed information about individual sub-commands, enter:\n");
            $this->stdout("\n  $scriptName " . $this->ansiFormat('help', Console::FG_YELLOW) . ' '
                . $this->ansiFormat('<sub-command>', Console::FG_CYAN) . "\n\n");
        }
    }

    /**
     * 显示命令操作的详细信息。
     * @param Controller $controller 控制器实例
     * @param string $actionID 动作 ID
     * @throws Exception 如果动作不存在
     */
    protected function getSubCommandHelp($controller, $actionID)
    {
        $action = $controller->createAction($actionID);
        if ($action === null) {
            $name = $this->ansiFormat(rtrim($controller->getUniqueId() . '/' . $actionID, '/'), Console::FG_YELLOW);
            throw new Exception("No help for unknown sub-command \"$name\".");
        }

        $description = $controller->getActionHelp($action);
        if ($description !== '') {
            $this->stdout("\nDESCRIPTION\n", Console::BOLD);
            $this->stdout("\n$description\n\n");
        }

        $this->stdout("\nUSAGE\n\n", Console::BOLD);
        $scriptName = $this->getScriptName();
        if ($action->id === $controller->defaultAction) {
            $this->stdout($scriptName . ' ' . $this->ansiFormat($controller->getUniqueId(), Console::FG_YELLOW));
        } else {
            $this->stdout($scriptName . ' ' . $this->ansiFormat($action->getUniqueId(), Console::FG_YELLOW));
        }

        $args = $controller->getActionArgsHelp($action);
        foreach ($args as $name => $arg) {
            if ($arg['required']) {
                $this->stdout(' <' . $name . '>', Console::FG_CYAN);
            } else {
                $this->stdout(' [' . $name . ']', Console::FG_CYAN);
            }
        }

        $options = $controller->getActionOptionsHelp($action);
        $options[\yii\console\Application::OPTION_APPCONFIG] = [
            'type' => 'string',
            'default' => null,
            'comment' => "custom application configuration file path.\nIf not set, default application configuration is used.",
        ];
        ksort($options);

        if (!empty($options)) {
            $this->stdout(' [...options...]', Console::FG_RED);
        }
        $this->stdout("\n\n");

        if (!empty($args)) {
            foreach ($args as $name => $arg) {
                $this->stdout($this->formatOptionHelp(
                        '- ' . $this->ansiFormat($name, Console::FG_CYAN),
                        $arg['required'],
                        $arg['type'],
                        $arg['default'],
                        $arg['comment']) . "\n\n");
            }
        }

        if (!empty($options)) {
            $this->stdout("\nOPTIONS\n\n", Console::BOLD);
            foreach ($options as $name => $option) {
                $this->stdout($this->formatOptionHelp(
                        $this->ansiFormat('--' . $name . $this->formatOptionAliases($controller, $name),
                            Console::FG_RED, empty($option['required']) ? Console::FG_RED : Console::BOLD),
                        !empty($option['required']),
                        $option['type'],
                        $option['default'],
                        $option['comment']) . "\n\n");
            }
        }
    }

    /**
     * 为参数或选项生成格式正确的字符串。
     * @param string $name 参数或选项的名称
     * @param bool $required 参数是否必需
     * @param string $type 选项或参数的类型
     * @param mixed $defaultValue 选项或参数的默认值
     * @param string $comment 关于选项或参数的注释
     * @return string 参数或选项的格式化字符串
     */
    protected function formatOptionHelp($name, $required, $type, $defaultValue, $comment)
    {
        $comment = trim($comment);
        $type = trim($type);
        if (strncmp($type, 'bool', 4) === 0) {
            $type = 'boolean, 0 or 1';
        }

        if ($defaultValue !== null && !is_array($defaultValue)) {
            if ($type === null) {
                $type = gettype($defaultValue);
            }
            if (is_bool($defaultValue)) {
                // show as integer to avoid confusion
                $defaultValue = (int)$defaultValue;
            }
            if (is_string($defaultValue)) {
                $defaultValue = "'" . $defaultValue . "'";
            } else {
                $defaultValue = var_export($defaultValue, true);
            }
            $doc = "$type (defaults to $defaultValue)";
        } else {
            $doc = $type;
        }

        if ($doc === '') {
            $doc = $comment;
        } elseif ($comment !== '') {
            $doc .= "\n" . preg_replace('/^/m', '  ', $comment);
        }

        $name = $required ? "$name (required)" : $name;

        return $doc === '' ? $name : "$name: $doc";
    }

    /**
     * @param Controller $controller 控制器实例
     * @param string $option 选项名称
     * @return string 别名参数或选项的格式化字符串。
     * @since 2.0.8
     */
    protected function formatOptionAliases($controller, $option)
    {
        foreach ($controller->optionAliases() as $name => $value) {
            if ($value === $option) {
                return ', -' . $name;
            }
        }

        return '';
    }

    /**
     * @return string 当前运行的 cli 脚本的名称。
     */
    protected function getScriptName()
    {
        return basename(Yii::$app->request->scriptFile);
    }

    /**
     * 返回默认帮助标题。
     * @return string 默认帮助标题。
     * @since 2.0.11
     */
    protected function getDefaultHelpHeader()
    {
        return "\nThis is Yii version " . \Yii::getVersion() . ".\n";
    }
}
