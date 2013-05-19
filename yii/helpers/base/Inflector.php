<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

use Yii;

/**
 * Inflector pluralizes and singularizes English nouns. It also contains other useful methods.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Inflector
{

	/**
	 * @var array rules of plural words
	 */
	protected static $plural = array(
		'rules' => array(
			'/(s)tatus$/i' => '\1\2tatuses',
			'/(quiz)$/i' => '\1zes',
			'/^(ox)$/i' => '\1\2en',
			'/([m|l])ouse$/i' => '\1ice',
			'/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
			'/(x|ch|ss|sh)$/i' => '\1es',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(hive)$/i' => '\1s',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/sis$/i' => 'ses',
			'/([ti])um$/i' => '\1a',
			'/(p)erson$/i' => '\1eople',
			'/(m)an$/i' => '\1en',
			'/(c)hild$/i' => '\1hildren',
			'/(buffal|tomat)o$/i' => '\1\2oes',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
			'/us$/i' => 'uses',
			'/(alias)$/i' => '\1es',
			'/(ax|cris|test)is$/i' => '\1es',
			'/s$/' => 's',
			'/^$/' => '',
			'/$/' => 's',
		),
		'uninflected' => array(
			'.*[nrlm]ese',
			'.*deer',
			'.*fish',
			'.*measles',
			'.*ois',
			'.*pox',
			'.*sheep',
			'people'
		),
		'irregular' => array(
			'atlas' => 'atlases',
			'beef' => 'beefs',
			'brother' => 'brothers',
			'cafe' => 'cafes',
			'child' => 'children',
			'cookie' => 'cookies',
			'corpus' => 'corpuses',
			'cow' => 'cows',
			'ganglion' => 'ganglions',
			'genie' => 'genies',
			'genus' => 'genera',
			'graffito' => 'graffiti',
			'hoof' => 'hoofs',
			'loaf' => 'loaves',
			'man' => 'men',
			'money' => 'monies',
			'mongoose' => 'mongooses',
			'move' => 'moves',
			'mythos' => 'mythoi',
			'niche' => 'niches',
			'numen' => 'numina',
			'occiput' => 'occiputs',
			'octopus' => 'octopuses',
			'opus' => 'opuses',
			'ox' => 'oxen',
			'penis' => 'penises',
			'person' => 'people',
			'sex' => 'sexes',
			'soliloquy' => 'soliloquies',
			'testis' => 'testes',
			'trilby' => 'trilbys',
			'turf' => 'turfs'
		)
	);
	/**
	 * @var array the rules to singular inflector
	 */
	protected static $singular = array(
		'rules' => array(
			'/(s)tatuses$/i' => '\1\2tatus',
			'/^(.*)(menu)s$/i' => '\1\2',
			'/(quiz)zes$/i' => '\\1',
			'/(matr)ices$/i' => '\1ix',
			'/(vert|ind)ices$/i' => '\1ex',
			'/^(ox)en/i' => '\1',
			'/(alias)(es)*$/i' => '\1',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
			'/([ftw]ax)es/i' => '\1',
			'/(cris|ax|test)es$/i' => '\1is',
			'/(shoe|slave)s$/i' => '\1',
			'/(o)es$/i' => '\1',
			'/ouses$/' => 'ouse',
			'/([^a])uses$/' => '\1us',
			'/([m|l])ice$/i' => '\1ouse',
			'/(x|ch|ss|sh)es$/i' => '\1',
			'/(m)ovies$/i' => '\1\2ovie',
			'/(s)eries$/i' => '\1\2eries',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/([lr])ves$/i' => '\1f',
			'/(tive)s$/i' => '\1',
			'/(hive)s$/i' => '\1',
			'/(drive)s$/i' => '\1',
			'/([^fo])ves$/i' => '\1fe',
			'/(^analy)ses$/i' => '\1sis',
			'/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
			'/([ti])a$/i' => '\1um',
			'/(p)eople$/i' => '\1\2erson',
			'/(m)en$/i' => '\1an',
			'/(c)hildren$/i' => '\1\2hild',
			'/(n)ews$/i' => '\1\2ews',
			'/eaus$/' => 'eau',
			'/^(.*us)$/' => '\\1',
			'/s$/i' => ''
		),
		'uninflected' => array(
			'.*[nrlm]ese',
			'.*deer',
			'.*fish',
			'.*measles',
			'.*ois',
			'.*pox',
			'.*sheep',
			'.*ss'
		),
		'irregular' => array(
			'foes' => 'foe',
			'waves' => 'wave',
			'curves' => 'curve'
		)
	);

	/**
	 * @var array list of words that should not be inflected
	 */
	protected static $uninflected = array(
		'Amoyese',
		'bison',
		'Borghese',
		'bream',
		'breeches',
		'britches',
		'buffalo',
		'cantus',
		'carp',
		'chassis',
		'clippers',
		'cod',
		'coitus',
		'Congoese',
		'contretemps',
		'corps',
		'debris',
		'diabetes',
		'djinn',
		'eland',
		'elk',
		'equipment',
		'Faroese',
		'flounder',
		'Foochowese',
		'gallows',
		'Genevese',
		'Genoese',
		'Gilbertese',
		'graffiti',
		'headquarters',
		'herpes',
		'hijinks',
		'Hottentotese',
		'information',
		'innings',
		'jackanapes',
		'Kiplingese',
		'Kongoese',
		'Lucchese',
		'mackerel',
		'Maltese',
		'.*?media',
		'mews',
		'moose',
		'mumps',
		'Nankingese',
		'news',
		'nexus',
		'Niasese',
		'Pekingese',
		'Piedmontese',
		'pincers',
		'Pistoiese',
		'pliers',
		'Portuguese',
		'proceedings',
		'rabies',
		'rice',
		'rhinoceros',
		'salmon',
		'Sarawakese',
		'scissors',
		'sea[- ]bass',
		'series',
		'Shavese',
		'shears',
		'siemens',
		'species',
		'swine',
		'testes',
		'trousers',
		'trout',
		'tuna',
		'Vermontese',
		'Wenchowese',
		'whiting',
		'wildebeest',
		'Yengeese'
	);

	/**
	 * @var array map of special chars and its translation
	 */
	protected static $transliteration = array(
		'/ä|æ|ǽ/' => 'ae',
		'/ö|œ/' => 'oe',
		'/ü/' => 'ue',
		'/Ä/' => 'Ae',
		'/Ü/' => 'Ue',
		'/Ö/' => 'Oe',
		'/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
		'/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
		'/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
		'/ç|ć|ĉ|ċ|č/' => 'c',
		'/Ð|Ď|Đ/' => 'D',
		'/ð|ď|đ/' => 'd',
		'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
		'/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
		'/Ĝ|Ğ|Ġ|Ģ/' => 'G',
		'/ĝ|ğ|ġ|ģ/' => 'g',
		'/Ĥ|Ħ/' => 'H',
		'/ĥ|ħ/' => 'h',
		'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
		'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
		'/Ĵ/' => 'J',
		'/ĵ/' => 'j',
		'/Ķ/' => 'K',
		'/ķ/' => 'k',
		'/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
		'/ĺ|ļ|ľ|ŀ|ł/' => 'l',
		'/Ñ|Ń|Ņ|Ň/' => 'N',
		'/ñ|ń|ņ|ň|ŉ/' => 'n',
		'/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
		'/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
		'/Ŕ|Ŗ|Ř/' => 'R',
		'/ŕ|ŗ|ř/' => 'r',
		'/Ś|Ŝ|Ş|Ș|Š/' => 'S',
		'/ś|ŝ|ş|ș|š|ſ/' => 's',
		'/Ţ|Ț|Ť|Ŧ/' => 'T',
		'/ţ|ț|ť|ŧ/' => 't',
		'/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
		'/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
		'/Ý|Ÿ|Ŷ/' => 'Y',
		'/ý|ÿ|ŷ/' => 'y',
		'/Ŵ/' => 'W',
		'/ŵ/' => 'w',
		'/Ź|Ż|Ž/' => 'Z',
		'/ź|ż|ž/' => 'z',
		'/Æ|Ǽ/' => 'AE',
		'/ß/' => 'ss',
		'/Ĳ/' => 'IJ',
		'/ĳ/' => 'ij',
		'/Œ/' => 'OE',
		'/ƒ/' => 'f'
	);

	/**
	 * Returns the plural of a $word
	 *
	 * @param string $word the word to pluralize
	 * @return string
	 */
	public static function pluralize($word)
	{
		$unInflected = ArrayHelper::merge(static::$plural['uninflected'], static::$uninflected);
		$irregular = array_keys(static::$plural['irregular']);

		$unInflectedRegex = '(?:' . implode('|', $unInflected) . ')';
		$irregularRegex = '(?:' . implode('|', $irregular) . ')';

		if (preg_match('/(.*)\\b(' . $irregularRegex . ')$/i', $word, $regs))
			return $regs[1] . substr($word, 0, 1) . substr(static::$plural['irregular'][strtolower($regs[2])], 1);

		if (preg_match('/^(' . $unInflectedRegex . ')$/i', $word, $regs))
			return $word;

		foreach (static::$plural['rules'] as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}
		return $word;
	}

	/**
	 * Returns the singular of the $word
	 *
	 * @param string $word the english word to singularize
	 * @return string Singular noun.
	 */
	public static function singularize($word)
	{

		$unInflected = ArrayHelper::merge(static::$singular['uninflected'], static::$uninflected);

		$irregular = array_merge(
			static::$singular['irregular'],
			array_flip(static::$plural['irregular'])
		);

		$unInflectedRegex = '(?:' . implode('|', $unInflected) . ')';
		$irregularRegex = '(?:' . implode('|', array_keys($irregular)) . ')';


		if (preg_match('/(.*)\\b(' . $irregularRegex . ')$/i', $word, $regs))
			return $regs[1] . substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);


		if (preg_match('/^(' . $unInflectedRegex . ')$/i', $word, $regs))
			return $word;


		foreach (static::$singular['rules'] as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}
		return $word;
	}

	/**
	 * Converts an underscored or CamelCase word into a English
	 * sentence.
	 *
	 * @param string $words
	 * @param bool $ucAll whether to set all words to uppercase
	 * @return string
	 */
	public static function titleize($words, $ucAll = false)
	{

		$words = static::humanize(static::underscore($words), $ucAll);
		return $ucAll ? ucwords($words) : ucfirst($words);
	}

	/**
	 * Returns given word as CamelCased
	 *
	 * Converts a word like "send_email" to "SendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "WhoSOnline"
	 *
	 * @see variablize
	 * @param string $word the word to CamelCase
	 * @return string
	 */
	public static function camelize($word)
	{
		return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
	}

	/**
	 * Converts any "CamelCased" or "ordinary Word" into an "underscored_word".
	 *
	 * @param string $words the word(s) to underscore
	 * @return string
	 */
	public static function underscore($words)
	{
		return  strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $words));
	}

	/**
	 * Returns a human-readable string from $word
	 *
	 * @param string $word the string to humanize
	 * @param bool $ucAll whether to set all words to uppercase or not
	 * @return string
	 */
	public static function humanize($word, $ucAll = false)
	{
		$word = str_replace('_', ' ', preg_replace('/_id$/', '', $word));
		return $ucAll ? ucwords($word) : ucfirst($word);
	}

	/**
	 * Same as camelize but first char is in lowercase
	 *
	 * Converts a word like "send_email" to "sendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "whoSOnline"
	 *
	 * @param string $word to lowerCamelCase
	 * @return string
	 */
	public static function variablize($word)
	{
		$word = static::camelize($word);
		return strtolower($word[0]) . substr($word, 1);
	}

	/**
	 * Converts a class name to its table name (pluralized)
	 * naming conventions. For example, converts "Person" to "people"
	 *
	 * @param string $class_name the class name for getting related table_name
	 * @return string
	 */
	public static function tableize($class_name)
	{
		return static::pluralize(static::underscore($class_name));
	}

	/**
	 * Returns a string with all spaces converted to given replacement and
	 * non word characters removed.  Maps special characters to ASCII using
	 * `Inflector::$transliteration`.
	 *
	 * @param string $string An arbitrary string to convert.
	 * @param string $replacement The replacement to use for spaces.
	 * @return string The converted string.
	 */
	public static function slug($string, $replacement = '-')
	{

		$map = static::$transliteration + array(
				'/[^\w\s]/' => ' ',
				'/\\s+/' => $replacement,
				'/(?<=[a-z])([A-Z])/' => $replacement . '\\1',
				str_replace(':rep', preg_quote($replacement, '/'), '/^[:rep]+|[:rep]+$/') => ''
			);
		return preg_replace(array_keys($map), array_values($map), $string);
	}

	/**
	 * Converts a table name to its class name. For example, converts "people" to "Person"
	 *
	 * @param string $table_name
	 * @return string
	 */
	public static function classify($table_name)
	{
		return static::camelize(static::singularize($table_name));
	}

	/**
	 * Converts number to its ordinal English form.
	 *
	 * This method converts 13 to 13th, 2 to 2nd ...
	 *
	 * @param int $number the number to get its ordinal value
	 * @return string
	 */
	public static function ordinalize($number)
	{
		if (in_array(($number % 100), range(11, 13))) {
			return $number . 'th';
		} else {
			switch (($number % 10)) {
				case 1:
					return $number . 'st';
					break;
				case 2:
					return $number . 'nd';
					break;
				case 3:
					return $number . 'rd';
				default:
					return $number . 'th';
					break;
			}
		}
	}
}
