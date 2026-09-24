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
use yiiunit\framework\i18n\stubs\ExposedPhpMessageSource;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\i18n\PhpMessageSource}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 * @group i18n
 */
final class PhpMessageSourceTest extends TestCase
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

        $this->assertStringNotContainsString('@', $path);
        $this->assertStringEndsWith('/en/test.php', $path);
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

    /**
     * Provides categories that resolve outside [[PhpMessageSource::basePath]] and must be rejected.
     *
     * @return array<string, array{string}> traversal, absolute, and stream-wrapper category inputs.
     */
    public static function unsafeCategoryProvider(): array
    {
        return [
            'absolute unix path' => ['/etc/passwd'],
            'bare parent segment' => ['..'],
            'embedded traversal segment' => ['app/../../config/db'],
            'leading traversal segment' => ['../secret'],
            'parent traversal' => ['../../etc/passwd'],
            'phar stream wrapper' => ['phar://archive.phar/config'],
            'php stream wrapper' => ['php://filter/resource=config/db'],
            'trailing traversal segment' => ['app/..'],
            'windows absolute path' => ['C:\\Windows\\system32'],
        ];
    }

    /**
     * Provides valid categories paired with their expected `basePath`-relative file name.
     *
     * @return array<string, array{string, string}> input category and resolved relative path.
     */
    public static function safeCategoryProvider(): array
    {
        return [
            'backslash namespace' => ['modules\\users\\validation', 'modules/users/validation'],
            'dashes and dots' => ['app-name/sub.module', 'app-name/sub.module'],
            'simple' => ['app', 'app'],
            'slash namespace' => ['app/error', 'app/error'],
        ];
    }

    /**
     * Provides categories outside the traversal threat model that must remain accepted.
     *
     * Leading `.`/`-`, a `letter:` prefix, non-ASCII names, and spaces resolve under `basePath` and predate the
     * hardening; rejecting them would be a backward-compatibility break wider than the documented threat model.
     *
     * @return array<string, array{string, string}> input category and resolved relative path.
     */
    public static function benignCategoryProvider(): array
    {
        return [
            'leading dot' => ['.messages', '.messages'],
            'leading hyphen' => ['-foo', '-foo'],
            'letter colon prefix' => ['a:foo', 'a:foo'],
            'non-ascii accented' => ['café', 'café'],
            'non-ascii cyrillic' => ['модуль', 'модуль'],
            'whitespace in name' => ['My Category', 'My Category'],
        ];
    }

    /**
     * Provides non-canonical but in-bounds categories that must remain accepted.
     *
     * Current-directory (`.`) segments and duplicate separators (`//`) still resolve under `basePath`; the documented
     * threat model only covers `..`, absolute paths, and stream wrappers, so rejecting them would break compatibility.
     *
     * @return array<string, array{string, string}> input category and resolved relative path.
     */
    public static function inBoundsNonCanonicalCategoryProvider(): array
    {
        return [
            'double slash' => ['app//error', 'app//error'],
            'embedded current-dir segment' => ['app/./db', 'app/./db'],
            'leading current-dir segment' => ['./config', './config'],
        ];
    }

    /**
     * @dataProvider unsafeCategoryProvider
     */
    public function testThrowInvalidArgumentExceptionForUnsafeCategory(string $category): void
    {
        $source = new ExposedPhpMessageSource(['basePath' => '@yiiunit/data/i18n/messages']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid message category:');

        $source->exposeMessageFilePath($category, 'en');
    }

    /**
     * @dataProvider safeCategoryProvider
     */
    public function testReturnExpectedPathForSafeCategory(string $category, string $expectedRelative): void
    {
        $source = new ExposedPhpMessageSource(
            [
                'basePath' => '@yiiunit/data/i18n/messages',
            ],
        );

        $expected = Yii::getAlias('@yiiunit/data/i18n/messages') . '/en/' . $expectedRelative . '.php';

        $this->assertSame(
            $expected,
            $source->exposeMessageFilePath($category, 'en'),
            'Namespace separators must be preserved under basePath.',
        );
    }

    /**
     * @dataProvider benignCategoryProvider
     */
    public function testReturnExpectedPathForBenignCategory(string $category, string $expectedRelative): void
    {
        $source = new ExposedPhpMessageSource(
            [
                'basePath' => '@yiiunit/data/i18n/messages',
            ],
        );

        $expected = Yii::getAlias('@yiiunit/data/i18n/messages') . '/en/' . $expectedRelative . '.php';

        $this->assertSame(
            $expected,
            $source->exposeMessageFilePath($category, 'en'),
            'Non-traversal category must resolve under basePath.',
        );
    }

    /**
     * @dataProvider inBoundsNonCanonicalCategoryProvider
     */
    public function testReturnExpectedPathForInBoundsNonCanonicalCategory(
        string $category,
        string $expectedRelative
    ): void {
        $source = new ExposedPhpMessageSource(
            [
                'basePath' => '@yiiunit/data/i18n/messages',
            ],
        );

        $expected = Yii::getAlias('@yiiunit/data/i18n/messages') . '/en/' . $expectedRelative . '.php';

        $this->assertSame(
            $expected,
            $source->exposeMessageFilePath($category, 'en'),
            'Non-canonical in-bounds category must be accepted.',
        );
    }

    public function testReturnFileMapPathForMappedCategory(): void
    {
        $source = new ExposedPhpMessageSource(
            [
                'basePath' => '@yiiunit/data/i18n/messages',
                'fileMap' => ['weird/../category' => 'safe.php'],
            ],
        );

        $expected = Yii::getAlias('@yiiunit/data/i18n/messages') . '/en/safe.php';

        $this->assertSame(
            $expected,
            $source->exposeMessageFilePath('weird/../category', 'en'),
            'fileMap entry must bypass category validation.',
        );
    }
}
