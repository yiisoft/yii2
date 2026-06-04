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
use yiiunit\framework\i18n\stubs\ExposedPhpMessageSource;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\i18n\PhpMessageSource} category path-traversal hardening.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 * @group i18n
 */
final class PhpMessageSourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
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
