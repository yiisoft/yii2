<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

/**
 * TODO adjust phpdoc
 * TODO test this on all kinds of terminals, especially windows (check out lib ncurses)
 *
 * Console View is the base class for console view components
 *
 * A console view provides functionality to create rich console application by allowing to format output
 * by adding color and font style to it.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Console
{
	const FG_BLACK  = 30;
	const FG_RED    = 31;
	const FG_GREEN  = 32;
	const FG_YELLOW = 33;
	const FG_BLUE   = 34;
	const FG_PURPLE = 35;
	const FG_CYAN   = 36;
	const FG_GREY   = 37;

	const BG_BLACK  = 40;
	const BG_RED    = 41;
	const BG_GREEN  = 42;
	const BG_YELLOW = 43;
	const BG_BLUE   = 44;
	const BG_PURPLE = 45;
	const BG_CYAN   = 46;
	const BG_GREY   = 47;

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
	 * @param integer $rows number of rows the cursor should be moved up
	 */
	public static function moveCursorUp($rows = 1)
	{
		echo "\033[" . (int)$rows . 'A';
	}

	/**
	 * Moves the terminal cursor down by sending ANSI control code CUD to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $rows number of rows the cursor should be moved down
	 */
	public static function moveCursorDown($rows = 1)
	{
		echo "\033[" . (int)$rows . 'B';
	}

	/**
	 * Moves the terminal cursor forward by sending ANSI control code CUF to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $steps number of steps the cursor should be moved forward
	 */
	public static function moveCursorForward($steps = 1)
	{
		echo "\033[" . (int)$steps . 'C';
	}

	/**
	 * Moves the terminal cursor backward by sending ANSI control code CUB to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $steps number of steps the cursor should be moved backward
	 */
	public static function moveCursorBackward($steps = 1)
	{
		echo "\033[" . (int)$steps . 'D';
	}

	/**
	 * Moves the terminal cursor to the beginning of the next line by sending ANSI control code CNL to the terminal.
	 * @param integer $lines number of lines the cursor should be moved down
	 */
	public static function moveCursorNextLine($lines = 1)
	{
		echo "\033[" . (int)$lines . 'E';
	}

	/**
	 * Moves the terminal cursor to the beginning of the previous line by sending ANSI control code CPL to the terminal.
	 * @param integer $lines number of lines the cursor should be moved up
	 */
	public static function moveCursorPrevLine($lines = 1)
	{
		echo "\033[" . (int)$lines . 'F';
	}

	/**
	 * Moves the cursor to an absolute position given as column and row by sending ANSI control code CUP or CHA to the terminal.
	 * @param integer $column 1-based column number, 1 is the left edge of the screen.
	 * @param integer|null $row 1-based row number, 1 is the top edge of the screen. if not set, will move cursor only in current line.
	 */
	public static function moveCursorTo($column, $row = null)
	{
		if ($row === null) {
			echo "\033[" . (int)$column . 'G';
		} else {
			echo "\033[" . (int)$row . ';' . (int)$column . 'H';
		}
	}

	/**
	 * Scrolls whole page up by sending ANSI control code SU to the terminal.
	 * New lines are added at the bottom. This is not supported by ANSI.SYS used in windows.
	 * @param int $lines number of lines to scroll up
	 */
	public static function scrollUp($lines = 1)
	{
		echo "\033[" . (int)$lines . "S";
	}

	/**
	 * Scrolls whole page down by sending ANSI control code SD to the terminal.
	 * New lines are added at the top. This is not supported by ANSI.SYS used in windows.
	 * @param int $lines number of lines to scroll down
	 */
	public static function scrollDown($lines = 1)
	{
		echo "\033[" . (int)$lines . "T";
	}

	/**
	 * Saves the current cursor position by sending ANSI control code SCP to the terminal.
	 * Position can then be restored with {@link restoreCursorPosition}.
	 */
	public static function saveCursorPosition()
	{
		echo "\033[s";
	}

	/**
	 * Restores the cursor position saved with {@link saveCursorPosition} by sending ANSI control code RCP to the terminal.
	 */
	public static function restoreCursorPosition()
	{
		echo "\033[u";
	}

	/**
	 * Hides the cursor by sending ANSI DECTCEM code ?25l to the terminal.
	 * Use {@link showCursor} to bring it back.
	 * Do not forget to show cursor when your application exits. Cursor might stay hidden in terminal after exit.
	 */
	public static function hideCursor()
	{
		echo "\033[?25l";
	}

	/**
	 * Will show a cursor again when it has been hidden by {@link hideCursor}  by sending ANSI DECTCEM code ?25h to the terminal.
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
	 * Will send ANSI format for following output
	 *
	 * You can pass any of the FG_*, BG_* and TEXT_* constants and also xterm256ColorBg
	 * TODO: documentation
	 */
	public static function ansiStyle()
	{
		echo "\033[" . implode(';', func_get_args()) . 'm';
	}

	/**
	 * Will return a string formatted with the given ANSI style
	 *
	 * See {@link ansiStyle} for possible arguments.
	 * @param string $string the string to be formatted
	 * @return string
	 */
	public static function ansiStyleString($string)
	{
		$args = func_get_args();
		array_shift($args);
		$code = implode(';', $args);
		return "\033[0m" . ($code !== '' ? "\033[" . $code . "m" : '') . $string . "\033[0m";
	}

	//const COLOR_XTERM256 = 38;// http://en.wikipedia.org/wiki/Talk:ANSI_escape_code#xterm-256colors
	public static function xterm256ColorFg($i) // TODO naming!
	{
		return '38;5;' . $i;
	}

	public static function xterm256ColorBg($i) // TODO naming!
	{
		return '48;5;' . $i;
	}

	/**
	 * Usage: list($w, $h) = ConsoleHelper::getScreenSize();
	 *
	 * @return array
	 */
	public static function getScreenSize()
	{
		// TODO implement
		return array(150, 50);
	}

	/**
	 * resets any ansi style set by previous method {@link ansiStyle}
	 * Any output after this is will have default text style.
	 */
	public static function reset()
	{
		echo "\033[0m";
	}

	/**
	 * Strips ANSI control codes from a string
	 *
	 * @param string $string String to strip
	 * @return string
	 */
	public static function strip($string)
	{
		return preg_replace('/\033\[[\d;]+m/', '', $string); // TODO currently only strips color
	}

	// TODO refactor and review
	public static function ansiToHtml($string)
	{
		$tags = 0;
		return preg_replace_callback(
			'/\033\[[\d;]+m/',
			function ($ansi) use (&$tags) {
				$styleA = array();
				foreach (explode(';', $ansi) as $controlCode) {
					switch ($controlCode) {
						case static::FG_BLACK:
							$style = array('color' => '#000000');
							break;
						case static::FG_BLUE:
							$style = array('color' => '#000078');
							break;
						case static::FG_CYAN:
							$style = array('color' => '#007878');
							break;
						case static::FG_GREEN:
							$style = array('color' => '#007800');
							break;
						case static::FG_GREY:
							$style = array('color' => '#787878');
							break;
						case static::FG_PURPLE:
							$style = array('color' => '#780078');
							break;
						case static::FG_RED:
							$style = array('color' => '#780000');
							break;
						case static::FG_YELLOW:
							$style = array('color' => '#787800');
							break;
						case static::BG_BLACK:
							$style = array('background-color' => '#000000');
							break;
						case static::BG_BLUE:
							$style = array('background-color' => '#000078');
							break;
						case static::BG_CYAN:
							$style = array('background-color' => '#007878');
							break;
						case static::BG_GREEN:
							$style = array('background-color' => '#007800');
							break;
						case static::BG_GREY:
							$style = array('background-color' => '#787878');
							break;
						case static::BG_PURPLE:
							$style = array('background-color' => '#780078');
							break;
						case static::BG_RED:
							$style = array('background-color' => '#780000');
							break;
						case static::BG_YELLOW:
							$style = array('background-color' => '#787800');
							break;
						case static::BOLD:
							$style = array('font-weight' => 'bold');
							break;
						case static::ITALIC:
							$style = array('font-style' => 'italic');
							break;
						case static::UNDERLINE:
							$style = array('text-decoration' => array('underline'));
							break;
						case static::OVERLINED:
							$style = array('text-decoration' => array('overline'));
							break;
						case static::CROSSED_OUT:
							$style = array('text-decoration' => array('line-through'));
							break;
						case static::BLINK:
							$style = array('text-decoration' => array('blink'));
							break;
						case static::NEGATIVE: // ???
						case static::CONCEALED:
						case static::ENCIRCLED:
						case static::FRAMED:
							// TODO allow resetting codes
							break;
						case 0: // ansi reset
							$return = '';
							for ($n = $tags; $tags > 0; $tags--) {
								$return .= '</span>';
							}
							return $return;
					}

					$styleA = ArrayHelper::merge($styleA, $style);
				}
				$styleString[] = array();
				foreach ($styleA as $name => $content) {
					if ($name === 'text-decoration') {
						$content = implode(' ', $content);
					}
					$styleString[] = $name . ':' . $content;
				}
				$tags++;
				return '<span' . (!empty($styleString) ? 'style="' . implode(';', $styleString) : '') . '>';
			},
			$string
		);
	}

	/**
	 *
	 * Returns an ANSI-Controlcode
	 *
	 * Takes 1 to 3 Arguments: either 1 to 3 strings containing the name of the
	 * FG Color, style and BG color, or one array with the indices color, style
	 * or background.
	 *
	 * @param mixed  $color      Optional.
	 *                           Either a string with the name of the foreground
	 *                           color, or an array with the indices 'color',
	 *                           'style', 'background' and corresponding names as
	 *                           values.
	 * @param string $style      Optional name of the style
	 * @param string $background Optional name of the background color
	 *
	 * @return string
	 */
	public function color($color = null, $style = null, $background = null) // {{{
	{
		$colors = static::getColorCodes();

		if (is_array($color)) {
			$style      = isset($color['style']) ? $color['style'] : null;
			$background = isset($color['background']) ? $color['background'] : null;
			$color      = isset($color['color']) ? $color['color'] : null;
		}

		if ($color == 'reset') {
			return "\033[0m";
		}

		$code = array();
		if (isset($style)) {
			$code[] = $colors['style'][$style];
		}

		if (isset($color)) {
			$code[] = $colors['color'][$color];
		}

		if (isset($background)) {
			$code[] = $colors['background'][$background];
		}

		if (empty($code)) {
			$code[] = 0;
		}
		$code = implode(';', $code);
		return "\033[{$code}m";
	}

	/**
	 * TODO syntax copied from https://github.com/pear/Console_Color2/blob/master/Console/Color2.php
	 *
	 * Converts colorcodes in the format %y (for yellow) into ansi-control
	 * codes. The conversion table is: ('bold' meaning 'light' on some
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
	 * colorcodes will just be removed (And %% will be transformed into %)
	 *
	 * @param string $string  String to convert
	 * @param bool   $colored Should the string be colored?
	 *
	 * @return string
	 */
	public static function renderColoredString($string, $colored = true)
	{
		static $conversions = array( // static so the array doesn't get built
			// everytime
			// %y - yellow, and so on... {{{
			'%y' => array('color' => 'yellow'),
			'%g' => array('color' => 'green'),
			'%b' => array('color' => 'blue'),
			'%r' => array('color' => 'red'),
			'%p' => array('color' => 'purple'),
			'%m' => array('color' => 'purple'),
			'%c' => array('color' => 'cyan'),
			'%w' => array('color' => 'grey'),
			'%k' => array('color' => 'black'),
			'%n' => array('color' => 'reset'),
			'%Y' => array('color' => 'yellow', 'style' => 'light'),
			'%G' => array('color' => 'green', 'style' => 'light'),
			'%B' => array('color' => 'blue', 'style' => 'light'),
			'%R' => array('color' => 'red', 'style' => 'light'),
			'%P' => array('color' => 'purple', 'style' => 'light'),
			'%M' => array('color' => 'purple', 'style' => 'light'),
			'%C' => array('color' => 'cyan', 'style' => 'light'),
			'%W' => array('color' => 'grey', 'style' => 'light'),
			'%K' => array('color' => 'black', 'style' => 'light'),
			'%N' => array('color' => 'reset', 'style' => 'light'),
			'%3' => array('background' => 'yellow'),
			'%2' => array('background' => 'green'),
			'%4' => array('background' => 'blue'),
			'%1' => array('background' => 'red'),
			'%5' => array('background' => 'purple'),
			'%6' => array('background' => 'cyan'),
			'%7' => array('background' => 'grey'),
			'%0' => array('background' => 'black'),
			// Don't use this, I can't stand flashing text
			'%F' => array('style' => 'blink'),
			'%U' => array('style' => 'underline'),
			'%8' => array('style' => 'inverse'),
			'%9' => array('style' => 'bold'),
			'%_' => array('style' => 'bold')
			// }}}
		);

		if ($colored) {
			$string = str_replace('%%', '% ', $string);
			foreach ($conversions as $key => $value) {
				$string = str_replace(
					$key,
					static::color($value),
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
	 * Returns the different foreground and background color codes and styles available.
	 * @return array the color codes
	 */
	public static function getColorCodes()
	{
		return array(
			'color'      => array(
				'black'  => static::FG_BLACK,
				'red'    => static::FG_RED,
				'green'  => static::FG_GREEN,
				'yellow' => static::FG_YELLOW,
				'blue'   => static::FG_BLUE,
				'purple' => static::FG_PURPLE,
				'cyan'   => static::FG_CYAN,
				'grey'   => static::FG_GREY,
			),
			'style'      => array(
				'normal'      => static::NORMAL,
				'bold'        => static::BOLD,
				'italic'      => static::ITALIC,
				'underline'   => static::UNDERLINE,
				'blink'       => static::BLINK,
				'negative'    => static::NEGATIVE,
				'concealed'   => static::CONCEALED,
				'crossed_out' => static::CROSSED_OUT,
				'framed'      => static::FRAMED,
				'encircled'   => static::ENCIRCLED,
				'overlined'   => static::OVERLINED
			),
			'background' => array(
				'black'  => static::BG_BLACK,
				'red'    => static::BG_RED,
				'green'  => static::BG_RED,
				'yellow' => static::BG_YELLOW,
				'blue'   => static::BG_BLUE,
				'purple' => static::BG_PURPLE,
				'cyan'   => static::BG_CYAN,
				'grey'   => static::BG_GREY
			)
		);
	}

	/**
	 * Escapes % so they don't get interpreted as color codes
	 *
	 * @param string $string String to escape
	 *
	 * @access public
	 * @return string
	 */
	public static function escape($string)
	{
		return str_replace('%', '%%', $string);
	}

	/**
	 * Returns true if the stream supports colorization. ANSI colors is disabled if not supported by the stream.
	 *
	 * - windows without asicon
	 * - not tty consoles
	 *
	 * @param mixed $stream
	 * @return bool true if the stream supports ANSI colors, otherwise false.
	 */
	public static function streamSupportsAnsiColors($stream)
	{
		return DIRECTORY_SEPARATOR == '\\'
			? null !== getenv('ANSICON')
			: function_exists('posix_isatty') && @posix_isatty($stream);
	}

	/**
	 * Returns true if the console is running on windows
	 * @return bool
	 */
	public static function isRunningOnWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Gets input from STDIN and returns a string right-trimmed for EOLs.
	 *
	 * @param bool $raw If set to true, returns the raw string without trimming
	 *
	 * @return string
	 */
	public static function stdin($raw = false)
	{
		return $raw ? fgets(STDIN) : rtrim(fgets(STDIN), PHP_EOL);
	}

	/**
	 * Prints text to STDOUT.
	 *
	 * @param string $text
	 * @param bool   $raw
	 *
	 * @return int|false Number of bytes printed or false on error
	 */
	public static function stdout($text, $raw = false)
	{
		if ($raw) {
			return fwrite(STDOUT, $text);
		} elseif (static::streamSupportsAnsiColors(STDOUT)) {
			return fwrite(STDOUT, static::renderColoredString($text));
		} else {
			return fwrite(STDOUT, static::renderColoredString($text, false));
		}
	}

	/**
	 * Prints text to STDERR.
	 *
	 * @param string $text
	 * @param bool   $raw
	 *
	 * @return mixed Number of bytes printed or bool false on error.
	 */
	public static function stderr($text, $raw = false)
	{
		if ($raw) {
			return fwrite(STDERR, $text);
		} elseif (static::streamSupportsAnsiColors(STDERR)) {
			return fwrite(STDERR, static::renderColoredString($text));
		} else {
			return fwrite(STDERR, static::renderColoredString($text, false));
		}
	}

	/**
	 * Prints text to STDERR appended with a carriage return (PHP_EOL).
	 *
	 * @param string $text
	 * @param bool   $raw
	 *
	 * @return mixed Number of bytes printed or false on error
	 */
	public static function error($text = null, $raw = false)
	{
		return static::stderr($text . PHP_EOL, $raw);
	}

	/**
	 * Asks the user for input. Ends when the user types a carriage return (PHP_EOL). Optionally, It also provides a
	 * prompt.
	 *
	 * @param string $prompt the prompt (optional)
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
	 * @param string $text
	 * @param bool $raw
	 *
	 * @return mixed Number of bytes printed or bool false on error
	 */
	public static function output($text = null, $raw = false)
	{
		return static::stdout($text . PHP_EOL, $raw);
	}

	/**
	 * Prompts the user for input and validates it
	 *
	 * @param string $text prompt string
	 * @param array $options the options to validate the input:
	 *  - required: whether it is required or not
	 *  - default: default value if no input is inserted by the user
	 *  - pattern: regular expression pattern to validate user input
	 *  - validator: a callable function to validate input. The function must accept two parameters:
	 *      - $input: the user input to validate
	 *      - $error: the error value passed by reference if validation failed.
	 * @return string the user input
	 */
	public static function prompt($text, $options = array())
	{
		$options = ArrayHelper::merge(
			$options,
			array(
				'required'  => false,
				'default'   => null,
				'pattern'   => null,
				'validator' => null,
				'error'     => 'Invalid input.',
			)
		);
		$error   = null;

		top:
		$input = $options['default']
			? static::input("$text [" . $options['default'] . ']: ')
			: static::input("$text: ");

		if (!strlen($input)) {
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
			!call_user_func_array($options['validator'], array($input, &$error))
		) {
			static::output(isset($error) ? $error : $options['error']);
			goto top;
		}

		return $input;
	}

	/**
	 * Asks the user for a simple yes/no confirmation.
	 *
	 * @param string $prompt the prompt string
	 *
	 * @return bool true or false according to user input.
	 */
	public static function confirm($prompt)
	{
		top:
		$input = strtolower(static::input("$prompt [y/n]: "));
		if (!in_array(substr($input, 0, 1), array('y', 'n'))) {
			static::output("Please, type 'y' or 'n'");
			goto top;
		}
		return $input === 'y' ? true : false;
	}

	/**
	 * Gives the user an option to choose from. Giving '?' as an input will show
	 * a list of options to choose from and their explanations.
	 *
	 * @param string $prompt the prompt message
	 * @param array  $options Key-value array of options to choose from
	 *
	 * @return string An option character the user chose
	 */
	public static function select($prompt, $options = array())
	{
		top:
		static::stdout("$prompt [" . implode(',', array_keys($options)) . ",?]: ");
		$input = static::stdin();
		if ($input === '?') {
			foreach ($options as $key => $value) {
				echo " $key - $value\n";
			}
			echo " ? - Show help\n";
			goto top;
		} elseif (!in_array($input, array_keys($options))) {
			goto top;
		}
		return $input;
	}

	/**
	 * Displays and updates a simple progress bar on screen.
	 *
	 * @param $done the number of items that are completed
	 * @param $total the total value of items that are to be done
	 * @param int $size the size of the status bar (optional)
	 * @see http://snipplr.com/view/29548/
	 */
	public static function showProgress($done, $total, $size = 30)
	{
		static $start;

		// if we go over our bound, just ignore it
		if ($done > $total) {
			return;
		}

		if (empty($start)) {
			$start = time();
		}

		$now = time();

		$percent = (double)($done / $total);
		$bar     = floor($percent * $size);

		$status = "\r[";
		$status .= str_repeat("=", $bar);
		if ($bar < $size) {
			$status .= ">";
			$status .= str_repeat(" ", $size - $bar);
		} else {
			$status .= "=";
		}

		$display = number_format($percent * 100, 0);

		$status .= "] $display%  $done/$total";

		$rate = ($now - $start) / $done;
		$left = $total - $done;
		$eta  = round($rate * $left, 2);

		$elapsed = $now - $start;

		$status .= " remaining: " . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

		static::stdout("$status  ");

		flush();

		// when done, send a newline
		if ($done == $total) {
			echo "\n";
		}
	}
}
