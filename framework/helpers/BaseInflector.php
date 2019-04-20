<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseInflector 为 [[Inflector]] 提供了具体的实现方法。
 *
 * 不要使用 BaseInflector 类。使用 [[Inflector]] 类来代替。
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseInflector
{
    /**
     * @var array 把一个词转换成复数形式的规则。
     * 键是正则表达式，值是相应的替换。
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
     * @var array 把一个词转换成单数形式的规则。
     * 键是正则表达式，值是相应的替换。
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
     * @var array 在复数形式和单数形式之间转换单词的特殊规则。
     * 关键字是单数形式的特殊词，值是对应的复数形式。
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
    ];
    /**
     * @var array 当 intl 不可用时 [[transliterate()]] 所使用的音译回退映射。
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
     * 为 `Any-Latin; NFKD` 提供快捷的音译规则。
     *
     * 规则很严格，字母将被音译与最接近的声音表示字符。
     * 结果可能包含任何 UTF-8 字符。
     * 例如：`获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` 会被音译成
     * `huò qǔ dào dochira Ukraí̈nsʹka: g̀,ê, Srpska: đ, n̂, d̂! ¿Español?`。
     *
     * 在 [[transliterate()]] 中使用。
     * 有关详细信息，请参阅 [unicode normalization forms](http://unicode.org/reports/tr15/#Normalization_Forms_Table)
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     * @since 2.0.7
     */
    const TRANSLITERATE_STRICT = 'Any-Latin; NFKD';
    /**
     * 为 `Any-Latin; Latin-ASCII` 提供快捷的音译规则。
     *
     * 规则是中等的，字母将被转换为 Latin-1（ISO 8859-1）ASCII 表中的字符。
     * 例如：
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` 会被音译成
     * `huo qu dao dochira Ukrainsʹka: g,e, Srpska: d, n, d! ¿Espanol?`。
     *
     * 在 [[transliterate()]] 中使用。
     * 有关详细信息，请参阅 [unicode normalization forms](http://unicode.org/reports/tr15/#Normalization_Forms_Table)
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     * @since 2.0.7
     */
    const TRANSLITERATE_MEDIUM = 'Any-Latin; Latin-ASCII';
    /**
     * 为 `Any-Latin; Latin-ASCII; [\u0080-\uffff] 提供移除规则的快捷方式。
     *
     * 规则是宽松的，
     * 字母将与基本拉丁 Unicode 块的字符进行音译。
     * 例如：
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` 会被音译成
     * `huo qu dao dochira Ukrainska: g,e, Srpska: d, n, d! Espanol?`。
     *
     * 在 [[transliterate()]] 中使用。
     * 有关详细信息，请参阅 [unicode normalization forms](http://unicode.org/reports/tr15/#Normalization_Forms_Table)
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     * @since 2.0.7
     */
    const TRANSLITERATE_LOOSE = 'Any-Latin; Latin-ASCII; [\u0080-\uffff] remove';

    /**
     * @var mixed 可以使用 [[\Transliterator]]，也可以使用字符串构建 [[\Transliterator]] 构建用于音译的 [[\Transliterator]]。 
     * 当 intl 可用时，由 [[transliterate()]] 使用。默认为 [[TRANSLITERATE_LOOSE]]
     * @see http://php.net/manual/en/transliterator.transliterate.php
     */
    public static $transliterator = self::TRANSLITERATE_LOOSE;


    /**
     * 将一个单词转换为其复数形式。
     * 注意，这只适用于英语!
     * 例如，'apple' 将变成复数形式 'apples'，并且 'child' 将变成复数形式 'children'。
     * @param string $word 将要转换为复数形式的单词
     * @return string 复数词
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
     * 返回 $word 的单数。
     * @param string $word 将英语单词单数化
     * @return string 单数名词。
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
     * 将带下划线或驼峰大小写的单词
     * 转换为英语句子。
     * @param string $words
     * @param bool $ucAll 是否将所有单词设置为大写
     * @return string
     */
    public static function titleize($words, $ucAll = false)
    {
        $words = static::humanize(static::underscore($words), $ucAll);

        return $ucAll ? StringHelper::mb_ucwords($words, self::encoding()) : StringHelper::mb_ucfirst($words, self::encoding());
    }

    /**
     * 返回以 CamelCase 格式给出的单词。
     *
     * 将 "send_email" 之类的单词转换为 "SendEmail"。
     * 它将从单词中删除非字母数字字符，
     * 所以 "who's online" 将被转换为 "WhoSOnline"。
     * @see variablize()
     * @param string $word 转成驼峰的单词
     * @return string
     */
    public static function camelize($word)
    {
        return str_replace(' ', '', StringHelper::mb_ucwords(preg_replace('/[^\pL\pN]+/u', ' ', $word), self::encoding()));
    }

    /**
     * 转换一个 CamelCase 命名的名称为以空格分隔的单词。
     * 例如，'PostTag' 将转换成 'Post Tag'。
     * @param string $name 将被转换的字符串
     * @param bool $ucwords 是否将每个单词的第一个字母大写
     * @return string 返回这个单词的结果
     */
    public static function camel2words($name, $ucwords = true)
    {
        $label = mb_strtolower(trim(str_replace([
            '-',
            '_',
            '.',
        ], ' ', preg_replace('/(\p{Lu})/u', ' \0', $name))), self::encoding());

        return $ucwords ? StringHelper::mb_ucwords($label, self::encoding()) : $label;
    }

    /**
     * 将 CamelCase 名称转换成小写 ID 单词。
     * 可以使用指定的字符连接 ID 中的单词（默认 '-'）。
     * 例如，'PostTag' 将被转成 'post-tag'。
     * @param string $name 将被转换的字符串
     * @param string $separator 用于连接 ID 中的单词的字符
     * @param bool|string $strict 是否在两个连续大写字符之间插入分隔符，默认 false
     * @return string 生成的结果 ID
     */
    public static function camel2id($name, $separator = '-', $strict = false)
    {
        $regex = $strict ? '/\p{Lu}/u' : '/(?<!\p{Lu})\p{Lu}/u';
        if ($separator === '_') {
            return mb_strtolower(trim(preg_replace($regex, '_\0', $name), '_'), self::encoding());
        }

        return mb_strtolower(trim(str_replace('_', $separator, preg_replace($regex, $separator . '\0', $name)), $separator), self::encoding());
    }

    /**
     * 转换 ID 成一个 CamelCase 名称。
     * ID 中以 `$separator` 分隔的单词（默认 '-'）将连接到 CamelCase 名称中。
     * 例如，'post-tag' 将被转换成 'PostTag'。
     * @param string $id ID 将被转换
     * @param string $separator 用于分隔 ID 中单词的字符
     * @return string 返回结果 CamelCase 命名
     */
    public static function id2camel($id, $separator = '-')
    {
        return str_replace(' ', '', StringHelper::mb_ucwords(str_replace($separator, ' ', $id), self::encoding()));
    }

    /**
     * 转换一些 "CamelCased" 成 "underscored_word"。
     * @param string $words 带下划线的 word(s)
     * @return string
     */
    public static function underscore($words)
    {
        return mb_strtolower(preg_replace('/(?<=\\pL)(\\p{Lu})/u', '_\\1', $words), self::encoding());
    }

    /**
     * $word 返回人类可读的字符串。
     * @param string $word 字符串要人性化
     * @param bool $ucAll 是否将所有单词设置为大写
     * @return string
     */
    public static function humanize($word, $ucAll = false)
    {
        $word = str_replace('_', ' ', preg_replace('/_id$/', '', $word));
        $encoding = self::encoding();

        return $ucAll ? StringHelper::mb_ucwords($word, $encoding) : StringHelper::mb_ucfirst($word, $encoding);
    }

    /**
     * 与 camelize 相同，但是第一个 char 是小写的。
     *
     * 将 "send_email" 之类的单词转换为 "sendEmail"。
     * 它将从单词中删除非字母数字字符，
     * 所以 "who's online" 将被转换成 "whoSOnline"。
     * @param string $word to lowerCamelCase
     * @return string
     */
    public static function variablize($word)
    {
        $word = static::camelize($word);

        return mb_strtolower(mb_substr($word, 0, 1, self::encoding())) . mb_substr($word, 1, null, self::encoding());
    }

    /**
     * 将类名转换为其表名（复数）命名约定。
     *
     * 例如，"Person" 转换成小写 "people"。
     * @param string $className 获取相关 table_name 的类名
     * @return string
     */
    public static function tableize($className)
    {
        return static::pluralize(static::underscore($className));
    }

    /**
     * 返回一个字符串，其中所有空格都转换为给定的替换，
     * 去掉非单词字符，将其余字符音译。
     *
     * 如果 intl 扩展不可用，则使用仅转换拉丁字符的回退
     * 然后把剩下的移除。
     * 您可以通过 $transliteration 属性自定义字符映射。
     *
     * @param string $string 要转换的任意字符串
     * @param string $replacement 用于空格的替换
     * @param bool $lowercase 是否返回小写字符串。默认是 `true`。
     * @return string 转换后的字符串。
     */
    public static function slug($string, $replacement = '-', $lowercase = true)
    {
        $parts = explode($replacement, static::transliterate($string));

        $replaced = array_map(function ($element) use ($replacement) {
            $element = preg_replace('/[^a-zA-Z0-9=\s—–]+/u', '', $element);
            return preg_replace('/[=\s—–]+/u', $replacement, $element);
        }, $parts);

        $string = trim(implode($replacement, $replaced), $replacement);

        return $lowercase ? strtolower($string) : $string;
    }

    /**
     * 返回字符串的音译版本。
     *
     * 如果 intl 扩展不可用，使用仅转换拉丁字符的回滚
     * 然后把剩下的移除。
     * 您可以通过 $transliteration 属性自定义字符映射。
     *
     * @param string $string 输入字符串
     * @param string|\Transliterator $transliterator 可以是
     * [[\Transliterator]] 或者字符串从中可以构建 [[\Transliterator]]。
     * @return string
     * @since 2.0.7 以上版本中此方法是公共的。
     */
    public static function transliterate($string, $transliterator = null)
    {
        if (static::hasIntl()) {
            if ($transliterator === null) {
                $transliterator = static::$transliterator;
            }

            return transliterator_transliterate($transliterator, $string);
        }

        return strtr($string, static::$transliteration);
    }

    /**
     * @return bool 如果 intl 扩展已加载
     */
    protected static function hasIntl()
    {
        return extension_loaded('intl');
    }

    /**
     * 将表名转换为其类名。
     *
     * 例如，将 "people" 转换成 "Person"。
     * @param string $tableName
     * @return string
     */
    public static function classify($tableName)
    {
        return static::camelize(static::singularize($tableName));
    }

    /**
     * 将数字转换为英文序数形式。例如，将 13 转成 13th，2 转成 2nd ...
     * @param int $number 获取其序数值的数字
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
     * 将单词列表转换为句子。
     *
     * 最后几句话要特别处理。例如，
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
     * @param array $words 要转换为字符串的单词
     * @param string $twoWordsConnector 当只有两个单词时连接单词的字符串
     * @param string $lastWordConnector 连接最后两个单词的字符串。
     * 如果这是空的，它将获取 `$twoWordsConnector` 的值。
     * @param string $connector 连接单词的字符串，
     * 而不是由 $lastWordConnector 和 $twoWordsConnector 连接的单词。
     * @return string 生成的句子
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
