<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\console\Markdown as ConsoleMarkdown;
use yii\base\Model;

/**
 * BaseConsole 为 [[Console]] 提供了具体的实现方法。
 *
 * 不要使用 BaseConsole 类。使用 [[Console]] 类代替。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class BaseConsole
{
    // foreground color control codes
    const FG_BLACK = 30;
    const FG_RED = 31;
    const FG_GREEN = 32;
    const FG_YELLOW = 33;
    const FG_BLUE = 34;
    const FG_PURPLE = 35;
    const FG_CYAN = 36;
    const FG_GREY = 37;
    // background color control codes
    const BG_BLACK = 40;
    const BG_RED = 41;
    const BG_GREEN = 42;
    const BG_YELLOW = 43;
    const BG_BLUE = 44;
    const BG_PURPLE = 45;
    const BG_CYAN = 46;
    const BG_GREY = 47;
    // fonts style control codes
    const RESET = 0;
    const NORMAL = 0;
    const BOLD = 1;
    const ITALIC = 3;
    const UNDERLINE = 4;
    const BLINK = 5;
    const NEGATIVE = 7;
    const CONCEALED = 8;
    const CROSSED_OUT = 9;
    const FRAMED = 51;
    const ENCIRCLED = 52;
    const OVERLINED = 53;


    /**
     * 通过向终端发送 ANSI 控制代码 CUU 将终端光标向上移动。
     * 如果光标已经在屏幕边缘，则不会有任何效果。
     * @param int $rows 光标应该向上移动的行数
     */
    public static function moveCursorUp($rows = 1)
    {
        echo "\033[" . (int) $rows . 'A';
    }

    /**
     * 通过向终端发送 ANSI 控制代码 CUD 将终端光标向下移动。
     * 如果光标已经在屏幕边缘，则不会有任何效果。
     * @param int $rows 光标应向下移动的行数
     */
    public static function moveCursorDown($rows = 1)
    {
        echo "\033[" . (int) $rows . 'B';
    }

    /**
     * 通过向终端发送 ANSI 控制代码 CUF，将终端光标向前移动。
     * 如果光标已经在屏幕边缘，则不会有任何效果。
     * @param int $steps 光标应向前移动的步数
     */
    public static function moveCursorForward($steps = 1)
    {
        echo "\033[" . (int) $steps . 'C';
    }

    /**
     * 通过向终端发送 ANSI 控制代码 CUB，将终端光标向后移动。
     * 如果光标已经在屏幕边缘，则不会有任何效果。
     * @param int $steps 光标应向后移动的步数
     */
    public static function moveCursorBackward($steps = 1)
    {
        echo "\033[" . (int) $steps . 'D';
    }

    /**
     * 通过向终端发送 ANSI 控制代码 CNL，将终端光标移动到下一行的开头。
     * @param int $lines 光标应向下移动的行数
     */
    public static function moveCursorNextLine($lines = 1)
    {
        echo "\033[" . (int) $lines . 'E';
    }

    /**
     * 通过向终端发送 ANSI 控制代码 CPL，将终端光标移动到前一行的开头。
     * @param int $lines 应该向上移动光标的行数
     */
    public static function moveCursorPrevLine($lines = 1)
    {
        echo "\033[" . (int) $lines . 'F';
    }

    /**
     * 通过发送 ANSI 控制码 CUP 或 CHA 到终端，将光标移动到列和行给定的绝对位置。
     * @param int $column 基于 1 的列号，1 是屏幕的左边缘。
     * @param int|null $row 基于 1 的行号，1 是屏幕的上边缘。如果没有设置，将光标移动到当前行。
     */
    public static function moveCursorTo($column, $row = null)
    {
        if ($row === null) {
            echo "\033[" . (int) $column . 'G';
        } else {
            echo "\033[" . (int) $row . ';' . (int) $column . 'H';
        }
    }

    /**
     * 通过向终端发送 ANSI 控制码 SU，将整个页面向上滚动。
     * 在底部添加了新行。ANSI 不支持这种方法。windows 中使用的 SYS。
     * @param int $lines 向上滚动的行数
     */
    public static function scrollUp($lines = 1)
    {
        echo "\033[" . (int) $lines . 'S';
    }

    /**
     * 通过向终端发送 ANSI 控制码 SD，向下滚动整个页面。
     * 在顶部添加新行。ANSI 不支持这种方法。windows 中使用的 SYS。
     * @param int $lines 向下滚动的行数
     */
    public static function scrollDown($lines = 1)
    {
        echo "\033[" . (int) $lines . 'T';
    }

    /**
     * 将 ANSI 控制代码 SCP 发送到终端，保存当前光标位置。
     * 可以使用 [[restoreCursorPosition()]] 恢复位置。
     */
    public static function saveCursorPosition()
    {
        echo "\033[s";
    }

    /**
     * 通过将 ANSI 控制代码 RCP 发送到终端，恢复 [[saveCursorPosition()]] 保存的光标位置。
     */
    public static function restoreCursorPosition()
    {
        echo "\033[u";
    }

    /**
     * 通过向终端发送 ANSI DECTCEM 代码 ?25l 来隐藏光标。
     * 使用 [[showCursor()]] 将它带回来。
     * 应用程序退出时不要忘记显示光标。退出后光标可能会隐藏在终端中。
     */
    public static function hideCursor()
    {
        echo "\033[?25l";
    }

    /**
     * 当光标被 [[hideCursor()]] 隐藏时，将通过发送 ANSI DECTCEM 代码 ?25h 再次显示光标。
     */
    public static function showCursor()
    {
        echo "\033[?25h";
    }

    /**
     * 通过发送参数带有 2 的 ANSI 控制代码 ED 到终端来清除整个屏幕内容。
     * 不会改变光标位置。
     * **Note:** ANSI.SYS 能够实现在 windows 中将坐标位置重置到屏幕左上角。
     */
    public static function clearScreen()
    {
        echo "\033[2J";
    }

    /**
     * 通过发送参数带有 1 的 ANSI 控制代码 ED 发送到终端，将文本从光标处清除到屏幕的开头。
     * 不会改变光标位置。
     */
    public static function clearScreenBeforeCursor()
    {
        echo "\033[1J";
    }

    /**
     * 通过发送参数带有 0 的 ANSI 控制代码 ED 发送到终端，将文本从光标清除到屏幕末端。
     * 不会改变光标位置。
     */
    public static function clearScreenAfterCursor()
    {
        echo "\033[0J";
    }

    /**
     * 清除行，光标当前是通过发送 ANSI 控制代码 EL 带有参数 2 到终端。
     * 不会改变光标位置。
     */
    public static function clearLine()
    {
        echo "\033[2K";
    }

    /**
     * 通过向终端发送带有变量 1 的 ANSI 控制码 EL，将文本从光标位置清除到行的开始位置。
     * 不会改变光标位置。
     */
    public static function clearLineBeforeCursor()
    {
        echo "\033[1K";
    }

    /**
     * 通过将参数为 0 的 ANSI 控制代码 EL 发送到终端，将文本从光标位置清除到行尾。
     * 不会改变光标位置。
     */
    public static function clearLineAfterCursor()
    {
        echo "\033[0K";
    }

    /**
     * 返回 ANSI 格式代码。
     *
     * @param array $format 包含格式化值的数组。
     * 您可以传递任何 `FG_*`，`BG_*` 和 `TEXT_*`
     * 常量，也可以通过 [[xtermFgColor]]] 和 [[xtermBgColor]] 来指定格式。
     * @return string ANSI 格式代码根据给定的格式化常量进行格式化。
     */
    public static function ansiFormatCode($format)
    {
        return "\033[" . implode(';', $format) . 'm';
    }

    /**
     * 输出一种 ANSI 格式代码，它影响以后打印的任何文本的格式。
     *
     * @param array $format 包含格式化值的数组。
     * 您可以传递任何 `FG_*`，`BG_*` 和 `TEXT_*`
     * 常量，也可以通过 [[xtermFgColor]]] 和 [[xtermBgColor]] 来指定格式。
     * @see ansiFormatCode()
     * @see endAnsiFormat()
     */
    public static function beginAnsiFormat($format)
    {
        echo "\033[" . implode(';', $format) . 'm';
    }

    /**
     * 重置之前方法 [[beginAnsiFormat()]] 设置的任何 ANSI 格式。
     * 在此之后的任何输出都将具有默认的文本格式。
     * 这等于调用。
     *
     * ```php
     * echo Console::ansiFormatCode([Console::RESET])
     * ```
     */
    public static function endAnsiFormat()
    {
        echo "\033[0m";
    }

    /**
     * 将返回一个使用给定 ANSI 样式格式化的字符串。
     *
     * @param string $string 要格式化的字符串
     * @param array $format 包含格式化值的数组。
     * 您可以传递任何 `FG_*`，`BG_*` 和 `TEXT_*`
     * 常量，也可以通过 [[xtermFgColor]]] 和 [[xtermBgColor]] 来指定格式。
     * @return string
     */
    public static function ansiFormat($string, $format = [])
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $string . "\033[0m";
    }

    /**
     * 返回 xterm 前景颜色的 ansi 格式代码。
     *
     * 您可以将这个函数的返回值传递给以下格式方法之一：
     * [[ansiFormat]]，[[ansiFormatCode]]，[[beginAnsiFormat]]。
     *
     * @param int $colorCode xterm 颜色代码
     * @return string
     * @see http://en.wikipedia.org/wiki/Talk:ANSI_escape_code#xterm-256colors
     */
    public static function xtermFgColor($colorCode)
    {
        return '38;5;' . $colorCode;
    }

    /**
     * 返回 xterm 背景颜色的 ansi 格式代码。
     *
     * 您可以将它的返回值传递给一个格式化方法：
     * [[ansiFormat]]，[[ansiFormatCode]]，[[beginAnsiFormat]]。
     *
     * @param int $colorCode xterm 颜色代码
     * @return string
     * @see http://en.wikipedia.org/wiki/Talk:ANSI_escape_code#xterm-256colors
     */
    public static function xtermBgColor($colorCode)
    {
        return '48;5;' . $colorCode;
    }

    /**
     * 从字符串中剥离 ANSI 控制代码
     *
     * @param string $string 匹配替换的字符串
     * @return string
     */
    public static function stripAnsiFormat($string)
    {
        return preg_replace('/\033\[[\d;?]*\w/', '', $string);
    }

    /**
     * 返回没有 ANSI 颜色代码的字符串长度。
     * @param string $string 计算长度的字符串
     * @return int 不包括 ANSI 格式字符的字符串长度
     */
    public static function ansiStrlen($string)
    {
        return mb_strlen(static::stripAnsiFormat($string));
    }

    /**
     * 将 ANSI 格式的字符串转换为 HTML。
     *
     * Note: 目前不支持 xTerm 256 位颜色。
     *
     * @param string $string 要转换的字符串。
     * @param array $styleMap ANSI 控件代码的可选映射，
     * 如 FG\_*COLOR* 或者 [[BOLD]] 去设置一组 CSS 样式的定义。
     * CSS 样式的定义被描述为一个数组,
     * 其中数组键对应 CSS 样式属性名称，且值为 CSS 值。
     * 当渲染的时候如果值是数组将使用 `' '` 进行拼接合并。
     * @return string ANSI 格式字符串的 HTML 表示
     */
    public static function ansiToHtml($string, $styleMap = [])
    {
        $styleMap = [
            // http://www.w3.org/TR/CSS2/syndata.html#value-def-color
            self::FG_BLACK => ['color' => 'black'],
            self::FG_BLUE => ['color' => 'blue'],
            self::FG_CYAN => ['color' => 'aqua'],
            self::FG_GREEN => ['color' => 'lime'],
            self::FG_GREY => ['color' => 'silver'],
            // http://meyerweb.com/eric/thoughts/2014/06/19/rebeccapurple/
            // http://dev.w3.org/csswg/css-color/#valuedef-rebeccapurple
            self::FG_PURPLE => ['color' => 'rebeccapurple'],
            self::FG_RED => ['color' => 'red'],
            self::FG_YELLOW => ['color' => 'yellow'],
            self::BG_BLACK => ['background-color' => 'black'],
            self::BG_BLUE => ['background-color' => 'blue'],
            self::BG_CYAN => ['background-color' => 'aqua'],
            self::BG_GREEN => ['background-color' => 'lime'],
            self::BG_GREY => ['background-color' => 'silver'],
            self::BG_PURPLE => ['background-color' => 'rebeccapurple'],
            self::BG_RED => ['background-color' => 'red'],
            self::BG_YELLOW => ['background-color' => 'yellow'],
            self::BOLD => ['font-weight' => 'bold'],
            self::ITALIC => ['font-style' => 'italic'],
            self::UNDERLINE => ['text-decoration' => ['underline']],
            self::OVERLINED => ['text-decoration' => ['overline']],
            self::CROSSED_OUT => ['text-decoration' => ['line-through']],
            self::BLINK => ['text-decoration' => ['blink']],
            self::CONCEALED => ['visibility' => 'hidden'],
        ] + $styleMap;

        $tags = 0;
        $result = preg_replace_callback(
            '/\033\[([\d;]+)m/',
            function ($ansi) use (&$tags, $styleMap) {
                $style = [];
                $reset = false;
                $negative = false;
                foreach (explode(';', $ansi[1]) as $controlCode) {
                    if ($controlCode == 0) {
                        $style = [];
                        $reset = true;
                    } elseif ($controlCode == self::NEGATIVE) {
                        $negative = true;
                    } elseif (isset($styleMap[$controlCode])) {
                        $style[] = $styleMap[$controlCode];
                    }
                }

                $return = '';
                while ($reset && $tags > 0) {
                    $return .= '</span>';
                    $tags--;
                }
                if (empty($style)) {
                    return $return;
                }

                $currentStyle = [];
                foreach ($style as $content) {
                    $currentStyle = ArrayHelper::merge($currentStyle, $content);
                }

                // if negative is set, invert background and foreground
                if ($negative) {
                    if (isset($currentStyle['color'])) {
                        $fgColor = $currentStyle['color'];
                        unset($currentStyle['color']);
                    }
                    if (isset($currentStyle['background-color'])) {
                        $bgColor = $currentStyle['background-color'];
                        unset($currentStyle['background-color']);
                    }
                    if (isset($fgColor)) {
                        $currentStyle['background-color'] = $fgColor;
                    }
                    if (isset($bgColor)) {
                        $currentStyle['color'] = $bgColor;
                    }
                }

                $styleString = '';
                foreach ($currentStyle as $name => $value) {
                    if (is_array($value)) {
                        $value = implode(' ', $value);
                    }
                    $styleString .= "$name: $value;";
                }
                $tags++;
                return "$return<span style=\"$styleString\">";
            },
            $string
        );
        while ($tags > 0) {
            $result .= '</span>';
            $tags--;
        }

        return $result;
    }

    /**
     * 通过应用一些 ANSI 格式，将 Markdown 转换为在控制台环境中更好的可读性。
     * @param string $markdown markdown 的字符串。
     * @return string 解析后的结果为 ANSI 格式化字符串。
     */
    public static function markdownToAnsi($markdown)
    {
        $parser = new ConsoleMarkdown();
        return $parser->parse($markdown);
    }

    /**
     * 将字符串转换成 ansi 格式，用 ansi 控制代码模式 %y 替换成（黄色）。
     *
     * 与 https://github.com/pear/Console_Color2/blob/master/Console/Color2.php 使用几乎相同的语法
     * 这张转换表如下：
     *（在一些终端上 'bold' 意思是 'light'）。它几乎与 irssi 使用的转换表相同。
     * <pre>
     *                  text      text            background
     *      ------------------------------------------------
     *      %k %K %0    black     dark grey       black
     *      %r %R %1    red       bold red        red
     *      %g %G %2    green     bold green      green
     *      %y %Y %3    yellow    bold yellow     yellow
     *      %b %B %4    blue      bold blue       blue
     *      %m %M %5    magenta   bold magenta    magenta
     *      %p %P       magenta (think: purple)
     *      %c %C %6    cyan      bold cyan       cyan
     *      %w %W %7    white     bold white      white
     *
     *      %F     Blinking, Flashing
     *      %U     Underline
     *      %8     Reverse
     *      %_,%9  Bold
     *
     *      %n     Resets the color
     *      %%     A single %
     * </pre>
     * 第一个参数是要转换的字符串，
     * 第二个参数是可选的标志是否需要使用颜色。它默认设置为 true，如果设置为 false，
     * 颜色代码将被移除（并且 %% 将被改变为 %）
     *
     * @param string $string 转换的字符串
     * @param bool $colored 是否应该为字符串设定颜色？
     * @return string
     */
    public static function renderColoredString($string, $colored = true)
    {
        // TODO rework/refactor according to https://github.com/yiisoft/yii2/issues/746
        static $conversions = [
            '%y' => [self::FG_YELLOW],
            '%g' => [self::FG_GREEN],
            '%b' => [self::FG_BLUE],
            '%r' => [self::FG_RED],
            '%p' => [self::FG_PURPLE],
            '%m' => [self::FG_PURPLE],
            '%c' => [self::FG_CYAN],
            '%w' => [self::FG_GREY],
            '%k' => [self::FG_BLACK],
            '%n' => [0], // reset
            '%Y' => [self::FG_YELLOW, self::BOLD],
            '%G' => [self::FG_GREEN, self::BOLD],
            '%B' => [self::FG_BLUE, self::BOLD],
            '%R' => [self::FG_RED, self::BOLD],
            '%P' => [self::FG_PURPLE, self::BOLD],
            '%M' => [self::FG_PURPLE, self::BOLD],
            '%C' => [self::FG_CYAN, self::BOLD],
            '%W' => [self::FG_GREY, self::BOLD],
            '%K' => [self::FG_BLACK, self::BOLD],
            '%N' => [0, self::BOLD],
            '%3' => [self::BG_YELLOW],
            '%2' => [self::BG_GREEN],
            '%4' => [self::BG_BLUE],
            '%1' => [self::BG_RED],
            '%5' => [self::BG_PURPLE],
            '%6' => [self::BG_CYAN],
            '%7' => [self::BG_GREY],
            '%0' => [self::BG_BLACK],
            '%F' => [self::BLINK],
            '%U' => [self::UNDERLINE],
            '%8' => [self::NEGATIVE],
            '%9' => [self::BOLD],
            '%_' => [self::BOLD],
        ];

        if ($colored) {
            $string = str_replace('%%', '% ', $string);
            foreach ($conversions as $key => $value) {
                $string = str_replace(
                    $key,
                    static::ansiFormatCode($value),
                    $string
                );
            }
            $string = str_replace('% ', '%', $string);
        } else {
            $string = preg_replace('/%((%)|.)/', '$2', $string);
        }

        return $string;
    }

    /**
     * 当字符串被解析时如果包含转义符 %
     * 则它们不会通过 [[renderColoredString]] 解释为颜色代码。
     *
     * @param string $string 转义字符串
     *
     * @return string
     */
    public static function escape($string)
    {
        // TODO rework/refactor according to https://github.com/yiisoft/yii2/issues/746
        return str_replace('%', '%%', $string);
    }

    /**
     * 如果流支持彩色化则返回 true。如果流不支持则 ANSI 颜色被禁用。
     *
     * - 不含 ansicon 窗口
     * - 非 tty 控制台
     *
     * @param mixed $stream
     * @return bool 如果流支持 ANSI 颜色返回 true，否则返回 false。
     */
    public static function streamSupportsAnsiColors($stream)
    {
        return DIRECTORY_SEPARATOR === '\\'
            ? getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON'
            : function_exists('posix_isatty') && @posix_isatty($stream);
    }

    /**
     * 如果控制台在 windows 上运行，则返回 true。
     * @return bool
     */
    public static function isRunningOnWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    /**
     * 返回终端屏幕大小。
     *
     * 使用如下：
     *
     * ```php
     * list($width, $height) = ConsoleHelper::getScreenSize();
     * ```
     *
     * @param bool $refresh 是否强制检查而不是重用缓存的大小值。
     * 这有助于在应用程序运行时检测窗口大小的变化，
     * 但可能无法在每个终端上获得最新的值。
     * @return array|bool 当无法确定数组中的值（$width，$height）或者返回 false。
     */
    public static function getScreenSize($refresh = false)
    {
        static $size;
        if ($size !== null && !$refresh) {
            return $size;
        }

        if (static::isRunningOnWindows()) {
            $output = [];
            exec('mode con', $output);
            if (isset($output[1]) && strpos($output[1], 'CON') !== false) {
                return $size = [(int) preg_replace('~\D~', '', $output[4]), (int) preg_replace('~\D~', '', $output[3])];
            }
        } else {
            // try stty if available
            $stty = [];
            if (exec('stty -a 2>&1', $stty)) {
                $stty = implode(' ', $stty);

                // Linux stty output
                if (preg_match('/rows\s+(\d+);\s*columns\s+(\d+);/mi', $stty, $matches)) {
                    return $size = [(int) $matches[2], (int) $matches[1]];
                }

                // MacOS stty output
                if (preg_match('/(\d+)\s+rows;\s*(\d+)\s+columns;/mi', $stty, $matches)) {
                    return $size = [(int) $matches[2], (int) $matches[1]];
                }
            }

            // fallback to tput, which may not be updated on terminal resize
            if (($width = (int) exec('tput cols 2>&1')) > 0 && ($height = (int) exec('tput lines 2>&1')) > 0) {
                return $size = [$width, $height];
            }

            // fallback to ENV variables, which may not be updated on terminal resize
            if (($width = (int) getenv('COLUMNS')) > 0 && ($height = (int) getenv('LINES')) > 0) {
                return $size = [$width, $height];
            }
        }

        return $size = false;
    }

    /**
     * 自动缩进以适合屏幕大小
     *
     * 如果无法检测到屏幕大小，或者缩进大于屏幕尺寸，则文本不会被换行。
     *
     * 第一行将被 **not** 定义，因此 `Console::wrapText("Lorem ipsum dolor sit amet.", 4)`
     * 会产生以下输出，给定屏幕宽度为 16 个字符：
     *
     * ```
     * Lorem ipsum
     *     dolor sit
     *     amet.
     * ```
     *
     * @param string $text 将要被覆盖的字符串
     * @param int $indent 用于缩进的空格数。
     * @param bool $refresh 是否强制刷新屏幕大小。
     * 这个将被传递给 [[getScreenSize()]]。
     * @return string 被覆盖的字符串。
     * @since 2.0.4
     */
    public static function wrapText($text, $indent = 0, $refresh = false)
    {
        $size = static::getScreenSize($refresh);
        if ($size === false || $size[0] <= $indent) {
            return $text;
        }
        $pad = str_repeat(' ', $indent);
        $lines = explode("\n", wordwrap($text, $size[0] - $indent, "\n", true));
        $first = true;
        foreach ($lines as $i => $line) {
            if ($first) {
                $first = false;
                continue;
            }
            $lines[$i] = $pad . $line;
        }

        return implode("\n", $lines);
    }

    /**
     * 从 STDIN 获取输入，并为 EOL 返回右侧被截取后的字符串
     *
     * @param bool $raw 如果设置 true，返回的字符串不进行删除
     * @return string 从 stdin 读取的字符串
     */
    public static function stdin($raw = false)
    {
        return $raw ? fgets(\STDIN) : rtrim(fgets(\STDIN), PHP_EOL);
    }

    /**
     * 打印字符串到 STDOUT。
     *
     * @param string $string 将要打印的字符串
     * @return int|bool 发生错误时将打印字节数或者返回 false
     */
    public static function stdout($string)
    {
        return fwrite(\STDOUT, $string);
    }

    /**
     * 将字符串打印到 STDERR。
     *
     * @param string $string 要打印的字符串
     * @return int|bool 发生错误时将打印字节数或者返回 false
     */
    public static function stderr($string)
    {
        return fwrite(\STDERR, $string);
    }

    /**
     * 请求用户输入。当用户键入回车时结束（PHP_EOL）。
     * 可选，它还提供了一个提示。
     *
     * @param string $prompt 等待输入之前显示提示（可选）
     * @return string 用户的输入
     */
    public static function input($prompt = null)
    {
        if (isset($prompt)) {
            static::stdout($prompt);
        }

        return static::stdin();
    }

    /**
     * 打印带有回车信息的 STDOUT 文本（PHP_EOL）。
     *
     * @param string $string 打印的字符串
     * @return int|bool 发生错误时将打印字节数或者返回 false。
     */
    public static function output($string = null)
    {
        return static::stdout($string . PHP_EOL);
    }

    /**
     * 打印文本到 STDERR 并附加回车信息（PHP_EOL）。
     *
     * @param string $string 打印的字符串
     * @return int|bool 发生错误时将打印字节数或者返回 false。
     */
    public static function error($string = null)
    {
        return static::stderr($string . PHP_EOL);
    }

    /**
     * 提示用户输入并验证。
     *
     * @param string $text 提示字符串
     * @param array $options 验证输入的选项：
     *
     * - `required`：无论是否被要求
     * - `default`：没用没有插入任何值则返回默认值
     * - `pattern`：通过正则表达式模式匹配验证用户的输入
     * - `validator`：回调函数验证输入。函数必须接受两个参数：
     * - `input`：去验证用户的输入
     * - `error`：如果验证失败则通过引用进行传递错误信息。
     *
     * @return string 用户输入
     */
    public static function prompt($text, $options = [])
    {
        $options = ArrayHelper::merge(
            [
                'required' => false,
                'default' => null,
                'pattern' => null,
                'validator' => null,
                'error' => 'Invalid input.',
            ],
            $options
        );
        $error = null;

        top:
        $input = $options['default']
            ? static::input("$text [" . $options['default'] . '] ')
            : static::input("$text ");

        if ($input === '') {
            if (isset($options['default'])) {
                $input = $options['default'];
            } elseif ($options['required']) {
                static::output($options['error']);
                goto top;
            }
        } elseif ($options['pattern'] && !preg_match($options['pattern'], $input)) {
            static::output($options['error']);
            goto top;
        } elseif ($options['validator'] &&
            !call_user_func_array($options['validator'], [$input, &$error])
        ) {
            static::output(isset($error) ? $error : $options['error']);
            goto top;
        }

        return $input;
    }

    /**
     * 请用户输入 y 或 n 进行确认。
     *
     * 一个典型的用法如下：
     *
     * ```php
     * if (Console::confirm("Are you sure?")) {
     *     echo "user typed yes\n";
     * } else {
     *     echo "user typed no\n";
     * }
     * ```
     *
     * @param string $message 在等待用户输入之前打印出来
     * @param bool $default 如果没有选择，将返回他的值。
     * @return bool 用户是否确认
     */
    public static function confirm($message, $default = false)
    {
        while (true) {
            static::stdout($message . ' (yes|no) [' . ($default ? 'yes' : 'no') . ']:');
            $input = trim(static::stdin());

            if (empty($input)) {
                return $default;
            }

            if (!strcasecmp($input, 'y') || !strcasecmp($input, 'yes')) {
                return true;
            }

            if (!strcasecmp($input, 'n') || !strcasecmp($input, 'no')) {
                return false;
            }
        }
    }

    /**
     * 给用户一个选项进行选择。
     * 输入 '?' 则给出可供选择的选项及其选项列表对应的说明解释。
     *
     * @param string $prompt 提示消息
     * @param array $options 从键值数组中进行选项的选择。
     * 输入和使用的是什么键，值是通过帮助命令显示给最终用户的内容。
     *
     * @return string 用户选择的选项字符
     */
    public static function select($prompt, $options = [])
    {
        top:
        static::stdout("$prompt [" . implode(',', array_keys($options)) . ',?]: ');
        $input = static::stdin();
        if ($input === '?') {
            foreach ($options as $key => $value) {
                static::output(" $key - $value");
            }
            static::output(' ? - Show help');
            goto top;
        } elseif (!array_key_exists($input, $options)) {
            goto top;
        }

        return $input;
    }

    private static $_progressStart;
    private static $_progressWidth;
    private static $_progressPrefix;
    private static $_progressEta;
    private static $_progressEtaLastDone = 0;
    private static $_progressEtaLastUpdate;

    /**
     * 开始在屏幕上显示进度条。
     *
     * 这进度条将被 [[updateProgress()]] 进行更新并且通过 [[endProgress()]] 进行结束。
     *
     * 下面的示例显示了进度条的简单用法：
     *
     * ```php
     * Console::startProgress(0, 1000);
     * for ($n = 1; $n <= 1000; $n++) {
     *     usleep(1000);
     *     Console::updateProgress($n, 1000);
     * }
     * Console::endProgress();
     * ```
     *
     * 像 Git 克隆一样进步（只显示状态信息）：
     * ```php
     * Console::startProgress(0, 1000, 'Counting objects: ', false);
     * for ($n = 1; $n <= 1000; $n++) {
     *     usleep(1000);
     *     Console::updateProgress($n, 1000);
     * }
     * Console::endProgress("done." . PHP_EOL);
     * ```
     *
     * @param int $done 已完成项的数量
     * @param int $total 需要完成的项目的总数量
     * @param string $prefix 在进度条之前显示的可选字符串。
     * 默认为空字符串，因此不显示前缀。
     * @param int|bool $width 可选的进度条宽度。
     * 可以通过整数来表示进度条显示的字符或者可以使用 0 到 1 之间的浮点数百分比来表示。
     * 也可以将其设置为 false
     * 以禁用只显示进度信息，比如百分比，总数量以及预估到达时间。
     * 如果不设置，横条将和屏幕一样宽。屏幕大小将使用 [[getScreenSize()]] 来检测。
     * @see startProgress
     * @see updateProgress
     * @see endProgress
     */
    public static function startProgress($done, $total, $prefix = '', $width = null)
    {
        self::$_progressStart = time();
        self::$_progressWidth = $width;
        self::$_progressPrefix = $prefix;
        self::$_progressEta = null;
        self::$_progressEtaLastDone = 0;
        self::$_progressEtaLastUpdate = time();

        static::updateProgress($done, $total);
    }

    /**
     * 使用 [[startProgress()]] 更新已启动的进度条。
     *
     * @param int $done 完成的项的数量。
     * @param int $total 需要完成的项目的总数量
     * @param string $prefix 在进度条之前显示的可选字符串。
     * 默认是 null 表示由 [[startProgress()]] 指定将被使用的前缀。
     * 如果指定前缀，它将更新以后调用将使用的前缀。
     * @see startProgress
     * @see endProgress
     */
    public static function updateProgress($done, $total, $prefix = null)
    {
        if ($prefix === null) {
            $prefix = self::$_progressPrefix;
        } else {
            self::$_progressPrefix = $prefix;
        }
        $width = static::getProgressbarWidth($prefix);
        $percent = ($total == 0) ? 1 : $done / $total;
        $info = sprintf('%d%% (%d/%d)', $percent * 100, $done, $total);
        self::setETA($done, $total);
        $info .= self::$_progressEta === null ? ' ETA: n/a' : sprintf(' ETA: %d sec.', self::$_progressEta);

        // Number extra characters outputted. These are opening [, closing ], and space before info
        // Since Windows uses \r\n\ for line endings, there's one more in the case
        $extraChars = static::isRunningOnWindows() ? 4 : 3;
        $width -= $extraChars + static::ansiStrlen($info);
        // skipping progress bar on very small display or if forced to skip
        if ($width < 5) {
            static::stdout("\r$prefix$info   ");
        } else {
            if ($percent < 0) {
                $percent = 0;
            } elseif ($percent > 1) {
                $percent = 1;
            }
            $bar = floor($percent * $width);
            $status = str_repeat('=', $bar);
            if ($bar < $width) {
                $status .= '>';
                $status .= str_repeat(' ', $width - $bar - 1);
            }
            static::stdout("\r$prefix" . "[$status] $info");
        }
        flush();
    }

    /**
     * 返回进度条的宽度
     * @param string $prefix 在进度条之前显示的可选字符串。
     * @see updateProgress
     * @return int 屏幕宽度
     * @since 2.0.14
     */
    private static function getProgressbarWidth($prefix)
    {
        $width = self::$_progressWidth;

        if ($width === false) {
            return 0;
        }

        $screenSize = static::getScreenSize(true);
        if ($screenSize === false && $width < 1) {
            return 0;
        }

        if ($width === null) {
            $width = $screenSize[0];
        } elseif ($width > 0 && $width < 1) {
            $width = floor($screenSize[0] * $width);
        }

        $width -= static::ansiStrlen($prefix);

        return $width;
    }

    /**
     * 预测变量 $_progressEta，$_progressEtaLastUpdate 和 $_progressEtaLastDone
     * @param int $done 完成的项的数量。
     * @param int $total 需要完成的项的总数量
     * @see updateProgress
     * @since 2.0.14
     */
    private static function setETA($done, $total)
    {
        if ($done > $total || $done == 0) {
            self::$_progressEta = null;
            self::$_progressEtaLastUpdate = time();
            return;
        }

        if ($done < $total && (time() - self::$_progressEtaLastUpdate > 1 && $done > self::$_progressEtaLastDone)) {
            $rate = (time() - (self::$_progressEtaLastUpdate ?: self::$_progressStart)) / ($done - self::$_progressEtaLastDone);
            self::$_progressEta = $rate * ($total - $done);
            self::$_progressEtaLastUpdate = time();
            self::$_progressEtaLastDone = $done;
        }
    }

    /**
     * 通过 [[startProgress()]] 结束已启动的进度条。
     *
     * @param string|bool $remove 它可能是 `false` 将进度条留在屏幕上，只打印一个换行符。
     * 如果设置为 `true`，进度条的线条将被清除。
     * 这也可能是要显示的字符串不是进度条。
     * @param bool $keepPrefix 当进度条开始移除时是否保留指定的前缀。
     * 默认设置为 true。
     * @see startProgress
     * @see updateProgress
     */
    public static function endProgress($remove = false, $keepPrefix = true)
    {
        if ($remove === false) {
            static::stdout(PHP_EOL);
        } else {
            if (static::streamSupportsAnsiColors(STDOUT)) {
                static::clearLine();
            }
            static::stdout("\r" . ($keepPrefix ? self::$_progressPrefix : '') . (is_string($remove) ? $remove : ''));
        }
        flush();

        self::$_progressStart = null;
        self::$_progressWidth = null;
        self::$_progressPrefix = '';
        self::$_progressEta = null;
        self::$_progressEtaLastDone = 0;
        self::$_progressEtaLastUpdate = null;
    }

    /**
     * 生成验证错误的摘要。
     * @param Model|Model[] $models 这个模型将显示验证错误的信息。
     * @param array $options 标签选项的键-值对。以下是特殊处理的选项：
     *
     * - showAllErrors：布尔型，如果设置为 true，则每个属性的每个错误消息将以其他方式显示。
     *   否则将只显示每个属性的第一个错误消息。默认为 `false`。
     *
     * @return string the generated error summary
     * @since 2.0.14
     */
    public static function errorSummary($models, $options = [])
    {
        $showAllErrors = ArrayHelper::remove($options, 'showAllErrors', false);
        $lines = self::collectErrors($models, $showAllErrors);

        return implode(PHP_EOL, $lines);
    }

    /**
     * 返回验证错误数组
     * @param Model|Model[] $models 要显示其验证错误的模型。
     * @param $showAllErrors 布尔型，如果设置为 true 则每个属性的每个错误消息将以其他方式显示
     * 否则将只显示每个属性的第一个错误消息。
     * @return 验证错误数组
     * @since 2.0.14
     */
    private static function collectErrors($models, $showAllErrors)
    {
        $lines = [];
        if (!is_array($models)) {
            $models = [$models];
        }

        foreach ($models as $model) {
            $lines = array_unique(array_merge($lines, $model->getErrorSummary($showAllErrors)));
        }

        return $lines;
    }
}
