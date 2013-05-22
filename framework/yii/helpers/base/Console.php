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
 * The following constants are available for formatting:
 *
 * TODO document constants
 *
 *
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
	 * Sets the ANSI format for any text that is printed afterwards.
	 *
	 * You can pass any of the FG_*, BG_* and TEXT_* constants and also [[xterm256ColorFg]] and [[xterm256ColorBg]].
	 * TODO: documentation
	 */
	public static function ansiFormatBegin()
	{
		echo "\033[" . implode(';', func_get_args()) . 'm';
	}

	/**
	 * Resets any ANSI format set by previous method [[ansiFormatBegin()]]
	 * Any output after this is will have default text style.
	 */
	public static function ansiFormatReset()
	{
		echo "\033[0m";
	}

	/**
	 * Returns the ANSI format code.
	 *
	 * You can pass any of the FG_*, BG_* and TEXT_* constants and also [[xterm256ColorFg]] and [[xterm256ColorBg]].
	 * TODO: documentation
	 */
	public static function ansiFormatCode($format)
	{
		return "\033[" . implode(';', $format) . 'm';
	}

	/**
	 * Will return a string formatted with the given ANSI style
	 *
	 * @param string $string the string to be formatted
	 * @param array $format array containing formatting values.
	 * You can pass any of the FG_*, BG_* and TEXT_* constants and also [[xterm256ColorFg]] and [[xterm256ColorBg]].
	 * @return string
	 */
	public static function ansiFormat($string, $format=array())
	{
		$code = implode(';', $format);
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
	 * Strips ANSI control codes from a string
	 *
	 * @param string $string String to strip
	 * @return string
	 */
	public static function stripAnsiFormat($string)
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
						case self::FG_BLACK:
							$style = array('color' => '#000000');
							break;
						case self::FG_BLUE:
							$style = array('color' => '#000078');
							break;
						case self::FG_CYAN:
							$style = array('color' => '#007878');
							break;
						case self::FG_GREEN:
							$style = array('color' => '#007800');
							break;
						case self::FG_GREY:
							$style = array('color' => '#787878');
							break;
						case self::FG_PURPLE:
							$style = array('color' => '#780078');
							break;
						case self::FG_RED:
							$style = array('color' => '#780000');
							break;
						case self::FG_YELLOW:
							$style = array('color' => '#787800');
							break;
						case self::BG_BLACK:
							$style = array('background-color' => '#000000');
							break;
						case self::BG_BLUE:
							$style = array('background-color' => '#000078');
							break;
						case self::BG_CYAN:
							$style = array('background-color' => '#007878');
							break;
						case self::BG_GREEN:
							$style = array('background-color' => '#007800');
							break;
						case self::BG_GREY:
							$style = array('background-color' => '#787878');
							break;
						case self::BG_PURPLE:
							$style = array('background-color' => '#780078');
							break;
						case self::BG_RED:
							$style = array('background-color' => '#780000');
							break;
						case self::BG_YELLOW:
							$style = array('background-color' => '#787800');
							break;
						case self::BOLD:
							$style = array('font-weight' => 'bold');
							break;
						case self::ITALIC:
							$style = array('font-style' => 'italic');
							break;
						case self::UNDERLINE:
							$style = array('text-decoration' => array('underline'));
							break;
						case self::OVERLINED:
							$style = array('text-decoration' => array('overline'));
							break;
						case self::CROSSED_OUT:
							$style = array('text-decoration' => array('line-through'));
							break;
						case self::BLINK:
							$style = array('text-decoration' => array('blink'));
							break;
						case self::NEGATIVE: // ???
						case self::CONCEALED:
						case self::ENCIRCLED:
						case self::FRAMED:
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

	public function markdownToAnsi()
	{
		// TODO implement
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
		static $conversions = array(
			'%y' => array(self::FG_YELLOW),
			'%g' => array(self::FG_GREEN),
			'%b' => array(self::FG_BLUE),
			'%r' => array(self::FG_RED),
			'%p' => array(self::FG_PURPLE),
			'%m' => array(self::FG_PURPLE),
			'%c' => array(self::FG_CYAN),
			'%w' => array(self::FG_GREY),
			'%k' => array(self::FG_BLACK),
			'%n' => array(0), // reset
			'%Y' => array(self::FG_YELLOW, self::BOLD),
			'%G' => array(self::FG_GREEN, self::BOLD),
			'%B' => array(self::FG_BLUE, self::BOLD),
			'%R' => array(self::FG_RED, self::BOLD),
			'%P' => array(self::FG_PURPLE, self::BOLD),
			'%M' => array(self::FG_PURPLE, self::BOLD),
			'%C' => array(self::FG_CYAN, self::BOLD),
			'%W' => array(self::FG_GREY, self::BOLD),
			'%K' => array(self::FG_BLACK, self::BOLD),
			'%N' => array(0, self::BOLD),
			'%3' => array(self::BG_YELLOW),
			'%2' => array(self::BG_GREEN),
			'%4' => array(self::BG_BLUE),
			'%1' => array(self::BG_RED),
			'%5' => array(self::BG_PURPLE),
			'%6' => array(self::BG_PURPLE),
			'%7' => array(self::BG_CYAN),
			'%0' => array(self::BG_GREY),
			'%F' => array(self::BLINK),
			'%U' => array(self::UNDERLINE),
			'%8' => array(self::NEGATIVE),
			'%9' => array(self::BOLD),
			'%_' => array(self::BOLD)
		);

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
	 * Gets input from STDIN and returns a string right-trimmed for EOLs.
	 *
	 * @param bool $raw If set to true, returns the raw string without trimming
	 * @return string
	 */
	public static function stdin($raw = false)
	{
		return $raw ? fgets(STDIN) : rtrim(fgets(STDIN), PHP_EOL);
	}

	/**
	 * Prints a string to STDOUT.
	 *
	 * @param string $string the string to print
	 * @return int|boolean Number of bytes printed or false on error
	 */
	public static function stdout($string)
	{
		return fwrite(STDOUT, $string);
	}

	/**
	 * Prints a string to STDERR.
	 *
	 * @param string $string the string to print
	 * @return int|boolean Number of bytes printed or false on error
	 */
	public static function stderr($string)
	{
		return fwrite(STDERR, $string);
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
	public static function output($text = null)
	{
		return static::stdout($text . PHP_EOL);
	}

	/**
	 * Prints text to STDERR appended with a carriage return (PHP_EOL).
	 *
	 * @param string $text
	 * @param bool   $raw
	 *
	 * @return mixed Number of bytes printed or false on error
	 */
	public static function error($text = null)
	{
		return static::stderr($text . PHP_EOL);
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
	 * Asks user to confirm by typing y or n.
	 *
	 * @param string $message to echo out before waiting for user input
	 * @param boolean $default this value is returned if no selection is made.
	 * @return boolean whether user confirmed
	 */
	public static function confirm($message, $default = true)
	{
		echo $message . ' (yes|no) [' . ($default ? 'yes' : 'no') . ']:';
		$input = trim(static::stdin());
		return empty($input) ? $default : !strncasecmp($input, 'y', 1);
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
	 * @param integer $done the number of items that are completed
	 * @param integer $total the total value of items that are to be done
	 * @param integer $size the size of the status bar (optional)
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
