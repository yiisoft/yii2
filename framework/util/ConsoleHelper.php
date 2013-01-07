<?php
/**
 * ConsoleHelper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

/**
 * ConsoleHelper provides additional unility functions for console applications.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Alexander Makarov <sam@rmcreative.ru>
 *
 * @since 2.0
 */
class ConsoleColor
{
	const FG_COLOR_BLACK = 30;
	const FG_COLOR_RED = 31;
	const FG_COLOR_GREEN = 32;
	const FG_COLOR_YELLOW = 33;
	const FG_COLOR_BLUE = 34;
	const FG_COLOR_PURPLE = 35;
	const FG_COLOR_CYAN = 36;
	const FG_COLOR_GREY = 37;

	const BG_COLOR_BLACK = 40;
	const BG_COLOR_RED = 41;
	const BG_COLOR_GREEN = 42;
	const BG_COLOR_YELLOW = 43;
	const BG_COLOR_BLUE = 44;
	const BG_COLOR_PURPLE = 45;
	const BG_COLOR_CYAN = 46;
	const BG_COLOR_GREY = 47;

	const TEXT_BOLD = 1;
	const TEXT_ITALIC = 3;
	const TEXT_UNDERLINE = 4;
	const TEXT_BLINK = 5;
	const TEXT_NEGATIVE = 7;
	const TEXT_CONCEALED = 8;
	const TEXT_CROSSED_OUT = 9;
	const TEXT_FRAMED = 51;
	const TEXT_ENCIRCLED = 52;
	const TEXT_OVERLINED = 53;

	/**
	 * Moves the terminal cursor up by sending ANSI code CUU to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $rows number of rows the cursor should be moved up
	 */
	public static function moveCursorUp($rows=1)
	{
		echo "\033[" . (int) $rows . 'A';
	}

	/**
	 * Moves the terminal cursor down by sending ANSI code CUD to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $rows number of rows the cursor should be moved down
	 */
	public static function moveCursorDown($rows=1)
	{
		echo "\033[" . (int) $rows . 'B';
	}

	/**
	 * Moves the terminal cursor forward by sending ANSI code CUF to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $steps number of steps the cursor should be moved forward
	 */
	public static function moveCursorForward($steps=1)
	{
		echo "\033[" . (int) $steps . 'C';
	}

	/**
	 * Moves the terminal cursor backward by sending ANSI code CUB to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $steps number of steps the cursor should be moved backward
	 */
	public static function moveCursorBackward($steps=1)
	{
		echo "\033[" . (int) $steps . 'D';
	}

	/**
	 * Moves the terminal cursor to the beginning of the next line by sending ANSI code CNL to the terminal.
	 * @param integer $lines number of lines the cursor should be moved down
	 */
	public static function moveCursorNextLine($lines=1)
	{
		echo "\033[" . (int) $lines . 'E';
	}

	/**
	 * Moves the terminal cursor to the beginning of the previous line by sending ANSI code CPL to the terminal.
	 * @param integer $lines number of lines the cursor should be moved up
	 */
	public static function moveCursorPrevLine($lines=1)
	{
		echo "\033[" . (int) $lines . 'F';
	}

	/**
	 * Moves the cursor to an absolute position given as column and row by sending ANSI code CUP or CHA to the terminal.
	 * @param integer $column 1-based column number, 1 is the left edge of the screen.
	 * @param integer|null $row 1-based row number, 1 is the top edge of the screen. if not set, will move cursor only in current line.
	 */
	public static function moveCursorTo($column, $row=null)
	{
		if ($row === null) {
			echo "\033[" . (int) $column . 'G';
		} else {
			echo "\033[" . (int) $row . ';' . (int) $column . 'H';
		}
	}

	/**
	 * Scrolls whole page up by sending ANSI code SU to the terminal.
	 * New lines are added at the bottom. This is not supported by ANSI.SYS used in windows.
	 * @param int $lines number of lines to scroll up
	 */
	public static function scrollUp($lines=1)
	{
		echo "\033[".(int)$lines."S";
	}

	/**
	 * Scrolls whole page down by sending ANSI code SD to the terminal.
	 * New lines are added at the top. This is not supported by ANSI.SYS used in windows.
	 * @param int $lines number of lines to scroll down
	 */
	public static function scrollDown($lines=1)
	{
		echo "\033[".(int)$lines."T";
	}

	/**
	 * Saves the current cursor position by sending ANSI code SCP to the terminal.
	 * Position can then be restored with {@link restoreCursorPosition}.
	 */
	public static function saveCursorPosition()
	{
		echo "\033[s";
	}

	/**
	 * Restores the cursor position saved with {@link saveCursorPosition} by sending ANSI code RCP to the terminal.
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
	 * Clears entire screen content by sending ANSI code ED with argument 2 to the terminal.
	 * Cursor position will not be changed.
	 * **Note:** ANSI.SYS implementation used in windows will reset cursor position to upper left corner of the screen.
	 */
	public static function clearScreen()
	{
		echo "\033[2J";
	}

	/**
	 * Clears text from cursor to the beginning of the screen by sending ANSI code ED with argument 1 to the terminal.
	 * Cursor position will not be changed.
	 */
	public static function clearScreenBeforeCursor()
	{
		echo "\033[1J";
	}

	/**
	 * Clears text from cursor to the end of the screen by sending ANSI code ED with argument 0 to the terminal.
	 * Cursor position will not be changed.
	 */
	public static function clearScreenAfterCursor()
	{
		echo "\033[0J";
	}


	/**
	 * Clears the line, the cursor is currently on by sending ANSI code EL with argument 2 to the terminal.
	 * Cursor position will not be changed.
	 */
	public static function clearLine()
	{
		echo "\033[2K";
	}

	/**
	 * Clears text from cursor position to the beginning of the line by sending ANSI code EL with argument 1 to the terminal.
	 * Cursor position will not be changed.
	 */
	public static function clearLineBeforeCursor()
	{
		echo "\033[1K";
	}

	/**
	 * Clears text from cursor position to the end of the line by sending ANSI code EL with argument 0 to the terminal.
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
		return "\033[0m" . ($code !== '' ? "\033[" . $code . "m" : '') . $string."\033[0m";
	}

	//const COLOR_XTERM256 = 38;// http://en.wikipedia.org/wiki/Talk:ANSI_escape_code#xterm-256colors
	public static function xterm256ColorFg($i) // TODO naming!
	{
		return '38;5;'.$i;
	}

	public static function xterm256ColorBg($i) // TODO naming!
	{
		return '48;5;'.$i;
	}

	/**
	 * Usage: list($w, $h) = ConsoleHelper::getScreenSize();
	 *
	 * @return array
	 */
	public static function getScreenSize()
	{
		// TODO implement
		return array(150,50);
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
		return preg_replace_callback('/\033\[[\d;]+m/', function($ansi) use (&$tags) {
			$styleA = array();
			foreach(explode(';', $ansi) as $controlCode)
			{
				switch($controlCode)
				{
					case static::FG_COLOR_BLACK:  $style = array('color' => '#000000'); break;
					case static::FG_COLOR_BLUE:   $style = array('color' => '#000078'); break;
					case static::FG_COLOR_CYAN:   $style = array('color' => '#007878'); break;
					case static::FG_COLOR_GREEN:  $style = array('color' => '#007800'); break;
					case static::FG_COLOR_GREY:   $style = array('color' => '#787878'); break;
					case static::FG_COLOR_PURPLE: $style = array('color' => '#780078'); break;
					case static::FG_COLOR_RED:    $style = array('color' => '#780000'); break;
					case static::FG_COLOR_YELLOW: $style = array('color' => '#787800'); break;
					case static::BG_COLOR_BLACK:  $style = array('background-color' => '#000000'); break;
					case static::BG_COLOR_BLUE:   $style = array('background-color' => '#000078'); break;
					case static::BG_COLOR_CYAN:   $style = array('background-color' => '#007878'); break;
					case static::BG_COLOR_GREEN:  $style = array('background-color' => '#007800'); break;
					case static::BG_COLOR_GREY:   $style = array('background-color' => '#787878'); break;
					case static::BG_COLOR_PURPLE: $style = array('background-color' => '#780078'); break;
					case static::BG_COLOR_RED:    $style = array('background-color' => '#780000'); break;
					case static::BG_COLOR_YELLOW: $style = array('background-color' => '#787800'); break;
					case static::TEXT_BOLD:       $style = array('font-weight' => 'bold'); break;
					case static::TEXT_ITALIC:     $style = array('font-style' => 'italic'); break;
					case static::TEXT_UNDERLINE:  $style = array('text-decoration' => array('underline')); break;
					case static::TEXT_OVERLINED:  $style = array('text-decoration' => array('overline')); break;
					case static::TEXT_CROSSED_OUT:$style = array('text-decoration' => array('line-through')); break;
					case static::TEXT_BLINK:      $style = array('text-decoration' => array('blink')); break;
					case static::TEXT_NEGATIVE:   // ???
					case static::TEXT_CONCEALED:
					case static::TEXT_ENCIRCLED:
					case static::TEXT_FRAMED:
					// TODO allow resetting codes
					break;
					case 0: // ansi reset
						$return = '';
						for($n=$tags; $tags>0; $tags--) {
							$return .= '</span>';
						}
						return $return;
				}

				$styleA = ArrayHelper::merge($styleA, $style);
			}
			$styleString[] = array();
			foreach($styleA as $name => $content) {
				if ($name = 'text-decoration') {
					$content = implode(' ', $content);
				}
				$styleString[] = $name.':'.$content;
			}
			$tags++;
			return '<span' . (!empty($styleString) ? 'style="' . implode(';', $styleString) : '') . '>';
		}, $string);
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
	public static function renderColoredString($string)
	{
		$colored = true;


		static $conversions = array ( // static so the array doesn't get built
		   // everytime
		// %y - yellow, and so on... {{{
		'%y' => array('color' => 'yellow'),
		'%g' => array('color' => 'green' ),
		'%b' => array('color' => 'blue'  ),
		'%r' => array('color' => 'red'   ),
		'%p' => array('color' => 'purple'),
		'%m' => array('color' => 'purple'),
		'%c' => array('color' => 'cyan'  ),
		'%w' => array('color' => 'grey'  ),
		'%k' => array('color' => 'black' ),
		'%n' => array('color' => 'reset' ),
		'%Y' => array('color' => 'yellow',  'style' => 'light'),
		'%G' => array('color' => 'green',   'style' => 'light'),
		'%B' => array('color' => 'blue',    'style' => 'light'),
		'%R' => array('color' => 'red',     'style' => 'light'),
		'%P' => array('color' => 'purple',  'style' => 'light'),
		'%M' => array('color' => 'purple',  'style' => 'light'),
		'%C' => array('color' => 'cyan',    'style' => 'light'),
		'%W' => array('color' => 'grey',    'style' => 'light'),
		'%K' => array('color' => 'black',   'style' => 'light'),
		'%N' => array('color' => 'reset',   'style' => 'light'),
		'%3' => array('background' => 'yellow'),
		'%2' => array('background' => 'green' ),
		'%4' => array('background' => 'blue'  ),
		'%1' => array('background' => 'red'   ),
		'%5' => array('background' => 'purple'),
		'%6' => array('background' => 'cyan'  ),
		'%7' => array('background' => 'grey'  ),
		'%0' => array('background' => 'black' ),
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
				$string = str_replace($key, Console_Color::color($value),
				$string);
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
}
