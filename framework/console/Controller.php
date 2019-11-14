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
use yii\helpers\Inflector;

/**
 * Controller 是控制台命令类的基类。
 *
 * 控制台控制器由一个或多个称为子命令的动作组成。
 * 用户通过指定标识控制器动作的相应路由来调用控制台命令。
 * 调用控制台命令时使用 `yii` 程序，如下所示：
 *
 * ```
 * yii <route> [--param1=value1 --param2 ...]
 * ```
 *
 * 其中 `<route>` 是指向控制器动作的路径，参数将作为命令的属性填充。
 * 有关详细信息看 [[options()]]。
 *
 * @property string $help 此属性是只读的。
 * @property string $helpSummary 此属性是只读的。
 * @property array $passedOptionValues 与传递的选项对应的属性。
 * 此属性是只读的。
 * @property array $passedOptions 执行期间传递的选项的名称。
 * 此属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
    /**
     * @deprecated 自 2.0.13。使用 [[ExitCode::OK]] 替代。
     */
    const EXIT_CODE_NORMAL = 0;
    /**
     * @deprecated 自 2.0.13。使用 [[ExitCode::UNSPECIFIED_ERROR]] 替代。
     */
    const EXIT_CODE_ERROR = 1;

    /**
     * @var bool 是否以交互方式运行命令。
     */
    public $interactive = true;
    /**
     * @var bool 是否在输出中启用 ANSI 颜色。
     * 如果未设置，则仅为支持 ANSI 颜色的终端启用 ANSI 颜色。
     */
    public $color;
    /**
     * @var bool 是否显示有关当前命令的帮助信息。
     * @since 2.0.10
     */
    public $help;

    /**
     * @var array 执行期间传递的选项。
     */
    private $_passedOptions = [];


    /**
     * 返回一个指示是否启用 ANSI 颜色的值。
     *
     * 仅当 [[color]] 设置为 true 或者没有设置并且终端支持 ANSI 颜色时
     * 才启用 ANSI 颜色
     *
     * @param resource $stream the stream to check.
     * @return bool Whether to enable ANSI style in output.
     */
    public function isColorEnabled($stream = \STDOUT)
    {
        return $this->color === null ? Console::streamSupportsAnsiColors($stream) : $this->color;
    }

    /**
     * 使用指定的动作 ID 和参数运行动作。
     * 如果动作 ID 为空，则该方法将使用 [[defaultAction]]。
     * @param string $id 要执行的动作的 ID。
     * @param array $params 要传递给动作的参数(名称-值 对)。
     * @return int 动作执行的状态。0 表示正常，其他值表示异常。
     * @throws InvalidRouteException 如果请求的动作 ID 无法成功解析为动作。
     * @throws Exception 如果存在未知选项或缺少参数
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
                        throw new Exception(Yii::t('yii', 'Unknown alias: -{name}', ['name' => $name]));
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
                    if (is_array($default)) {
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
                    throw new Exception(Yii::t('yii', 'Unknown option: --{name}', ['name' => $name]));
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
     * 将参数绑定到动作。
     * 当 [[Action]] 开始使用给定的参数运行时，此方法被调用。
     * 此方法首先将参数与操作可用的 [[options()|options]]绑定。
     * 然后验证给定的参数。
     * @param Action $action 要用参数绑定的动作
     * @param array $params 要绑定到动作的参数
     * @return array 可以运行动作的有效参数。
     * @throws Exception 如果存在未知选项或缺少参数
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
                $args[$i] = $args[$i] === '' ? [] : preg_split('/\s*,\s*/', $args[$i]);
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
     * 使用 ANSI 代码格式化字符串。
     *
     * 你可以使用 [[\yii\helpers\Console]] 中定义的常量传递其他参数。
     *
     * 例如：
     *
     * ```
     * echo $this->ansiFormat('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ```
     *
     * @param string $string 要格式化的字符串
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
     * 将字符串打印到 STDOUT。
     *
     * 你可以选择使用 ANSI 代码格式化字符串，通过
     * 使用 [[\yii\helpers\Console]] 中定义的常量传递其他参数。
     *
     * 例如：
     *
     * ```
     * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ```
     *
     * @param string $string 要打印的字符串
     * @return int|bool 打印的字节数或 false 在错误时
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
     * 将字符串打印到 STDERR。
     *
     * 你可以选择使用 ANSI 代码格式化字符串，
     * 通过使用 [[\yii\helpers\Console]] 中定义的常量传递其他参数。
     *
     * 例如：
     *
     * ```
     * $this->stderr('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ```
     *
     * @param string $string 要打印的字符串
     * @return int|bool 打印的字节数或 false 在错误时
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
     * 提示用户输入并验证它。
     *
     * @param string $text 提示字符串
     * @param array $options 验证输入的选项：
     *
     *  - required：是否需要
     *  - default：如果用户未插入输入，则为默认值
     *  - pattern：用于验证用户输入的正则表达式模式
     *  - validator：用于验证输入的可调用函数。该函数必须接受两个参数：
     *      - $input：用于验证的用户输入
     *      - $error：如果验证失败，则通过引用传递的错误值。
     *
     * 如何将提示方法与验证器函数一起使用的示例。
     *
     * ```php
     * $code = $this->prompt('Enter 4-Chars-Pin', ['required' => true, 'validator' => function($input, &$error) {
     *     if (strlen($input) !== 4) {
     *         $error = 'The Pin must be exactly 4 chars!';
     *         return false;
     *     }
     *     return true;
     * }]);
     * ```
     *
     * @return string 用户输入
     */
    public function prompt($text, $options = [])
    {
        if ($this->interactive) {
            return Console::prompt($text, $options);
        }

        return isset($options['default']) ? $options['default'] : '';
    }

    /**
     * 要求用户通过键入 y 或 n 来确认。
     *
     * 典型用法如下所示：
     *
     * ```php
     * if ($this->confirm("Are you sure?")) {
     *     echo "user typed yes\n";
     * } else {
     *     echo "user typed no\n";
     * }
     * ```
     *
     * @param string $message 在等待用户输入之前回显
     * @param bool $default 如果未进行选择，则返回此值。
     * @return bool 用户是否确认。
     * 如果 [[interactive]] 是 false 则返回 true。
     */
    public function confirm($message, $default = false)
    {
        if ($this->interactive) {
            return Console::confirm($message, $default);
        }

        return true;
    }

    /**
     * 为用户提供可供选择的选项。给予 '?' 作为输入将显示
     * 可供选择的选项列表及其解释。
     *
     * @param string $prompt 提示消息
     * @param array $options 可供选择的选项的键值数组
     *
     * @return string 用户选择的选项字符
     */
    public function select($prompt, $options = [])
    {
        return Console::select($prompt, $options);
    }

    /**
     * 返回操作的有效选项的动作（id）
     * 选项要求存在一个名为选项名的
     * 公共成员变量。
     * 子类可以重写此方法以指定可能的选项。
     *
     * 请注意，通过选项设置的值不可用
     * 直到调用 [[beforeAction()]]。
     *
     * @param string $actionID 当前请求的动作 id
     * @return string[] 对操作有效的选项名称
     */
    public function options($actionID)
    {
        // $actionId might be used in subclasses to provide options specific to action id
        return ['color', 'interactive', 'help'];
    }

    /**
     * 返回选项别名。
     * 子类可以重写此方法以指定别名选项。
     *
     * @return array 选项别名对操作有效，
     * 其中键是选项的别名，值是选项名。
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
     * 返回与动作 ID 的选项对应的属性
     * 子类可以重写此方法以指定可能的属性。
     *
     * @param string $actionID 当前请求的动作 id
     * @return array 属性对应于动作的选项
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
     * 返回执行期间传递的有效选项的名称。
     *
     * @return array 执行期间传递的选项的名称
     */
    public function getPassedOptions()
    {
        return $this->_passedOptions;
    }

    /**
     * 返回与传递的选项对应的属性。
     *
     * @return array 与传递的选项对应的属性
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
     * 返回描述此控制器的一行简短摘要。
     *
     * 您可以重写此方法以返回自定义摘要。
     * 默认实现返回 PHPDoc 注释的第一行。
     *
     * @return string
     */
    public function getHelpSummary()
    {
        return $this->parseDocCommentSummary(new \ReflectionClass($this));
    }

    /**
     * 返回此控制器的帮助信息。
     *
     * 您可以重写此方法以返回自定义帮助。
     * 默认实现返回从 PHPDoc 注释中检索的帮助信息。
     * @return string
     */
    public function getHelp()
    {
        return $this->parseDocCommentDetail(new \ReflectionClass($this));
    }

    /**
     * 返回描述指定动作的一行简短摘要。
     * @param Action $action 获取摘要的动作
     * @return string 描述指定动作的一行简短摘要。
     */
    public function getActionHelpSummary($action)
    {
        return $this->parseDocCommentSummary($this->getActionMethodReflection($action));
    }

    /**
     * 返回指定动作的详细帮助信息。
     * @param Action $action 获取帮助的动作
     * @return string 指定动作的详细帮助信息。
     */
    public function getActionHelp($action)
    {
        return $this->parseDocCommentDetail($this->getActionMethodReflection($action));
    }

    /**
     * 返回动作的匿名参数的帮助信息。
     *
     * 返回的值应该是一个数组。键是参数名称，值是
     * 相应的帮助信息。每个值必须是以下结构的数组：
     *
     * - required: boolean，是否需要此参数。
     * - type: string，此参数的 PHP 类型。
     * - default: string，此参数的默认值
     * - comment: string，这个参数的注释
     *
     * 默认实现将返回从与动作方法对应的参数的文档注释中
     * 提取的帮助信息。
     *
     * @param Action $action
     * @return array 动作参数的帮助信息
     */
    public function getActionArgsHelp($action)
    {
        $method = $this->getActionMethodReflection($action);
        $tags = $this->parseDocCommentTags($method);
        $params = isset($tags['param']) ? (array) $tags['param'] : [];

        $args = [];

        /** @var \ReflectionParameter $reflection */
        foreach ($method->getParameters() as $i => $reflection) {
            if ($reflection->getClass() !== null) {
                continue;
            }
            $name = $reflection->getName();
            $tag = isset($params[$i]) ? $params[$i] : '';
            if (preg_match('/^(\S+)\s+(\$\w+\s+)?(.*)/s', $tag, $matches)) {
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
     * 返回动作选项的帮助信息。
     *
     * 返回的值应该是一个数组。键是选项名称，值是
     * 相应的帮助信息。每个值必须是以下结构的数组：
     *
     * - type: string，此参数的 PHP 类型。
     * - default: string，此参数的默认值
     * - comment: string，这个参数的注释
     *
     * 默认实现将返回从与动作方法对应的属性的文档注释中
     * 提取的帮助信息。
     *
     * @param Action $action
     * @return array 动作选项的帮助信息
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
     * 将注释块解析为标记。
     * @param \Reflector $reflection 注释块
     * @return array 解析的标记
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
     * 返回 docblock 的第一行。
     *
     * @param \Reflector $reflection
     * @return string
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
     * 返回 docblock 的完整描述。
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
