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
use yii\helpers\Console;
use yii\helpers\Inflector;

/**
 * Provides help information about console commands.
 *
 * This command displays the available command list in
 * the application or the detailed instructions about using
 * a specific command.
 *
 * This command can be used as follows on command line:
 *
 * ~~~
 * yii help [command name]
 * ~~~
 *
 * In the above, if the command name is not provided, all
 * available commands will be displayed.
 *
 * @property array $commands All available command names. This property is read-only.
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
                throw new Exception(Yii::t('yii', 'No help for unknown command "{command}".', [
                    'command' => $this->ansiFormat($command, Console::FG_YELLOW),
                ]));
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
     * Returns an array of commands an their descriptions.
     * @return array all available commands as keys and their description as values.
     */
    protected function getCommandDescriptions()
    {
        $descriptions = [];
        foreach ($this->getCommands() as $command) {
            $description = '';

            $result = Yii::$app->createController($command);
            if ($result !== false) {
                list($controller, $actionID) = $result;
                $class = new \ReflectionClass($controller);

                $docLines = preg_split('~(\n|\r|\r\n)~', $class->getDocComment());
                if (isset($docLines[1])) {
                    $description = trim($docLines[1], ' *');
                }
            }

            $descriptions[$command] = $description;
        }

        return $descriptions;
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
                $actions[] = Inflector::camel2id(substr($name, 6));
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
            $files = scandir($controllerPath);
            foreach ($files as $file) {
                if (strcmp(substr($file, -14), 'Controller.php') === 0) {
                    $controllerClass = $module->controllerNamespace . '\\' . substr(basename($file), 0, -4);
                    if ($this->validateControllerClass($controllerClass)) {
                        $commands[] = $prefix . Inflector::camel2id(substr(basename($file), 0, -14));
                    }
                }
            }
        }

        return $commands;
    }

    /**
     * Validates if the given class is a valid console controller class.
     * @param string $controllerClass
     * @return bool
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
     * Displays all available commands.
     */
    protected function getHelp()
    {
        $commands = $this->getCommandDescriptions();
        if (!empty($commands)) {
            $this->stdout("\nThe following commands are available:\n\n", Console::BOLD);
            $len = 0;
            foreach ($commands as $command => $description) {
                if (($l = strlen($command)) > $len) {
                    $len = $l;
                }
            }
            foreach ($commands as $command => $description) {
                echo "- " . $this->ansiFormat($command, Console::FG_YELLOW);
                echo str_repeat(' ', $len + 3 - strlen($command)) . $description;
                echo "\n";
            }
            $scriptName = $this->getScriptName();
            $this->stdout("\nTo see the help of each command, enter:\n", Console::BOLD);
            echo "\n  $scriptName " . $this->ansiFormat('help', Console::FG_YELLOW) . ' '
                            . $this->ansiFormat('<command-name>', Console::FG_CYAN) . "\n\n";
        } else {
            $this->stdout("\nNo commands are found.\n\n", Console::BOLD);
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
            $this->stdout("\nDESCRIPTION\n", Console::BOLD);
            echo "\n" . rtrim(Console::renderColoredString(Console::markdownToAnsi($comment))) . "\n\n";
        }

        $actions = $this->getActions($controller);
        if (!empty($actions)) {
            $this->stdout("\nSUB-COMMANDS\n\n", Console::BOLD);
            $prefix = $controller->getUniqueId();
            foreach ($actions as $action) {
                echo '- ' . $this->ansiFormat($prefix.'/'.$action, Console::FG_YELLOW);
                if ($action === $controller->defaultAction) {
                    $this->stdout(' (default)', Console::FG_GREEN);
                }
                $summary = $this->getActionSummary($controller, $action);
                if ($summary !== '') {
                    echo ': ' . $summary;
                }
                echo "\n";
            }
            $scriptName = $this->getScriptName();
            echo "\nTo see the detailed information about individual sub-commands, enter:\n";
            echo "\n  $scriptName " . $this->ansiFormat('help', Console::FG_YELLOW) . ' '
                            . $this->ansiFormat('<sub-command>', Console::FG_CYAN) . "\n\n";
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
            throw new Exception(Yii::t('yii', 'No help for unknown sub-command "{command}".', [
                'command' => rtrim($controller->getUniqueId() . '/' . $actionID, '/'),
            ]));
        }
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($controller, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $tags = $this->parseComment($method->getDocComment());
        $options = $this->getOptionHelps($controller, $actionID);

        if ($tags['description'] !== '') {
            $this->stdout("\nDESCRIPTION\n", Console::BOLD);
            echo "\n" . rtrim(Console::renderColoredString(Console::markdownToAnsi($tags['description']))) . "\n\n";
        }

        $this->stdout("\nUSAGE\n\n", Console::BOLD);
        $scriptName = $this->getScriptName();
        if ($action->id === $controller->defaultAction) {
            echo $scriptName . ' ' . $this->ansiFormat($controller->getUniqueId(), Console::FG_YELLOW);
        } else {
            echo $scriptName . ' ' . $this->ansiFormat($action->getUniqueId(), Console::FG_YELLOW);
        }
        list ($required, $optional) = $this->getArgHelps($method, isset($tags['param']) ? $tags['param'] : []);
        foreach ($required as $arg => $description) {
            $this->stdout(' <' . $arg . '>', Console::FG_CYAN);
        }
        foreach ($optional as $arg => $description) {
            $this->stdout(' [' . $arg . ']', Console::FG_CYAN);
        }
        if (!empty($options)) {
            $this->stdout(' [...options...]', Console::FG_RED);
        }
        echo "\n\n";

        if (!empty($required) || !empty($optional)) {
            echo implode("\n\n", array_merge($required, $optional)) . "\n\n";
        }

        if (!empty($options)) {
            $this->stdout("\nOPTIONS\n\n", Console::BOLD);
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
            $tags = [$tags];
        }
        $params = $method->getParameters();
        $optional = $required = [];
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
                $optional[$name] = $this->formatOptionHelp('- ' . $this->ansiFormat($name, Console::FG_CYAN), false, $type, $param->getDefaultValue(), $comment);
            } else {
                $required[$name] = $this->formatOptionHelp('- ' . $this->ansiFormat($name, Console::FG_CYAN), true, $type, null, $comment);
            }
        }

        return [$required, $optional];
    }

    /**
     * Returns the help information about the options available for a console controller.
     * @param Controller $controller the console controller
     * @param string $actionID name of the action, if set include local options for that action
     * @return array the help information about the options
     */
    protected function getOptionHelps($controller, $actionID)
    {
        $optionNames = $controller->options($actionID);
        if (empty($optionNames)) {
            return [];
        }

        $class = new \ReflectionClass($controller);
        $options = [];
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
                $options[$name] = $this->formatOptionHelp($this->ansiFormat('--' . $name, Console::FG_RED), false, $type, $defaultValue, $comment);
            } else {
                $options[$name] = $this->formatOptionHelp($this->ansiFormat('--' . $name, Console::FG_RED), false, null, $defaultValue, '');
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
        $tags = [];
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
                    $tags[$name] = [$tags[$name], trim($matches[2])];
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
            if (is_bool($defaultValue)) {
                // show as integer to avoid confusion
                $defaultValue = (int) $defaultValue;
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

    /**
     * @return string the name of the cli script currently running.
     */
    protected function getScriptName()
    {
        return basename(Yii::$app->request->scriptFile);
    }
}
