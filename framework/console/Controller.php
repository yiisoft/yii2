<?php
/**
 * Controller class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\base\Action;
use yii\base\Exception;

/**
 * Controller is the base class of console command classes.
 *
 * A controller consists of one or several actions known as sub-commands.
 * Users call a console command by specifying the corresponding route which identifies a controller action.
 * The `yiic` program is used when calling a console command, like the following:
 *
 * ~~~
 * yiic <route> [--param1=value1 --param2 ...]
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
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

	public $color = null;

	/**
	 * This method is invoked when the request parameters do not satisfy the requirement of the specified action.
	 * The default implementation will throw an exception.
	 * @param Action $action the action being executed
	 * @param Exception $exception the exception about the invalid parameters
	 */
	public function invalidActionParams($action, $exception)
	{
		echo \Yii::t('yii', 'Error: {message}', array(
			'{message}' => $exception->getMessage(),
		));
		\Yii::$application->end(1);
	}

	/**
	 * This method is invoked when extra parameters are provided to an action while it is executed.
	 * @param Action $action the action being executed
	 * @param array $expected the expected action parameters (name => value)
	 * @param array $actual the actual action parameters (name => value)
	 */
	public function extraActionParams($action, $expected, $actual)
	{
		unset($expected['args'], $actual['args']);

		$keys = array_diff(array_keys($actual), array_keys($expected));
		if (!empty($keys)) {
			echo \Yii::t('yii', 'Error: Unknown parameter(s): {params}', array(
				'{params}' => implode(', ', $keys),
			)) . "\n";
			\Yii::$application->end(1);
		}
	}

	/**
	 * Reads input via the readline PHP extension if that's available, or fgets() if readline is not installed.
	 *
	 * @param string $message to echo out before waiting for user input
	 * @param string $default the default string to be returned when user does not write anything.
	 * Defaults to null, means that default string is disabled.
	 * @return mixed line read as a string, or false if input has been closed
	 */
	public function prompt($message, $default = null)
	{
		if($default !== null) {
			$message .= " [$default] ";
		}
		else {
			$message .= ' ';
		}

		if(extension_loaded('readline')) {
			$input = readline($message);
			if($input !== false) {
				readline_add_history($input);
			}
		}
		else {
			echo $message;
			$input = fgets(STDIN);
		}

		if($input === false) {
			return false;
		}
		else {
			$input = trim($input);
			return ($input === '' && $default !== null) ? $default : $input;
		}
	}

	/**
	 * Asks user to confirm by typing y or n.
	 *
	 * @param string $message to echo out before waiting for user input
	 * @param boolean $default this value is returned if no selection is made.
	 * @return boolean whether user confirmed
	 */
	public function confirm($message, $default = false)
	{
		echo $message . ' (yes|no) [' . ($default ? 'yes' : 'no') . ']:';

		$input = trim(fgets(STDIN));
		return empty($input) ? $default : !strncasecmp($input, 'y', 1);
	}

	/**
	 * Moves the terminal cursor up by sending ANSI code CUU to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $rows number of rows the cursor should be moved up
	 */
	public function moveCursorUp($rows=1)
	{
		echo "\033[" . (int) $rows . 'A';
	}

	/**
	 * Moves the terminal cursor down by sending ANSI code CUD to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $rows number of rows the cursor should be moved down
	 */
	public function moveCursorDown($rows=1)
	{
		echo "\033[" . (int) $rows . 'B';
	}

	/**
	 * Moves the terminal cursor forward by sending ANSI code CUF to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $steps number of steps the cursor should be moved forward
	 */
	public function moveCursorForward($steps=1)
	{
		echo "\033[" . (int) $steps . 'C';
	}

	/**
	 * Moves the terminal cursor backward by sending ANSI code CUB to the terminal.
	 * If the cursor is already at the edge of the screen, this has no effect.
	 * @param integer $steps number of steps the cursor should be moved backward
	 */
	public function moveCursorBackward($steps=1)
	{
		echo "\033[" . (int) $steps . 'D';
	}

	/**
	 * Moves the terminal cursor to the beginning of the next line by sending ANSI code CNL to the terminal.
	 * @param integer $lines number of lines the cursor should be moved down
	 */
	public function moveCursorNextLine($lines=1)
	{
		echo "\033[" . (int) $lines . 'E';
	}

	/**
	 * Moves the terminal cursor to the beginning of the previous line by sending ANSI code CPL to the terminal.
	 * @param integer $lines number of lines the cursor should be moved up
	 */
	public function moveCursorPrevLine($lines=1)
	{
		echo "\033[" . (int) $lines . 'F';
	}

	/**
	 * Moves the cursor to an absolute position given as column and row by sending ANSI code CUP or CHA to the terminal.
	 *
	 * @param integer $column 1-based column number, 1 is the left edge of the screen.
	 * @param integer|null $row 1-based row number, 1 is the top edge of the screen. if not set, will move cursor only in current line.
	 */
	public function moveCursorTo($column, $row=null)
	{
		if ($row === null) {
			echo "\033[" . (int) $column . 'G';
		} else {
			echo "\033[" . (int) $row . ';' . (int) $column . 'H';
		}
	}

	/**
	 * Scrolls whole page up by sending ANSI code SU to the terminal.
	 * New lines are added at the bottom. This is not supported by ANSI.SYS
	 * @param int $lines number of lines to scroll up
	 */
	public function scrollUp($lines=1)
	{
		echo "\033[".(int)$lines."S";
	}

	/**
	 * Scrolls whole page down by sending ANSI code SD to the terminal.
	 * New lines are added at the top. This is not supported by ANSI.SYS
	 * @param int $lines number of lines to scroll down
	 */
	public function scrollDown($lines=1)
	{
		echo "\033[".(int)$lines."T";
	}

	/**
	 * Saves the current cursor position by sending ANSI code SCP to the terminal.
	 * Position can then be restored with {@link restoreCursorPosition}
	 */
	public function saveCursorPosition()
	{
		echo "\033[s";
	}

	/**
	 * Restores the cursor position saved with {@link saveCursorPosition} by sending ANSI code RCP to the terminal.
	 */
	public function restoreCursorPosition()
	{
		echo "\033[u";
	}

	/**
	 * Hides the cursor by sending ANSI by sending ANSI DECTCEM code ?25l to the terminal.
	 * Use {@link showCursor} to bring it back.
	 */
	public function hideCursor()
	{
		echo "\033[?25l";
	}

	/**
	 * Will show a cursor again when it has been hidden by {@link hideCursor}  by sending ANSI DECTCEM code ?25h to the terminal.
	 */
	public function showCursor()
	{
		echo "\033[?25h";
	}

	/**
	 * clears entire screen content by sending ANSI code ED with argument 2 to the terminal
	 * Cursor position will not be changed (ANSI.SYS implementation used in windows will reset cursor position to upper left corner of the screen).
	 */
	public function clearScreen()
	{
		echo "\033[2J";
	}

	/**
	 * clears text from cursor to the beginning of the screen by sending ANSI code ED with argument 1 to the terminal
	 * Cursor position will not be changed.
	 */
	public function clearScreenBeforeCursor()
	{
		echo "\033[1J";
	}

	/**
	 * clears text from cursor to the end of the screen by sending ANSI code ED with argument 0 to the terminal
	 * Cursor position will not be changed.
	 */
	public function clearScreenAfterCursor()
	{
		echo "\033[0J";
	}


	/**
	 * clears entire screen content by sending ANSI code EL with argument 2 to the terminal
	 * Cursor position will not be changed.
	 */
	public function clearLine()
	{
		echo "\033[2K";
	}

	/**
	 * clears text from cursor to the beginning of the screen by sending ANSI code EL with argument 1 to the terminal
	 * Cursor position will not be changed.
	 */
	public function clearLineBeforeCursor()
	{
		echo "\033[1K";
	}

	/**
	 * clears text from cursor to the end of the screen by sending ANSI code EL with argument 0 to the terminal
	 * Cursor position will not be changed.
	 */
	public function clearLineAfterCursor()
	{
		echo "\033[0K";
	}






	//const COLOR_XTERM256 = 38;// http://en.wikipedia.org/wiki/Talk:ANSI_escape_code#xterm-256colors
	public function xtermColor($i) {

	}







	/**
	 * This method will turn given string into one colorized with ansi color
	 */
	public function colorize($string, $foreground = null, $background = null, $style = null)
	{
		$codes = array();
		if ($foreground !== null) {
			$codes[] = static::FOREGROUND_COLOR + $foreground;
		}
		if ($background !== null) {
			$codes[] = static::BACKGROUND_COLOR + $background;
		}
		if ($style !== null) {
			$codes[] = $style;
		}

		$code = implode(';', $codes);
		return "\033[0m" . ($code !== '' ? "\033[" . $code . "m" : '') . $string . "\033[0m";
	}


	public function style($code)
	{
		return "\033[{$code}m";
	}

	public function color($foreground, $background=null)
	{
		if ($foreground === null && $background === null) {
			return '';
		}

		$codes = array();
		if ($foreground !== null) {
			$codes[] = static::FOREGROUND_COLOR + $foreground;
		}
		if ($background !== null) {
			$codes[] = static::BACKGROUND_COLOR + $background;
		}

		return "\033[" . implode(';', $codes) . "m";
	}

	public function reset()
	{
		return "\033[0m";
	}


	public function renderColoredString($string)
	{

	}


	// TODO refactor and review
	public function ansiToHtml($string)
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
				$styleA = \yii\util\ArrayHelper::merge($styleA, $style);
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

}