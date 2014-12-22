<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\Action;
use yii\base\InlineAction;
use yii\base\InvalidRouteException;
use yii\helpers\Console;

/**
 * Controller is the base class of console command classes.
 *
 * A console controller consists of one or several actions known as sub-commands.
 * Users call a console command by specifying the corresponding route which identifies a controller action.
 * The `yii` program is used when calling a console command, like the following:
 *
 * ~~~
 * yii <route> [--param1=value1 --param2 ...]
 * ~~~
 *
 * where `<route>` is a route to a controller action and the params will be populated as properties of a command.
 * See [[options()]] for details.
 *
 * @property string $help This property is read-only.
 * @property string $helpSummary This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
    const EXIT_CODE_NORMAL = 0;
    const EXIT_CODE_ERROR = 1;

    /**
     * @var boolean whether to run the command interactively.
     */
    public $interactive = true;
    /**
     * @var boolean whether to enable ANSI color in the output.
     * If not set, ANSI color will only be enabled for terminals that support it.
     */
    public $color;


    /**
     * Returns a value indicating whether ANSI color is enabled.
     *
     * ANSI color is enabled only if [[color]] is set true or is not set
     * and the terminal supports ANSI color.
     *
     * @param resource $stream the stream to check.
     * @return boolean Whether to enable ANSI style in output.
     */
    public function isColorEnabled($stream = \STDOUT)
    {
        return $this->color === null ? Console::streamSupportsAnsiColors($stream) : $this->color;
    }

    /**
     * Runs an action with the specified action ID and parameters.
     * If the action ID is empty, the method will use [[defaultAction]].
     * @param string $id the ID of the action to be executed.
     * @param array $params the parameters (name-value pairs) to be passed to the action.
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
     * @throws Exception if there are unknown options or missing arguments
     * @see createAction
     */
    public function runAction($id, $params = [])
    {
        if (!empty($params)) {
            // populate options here so that they are available in beforeAction().
            $options = $this->options($id);
            foreach ($params as $name => $value) {
                if (in_array($name, $options, true)) {
                    $default = $this->$name;
                    $this->$name = is_array($default) ? preg_split('/\s*,\s*/', $value) : $value;
                    unset($params[$name]);
                } elseif (!is_int($name)) {
                    throw new Exception(Yii::t('yii', 'Unknown option: --{name}', ['name' => $name]));
                }
            }
        }
        return parent::runAction($id, $params);
    }

    /**
     * Binds the parameters to the action.
     * This method is invoked by [[Action]] when it begins to run with the given parameters.
     * This method will first bind the parameters with the [[options()|options]]
     * available to the action. It then validates the given arguments.
     * @param Action $action the action to be bound with parameters
     * @param array $params the parameters to be bound to the action
     * @return array the valid parameters that the action can run with.
     * @throws Exception if there are unknown options or missing arguments
     */
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = array_values($params);

        $missing = [];
        foreach ($method->getParameters() as $i => $param) {
            if ($param->isArray() && isset($args[$i])) {
                $args[$i] = preg_split('/\s*,\s*/', $args[$i]);
            }
            if (!isset($args[$i])) {
                if ($param->isDefaultValueAvailable()) {
                    $args[$i] = $param->getDefaultValue();
                } else {
                    $missing[] = $param->getName();
                }
            }
        }

        if (!empty($missing)) {
            throw new Exception(Yii::t('yii', 'Missing required arguments: {params}', ['params' => implode(', ', $missing)]));
        }

        return $args;
    }

    /**
     * Formats a string with ANSI codes
     *
     * You may pass additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ~~~
     * echo $this->ansiFormat('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ~~~
     *
     * @param string $string the string to be formatted
     * @return string
     */
    public function ansiFormat($string)
    {
        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return $string;
    }

    /**
     * Prints a string to STDOUT
     *
     * You may optionally format the string with ANSI codes by
     * passing additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ~~~
     * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ~~~
     *
     * @param string $string the string to print
     * @return int|boolean Number of bytes printed or false on error
     */
    public function stdout($string)
    {
        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return Console::stdout($string);
    }

    /**
     * Prints a string to STDERR
     *
     * You may optionally format the string with ANSI codes by
     * passing additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ~~~
     * $this->stderr('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ~~~
     *
     * @param string $string the string to print
     * @return int|boolean Number of bytes printed or false on error
     */
    public function stderr($string)
    {
        if ($this->isColorEnabled(\STDERR)) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return fwrite(\STDERR, $string);
    }

    /**
     * Prompts the user for input and validates it
     *
     * @param string $text prompt string
     * @param array $options the options to validate the input:
     *
     *  - required: whether it is required or not
     *  - default: default value if no input is inserted by the user
     *  - pattern: regular expression pattern to validate user input
     *  - validator: a callable function to validate input. The function must accept two parameters:
     *      - $input: the user input to validate
     *      - $error: the error value passed by reference if validation failed.
     * @return string the user input
     */
    public function prompt($text, $options = [])
    {
        if ($this->interactive) {
            return Console::prompt($text, $options);
        } else {
            return isset($options['default']) ? $options['default'] : '';
        }
    }

    /**
     * Asks user to confirm by typing y or n.
     *
     * @param string $message to echo out before waiting for user input
     * @param boolean $default this value is returned if no selection is made.
     * @return boolean whether user confirmed.
     * Will return true if [[interactive]] is false.
     */
    public function confirm($message, $default = false)
    {
        if ($this->interactive) {
            return Console::confirm($message, $default);
        } else {
            return true;
        }
    }

    /**
     * Gives the user an option to choose from. Giving '?' as an input will show
     * a list of options to choose from and their explanations.
     *
     * @param string $prompt the prompt message
     * @param array $options Key-value array of options to choose from
     *
     * @return string An option character the user chose
     */
    public function select($prompt, $options = [])
    {
        return Console::select($prompt, $options);
    }

    /**
     * Returns the names of valid options for the action (id)
     * An option requires the existence of a public member variable whose
     * name is the option name.
     * Child classes may override this method to specify possible options.
     *
     * Note that the values setting via options are not available
     * until [[beforeAction()]] is being called.
     *
     * @param string $actionID the action id of the current request
     * @return array the names of the options valid for the action
     */
    public function options($actionID)
    {
        // $actionId might be used in subclasses to provide options specific to action id
        return ['color', 'interactive'];
    }

    /**
     * Returns one-line short summary describing this controller.
     *
     * You may override this method to return customized summary.
     * The default implementation returns first line from the PHPDoc comment.
     *
     * @return string
     */
    public function getHelpSummary()
    {
        return $this->parseDocCommentSummary(new \ReflectionClass($this));
    }

    /**
     * Returns help information for this controller.
     *
     * You may override this method to return customized help.
     * The default implementation returns help information retrieved from the PHPDoc comment.
     * @return string
     */
    public function getHelp()
    {
        return $this->parseDocCommentDetail(new \ReflectionClass($this));
    }

    /**
     * Returns a one-line short summary describing the specified action.
     * @param Action $action action to get summary for
     * @return string a one-line short summary describing the specified action.
     */
    public function getActionHelpSummary($action)
    {
        return $this->parseDocCommentSummary($this->getActionMethodReflection($action));
    }

    /**
     * Returns the detailed help information for the specified action.
     * @param Action $action action to get help for
     * @return string the detailed help information for the specified action.
     */
    public function getActionHelp($action)
    {
        return $this->parseDocCommentDetail($this->getActionMethodReflection($action));
    }

    /**
     * Returns the help information for the anonymous arguments for the action.
     * The returned value should be an array. The keys are the argument names, and the values are
     * the corresponding help information. Each value must be an array of the following structure:
     *
     * - required: boolean, whether this argument is required.
     * - type: string, the PHP type of this argument.
     * - default: string, the default value of this argument
     * - comment: string, the comment of this argument
     *
     * The default implementation will return the help information extracted from the doc-comment of
     * the parameters corresponding to the action method.
     *
     * @param Action $action
     * @return array the help information of the action arguments
     */
    public function getActionArgsHelp($action)
    {
        $method = $this->getActionMethodReflection($action);
        $tags = $this->parseDocCommentTags($method);
        $params = isset($tags['param']) ? (array) $tags['param'] : [];

        $args = [];
        foreach ($method->getParameters() as $i => $reflection) {
            $name = $reflection->getName();
            $tag = isset($params[$i]) ? $params[$i] : '';
            if (preg_match('/^([^\s]+)\s+(\$\w+\s+)?(.*)/s', $tag, $matches)) {
                $type = $matches[1];
                $comment = $matches[3];
            } else {
                $type = null;
                $comment = $tag;
            }
            if ($reflection->isDefaultValueAvailable()) {
                $args[$name] = [
                    'required' => false,
                    'type' => $type,
                    'default' => $reflection->getDefaultValue(),
                    'comment' => $comment,
                ];
            } else {
                $args[$name] = [
                    'required' => true,
                    'type' => $type,
                    'default' => null,
                    'comment' => $comment,
                ];
            }
        }
        return $args;
    }

    /**
     * Returns the help information for the options for the action.
     * The returned value should be an array. The keys are the option names, and the values are
     * the corresponding help information. Each value must be an array of the following structure:
     *
     * - type: string, the PHP type of this argument.
     * - default: string, the default value of this argument
     * - comment: string, the comment of this argument
     *
     * The default implementation will return the help information extracted from the doc-comment of
     * the properties corresponding to the action options.
     *
     * @param Action $action
     * @return array the help information of the action options
     */
    public function getActionOptionsHelp($action)
    {
        $optionNames = $this->options($action->id);
        if (empty($optionNames)) {
            return [];
        }

        $class = new \ReflectionClass($this);
        $options = [];
        foreach ($class->getProperties() as $property) {
            $name = $property->getName();
            if (!in_array($name, $optionNames, true)) {
                continue;
            }
            $defaultValue = $property->getValue($this);
            $tags = $this->parseDocCommentTags($property);
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
                $options[$name] = [
                    'type' => $type,
                    'default' => $defaultValue,
                    'comment' => $comment,
                ];
            } else {
                $options[$name] = [
                    'type' => null,
                    'default' => $defaultValue,
                    'comment' => '',
                ];
            }
        }
        return $options;
    }

    private $_reflections = [];

    /**
     * @param Action $action
     * @return \ReflectionMethod
     */
    protected function getActionMethodReflection($action)
    {
        if (!isset($this->_reflections[$action->id])) {
            if ($action instanceof InlineAction) {
                $this->_reflections[$action->id] = new \ReflectionMethod($this, $action->actionMethod);
            } else {
                $this->_reflections[$action->id] = new \ReflectionMethod($action, 'run');
            }
        }
        return $this->_reflections[$action->id];
    }

    /**
     * Parses the comment block into tags.
     * @param \Reflector $reflection the comment block
     * @return array the parsed tags
     */
    protected function parseDocCommentTags($reflection)
    {
        $comment = $reflection->getDocComment();
        $comment = "@description \n" . strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($comment, '/'))), "\r", '');
        $parts = preg_split('/^\s*@/m', $comment, -1, PREG_SPLIT_NO_EMPTY);
        $tags = [];
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
     * Returns the first line of docblock.
     *
     * @param \Reflector $reflection
     * @return string
     */
    protected function parseDocCommentSummary($reflection)
    {
        $docLines = preg_split('~\R~', $reflection->getDocComment());
        if (isset($docLines[1])) {
            return trim($docLines[1], ' *');
        }
        return '';
    }

    /**
     * Returns full description from the docblock.
     *
     * @param \Reflector $reflection
     * @return string
     */
    protected function parseDocCommentDetail($reflection)
    {
        $comment = strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($reflection->getDocComment(), '/'))), "\r", '');
        if (preg_match('/^\s*@\w+/m', $comment, $matches, PREG_OFFSET_CAPTURE)) {
            $comment = trim(substr($comment, 0, $matches[0][1]));
        }
        if ($comment !== '') {
            return rtrim(Console::renderColoredString(Console::markdownToAnsi($comment)));
        }
        return '';
    }
}
