<?php
namespace yiiunit\framework\helpers;

use yii\helpers\StringHelper;
use yiiunit\TestCase;

/**
 * StringHelperTest
 * @group helpers
 */
class StringHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
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

        $this->assertEquals('de',     StringHelper::byteSubstr('abcdef', 3, 2));
        $this->assertEquals('def',    StringHelper::byteSubstr('abcdef', 3));
        $this->assertEquals('def',    StringHelper::byteSubstr('abcdef', 3, null));

        $this->assertEquals('cd',     StringHelper::byteSubstr('abcdef', -4, 2));
        $this->assertEquals('cdef',   StringHelper::byteSubstr('abcdef', -4));
        $this->assertEquals('cdef',   StringHelper::byteSubstr('abcdef', -4, null));

        $this->assertEquals('',   StringHelper::byteSubstr('abcdef', 4, 0));
        $this->assertEquals('',   StringHelper::byteSubstr('abcdef', -4, 0));

        $this->assertEquals('это', StringHelper::byteSubstr('это', 0));
        $this->assertEquals('это', StringHelper::byteSubstr('это', 0, null));

        $this->assertEquals('т',     StringHelper::byteSubstr('это', 2, 2));
        $this->assertEquals('то',    StringHelper::byteSubstr('это', 2));
        $this->assertEquals('то',    StringHelper::byteSubstr('это', 2, null));

        $this->assertEquals('т',     StringHelper::byteSubstr('это', -4, 2));
        $this->assertEquals('то',   StringHelper::byteSubstr('это', -4));
        $this->assertEquals('то',   StringHelper::byteSubstr('это', -4, null));

        $this->assertEquals('',   StringHelper::byteSubstr('это', 4, 0));
        $this->assertEquals('',   StringHelper::byteSubstr('это', -4, 0));
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

        // http://www.php.net/manual/en/function.basename.php#72254
        $this->assertEquals('foo', StringHelper::basename('/bar/foo/'));
        $this->assertEquals('foo', StringHelper::basename('\\bar\\foo\\'));
    }
    
    public function testTruncate()
    {
        $this->assertEquals('привет, я multibyte...', StringHelper::truncate('привет, я multibyte строка!', 20));
        $this->assertEquals('Не трогаем строку', StringHelper::truncate('Не трогаем строку', 20));
        $this->assertEquals('исполь!!!', StringHelper::truncate('используем восклицательные знаки', 6, '!!!'));
    }
    
    public function testTruncateWords()
    {
        $this->assertEquals('это тестовая multibyte строка', StringHelper::truncateWords('это тестовая multibyte строка', 5));
        $this->assertEquals('это тестовая multibyte...', StringHelper::truncateWords('это тестовая multibyte строка', 3));
        $this->assertEquals('это тестовая multibyte!!!', StringHelper::truncateWords('это тестовая multibyte строка', 3, '!!!'));
        $this->assertEquals('это строка с          неожиданными...', StringHelper::truncateWords('это строка с          неожиданными пробелами', 4));
    }

    /**
     * @dataProvider providerStartsWith
     */
    public function testStartsWith($result, $string, $with)
    {
        // case sensitive version check
        $this->assertSame($result, StringHelper::startsWith($string, $with));
        // case insensitive version check
        $this->assertSame($result, StringHelper::startsWith($string, $with, false));
    }

    /**
     * Rules that should work the same for case-sensitive and case-insensitive `startsWith()`
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
     */
    public function testEndsWith($result, $string, $with)
    {
        // case sensitive version check
        $this->assertSame($result, StringHelper::endsWith($string, $with));
        // case insensitive version check
        $this->assertSame($result, StringHelper::endsWith($string, $with, false));
    }

    /**
     * Rules that should work the same for case-sensitive and case-insensitive `endsWith()`
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
}
