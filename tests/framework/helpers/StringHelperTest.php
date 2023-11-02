<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\StringHelper;
use yiiunit\TestCase;

/**
 * StringHelperTest.
 * @group helpers
 */
class StringHelperTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();
    }

    public function testStrlen()
    {
        $this->assertEquals(4, StringHelper::byteLength('this'));
        $this->assertEquals(6, StringHelper::byteLength('это'));
    }

    public function testSubstr()
    {
        $this->assertEquals('th', StringHelper::byteSubstr('this', 0, 2));
        $this->assertEquals('э', StringHelper::byteSubstr('это', 0, 2));

        $this->assertEquals('abcdef', StringHelper::byteSubstr('abcdef', 0));
        $this->assertEquals('abcdef', StringHelper::byteSubstr('abcdef', 0, null));

        $this->assertEquals('de', StringHelper::byteSubstr('abcdef', 3, 2));
        $this->assertEquals('def', StringHelper::byteSubstr('abcdef', 3));
        $this->assertEquals('def', StringHelper::byteSubstr('abcdef', 3, null));

        $this->assertEquals('cd', StringHelper::byteSubstr('abcdef', -4, 2));
        $this->assertEquals('cdef', StringHelper::byteSubstr('abcdef', -4));
        $this->assertEquals('cdef', StringHelper::byteSubstr('abcdef', -4, null));

        $this->assertEquals('', StringHelper::byteSubstr('abcdef', 4, 0));
        $this->assertEquals('', StringHelper::byteSubstr('abcdef', -4, 0));

        $this->assertEquals('это', StringHelper::byteSubstr('это', 0));
        $this->assertEquals('это', StringHelper::byteSubstr('это', 0, null));

        $this->assertEquals('т', StringHelper::byteSubstr('это', 2, 2));
        $this->assertEquals('то', StringHelper::byteSubstr('это', 2));
        $this->assertEquals('то', StringHelper::byteSubstr('это', 2, null));

        $this->assertEquals('т', StringHelper::byteSubstr('это', -4, 2));
        $this->assertEquals('то', StringHelper::byteSubstr('это', -4));
        $this->assertEquals('то', StringHelper::byteSubstr('это', -4, null));

        $this->assertEquals('', StringHelper::byteSubstr('это', 4, 0));
        $this->assertEquals('', StringHelper::byteSubstr('это', -4, 0));
    }

    public function testBasename()
    {
        $this->assertEquals('', StringHelper::basename(''));

        $this->assertEquals('file', StringHelper::basename('file'));
        $this->assertEquals('file.test', StringHelper::basename('file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('/file'));
        $this->assertEquals('file.test', StringHelper::basename('/file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('/file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('/path/to/file'));
        $this->assertEquals('file.test', StringHelper::basename('/path/to/file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('/path/to/file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('\file'));
        $this->assertEquals('file.test', StringHelper::basename('\file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('\file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('C:\file'));
        $this->assertEquals('file.test', StringHelper::basename('C:\file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('C:\file.test', '.test'));

        $this->assertEquals('file', StringHelper::basename('C:\path\to\file'));
        $this->assertEquals('file.test', StringHelper::basename('C:\path\to\file.test', '.test2'));
        $this->assertEquals('file', StringHelper::basename('C:\path\to\file.test', '.test'));

        // mixed paths
        $this->assertEquals('file.test', StringHelper::basename('/path\to/file.test'));
        $this->assertEquals('file.test', StringHelper::basename('/path/to\file.test'));
        $this->assertEquals('file.test', StringHelper::basename('\path/to\file.test'));

        // \ and / in suffix
        $this->assertEquals('file', StringHelper::basename('/path/to/filete/st', 'te/st'));
        $this->assertEquals('st', StringHelper::basename('/path/to/filete/st', 'te\st'));
        $this->assertEquals('file', StringHelper::basename('/path/to/filete\st', 'te\st'));
        $this->assertEquals('st', StringHelper::basename('/path/to/filete\st', 'te/st'));

        // https://www.php.net/manual/en/function.basename.php#72254
        $this->assertEquals('foo', StringHelper::basename('/bar/foo/'));
        $this->assertEquals('foo', StringHelper::basename('\\bar\\foo\\'));
    }

    public function testTruncate()
    {
        $this->assertEquals('привет, я multibyte...', StringHelper::truncate('привет, я multibyte строка!', 20));
        $this->assertEquals('Не трогаем строку', StringHelper::truncate('Не трогаем строку', 20));
        $this->assertEquals('исполь!!!', StringHelper::truncate('используем восклицательные знаки', 6, '!!!'));

        // With Html
        $this->assertEquals('<span>This is a test</span>...', StringHelper::truncate('<span>This is a test sentance</span>', 14, '...', null, true));
        $this->assertEquals('<span>This is a test</span>...', StringHelper::truncate('<span>This is a test </span>sentance', 14, '...', null, true));
        $this->assertEquals('<span>This is a test </span><strong>for</strong>...', StringHelper::truncate('<span>This is a test </span><strong>for a sentance</strong>', 18, '...', null, true));
        $this->assertEquals('<span>This is a test</span><strong> for</strong>...', StringHelper::truncate('<span>This is a test</span><strong> for a sentance</strong>', 18, '...', null, true));

        $this->assertEquals('<span><img src="image.png" />This is a test</span>...', StringHelper::truncate('<span><img src="image.png" />This is a test sentance</span>', 14, '...', null, true));
        $this->assertEquals('<span><img src="image.png" />This is a test</span>...', StringHelper::truncate('<span><img src="image.png" />This is a test </span>sentance', 14, '...', null, true));
        $this->assertEquals('<span><img src="image.png" />This is a test </span><strong>for</strong>...', StringHelper::truncate('<span><img src="image.png" />This is a test </span><strong>for a sentance</strong>', 18, '...', null, true));

        $this->assertEquals('<p>This is a test</p><ul><li>bullet1</li><li>b</li></ul>...', StringHelper::truncate('<p>This is a test</p><ul><li>bullet1</li><li>bullet2</li><li>bullet3</li><li>bullet4</li></ul>', 22, '...', null, true));

        $this->assertEquals('<div><ul><li>bullet1</li><li><div>b</div></li></ul></div>...', StringHelper::truncate('<div><ul><li>bullet1</li><li><div>bullet2</div></li></ul><br></div>', 8, '...', null, true));
    }

    public function testTruncateWords()
    {
        $this->assertEquals('это тестовая multibyte строка', StringHelper::truncateWords('это тестовая multibyte строка', 5));
        $this->assertEquals('это тестовая multibyte...', StringHelper::truncateWords('это тестовая multibyte строка', 3));
        $this->assertEquals('это тестовая multibyte!!!', StringHelper::truncateWords('это тестовая multibyte строка', 3, '!!!'));
        $this->assertEquals('это строка с          неожиданными...', StringHelper::truncateWords('это строка с          неожиданными пробелами', 4));

        $this->assertEquals('lorem ipsum', StringHelper::truncateWords('lorem ipsum', 3, '...', true));
        $this->assertEquals(' lorem ipsum', StringHelper::truncateWords(' lorem ipsum', 3, '...', true));
        // With Html
        $this->assertEquals('<span>This is a test</span>...', StringHelper::truncateWords('<span>This is a test sentance</span>', 4, '...', true));
        $this->assertEquals('<span>This is a test </span><strong>for</strong>...', StringHelper::truncateWords('<span>This is a test </span><strong>for a sentance</strong>', 5, '...', true));
        $this->assertEquals('<span>This is a test</span><strong> for</strong>...', StringHelper::truncateWords('<span>This is a test</span><strong> for a sentance</strong>', 5, '...', true));
        $this->assertEquals('<p> раз два три четыре пять </p> <p> шесть</p>...', StringHelper::truncateWords('<p> раз два три четыре пять </p> <p> шесть семь восемь девять десять</p>', 6, '...', true));

        $this->assertEquals('<span><img src="image.png" />This is a test</span>...', StringHelper::truncateWords('<span><img src="image.png" />This is a test sentance</span>', 4, '...', true));
        $this->assertEquals('<span><img src="image.png" />This is a test </span><strong>for</strong>...', StringHelper::truncateWords('<span><img src="image.png" />This is a test </span><strong>for a sentance</strong>', 5, '...', true));
        $this->assertEquals('<span><img src="image.png" />This is a test</span><strong> for</strong>...', StringHelper::truncateWords('<span><img src="image.png" />This is a test</span><strong> for a sentance</strong>', 5, '...', true));
    }

    /**
     * @dataProvider providerStartsWith
     * @param bool $result
     * @param string $string
     * @param string|null $with
     */
    public function testStartsWith($result, $string, $with)
    {
        // case sensitive version check
        $this->assertSame($result, StringHelper::startsWith($string, $with));
        // case insensitive version check
        $this->assertSame($result, StringHelper::startsWith($string, $with, false));
    }

    /**
     * Rules that should work the same for case-sensitive and case-insensitive `startsWith()`.
     */
    public function providerStartsWith()
    {
        return [
            // positive check
            [true, '', ''],
            [true, '', null],
            [true, 'string', ''],
            [true, ' string', ' '],
            [true, 'abc', 'abc'],
            [true, 'Bürger', 'Bürger'],
            [true, '我Я multibyte', '我Я'],
            [true, 'Qנטשופ צרכנות', 'Qנ'],
            [true, 'ไทย.idn.icann.org', 'ไ'],
            [true, '!?+', "\x21\x3F"],
            [true, "\x21?+", '!?'],
            // false-positive check
            [false, '', ' '],
            [false, ' ', '  '],
            [false, 'Abc', 'Abcde'],
            [false, 'abc', 'abe'],
            [false, 'abc', 'b'],
            [false, 'abc', 'c'],
        ];
    }

    public function testStartsWithCaseSensitive()
    {
        $this->assertFalse(StringHelper::startsWith('Abc', 'a'));
        $this->assertFalse(StringHelper::startsWith('üЯ multibyte', 'Üя multibyte'));
    }

    public function testStartsWithCaseInsensitive()
    {
        $this->assertTrue(StringHelper::startsWith('sTrInG', 'StRiNg', false));
        $this->assertTrue(StringHelper::startsWith('CaSe', 'cAs', false));
        $this->assertTrue(StringHelper::startsWith('HTTP://BÜrger.DE/', 'http://bürger.de', false));
        $this->assertTrue(StringHelper::startsWith('üЯйΨB', 'ÜяЙΨ', false));
    }

    /**
     * @dataProvider providerEndsWith
     * @param bool $result
     * @param string $string
     * @param string|null $with
     */
    public function testEndsWith($result, $string, $with)
    {
        // case sensitive version check
        $this->assertSame($result, StringHelper::endsWith($string, $with));
        // case insensitive version check
        $this->assertSame($result, StringHelper::endsWith($string, $with, false));
    }

    /**
     * Rules that should work the same for case-sensitive and case-insensitive `endsWith()`.
     */
    public function providerEndsWith()
    {
        return [
            // positive check
            [true, '', ''],
            [true, '', null],
            [true, 'string', ''],
            [true, 'string ', ' '],
            [true, 'string', 'g'],
            [true, 'abc', 'abc'],
            [true, 'Bürger', 'Bürger'],
            [true, 'Я multibyte строка我!', ' строка我!'],
            [true, '+!?', "\x21\x3F"],
            [true, "+\x21?", "!\x3F"],
            [true, 'נטשופ צרכנות', 'ת'],
            // false-positive check
            [false, '', ' '],
            [false, ' ', '  '],
            [false, 'aaa', 'aaaa'],
            [false, 'abc', 'abe'],
            [false, 'abc', 'a'],
            [false, 'abc', 'b'],
        ];
    }

    public function testEndsWithCaseSensitive()
    {
        $this->assertFalse(StringHelper::endsWith('string', 'G'));
        $this->assertFalse(StringHelper::endsWith('multibyte строка', 'А'));
    }

    public function testEndsWithCaseInsensitive()
    {
        $this->assertTrue(StringHelper::endsWith('sTrInG', 'StRiNg', false));
        $this->assertTrue(StringHelper::endsWith('string', 'nG', false));
        $this->assertTrue(StringHelper::endsWith('BüЯйΨ', 'ÜяЙΨ', false));
    }

    public function testExplode()
    {
        $this->assertEquals(['It', 'is', 'a first', 'test'], StringHelper::explode('It, is, a first, test'));
        $this->assertEquals(['It', 'is', 'a test with trimmed digits', '0', '1', '2'], StringHelper::explode('It, is, a test with trimmed digits, 0, 1, 2', ',', true, true));
        $this->assertEquals(['It', 'is', 'a second', 'test'], StringHelper::explode('It+ is+ a second+ test', '+'));
        $this->assertEquals(['Save', '', '', 'empty trimmed string'], StringHelper::explode('Save, ,, empty trimmed string', ','));
        $this->assertEquals(['44', '512'], StringHelper::explode('0 0 440 512', ' ', '0', true));
        $this->assertEquals(['Здесь', 'multibyte', 'строка'], StringHelper::explode('Здесь我 multibyte我 строка', '我'));
        $this->assertEquals(['Disable', '  trim  ', 'here but ignore empty'], StringHelper::explode('Disable,  trim  ,,,here but ignore empty', ',', false, true));
        $this->assertEquals(['It/', ' is?', ' a', ' test with rtrim'], StringHelper::explode('It/, is?, a , test with rtrim', ',', 'rtrim'));
        $this->assertEquals(['It', ' is', ' a ', ' test with closure'], StringHelper::explode('It/, is?, a , test with closure', ',', function ($value) { return trim($value, '/?'); }));
    }

    public function testWordCount()
    {
        $this->assertEquals(3, StringHelper::countWords('china 中国 ㄍㄐㄋㄎㄌ'));
        $this->assertEquals(4, StringHelper::countWords('и много тут слов?'));
        $this->assertEquals(4, StringHelper::countWords("и\rмного\r\nтут\nслов?"));
        $this->assertEquals(1, StringHelper::countWords('крем-брюле'));
        $this->assertEquals(1, StringHelper::countWords(' слово '));
    }

    /**
     * @dataProvider base64UrlEncodedStringsProvider
     * @param $input
     * @param $base64UrlEncoded
     */
    public function testBase64UrlEncode($input, $base64UrlEncoded)
    {
        $encoded = StringHelper::base64UrlEncode($input);
        $this->assertEquals($base64UrlEncoded, $encoded);
    }

    /**
     * @dataProvider base64UrlEncodedStringsProvider
     * @param $output
     * @param $base64UrlEncoded
     */
    public function testBase64UrlDecode($output, $base64UrlEncoded)
    {
        $decoded = StringHelper::base64UrlDecode($base64UrlEncoded);
        $this->assertEquals($output, $decoded);
    }

    public function base64UrlEncodedStringsProvider()
    {
        return [
            ['This is an encoded string', 'VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw=='],
            ['subjects?_d=1', 'c3ViamVjdHM_X2Q9MQ=='],
            ['subjects>_d=1', 'c3ViamVjdHM-X2Q9MQ=='],
            ['Это закодированная строка', '0K3RgtC-INC30LDQutC-0LTQuNGA0L7QstCw0L3QvdCw0Y8g0YHRgtGA0L7QutCw'],
        ];
    }

    /**
     * Data provider for [[testMatchWildcard()]]
     * @return array test data.
     */
    public function dataProviderMatchWildcard()
    {
        return [
            // *
            ['*', 'any', true],
            ['*', '', true],
            ['begin*end', 'begin-middle-end', true],
            ['begin*end', 'beginend', true],
            ['begin*end', 'begin-d', false],
            ['*end', 'beginend', true],
            ['*end', 'begin', false],
            ['begin*', 'begin-end', true],
            ['begin*', 'end', false],
            ['begin*', 'before-begin', false],
            // ?
            ['begin?end', 'begin1end', true],
            ['begin?end', 'beginend', false],
            ['begin??end', 'begin12end', true],
            ['begin??end', 'begin1end', false],
            // []
            ['gr[ae]y', 'gray', true],
            ['gr[ae]y', 'grey', true],
            ['gr[ae]y', 'groy', false],
            ['a[2-8]', 'a1', false],
            ['a[2-8]', 'a3', true],
            ['[][!]', ']', true],
            ['[-1]', '-', true],
            // [!]
            ['gr[!ae]y', 'gray', false],
            ['gr[!ae]y', 'grey', false],
            ['gr[!ae]y', 'groy', true],
            ['a[!2-8]', 'a1', true],
            ['a[!2-8]', 'a3', false],
            // -
            ['a-z', 'a-z', true],
            ['a-z', 'a-c', false],
            // slashes
            ['begin/*/end', 'begin/middle/end', true],
            ['begin/*/end', 'begin/two/steps/end', true],
            ['begin/*/end', 'begin/end', false],
            ['begin\\\\*\\\\end', 'begin\middle\end', true],
            ['begin\\\\*\\\\end', 'begin\two\steps\end', true],
            ['begin\\\\*\\\\end', 'begin\end', false],
            // dots
            ['begin.*.end', 'begin.middle.end', true],
            ['begin.*.end', 'begin.two.steps.end', true],
            ['begin.*.end', 'begin.end', false],
            // case
            ['begin*end', 'BEGIN-middle-END', false],
            ['begin*end', 'BEGIN-middle-END', true, ['caseSensitive' => false]],
            // file path
            ['begin/*/end', 'begin/middle/end', true, ['filePath' => true]],
            ['begin/*/end', 'begin/two/steps/end', false, ['filePath' => true]],
            ['begin\\\\*\\\\end', 'begin\middle\end', true, ['filePath' => true]],
            ['begin\\\\*\\\\end', 'begin\two\steps\end', false, ['filePath' => true]],
            ['*', 'any', true, ['filePath' => true]],
            ['*', 'any/path', false, ['filePath' => true]],
            ['[.-0]', 'any/path', false, ['filePath' => true]],
            ['*', '.dotenv', true, ['filePath' => true]],
            // escaping
            ['\*\?', '*?', true],
            ['\*\?', 'zz', false],
            ['begin\*\end', 'begin\middle\end', true, ['escape' => false]],
            ['begin\*\end', 'begin\two\steps\end', true, ['escape' => false]],
            ['begin\*\end', 'begin\end', false, ['escape' => false]],
            ['begin\*\end', 'begin\middle\end', true, ['filePath' => true, 'escape' => false]],
            ['begin\*\end', 'begin\two\steps\end', false, ['filePath' => true, 'escape' => false]],
        ];
    }

    /**
     * @dataProvider dataProviderMatchWildcard
     *
     * @param string $pattern
     * @param string $string
     * @param bool $expectedResult
     * @param array $options
     */
    public function testMatchWildcard($pattern, $string, $expectedResult, $options = [])
    {
        $this->assertSame($expectedResult, StringHelper::matchWildcard($pattern, $string, $options));
    }

    public function dataProviderMb_ucfirst()
    {
        return [
            ['foo', 'Foo'],
            ['foo bar', 'Foo bar'],
            ['👍🏻 foo bar', '👍🏻 foo bar'],
            ['', ''],
            [null, ''],
            ['здесь我 multibyte我 строка', 'Здесь我 multibyte我 строка'],
        ];
    }

    /**
     * @param string $string
     * @param string $expectedResult
     * @dataProvider dataProviderMb_ucfirst
     */
    public function testMb_ucfirst($string, $expectedResult)
    {
        $this->assertSame($expectedResult, StringHelper::mb_ucfirst($string));
    }

    public function dataProviderMb_ucwords()
    {
        return [
            ['foo', 'Foo'],
            ['foo bar', 'Foo Bar'],
            ['!foo bar-baz', '!foo Bar-baz'],
            [' foo BAR', ' Foo BAR'],
            [' ! foo BAR', ' ! Foo BAR'],
            ["\tfoo\nbar  baz", "\tFoo\nBar  Baz"],
            ['👍🏻 foo bar', '👍🏻 Foo Bar'],
            ['', ''],
            ['0', '0'],
            [null, ''],
            ['здесь我 multibyte我 строка', 'Здесь我 Multibyte我 Строка'],
            ['p0 upload', 'P0 Upload'],
            ['p5 upload', 'P5 Upload'],
        ];
    }

    /**
     * @param string $string
     * @param string $expectedResult
     * @dataProvider dataProviderMb_ucwords
     */
    public function testMb_ucwords($string, $expectedResult)
    {
        $this->assertSame($expectedResult, StringHelper::mb_ucwords($string));
    }

    /**
     * @param string $string
     * @param string $expectedResult
     * @dataProvider dataProviderDirname
     */
    public function testDirname($string, $expectedResult)
    {
        $this->assertSame($expectedResult, StringHelper::dirname($string));
    }

    public function dataProviderDirname()
    {
        return [
            ['\\foo\\bar\\test', '\foo\bar'],
            ['\\foo/bar\\test', '\foo/bar'],
            ['\\foo\\bar\\test\\', '\foo\bar'],
            ['foo/bar/test', 'foo/bar'],
            ['foo', ''],
            ['', ''],
        ];
    }

    public function testMask()
    {
        // Standard masking
        $this->assertSame('12******90', StringHelper::mask('1234567890', 2, 6));
        $this->assertSame('a********j', StringHelper::mask('abcdefghij', 1, 8));
        $this->assertSame('*************', StringHelper::mask('Hello, World!', 0, 13));
        $this->assertSame('************!', StringHelper::mask('Hello, World!', 0, 12));
        $this->assertSame('Hello, *orld!', StringHelper::mask('Hello, World!', 7, 1));
        $this->assertSame('Saleh Hashemi', StringHelper::mask('Saleh Hashemi', 0, 0));

        // Different Mask Character
        $this->assertSame('12######90', StringHelper::mask('1234567890', 2, 6, '#'));

        // Positions outside the string
        $this->assertSame('1234567890', StringHelper::mask('1234567890', 20, 6));
        $this->assertSame('1234567890', StringHelper::mask('1234567890', -20, 6));

        // Negative values for start
        $this->assertSame('1234****90', StringHelper::mask('1234567890', -6, 4));

        // type-related edge case
        $this->assertSame('1234****90', StringHelper::mask(1234567890, -6, 4));

        // Multibyte characters
        $this->assertSame('你**', StringHelper::mask('你好吗', 1, 2));
        $this->assertSame('你好吗', StringHelper::mask('你好吗', 4, 2));

        // Special characters
        $this->assertSame('em**l@email.com', StringHelper::mask('email@email.com', 2, 2));
        $this->assertSame('******email.com', StringHelper::mask('email@email.com', 0, 6));
    }

    /**
     * @param string $string
     * @param string $start
     * @param string $end
     * @param string $expectedResult
     * @dataProvider dataProviderFindBetween
     */
    public function testFindBetween($string, $start, $end, $expectedResult)
    {
        $this->assertSame($expectedResult, StringHelper::findBetween($string, $start, $end));
    }

    public function dataProviderFindBetween()
    {
        return [
            ['hello world hello', ' hello', ' world', null],  // end before start
            ['This is a sample string', ' is ', ' string', 'a sample'],  // normal case
            ['startendstart', 'start', 'end', ''],  // end before start
            ['startmiddleend', 'start', 'end', 'middle'],  // normal case
            ['startend', 'start', 'end', ''],  // end immediately follows start
            ['multiple start start end end', 'start ', ' end', 'start end'],  // multiple starts and ends
            ['', 'start', 'end', null],  // empty string
            ['no delimiters here', 'start', 'end', null],  // no start and end
            ['start only', 'start', 'end', null], // start found but no end
            ['end only', 'start', 'end', null], // end found but no start
            ['spécial !@#$%^&*()', 'spé', '&*()', 'cial !@#$%^'],  // Special characters
            ['من صالح هاشمی هستم', 'من ', ' هستم', 'صالح هاشمی'], // other languages
        ];
    }
}
