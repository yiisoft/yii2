<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseTransliteratorHelper provides concrete implementation for [[TransliteratorHelper]].
 *
 * Do not use BaseTransliteratorHelper. Use [[TransliteratorHelper]] instead.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class BaseTransliteratorHelper
{
	/**
	 * Transliterates UTF-8 encoded text to US-ASCII. If 'intl' extension is loaded it will use it to transliterate the
	 * string, otherwise, it will fallback on Unicode character code replacement.
	 *
	 * @param string $string the UTF-8 encoded string.
	 * @param string $unknown replacement string for characters that do not have a suitable ASCII equivalent
	 * @param string $language optional ISO 639 language code that denotes the language of the input and
	 * is used to apply language-specific variations. Otherwise the current display language will be used.
	 * @return string the transliterated text
	 */
	public static function process($string, $unknown = '?', $language = null)
	{
		// If intl extension load
		if (extension_loaded('intl') === true) {
			$options = 'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;';
			return transliterator_transliterate($options, $string);
		}
		if (!preg_match('/[\x80-\xff]/', $string)) {
			return $string;
		}
		static $tail_bytes;

		if (!isset($tail_bytes)) {
			$tail_bytes = array();
			for ($n = 0; $n < 256; $n++) {
				if ($n < 0xc0) {
					$remaining = 0;
				} elseif ($n < 0xe0) {
					$remaining = 1;
				} elseif ($n < 0xf0) {
					$remaining = 2;
				} elseif ($n < 0xf8) {
					$remaining = 3;
				} elseif ($n < 0xfc) {
					$remaining = 4;
				} elseif ($n < 0xfe) {
					$remaining = 5;
				} else {
					$remaining = 0;
				}
				$tail_bytes[chr($n)] = $remaining;
			}
		}

		preg_match_all('/[\x00-\x7f]+|[\x80-\xff][\x00-\x40\x5b-\x5f\x7b-\xff]*/', $string, $matches);

		$result = [];
		foreach ($matches[0] as $str) {
			if ($str[0] < "\x80") {
				$result[] = $str;
				continue;
			}

			$head = '';
			$chunk = strlen($str);
			$len = $chunk + 1;
			for ($i = -1; --$len;) {
				$c = $str[++$i];
				if ($remaining = $tail_bytes[$c]) {
					$sequence = $head = $c;
					do {
						if (--$len && ($c = $str[++$i]) >= "\x80" && $c < "\xc0") {
							$sequence .= $c;
						} else {
							if ($len == 0) {
								$result[] = $unknown;
								break 2;
							} else {
								$result[] = $unknown;
								--$i;
								++$len;
								continue 2;
							}
						}
					} while (--$remaining);

					$n = ord($head);
					if ($n <= 0xdf) {
						$ord = ($n - 192) * 64 + (ord($sequence[1]) - 128);
					} elseif ($n <= 0xef) {
						$ord = ($n - 224) * 4096 + (ord($sequence[1]) - 128) * 64 + (ord($sequence[2]) - 128);
					} elseif ($n <= 0xf7) {
						$ord = ($n - 240) * 262144 + (ord($sequence[1]) - 128) * 4096 +
							(ord($sequence[2]) - 128) * 64 + (ord($sequence[3]) - 128);
					} elseif ($n <= 0xfb) {
						$ord = ($n - 248) * 16777216 + (ord($sequence[1]) - 128) * 262144 +
							(ord($sequence[2]) - 128) * 4096 + (ord($sequence[3]) - 128) * 64 + (ord($sequence[4]) - 128);
					} elseif ($n <= 0xfd) {
						$ord = ($n - 252) * 1073741824 + (ord($sequence[1]) - 128) * 16777216 +
							(ord($sequence[2]) - 128) * 262144 + (ord($sequence[3]) - 128) * 4096 +
							(ord($sequence[4]) - 128) * 64 + (ord($sequence[5]) - 128);
					}
					$result[] = static::replace($ord, $unknown, $language);
					$head = '';
				} elseif ($c < "\x80") {
					$result[] = $c;
					$head = '';
				} elseif ($c < "\xc0") {
					if ($head == '') {
						$result[] = $unknown;
					}
				} else {
					$result[] = $unknown;
					$head = '';
				}
			}
		}
		return implode('', $result);
	}

	/**
	 * @param int $ord an ordinal Unicode character code
	 * @param string $unknown a replacement string for characters that do not have a suitable ASCII equivalent
	 * @param string $language optional ISO 639 language code that specifies the language of the input and is used
	 * to apply
	 * @return string the ASCII replacement character
	 */
	public static function replace($ord, $unknown = '?', $language = null)
	{
		static $map = array();

		if (!isset($language)) {
			$language = Yii::$app->language;
			if (strpos($language, '-')) {
				$language = substr($language, 0, strpos($language, '-'));
			}
		}

		$key = $ord >> 8;

		if (!isset($map[$key][$language])) {
			$file = dirname(__FILE__) . DIRECTORY_SEPARATOR .
				'transliteration' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR .
				sprintf('x%02x', $key) . '.php';

			if (file_exists($file)) {
				include $file;
				// $base + $variant are included vars from
				if ($language != 'en' && isset($variant[$language])) {
					$map[$key][$language] = $variant[$language] + $base;
				} else {
					$map[$key][$language] = $base;
				}
			} else {
				$map[$key][$language] = array();
			}
		}

		$ord = $ord & 255;

		return isset($map[$key][$language][$ord]) ? $map[$key][$language][$ord] : $unknown;
	}
}