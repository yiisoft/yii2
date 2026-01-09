<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\Action;
use yii\base\InlineAction;
use yii\base\InvalidRouteException;
use yii\helpers\Console;
use yii\helpers\Inflector;
use yii\base\Controller as BaseController;
use yii\base\Module;

/**
 * Controller is the base class of console command classes.
 *
 * A console controller consists of one or several actions known as sub-commands.
 * Users call a console command by specifying the corresponding route which identifies a controller action.
 * The `yii` program is used when calling a console command, like the following:
 *
 * ```
 * yii <route> [--param1=value1 --param2 ...]
 * ```
 *
 * where `<route>` is a route to a controller action and the params will be populated as properties of a command.
 * See [[options()]] for details.
 *
 * @property Request $request The request object.
 * @property Response $response The response object.
 * @property-read string $help The help information for this controller.
 * @property-read string $helpSummary The one-line short summary describing this controller.
 * @property-read array $passedOptionValues The properties corresponding to the passed options.
 * @property-read array $passedOptions The names of the options passed during execution.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @template T of Module
 * @extends BaseController<T>
 */
class Controller extends BaseController
{
    /**
     * @deprecated since 2.0.13. Use [[ExitCode::OK]] instead.
     */
    public const EXIT_CODE_NORMAL = 0;
    /**
     * @deprecated since 2.0.13. Use [[ExitCode::UNSPECIFIED_ERROR]] instead.
     */
    public const EXIT_CODE_ERROR = 1;
    /**
     * @var bool whether to run the command interactively.
     */
    public $interactive = true;
    /**
     * @var bool|null whether to enable ANSI color in the output.
     * If not set, ANSI color will only be enabled for terminals that support it.
     */
    public $color;
    /**
     * @var bool whether to display help information about current command.
     * @since 2.0.10
     */
    public $help = false;
    /**
     * @var bool|null if true - script finish with `ExitCode::OK` in case of exception.
     * false - `ExitCode::UNSPECIFIED_ERROR`.
     * Default: `YII_ENV_TEST`
     * @since 2.0.36
     */
    public $silentExitOnException;

    /**
     * @var array the options passed during execution.
     */
    private $_passedOptions = [];


    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $silentExit = $this->silentExitOnException !== null ? $this->silentExitOnException : YII_ENV_TEST;
        Yii::$app->errorHandler->silentExitOnException = $silentExit;

        return parent::beforeAction($action);
    }

    /**
     * Returns a value indicating whether ANSI color is enabled.
     *
     * ANSI color is enabled only if [[color]] is set true or is not set
     * and the terminal supports ANSI color.
     *
     * @param resource $stream the stream to check.
     * @return bool Whether to enable ANSI style in output.
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
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
     * @throws Exception if there are unknown options or missing arguments
     * @see createAction
     */
    public function runAction($id, $params = [])
    {
        if (!empty($params)) {
            // populate options here so that they are available in beforeAction().
            $options = $this->options($id === '' ? $this->defaultAction : $id);
            if (isset($params['_aliases'])) {
                $optionAliases = $this->optionAliases();
                foreach ($params['_aliases'] as $name => $value) {
                    if (array_key_exists($name, $optionAliases)) {
                        $params[$optionAliases[$name]] = $value;
                    } else {
                        $message = Yii::t('yii', 'Unknown alias: -{name}', ['name' => $name]);
                        if (!empty($optionAliases)) {
                            $aliasesAvailable = [];
                            foreach ($optionAliases as $alias => $option) {
                                $aliasesAvailable[] = '-' . $alias . ' (--' . $option . ')';
                            }

                            $message .= '. ' . Yii::t('yii', 'Aliases available: {aliases}', [
                                'aliases' => implode(', ', $aliasesAvailable)
                            ]);
                        }
                        throw new Exception($message);
                    }
                }
                unset($params['_aliases']);
            }
            foreach ($params as $name => $value) {
                // Allow camelCase options to be entered in kebab-case
                if (!in_array($name, $options, true) && strpos($name, '-') !== false) {
                    $kebabName = $name;
                    $altName = lcfirst(Inflector::id2camel($kebabName));
                    if (in_array($altName, $options, true)) {
                        $name = $altName;
                    }
                }

                if (in_array($name, $options, true)) {
                    $default = $this->$name;
                    if (is_array($default) && is_string($value)) {
                        $this->$name = preg_split('/\s*,\s*(?![^()]*\))/', $value);
                    } elseif ($default !== null) {
                        settype($value, gettype($default));
                        $this->$name = $value;
                    } else {
                        $this->$name = $value;
                    }
                    $this->_passedOptions[] = $name;
                    unset($params[$name]);
                    if (isset($kebabName)) {
                        unset($params[$kebabName]);
                    }
                } elseif (!is_int($name)) {
                    $message = Yii::t('yii', 'Unknown option: --{name}', ['name' => $name]);
                    if (!empty($options)) {
                        $message .= '. ' . Yii::t('yii', 'Options available: {options}', ['options' => '--' . implode(', --', $options)]);
                    }

                    throw new Exception($message);
                }
            }
        }
        if ($this->help) {
            $route = $this->getUniqueId() . '/' . $id;
            return Yii::$app->runAction('help', [$route]);
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
     *
     * @phpstan-param Action<$this> $action
     * @psalm-param Action<$this> $action
     *
     * @phpstan-param array<array-key, mixed> $params
     * @psalm-param array<array-key, mixed> $params
     *
     * @phpstan-return mixed[]
     * @psalm-return mixed[]
     */
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $paramKeys = array_keys($params);
        $args = [];
        $missing = [];
        $actionParams = [];
        $requestedParams = [];
        foreach ($method->getParameters() as $i => $param) {
            $name = $param->getName();
            $key = null;
            if (array_key_exists($i, $params)) {
                $key = $i;
            } elseif (array_key_exists($name, $params)) {
                $key = $name;
            }

            if ($key !== null) {
                if ($param->isVariadic()) {
                    for ($j = array_search($key, $paramKeys); $j < count($paramKeys); $j++) {
                        $jKey = $paramKeys[$j];
                        if ($jKey !== $key && !is_int($jKey)) {
                            break;
                        }
                        $args[] = $actionParams[$key][] = $params[$jKey];
                        unset($params[$jKey]);
                    }
                } else {
                    if (PHP_VERSION_ID >= 80000) {
                        $isArray = ($type = $param->getType()) instanceof \ReflectionNamedType && $type->getName() === 'array';
                    } else {
                        $isArray = $param->isArray();
                    }
                    if ($isArray) {
                        $params[$key] = $params[$key] === '' ? [] : preg_split('/\s*,\s*/', $params[$key]);
                    }
                    $args[] = $actionParams[$key] = $params[$key];
                    unset($params[$key]);
                }
            } elseif (
                PHP_VERSION_ID >= 70100
                && ($type = $param->getType()) !== null
                && $type instanceof \ReflectionNamedType
                && !$type->isBuiltin()
            ) {
                try {
                    $this->bindInjectedParams($type, $name, $args, $requestedParams);
                } catch (\yii\base\Exception $e) {
                    throw new Exception($e->getMessage());
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$i] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new Exception(Yii::t('yii', 'Missing required arguments: {params}', ['params' => implode(', ', $missing)]));
        }

        // We use a different array here, specifically one that doesn't contain service instances but descriptions instead.
        if (\Yii::$app->requestedParams === null) {
            \Yii::$app->requestedParams = array_merge($actionParams, $requestedParams);
        }

        return array_merge($args, $params);
    }

    /**
     * Formats a string with ANSI codes.
     *
     * You may pass additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ```
     * echo $this->ansiFormat('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ```
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
     * Prints a string to STDOUT.
     *
     * You may optionally format the string with ANSI codes by
     * passing additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ```
     * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ```
     *
     * @param string $string the string to print
     * @param int ...$args additional parameters to decorate the output
     * @return int|bool Number of bytes printed or false on error
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
     * Prints a string to STDERR.
     *
     * You may optionally format the string with ANSI codes by
     * passing additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ```
     * $this->stderr('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ```
     *
     * @param string $string the string to print
     * @param int ...$args additional parameters to decorate the output
     * @return int|bool Number of bytes printed or false on error
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
     * Prompts the user for input and validates it.
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
     *
     * An example of how to use the prompt method with a validator function.
     *
     * ```
     * $code = $this->prompt('Enter 4-Chars-Pin', ['required' => true, 'validator' => function($input, &$error) {
     *     if (strlen($input) !== 4) {
     *         $error = 'The Pin must be exactly 4 chars!';
     *         return false;
     *     }
     *     return true;
     * }]);
     * ```
     *
     * @return string the user input
     */
    public function prompt($text, $options = [])
    {
        if ($this->interactive) {
            return Console::prompt($text, $options);
        }

        return isset($options['default']) ? $options['default'] : '';
    }

    /**
     * Asks user to confirm by typing y or n.
     *
     * A typical usage looks like the following:
     *
     * ```
     * if ($this->confirm("Are you sure?")) {
     *     echo "user typed yes\n";
     * } else {
     *     echo "user typed no\n";
     * }
     * ```
     *
     * @param string $message to echo out before waiting for user input
     * @param bool $default this value is returned if no selection is made.
     * @return bool whether user confirmed.
     * Will return true if [[interactive]] is false.
     */
    public function confirm($message, $default = false)
    {
        if ($this->interactive) {
            return Console::confirm($message, $default);
        }

        return true;
    }

    /**
     * Gives the user an option to choose from. Giving '?' as an input will show
     * a list of options to choose from and their explanations.
     *
     * @param string $prompt the prompt message
     * @param array $options Key-value array of options to choose from
     * @param string|null $default value to use when the user doesn't provide an option.
     * If the default is `null`, the user is required to select an option.
     *
     * @return string An option character the user chose
     * @since 2.0.49 Added the $default argument
     */
    public function select($prompt, $options = [], $default = null)
    {
        if ($this->interactive) {
            return Console::select($prompt, $options, $default);
        }

        return $default;
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
     * @return string[] the names of the options valid for the action
     */
    public function options($actionID)
    {
        // $actionId might be used in subclasses to provide options specific to action id
        return ['color', 'interactive', 'help', 'silentExitOnException'];
    }

    /**
     * Returns option alias names.
     * Child classes may override this method to specify alias options.
     *
     * @return array the options alias names valid for the action
     * where the keys is alias name for option and value is option name.
     *
     * @since 2.0.8
     * @see options()
     */
    public function optionAliases()
    {
        return [
            'h' => 'help',
        ];
    }

    /**
     * Returns properties corresponding to the options for the action id
     * Child classes may override this method to specify possible properties.
     *
     * @param string $actionID the action id of the current request
     * @return array properties corresponding to the options for the action
     */
    public function getOptionValues($actionID)
    {
        // $actionId might be used in subclasses to provide properties specific to action id
        $properties = [];
        foreach ($this->options($this->action->id) as $property) {
            $properties[$property] = $this->$property;
        }

        return $properties;
    }

    /**
     * Returns the names of valid options passed during execution.
     *
     * @return array the names of the options passed during execution
     */
    public function getPassedOptions()
    {
        return $this->_passedOptions;
    }

    /**
     * Returns the properties corresponding to the passed options.
     *
     * @return array the properties corresponding to the passed options
     */
    public function getPassedOptionValues()
    {
        $properties = [];
        foreach ($this->_passedOptions as $property) {
            $properties[$property] = $this->$property;
        }

        return $properties;
    }

    /**
     * Returns one-line short summary describing this controller.
     *
     * You may override this method to return customized summary.
     * The default implementation returns first line from the PHPDoc comment.
     *
     * @return string the one-line short summary describing this controller.
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
     * @return string the help information for this controller.
     */
    public function getHelp()
    {
        return $this->parseDocCommentDetail(new \ReflectionClass($this));
    }

    /**
     * Returns a one-line short summary describing the specified action.
     * @param Action $action action to get summary for
     * @return string a one-line short summary describing the specified action.
     *
     * @phpstan-param Action<$this> $action
     * @psalm-param Action<$this> $action
     */
    public function getActionHelpSummary($action)
    {
        if ($action === null) {
            return $this->ansiFormat(Yii::t('yii', 'Action not found.'), Console::FG_RED);
        }

        return $this->parseDocCommentSummary($this->getActionMethodReflection($action));
    }

    /**
     * Returns the detailed help information for the specified action.
     * @param Action $action action to get help for
     * @return string the detailed help information for the specified action.
     *
     * @phpstan-param Action<$this> $action
     * @psalm-param Action<$this> $action
     */
    public function getActionHelp($action)
    {
        return $this->parseDocCommentDetail($this->getActionMethodReflection($action));
    }

    /**
     * Returns the help information for the anonymous arguments for the action.
     *
     * The returned value should be an array. The keys are the argument names, and the values are
     * the corresponding help information. Each value must be an array of the following structure:
     *
     * - required: bool, whether this argument is required
     * - type: string|null, the PHP type(s) of this argument
     * - default: mixed, the default value of this argument
     * - comment: string, the description of this argument
     *
     * The default implementation will return the help information extracted from the Reflection or
     * DocBlock of the parameters corresponding to the action method.
     *
     * @param Action $action the action instance
     * @return array the help information of the action arguments
     *
     * @phpstan-param Action<$this> $action
     * @psalm-param Action<$this> $action
     */
    public function getActionArgsHelp($action)
    {
        $method = $this->getActionMethodReflection($action);

        $tags = $this->parseDocCommentTags($method);
        $tags['param'] = isset($tags['param']) ? (array) $tags['param'] : [];
        $phpDocParams = [];
        foreach ($tags['param'] as $i => $tag) {
            if (preg_match('/^(?<type>\S+)(\s+\$(?<name>\w+))?(?<comment>.*)/us', $tag, $matches) === 1) {
                $key = empty($matches['name']) ? $i : $matches['name'];
                $phpDocParams[$key] = ['type' => $matches['type'], 'comment' => $matches['comment']];
            }
        }
        unset($tags);

        $args = [];

        /** @var \ReflectionParameter $parameter */
        foreach ($method->getParameters() as $i => $parameter) {
            $type = null;
            $comment = '';
            if (PHP_MAJOR_VERSION > 5 && $parameter->hasType()) {
                $reflectionType = $parameter->getType();
                if (PHP_VERSION_ID >= 70100) {
                    $types = method_exists($reflectionType, 'getTypes') ? $reflectionType->getTypes() : [$reflectionType];
                    foreach ($types as $key => $reflectionType) {
                        $types[$key] = $reflectionType->getName();
                    }
                    $type = implode('|', $types);
                } else {
                    $type = (string) $reflectionType;
                }
            }
            // find PhpDoc tag by property name or position
            $key = isset($phpDocParams[$parameter->name]) ? $parameter->name : (isset($phpDocParams[$i]) ? $i : null);
            if ($key !== null) {
                $comment = $phpDocParams[$key]['comment'];
                if ($type === null && !empty($phpDocParams[$key]['type'])) {
                    $type = $phpDocParams[$key]['type'];
                }
            }
            // if type still not detected, then using type of default value
            if ($type === null && $parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() !== null) {
                $type = gettype($parameter->getDefaultValue());
            }

            $args[$parameter->name] = [
                'required' => !$parameter->isOptional(),
                'type' => $type,
                'default' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                'comment' => $comment,
            ];
        }

        return $args;
    }

    /**
     * Returns the help information for the options for the action.
     *
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
     *
     * @phpstan-param Action<$this> $action
     * @psalm-param Action<$this> $action
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

            // Display camelCase options in kebab-case
            $name = Inflector::camel2id($name, '-', true);

            if (isset($tags['var']) || isset($tags['property'])) {
                $doc = isset($tags['var']) ? $tags['var'] : $tags['property'];
                if (is_array($doc)) {
                    $doc = reset($doc);
                }
                if (preg_match('/^(\S+)(.*)/s', $doc, $matches)) {
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
     * @return \ReflectionFunctionAbstract
     *
     * @phpstan-param Action<$this> $action
     * @psalm-param Action<$this> $action
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
     * @param \ReflectionClass|\ReflectionProperty|\ReflectionFunctionAbstract $reflection the comment block
     * @return array the parsed tags
     *
     * @phpstan-param \ReflectionClass<object>|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     * @psalm-param \ReflectionClass<object>|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     */
    protected function parseDocCommentTags($reflection)
    {
        $comment = $reflection->getDocComment();
        $comment = "@description \n" . strtr(trim(preg_replace('/^\s*\**([ \t])?/m', '', trim($comment, '/'))), "\r", '');
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
     * @param \ReflectionClass|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     * @return string
     *
     * @phpstan-param \ReflectionClass<$this>|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     * @psalm-param \ReflectionClass<$this>|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     */
    protected function parseDocCommentSummary($reflection)
    {
        $docLines = preg_split('~\R~u', $reflection->getDocComment());
        if (isset($docLines[1])) {
            return trim($docLines[1], "\t *");
        }

        return '';
    }

    /**
     * Returns full description from the docblock.
     *
     * @param \ReflectionClass|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     * @return string
     *
     * @phpstan-param \ReflectionClass<$this>|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     * @psalm-param \ReflectionClass<$this>|\ReflectionProperty|\ReflectionFunctionAbstract $reflection
     */
    protected function parseDocCommentDetail($reflection)
    {
        $comment = strtr(trim(preg_replace('/^\s*\**([ \t])?/m', '', trim($reflection->getDocComment(), '/'))), "\r", '');
        if (preg_match('/^\s*@\w+/m', $comment, $matches, PREG_OFFSET_CAPTURE)) {
            $comment = trim(substr($comment, 0, $matches[0][1]));
        }
        if ($comment !== '') {
            return rtrim(Console::renderColoredString(Console::markdownToAnsi($comment)));
        }

        return '';
    }
}
