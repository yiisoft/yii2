<?php
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

/**
 * VarDumper is intended to replace the buggy PHP function var_dump and print_r.
 * It can correctly identify the recursively referenced objects in a complex
 * object structure. It also has a recursive depth control to avoid indefinite
 * recursive display of some peculiar variables.
 *
 * VarDumper can be used as follows,
 *
 * ~~~
 * VarDumper::dump($var);
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class VarDumper
{
	private static $_objects;
	private static $_output;
	private static $_depth;

	/**
	 * Displays a variable.
	 * This method achieves the similar functionality as var_dump and print_r
	 * but is more robust when handling complex objects such as Yii controllers.
	 * @param mixed $var variable to be dumped
	 * @param integer $depth maximum depth that the dumper should go into the variable. Defaults to 10.
	 * @param boolean $highlight whether the result should be syntax-highlighted
	 */
	public static function dump($var, $depth = 10, $highlight = false)
	{
		echo static::dumpAsString($var, $depth, $highlight);
	}

	/**
	 * Dumps a variable in terms of a string.
	 * This method achieves the similar functionality as var_dump and print_r
	 * but is more robust when handling complex objects such as Yii controllers.
	 * @param mixed $var variable to be dumped
	 * @param integer $depth maximum depth that the dumper should go into the variable. Defaults to 10.
	 * @param boolean $highlight whether the result should be syntax-highlighted
	 * @return string the string representation of the variable
	 */
	public static function dumpAsString($var, $depth = 10, $highlight = false)
	{
		self::$_output = '';
		self::$_objects = array();
		self::$_depth = $depth;
		self::dumpInternal($var, 0);
		if ($highlight) {
			$result = highlight_string("<?php\n" . self::$_output, true);
			self::$_output = preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
		}
		return self::$_output;
	}

	/**
	 * @param mixed $var variable to be dumped
	 * @param integer $level depth level
	 */
	private static function dumpInternal($var, $level)
	{
		switch (gettype($var)) {
			case 'boolean':
				self::$_output .= $var ? 'true' : 'false';
				break;
			case 'integer':
				self::$_output .= "$var";
				break;
			case 'double':
				self::$_output .= "$var";
				break;
			case 'string':
				self::$_output .= "'" . addslashes($var) . "'";
				break;
			case 'resource':
				self::$_output .= '{resource}';
				break;
			case 'NULL':
				self::$_output .= "null";
				break;
			case 'unknown type':
				self::$_output .= '{unknown}';
				break;
			case 'array':
				if (self::$_depth <= $level) {
					self::$_output .= 'array(...)';
				} elseif (empty($var)) {
					self::$_output .= 'array()';
				} else {
					$keys = array_keys($var);
					$spaces = str_repeat(' ', $level * 4);
					self::$_output .= "array\n" . $spaces . '(';
					foreach ($keys as $key) {
						self::$_output .= "\n" . $spaces . '    ';
						self::dumpInternal($key, 0);
						self::$_output .= ' => ';
						self::dumpInternal($var[$key], $level + 1);
					}
					self::$_output .= "\n" . $spaces . ')';
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
					$members = (array)$var;
					$spaces = str_repeat(' ', $level * 4);
					self::$_output .= "$className#$id\n" . $spaces . '(';
					foreach ($members as $key => $value) {
						$keyDisplay = strtr(trim($key), array("\0" => ':'));
						self::$_output .= "\n" . $spaces . "    [$keyDisplay] => ";
						self::dumpInternal($value, $level + 1);
					}
					self::$_output .= "\n" . $spaces . ')';
				}
				break;
		}
	}
}
