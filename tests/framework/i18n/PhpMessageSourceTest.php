<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\i18n;

use Yii;
use yii\base\InvalidArgumentException;
use yii\i18n\PhpMessageSource;
use yii\log\Logger;
use yiiunit\TestCase;

/**
 * @group i18n
 */
class PhpMessageSourceTest extends TestCase
{
    /**
     * @var string
     */
    private $basePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        $this->basePath = Yii::getAlias('@yiiunit/data/i18n/messages');
    }

    /**
     * @return PhpMessageSource
     */
    private function createSource(array $config = [])
    {
        return new PhpMessageSource(array_merge([
            'basePath' => '@yiiunit/data/i18n/messages',
        ], $config));
    }

    /**
     * @return array[]
     */
    private function getLogMessages(int $level)
    {
        return array_filter(Yii::getLogger()->messages, function ($msg) use ($level) {
            return $msg[1] === $level
                && strpos($msg[2], PhpMessageSource::class) === 0;
        });
    }

    public function testGetMessageFilePathWithoutFileMap(): void
    {
        $source = $this->createSource();
        $path = $this->invokeMethod($source, 'getMessageFilePath', ['app', 'en']);

        $this->assertStringEndsWith('/en/app.php', $path);
    }

    public function testGetMessageFilePathWithFileMap(): void
    {
        $source = $this->createSource([
            'fileMap' => ['app' => 'application.php'],
        ]);
        $path = $this->invokeMethod($source, 'getMessageFilePath', ['app', 'en']);

        $this->assertStringEndsWith('/en/application.php', $path);
    }

    public function testGetMessageFilePathUnmappedCategoryIgnoresFileMap(): void
    {
        $source = $this->createSource([
            'fileMap' => ['app' => 'application.php'],
        ]);
        $path = $this->invokeMethod($source, 'getMessageFilePath', ['other', 'en']);

        $this->assertStringEndsWith('/en/other.php', $path);
    }

    public function testGetMessageFilePathConvertsBackslashesToSlashes(): void
    {
        $source = $this->createSource();
        $path = $this->invokeMethod($source, 'getMessageFilePath', ['app\\models\\User', 'en']);

        $this->assertStringEndsWith('/en/app/models/User.php', $path);
    }

    public function testGetMessageFilePathResolvesAlias(): void
    {
        $source = $this->createSource();
        $path = $this->invokeMethod($source, 'getMessageFilePath', ['test', 'en']);

        $this->assertStringStartsWith('/', $path);
        $this->assertStringNotContainsString('@', $path);
    }

    public function testGetMessageFilePathWithEmptyLanguage(): void
    {
        $source = $this->createSource();
        $path = $this->invokeMethod($source, 'getMessageFilePath', ['test', '']);

        $this->assertStringEndsWith('//test.php', $path);
    }

    /**
     * @dataProvider invalidLanguageCodeProvider
     */
    public function testGetMessageFilePathThrowsOnInvalidLanguageCode(string $language): void
    {
        $source = $this->createSource();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid language code: "%s".', $language));
        $this->invokeMethod($source, 'getMessageFilePath', ['test', $language]);
    }

    public static function invalidLanguageCodeProvider(): array
    {
        return [
            'with spaces' => ['en US'],
            'with special chars' => ['en@US'],
            'with dots' => ['en.US'],
            'with slash' => ['en/US'],
        ];
    }

    /**
     * @dataProvider validLanguageCodeProvider
     */
    public function testGetMessageFilePathAcceptsValidLanguageCodes(string $language): void
    {
        $source = $this->createSource();
        $path = $this->invokeMethod($source, 'getMessageFilePath', ['test', $language]);

        $this->assertStringContainsString("/$language/", $path);
    }

    public static function validLanguageCodeProvider(): array
    {
        return [
            'simple' => ['en'],
            'with region' => ['en-US'],
            'with numeric region' => ['en-150'],
            'with underscore' => ['zh_CN'],
            'uppercase' => ['EN'],
        ];
    }

    public function testLoadMessagesFromExistingFile(): void
    {
        $source = $this->createSource();
        $file = $this->basePath . '/de/test.php';

        $messages = $this->invokeMethod($source, 'loadMessagesFromFile', [$file]);

        $this->assertIsArray($messages);
        $this->assertSame('Hallo Welt!', $messages['Hello world!']);
    }

    public function testLoadMessagesFromFileWithNonArrayReturnGivesEmptyArray(): void
    {
        $source = $this->createSource();
        $file = $this->basePath . '/it/test.php';

        $messages = $this->invokeMethod($source, 'loadMessagesFromFile', [$file]);

        $this->assertSame([], $messages);
    }

    public function testLoadMessagesFromMissingFileReturnsNull(): void
    {
        $source = $this->createSource();

        $result = $this->invokeMethod($source, 'loadMessagesFromFile', ['/nonexistent/path.php']);

        $this->assertNull($result);
    }

    public function testFallbackFromRegionToLanguage(): void
    {
        $source = $this->createSource();
        $messages = $this->invokeMethod($source, 'loadMessages', ['test', 'de-DE']);

        $this->assertSame('Der Hund rennt schnell.', $messages['The dog runs fast.']);
        $this->assertSame('Hallo Welt!', $messages['Hello world!']);
    }

    public function testFallbackToLanguageWhenRegionFileMissing(): void
    {
        $source = $this->createSource();
        $messages = $this->invokeMethod($source, 'loadMessages', ['test', 'ru-RU']);

        $this->assertSame('Собака бегает быстро.', $messages['The dog runs fast.']);
    }

    public function testSourceLanguageFallback(): void
    {
        $source = $this->createSource(['sourceLanguage' => 'en-US']);
        $messages = $this->invokeMethod($source, 'loadMessages', ['test', 'en']);

        $this->assertSame('The dog runs fast (en-US).', $messages['The dog runs fast.']);
    }

    public function testWarningWhenNoFileAndNoFallback(): void
    {
        $source = $this->createSource();
        $logger = Yii::getLogger();
        $logger->messages = [];

        $messages = $this->invokeMethod($source, 'loadMessages', ['test', 'xx']);

        $this->assertIsArray($messages);
        $warnings = $this->getLogMessages(Logger::LEVEL_WARNING);
        $this->assertNotEmpty($warnings);
    }

    public function testFallbackMergeBehavior(): void
    {
        $source = $this->createSource();
        $messages = $this->invokeMethod($source, 'loadMessages', ['test', 'fr-FR']);

        $this->assertSame('Le chien court vite.', $messages['The dog runs fast.'], 'Existing translation must not be overridden by fallback');
        $this->assertSame('Bonjour le monde!', $messages['Hello world!'], 'Empty value must be filled from fallback');
        $this->assertSame('Bonjour!', $messages['Good morning!'], 'Missing key must be added from fallback');
    }

    public function testErrorLoggedWhenBothFilesMissing(): void
    {
        $source = $this->createSource(['sourceLanguage' => 'en-US']);
        $logger = Yii::getLogger();
        $logger->messages = [];

        $messages = $this->invokeMethod($source, 'loadMessages', ['test', 'hz-HZ']);

        $this->assertIsArray($messages);
        $errors = $this->getLogMessages(Logger::LEVEL_ERROR);
        $this->assertNotEmpty($errors);
        $errorMessage = array_values($errors)[0][0];
        $this->assertStringContainsString('hz-HZ', $errorMessage);
        $this->assertStringContainsString('Fallback file does not exist', $errorMessage);
        $this->assertLessThan(
            strpos($errorMessage, 'Fallback file'),
            strpos($errorMessage, 'The message file'),
            'Original file error must appear before fallback error'
        );
    }

    public function testNoErrorWhenFallbackPrefixesSourceLanguage(): void
    {
        $source = $this->createSource(['sourceLanguage' => 'en-US']);
        $logger = Yii::getLogger();
        $logger->messages = [];

        $this->invokeMethod($source, 'loadMessages', ['nonexistent', 'en-GB']);

        $errors = $this->getLogMessages(Logger::LEVEL_ERROR);
        $this->assertEmpty($errors);
    }

    public function testNoErrorWhenFallbackEqualsSourceLanguage(): void
    {
        $source = $this->createSource(['sourceLanguage' => 'zz']);
        $logger = Yii::getLogger();
        $logger->messages = [];

        $this->invokeMethod($source, 'loadMessages', ['nonexistent', 'zz-ZZ']);

        $errors = $this->getLogMessages(Logger::LEVEL_ERROR);
        $this->assertEmpty($errors);
    }
}
