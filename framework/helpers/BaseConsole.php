<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\console\Markdown as ConsoleMarkdown;

/**
 * BaseConsole provides concrete implementation for [[Console]].
 *
 * Do not use BaseConsole. Use [[Console]] instead.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class BaseConsole
{
    // foreground color control codes
    const FG_BLACK  = 30;
    const FG_RED    = 31;
    const FG_GREEN  = 32;
    const FG_YELLOW = 33;
    const FG_BLUE   = 34;
    const FG_PURPLE = 35;
    const FG_CYAN   = 36;
    const FG_GREY   = 37;
    // background color control codes
    const BG_BLACK  = 40;
    const BG_RED    = 41;
    const BG_GREEN  = 42;
    const BG_YELLOW = 43;
    const BG_BLUE   = 44;
    const BG_PURPLE = 45;
    const BG_CYAN   = 46;
    const BG_GREY   = 47;
    // fonts style control codes
    const RESET       = 0;
    const NORMAL      = 0;
    const BOLD        = 1;
    const ITALIC      = 3;
    const UNDERLINE   = 4;
    const BLINK       = 5;
    const NEGATIVE    = 7;
    const CONCEALED   = 8;
    const CROSSED_OUT = 9;
    const FRAMED      = 51;
    const ENCIRCLED   = 52;
    const OVERLINED   = 53;


    /**
     * Moves the terminal cursor up by sending ANSI control code CUU to the terminal.
     * If the cursor is already at the edge of the screen, this has no effect.
     * @param int $rows number of rows the cursor should be moved up
     */
    public static function moveCursorUp($rows = 1)
    {
        echo "\033[" . (int) $rows . 'A';
    }

    /**
     * Moves the terminal cursor down by sending ANSI control code CUD to the terminal.
     * If the cursor is already at the edge of the screen, this has no effect.
     * @param int $rows number of rows the cursor should be moved down
     */
    public static function moveCursorDown($rows = 1)
    {
        echo "\033[" . (int) $rows . 'B';
    }

    /**
     * Moves the terminal cursor forward by sending ANSI control code CUF to the terminal.
     * If the cursor is already at the edge of the screen, this has no effect.
     * @param int $steps number of steps the cursor should be moved forward
     */
    public static function moveCursorForward($steps = 1)
    {
        echo "\033[" . (int) $steps . 'C';
    }

    /**
     * Moves the terminal cursor backward by sending ANSI control code CUB to the terminal.
     * If the cursor is already at the edge of the screen, this has no effect.
     * @param int $steps number of steps the cursor should be moved backward
     */
    public static function moveCursorBackward($steps = 1)
    {
        echo "\033[" . (int) $steps . 'D';
    }

    /**
     * Moves the terminal cursor to the beginning of the next line by sending ANSI control code CNL to the terminal.
     * @param int $lines number of lines the cursor should be moved down
     */
    public static function moveCursorNextLine($lines = 1)
    {
        echo "\033[" . (int) $lines . 'E';
    }

    /**
     * Moves the terminal cursor to the beginning of the previous line by sending ANSI control code CPL to the terminal.
     * @param int $lines number of lines the cursor should be moved up
     */
    public static function moveCursorPrevLine($lines = 1)
    {
        echo "\033[" . (int) $lines . 'F';
    }

    /**
     * Moves the cursor to an absolute position given as column and row by sending ANSI control code CUP or CHA to the terminal.
     * @param int $column 1-based column number, 1 is the left edge of the screen.
     * @param int|null $row 1-based row number, 1 is the top edge of the screen. if not set, will move cursor only in current line.
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
     * Scrolls whole page up by sending ANSI control code SU to the terminal.
     * New lines are added at the bottom. This is not supported by ANSI.SYS used in windows.
     * @param int $lines number of lines to scroll up
     */
    public static function scrollUp($lines = 1)
    {
        echo "\033[" . (int) $lines . 'S';
    }

    /**
     * Scrolls whole page down by sending ANSI control code SD to the terminal.
     * New lines are added at the top. This is not supported by ANSI.SYS used in windows.
     * @param int $lines number of lines to scroll down
     */
    public static function scrollDown($lines = 1)
    {
        echo "\033[" . (int) $lines . 'T';
    }

    /**
     * Saves the current cursor position by sending ANSI control code SCP to the terminal.
     * Position can then be restored with [[restoreCursorPosition()]].
     */
    public static function saveCursorPosition()
    {
        echo "\033[s";
    }

    /**
     * Restores the cursor position saved with [[saveCursorPosition()]] by sending ANSI control code RCP to the terminal.
     */
    public static function restoreCursorPosition()
    {
        echo "\033[u";
    }

    /**
     * Hides the cursor by sending ANSI DECTCEM code ?25l to the terminal.
     * Use [[showCursor()]] to bring it back.
     * Do not forget to show cursor when your application exits. Cursor might stay hidden in terminal after exit.
     */
    public static function hideCursor()
    {
        echo "\033[?25l";
    }

    /**
     * Will show a cursor again when it has been hidden by [[hideCursor()]]  by sending ANSI DECTCEM code ?25h to the terminal.
     */
    public static function showCursor()
    {
        echo "\033[?25h";
    }

    /**
     * Clears entire screen content by sending ANSI control code ED with argument 2 to the terminal.
     * Cursor position will not be changed.
     * **Note:** ANSI.SYS implementation used in windows will reset cursor position to upper left corner of the screen.
     */
    public static function clearScreen()
    {
        echo "\033[2J";
    }

    /**
     * Clears text from cursor to the beginning of the screen by sending ANSI control code ED with argument 1 to the terminal.
     * Cursor position will not be changed.
     */
    public static function clearScreenBeforeCursor()
    {
        echo "\033[1J";
    }

    /**
     * Clears text from cursor to the end of the screen by sending ANSI control code ED with argument 0 to the terminal.
     * Cursor position will not be changed.
     */
    public static function clearScreenAfterCursor()
    {
        echo "\033[0J";
    }

    /**
     * Clears the line, the cursor is currently on by sending ANSI control code EL with argument 2 to the terminal.
     * Cursor position will not be changed.
     */
    public static function clearLine()
    {
        echo "\033[2K";
    }

    /**
     * Clears text from cursor position to the beginning of the line by sending ANSI control code EL with argument 1 to the terminal.
     * Cursor position will not be changed.
     */
    public static function clearLineBeforeCursor()
    {
        echo "\033[1K";
    }

    /**
     * Clears text from cursor position to the end of the line by sending ANSI control code EL with argument 0 to the terminal.
     * Cursor position will not be changed.
     */
    public static function clearLineAfterCursor()
    {
        echo "\033[0K";
    }

    /**
     * Returns the ANSI format code.
     *
     * @param array $format An array containing formatting values.
     * You can pass any of the `FG_*`, `BG_*` and `TEXT_*` constants
     * and also [[xtermFgColor]] and [[xtermBgColor]] to specify a format.
     * @return string The ANSI format code according to the given formatting constants.
     */
    public static function ansiFormatCode($format)
    {
        return "\033[" . implode(';', $format) . 'm';
    }

    /**
     * Echoes an ANSI format code that affects the formatting of any text that is printed afterwards.
     *
     * @param array $format An array containing formatting values.
     * You can pass any of the `FG_*`, `BG_*` and `TEXT_*` constants
     * and also [[xtermFgColor]] and [[xtermBgColor]] to specify a format.
     * @see ansiFormatCode()
     * @see endAnsiFormat()
     */
    public static function beginAnsiFormat($format)
    {
        echo "\033[" . implode(';', $format) . 'm';
    }

    /**
     * Resets any ANSI format set by previous method [[beginAnsiFormat()]]
     * Any output after this will have default text format.
     * This is equal to calling
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
     * Will return a string formatted with the given ANSI style
     *
     * @param string $string the string to be formatted
     * @param array $format An array containing formatting values.
     * You can pass any of the `FG_*`, `BG_*` and `TEXT_*` constants
     * and also [[xtermFgColor]] and [[xtermBgColor]] to specify a format.
     * @return string
     */
    public static function ansiFormat($string, $format = [])
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $string . "\033[0m";
    }

    /**
     * Returns the ansi format code for xterm foreground color.
     * You can pass the return value of this to one of the formatting methods:
     * [[ansiFormat]], [[ansiFormatCode]], [[beginAnsiFormat]]
     *
     * @param int $colorCode xterm color code
     * @return string
     * @see http://en.wikipedia.org/wiki/Talk:ANSI_escape_code#xterm-256colors
     */
    public static function xtermFgColor($colorCode)
    {
        return '38;5;' . $colorCode;
    }

    /**
     * Returns the ansi format code for xterm background color.
     * You can pass the return value of this to one of the formatting methods:
     * [[ansiFormat]], [[ansiFormatCode]], [[beginAnsiFormat]]
     *
     * @param int $colorCode xterm color code
     * @return string
     * @see http://en.wikipedia.org/wiki/Talk:ANSI_escape_code#xterm-256colors
     */
    public static function xtermBgColor($colorCode)
    {
        return '48;5;' . $colorCode;
    }

    /**
     * Strips ANSI control codes from a string
     *
     * @param string $string String to strip
     * @return string
     */
    public static function stripAnsiFormat($string)
    {
        return preg_replace('/\033\[[\d;?]*\w/', '', $string);
    }

    /**
     * Returns the length of the string without ANSI color codes.
     * @param string $string the string to measure
     * @return int the length of the string not counting ANSI format characters
     */
    public static function ansiStrlen($string)
    {
        return mb_strlen(static::stripAnsiFormat($string));
    }

    /**
     * Converts an ANSI formatted string to HTML
     *
     * Note: xTerm 256 bit colors are currently not supported.
     *
     * @param string $string the string to convert.
     * @param array $styleMap an optional mapping of ANSI control codes such as
     * FG\_*COLOR* or [[BOLD]] to a set of css style definitions.
     * The CSS style definitions are represented as an array where the array keys correspond
     * to the css style attribute names and the values are the css values.
     * values may be arrays that will be merged and imploded with `' '` when rendered.
     * @return string HTML representation of the ANSI formatted string
     */
    public static function ansiToHtml($string, $styleMap = [])
    {
        $styleMap = [
            // http://www.w3.org/TR/CSS2/syndata.html#value-def-color
            self::FG_BLACK =>    ['color' => 'black'],
            self::FG_BLUE =>     ['color' => 'blue'],
            self::FG_CYAN =>     ['color' => 'aqua'],
            self::FG_GREEN =>    ['color' => 'lime'],
            self::FG_GREY =>     ['color' => 'silver'],
            // http://meyerweb.com/eric/thoughts/2014/06/19/rebeccapurple/
            // http://dev.w3.org/csswg/css-color/#valuedef-rebeccapurple
            self::FG_PURPLE =>   ['color' => 'rebeccapurple'],
            self::FG_RED =>      ['color' => 'red'],
            self::FG_YELLOW =>   ['color' => 'yellow'],
            self::BG_BLACK =>    ['background-color' => 'black'],
            self::BG_BLUE =>     ['background-color' => 'blue'],
            self::BG_CYAN =>     ['background-color' => 'aqua'],
            self::BG_GREEN =>    ['background-color' => 'lime'],
            self::BG_GREY =>     ['background-color' => 'silver'],
            self::BG_PURPLE =>   ['background-color' => 'rebeccapurple'],
            self::BG_RED =>      ['background-color' => 'red'],
            self::BG_YELLOW =>   ['background-color' => 'yellow'],
            self::BOLD =>        ['font-weight' => 'bold'],
            self::ITALIC =>      ['font-style' => 'italic'],
            self::UNDERLINE =>   ['text-decoration' => ['underline']],
            self::OVERLINED =>   ['text-decoration' => ['overline']],
            self::CROSSED_OUT => ['text-decoration' => ['line-through']],
            self::BLINK =>       ['text-decoration' => ['blink']],
            self::CONCEALED =>   ['visibility' => 'hidden'],
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
     * Converts Markdown to be better readable in console environments by applying some ANSI format
     * @param string $markdown the markdown string.
     * @return string the parsed result as ANSI formatted string.
     */
    public static function markdownToAnsi($markdown)
    {
        $parser = new ConsoleMarkdown();
        return $parser->parse($markdown);
    }

    /**
     * Converts a string to ansi formatted by replacing patterns like %y (for yellow) with ansi control codes
     *
     * Uses almost the same syntax as https://github.com/pear/Console_Color2/blob/master/Console/Color2.php
     * The conversion table is: ('bold' meaning 'light' on some
     * terminals). It's almost the same conversion table irssi uses.
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
     * First param is the string to convert, second is an optional flag if
     * colors should be used. It defaults to true, if set to false, the
     * color codes will just be removed (And %% will be transformed into %)
     *
     * @param string $string String to convert
     * @param bool $colored Should the string be colored?
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
     * Escapes % so they don't get interpreted as color codes when
     * the string is parsed by [[renderColoredString]]
     *
     * @param string $string String to escape
     *
     * @access public
     * @return string
     */
    public static function escape($string)
    {
        // TODO rework/refactor according to https://github.com/yiisoft/yii2/issues/746
        return str_replace('%', '%%', $string);
    }

    /**
     * Returns true if the stream supports colorization. ANSI colors are disabled if not supported by the stream.
     *
     * - windows without ansicon
     * - not tty consoles
     *
     * @param mixed $stream
     * @return bool true if the stream supports ANSI colors, otherwise false.
     */
    public static function streamSupportsAnsiColors($stream)
    {
        return DIRECTORY_SEPARATOR === '\\'
            ? getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON'
            : function_exists('posix_isatty') && @posix_isatty($stream);
    }

    /**
     * Returns true if the console is running on windows
     * @return bool
     */
    public static function isRunningOnWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    /**
     * Usage: list($width, $height) = ConsoleHelper::getScreenSize();
     *
     * @param bool $refresh whether to force checking and not re-use cached size value.
     * This is useful to detect changing window size while the application is running but may
     * not get up to date values on every terminal.
     * @return array|bool An array of ($width, $height) or false when it was not able to determine size.
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
            if (isset($output, $output[1]) && strpos($output[1], 'CON') !== false) {
                return $size = [(int) preg_replace('~\D~', '', $output[4]), (int) preg_replace('~\D~', '', $output[3])];
            }
        } else {
            // try stty if available
            $stty = [];
            if (exec('stty -a 2>&1', $stty)) {
                $stty = implode(' ', $stty);

                // Linux stty output
                if (preg_match('/rows\s+(\d+);\s*columns\s+(\d+);/mi', $stty, $matches)) {
                    return $size = [(int)$matches[2], (int)$matches[1]];
                }

                // MacOS stty output
                if (preg_match('/(\d+)\s+rows;\s*(\d+)\s+columns;/mi', $stty, $matches)) {
                    return $size = [(int)$matches[2], (int)$matches[1]];
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
     * Word wrap text with indentation to fit the screen size
     *
     * If screen size could not be detected, or the indentation is greater than the screen size, the text will not be wrapped.
     *
     * The first line will **not** be indented, so `Console::wrapText("Lorem ipsum dolor sit amet.", 4)` will result in the
     * following output, given the screen width is 16 characters:
     *
     * ```
     * Lorem ipsum
     *     dolor sit
     *     amet.
     * ```
     *
     * @param string $text the text to be wrapped
     * @param int $indent number of spaces to use for indentation.
     * @param bool $refresh whether to force refresh of screen size.
     * This will be passed to [[getScreenSize()]].
     * @return string the wrapped text.
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
     * Gets input from STDIN and returns a string right-trimmed for EOLs.
     *
     * @param bool $raw If set to true, returns the raw string without trimming
     * @return string the string read from stdin
     */
    public static function stdin($raw = false)
    {
        return $raw ? fgets(\STDIN) : rtrim(fgets(\STDIN), PHP_EOL);
    }

    /**
     * Prints a string to STDOUT.
     *
     * @param string $string the string to print
     * @return int|bool Number of bytes printed or false on error
     */
    public static function stdout($string)
    {
        return fwrite(\STDOUT, $string);
    }

    /**
     * Prints a string to STDERR.
     *
     * @param string $string the string to print
     * @return int|bool Number of bytes printed or false on error
     */
    public static function stderr($string)
    {
        return fwrite(\STDERR, $string);
    }

    /**
     * Asks the user for input. Ends when the user types a carriage return (PHP_EOL). Optionally, It also provides a
     * prompt.
     *
     * @param string $prompt the prompt to display before waiting for input (optional)
     * @return string the user's input
     */
    public static function input($prompt = null)
    {
        if (isset($prompt)) {
            static::stdout($prompt);
        }

        return static::stdin();
    }

    /**
     * Prints text to STDOUT appended with a carriage return (PHP_EOL).
     *
     * @param string $string the text to print
     * @return int|bool number of bytes printed or false on error.
     */
    public static function output($string = null)
    {
        return static::stdout($string . PHP_EOL);
    }

    /**
     * Prints text to STDERR appended with a carriage return (PHP_EOL).
     *
     * @param string $string the text to print
     * @return int|bool number of bytes printed or false on error.
     */
    public static function error($string = null)
    {
        return static::stderr($string . PHP_EOL);
    }

    /**
     * Prompts the user for input and validates it
     *
     * @param string $text prompt string
     * @param array $options the options to validate the input:
     *
     * - `required`: whether it is required or not
     * - `default`: default value if no input is inserted by the user
     * - `pattern`: regular expression pattern to validate user input
     * - `validator`: a callable function to validate input. The function must accept two parameters:
     * - `input`: the user input to validate
     * - `error`: the error value passed by reference if validation failed.
     *
     * @return string the user input
     */
    public static function prompt($text, $options = [])
    {
        $options = ArrayHelper::merge(
            [
                'required'  => false,
                'default'   => null,
                'pattern'   => null,
                'validator' => null,
                'error'     => 'Invalid input.',
            ],
            $options
        );
        $error   = null;

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
     * Asks user to confirm by typing y or n.
     *
     * A typical usage looks like the following:
     *
     * ```php
     * if (Console::confirm("Are you sure?")) {
     *     echo "user typed yes\n";
     * } else {
     *     echo "user typed no\n";
     * }
     * ```
     *
     * @param string $message to print out before waiting for user input
     * @param bool $default this value is returned if no selection is made.
     * @return bool whether user confirmed
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
     * Gives the user an option to choose from. Giving '?' as an input will show
     * a list of options to choose from and their explanations.
     *
     * @param string $prompt the prompt message
     * @param array $options Key-value array of options to choose from
     *
     * @return string An option character the user chose
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
     * Starts display of a progress bar on screen.
     *
     * This bar will be updated by [[updateProgress()]] and my be ended by [[endProgress()]].
     *
     * The following example shows a simple usage of a progress bar:
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
     * Git clone like progress (showing only status information):
     * ```php
     * Console::startProgress(0, 1000, 'Counting objects: ', false);
     * for ($n = 1; $n <= 1000; $n++) {
     *     usleep(1000);
     *     Console::updateProgress($n, 1000);
     * }
     * Console::endProgress("done." . PHP_EOL);
     * ```
     *
     * @param int $done the number of items that are completed.
     * @param int $total the total value of items that are to be done.
     * @param string $prefix an optional string to display before the progress bar.
     * Default to empty string which results in no prefix to be displayed.
     * @param int|bool $width optional width of the progressbar. This can be an integer representing
     * the number of characters to display for the progress bar or a float between 0 and 1 representing the
     * percentage of screen with the progress bar may take. It can also be set to false to disable the
     * bar and only show progress information like percent, number of items and ETA.
     * If not set, the bar will be as wide as the screen. Screen size will be detected using [[getScreenSize()]].
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
     * Updates a progress bar that has been started by [[startProgress()]].
     *
     * @param int $done the number of items that are completed.
     * @param int $total the total value of items that are to be done.
     * @param string $prefix an optional string to display before the progress bar.
     * Defaults to null meaning the prefix specified by [[startProgress()]] will be used.
     * If prefix is specified it will update the prefix that will be used by later calls.
     * @see startProgress
     * @see endProgress
     */
    public static function updateProgress($done, $total, $prefix = null)
    {
        $width = self::$_progressWidth;
        if ($width === false) {
            $width = 0;
        } else {
            $screenSize = static::getScreenSize(true);
            if ($screenSize === false && $width < 1) {
                $width = 0;
            } elseif ($width === null) {
                $width = $screenSize[0];
            } elseif ($width > 0 && $width < 1) {
                $width = floor($screenSize[0] * $width);
            }
        }
        if ($prefix === null) {
            $prefix = self::$_progressPrefix;
        } else {
            self::$_progressPrefix = $prefix;
        }
        $width -= static::ansiStrlen($prefix);

        $percent = ($total == 0) ? 1 : $done / $total;
        $info = sprintf('%d%% (%d/%d)', $percent * 100, $done, $total);

        if ($done > $total || $done == 0) {
            self::$_progressEta = null;
            self::$_progressEtaLastUpdate = time();
        } elseif ($done < $total) {
            // update ETA once per second to avoid flapping
            if (time() - self::$_progressEtaLastUpdate > 1 && $done > self::$_progressEtaLastDone) {
                $rate = (time() - (self::$_progressEtaLastUpdate ?: self::$_progressStart)) / ($done - self::$_progressEtaLastDone);
                self::$_progressEta = $rate * ($total - $done);
                self::$_progressEtaLastUpdate = time();
                self::$_progressEtaLastDone = $done;
            }
        }
        if (self::$_progressEta === null) {
            $info .= ' ETA: n/a';
        } else {
            $info .= sprintf(' ETA: %d sec.', self::$_progressEta);
        }

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
     * Ends a progress bar that has been started by [[startProgress()]].
     *
     * @param string|bool $remove This can be `false` to leave the progress bar on screen and just print a newline.
     * If set to `true`, the line of the progress bar will be cleared. This may also be a string to be displayed instead
     * of the progress bar.
     * @param bool $keepPrefix whether to keep the prefix that has been specified for the progressbar when progressbar
     * gets removed. Defaults to true.
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
}
