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
	
    public function testParseUri()
    {
        $r = StringHelper::parseUri('http://google.de/search?q=yii2');
        $this->assertArrayNotHasKey('fragment', $r);
        $this->assertEquals(['scheme' => 'http', 'authority' => 'google.de', 'path' => '/search', 'query' => 'q=yii2'], $r);
        $r = StringHelper::parseUri('assets://my/path/file.css');
        $this->assertArrayNotHasKey('query', $r);
        $this->assertEquals(['scheme' => 'assets', 'authority' => 'my', 'path' => '/path/file.css'], $r);
        $this->assertEquals(['scheme' => 'assets', 'path' => '/my/path/file.css'], $r = StringHelper::parseUri('assets:///my/path/file.css'));
        $this->assertArrayNotHasKey('authority', $r);
        $this->assertArrayNotHasKey('matches', $r);
        $this->assertArrayHasKey('matches', StringHelper::parseUri('assets://localhost/path', true));
		$this->assertEmpty(StringHelper::parseUri(''));
		$this->assertEquals(['authority' => 'abc', 'path' => '/fdg'], StringHelper::parseUri('//abc/fdg'));
		$this->assertEquals(['path' => 'testfile.php'], StringHelper::parseUri('testfile.php'));
		$this->assertEquals(['path' => '/file'], StringHelper::parseUri('///file'));
		$this->assertEquals(['scheme' => 'file', 'path' =>'/'], StringHelper::parseUri('file:///'));
		
	}
}
