<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseInflector provides concrete implementation for [[Inflector]].
 *
 * Do not use BaseInflector. Use [[Inflector]] instead.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class BaseInflector
{
	/**
	 * @var array the rules for converting a word into its plural form.
	 * The keys are the regular expressions and the values are the corresponding replacements.
	 */
	public static $plurals = [
		'/([nrlm]ese|deer|fish|sheep|measles|ois|pox|media)$/i' => '\1',
		'/^(sea[- ]bass)$/i' => '\1',
		'/(m)ove$/i' => '\1oves',
		'/(f)oot$/i' => '\1eet',
		'/(h)uman$/i' => '\1umans',
		'/(s)tatus$/i' => '\1tatuses',
		'/(s)taff$/i' => '\1taff',
		'/(t)ooth$/i' => '\1eeth',
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
		'/(buffal|tomat|potat|ech|her|vet)o$/i' => '\1oes',
		'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
		'/us$/i' => 'uses',
		'/(alias)$/i' => '\1es',
		'/(ax|cris|test)is$/i' => '\1es',
		'/s$/' => 's',
		'/^$/' => '',
		'/$/' => 's',
	];
	/**
	 * @var array the rules for converting a word into its singular form.
	 * The keys are the regular expressions and the values are the corresponding replacements.
	 */
	public static $singulars = [
		'/([nrlm]ese|deer|fish|sheep|measles|ois|pox|media|ss)$/i' => '\1',
		'/^(sea[- ]bass)$/i' => '\1',
		'/(s)tatuses$/i' => '\1tatus',
		'/(f)eet$/i' => '\1oot',
		'/(t)eeth$/i' => '\1ooth',
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
		'/s$/i' => '',
	];
	/**
	 * @var array the special rules for converting a word between its plural form and singular form.
	 * The keys are the special words in singular form, and the values are the corresponding plural form.
	 */
	public static $specials = [
		'atlas' => 'atlases',
		'beef' => 'beefs',
		'brother' => 'brothers',
		'cafe' => 'cafes',
		'child' => 'children',
		'cookie' => 'cookies',
		'corpus' => 'corpuses',
		'cow' => 'cows',
		'curve' => 'curves',
		'foe' => 'foes',
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
		'sex' => 'sexes',
		'soliloquy' => 'soliloquies',
		'testis' => 'testes',
		'trilby' => 'trilbys',
		'turf' => 'turfs',
		'wave' => 'waves',
		'Amoyese' => 'Amoyese',
		'bison' => 'bison',
		'Borghese' => 'Borghese',
		'bream' => 'bream',
		'breeches' => 'breeches',
		'britches' => 'britches',
		'buffalo' => 'buffalo',
		'cantus' => 'cantus',
		'carp' => 'carp',
		'chassis' => 'chassis',
		'clippers' => 'clippers',
		'cod' => 'cod',
		'coitus' => 'coitus',
		'Congoese' => 'Congoese',
		'contretemps' => 'contretemps',
		'corps' => 'corps',
		'debris' => 'debris',
		'diabetes' => 'diabetes',
		'djinn' => 'djinn',
		'eland' => 'eland',
		'elk' => 'elk',
		'equipment' => 'equipment',
		'Faroese' => 'Faroese',
		'flounder' => 'flounder',
		'Foochowese' => 'Foochowese',
		'gallows' => 'gallows',
		'Genevese' => 'Genevese',
		'Genoese' => 'Genoese',
		'Gilbertese' => 'Gilbertese',
		'graffiti' => 'graffiti',
		'headquarters' => 'headquarters',
		'herpes' => 'herpes',
		'hijinks' => 'hijinks',
		'Hottentotese' => 'Hottentotese',
		'information' => 'information',
		'innings' => 'innings',
		'jackanapes' => 'jackanapes',
		'Kiplingese' => 'Kiplingese',
		'Kongoese' => 'Kongoese',
		'Lucchese' => 'Lucchese',
		'mackerel' => 'mackerel',
		'Maltese' => 'Maltese',
		'mews' => 'mews',
		'moose' => 'moose',
		'mumps' => 'mumps',
		'Nankingese' => 'Nankingese',
		'news' => 'news',
		'nexus' => 'nexus',
		'Niasese' => 'Niasese',
		'Pekingese' => 'Pekingese',
		'Piedmontese' => 'Piedmontese',
		'pincers' => 'pincers',
		'Pistoiese' => 'Pistoiese',
		'pliers' => 'pliers',
		'Portuguese' => 'Portuguese',
		'proceedings' => 'proceedings',
		'rabies' => 'rabies',
		'rice' => 'rice',
		'rhinoceros' => 'rhinoceros',
		'salmon' => 'salmon',
		'Sarawakese' => 'Sarawakese',
		'scissors' => 'scissors',
		'series' => 'series',
		'Shavese' => 'Shavese',
		'shears' => 'shears',
		'siemens' => 'siemens',
		'species' => 'species',
		'swine' => 'swine',
		'testes' => 'testes',
		'trousers' => 'trousers',
		'trout' => 'trout',
		'tuna' => 'tuna',
		'Vermontese' => 'Vermontese',
		'Wenchowese' => 'Wenchowese',
		'whiting' => 'whiting',
		'wildebeest' => 'wildebeest',
		'Yengeese' => 'Yengeese',
	];
	/**
	 * @var array map of special chars and its translation. This is used by [[slug()]].
	 */
	public static $transliteration = [
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
	];

	/**
	 * Converts a word to its plural form.
	 * Note that this is for English only!
	 * For example, 'apple' will become 'apples', and 'child' will become 'children'.
	 * @param string $word the word to be pluralized
	 * @return string the pluralized word
	 */
	public static function pluralize($word)
	{
		if (isset(static::$specials[$word])) {
			return static::$specials[$word];
		}
		foreach (static::$plurals as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}
		return $word;
	}

	/**
	 * Returns the singular of the $word
	 * @param string $word the english word to singularize
	 * @return string Singular noun.
	 */
	public static function singularize($word)
	{
		$result = array_search($word, static::$specials, true);
		if ($result !== false) {
			return $result;
		}
		foreach (static::$singulars as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}
		return $word;
	}

	/**
	 * Converts an underscored or CamelCase word into a English
	 * sentence.
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
	 * Converts a word like "send_email" to "SendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "WhoSOnline"
	 * @see variablize()
	 * @param string $word the word to CamelCase
	 * @return string
	 */
	public static function camelize($word)
	{
		return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
	}

	/**
	 * Converts a CamelCase name into space-separated words.
	 * For example, 'PostTag' will be converted to 'Post Tag'.
	 * @param string $name the string to be converted
	 * @param boolean $ucwords whether to capitalize the first letter in each word
	 * @return string the resulting words
	 */
	public static function camel2words($name, $ucwords = true)
	{
		$label = trim(strtolower(str_replace([
			'-',
			'_',
			'.'
		], ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));
		return $ucwords ? ucwords($label) : $label;
	}

	/**
	 * Converts a CamelCase name into an ID in lowercase.
	 * Words in the ID may be concatenated using the specified character (defaults to '-').
	 * For example, 'PostTag' will be converted to 'post-tag'.
	 * @param string $name the string to be converted
	 * @param string $separator the character used to concatenate the words in the ID
	 * @return string the resulting ID
	 */
	public static function camel2id($name, $separator = '-')
	{
		if ($separator === '_') {
			return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $name)), '_');
		} else {
			return trim(strtolower(str_replace('_', $separator, preg_replace('/(?<![A-Z])[A-Z]/', $separator . '\0', $name))), $separator);
		}
	}

	/**
	 * Converts an ID into a CamelCase name.
	 * Words in the ID separated by `$separator` (defaults to '-') will be concatenated into a CamelCase name.
	 * For example, 'post-tag' is converted to 'PostTag'.
	 * @param string $id the ID to be converted
	 * @param string $separator the character used to separate the words in the ID
	 * @return string the resulting CamelCase name
	 */
	public static function id2camel($id, $separator = '-')
	{
		return str_replace(' ', '', ucwords(implode(' ', explode($separator, $id))));
	}

	/**
	 * Converts any "CamelCased" into an "underscored_word".
	 * @param string $words the word(s) to underscore
	 * @return string
	 */
	public static function underscore($words)
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $words));
	}

	/**
	 * Returns a human-readable string from $word
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
	 * Same as camelize but first char is in lowercase.
	 * Converts a word like "send_email" to "sendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "whoSOnline"
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
	 * @param string $className the class name for getting related table_name
	 * @return string
	 */
	public static function tableize($className)
	{
		return static::pluralize(static::underscore($className));
	}

	/**
	 * Returns a string with all spaces converted to given replacement and
	 * non word characters removed.  Maps special characters to ASCII using
	 * `Inflector::$transliteration`
	 * @param string $string An arbitrary string to convert
	 * @param string $replacement The replacement to use for spaces
	 * @return string The converted string.
	 */
	public static function slug($string, $replacement = '-')
	{
		$map = static::$transliteration + [
				'/[^\w\s]/' => ' ',
				'/\\s+/' => $replacement,
				'/(?<=[a-z])([A-Z])/' => $replacement . '\\1',
				str_replace(':rep', preg_quote($replacement, '/'), '/^[:rep]+|[:rep]+$/') => ''
			];
		return preg_replace(array_keys($map), array_values($map), $string);
	}

	/**
	 * Converts a table name to its class name. For example, converts "people" to "Person"
	 * @param string $tableName
	 * @return string
	 */
	public static function classify($tableName)
	{
		return static::camelize(static::singularize($tableName));
	}

	/**
	 * Converts number to its ordinal English form. For example, converts 13 to 13th, 2 to 2nd ...
	 * @param int $number the number to get its ordinal value
	 * @return string
	 */
	public static function ordinalize($number)
	{
		if (in_array(($number % 100), range(11, 13))) {
			return $number . 'th';
		}
		switch ($number % 10) {
			case 1: return $number . 'st';
			case 2: return $number . 'nd';
			case 3: return $number . 'rd';
			default: return $number . 'th';
		}
	}
}
