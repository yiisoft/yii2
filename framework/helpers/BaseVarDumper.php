<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\Arrayable;
use yii\base\InvalidValueException;

/**
 * BaseVarDumper 为 [[VarDumper]] 提供了具体的实现。
 *
 * 不要使用 BaseVarDumper。使用 [[VarDumper]] 来替代。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseVarDumper
{
    private static $_objects;
    private static $_output;
    private static $_depth;


    /**
     * 显示一个变量。
     * 这个方法达到了类似 var_dump 和 print_r 的功能，
     * 但是处理 Yii 控制器等复杂对象时更健壮。
     * @param mixed $var 待转储变量
     * @param int $depth 深层次输出这个变量的最大的深度。默认是 10。
     * @param bool $highlight 结果是否应该突出高亮语法
     */
    public static function dump($var, $depth = 10, $highlight = false)
    {
        echo static::dumpAsString($var, $depth, $highlight);
    }

    /**
     * 以字符串的形式转储变量。
     * 这个方法达到了类似 var_dump 和 print_r 的功能，
     * 但是处理 Yii 控制器等复杂对象时更健壮。
     * @param mixed $var 待转储变量
     * @param int $depth 深层次输出这个变量的最大的深度。默认是 10。
     * @param bool $highlight 结果是否应该突出高亮语法
     * @return string 变量的字符串表示形式
     */
    public static function dumpAsString($var, $depth = 10, $highlight = false)
    {
        self::$_output = '';
        self::$_objects = [];
        self::$_depth = $depth;
        self::dumpInternal($var, 0);
        if ($highlight) {
            $result = highlight_string("<?php\n" . self::$_output, true);
            self::$_output = preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
        }

        return self::$_output;
    }

    /**
     * @param mixed $var 待转储变量
     * @param int $level 转储的深度级别
     */
    private static function dumpInternal($var, $level)
    {
        switch (gettype($var)) {
            case 'boolean':
                self::$_output .= $var ? 'true' : 'false';
                break;
            case 'integer':
                self::$_output .= (string)$var;
                break;
            case 'double':
                self::$_output .= (string)$var;
                break;
            case 'string':
                self::$_output .= "'" . addslashes($var) . "'";
                break;
            case 'resource':
                self::$_output .= '{resource}';
                break;
            case 'NULL':
                self::$_output .= 'null';
                break;
            case 'unknown type':
                self::$_output .= '{unknown}';
                break;
            case 'array':
                if (self::$_depth <= $level) {
                    self::$_output .= '[...]';
                } elseif (empty($var)) {
                    self::$_output .= '[]';
                } else {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= '[';
                    foreach ($keys as $key) {
                        self::$_output .= "\n" . $spaces . '    ';
                        self::dumpInternal($key, 0);
                        self::$_output .= ' => ';
                        self::dumpInternal($var[$key], $level + 1);
                    }
                    self::$_output .= "\n" . $spaces . ']';
                }
                break;
            case 'object':
                if (($id = array_search($var, self::$_objects, true)) !== false) {
                    self::$_output .= get_class($var) . '#' . ($id + 1) . '(...)';
                } elseif (self::$_depth <= $level) {
                    self::$_output .= get_class($var) . '(...)';
                } else {
                    $id = array_push(self::$_objects, $var);
                    $className = get_class($var);
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= "$className#$id\n" . $spaces . '(';
                    if ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__debugInfo')) {
                        $dumpValues = $var->__debugInfo();
                        if (!is_array($dumpValues)) {
                            throw new InvalidValueException('__debuginfo() must return an array');
                        }
                    } else {
                        $dumpValues = (array) $var;
                    }
                    foreach ($dumpValues as $key => $value) {
                        $keyDisplay = strtr(trim($key), "\0", ':');
                        self::$_output .= "\n" . $spaces . "    [$keyDisplay] => ";
                        self::dumpInternal($value, $level + 1);
                    }
                    self::$_output .= "\n" . $spaces . ')';
                }
                break;
        }
    }

    /**
     * 将变量导出为字符串表示形式。
     *
     * 这个字符串是一个有效的可以由 PHP 解析器计算的表达式
     * 并且将计算结果返回变量值。
     *
     * 该方法类似于 `var_export()`。
     * 主要的区别在于它使用短数组语法生成更紧凑的字符串表示。
     *
     * 它还使用 PHP 函数 serialize() 和 unserialize() 来处理对象。
     *
     * PHP 5.4 或以上版本是解析输出值所必需的。
     *
     * @param mixed $var 要输出的变量。
     * @return string 变量的字符串表示形式
     */
    public static function export($var)
    {
        self::$_output = '';
        self::exportInternal($var, 0);
        return self::$_output;
    }

    /**
     * @param mixed $var 输出的变量
     * @param int $level 深度级别
     */
    private static function exportInternal($var, $level)
    {
        switch (gettype($var)) {
            case 'NULL':
                self::$_output .= 'null';
                break;
            case 'array':
                if (empty($var)) {
                    self::$_output .= '[]';
                } else {
                    $keys = array_keys($var);
                    $outputKeys = ($keys !== range(0, count($var) - 1));
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= '[';
                    foreach ($keys as $key) {
                        self::$_output .= "\n" . $spaces . '    ';
                        if ($outputKeys) {
                            self::exportInternal($key, 0);
                            self::$_output .= ' => ';
                        }
                        self::exportInternal($var[$key], $level + 1);
                        self::$_output .= ',';
                    }
                    self::$_output .= "\n" . $spaces . ']';
                }
                break;
            case 'object':
                if ($var instanceof \Closure) {
                    self::$_output .= self::exportClosure($var);
                } else {
                    try {
                        $output = 'unserialize(' . var_export(serialize($var), true) . ')';
                    } catch (\Exception $e) {
                        // serialize may fail, for example: if object contains a `\Closure` instance
                        // so we use a fallback
                        if ($var instanceof Arrayable) {
                            self::exportInternal($var->toArray(), $level);
                            return;
                        } elseif ($var instanceof \IteratorAggregate) {
                            $varAsArray = [];
                            foreach ($var as $key => $value) {
                                $varAsArray[$key] = $value;
                            }
                            self::exportInternal($varAsArray, $level);
                            return;
                        } elseif ('__PHP_Incomplete_Class' !== get_class($var) && method_exists($var, '__toString')) {
                            $output = var_export($var->__toString(), true);
                        } else {
                            $outputBackup = self::$_output;
                            $output = var_export(self::dumpAsString($var), true);
                            self::$_output = $outputBackup;
                        }
                    }
                    self::$_output .= $output;
                }
                break;
            default:
                self::$_output .= var_export($var, true);
        }
    }

    /**
     * 导出一个 [[Closure]] 实例。
     * @param \Closure $closure 闭包实例。
     * @return string
     */
    private static function exportClosure(\Closure $closure)
    {
        $reflection = new \ReflectionFunction($closure);

        $fileName = $reflection->getFileName();
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();

        if ($fileName === false || $start === false || $end === false) {
            return 'function() {/* Error: unable to determine Closure source */}';
        }

        --$start;

        $source = implode("\n", array_slice(file($fileName), $start, $end - $start));
        $tokens = token_get_all('<?php ' . $source);
        array_shift($tokens);

        $closureTokens = [];
        $pendingParenthesisCount = 0;
        foreach ($tokens as $token) {
            if (isset($token[0]) && $token[0] === T_FUNCTION) {
                $closureTokens[] = $token[1];
                continue;
            }
            if ($closureTokens !== []) {
                $closureTokens[] = isset($token[1]) ? $token[1] : $token;
                if ($token === '}') {
                    $pendingParenthesisCount--;
                    if ($pendingParenthesisCount === 0) {
                        break;
                    }
                } elseif ($token === '{') {
                    $pendingParenthesisCount++;
                }
            }
        }

        return implode('', $closureTokens);
    }
}
