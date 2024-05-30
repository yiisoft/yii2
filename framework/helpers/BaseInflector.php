<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseInflector provides concrete implementation for [[Inflector]].
 *
 * Do not use BaseInflector. Use [[Inflector]] instead.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
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
        '/(currenc)y$/' => '\1ies',
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
        '/(n)etherlands$/i' => '\1\2etherlands',
        '/eaus$/' => 'eau',
        '/(currenc)ies$/' => '\1y',
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
        'pasta' => 'pasta',
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
        'software' => 'software',
        'hardware' => 'hardware',
    ];
    /**
     * @var array fallback map for transliteration used by [[transliterate()]] when intl isn't available.
     */
    public static $transliteration = [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
        'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
        'ß' => 'ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
        'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
        'ÿ' => 'y',
    ];
    /**
     * Shortcut for `Any-Latin; NFKD` transliteration rule.
     *
     * The rule is strict, letters will be transliterated with
     * the closest sound-representation chars. The result may contain any UTF-8 chars. For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huò qǔ dào dochira Ukraí̈nsʹka: g̀,ê, Srpska: đ, n̂, d̂! ¿Español?`.
     *
     * Used in [[transliterate()]].
     * For detailed information see [unicode normalization forms](https://unicode.org/reports/tr15/#Normalization_Forms_Table)
     * @see https://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     * @since 2.0.7
     */
    const TRANSLITERATE_STRICT = 'Any-Latin; NFKD';
    /**
     * Shortcut for `Any-Latin; Latin-ASCII` transliteration rule.
     *
     * The rule is medium, letters will be
     * transliterated to characters of Latin-1 (ISO 8859-1) ASCII table. For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huo qu dao dochira Ukrainsʹka: g,e, Srpska: d, n, d! ¿Espanol?`.
     *
     * Used in [[transliterate()]].
     * For detailed information see [unicode normalization forms](https://unicode.org/reports/tr15/#Normalization_Forms_Table)
     * @see https://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     * @since 2.0.7
     */
    const TRANSLITERATE_MEDIUM = 'Any-Latin; Latin-ASCII';
    /**
     * Shortcut for `Any-Latin; Latin-ASCII; [\u0080-\uffff] remove` transliteration rule.
     *
     * The rule is loose,
     * letters will be transliterated with the characters of Basic Latin Unicode Block.
     * For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huo qu dao dochira Ukrainska: g,e, Srpska: d, n, d! Espanol?`.
     *
     * Used in [[transliterate()]].
     * For detailed information see [unicode normalization forms](https://unicode.org/reports/tr15/#Normalization_Forms_Table)
     * @see https://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     * @since 2.0.7
     */
    const TRANSLITERATE_LOOSE = 'Any-Latin; Latin-ASCII; [\u0080-\uffff] remove';

    /**
     * @var mixed Either a [[\Transliterator]], or a string from which a [[\Transliterator]] can be built
     * for transliteration. Used by [[transliterate()]] when intl is available. Defaults to [[TRANSLITERATE_LOOSE]]
     * @see https://www.php.net/manual/en/transliterator.transliterate.php
     */
    public static $transliterator = self::TRANSLITERATE_LOOSE;


    /**
     * Converts a word to its plural form.
     * Note that this is for English only!
     * For example, 'apple' will become 'apples', and 'child' will become 'children'.
     * @param string $word the word to be pluralized
     * @return string the pluralized word
     */
    public static function pluralize($word)
    {
        if (empty($word)) {
            return (string) $word;
        }
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
     * Returns the singular of the $word.
     * @param string $word the english word to singularize
     * @return string Singular noun.
     */
    public static function singularize($word)
    {
        if (empty($word)) {
            return (string) $word;
        }
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
        if (empty($words)) {
            return (string) $words;
        }
        $words = static::humanize(static::underscore($words), $ucAll);

        return $ucAll ? StringHelper::mb_ucwords($words, self::encoding()) : StringHelper::mb_ucfirst($words, self::encoding());
    }

    /**
     * Returns given word as CamelCased.
     *
     * Converts a word like "send_email" to "SendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "WhoSOnline".
     * @param string $word the word to CamelCase
     * @return string
     * @see variablize()
     */
    public static function camelize($word)
    {
        if (empty($word)) {
            return (string) $word;
        }
        return str_replace(' ', '', StringHelper::mb_ucwords(preg_replace('/[^\pL\pN]+/u', ' ', $word), self::encoding()));
    }

    /**
     * Converts a CamelCase name into space-separated words.
     * For example, 'PostTag' will be converted to 'Post Tag'.
     * @param string $name the string to be converted
     * @param bool $ucwords whether to capitalize the first letter in each word
     * @return string the resulting words
     */
    public static function camel2words($name, $ucwords = true)
    {
        if (empty($name)) {
            return (string) $name;
        }
        // Add a space before any uppercase letter preceded by a lowercase letter (xY => x Y)
        // and any uppercase letter preceded by an uppercase letter and followed by a lowercase letter (XYz => X Yz)
        $label = preg_replace('/(?<=\p{Ll})\p{Lu}|(?<=\p{L})\p{Lu}(?=\p{Ll})/u', ' \0', $name);

        $label = mb_strtolower(trim(str_replace(['-', '_', '.'], ' ', $label)), self::encoding());

        return $ucwords ? StringHelper::mb_ucwords($label, self::encoding()) : $label;
    }

    /**
     * Converts a CamelCase name into an ID in lowercase.
     * Words in the ID may be concatenated using the specified character (defaults to '-').
     * For example, 'PostTag' will be converted to 'post-tag'.
     * @param string $name the string to be converted
     * @param string $separator the character used to concatenate the words in the ID
     * @param bool|string $strict whether to insert a separator between two consecutive uppercase chars, defaults to false
     * @return string the resulting ID
     */
    public static function camel2id($name, $separator = '-', $strict = false)
    {
        if (empty($name)) {
            return (string) $name;
        }
        $regex = $strict ? '/\p{Lu}/u' : '/(?<!\p{Lu})\p{Lu}/u';
        if ($separator === '_') {
            return mb_strtolower(trim(preg_replace($regex, '_\0', $name), '_'), self::encoding());
        }

        return mb_strtolower(trim(str_replace('_', $separator, preg_replace($regex, $separator . '\0', $name)), $separator), self::encoding());
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
        if (empty($id)) {
            return (string) $id;
        }
        return str_replace(' ', '', StringHelper::mb_ucwords(str_replace($separator, ' ', $id), self::encoding()));
    }

    /**
     * Converts any "CamelCased" into an "underscored_word".
     * @param string $words the word(s) to underscore
     * @return string
     */
    public static function underscore($words)
    {
        if (empty($words)) {
            return (string) $words;
        }
        return mb_strtolower(preg_replace('/(?<=\\pL)(\\p{Lu})/u', '_\\1', $words), self::encoding());
    }

    /**
     * Returns a human-readable string from $word.
     * @param string $word the string to humanize
     * @param bool $ucAll whether to set all words to uppercase or not
     * @return string
     */
    public static function humanize($word, $ucAll = false)
    {
        if (empty($word)) {
            return (string) $word;
        }
        $word = str_replace('_', ' ', preg_replace('/_id$/', '', $word));
        $encoding = self::encoding();

        return $ucAll ? StringHelper::mb_ucwords($word, $encoding) : StringHelper::mb_ucfirst($word, $encoding);
    }

    /**
     * Same as camelize but first char is in lowercase.
     *
     * Converts a word like "send_email" to "sendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "whoSOnline".
     * @param string $word to lowerCamelCase
     * @return string
     */
    public static function variablize($word)
    {
        if (empty($word)) {
            return (string) $word;
        }
        $word = static::camelize($word);

        return mb_strtolower(mb_substr($word, 0, 1, self::encoding())) . mb_substr($word, 1, null, self::encoding());
    }

    /**
     * Converts a class name to its table name (pluralized) naming conventions.
     *
     * For example, converts "Person" to "people".
     * @param string $className the class name for getting related table_name
     * @return string
     */
    public static function tableize($className)
    {
        if (empty($className)) {
            return (string) $className;
        }
        return static::pluralize(static::underscore($className));
    }

    /**
     * Returns a string with all spaces converted to given replacement,
     * non word characters removed and the rest of characters transliterated.
     *
     * If intl extension isn't available uses fallback that converts latin characters only
     * and removes the rest. You may customize characters map via $transliteration property
     * of the helper.
     *
     * @param string $string An arbitrary string to convert
     * @param string $replacement The replacement to use for spaces
     * @param bool $lowercase whether to return the string in lowercase or not. Defaults to `true`.
     * @return string The converted string.
     */
    public static function slug($string, $replacement = '-', $lowercase = true)
    {
        if (empty($string)) {
            return (string) $string;
        }
        if ((string)$replacement !== '') {
            $parts = explode($replacement, static::transliterate($string));
        } else {
            $parts = [static::transliterate($string)];
        }

        $replaced = array_map(function ($element) use ($replacement) {
            $element = preg_replace('/[^a-zA-Z0-9=\s—–-]+/u', '', $element);
            return preg_replace('/[=\s—–-]+/u', $replacement, $element);
        }, $parts);

        $string = trim(implode($replacement, $replaced), $replacement);
        if ((string)$replacement !== '') {
            $string = preg_replace('#' . preg_quote($replacement, '#') . '+#', $replacement, $string);
        }

        return $lowercase ? strtolower($string) : $string;
    }

    /**
     * Returns transliterated version of a string.
     *
     * If intl extension isn't available uses fallback that converts latin characters only
     * and removes the rest. You may customize characters map via $transliteration property
     * of the helper.
     *
     * @param string $string input string
     * @param string|\Transliterator|null $transliterator either a [[\Transliterator]] or a string
     * from which a [[\Transliterator]] can be built.
     * @return string
     * @since 2.0.7 this method is public.
     */
    public static function transliterate($string, $transliterator = null)
    {
        if (empty($string)) {
            return (string) $string;
        }
        if (static::hasIntl()) {
            if ($transliterator === null) {
                $transliterator = static::$transliterator;
            }

            return transliterator_transliterate($transliterator, $string);
        }

        return strtr($string, static::$transliteration);
    }

    /**
     * @return bool if intl extension is loaded
     */
    protected static function hasIntl()
    {
        return extension_loaded('intl');
    }

    /**
     * Converts a table name to its class name.
     *
     * For example, converts "people" to "Person".
     * @param string $tableName
     * @return string
     */
    public static function classify($tableName)
    {
        if (empty($tableName)) {
            return (string) $tableName;
        }
        return static::camelize(static::singularize($tableName));
    }

    /**
     * Converts number to its ordinal English form. For example, converts 13 to 13th, 2 to 2nd ...
     * @param int $number the number to get its ordinal value
     * @return string
     */
    public static function ordinalize($number)
    {
        if (in_array($number % 100, range(11, 13))) {
            return $number . 'th';
        }
        switch ($number % 10) {
            case 1:
                return $number . 'st';
            case 2:
                return $number . 'nd';
            case 3:
                return $number . 'rd';
            default:
                return $number . 'th';
        }
    }

    /**
     * Converts a list of words into a sentence.
     *
     * Special treatment is done for the last few words. For example,
     *
     * ```php
     * $words = ['Spain', 'France'];
     * echo Inflector::sentence($words);
     * // output: Spain and France
     *
     * $words = ['Spain', 'France', 'Italy'];
     * echo Inflector::sentence($words);
     * // output: Spain, France and Italy
     *
     * $words = ['Spain', 'France', 'Italy'];
     * echo Inflector::sentence($words, ' & ');
     * // output: Spain, France & Italy
     * ```
     *
     * @param array $words the words to be converted into an string
     * @param string|null $twoWordsConnector the string connecting words when there are only two
     * @param string|null $lastWordConnector the string connecting the last two words. If this is null, it will
     * take the value of `$twoWordsConnector`.
     * @param string $connector the string connecting words other than those connected by
     * $lastWordConnector and $twoWordsConnector
     * @return string the generated sentence
     * @since 2.0.1
     */
    public static function sentence(array $words, $twoWordsConnector = null, $lastWordConnector = null, $connector = ', ')
    {
        if ($twoWordsConnector === null) {
            $twoWordsConnector = Yii::t('yii', ' and ');
        }
        if ($lastWordConnector === null) {
            $lastWordConnector = $twoWordsConnector;
        }
        switch (count($words)) {
            case 0:
                return '';
            case 1:
                return reset($words);
            case 2:
                return implode($twoWordsConnector, $words);
            default:
                return implode($connector, array_slice($words, 0, -1)) . $lastWordConnector . end($words);
        }
    }

    /**
     * @return string
     */
    private static function encoding()
    {
        return isset(Yii::$app) ? Yii::$app->charset : 'UTF-8';
    }
}
